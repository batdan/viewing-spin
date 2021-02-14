<?php
namespace spin;

/**
 * Traite toutes les modifications de l'arbre XML liées aux traitement
 * des balises HTML (tag)
 *
 * @author Daniel Gomes
 */
class tags
{
	/**
	 * Attributs
	 */
	private $_xml;				// Rendu XML
	private $_dom;				// rendu Dom

	private $_elemid1;			// Elément à modifier ou de départ pour une séléction multiple
	private $_elemid2;			// Sélection multiple - élément de fin

	private $_tag;				// Nom d'une balise HTML (tag)
	private $_close;			// Indique si une balise HTML est fermante
	private $_attributes;		// Tableau des attributs et de leurs valeurs d'une balise HTML
	private $_sibling;			// Positionne l'action : avant ou après

	// Liste des balises HTML auto-fermantes
	private $_autoFermantes	= array('area', 'br', 'hr' , 'img', 'input', 'link', 'meta', 'param', 'spe');


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
	public function setTag($tag) {
		$this->_tag = $tag;
	}
	public function setClose($close) {
		$this->_close = $close;
	}
	public function setAttributes($attributes) {
		$this->_attributes = $attributes;
	}
	public function setSibling($sibling) {
		$this->_sibling = $sibling;
	}


	/**
	 * Ajout des balises classiques (fermante ou non)
	 */
	public function addTag()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		// Premier noeud
		$req		= '//*[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);

		// Création et insertion d'un noeud tag
		$lastId++;

		$tagid = $lastId;
		$tag = $this->_dom->createElement('tag');
		$tag->setAttribute('tag', $this->_tag);
		$tag->setAttribute('close', $this->_close);
		$tag->setAttribute('id', $lastId);

		$entry->parentNode->insertBefore($tag, $entry);

