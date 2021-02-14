<?php
namespace spin;

/**
 * Gestion des tirages et calculs de spin
 *
 * @author Daniel Gomes
 */
class process
{
	/**
	 * Attributs
	 */
    private $_xml;
	private $_dom;

    private $_decalRang		= 0;			// Permet un décallage de rang pour les spins non calculés (duplication de projet)
    private $_objectif 		= 2000;			// Objectif d'un projet : nombre de villes à atteindre

    private $_maxPotential 	= 1500000;		// Seuil de passage au calcul de spin


	/**
	 * Constructeur
	 */
	public function __construct()
    {
        // Initialisation du dom
		$this->_dom = new \DOMDocument('1.0', 'utf-8');
	}


    /**
	 * Hydratation de la classe
	 *
	 * @param array $donnees
	 */
	public function hydrate(array $data)
	{
		foreach ($data as $k=>$v)
		{
			$method = 'set'.ucfirst($k);

			if (method_exists($this, $method)) {
				$this->$method($v);
			}
		}
	}


	/**
	 * Setters
	 */
    public function setXml($xml) {
 		$this->_dom->loadXML($xml, LIBXML_NOBLANKS);
 		$this->_xml = $xml;
 	}
	public function setDom($dom) {
		$this->_dom = $dom;
	}
	public function setObjectif($objectif) {
		$this->_objectif = $objectif;
	}
	public function setDecalRang($decalRang) {
		$this->_decalRang = $decalRang;
	}


	/**
	 * Getters
	 */
	public function getXml() {
		return $this->_dom->saveXML();
	}


    /**
     * Renvoi le noeud masterspin
     */
    public function getMasterspinNode()
    {
        $xpath		= new \DOMXPath($this->_dom);

        $req 		= '//masterspin';
        $entries 	= $xpath->query($req);
        $masterspin = $entries->item(0);

        return $masterspin;
    }


    /**
     * Renvoi le nombre de combinaisons d'un masterspin
     */
    public function getPotential()
    {
        $masterspin = $this->getMasterspinNode();
        return $masterspin->getAttribute('potential');
    }


    /**
	 * Potentiel d'un spin (nombre de possibilités)
	 * Envoi au calcul si le nombre dépasse l'attribut '_maxPotential'
	 */
	public function potential()
    {
        $potential 	= 1;

        $xpath		= new \DOMXPath($this->_dom);

        $req 		= '//masterspin';
        $entries 	= $xpath->query($req);
        $masterspin = $entries->item(0);

        if ($masterspin->hasAttribute('statut') && ($masterspin->getAttribute('statut') == 'calcul_on' || $masterspin->getAttribute('statut') == 'calcul_end')) {
            return;
        }

        $req 		= '//masterspin/*';
        $entries 	= $xpath->query($req);

        foreach ($entries as $entry) {

            $potential *= $this->potential_aux($entry);

            if ($potential == 0 || $potential > $this->_maxPotential) {
                $potential = 'calcul';
                break;
            }
        }

        $masterspin->setAttribute('potential', $potential);

        if ($potential == 'calcul') {

            // Suppression des attributs poss
            $req 		= '//*[@n]';
            $entries 	= $xpath->query($req);

            foreach ($entries as $entry) {
                $entry->removeAttribute('n');
            }

            // Ajout du suivi
            $masterspin->setAttribute('statut', 'edit');

        } else {

            // Suppression du statut de spin calculé (edit|calcul_on|calcul_end)
            if ($masterspin->hasAttribute('statut')) {
                $masterspin->removeAttribute('statut');
            }
        }
    }


