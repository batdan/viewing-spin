<?php
namespace spin;

/**
 * Retourne la vue HTML des variantes (comb) dans les modals
 * d'édition de spin
 *
 * @author Daniel Gomes
 */
class renduModal
{
	/**
	 * Attributs
	 */
    private $_spinid;          // Identifiant unique masterspin
    private $_lastmodif;       // Date de dernière modification

	private $_xml;             // Rendu XML

	private $_dom;             // Rendu Dom
	private $_domHTML;         // Rendu Dom HTML

    private $_elemid1;         // Id du premier élément sélectionné

	/**
	 * Constructeur
	 */
	public function __construct()
	{
		$this->_dom     = new \DOMDocument('1.0', 'utf-8');
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
    public function setSpinid($spinid) {
		$this->_spinid = $spinid;
	}
    public function setLastmodif($lastmodif) {
		$this->_lastmodif = $lastmodif;
	}
	public function setXml($xml) {
		$this->_dom->loadXML($xml, LIBXML_NOBLANKS);
		$this->_xml = $xml;
	}
    public function setElemid1($elemid1) {
		$this->_elemid1 = $elemid1;
	}


	/**
	 * Getters
	 */
	public function getXml() {
		return $this->_xml;
	}


	/**
	 * Rendu HTML modal spin
	 */
	public function getHTML()
	{
		$html 	= '';

		$xpath	= new \DOMXPath($this->_dom);

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin = $entries->item(0);
		$chp		= $masterspin->parentNode->getAttribute('chp');

        // Récupération des 'comb'
        $req		= '//spin[@id="'.$this->_elemid1.'"]/comb';
		$entries 	= $xpath->query($req);

        // Boucles sur les variantes
        $i=0;
        foreach ($entries as $entry) {

            if ($i==0)  { $class_container_comb = 'container_comb_0'; }
            else        { $class_container_comb = 'container_comb';   }

            // Container comb
            $html .= '<div class="'.$class_container_comb.'">';

                // Comb - texte
                $idComb  = 'modal__'.$this->_elemid1 . '--' . $chp.'__'.$entry->getAttribute('id');
                $html   .= '<div id ="'.$idComb.'" class="comb" spinid="'.$this->_spinid.'" lastmodif="'.$this->_lastmodif.'" source="modal">';

                    // Appel fonction récursive
                    $html .= $this->getHTML_aux($entry, $chp);

                $html .= '</div>';

                // Comb - boutons d'action
                $btnSupprComb = false;

                if ($i==0 && $this->deleteFirstCom($entries)) {
                    $btnSupprComb = true;
                }

                if ($i>0 && $entries->length > 1) {
                    $btnSupprComb = true;
                }

                if ($i==0 && $entries->length==1) {
                    $btnSupprComb = false;
                }

                if ($masterspin->hasAttribute('statut') && $masterspin->getAttribute('statut') != 'edit') {
                    $btnSupprComb = false;
                }

                if ($btnSupprComb) {


                    $html .= '<div class="comb_actions">';

                        // Comb - Bouton suppression
                        $html .= '<button type="button" class="btn btn-danger btn-xs delete-comb" deletecombid="'.$entry->getAttribute('id').'" idspin="'.$chp.'__'.$this->_elemid1.'">';
                            $html .= '<i class="fa fa-trash"></i>';
                        $html .= '</button>';

                    $html .= '</div>';
                }

            $html .= '</div>';

            $i++;
		}

		return $html;
	}


    /**
	 * Rendu HTML auxiliaire
	 *
	 * @param unknown $dom
	 */
	public function getHTML_aux($dom, $chp)
	{
		$xpath 	= new \DOMXPath($dom->ownerDocument);

        $html 	= '';

        ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Balises 'spin'
		if ($dom->nodeName == 'spin') {

            $id    = 'modal__' . $this->_elemid1 . '--' . $chp.'__'.$dom->getAttribute('id');
            $node  = $dom->childNodes->item(0);

            $value = rendu::textSpin($this->getHTML_aux($node, $chp));

			$html .= '<div id="' . $id . '" class="spin_off">';
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

            $id = 'modal__' . $this->_elemid1 . '--' . $chp.'__'.$dom->getAttribute('id');

            if ($dom->nodeValue != '') {
                $html .= '<div id="' . $id . '" class="off">';
                $html .= $dom->nodeValue;
			    $html .= '</div>';
            } else {
                $html .= '<div class="null">null</div>';
            }

		}

        ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Balises 'tag'
		if ($dom->nodeName == 'tag') {

			$req 	= 'spin|text|tag';
			$res 	= $xpath->query($req, $dom);

			$id 	= 'modal__' . $this->_elemid1 . '--' . $chp . '__' . $dom->getAttribute('id');
			$close	= $dom->getAttribute('close');			// Balise fermante ou non
			$tag	= $dom->getAttribute('tag');			// Nom de la balise

			// Mise en forme en fonction des balises HTML
			$BR_1 = ''; $BR_2 = ''; $HR   = '';
			$simpleBR = array('br');
			$doubleBR = array('p', 'h1', 'h2', 'h3', 'h4', 'h5');
			if (in_array($tag, $simpleBR)) 	{ $BR_1 = '<br>'; 					}
			if (in_array($tag, $doubleBR)) 	{ $BR_1 = '<br>'; $BR_2 = '<br>'; 	}
			if ($tag == 'hr') 				{ $HR   = '<hr>'; 					}

			if ($close == 1) {

    			if ($dom->previousSibling) {
    				$html .= $BR_1;
    			}
				$html .= '<div id="' . $id . '" class="tag tag-edit" idunique="' . $id . '" title="' . rendu::titleTag1($dom) . '">';
				$html .= '<div class="fa fa-caret-left fa-lg tag-b"></div>';
				$html .= '</div>';

				foreach ($res as $child) {
					$html .= $this->getHTML_aux($child, $chp);
				}

				$html .= '<div id="' . $id . 'øfin" class="tag" idunique="' . $id . '" title="' . htmlentities('</' . $tag . '>') . '">';
				$html .= '<div class="fa fa-caret-right fa-lg tag-b"></div>';
				$html .= '</div>';
                if ($dom->nextSibling) {
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

                    $html .= '<div id="'.$id.'" class="tag-spe" idunique="'.$id.'" title="' . rendu::titleTag1($dom) . '">';
					$html .= $tagSpe;
					$html .= '</div>';

                } else {

    				$html .= '<div id="' . $id . '" class="tag tag-edit" idunique="' . $id . '" title="' . rendu::titleTag1($dom) . '">';
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
     * Empêche de supprimer la première combo s'il n'y en a que deux et que la seconde est 'null'
     *
     * @param   dom         $dom
     * @return  boolean
     */
    private function deleteFirstCom($entries) {

        // Impossible de supprimer le premier comb
        if ($entries->length == 2 && empty($entries->item(1)->nodeValue)) {
            return false;

        // Premier comb supprimable
        } else {
            return true;
        }
    }
}
