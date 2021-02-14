<?php
namespace spin;

/**
 * Traite toutes les requêtes AJAX de l'éditeur de spin
 *
 * 		- Récupère la version XML du spin en BDD
 *  	- Appelle la classe qui traitera l'action en domXML
 *		- Sauvegarde le nouveau rendu XML et retourne la vue HTML en JSON
 *
 * @author Daniel Gomes
 */
class ajax
{
	/**
	 * Attributs
	 */
	private $_dbh;				// Instance PDO

    private $_action;			// Action à traiter
    private $_action2;			// Action 2 (permet de joindre plusieurs actions sur une méthode)
	private $_labelAction;		// Nom de l'action

	private $_source;			// Source de la demande (form | modal | fullscreen)
    private $_spinid;			// ID unique du spin
    private $_lastmodif;		// Dernière date de modification
	private $_statut;			// Statut dans le cas d'un spin calculé (edit | calcul_on | calcul_end)

	private $_elemid1;			// Elément à modifier ou de départ pour une séléction multiple
	private $_elemid2;			// Sélection multiple - élément de fin
	private $_elemSpinId;		// id du spin contenant le ou les éléménts modifiés

	private $_myArray;			// Permet de faire passer un tableau de valeurs

	private $_tag;				// Nom d'une balise HTML (tag)
	private $_close;			// Indique si une balise HTML est fermante
	private $_nodeName;			// Nom d'une balise HTML
	private $_attributes;		// Tableau des attributs et de leurs valeurs d'une balise HTML

	private $_text;				// Texte
	private $_sibling;			// Positionne l'action : avant ou après

	private $_html;				// Rendu HTML
	private $_xml;				// Rendu XML

	private $_other = array();	// autres options pouvant passer en JSON


	/**
	 * Constructeur
	 */
	public function __construct()
	{
        // Instance PDO
        $this->_dbh = \core\dbSingleton::getInstance();
	}


	/**
	 * Hydratation de la classe, exécution de la méthode et retour Ajax
	 *
	 * @param array $donnees
	 */
	public function hydrateInitResult(array $data)
	{
		// PDO : begin transaction
		$this->_dbh->beginTransaction();

		// Setters
		foreach ($data as $k=>$v)
		{
			$method = 'set'.ucfirst($k);

			if (method_exists($this, $method)) {
				$this->$method($v);
			}
		}

		// Appel de la méthode du nom de l'attribut 'action'
		if (method_exists($this, $this->_action)) {
			$this->{$this->_action}();
		}

		// PDO : commit transaction
		$this->_dbh->commit();

		// Récupération du statut dans le cas d'un spin calculé
		$this->checkSpinCalcStatut();

		// Retourne le résultat
		return $this->getResult();
	}


	/**
	 * Setters
	 */
    public function setSource($source) {
        $this->_source = $source;
    }
    public function setAction($action) {
        $this->_action = $action;
    }
    public function setAction2($action2) {
        $this->_action2 = $action2;
    }
    public function setSpinid($spinid) {
        $this->_spinid = $spinid;
    }
    public function setLastmodif($lastmodif) {
        $this->_lastmodif = $lastmodif;
    }
	public function setElemid1($elemid1) {
		$this->_elemid1 = $elemid1;
	}
	public function setElemid2($elemid2) {
		$this->_elemid2 = $elemid2;
	}
	public function setElemSpinId($elemSpinId) {
		$this->_elemSpinId = $elemSpinId;
	}
	public function setMyArray($myArray) {
		$this->_myArray = $myArray;
	}
	public function setTag($tag) {
		$this->_tag = $tag;
	}
	public function setClose($close) {
		$this->_close = $close;
	}
	public function setAttributes($attributes) {
		$this->_attributes = json_decode($attributes);
	}
	public function setText($text) {
		$this->_text = $text;
	}
	public function setSibling($sibling) {
		$this->_sibling = $sibling;
	}


	/**
	 * Getters
	 */
	public function getResult()
	{
		$result = array(
						'html'		=> $this->_html,
						'lastmodif'	=> $this->_lastmodif,
						'spinid'	=> $this->_spinid,
						'statut'	=> $this->_statut,
						'other'		=> $this->_other,
					   );

		return json_encode($result);
	}


