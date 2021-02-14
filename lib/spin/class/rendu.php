<?php
namespace spin;

/**
 * Retourne la vue HTML de premier niveau (formulaire)
 *
 * @author Daniel Gomes
 */
class rendu
{
	/**
	 * Attributs
	 */
	private $_dbh;				// Instance PDO

	private $_xml;				// Rendu XML

	private $_dom;				// Rendu Dom
	private $_domHTML;			// Rendu Dom HTML


	/**
	 * Constructeur
	 */
	public function __construct()
	{
		// Instance PDO
        $this->_dbh = \core\dbSingleton::getInstance();

		// Initialisation du dom
		$this->_dom = new \DOMDocument('1.0', 'utf-8');
		$this->_domHTML = new \DOMDocument('1.0', 'utf-8');
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


	/**
	 * Getters
	 */
	public function getXml() {
		return $this->_xml;
	}


	/**
	 * Rendu HTML principal
	 */
	public function getHTML()
	{
		$html 	= '';

		$xpath	= new \DOMXPath($this->_dom);

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$chp		= $masterspin->parentNode->getAttribute('chp');

		$req		= '//tirage';
		$entries 	= $xpath->query($req);
		$tirage		= $entries->item(0);

		$req 		= '//masterspin/*';
		$entries 	= $xpath->query($req);

		if ($entries->length == 0) {

			$html = '<div id="init_' . $chp . '" class="fa fa-plus-circle add-new-spin"></div>';

		} else {

			// Barre d'outils
			$spinTools = new spinTools($masterspin, $tirage);
			$html = $spinTools->render();

			foreach ($entries as $entry) {
				$html .= $this->getHTML_aux($entry, $chp);
			}
		}

		return $html;
	}


	/**
	 * Rendu HTML
	 * Fonction auxiliaire récursive
	 *
	 * @param unknown 	$dom		// Dom XML
	 * @param string 	$chp		// Nom du champ lié à ce spin
	 */
	public function getHTML_aux($dom, $chp)
	{
		$xpath 	= new \DOMXPath($dom->ownerDocument);

		$html = '';

		////////////////////////////////////////////////////////////////////////////////////////////////////
		// Balises 'spin'
		if ($dom->nodeName == 'spin') {

			$id    = $chp.'__'.$dom->getAttribute('id');
			$node  = $dom->childNodes->item(0);

			$value = self::textSpin($this->getHTML_aux($node, $chp));

			$html .= '<div id="'.$id.'" class="spin_off">';
			$html .= $value;
			$html .= '</div>';
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////
		// Balises 'comb'
		if ($dom->nodeName == 'comb') {

			$req 	= 'spin|text|tag';
			$res 	= $xpath->query($req, $dom);

			foreach ($res as $child) {
				$html .= $this->getHTML_aux($child, $chp);
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////
		// Balises 'text'
		if ($dom->nodeName == 'text') {

			$id = $chp.'__'.$dom->getAttribute('id');

			$html .= '<div id="'.$id.'" class="off">';
			$html .= $dom->nodeValue;
			$html .= '</div>';
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////
		// Balises 'tag'
		if ($dom->nodeName == 'tag') {

			$req 	= 'spin|text|tag';
			$res 	= $xpath->query($req, $dom);

			$id 	= $chp.'__'.$dom->getAttribute('id');
			$close	= $dom->getAttribute('close');			// Balise fermante ou non
			$tag	= $dom->getAttribute('tag');			// Nom de la balise

			// Mise en forme en fonction des balises HTML
			$BR_1 = ''; $BR_2 = ''; $HR   = '';
			$simpleBR = array('br');
			$doubleBR = array('p', 'h1', 'h2', 'h3', 'h4', 'h5');
			if (in_array($tag, $simpleBR)) 	{ $BR_1 = '<br>'; 					}
			if (in_array($tag, $doubleBR)) 	{ $BR_1 = '<br>'; $BR_2 = '<br>'; 	}
			if ($tag == 'hr') 				{ $HR = '<hr>'; 					}

			/*
			// Taille des titres
			switch ($tag)
			{
				case 'h1' : 	$size = "font-size:26px;";	break;
				case 'h2' : 	$size = "font-size:22px;";	break;
				case 'h3' : 	$size = "font-size:18px;";	break;
				case 'h4' : 	$size = "font-size:16px;";	break;
				case 'h5' : 	$size = "font-size:14px;";	break;
				default :		$size = "";
			}

			// Affichage des puces
			switch ($tag)
			{
				case 'ul' :
					$ul 	= '<ul>';
					$ulEnd 	= '</ul>';
					break;
				case 'li' :
					$li 	= '<li>&bull; ';
					$liEnd 	= '</li>';
					$BR_2 	= '<br>';
					break;
				default :
					$ul 	= '';
					$ulEnd 	= '';
					$li 	= '';
					$liEnd 	= '';
			}
			*/

			// gestion des puces
			switch ($tag)
			{
				case 'ul' :
					$BR_1 = '<br>';
					$BR_2 = '<br>';
					break;
				case 'li' :
					$BR_2 = '<br>';
					break;
			}

			if ($close == 1) {

				if ($dom->previousSibling) {
					$html .= $BR_1;
					//$html .= '<span style="' . $size . '">' . $ul . $li;
				}

				$html .= '<div id="'.$id.'" class="tag tag-edit" idunique="'.$id.'" title="' . self::titleTag1($dom) . '">';
				$html .= '<div class="fa fa-caret-left fa-lg tag-b"></div>';
				$html .= '</div>';


				foreach ($res as $child) {
					$html .= $this->getHTML_aux($child, $chp);
				}


				$html .= '<div id="'.$id.'øfin" class="tag" idunique="'.$id.'" title="' . htmlentities('</'.$tag.'>') . '">';
				$html .= '<div class="fa fa-caret-right fa-lg tag-b"></div>';
				$html .= '</div>';

				if ($dom->nextSibling) {
					//$html .= $liEnd . $ulEnd . '</span>';
					$html .= $BR_2;
				}

			} else {

				if ($tag == 'spe') {

					$tagSpe = $dom->getAttribute('var');
					if (! empty($dom->getAttribute('ucfirst')) && $dom->getAttribute('ucfirst') == 1) {

						$tagSpeUcfirst = substr($tagSpe, 0, 1);
						$tagSpeUcfirst = \core\tools::strtoupperSansAccent($tagSpeUcfirst);

						$tagSpe = $tagSpeUcfirst . substr($tagSpe, 1, strlen($tagSpe) - 1);

					}

					$html .= '<div id="'.$id.'" class="tag-spe" idunique="'.$id.'" title="' . self::titleTag1($dom) . '">';
					$html .= $tagSpe;
					$html .= '</div>';

				} else {

					$html .= '<div id="'.$id.'" class="tag tag-edit" idunique="'.$id.'" title="' . self::titleTag1($dom) . '">';
					$html .= '<div class="fa fa-caret-left fa-lg tag-a-1"></div>';
					$html .= $tag;
					$html .= '<div class="fa fa-caret-right fa-lg tag-a-2"></div>';
					$html .= '</div>';
					if ($dom->nextSibling) {
						$html .= $BR_1;
						$html .= $HR;
					}
				}
			}
		}

		return str_replace("<br><br><br>", "<br><br>", $html);
	}


	/**
	 * Permet de gérer les retours chariot et <hr> pour l'affichage des spins (texte bleu)
	 *
	 * @param	string 	$html
	 * @return  string
	 */
	public static function textSpin($html)
	{
		if (empty($html)) {
			return;
		}

		$dom = new \DOMDocument('1.0', "utf-8");

		$dom->loadHTML($html, LIBXML_NOBLANKS);

		$xpath 	= new \DOMXPath($dom);

		$query 	= '//body';
		$entries= $xpath->query($query, $dom);
		$body	= $entries->item(0);

		$query	= '//*';
		$entries= $xpath->query($query, $dom);

		foreach ($entries as $entry) {

			if ($entry->hasAttribute('class') && $entry->getAttribute('class') == 'tag tag-edit') {

				// Suppression des tags
				$entry->parentNode->removeChild($entry);

			} elseif ($entry->nodeName != 'br' && $entry->nodeName != 'hr') {

				// Suppression des balises HTML en conservant leur contenu
				$newNode = $dom->createTextNode($entry->nodeValue);
				$entry->parentNode->insertBefore($newNode, $entry);
				$entry->parentNode->removeChild($entry);
			}
		}

		// Sauvergarde vue HTML
		$html = $dom->saveHTML($body);

		// Suppression de la balise 'body'
		$html = substr($html, 6, strlen($html)-6 );
		$html = substr($html, 0, strlen($html)-7 );
		$html = utf8_decode($html);

		return $html;
	}


	/**
	 * Infobulle des balises HTML ouvrantes
	 */
	public static function titleTag1($dom)
	{
		$title  = '<';

		$title .= $dom->getAttribute('tag');

		$filtre     = array('id', 'tag', 'close', 'n');
		$attributes = array();
		foreach ($dom->attributes as $attr) {
			if (! in_array($attr->nodeName, $filtre)) {
				$attributes[] = $attr->nodeName.'="'.$attr->nodeValue.'"';
			}
		}

		if (count($attributes) > 0 ){
			$title .= ' ';
		}

		$title .= implode(' ', $attributes);
		$title .= '>';

		return str_replace('"', "''", $title);
	}
}
