<?php
namespace form;

/**
 * Gestion des éléments de formulaire
 * Element de type spin
 * Nécessite la package "form" de /vw/framework
 *
 * @author Daniel Gomes
 */
class elemSpin extends element
{
    /**
     * Attributs
     */
    private $_dbh;              // Instance PDO

    private $_xml;              // XML du spin
    private $_spinId;           // Clé unique du spin
    private $_lastModif;        // date et heure de la dernière modification du XML chargé
    private $_statut;           // Statut du spin s'il est calculé

    private $_table;            // Table du spin
    private $_idBDD;            // ID de la ligne contenant le spin

    /**
     * Constructeur
     */
    public function __construct($form, $type, $champ, $label=null, $options=null)
    {
        $type    = null;
        $options = null;

        parent::__construct($form, $type, $champ, $label, $options);

        $this->_load = true;    // On prévient la classe 'form' de la modification de la méthode load
        $this->_save = true;    // On prévient la classe 'form' de la modification de la méthode save

        // Instance PDO
		$this->_dbh = \core\dbSingleton::getInstance();

        // Chargement des JS et CSS pour le spin
        \core\libIncluderList::add_vwSpin();
        
        // Chargement fancybox
        \core\libIncluderList::add_fancybox();

        // Chargement de l'élément
        $this->chpSpin();
    }