	/**
	 * Statut dans le cas d'un spin calculé (edit | calcul_on | calcul_end)
	 */
	private function checkSpinCalcStatut()
	{
		$this->_statut = null;

		if (! empty($this->_xml)) {

			$dom = new \DOMDocument('1.0', 'utf-8');
			$dom->loadXML($this->_xml, LIBXML_NOBLANKS);

			$xpath = new \DOMXPath($dom);

			$req		= '//masterspin';
			$entries 	= $xpath->query($req);
			$masterspin	= $entries->item(0);

			if ($masterspin->hasAttribute('statut')) {
				$this->_statut = $masterspin->getAttribute('statut');
			}
		}
	}


    /**
     * Récupération du xml dans la table 'spin_histo'
     */
	private function getXML()
	{
		$req = "SELECT xml FROM spin_histo WHERE spinid = :spinid AND lastmodif = :lastmodif";
        $sql = $this->_dbh->prepare($req);
        $sql->execute( array( ':spinid'=>$this->_spinid, ':lastmodif'=>$this->_lastmodif ));

		if ($sql->rowCount() > 0) {
            $res = $sql->fetch();
			$this->_xml = $res->xml;
        }
	}


	/**
	 * Sauvegarde des modification dans 'spin_histo'
	 */
	private function saveSpinHisto()
	{
		// On supprime les lignes ayant le champ 'activ' à 0 (suite à un retour d'historique)
		$req = "DELETE FROM spin_histo WHERE spinid = :spinid AND activ = 0";
		$sql = $this->_dbh->prepare($req);
		$sql->execute( array(':spinid'   => $this->_spinid));

		// Mise à jour du potentiel d'un spin
		$this->majPotential();

		$req = "INSERT INTO spin_histo (spinid, action, xml, lastmodif) VALUES (:spinid, :action, :xml, :lastmodif)";
        $sql = $this->_dbh->prepare($req);
		$sql->execute( array(
							':spinid'   => $this->_spinid,
	                        ':action'	=> $this->_labelAction,
	                        ':xml'		=> $this->_xml,
	                        ':lastmodif'=> $this->_lastmodif,
					   ));
	}


	/**
	 * Mise à jour du potentiel d'un spin
	 */
	private function majPotential()
	{
		$process = new process();
		$process->hydrate( array('xml'=>$this->_xml));
		$process->potential();

		$this->_xml =  $process->getXml();
	}


	/**
	 * Récupération du nombre de possibilités d'un masterspin
	 */
	private function getPotential()
	{
		$dom = new \DOMDocument('1.0', 'utf-8');
		$dom->loadXML($this->_xml, LIBXML_NOBLANKS);

		$xpath = new \DOMXPath($dom);

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);

		$potential  = $masterspin->getAttribute('potential');
		if ($potential != 'calcul') {
			$potential	= number_format($potential, 0, ',', ' ');
		}

