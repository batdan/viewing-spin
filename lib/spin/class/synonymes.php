<?php
namespace spin;

/**
 * Dictionnaire des synonymes
 *
 * @author Daniel Gomes
 */
class synonymes
{
    private     $_str;              // Mot ou expression
    private     $_dom;				// Rendu Dom

    /**
	 * Constructeur
	 */
	public function __construct($str)
	{
        $this->_str = str_replace("&nbsp;", " ", $str);
        $this->_str = trim($this->_str);

        // Initialisation du dom
		$this->_dom = new \DOMDocument('1.0', 'utf-8');

        // Url du site de synonymes
        $urlSyn = "http://www.crisco.unicaen.fr/des/synonymes/" . strtolower($this->_str);

        $this->_dom->loadHTMLFile($urlSyn, LIBXML_NOBLANKS);
	}


    public function getSynonymes()
    {
        $xpath = new \DOMXPath($this->_dom);

		$req		= '//table/tr/td/a';
		$entries 	= $xpath->query($req);

        $synonymes  = array();

        foreach ($entries as $entry) {

            $mot = str_replace('&nbsp;', '', $entry->nodeValue);
            $mot = trim($mot);
            $mot = substr($mot, 2, strlen($mot) - 4);
            $mot = rtrim($mot, '*');

            // Si le premier carctère est en majuscule, on retourne tous les synonymes avec la première lettre en majuscule
            if (ctype_upper(substr($this->_str, 0, 1))) {
                $mot = mb_strtoupper(mb_substr($mot, 0, 1)) . mb_strtolower(mb_substr($mot, 1));
            }

            $synonymes[] = $mot;
        }

        return $synonymes;
    }
}