    /**
	 * Affichage des champs 'spin'
	 */
	private function chpSpin()
	{
        $champ = $this->_champ;
        $name  = $this->_form->getName() . '_' . $champ;
        $id    = $name . '_id';

        // Mise à jour du rendu HTML dans l'éditeur après la sauvegarde
        $js = "majAfterSave('" . $id . "');";
        $this->_form->setJsAfterSave($js);

        // Conteneur
        $div = $this->_dom->createElement('div');
        $div->setAttribute('class', $this->_champWidth);

        // Conteneur spin HTML
        $conteneurSpin = $this->_dom->createElement('div');
        $conteneurSpin->setAttribute('id', $id);
        $conteneurSpin->setAttribute('name', $name);
        $conteneurSpin->setAttribute('source', 'form');
        $conteneurSpin->setAttribute('class', 'spin no-select');

        // Récupération du spin
        $this->_table = $this->_form->getTable();
        $this->_idBDD = $this->_form->getClePrimaireId();

        $this->_xml = '';

        if (! empty($this->_idBDD)) {
            $table = $this->_table;
            $req   = "SELECT $champ FROM $table WHERE id = :id";

            $sql = $this->_dbh->prepare($req);
            $sql->execute( array( ':id' => $this->_idBDD ));

            $res = $sql->fetch();

            $this->_xml = $res->$champ;
        }

        // Champ du spin vide, on initialise le rendu XML avec la balise 'masterspin'
        // dans le champ du formulaire et dans 'spin_histo'
        if (empty($this->_xml)) {

            $this->_spinId      = sha1(time() . $name);
            $this->_lastModif   = date('Y-m-d H:i:s');

            // Mise en forme du HTML
            $newSpin = $this->_dom->createElement('div');
            $newSpin->setAttribute('id', 'init_' . $this->_champ);
            $newSpin->setAttribute('class', 'fa fa-plus-circle add-new-spin');

            $conteneurSpin->setAttribute('spinid',     $this->_spinId);
            $conteneurSpin->setAttribute('lastmodif',  $this->_lastModif);

            $conteneurSpin->appendChild($newSpin);

            // Sauvegarde en BDD du XML d'init
            $newDom = new \DOMDocument('1.0', 'utf-8');

            $dateTime	= date('Y-m-d H:i:s');

            // Nom de la base de donnée
            $db     = \core\config::getConfig('db');
            $base   = $db['default']['base'];

            $master = $newDom->createElement('master');
            $master->setAttribute('bdd', $base);
            $master->setAttribute('bddtable', $this->_table);
            $master->setAttribute('bddid', $this->_idBDD);
            $master->setAttribute('chp', $this->_champ);

            $masterspin = $newDom->createElement('masterspin');
            $masterspin->setAttribute('spinid', $this->_spinId);
            $masterspin->setAttribute('lastmodif', $this->_lastModif);

            $tirage = $newDom->createElement('tirage');

            $master->appendChild($masterspin);
            $master->appendChild($tirage);
            $newDom->appendChild($master);

            // Sauvegarde dans 'spin_histo'
            $req = "INSERT INTO spin_histo (spinid, action, xml, lastmodif) VALUES (:spinid, :action, :xml, :lastmodif)";
            $sql = $this->_dbh->prepare($req);
            $sql->execute( array(
                                ':spinid'   => $this->_spinId,
                                ':action'   => 'Init',
                                ':xml'      => $newDom->saveXML(),
                                ':lastmodif'=> $this->_lastModif,
            ));

        } else {

            $this->_domSpin = new \DOMDocument('1.0', 'utf-8');
            $this->_domSpin->loadXML($this->_xml, LIBXML_NOBLANKS);

            $xpath	= new \DOMXPath($this->_domSpin);

            $req      = '//masterspin';
            $entries  = $xpath->query($req);
            $entry    = $entries->item(0);

            // Récupération du 'spinId' et de 'lastmodif'
            $this->_spinId      = $entry->getAttribute('spinid');
            $this->_lastModif   = $entry->getAttribute('lastmodif');

            // On vérifie si l'id de la page est présent, dans le cas contraire, on l'ajoute
            $req      = '//master';
            $entries  = $xpath->query($req);
            $master   = $entries->item(0);

            if ( empty($master->getAttribute('bddid')) ) {

                $master->setAttribute('bddid', $this->_idBDD);
                $dateAddId = date('Y-m-d H:i:s');

                // Sauvegarde dans spin_histo
                $req = "INSERT INTO spin_histo (spinid, action, xml, lastmodif) VALUES (:spinid, :action, :xml, :lastmodif)";
                $sql = $this->_dbh->prepare($req);
                $sql->execute( array(
                                    ':spinid'   => $this->_spinId,
                                    ':action'   => 'Save id',
                                    ':xml'      => $this->_domSpin->saveXML(),
                                    ':lastmodif'=> $dateAddId,
                ));

                // Sauvegarde dans la table de la page
                $bddtable = $master->getAttribute('bddtable');
                $bddchamp = $master->getAttribute('chp');

                $req = "UPDATE $bddtable SET $bddchamp = :xml WHERE id = :id";
                $sql = $this->_dbh->prepare($req);
                $sql->execute( array(
                                    ':xml'  => $this->_domSpin->saveXML(),
                                    ':id'   => $this->_idBDD,
                ));
            }


            if ($entry->hasAttribute('statut')) {
                $this->_statut  = $entry->getAttribute('statut');
            }

            // On vérifie si le spin est vide
            $req2     = '//masterspin/*';
    		$entries2 = $xpath->query($req2);

            // Vue XML déjà créée mais vide
            if ($entries2->length == 0) {

                // Mise en forme du HTML
                $newSpin = $this->_dom->createElement('div');
                $newSpin->setAttribute('id', 'init_' . $this->_champ);
                $newSpin->setAttribute('class', 'fa fa-plus-circle add-new-spin');

                $conteneurSpin->setAttribute('spinid',     $this->_spinId);
                $conteneurSpin->setAttribute('lastmodif',  $this->_lastModif);

                $conteneurSpin->appendChild($newSpin);

            // Chargement d'un spin existant
            } else {

                $conteneurSpin->setAttribute('spinid',     $this->_spinId);
                $conteneurSpin->setAttribute('lastmodif',  $this->_lastModif);
                $conteneurSpin->setAttribute('statut',     $this->_statut);

                // Récupération du rendu HTML pour l'éditeur de spin
                $rendu = new \spin\rendu();
                $rendu->hydrate(array('xml'=>$this->_xml));

                $html = $rendu->getHTML();

                // Convertion en dom et insertion
                $conv = new \tpl\addHtml($html);
                $nodes = $conv->getDom()->childNodes->item(0)->childNodes->item(0)->childNodes;

                foreach ($nodes as $child) {
                    $newNode = $this->_dom->importNode($child, true);
                    $conteneurSpin->appendChild($newNode);
                }
            }
        }

        $div->appendChild($conteneurSpin);
        $this->_container->appendChild($div);
    }


