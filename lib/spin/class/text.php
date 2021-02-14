<?php
namespace spin;

/**
 * Traite toutes les modifications de l'arbre XML liées aux traitement
 * des textes
 *
 * @author Daniel Gomes
 */
class text
{
	/**
	 * Attributs
	 */
	private $_xml;				// Rendu XML
	private $_dom;				// Rendu Dom

	private $_elemid1;			// Elément à modifier ou de départ pour une séléction multiple
	private $_elemid2;			// Sélection multiple - élément de fin

	private $_text;				// Texte
	private $_sibling;			// Positionne l'action : avant ou après

	private $_myArray;			// Permet de faire passer un tableau de valeurs

	private $_ponctus = array(".", ";", ":", "!", "?", ",", "«", "»", "(", ")", '"', "'");


	/**
	 * Constructeur
	 */
	public function __construct()
	{
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
	public function setElemid1($elemid1) {
		$this->_elemid1 = $elemid1;
	}
	public function setElemid2($elemid2) {
		$this->_elemid2 = $elemid2;
	}
	public function setText($text) {
		$this->_text = $text;
	}
	public function setSibling($sibling) {
		$this->_sibling = $sibling;
	}
	public function setMyArray($myArray) {
		$this->_myArray = $myArray;
	}


	/**
	 * Getters
	 */
	public function getDom() {
		return $this->_dom;
	}


	/**
	 * Initialisation d'un bloc de spin
	 */
	public function initSpin()
	{
		$xpath = new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		// Suppression des espaces en trop, et des retours chariots
		$text = str_replace("  ", 		" ", $this->_text);
		$text = str_replace("<br>", 	" ", $text);
		$text = str_replace("<br/>", 	" ", $text);
		$text = str_replace("<br />", 	" ", $text);
		$text = str_replace(chr(10), 	" ", $text);

		// Découpage des mots, des poncutations et des espaces
		$pipePonctu = array();
		foreach($this->_ponctus as $ponctu) {
			$pipePonctu[] = '|'.$ponctu.'|';
		}

		// Hack pour les ajouts de pipe
		$text = str_replace("|", "ø", $text);

		$text = str_replace(" ", "| |", $text);
		$text = str_replace($this->_ponctus, $pipePonctu, $text);
		$text = str_replace("||", "|", $text);
		$text = trim($text, '|');

		// Création des nouveaux noeuds de type texte
		$lastId = 0;
		foreach (explode("|", $text) as $mot) {

			$lastId++;

			// Hack pour les ajouts de pipe
			$mot = str_replace("ø", "|", $mot);

			$newNode = $this->_dom->createElement('text', $mot);
			$newNode->setAttribute('id', $lastId);

			$masterspin->appendChild($newNode);
		}

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Init spin'
		);
	}