    /**
	 * Potentiel d'un spin (nombre de possibilités)
	 * Fonction récursive auxiliaire
	 *
	 * @param unknown 	$dom
	 */
	public function potential_aux($dom)
	{
        $potential 	= 1;

        $xpath = new \DOMXPath($dom->ownerDocument);

		// Balises 'spin'
		if ($dom->nodeName == 'spin') {

			$req 	= 'comb';
			$res 	= $xpath->query($req, $dom);

			$spinPotential = 0;

			foreach ($res as $child) {
				$spinPotential += $this->potential_aux($child);
			}

            $dom->setAttribute('n', $spinPotential);

			$potential *= $spinPotential;

			if ($potential > $this->_maxPotential) {
				return 0;
			}
		}

		// Balises 'comb' ou 'tag'
		if ($dom->nodeName == 'comb' || $dom->nodeName == 'tag') {

			$req 	= 'spin|tag';
			$res 	= $xpath->query($req, $dom);

			foreach ($res as $child) {

				$potential *= $this->potential_aux($child);

				if ($potential > $this->_maxPotential) {
					return 0;
				}
			}

            $dom->setAttribute('n', $potential);
		}

		return $potential;
	}


    /**
     * Tirage d'un spin (classic|calculé)
     *
     * @param   integer     $rang       N° de la version / rang de la ville
     */
    public function version($rang)
    {
        $texte = '';

        $potential = $this->getPotential();

        if ($potential == 'calcul') {

            $texte = $this->versionCalc($rang);

        } else {

            if ( ! empty($this->_objectif) && $potential > ($this->_objectif + $this->_decalRang) ) {
                $tirage = ceil($potential / $this->_objectif) * $rang;
                $texte = $this->versionClassic($tirage + $this->_decalRang);
            } else {
                $texte = $this->versionClassic($rang + $this->_decalRang);
            }
        }

        // Remplacement des balises spécifiques (géo, blocs html, etc.)
        $dom = new \DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML('<?xml encoding="UTF-8"><body>' . $texte . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $tags  = new tags;
        $tags->hydrate(array( 'dom'=>$dom ));
        $texte = $tags->replaceBalisesSpe($rang);

		// Gestion des espaces inséclables : remplacement des ~ par des espaces insécables
		$texte = str_replace('~', '&nbsp;', $texte);

        return $texte;
    }


    /**
     * Récupère une version d'un spin non calculé
     *
     * @param   integer     $numVer     N° de la version / rang de la ville
     */
    public function versionClassic($numVer)
    {
        $masterspin   = $this->getMasterspinNode();
		$html         = $this->versionClassic_aux($masterspin, $numVer);

		return $html;
    }


    /**
     * Récupère une version d'un spin non calculé
     * Fonction auxiliaire récursive
     */
    public function versionClassic_aux($dom, $numVer)
	{
		$xpath 	= new \DOMXPath($dom->ownerDocument);

		$html = '';

		// Balises 'spin'
		if ($dom->nodeName == 'spin') {

            $req 	= 'comb';
			$res 	= $xpath->query($req, $dom);

            // Nombre de possibilité de l'élément
            $poss   = 0;
            foreach ($res as $child) {

                $poss += $child->getAttribute('n');

                if ($poss >= $numVer) {
					return $this->versionClassic_aux($child, $numVer);
				}
            }
		}

		// Autres balises
        $arrayTags = array('masterspin', 'comb', 'text', 'tag');
		if (in_array($dom->nodeName, $arrayTags)) {

			$req 	= 'spin|text|tag';
			$res 	= $xpath->query($req, $dom);

            $NumVerTmp1	= $numVer;
			$poss    	= 1;

            // Balise HTML d'ouverture
            if ($dom->nodeName == 'tag') {
                $html .= $this->tagHTML($dom, 'deb', $numVer);
            }

            // Récupération des contenus
			foreach ($res as $child) {

                if ($child->nodeName == 'text') {
					$html .= $child->nodeValue;
				}

                if ($child->hasAttribute('n'))  { $n = $child->getAttribute('n'); }
                else                            { $n = 1;                         }

                $NumVerTmp2	= ($NumVerTmp1 % $n) + 1;

                // Récursivité
				$html .= $this->versionClassic_aux($child, $NumVerTmp2);

                $poss *= $n;

                $NumVerTmp1 = intval($numVer/$poss);
                if ($numVer/$poss - intval($numVer/$poss) > 0) {
                    $NumVerTmp1 += 1;
                }
			}

            // Balise HTML de fermeture
            if ($dom->nodeName == 'tag') {
                $html .= $this->tagHTML($dom, 'fin', $numVer);
            }
		}

        return $html;
	}


    /**
     * Récupère une version d'un spin calculé
     */
    public function versionCalc($numVer=null)
    {
        $html       = '';

        $masterspin = $this->getMasterspinNode();
        $statut     = $masterspin->getAttribute('statut');

        if ($statut=='edit' || $statut=='calcul_on') {

            $tirage = $this->calculAiguillageSpin(1);
            $tirage = json_decode($tirage[0]);

            $html  .= '<i class="fa fa-clone" style="color:#C63632;"></i>';

        } else {

            $xpath		= new \DOMXPath($this->_dom);

            // On vérifie si le résultat est stocké dans le XML (V1) ou dans un fichier (V2)
            $req 		= '//tirage';
            $entries 	= $xpath->query($req);
            $entry    = $entries->item(0);

            // Version stockée dans un fichier
            if ($entry->hasAttribute('nbCycle') && $entry->childNodes->length == 0) {

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

                $file       = file( $filePath . $fileName );

				if (!array_key_exists($numVer-1, $file)) {
					error_log('Test erreur ('.($numVer-1).') : ' . $filePath . $fileName, 0);
				}

                $tirage     = json_decode( $file[$numVer-1] );
            }

            // Version stockée dans le XML (old)
            if ($entry->hasAttribute('nbCycle') && $entry->childNodes->length > 0) {

                $req 		= '//tirage/version[@rang="'.$numVer.'"]';
                $entries 	= $xpath->query($req);
                $version    = $entries->item(0);
                $tirage     = json_decode( $version->getAttribute('json') );
            }
        }

		$html .= $this->versionCalc_aux($masterspin, $tirage, $numVer);

		return $html;
    }


    /**
     *
     */
    public function versionCalc_aux($dom, $tirage, $numVer)
    {
        $xpath 	= new \DOMXPath($dom->ownerDocument);

		$html = '';

		// Balises 'spin'
		if ($dom->nodeName == 'spin') {

            $id     = $dom->getAttribute('id');
            $id_comb= $tirage->$id;
            $id_comb--;

            $comb   = $dom->childNodes->item($id_comb);

			$html .= $this->versionCalc_aux($comb, $tirage, $numVer);
		}

        // Balises 'text'
		if ($dom->nodeName == 'text') {
            $html .= $dom->nodeValue;
		}

		// Autres balises
        $arrayTags = array('masterspin', 'comb', 'tag');
		if (in_array($dom->nodeName, $arrayTags)) {

			$req 	= 'spin|text|tag';
			$res 	= $xpath->query($req, $dom);

            // Balise HTML d'ouverture
            if ($dom->nodeName == 'tag') {
                $html .= $this->tagHTML($dom, 'deb', $numVer);
            }

            // Récupération des enfants
			foreach ($res as $child) {
                $html .= $this->versionCalc_aux($child, $tirage, $numVer);
			}

            // Balise HTML de fermeture
            if ($dom->nodeName == 'tag') {
                $html .= $this->tagHTML($dom, 'fin', $numVer);
            }
		}

        return $html;
    }


    /**
     * Récupère tous les noeuds spins d'un masterspin
     * et leur nombre de combinaisons
     */
    public function spinListPoss()
    {
        $xpath		= new \DOMXPath($this->_dom);

        $query 	    = '//spin';
        $entries 	= $xpath->query($query);

        $spinList = array();
        foreach ($entries as $entry) {
            $spinList[$entry->getAttribute('id')] = $entry->childNodes->length;
        }

        return $spinList;
    }


    /**
     * Calcul de l'aiguillage des spins (basé sur l'aléa)
     */
    public function calculAiguillageSpin($objectif)
    {
        $spinList = $this->spinListPoss();

        // Liste des tirages
        $tiragesList = array();

        $j=0;
        for ($i=0; $i<$objectif;) {

            // Récupération d'un choix aléatoire dans chaque spin
            $tirage = array();
            foreach ($spinList as $k=>$v) {
                $tirage[$k] = mt_rand(1, $v);
            }

            // 1 tirage = 1 texte
            $tirage = json_encode($tirage);
            if (! in_array($tirage, $tiragesList)) {
                $tiragesList[$i] = $tirage;
                $i++;
            }

            // Sécurité
            if ($j==100000) { break; }
            $j++;
        }

        return $tiragesList;
    }


    /**
     * Convertion balise tag en HTML
     *
     * @param   $dom        dom         domElement
     * @param   $sibling    string      Balise HTML d'ouverture ou de fermeture ()
     */
    private function tagHTML($dom, $sibling, $numVer)
    {
        if ($sibling == 'deb') {

            $filtre     = array('id', 'tag', 'close', 'n');
            $attributes = array();

			$tags = new tags();

			foreach ($dom->attributes as $attr) {
    			if (! in_array($attr->nodeName, $filtre)) {

					// on vérifie s'il n'y a pas de variables spécifique en texte (ex: ___a_ville___ )
					$attrValue = $attr->nodeValue;
					preg_match_all("/___([a-zA-Z_]+)___/", $attrValue, $listTagSpe);

					if (count($listTagSpe[1]) > 0) {
						foreach ($listTagSpe[1] as $tagSpe) {
							$attrValue = str_replace('___'.$tagSpe.'___', $tags->flatTagSpe($tagSpe, $numVer), $attrValue);
						}
					}

    				$attributes[] = $attr->nodeName.'="'.$attrValue.'"';
    			}
    		}

    		if (count($attributes) > 0) {
    			$attributes = ' ' . implode(' ', $attributes);
    		} else {
                $attributes = '';
            }

            $endTag = '';
            if ($dom->getAttribute('tag') == 'spe') {
                $endTag = '/';
            }

    		return '<'.$dom->getAttribute('tag').$attributes.$endTag.'>';
        }

        if ($sibling == 'fin') {
            if ($dom->getAttribute('close') == 1) {
                return '</'.$dom->getAttribute('tag').'>';
            } else {
                return '';
            }
        }
    }


    /**
     * Retourne une durée en hh:mm:ss
     */
    private function duree($dateTimeDeb, $dateTimeFin=null)
    {
        if (is_null($dateTimeFin)) {
            $dateTimeFin = new \DateTime();
        }

        $interval = $dateTimeFin->diff($dateTimeDeb);
        return $interval->format('%Hh:%Im:%Ss');
    }


    /**
	 * Calcul spin
	 */
	public function calculSpin($debug = true)
	{
        $result = array();

        $colors = new \core\cliColorText();

        if (PHP_SAPI === 'cli') {
            $br = chr(10);
            $hr = chr(10);
        } else {
            $br = '<br>';
            $hr = '<hr>';
        }

        // Timer : debut
        $dateTimeDeb = new \DateTime();

        // Tirage aléatoire lié à l'objectif (idspin|nbVersion)
        $aiguillages = $this->calculAiguillageSpin($this->_objectif);

        $result['aiguillages'] = $aiguillages;

        $duree = $this->duree($dateTimeDeb);
        if ($debug) {
            echo $br . ' ';
            echo $colors->getColor($duree, "light_gray");
            echo $colors->getColor(' - Aiguillages', "light_blue");
            flush();
        }

        ///////////////////////////////////////////////////////////////////////////
        // Récupération des textes
        $textes = array();
        foreach ($aiguillages as $tirage) {
            $textes[] = $this->versionCalc(json_decode($tirage));
        }

        $duree = $this->duree($dateTimeDeb);
        if ($debug) {
            echo $br . ' ';
            echo $colors->getColor($duree, "light_gray");
            echo $colors->getColor(' - Textes', "light_blue");
            flush();
        }

        // tableau de mots racinisés, suppression des mots vides (PaiceHuskStemmer)
        $phs = new paiceHuskStem('fr');

        ///////////////////////////////////////////////////////////////////////////
        // Suppression des caractères ponctués de tous les textes
        $textes2 = array();
        foreach ($textes as $texte) {
            $textes2[] = $phs->supprPonctus($texte);
        }

        $duree = $this->duree($dateTimeDeb);
        if ($debug) {
            echo $br . ' ';
            echo $colors->getColor($duree, "light_gray");
            echo $colors->getColor(' - Suppr. ponctu', "light_blue");
            flush();
        }

        ///////////////////////////////////////////////////////////////////////////
        // Suppression des mots vides de tous les textes
        $textes3 = array();
        foreach ($textes2 as $texte) {
            $textes3[] = $phs->supprStopWord($texte);
        }

        $duree = $this->duree($dateTimeDeb);
        if ($debug) {
            echo $br . ' ';
            echo $colors->getColor($duree, "light_gray");
            echo $colors->getColor(' - Suppr. stopWord', "light_blue");
            flush();
        }

        ///////////////////////////////////////////////////////////////////////////
        // Racinisation des mots de tous les textes
        $textesPHS = array();
        foreach ($textes3 as $texte) {
            $textesPHS[] = $phs->textPaiceHuskStemmer($texte);
        }

        $duree = $this->duree($dateTimeDeb);
        if ($debug) {
            echo $br . ' ';
            echo $colors->getColor($duree, "light_gray");
            echo $colors->getColor(' - Paice Husk Stemmer', "light_blue");
            flush();
        }

        ///////////////////////////////////////////////////////////////////////////
        // Comparaison des couples de texte
        $nbMots     = array();
        $similarity = array();

        $i=0;
        foreach ($textesPHS as $textePHS1) {

            $nbMots[] = count($textePHS1);

            $j=0;
            foreach ($textesPHS as $textePHS2) {

                if ($i != $j) {

                    $similarity[$i.'|'.$j] = count( array_intersect($textePHS1, $textePHS2) );
                }
                $j++;
            }
            $i++;
        }

        $duree = $this->duree($dateTimeDeb);
        if ($debug) {
            echo $br . ' ';
            echo $colors->getColor($duree, "light_gray");
            echo $colors->getColor(' - Fin comparaisons', "light_blue");
            flush();
        }

        // Nombre de mots moyen
        $nbMotsMoy = round(array_sum($nbMots) / count($nbMots), 2);
        $result['nbMotsMoy'] = $nbMotsMoy;

		/*
        $nbMots_20 = round(($nbMotsMoy * 0.2), 2);  // Nombre de mots | 20 %
        $nbMots_30 = round(($nbMotsMoy * 0.3), 2);  // Nombre de mots | 30 %
		*/
		$nbMots_40 = round(($nbMotsMoy * 0.4), 2);  // Nombre de mots | 40 %
        $nbMots_50 = round(($nbMotsMoy * 0.5), 2);  // Nombre de mots | 50 %
        $nbMots_60 = round(($nbMotsMoy * 0.6), 2);  // Nombre de mots | 60 %
        $nbMots_70 = round(($nbMotsMoy * 0.7), 2);  // Nombre de mots | 70 %

        // Similarité
        sort($similarity);

		/*
        $countSim_20 = 0;
        $countSim_30 = 0;
		*/
        $countSim_40 = 0;
        $countSim_50 = 0;
        $countSim_60 = 0;
        $countSim_70 = 0;

        foreach ($similarity as $nbMots) {
			/*
            if ($nbMots > $nbMots_20) { $countSim_20++; }
            if ($nbMots > $nbMots_30) { $countSim_30++; }
			*/
            if ($nbMots > $nbMots_40) { $countSim_40++; }
            if ($nbMots > $nbMots_50) { $countSim_50++; }
            if ($nbMots > $nbMots_60) { $countSim_60++; }
			if ($nbMots > $nbMots_70) { $countSim_70++; }
        }

        $countSimilarity = pow($this->_objectif, 2);

		/*
        $sup20Pct = round((100 / $countSimilarity) * $countSim_20, 2);    // Similarité supérieur à 20%
        $sup30Pct = round((100 / $countSimilarity) * $countSim_30, 2);    // Similarité supérieur à 30%
		*/
        $sup40Pct = round((100 / $countSimilarity) * $countSim_40, 2);    // Similarité supérieur à 40%
        $sup50Pct = round((100 / $countSimilarity) * $countSim_50, 2);    // Similarité supérieur à 50%
        $sup60Pct = round((100 / $countSimilarity) * $countSim_60, 2);    // Similarité supérieur à 60%
        $sup70Pct = round((100 / $countSimilarity) * $countSim_70, 2);    // Similarité supérieur à 70%

		$result['sup20Pct'] = 0;
        $result['sup30Pct'] = 0;
        $result['sup40Pct'] = $sup40Pct;
		$result['sup50Pct'] = $sup50Pct;
        $result['sup60Pct'] = $sup60Pct;
        $result['sup70Pct'] = $sup70Pct;

        // Moyenne des similarités
        $simMoyNb  = round( array_sum($similarity) / $countSimilarity, 2);
        $simMoyPct = round( (100 / $nbMotsMoy) * $simMoyNb , 2);
        $result['simMoyNb']  = $simMoyNb;
        $result['simMoyPct'] = $simMoyPct;

        // meilleur couple
        $meilleursCoupleNB  = reset($similarity);
        $meilleursCouplePct = round( (100 / $nbMotsMoy) * $meilleursCoupleNB, 2);
        $result['meilleursCoupleNB']  = $meilleursCoupleNB;
        $result['meilleursCouplePct'] = $meilleursCouplePct;

        // Pire couple
        $pireCoupleNb = end($similarity);
        $pireCouplePct = round( (100 / $nbMotsMoy) * $pireCoupleNb, 2);
        $result['pireCoupleNb']  = $pireCoupleNb;
        $result['pireCouplePct'] = $pireCouplePct;

        $duree = $this->duree($dateTimeDeb);
        if ($debug) {
            echo $br . ' ';
            echo $colors->getColor($duree, "light_gray");
            echo $colors->getColor(' - Fin calculs', "light_blue");
            flush();
        }

        // Nombre de mots moyen
        if ($debug) {
            echo $br . $br . ' ';
            echo $colors->getColor('Nb de mots moyen : ', "light_blue");
            echo $colors->getColor($nbMotsMoy, "light_gray");

            // Similarité
            echo $br . $br . ' ';
            echo $colors->getColor('Similarité moyenne (nb) : ', "light_blue");
            echo $colors->getColor($simMoyNb, "light_gray");
            echo $br . ' ';
            echo $colors->getColor('Similarité moyenne : ', "light_blue");
            echo $colors->getColor($simMoyPct . '%', "light_gray");

            // Meilleur / pire couple
            echo $br . $br . ' ';
            echo $colors->getColor('Meilleur couple (nb) : ', "light_blue");
            echo $colors->getColor($meilleursCoupleNB, "light_gray");
            echo $br . ' ';
            echo $colors->getColor('Meilleur couple : ', "light_blue");
            echo $colors->getColor($meilleursCouplePct . '%', "light_gray");
            echo $br . $br . ' ';
            echo $colors->getColor('Pire couple (nb) : ', "light_blue");
            echo $colors->getColor($pireCoupleNb, "light_gray");
            echo $br . ' ';
            echo $colors->getColor('Pire couple : ', "light_blue");
            echo $colors->getColor($pireCouplePct . '%', "light_gray");

            // Pourcentage de similarité au dessus de 20, 30, 40%
			/*
            echo $br . $br . ' ';
            echo $colors->getColor('Similarité au dessus de 20% : ', "light_blue");
            echo $colors->getColor($sup20Pct . '%', "light_gray");
            echo $br . ' ';
            echo $colors->getColor('Similarité au dessus de 30% : ', "light_blue");
            echo $colors->getColor($sup30Pct . '%', "light_gray");
			*/
			echo $br . $br . ' ';
            echo $colors->getColor('Similarité au dessus de 40% : ', "light_blue");
            echo $colors->getColor($sup40Pct . '%', "light_gray");
			echo $br . ' ';
            echo $colors->getColor('Similarité au dessus de 50% : ', "light_blue");
            echo $colors->getColor($sup50Pct . '%', "light_gray");
            echo $br . ' ';
            echo $colors->getColor('Similarité au dessus de 60% : ', "light_blue");
            echo $colors->getColor($sup60Pct . '%', "light_gray");
            echo $br . ' ';
            echo $colors->getColor('Similarité au dessus de 70% : ', "light_blue");
            echo $colors->getColor($sup70Pct . '%', "light_gray");

            echo $br . $br . ' ';
        }

        return $result;
	}
}
