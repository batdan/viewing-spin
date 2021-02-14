<?php
namespace spin;

/**
 * Gestion des tirages et calculs de spin
 *
 * @author Daniel Gomes
 */
class processCalc implements \Jenner\SimpleFork\Runnable
{
    /**
    * process entry
    *
    * @return mixed
    */
    public function run()
    {
        // Limite nombre de tirages
        $limit = 30;

        // Instance PDO
        $dbh = \core\dbSingleton::getInstance();

        $req = "SELECT      id, spinid, xml, bdd_table, bdd_champ, bdd_id,
                            objectif, nbCycle, lastNbCycle, simMoyPct, nbEnvoiCalc,
                            (nbCycle - lastNbCycle) AS cycles

                FROM        spin_calc

                WHERE       statut = :statut
                AND         ((nbCycle - lastNbCycle) < $limit)
                AND         ((process_deb IS NULL AND process_fin IS NULL) OR (process_deb IS NOT NULL AND process_fin IS NOT NULL))

                ORDER BY    cycles ASC";

        $sql = $dbh->prepare($req);
        $sql->execute( array( ':statut'=>'calcul_on' ));

		if ($sql->rowCount() > 0) {

            $res = $sql->fetch();

            $id             = $res->id;
            $spinid         = $res->spinid;
            $xml            = $res->xml;
            $bddTable       = $res->bdd_table;
            $bddChamp       = $res->bdd_champ;
            $bddId          = $res->bdd_id;
            $objectif       = $res->objectif;
            $nbCycle        = $res->nbCycle;
            $lastNbCycle    = $res->lastNbCycle;
            $nbEnvoiCalc    = $res->nbEnvoiCalc;
            $simMoyPct      = $res->simMoyPct;

            $process_deb    = date('Y-m-d H:i:s');

            // Date et heure du démarrage
            $req = "UPDATE      spin_calc

                    SET         process_deb = :process_deb,
                                process_fin = NULL

                    WHERE       id = :id";

            $sql = $dbh->prepare($req);
            $sql->execute( array( ':id'=>$id, ':process_deb'=>$process_deb ));

            // Affichage du spinid
            system("clear");
            $colors = new \core\cliColorText();
            echo chr(10) . ' ';
            echo $colors->getColor( 'spinid : ', "light_blue" );
            echo $colors->getColor( $spinid,     "light_gray" );
            echo chr(10);
            flush();

            // Calcul
            $process = new process();
            $process->hydrate( array( 'xml'=>$xml, 'objectif'=>$objectif ));
            $result = $process->calculSpin();

            $newNbCycle = $nbCycle + 1;

            $process_fin = date('Y-m-d H:i:s');

            // Mise à jour des résultats du spin et de la date et heure de fin de calcul
            if ($simMoyPct == 0 || $result['simMoyPct'] < $simMoyPct) {

                // Résultats meilleurs = sauvegarde

                // Stockages des résultats dans la vue xml
                $dom = new \DOMDocument('1.0', 'utf-8');
                $dom->loadXML($xml, LIBXML_NOBLANKS);

                $xpath = new \DOMXPath($dom);

        		$req		= '//masterspin';
        		$entries 	= $xpath->query($req);
        		$masterspin	= $entries->item(0);
        		$masterspin->setAttribute('lastmodif', $process_fin);

                // Suppression des anciens enfants de tirage
                $req		= '//tirage/*';
        		$entries 	= $xpath->query($req);

        		foreach ($entries as $entry) {
        			$entry->parentNode->removeChild($entry);
        		}

                $req 		= '//tirage';
                $entries 	= $xpath->query($req);
                $tirage     = $entries->item(0);

                $tirage->setAttribute('nbCycle',            $newNbCycle);
                $tirage->setAttribute('lastNbCycle',        $lastNbCycle);
                $tirage->setAttribute('nbEnvoiCalc',        $nbEnvoiCalc);
                $tirage->setAttribute('nbMotsMoy',          $result['nbMotsMoy']);
                $tirage->setAttribute('simMoyNb',           $result['simMoyNb']);
                $tirage->setAttribute('simMoyPct',          $result['simMoyPct']);
                $tirage->setAttribute('meilleursCoupleNB',  $result['meilleursCoupleNB']);
                $tirage->setAttribute('meilleursCouplePct', $result['meilleursCouplePct']);
                $tirage->setAttribute('pireCoupleNb',       $result['pireCoupleNb']);
                $tirage->setAttribute('pireCoupleNb',       $result['pireCouplePct']);

                $tirage->setAttribute('sup20Pct',           $result['sup20Pct']);
                $tirage->setAttribute('sup30Pct',           $result['sup30Pct']);
                $tirage->setAttribute('sup40Pct',           $result['sup40Pct']);
                $tirage->setAttribute('sup50Pct',           $result['sup50Pct']);
                $tirage->setAttribute('sup60Pct',           $result['sup60Pct']);
                $tirage->setAttribute('sup70Pct',           $result['sup70Pct']);

                $tirage->setAttribute('process_deb',        $process_deb);
                $tirage->setAttribute('process_fin',        $process_fin);


                /*
                // Ajout des nouveaux résultats
                $i=1;
                foreach ($result['aiguillages'] as $res) {
                    $version = $dom->createElement('version');
                    $version->setAttribute('rang', $i);
                    $version->setAttribute('json', $res);

                    $tirage->appendChild($version);
                    $i++;
                }
                */

                $newXML = $dom->saveXML();

                // Stockage des résultats dans un fichier
                $cfgSpin    = \core\config::getConfig('spinCalc');

                $req 		= '//master';
                $entries 	= $xpath->query($req);
                $master     = $entries->item(0);

                $bdd        = $master->getAttribute('bdd');
                $bddtable   = $master->getAttribute('bddtable');
                $baseid     = $master->getAttribute('bddid');
                $chp        = $master->getAttribute('chp');

                $fileName   = 'SC#' . $bdd . '#' . $bddtable . '#' . $baseid . '#' . $chp;
                $filePath   = $cfgSpin['path'];

                $file = fopen($filePath . $fileName, "w+");
                foreach ($result['aiguillages'] as $res) {
                    fputs($file, $res . "\n");
                }
                fclose($file);

                // Sauvegarde des résultats et du spin dans 'spin_calc'
                $req = "UPDATE      spin_calc

                        SET         xml                 = :xml,
                                    nbCycle             = :nbCycle,
                                    nbMotsMoy           = :nbMotsMoy,
                                    simMoyNb            = :simMoyNb,
                                    simMoyPct           = :simMoyPct,
                                    meilleursCoupleNB   = :meilleursCoupleNB,
                                    meilleursCouplePct  = :meilleursCouplePct,
                                    pireCoupleNb        = :pireCoupleNb,
                                    pireCouplePct       = :pireCouplePct,
                                    sup20Pct            = :sup20Pct,
                                    sup30Pct            = :sup30Pct,
                                    sup40Pct            = :sup40Pct,
                                    sup50Pct            = :sup50Pct,
                                    sup60Pct            = :sup60Pct,
                                    sup70Pct            = :sup70Pct,
                                    process_fin         = :process_fin

                        WHERE       id = :id";

                $sql = $dbh->prepare($req);
                $sql->execute( array(
                                    ':xml'                  => $newXML,
                                    ':id'                   => $id,
                                    ':nbCycle'              => $newNbCycle,
                                    ':nbMotsMoy'            => $result['nbMotsMoy'],
                                    ':simMoyNb'             => $result['simMoyNb'],
                                    ':simMoyPct'            => $result['simMoyPct'],
                                    ':meilleursCoupleNB'    => $result['meilleursCoupleNB'],
                                    ':meilleursCouplePct'   => $result['meilleursCouplePct'],
                                    ':pireCoupleNb'         => $result['pireCoupleNb'],
                                    ':pireCouplePct'        => $result['pireCouplePct'],
                                    ':sup20Pct'             => $result['sup20Pct'],
                                    ':sup30Pct'             => $result['sup30Pct'],
                                    ':sup40Pct'             => $result['sup40Pct'],
                                    ':sup50Pct'             => $result['sup50Pct'],
                                    ':sup60Pct'             => $result['sup60Pct'],
                                    ':sup70Pct'             => $result['sup70Pct'],
                                    ':process_fin'          => $process_fin,
                                    ));

                // Sauvegarde du spin dans sa table
                $req = "UPDATE $bddTable SET $bddChamp = :xml WHERE id = :id";
                $sql = $dbh->prepare($req);
                $sql->execute( array( ':xml'=>$newXML, ':id'=>$bddId ));

                // Sauvegarde du spin dans spin_histo
                $req = "SELECT id FROM spin_histo WHERE spinid = :spinid ORDER BY id DESC";
                $sql = $dbh->prepare($req);
                $sql->execute( array( ':spinid'=>$spinid ));

                if ($sql->rowCount() > 0) {

                    $res = $sql->fetch();

                    $req = "UPDATE      spin_histo

                            SET         xml         = :xml,
                                        lastmodif   = :lastmodif

                            WHERE       id = :id";

                    $sql = $dbh->prepare($req);
                    $sql->execute( array(
                                        ':xml'          => $newXML,
                                        ':lastmodif'    => $process_fin,
                                        ':id'           => $res->id,
                                        ));
                }


            } else {

                // Pas d'amélioration, conservation du dernier calcul
                $req = "UPDATE spin_calc SET nbCycle = :nbCycle, process_fin = :process_fin WHERE id = :id";
                $sql = $dbh->prepare($req);
                $sql->execute( array(
                                    ':id'           => $id,
                                    ':nbCycle'      => $newNbCycle,
                                    ':process_fin'  => $process_fin,
                                    ));
            }

            // On vérifie si le satut du spin n'a pas été changé manuellement pendant le calcul
            $req = "SELECT statut FROM spin_calc WHERE spinid = :spinid";
            $sql = $dbh->prepare($req);
            $sql->execute( array( ':spinid'=>$spinid ));
            $res = $sql->fetch();
            $actuStatut = $res->statut;

            ////////////////////////////////////////////////////////////////////
            // Le spin est renvoyé à l'édition, on met à jour les vues XML
            if ($actuStatut == 'edit') {

                // Changement de statut du spin dans la vue XML -> statut = calcul_end
                $dom = new \DOMDocument('1.0', 'utf-8');
                $dom->loadXML($xml, LIBXML_NOBLANKS);

                $xpath = new \DOMXPath($dom);

        		$req		= '//masterspin';
        		$entries 	= $xpath->query($req);
        		$masterspin	= $entries->item(0);
        		$masterspin->setAttribute('lastmodif', $process_fin);
                $masterspin->setAttribute('statut', 'edit');

                $newXML = $dom->saveXML();

                // Sauvegarde du spin dans sa table
                $req = "UPDATE $bddTable SET $bddChamp = :xml WHERE id = :id";
                $sql = $dbh->prepare($req);
                $sql->execute( array( ':xml'=>$newXML, ':id'=>$bddId ));

                // Sauvegarde du spin dans spin_histo
                $req = "INSERT INTO spin_histo (spinid, action, xml, lastmodif, activ) VALUES (:spinid, :action, :xml, :lastmodif, 1)";
                $sql = $dbh->prepare($req);
                $sql->execute( array(
                                    ':spinid'       => $spinid,
                                    ':action'       => 'Retour edtion',
                                    ':xml'          => $newXML,
                                    ':lastmodif'    => $process_fin,
                                    ));
            }

            ////////////////////////////////////////////////////////////////////
            // L'objectif est atteint, passage du spin en 'calculé' ou le spin a été forcé en statut terminé
            if ($newNbCycle == ($limit + $lastNbCycle) || $actuStatut == 'calcul_end') {

                // Changement de statut du spin dans la vue XML -> statut = calcul_end
                $dom = new \DOMDocument('1.0', 'utf-8');
                $dom->loadXML($xml, LIBXML_NOBLANKS);

                $xpath = new \DOMXPath($dom);

        		$req		= '//masterspin';
        		$entries 	= $xpath->query($req);
        		$masterspin	= $entries->item(0);
        		$masterspin->setAttribute('lastmodif', $process_fin);
                $masterspin->setAttribute('statut', 'calcul_end');

                $newXML = $dom->saveXML();

                // Sauvegarde du spin dans sa table
                $req = "UPDATE $bddTable SET $bddChamp = :xml WHERE id = :id";
                $sql = $dbh->prepare($req);
                $sql->execute( array( ':xml'=>$newXML, ':id'=>$bddId ));

                // Sauvegarde du spin dans spin_histo
                $req = "INSERT INTO spin_histo (spinid, action, xml, lastmodif, activ) VALUES (:spinid, :action, :xml, :lastmodif, 1)";
                $sql = $dbh->prepare($req);
                $sql->execute( array(
                                    ':spinid'       => $spinid,
                                    ':action'       => 'Calcul terminé',
                                    ':xml'          => $newXML,
                                    ':lastmodif'    => $process_fin,
                                    ));

                // Changement de statut dans la table 'spin_calc' si nécessaire
                if ($actuStatut == 'calcul_on') {
                    $req = "UPDATE spin_calc SET statut = :statut, xml = :xml WHERE spinid = :spinid";
                    $sql = $dbh->prepare($req);
                    $sql->execute( array( ':statut'=>'calcul_end', ':xml'=>$newXML, ':spinid'=>$spinid ));
                }
            }
        }

        // Close instance PDO
        \core\dbSingleton::closeInstance();
    }
}
