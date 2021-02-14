<?php
namespace spin;

/**
 * Traite toutes les modifications de l'arbre XML liées aux traitement
 * des variantes (spin)
 *
 * @author Daniel Gomes
 */
class spin
{
	/**
	 * Attributs
	 */
	private $_xml;				// Rendu XML
	private $_dom;				// rendu Dom

	private $_elemid1;			// Elément à modifier ou de départ pour une séléction multiple
	private $_elemid2;			// Sélection multiple - élément de fin

	private $_text;				// Texte


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
	public function setElemid1($elemid1) {
		$this->_elemid1 = $elemid1;
	}
	public function setElemid2($elemid2) {
		$this->_elemid2 = $elemid2;
	}
	public function setText($text) {
		$this->_text = $text;
	}


	/**
	 * Activation d'un spin
	 */
	public function activSpin()
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

		// Un seul bloc de texte à convertir en spin
		if (empty($this->_elemid2)) {

			// Ajout du noeud texte
			$comb->appendChild($entry);

		// Conversion en spin d'une sélection de blocs textes
		} else {

			// Boucle sur les noeuds suivants à fusionner
			while ($entry->getAttribute('id') != $this->_elemid2 && $entry->nextSibling) {

				$nextNode = $entry->nextSibling;		// Sauvegarde noeud suivant
				$comb->appendChild($entry);				// Copie du noeud dans la balise 'comb'
				$entry = $nextNode;						// Rappel noeud suivant
			}

			// Copie texte du dernier noeud
			$comb->appendChild($entry);
		}

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Active spin',
					'other'			=> array(
											'spinid' => $spinid,
					),
		);
	}


	/**
	 * Désactivation d'un spin
	 */
	public function desactivSpin()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		// Récupération du 'spin'
		$req		= '//spin[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$spin		= $entries->item(0);

		// Récupération des noeuds du premier 'comb' et déplacement avant le spin
		$req		= '//comb[@id="'.$spin->childNodes->item(0)->getAttribute('id').'"]/*';
		$entries 	= $xpath->query($req);

		foreach ($entries as $entry) {
			$spin->parentNode->insertBefore($entry, $spin);
		}

		// Suppression du spin
		$spin->parentNode->removeChild($spin);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Désactive spin',
		);
	}


	/**
     * SPIN - Ajout d'une nouvelle variante
     */
	public function addComb()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		// Récupération du 'spin'
		$req		= '//spin[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$spin		= $entries->item(0);

		// Si la variante est vide, on vérfie qu'il n'y en a pas déjà une
		// auquel cas on ne l'enregistre pas
		$countEmpty = 0;
		if (empty($this->_text)) {
			$req	 = '//spin[@id="'.$this->_elemid1.'"]/comb';
			$entries = $xpath->query($req);

			foreach ($entries as $entry) {
				if ($entry->childNodes->length==1 && empty($entry->firstChild->nodeValue)) {
					$countEmpty++;
				}
			}
		}

		if (empty($countEmpty)) {

			$lastId++;
			$newComb = $this->_dom->createElement('comb');
			$newComb->setAttribute('id', $lastId);

			$lastId++;
			$newText = $this->_dom->createElement('text', $this->_text);
			$newText->setAttribute('id', $lastId);

			$spin->appendChild($newComb);
			$newComb->appendChild($newText);

			$masterspin->setAttribute('lastid', $lastId);
		}

		// Split du texte
		$text = new text();
		$text->hydrate( array( 'dom'=>$this->_dom, 'elemid1'=>$lastId));
		$text->splitText(false);

		$this->_dom = $text->getDom();

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Ajout variante',
					'other'			=> array(
											'elemid' => $this->_elemid1,
											'text' 	 => $this->_text,
					),
		);
	}


	/**
     * SPIN - Suppression d'une variante
     */
	public function removeComb()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		// Récupération du 'comb'
		$req		= '//comb[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$comb		= $entries->item(0);

		$comb->parentNode->removeChild($comb);

		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Suppr. variante',
		);
	}


	/**
	 * Suppression d'un spin
	 */
	public function supprSpin()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		// Récupération du 'spin'
		$req		= '//spin[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$spin		= $entries->item(0);

		// Suppression des espaces inutiles
		if ($spin->previousSibling && $spin->nextSibling && $spin->previousSibling->nodeValue==' ' && $spin->nextSibling->nodeValue==' ') {
			$spin->parentNode->removeChild($spin->previousSibling);
		}

		// Suppression du spin
		$spin->parentNode->removeChild($spin);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Suppression spin',
		);
	}


	/**
	 * SPIN - Fermeture d'une modal d'édition de spin
	 * Permet de mettre à jour l'affichage du premier comb
	 */
	public function closeSpinMajFirstComb()
	{
		$xpath	= new \DOMXPath($this->_dom);

		// Récupération du 'comb'
		$req		= '//spin[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$spin		= $entries->item(0);

		$renduComb	= new renduModal();
		$renduComb->hydrate( array('elemid1'=>$this->_elemid1));
		$html = $renduComb->getHTML_aux($spin, 'test');

		$dom = new \DOMDocument('1.0', 'utf-8');
		$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOBLANKS);

		$xpath2 = new \DOMXPath($dom);
		$req		= '//body/div';
		$entries 	= $xpath2->query($req);
		$div 		= $entries->item(0);

		$firstCombTxt = '';
		foreach ($div->childNodes as $child) {
			$firstCombTxt .= $dom->saveHTML($child);
		}

		return array('html' => $firstCombTxt);
	}


	/**
	 * Suppression des spin n'ayant qu'un seul combo
	 */
	public function supprSpinEmpty()
	{
		
	}
}