		if ($this->_close == 1) {

			// Un seul bloc de texte à encapsuler dans un tag
			if (empty($this->_elemid2)) {

				// On déplace le noeud dans le tag
				$tag->appendChild($entry);

			// Encapsulation d'une sélection de blocs textes dans un tag
			} else {

				// Boucle sur les noeuds suivant à fusionner
				while ($entry->getAttribute('id') != $this->_elemid2 && $entry->nextSibling) {

					$nextNode = $entry->nextSibling;		// Sauvegarde noeud suivant
					$tag->appendChild($entry);				// Copie du noeud dans la balise 'comb'
					$entry = $nextNode;						// Rappel noeud suivant
				}

				// Copie texte du dernier noeud
				$tag->appendChild($entry);
			}
		}

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Ajout balise',
					'other'			=> array(
											'tagid' => $tagid,
					),
		);
	}


	/**
	 * TAG - Ajout d'une balise HTML libre (tag)
	 */
	public function tagFree()
	{
		// Vérifie si la balise est auto-fermante
		if (in_array($this->_tag, $this->_autoFermantes)) {
			$this->_close = 0;
		} else {
			$this->_close = 1;
		}

		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		// Premier noeud
		$req		= '//*[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);

		$lastId++;

		$tagid = $lastId;
		$tag = $this->_dom->createElement('tag');
		$tag->setAttribute('tag', $this->_tag);
		$tag->setAttribute('close', $this->_close);
		$tag->setAttribute('id', $lastId);
		foreach ($this->_attributes as $k => $v) {
			$tag->setAttribute($k, $v);
		}

		$entry->parentNode->insertBefore($tag, $entry);

		if ($this->_close == 1) {

			// Un seul bloc de texte à encapsuler dans un tag
			if (empty($this->_elemid2)) {

				// On déplace le noeud dans le tag
				$tag->appendChild($entry);

			// Encapsulation d'une sélection de blocs textes dans un tag
			} else {

				// Boucle sur les noeuds suivant à fusionner
				while ($entry->getAttribute('id') != $this->_elemid2 && $entry->nextSibling) {

					$nextNode = $entry->nextSibling;		// Sauvegarde noeud suivant
					$tag->appendChild($entry);				// Copie du noeud dans la balise 'comb'
					$entry = $nextNode;						// Rappel noeud suivant
				}

				// Copie texte du dernier noeud
				$tag->appendChild($entry);
			}
		}

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Ajout balise libre',
					'other'			=> array(
											'tagid' => $tagid,
					),
		);
	}


	/**
	 * TAG - Modification d'une balise HTML libre (tag)
	 */
	public function tagFreeMaj()
	{
		// Vérifie si la balise est auto-fermante
		if (in_array($this->_tag, $this->_autoFermantes)) {
			$this->_close = 0;
		} else {
			$this->_close = 1;
		}

		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		// Premier noeud
		$req		= '//*[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);
		$entryClose = $entry->getAttribute('close');

		// Suppression des anciens attributs
		$attrList = array();
		foreach ($entry->attributes as $attr) {

			if ($attr->nodeName != 'id') {
				$attrList[] = $attr->nodeName;
			}
		}
		foreach($attrList as $attr) {
			$entry->removeAttribute($attr);
		}

		// Ajout des nouveaux attributs
		$entry->setAttribute('tag', $this->_tag);
		$entry->setAttribute('close', $this->_close);
		foreach ($this->_attributes as $k => $v) {
			$entry->setAttribute($k, $v);
		}

		// La balise n'est plus de même type (fermante ou auto-fermante)
		if ($entryClose != $this->_close) {

			// La balise devient fermante, elle doit encapsuler l'élément suivant
			if ($this->_close == 1) {

				if ($entry->nextSibling) {
					$entry->appendChild($entry->nextSibling);
				}

			// La balise devient auto-fermante, elle doit se positionner avant ses anciens enfant
			} else {

				// Récupération des noeud enfant
				$req		= '//*[@id="'.$this->_elemid1.'"]/*';
				$childNodes	= $xpath->query($req);

				// On place tous les noeuds contenu dans la balise avant le noeud suivant
				if ($entry->nextSibling) {

					$nextNode = $entry->nextSibling;

					foreach($childNodes as $node) {
						$entry->parentNode->insertBefore($node, $nextNode);
					}

				// Nous sommes à la fin du texte, on déplace les noeuds enfant de la balise à la fin
				} else {

					foreach($childNodes as $node) {
						$entry->parentNode->appendChild($node);
					}
				}
			}
		}

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Modif balise libre',
					'other'			=> array(
											'tagid' => $this->_elemid1,
					),
		);
	}


	/**
	 * TAG - Edition d'une balise HTML (tag)
	 */
	public function editTag()
	{
		$xpath	= new \DOMXPath($this->_dom);

		// Récupération du 'tag'
		$req		= '//tag[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$tag		= $entries->item(0);

		// Récupération des attributs et de leur valeur
		$filtre     = array('id', 'tag', 'close', 'n');
		$attributes = array();
		foreach ($tag->attributes as $attr) {
			if (! in_array($attr->nodeName, $filtre)) {
				$attributes[] = $attr->nodeName.'="'.$attr->nodeValue.'"';
			}
		}

		if (count($attributes) > 0) {
			$attributes = ' ' . implode(' ', $attributes);
		} else {
			$attributes = '';
		}

		$balise = '<'.$tag->getAttribute('tag').$attributes.'>';

		if ($tag->getAttribute('close') == 1) {
			$balise .= '</'.$tag->getAttribute('tag').'>';
		}

		return array(
					'other'			=> array(
											'balise' => $balise,
					),
		);
	}


	/**
	 * TAG - Suppression d'une balise HTML (tag)
	 */
	public function deleteTag()
	{
		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		// Récupération du 'tag'
		$req		= '//tag[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$tag		= $entries->item(0);

		// Récupération de ses enfants
		$req		= '//tag[@id="'.$this->_elemid1.'"]/*';
		$entries 	= $xpath->query($req);

		foreach ($entries as $entry) {
			$tag->parentNode->insertBefore($entry, $tag);
		}

		// Suppression du spin
		$tag->parentNode->removeChild($tag);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Suppression balise',
		);
	}


	/**
	 * TAG SPE - Ajout d'une variable libre (tag spe)
	 */
	public function tagSpeFree()
	{
		// Vérifie si la balise est auto-fermante
		$this->_close = 0;

		$xpath	= new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);

		$lastId 	= $masterspin->getAttribute('lastid');

		// Noeud de référence
		$req		= '//*[@id="'.$this->_elemid1.'"]';
		$entries 	= $xpath->query($req);
		$entry		= $entries->item(0);

		$lastId++;
		$tagid = $lastId;
		$tag = $this->_dom->createElement('tag');
		$tag->setAttribute('tag', $this->_tag);
		$tag->setAttribute('close', $this->_close);
		$tag->setAttribute('id', $lastId);
		foreach ($this->_attributes as $k => $v) {
			$tag->setAttribute($k, $v);
		}

		if ($this->_sibling == 'avant') {

			// Insertion du noeud
			$entry->parentNode->insertBefore($tag, $entry);

			// Insertion d'un espace
			$lastId++;
			$spaceNode = $this->_dom->createElement('text', ' ');
			$spaceNode->setAttribute('id', $lastId);
			$entry->parentNode->insertBefore($spaceNode, $entry);
		}

		if ($this->_sibling == 'après') {

			if ($entry->nextSibling) {

				$entry = $entry->nextSibling;

				// Insertion d'un espace
				$lastId++;
				$spaceNode = $this->_dom->createElement('text', ' ');
				$spaceNode->setAttribute('id', $lastId);
				$entry->parentNode->insertBefore($spaceNode, $entry);

				// Insertion du noeud
				$entry->parentNode->insertBefore($tag, $entry);

			} else {

				// Insertion d'un espace
				$lastId++;
				$spaceNode = $this->_dom->createElement('text', ' ');
				$spaceNode->setAttribute('id', $lastId);
				$entry->parentNode->appendChild($spaceNode);

				// Insertion du noeud
				$entry->parentNode->appendChild($tag);
			}
		}

		// Mise à jour de l'attribut 'lastid'
		$masterspin->setAttribute('lastid', $lastId);

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Ajout balise libre',
					'other'			=> array(
											'tagid' => $tagid,
					),
		);
	}


	/**
	 * Remplacement des balises spécifiques (géo, blocs html, etc.)
	 *
	 * @param 		string 		$texte 		texte à gérer
	 * @param 		integer 	$rang		rang de la ville
	 */
	public function replaceBalisesSpe($rang)
	{
		$xpath = new \DOMXPath($this->_dom);

    	$query		= '//body';
		$entries	= $xpath->query($query);
    	$body		= $entries->item(0);

		$query		= '//spe';
		$entries	= $xpath->query($query);

		foreach ($entries as $entry) {

			$filtre     = array('var');
			$attributes = array();

			foreach ($entry->attributes as $attr) {
				if (! in_array($attr->nodeName, $filtre)) {
					$attributes[$attr->nodeName] = $attr->nodeValue;
				}
			}

			$var 	= $entry->getAttribute('var');
			$method = 'spe_' . $var;

			if (method_exists($this, $method)) {
				$this->$method($entry, $rang, $attributes);
			} else {
				// \FB::log('La méthode ' . $method . ' n\'existe pas');
			}
		}

		$result = $this->_dom->saveHTML($body);
		$result = substr($result, 6, strlen($result) - 13);
		$result = trim($result);

		return $result;

	}


	/**
	 * Permet d'ajouter des balises spécifiques géographiques dans les attributs des tags
	 */
	public function flatTagSpe($var, $rang)
	{
		// Permière lettre min ou maj
		$opt = array('ucfirst' => 0);
		if (ctype_upper( substr($var, 0, 1) )) {
			$opt['ucfirst'] = 1;
		}

		$tagSpeList = array('ville', 	'dep', 		'reg',
							'a_ville', 	'la_ville', 'de_ville',
							'le_dep', 	'du_dep', 	'dans_le_dep', 'dep_num', 'dans_le_dep_num',
							'la_reg', 	'de_reg', 	'dans_la_reg');

		$lowerVar = strtolower($var);

		if (in_array( $lowerVar, $tagSpeList )) {
			$var = $this->{'spe_' . $lowerVar}(null, $rang, $opt);
		}

		return $var;
	}


	/**
	 * Permet d'intégrer un bloc HTML depuis sa variable unique
	 */
	public function spe_html($dom, $rang, $opt)
	{
		if ($opt['bloc']) {

			// Instance PDO
	        $dbh = \core\dbSingleton::getInstance();

			$req = "SELECT html FROM projects_html WHERE variable = :variable";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':variable'=>$opt['bloc'] ));

			if ($sql->rowCount() > 0) {

				$res  = $sql->fetch();
				$html = $res->html;

				$newDom = new \DOMDocument("1.0", "utf-8");
		    	$newDom->loadHTML($html);

				$xpath = new \DOMXPath($newDom);
		    	$query		= '//body';
				$entries	= $xpath->query($query);
		    	$body		= $entries->item(0);

				if ($entries->length > 0) {
			    	foreach ($body->childNodes as $child) {
			    		$newNode = $this->_dom->importNode($child, true);
						$dom->parentNode->insertBefore($newNode, $dom);
			    	}
				}
			}

			// suppression de la balise 'spe'
			$dom->parentNode->removeChild($dom);
		}
	}


	/**
	 * Balise SPE 'a_ville' et 'A_ville'
	 */
	public function spe_a_ville($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT tncc, nccenr FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res  = $sql->fetch();

			$nccenr = str_replace('-', '~', $res->nccenr);
			$nccenr = \core\tools::supprAccent($nccenr);

			switch ($res->tncc) {
				case 0 : $charniere = 'à ';			break;
				case 1 : $charniere = 'à ';			break;
				case 2 : $charniere = 'au ';		break;
				case 3 : $charniere = 'à la ';		break;
				case 4 : $charniere = 'aux ';		break;
				case 5 : $charniere = 'à l\'';		break;
				case 6 : $charniere = 'aux ';		break;
				case 8 : $charniere = 'à los ';		break;
			}

			if (isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
				$charniere = 'A' . mb_substr($charniere, 1, strlen($charniere) - 1);
			}

			if (! is_null($dom)) {
				// Insertion du texte avant la balise 'spe'
				$texte = $this->_dom->createTextNode($charniere . $nccenr);
				$dom->parentNode->insertBefore($texte, $dom);

				// suppression de la balise 'spe'
				$dom->parentNode->removeChild($dom);
			} else {
				return $charniere . $nccenr;
			}
		}
	}


	/**
	 * Balise SPE 'ville'
	 */
	public function spe_ville($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT nccenr FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res  = $sql->fetch();

			$nccenr = str_replace('-', '~', $res->nccenr);
			$nccenr = \core\tools::supprAccent($nccenr);

			if (! is_null($dom)) {
				// Insertion du texte avant la balise 'spe'
				$texte = $this->_dom->createTextNode($nccenr);
				$dom->parentNode->insertBefore($texte, $dom);

				// suppression de la balise 'spe'
				$dom->parentNode->removeChild($dom);
			} else {
				return $nccenr;
			}
		}
	}


	/**
	 * Balise SPE 'la_ville' et 'La_ville'
	 */
	public function spe_la_ville($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT tncc, nccenr FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res  = $sql->fetch();

			$nccenr = str_replace('-', '~', $res->nccenr);
			$nccenr = \core\tools::supprAccent($nccenr);

			switch ($res->tncc) {
				case 0 : $charniere = '';			break;
				case 1 : $charniere = '';			break;
				case 2 : $charniere = 'le ';		break;
				case 3 : $charniere = 'la ';		break;
				case 4 : $charniere = 'les ';		break;
				case 5 : $charniere = 'l\'';		break;
				case 6 : $charniere = 'les ';		break;
				case 8 : $charniere = 'los ';		break;
			}

			if (strlen($charniere)>0 && isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
				$charniere = 'L' . mb_substr($charniere, 1, strlen($charniere) - 1);
			}

			if (! is_null($dom)) {
				// Insertion du texte avant la balise 'spe'
				$texte = $this->_dom->createTextNode($charniere . $nccenr);
				$dom->parentNode->insertBefore($texte, $dom);

				// suppression de la balise 'spe'
				$dom->parentNode->removeChild($dom);
			} else {
				return $charniere . $nccenr;
			}
		}
	}


	/**
	 * Balise SPE 'de_ville' et 'De_ville'
	 */
	public function spe_de_ville($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT tncc, nccenr FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res  = $sql->fetch();

			$nccenr = str_replace('-', '~', $res->nccenr);
			$nccenr = \core\tools::supprAccent($nccenr);

			switch ($res->tncc) {
				case 0 : $charniere = 'de ';		break;
				case 1 : $charniere = 'd\'';		break;
				case 2 : $charniere = 'du ';		break;
				case 3 : $charniere = 'de la ';		break;
				case 4 : $charniere = 'des ';		break;
				case 5 : $charniere = 'de l\'';		break;
				case 6 : $charniere = 'des ';		break;
				case 8 : $charniere = 'de los ';	break;
			}

			if (isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
				$charniere = 'D' . mb_substr($charniere, 1, strlen($charniere) - 1);
			}

			if (! is_null($dom)) {
				// Insertion du texte avant la balise 'spe'
				$texte = $this->_dom->createTextNode($charniere . $nccenr);
				$dom->parentNode->insertBefore($texte, $dom);

				// suppression de la balise 'spe'
				$dom->parentNode->removeChild($dom);
			} else {
				return $charniere . $nccenr;
			}
		}
	}


	/**
	 * Balise SPE 'dep'
	 */
	public function spe_dep($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_departement FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$dep = $res->cde_departement;

			$req = "SELECT nccenr FROM geo_depts2015 WHERE cde_departement = :cde_departement";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':cde_departement'=>$dep ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();

				$nccenr = str_replace('-', '~', $res->nccenr);
				$nccenr = \core\tools::supprAccent($nccenr);

				if (! is_null($dom)) {
					// Insertion du texte avant la balise 'spe'
					$texte = $this->_dom->createTextNode($nccenr);
					$dom->parentNode->insertBefore($texte, $dom);

					// suppression de la balise 'spe'
					$dom->parentNode->removeChild($dom);
				} else {
					return $nccenr;
				}
			}
		}
	}


	/**
	 * Balise SPE 'le_dep' et 'Le_dep'
	 */
	public function spe_le_dep($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_departement FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$dep = $res->cde_departement;

			$req = "SELECT tncc, nccenr FROM geo_depts2015 WHERE cde_departement = :cde_departement";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':cde_departement'=>$dep ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();

				$nccenr = str_replace('-', '~', $res->nccenr);
				$nccenr = \core\tools::supprAccent($nccenr);

				switch ($res->tncc) {
					case 0 : $charniere = '';		break;
					case 1 : $charniere = '';		break;
					case 2 : $charniere = 'le ';	break;
					case 3 : $charniere = 'la ';	break;
					case 4 : $charniere = 'les ';	break;
					case 5 : $charniere = 'l\'';	break;
				}

				if (strlen($charniere)>0 && isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
					$charniere = 'L' . mb_substr($charniere, 1, strlen($charniere) - 1);
				}

				if (! is_null($dom)) {
					// Insertion du texte avant la balise 'spe'
					$texte = $this->_dom->createTextNode($charniere . $nccenr);
					$dom->parentNode->insertBefore($texte, $dom);

					// suppression de la balise 'spe'
					$dom->parentNode->removeChild($dom);
				} else {
					return $charniere . $nccenr;
				}
			}
		}
	}


	/**
	 * Balise SPE 'du_dep' et 'Du_dep'
	 */
	public function spe_du_dep($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_departement FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$dep = $res->cde_departement;

			$req = "SELECT tncc, nccenr FROM geo_depts2015 WHERE cde_departement = :cde_departement";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':cde_departement'=>$dep ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();

				$nccenr = str_replace('-', '~', $res->nccenr);
				$nccenr = \core\tools::supprAccent($nccenr);

				switch ($res->tncc) {
					case 0 : $charniere = 'de ';	break;
					case 1 : $charniere = 'd\'';	break;
					case 2 : $charniere = 'du ';	break;
					case 3 : $charniere = 'de la ';	break;
					case 4 : $charniere = 'des ';	break;
					case 5 : $charniere = 'de l\'';	break;
				}

				if (isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
					$charniere = 'D' . mb_substr($charniere, 1, strlen($charniere) - 1);
				}

				if (! is_null($dom)) {
					// Insertion du texte avant la balise 'spe'
					$texte = $this->_dom->createTextNode($charniere . $nccenr);
					$dom->parentNode->insertBefore($texte, $dom);

					// suppression de la balise 'spe'
					$dom->parentNode->removeChild($dom);
				} else {
					return $charniere . $nccenr;
				}
			}
		}
	}


	/**
	 * Balise SPE 'dans_le_dep' et 'Dans_le_dep'
	 */
	public function spe_dans_le_dep($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_departement FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$dep = $res->cde_departement;

			$req = "SELECT tncc, nccenr FROM geo_depts2015 WHERE cde_departement = :cde_departement";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':cde_departement'=>$dep ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();

				$nccenr = str_replace('-', '~', $res->nccenr);
				$nccenr = \core\tools::supprAccent($nccenr);

				switch ($res->tncc) {
					case 0 : $charniere = 'dans ';		break;
					case 1 : $charniere = 'dans l\'';	break;
					case 2 : $charniere = 'dans le ';	break;
					case 3 : $charniere = 'dans la ';	break;
					case 4 : $charniere = 'dans les ';	break;
					case 5 : $charniere = 'dans l\'';	break;
				}

				if (isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
					$charniere = 'D' . mb_substr($charniere, 1, strlen($charniere) - 1);
				}

				if (! is_null($dom)) {
					// Insertion du texte avant la balise 'spe'
					$texte = $this->_dom->createTextNode($charniere . $nccenr);
					$dom->parentNode->insertBefore($texte, $dom);

					// suppression de la balise 'spe'
					$dom->parentNode->removeChild($dom);
				} else {
					return $charniere . $nccenr;
				}
			}
		}
	}


	/**
	 * Balise SPE 'dep_num'
	 */
	public function spe_dep_num($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_departement FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$dep = $res->cde_departement;
			$dep = str_pad($dep, 2, "0", STR_PAD_LEFT);

			if (! is_null($dom)) {
				// Insertion du texte avant la balise 'spe'
				$texte = $this->_dom->createTextNode($dep);
				$dom->parentNode->insertBefore($texte, $dom);

				// suppression de la balise 'spe'
				$dom->parentNode->removeChild($dom);
			} else {
				return $dep;
			}
		}
	}


	/**
	 * Balise SPE 'dans_le_dep_num' et 'Dans_le_dep_num'
	 */
	public function spe_dans_le_dep_num($dom=null, $rang, $opt)
	{
		$charniere = 'dans le ';

		if (isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
			$charniere = 'Dans le ';
		}

		$dep = $this->spe_dep_num(null, $rang, $opt);

		if (! is_null($dom)) {
			// Insertion du texte avant la balise 'spe'
			$texte = $this->_dom->createTextNode($charniere . $dep);
			$dom->parentNode->insertBefore($texte, $dom);

			// suppression de la balise 'spe'
			$dom->parentNode->removeChild($dom);
		} else {
			return $charniere . $dep;
		}
	}


	/**
	* Balise SPE 'la_reg' et 'La_reg'
	*/
	public function spe_la_reg($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_region FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$reg = $res->	cde_region;

			$req = "SELECT tncc, nccenr FROM geo_reg2015 WHERE cde_region = :cde_region";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':cde_region'=>$reg ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();

				$nccenr = str_replace('-', '~', $res->nccenr);
				$nccenr = \core\tools::supprAccent($nccenr);

				switch ($res->tncc) {
					case 0 : $charniere = '';		break;
					case 1 : $charniere = 'l\'';	break;
					case 2 : $charniere = 'le ';	break;
					case 3 : $charniere = 'la ';	break;
					case 4 : $charniere = 'les ';	break;
				}

				if (strlen($charniere)>0 && isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
					$charniere = 'L' . mb_substr($charniere, 1, strlen($charniere) - 1);
				}

				if (! is_null($dom)) {
					// Insertion du texte avant la balise 'spe'
					$texte = $this->_dom->createTextNode($charniere . $nccenr);
					$dom->parentNode->insertBefore($texte, $dom);

					// suppression de la balise 'spe'
					$dom->parentNode->removeChild($dom);
				} else {
					return $charniere . $nccenr;
				}
			}
		}
	}


	/**
	* Balise SPE 'reg'
	*/
	public function spe_reg($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_region FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$reg = $res->	cde_region;

			$req = "SELECT nccenr FROM geo_reg2015 WHERE cde_region = :cde_region";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':cde_region'=>$reg ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();

				$nccenr = str_replace('-', '~', $res->nccenr);
				$nccenr = \core\tools::supprAccent($nccenr);

				if (! is_null($dom)) {
					// Insertion du texte avant la balise 'spe'
					$texte = $this->_dom->createTextNode($nccenr);
					$dom->parentNode->insertBefore($texte, $dom);

					// suppression de la balise 'spe'
					$dom->parentNode->removeChild($dom);
				} else {
					return $nccenr;
				}
			}
		}
	}


	/**
	* Balise SPE 'de_reg' et 'De_reg'
	*/
	public function spe_de_reg($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_region FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$reg = $res->	cde_region;

			$req = "SELECT tncc, nccenr FROM geo_reg2015 WHERE cde_region = :cde_region";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':cde_region'=>$reg ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();

				$nccenr = str_replace('-', '~', $res->nccenr);
				$nccenr = \core\tools::supprAccent($nccenr);

				switch ($res->tncc) {
					case 0 : $charniere = 'de ';		break;
					case 1 : $charniere = 'd\'';		break;
					case 2 : $charniere = 'du ';		break;
					case 3 : $charniere = 'de la ';		break;
					case 4 : $charniere = 'des ';		break;
				}

				if (isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
					$charniere = 'D' . mb_substr($charniere, 1, strlen($charniere) - 1);
				}

				if (! is_null($dom)) {
					// Insertion du texte avant la balise 'spe'
					$texte = $this->_dom->createTextNode($charniere . $nccenr);
					$dom->parentNode->insertBefore($texte, $dom);

					// suppression de la balise 'spe'
					$dom->parentNode->removeChild($dom);
				} else {
					return $charniere . $nccenr;
				}
			}
		}
	}


	/**
	* Balise SPE 'dans_la_reg' et 'Dans_la_reg'
	*/
	public function spe_dans_la_reg($dom=null, $rang, $opt)
	{
		// Instance PDO
		$dbh = \core\dbSingleton::getInstance();

		$req = "SELECT cde_region FROM geo_communes WHERE rang = :rang";
		$sql = $dbh->prepare($req);
		$sql->execute( array( ':rang'=>$rang ));

		if ($sql->rowCount() > 0) {

			$res = $sql->fetch();
			$reg = $res->	cde_region;

			$req = "SELECT tncc, nccenr FROM geo_reg2015 WHERE cde_region = :cde_region";
			$sql = $dbh->prepare($req);
			$sql->execute( array( ':cde_region'=>$reg ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();

				$nccenr = str_replace('-', '~', $res->nccenr);
				$nccenr = \core\tools::supprAccent($nccenr);

				switch ($res->tncc) {
					case 0 : $charniere = 'en ';		break;
					case 1 : $charniere = 'en ';		break;
					case 2 : $charniere = 'dans le ';	break;
					case 3 : $charniere = 'en ';		break;
					case 4 : $charniere = 'dans les ';	break;
				}

				if (isset($opt['ucfirst']) && $opt['ucfirst'] == 1) {
					if ($res->tncc < 2) {
						$charniere = 'E' . mb_substr($charniere, 1, strlen($charniere) - 1);
					} else {
						$charniere = 'D' . mb_substr($charniere, 1, strlen($charniere) - 1);
					}
				}

				if (! is_null($dom)) {
					// Insertion du texte avant la balise 'spe'
					$texte = $this->_dom->createTextNode($charniere . $nccenr);
					$dom->parentNode->insertBefore($texte, $dom);

					// suppression de la balise 'spe'
					$dom->parentNode->removeChild($dom);
				} else {
					return $charniere . $nccenr;
				}
			}
		}
	}
}