	/**
	 * TEXT - Suppression contenu d'un masterspin
	 */
	public function eraseSpin()
	{
		$xpath = new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$req		= '//masterspin/*';
		$entries 	= $xpath->query($req);

		// Suppression des enfants de masterspin
		foreach ($entries as $entry) {
			$entry->parentNode->removeChild($entry);
		}

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Suppression spin',
		);
	}


	/**
	 * TEXT - Minifier contenu d'un masterspin
	 */
	public function minifySpin()
	{
		$xpath = new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		// Vérification de tous les spins
		$req		= '//spin';
		$entries 	= $xpath->query($req);

		foreach ($entries as $spin) {

			// S'il n'y a qu'une seul comb, le spin n'a lieu d'être
			if ($spin->childNodes->length == 1) {

				// On déplace les noeuds du premier comb avant le spin
				$req		= '//comb[@id="' . $spin->firstChild->getAttribute('id') . '"]/*';
				$entries2 	= $xpath->query($req);

				foreach ($entries2 as $entry2) {
					$spin->parentNode->insertBefore($entry2, $spin);
				}

				// Suppression du noeud spin
				$spin->parentNode->removeChild($spin);
			}
		}

		// Regroupement des noeuds de type 'text' qui se suivent
		$req		= '//text';
		$entries 	= $xpath->query($req);

		foreach ($entries as $entry) {

			while ($entry->nextSibling && $entry->nextSibling->nodeName == 'text') {

				// Concaténation du noeud text encours et du suivant
				$entry->nodeValue = $entry->nodeValue . $entry->nextSibling->nodeValue;

				// Suppression du noeud text suivant
				$entry->nextSibling->parentNode->removeChild($entry->nextSibling);
			}
		}

		// Réindexation et mise à jour du lastid
		$lastId = $this->spinReindex();
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Minifier spin',
		);
	}


	/**
	 * TEXT - Minifier contenu d'un masterspin
	 */
	public function splitAll()
	{
		$xpath = new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$req		= '//text';
		$entries 	= $xpath->query($req);

		foreach ($entries as $entry) {

			$check = 0;
			// Recherche d'espace
			if (strstr($entry->nodeValue, ' ') !== false) {
				$check++;
			}
			// Recherche de caractères ponctués
			foreach ($this->_ponctus as $ponctu) {
				if (strstr($entry->nodeValue, $ponctu) !== false) {
					$check++;
					break;
				}
			}

			// La découpe est possible, on scinde le noeud texte
			if ($check > 0) {
				$this->_elemid1 = $entry->getAttribute('id');
				$this->splitText(false);
			}
		}

		// Réindexation et mise à jour du lastid
		$lastId = $this->spinReindex();
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'splitAll',
		);
	}


	/**
	 * Réindexation de tous les noeuds d'un spin
	 */
	private function spinReindex()
	{
		$xpath = new \DOMXPath($this->_dom);

		// suppression de tous les id
		$req		= '//*[@id]';
		$entries 	= $xpath->query($req);

		foreach ($entries as $entry) {
			$entry->removeAttribute('id');
		}

		// Récupération du noued masterspin avant de lire son contenu
		$req		= '//masterspin/*';
		$entries 	= $xpath->query($req);

		$lastId = 0;
		foreach ($entries as $entry) {
			$this->spinReindex_aux($entry, $lastId);
		}

		return $lastId;
	}


	/**
	 * Réindexation de tous les noeuds d'un spin
	 * Fonction auxiliaire recursive
	 */
	private function spinReindex_aux($dom, &$lastId)
	{
		$xpath 	= new \DOMXPath($dom->ownerDocument);

		$lastId++;
		$dom->setAttribute('id', $lastId);

		$req 		= 'spin|text|comb|tag';
		$entries	= $xpath->query($req, $dom);

		foreach ($entries as $entry) {
			$this->spinReindex_aux($entry, $lastId);
		}
	}


	/**
	 * Edition d'un bloc de texte
	 */
	public function editText()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);
		$masterspin->removeAttribute('potential');

		$req		= '//text[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);
		$entry->nodeValue = $this->_text;

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Edition texte',
		);
	}


	/**
	 * Ajout d'un bloc de texte
	 */
	public function addText()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		$req		= '//*[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);

		// Noeud parent
		$parentNode	= $entry->parentNode;

		// Nouveau noeud
		$lastId++;
		$newNode = $this->_dom->createElement('text', $this->_text);
		$newNode->setAttribute('id', $lastId);

		if ($this->_sibling == 'avant') {

			// Nouveau noeud
			$entry->parentNode->insertBefore($newNode, $entry);

			// Si le dernier caractère n'est pas un espace, on l'ajoute
			if (substr($this->_text, strlen($this->_text - 1), 1) != ' ') {

				// Noeud espace
				$lastId++;
				$spaceNode = $this->_dom->createElement('text', ' ');
				$spaceNode->setAttribute('id', $lastId);

				$entry->parentNode->insertBefore($spaceNode, $entry);
			}

		} else {

			// Si le premier caractère n'est pas un espace, on l'ajoute
			$space = false;
			if (substr($this->_text, 0, 1) != ' ') {
				$space = true;

				// Noeud espace
				$lastId++;
				$spaceNode = $this->_dom->createElement('text', ' ');
				$spaceNode->setAttribute('id', $lastId);
			}

			if ($entry->nextSibling) {

				$nextEntry = $entry->nextSibling;

				// Espace
				if ($space) {
					$nextEntry->parentNode->insertBefore($spaceNode, $nextEntry);
				}

				// Nouveau noeud
				$nextEntry->parentNode->insertBefore($newNode, $nextEntry);

			} else {

				// Espace
				if ($space) {
					$entry->parentNode->appendChild($spaceNode);
				}

				// Nouveau noeud
				$entry->parentNode->appendChild($newNode);
			}
		}

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		// Split du texte ajouté
		$this->_elemid1 = $newNode->getAttribute('id');
		$this->splitText(false);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Ajout texte'
		);
	}


	/**
	 * Split d'un bloc de texte
	 * Découpe des espaces et caractères accentués
	 *
	 * @param 	boolean 	$return 	Retourne le résultat si la méthode est appelée depuis l'éditeur
	 */
	public function splitText($return=true)
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		$req		= '//text[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);
		$text		= $entry->nodeValue;

		// Hack pour les ajouts de pipe
		$text = str_replace("|", "ø", $text);

		$pipePonctu = array();
		foreach($this->_ponctus as $ponctu) {
			$pipePonctu[] = '|'.$ponctu.'|';
		}

		$text		= str_replace(" ", "| |", $text);
		$text		= str_replace($this->_ponctus, $pipePonctu, $text);
		$text		= str_replace("||", "|", $text);
		$text		= trim($text, '|');

		// Noeud parent
		$parentNode	= $entry->parentNode;

		// Emplacement des noeuds à insérer (soit après un noeud, soit à la fin)
		if ($entry->nextSibling) {
			$next = true;
			$nextNode = $entry->nextSibling;
		} else {
			$next = false;
		}

		// Création des nouveaux noeuds texte
		foreach (explode("|", $text) as $mot) {

			$lastId++;

			// Hack pour les ajouts de pipe
			$mot = str_replace("ø", "|", $mot);

			$newNode = $this->_dom->createElement('text', $mot);
			$newNode->setAttribute('id', $lastId);

			if ($next) {
				$nextNode->parentNode->insertBefore($newNode, $nextNode);
			} else {
				$parentNode->appendChild($newNode);
			}
		}

		// Suppression de l'ancien noeud texte
		$entry->parentNode->removeChild($entry);

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		if ($return) {
			return array(
						'xml' 			=> $this->_dom->saveXML(),
						'lastmodif'		=> $dateTime,
						'labelAction'	=> 'Split texte'
			);
		}
	}


	/**
	 * Fusion de plusieurs blocs de texte
	 */
	public function fusionText()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		// Premier noeud sélectionné
		$req		= '//text[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);

		// Création du nouveau noeud
		$lastId++;
		$newNode = $this->_dom->createElement('text');
		$newNode->setAttribute('id', $lastId);

		// Insertion avant le premier noeud
		$entry->parentNode->insertBefore($newNode, $entry);

		// Stockage du texte
		$text = '';

		// Boucle sur les noeuds suivant à fusionner
		while ($entry->getAttribute('id') != $this->_elemid2 && $entry->nextSibling) {

			$nextNode = $entry->nextSibling;			// Noeud suivant
			$text 	 .= $entry->nodeValue;				// Récupération du texte
			$entry->parentNode->removeChild($entry);	// Suppression du noeux

			$entry = $nextNode;
		}

		// Récupération texte et suppression du dernier noeud
		$text .= $entry->nodeValue;
		$entry->parentNode->removeChild($entry);

		// Stockage du texte dans le nouveau noeud
		$newNode->nodeValue = $text;

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Fusion texte',
		);
	}


	/**
	 * Recherche de synonymes
	 */
	public function synonymes()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$req		= '//text[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);

		$synonymes	= new synonymes( $entry->nodeValue );
		$possSyn 	= $synonymes->getSynonymes();

		return array(
					'other'			=> $possSyn,
		);

		return $entry->nodeValue;
	}


	/**
	 * Ajout des synonymes sélectionnés
	 */
	public function addSynonymes()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		// Premier noeud
		$req		= '//text[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);

		// Création et insertion d'un noeud spin
		$lastId++;
		$spinid = $lastId;
		$spin = $this->_dom->createElement('spin');
		$spin->setAttribute('id', $lastId);

		$entry->parentNode->insertBefore($spin, $entry);

		// Création et insertion d'un noeud comb
		$lastId++;
		$comb = $this->_dom->createElement('comb');
		$comb->setAttribute('id', $lastId);

		$spin->appendChild($comb);

		// Ajout du noeud texte
		$comb->appendChild($entry);

		// Ajout des synonymes sélectionnés
		foreach ($this->_myArray as $synonyme) {

			// Création et insertion d'un noeud comb
			$lastId++;
			$comb = $this->_dom->createElement('comb');
			$comb->setAttribute('id', $lastId);

			$spin->appendChild($comb);

			// Création et ajout du synonyme
			$lastId++;
			$nodeSynonyme = $this->_dom->createElement('text', $synonyme);
			$nodeSynonyme->setAttribute('id', $lastId);

			// Ajout du noeud texte contenant le synonyme
			$comb->appendChild($nodeSynonyme);
		}

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Ajout de synonymes',
					'other'			=> array(
											'spinid' => $spinid,
					),
		);
	}


	/**
	 * Suppression d'un bloc de texte
	 */
	public function supprText()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$req		= '//text[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);

		// Suppression des espaces inutiles
		if ($entry->previousSibling && $entry->nextSibling && $entry->previousSibling->nodeValue==' ' && $entry->nextSibling->nodeValue==' ') {
			$entry->parentNode->removeChild($entry->previousSibling);
		}
		if (!$entry->previousSibling && $entry->nextSibling && $entry->nextSibling->nodeValue==' ') {
			$entry->parentNode->removeChild($entry->nextSibling);
		}
		if (!$entry->nextSibling && $entry->previousSibling && $entry->previousSibling->nodeValue==' ') {
			$entry->parentNode->removeChild($entry->previousSibling);
		}

		// Suppression du noeud
		$entry->parentNode->removeChild($entry);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Suppression texte'
		);
	}
}