		return $potential;
	}


	/**
	 * Appel du bon rendu en fonction de la source (form | modal | fullscreen)
	 */
	private function selectRendu()
	{
		switch ($this->_source) {
			case 'form' :
				$this->renduHTML(false);
				break;
			case 'modal' :
				$this->renduModalHTML(false);
				break;
		}
	}


    /**
     * RENDU - Rendu HTML
     */
	private function renduHTML($getXML=true)
	{
		if ($getXML) {
			$this->getXML();
			$this->elemSpinId();
		}

		if (! empty($this->_xml)) {

			$rendu = new rendu();
			$rendu->hydrate( array( 'xml'=>$this->_xml ));

			$this->_html = $rendu->getHTML();
		}
	}


	/**
	 * RENDU - Rendu HTML
	 */
	private function majAfterSave()
	{
		$req = "SELECT xml, lastmodif FROM spin_histo WHERE spinid = :spinid AND activ = 1 ORDER BY id DESC";
		$sql = $this->_dbh->prepare($req);
		$sql->execute( array( ':spinid'=>$this->_spinid ));

		if ($sql->rowCount() > 0) {
			$res = $sql->fetch();
			$this->_xml 		= $res->xml;
			$this->_lastmodif	= $res->lastmodif;

			$this->renduHTML(false);
		}
	}


	/**
	 * RENDU - Rendu modal HTML
	 */
	private function renduModalHTML($getXML=true)
	{
		if ($getXML) {
			$this->getXML();
			$this->elemSpinId();
			$elemid = $this->_elemid1;
		} else {
			// Récupération de l'id du spin (parent du comb de l'élément modifié)
			$elemid = $this->_elemSpinId;
		}

		// echo $elemid.chr(10);

		if (! empty($this->_xml)) {

			$renduModal = new renduModal();
			$renduModal->hydrate( array(
										'xml'		=> $this->_xml,
										'elemid1' 	=> $elemid,
										'spinid' 	=> $this->_spinid,
										'lastmodif'	=> $this->_lastmodif,
									   ));

			$this->_html = $renduModal->getHTML();
		}
	}


	/**
	 * id du spin contenant le ou les éléménts modifiés
	 */
	private function elemSpinId()
	{
		if ($this->_source == 'modal') {

			$this->_dom = new \DOMDocument('1.0', 'utf-8');
			$this->_dom->loadXML($this->_xml, LIBXML_NOBLANKS);

			$xpath		= new \DOMXPath($this->_dom);

			// echo $this->_elemid1 . chr(10);

			$req		= '//*[@id="'.$this->_elemid1.'"]';
			$entries 	= $xpath->query($req);
			$entry		= $entries->item(0);

			$this->_elemSpinId = $this->searchSpinId($entry);
		}
	}


	/**
	 * Recherche du premier parent de type spin (remonte les balises 'tag')
	 */
	private function searchSpinId($node)
	{
		$nodeId = '';

		if ($node->parentNode) {
			if ($node->parentNode->nodeName == 'spin') {
				$nodeId = $node->parentNode->getAttribute('id');
			} else {
				$nodeId = $this->searchSpinId($node->parentNode);
			}
		}

		return $nodeId;
	}


	/**
	 * TEXT - Initialisation d'un nouveau spin
	 */
	private function initSpin()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		$text = new text();
		$text->hydrate( array(
							 'xml' 		=> $this->_xml,
							 'elemid1' 	=> $this->_elemid1,
							 'text'		=> $this->_text,
							 ));
		$result = $text->initSpin();

		$this->_xml 		= $result['xml'];
		$this->_lastmodif	= $result['lastmodif'];
		$this->_labelAction	= $result['labelAction'];

		// Sauvegarde
		$this->saveSpinHisto();

		// Affichage
		$this->selectRendu();
	}


	/**
	 * TEXT - Suppression contenu d'un masterspin
	 */
	private function eraseSpin()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		$text = new text();
		$text->hydrate( array(
							 'xml' 		=> $this->_xml,
							 ));
		$result = $text->eraseSpin();

		$this->_xml 		= $result['xml'];
		$this->_lastmodif	= $result['lastmodif'];
		$this->_labelAction	= $result['labelAction'];

		// Sauvegarde
		$this->saveSpinHisto();

		// Affichage
		$this->selectRendu();
	}


	/**
	 * TEXT - Minifier contenu d'un masterspin
	 */
	private function minifySpin()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		$text = new text();
		$text->hydrate( array(
							 'xml' 		=> $this->_xml,
							 ));
		$result = $text->minifySpin();

		$this->_xml 		= $result['xml'];
		$this->_lastmodif	= $result['lastmodif'];
		$this->_labelAction	= $result['labelAction'];

		// Sauvegarde
		$this->saveSpinHisto();

		// Affichage
		$this->selectRendu();
	}


	/**
	 * TEXT - Scinder tous les champs texte du masterspin
	 */
	private function splitAll()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		$text = new text();
		$text->hydrate( array(
							 'xml' 		=> $this->_xml,
							 ));
		$result = $text->splitAll();

		$this->_xml 		= $result['xml'];
		$this->_lastmodif	= $result['lastmodif'];
		$this->_labelAction	= $result['labelAction'];

		// Sauvegarde
		$this->saveSpinHisto();

		// Affichage
		$this->selectRendu();
	}


	/**
	 * Gestion de l'historique
	 */
	private function historique()
	{
		// Passage à 0 de tous les historiques du spin ayant une date supérieur à celui appelé
		$req = "UPDATE spin_histo SET activ = 0 WHERE spinid = :spinid AND lastmodif > :lastmodif";
		$sql = $this->_dbh->prepare($req);
		$sql->execute( array( ':spinid'=>$this->_spinid, ':lastmodif'=>$this->_lastmodif ));

		// Passage à 1 de tous les historiques du spin ayant une date supérieur à celui appelé
		$req = "UPDATE spin_histo SET activ = 1 WHERE spinid = :spinid AND lastmodif <= :lastmodif";
		$sql = $this->_dbh->prepare($req);
		$sql->execute( array( ':spinid'=>$this->_spinid, ':lastmodif'=>$this->_lastmodif ));

		// Récupération du XML dans spin_histo
		$this->getXML();

		// Affichage
		$this->selectRendu();
	}


    /**
     * TEXT - Edition d'un bloc de texte
     */
    private function editText()
    {
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$text = new text();
			$text->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 'text'		=> $this->_text,
								 ));
			$result = $text->editText();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
    }


    /**
     * TEXT - Ajout d'un bloc de texte
     */
    private function addText()
    {
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$text = new text();
			$text->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 'text'		=> $this->_text,
								 'sibling'	=> $this->_sibling,
								 ));
			$result = $text->addText();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
    }


    /**
     * TEXT - Split d'un bloc de texte
     */
    private function splitText()
    {
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$text = new text();
			$text->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 ));
			$result = $text->splitText();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
    }


    /**
     * TEXT - Fusion de plusieurs blocs de texte
     */
    private function fusionText()
    {
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$text = new text();
			$text->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 'elemid2' 	=> $this->_elemid2,
								 ));
			$result = $text->fusionText();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
    }


	/**
     * TEXT - Recherche de synonymes
     */
    private function synonymes()
    {
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$text = new text();
			$text->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 ));
			$result = $text->synonymes();

			$this->_other 		= $result['other'];

			// Affichage
			$this->selectRendu();
		}
    }


	/**
     * TEXT - Ajout des synonymes sélectionnés
     */
    private function addSynonymes()
    {
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$text = new text();
			$text->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 'myArray' 	=> $this->_myArray,
								 ));
			$result = $text->addSynonymes();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];
			$this->_other 		= $result['other'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
    }


    /**
     * TEXT - Suppression d'un bloc de texte
     */
    private function supprText()
    {
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$text = new text();
			$text->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 ));
			$result = $text->supprText();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
    }


    /**
     * SPIN - Activation d'un spin
     */
    private function activSpin()
    {
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$spin = new spin();
			$spin->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 'elemid2' 	=> $this->_elemid2,
								 ));
			$result = $spin->activSpin();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];
			$this->_other		= $result['other'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
    }


    /**
     * SPIN - Désactivation d'un spin
     */
	private function desactivSpin()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		if (! empty($this->_xml)) {
			$spin = new spin();
			$spin->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 ));
			$result = $spin->desactivSpin();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
	}


	/**
     * SPIN - Ajout d'une nouvelle variante
     */
	private function addComb()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		if (! empty($this->_xml)) {
			$spin = new spin();
			$spin->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 'text'		=> $this->_text,
								 ));
			$result = $spin->addComb();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];
			$this->_other		= $result['other'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Récupération du nombre de possibilités du masterspin
			$this->_other['potential'] = $this->getPotential();

			// Affichage
			$this->selectRendu();
		}
	}


	/**
     * SPIN - Suppression d'une variante
     */
	private function removeComb()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		if (! empty($this->_xml)) {
			$spin = new spin();
			$spin->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 'text'		=> $this->_text,
								 ));
			$result = $spin->removeComb();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Récupération du nombre de possibilités du masterspin
			$this->_other['potential'] = $this->getPotential();

			// Affichage
			$this->selectRendu();
		}
	}


    /**
     * SPIN - Suppression d'un spin
     */
	private function supprSpin()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$spin = new spin();
			$spin->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 ));
			$result = $spin->supprSpin();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
	}


	/**
	 * SPIN - Fermeture d'une modal d'édition de spin
	 * Permet de mettre à jour l'affichage du premier comb
	 */
	private function closeSpinMajFirstComb()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$spin = new spin();
			$spin->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 ));
			$result = $spin->closeSpinMajFirstComb();

			$this->_html 		= $result['html'];
		}
	}


	/**
	 * TAG - Ajout d'une balise HTML (tag)
	 */
	private function addTag()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$tag = new tags();
			$tag->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'tag' 		=> $this->_tag,
								 'close'	=> $this->_close,
								 'elemid1' 	=> $this->_elemid1,
								 'elemid2' 	=> $this->_elemid2,
								 ));
			$result = $tag->addTag();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];
			$this->_other		= $result['other'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
	}


	/**
	 * TAG - Ajout / modification d'une balise HTML (tag)
	 */
	private function tagFree()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$tag = new tags();
			$tag->hydrate( array(
								 'xml' 			=> $this->_xml,
								 'tag' 			=> $this->_tag,
								 'attributes'	=> $this->_attributes,
								 'elemid1' 		=> $this->_elemid1,
								 'elemid2' 		=> $this->_elemid2,
								 ));

			if ($this->_action2 == 'ajout') {
				$result = $tag->tagFree();
			} else {
				$result = $tag->tagFreeMaj();
			}

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];
			$this->_other 		= $result['other'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
	}


	/**
	 * TAG - Ajout / modification d'une balise HTML (tag)
	 */
	private function tagSpeFree()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$tag = new tags();
			$tag->hydrate( array(
								 'xml' 			=> $this->_xml,
								 'tag' 			=> $this->_tag,
								 'sibling'		=> $this->_sibling,
								 'attributes'	=> $this->_attributes,
								 'elemid1' 		=> $this->_elemid1,
								 'elemid2' 		=> $this->_elemid2,
								 ));

			if ($this->_action2 == 'ajout') {
				$result = $tag->tagSpeFree();
			} else {
				$result = $tag->tagFreeMaj();
			}

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];
			$this->_other 		= $result['other'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
	}


	/**
	 * TAG - Edition d'une balise HTML (tag)
	 */
	private function editTag()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		if (! empty($this->_xml)) {
			$tag = new tags();
			$tag->hydrate( array(
								 'xml' 			=> $this->_xml,
								 'elemid1' 		=> $this->_elemid1,
								 ));
			$result = $tag->editTag();

			$this->_xml 		= null;
			$this->_lastmodif	= null;
			$this->_spinid		= null;
			$this->_other 		= $result['other'];
		}
	}


    /**
     * TAG - Suppression d'une balise HTML (tag)
     */
	private function deleteTag()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();
		$this->elemSpinId();

		if (! empty($this->_xml)) {
			$tag = new tags();
			$tag->hydrate( array(
								 'xml' 		=> $this->_xml,
								 'elemid1' 	=> $this->_elemid1,
								 ));
			$result = $tag->deleteTag();

			$this->_xml 		= $result['xml'];
			$this->_lastmodif	= $result['lastmodif'];
			$this->_labelAction	= $result['labelAction'];

			// Sauvegarde
			$this->saveSpinHisto();

			// Affichage
			$this->selectRendu();
		}
	}


	/**
	 * SPIN CALCULE - Retour à l'édition
	 */
	private function spinCalculEdit()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		$calc = new calc();
		$calc->hydrate( array(
							 'xml' 		=> $this->_xml,
							 ));
		$result = $calc->spinCalculEdit();

		$this->_xml 		= $result['xml'];
		$this->_lastmodif	= $result['lastmodif'];
		$this->_labelAction	= $result['labelAction'];

		// Sauvegarde
		$this->saveSpinHisto();

		// Affichage
		$this->selectRendu();
	}


	/**
	 * SPIN CALCULE - Envoi au calcul
	 */
	private function spinCaclulOn()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		$calc = new calc();
		$calc->hydrate( array(
							 'xml' 		=> $this->_xml,
							 ));
		$result = $calc->spinCaclulOn();

		$this->_xml 		= $result['xml'];
		$this->_lastmodif	= $result['lastmodif'];
		$this->_labelAction	= $result['labelAction'];

		// Sauvegarde
		$this->saveSpinHisto();

		// Affichage
		$this->selectRendu();
	}


	/**
	 * SPIN CALCULE - Calcul terminé
	 */
	private function spinCaclulEnd()
	{
		// Récupération du XML dans spin_histo
		$this->getXML();

		$calc = new calc();
		$calc->hydrate( array(
							 'xml' 		=> $this->_xml,
							 ));
		$result = $calc->spinCaclulEnd();

		$this->_xml 		= $result['xml'];
		$this->_lastmodif	= $result['lastmodif'];
		$this->_labelAction	= $result['labelAction'];

		// Sauvegarde
		$this->saveSpinHisto();

		// Affichage
		$this->selectRendu();
	}
}