    /**
     * On surclasse la méthode load
     */
    public function load($data)
    {
        // On ne charge pas le XML dans le champ caché
        $data[$this->_champ] = '';

        // Suppression des versions dans 'spin_histo' plus récentes et n'ayant pas été sauvegardées
        $req = "DELETE FROM spin_histo WHERE spinid = :spinid AND lastmodif > :lastmodif";
        $sql = $this->_dbh->prepare($req);
        $sql->execute( array( ':spinid'=>$this->_spinId, ':lastmodif'=>$this->_lastModif ));

        // On vérifie si la version de spin_histo est bien la plus récente
        $req = "SELECT lastmodif FROM spin_histo WHERE spinid = :spinid ORDER BY id DESC";
        $sql = $this->_dbh->prepare($req);
        $sql->execute( array( ':spinid'=>$this->_spinId ));

        // Copie de la version du XML la plus récente si elle n'est pas dans la table 'spin_histo'
        $insert = false;

        if ($sql->rowCount() > 0) {
            $res = $sql->fetch();
            if ($this->_lastModif >= $res->lastmodif) {
                $insert = true;
            }
        }

        if (! empty($this->_xml) && ($insert || $sql->rowCount() == 0)) {

            $req = "INSERT INTO spin_histo (spinid, action, xml, lastmodif) VALUES (:spinid, :action, :xml, :lastmodif)";
            $sql = $this->_dbh->prepare($req);
            $sql->execute( array(
                                    ':spinid'   => $this->_spinId,
                                    ':action'   => 'Sauvegarde',
                                    ':xml'      => $this->_xml,
                                    ':lastmodif'=> $this->_lastModif,
                                ));
        }

        return $data;
    }


    /**
     * On surclasse la méthode save
     */
    public function save($data)
    {
        // Récupération de la vue XML la plus récente active
        $req = "SELECT id, xml, action FROM spin_histo WHERE spinid = :spinid AND activ = 1 ORDER BY id DESC";
        $sql = $this->_dbh->prepare($req);
        $sql->execute( array( ':spinid' => $this->_spinId ));
        $res = $sql->fetchAll();

        if (count($res) > 0) {

            // Historiques : Conservation des 10 dernières actions
            $req2 = "DELETE FROM spin_histo WHERE id = :id";
            $sql2 = $this->_dbh->prepare($req2);

            foreach ($res as $k => $v) {
                if ($k > 9) {
                    $sql2->execute( array( ':id' => $v->id ));
                }
            }

            // On ne sauvegarde que s'il y a eu des modifications et si le spin n'est pas déjà calculé
            if ($res[0]->action != 'Sauvegarde') {

                // Minifie la version la plus récente
                $text = new \spin\text();
                $text->hydrate(array('xml'=> $res[0]->xml));
                $result = $text->minifySpin();
                $minifyXML = $result['xml'];

                // Récupération de lastmodif
                $dom = new \DOMDocument('1.0', 'utf-8');
                $dom->loadXML($minifyXML, LIBXML_NOBLANKS);

                $xpath = new \DOMXPath($dom);

                $req		= '//masterspin';
                $entries 	= $xpath->query($req);
                $masterspin	= $entries->item(0);

                $lastmodif  = $masterspin->getAttribute('lastmodif');

                // On empêche la sauvegarde d'un spin calculé qui serait en cours de calcul ou terminé
                $validToSave = true;
                if ($masterspin->hasAttribute('statut') && $masterspin->getAttribute('statut') != 'edit') {
                    $validToSave = false;
                }

                // Sauvegarde de la version minifiée dans spin_histo
                if ($validToSave === true) {

                    $req = "INSERT INTO spin_histo (spinid, action, xml, lastmodif, activ) VALUES (:spinid, :action, :xml, :lasmodif, :activ)";
                    $sql = $this->_dbh->prepare($req);
                    $sql->execute( array(
                        ':spinid'   => $this->_spinId,
                        ':action'   => 'Sauvegarde',
                        ':xml'      => $minifyXML,
                        ':lasmodif' => $lastmodif,
                        ':activ'    => 1,
                    ));

                    // Sauvegarde de la version la plus récente minifiée dans la table du formulaire
                    $data[$this->_champ] = $minifyXML;
                } else {
                    $data[$this->_champ] = $res[0]->xml;
                }

            } else {
                $data[$this->_champ] = $res[0]->xml;
            }
        }

        // Suppression des versions invalidées par un déplacement dans l'historique
        $req = "DELETE FROM spin_histo WHERE spinid = :spinid AND activ = 0";
        $sql = $this->_dbh->prepare($req);
        $sql->execute( array( ':spinid' => $this->_spinId ));

        return $data;
    }
}
