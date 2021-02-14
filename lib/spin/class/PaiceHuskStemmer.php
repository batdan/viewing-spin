<?php
namespace spin;

/**
 * Implements a Paice/Husk Stemmer written in PHP by Alexis Ulrich (http://alx2002.free.fr)
 * Convert function PHP in a new class PHP by Daniel Gomes (and add new methods : supprPonctus, supprStopWord, textPaiceHuskStemmer)
 * This code is in the public domain.
 */
class paiceHuskStem
{
	/**
	 * the rule patterns include all accented forms for a given language
	 */
	private $_rulePattern 			= array();
	private $_paiceHuskStemmerRules = array();		// Règles de suppression
	private $_stopList 				= array();		// Mots vides
	private $_language;
	private $_ponctus 				= array(".", ";", ":", "!", "?", ",", "«", "»", "(", ")", '"', "--");


	/**
	 * Constructeur
	 */
	public function __construct($language)
	{
		$this->_language = $language;

		switch ($this->_language) {
			case 'fr' :
				$this->_rulePattern = "/^([a-zàâèéêëîïôûùç]*)(\*){0,1}(\d)([a-zàâèéêëîïôûùç]*)([.|>])/";

				if (file_exists( __DIR__ . '/../ext/PaiceHuskStemRules_fr.php')) {
					$this->_paiceHuskStemmerRules = include  __DIR__ . '/../ext/PaiceHuskStemRules_fr.php';
				}
				// $this->_stopList = include 'stoplist_fr.inc.php';

				if (file_exists( __DIR__ . '/../ext/stop-words_french_fr.php')) {
					$this->_stopList = include  __DIR__ . '/../ext/stop-words_french_fr.php';
				}

				break;

			case 'en' :
				$this->_rulePattern = "/^([a-z]*)(\*){0,1}(\d)([a-z]*)([.|>])/";

				if (file_exists( __DIR__ . '/../ext/PaiceHuskStemRules_en.php')) {
					$this->_paiceHuskStemmerRules = include  __DIR__ . '/../ext/PaiceHuskStemRules_en.php';
				}

				if (file_exists( __DIR__ . '/../ext/stoplist_en.php')) {
					$this->_stopList = include __DIR__ . '/../ext/stoplist_en.inc.php';
				}
				break;
		}
	}


	/**
	 * Returns the number of the first rule from the rule number $rule_number
	 * that can be applied to the given reversed form
	 * returns -1 if no rule can be applied, ie the stem has been found
	 */
	private function getFirstRule($reversed_form, $rule_number) {

		$nb_rules = count($this->_paiceHuskStemmerRules);

		for ($i=$rule_number; $i<$nb_rules; $i++) {
			// gets the letters from the current rule
			$rule = $this->_paiceHuskStemmerRules[$i];
			$rule = preg_replace($this->_rulePattern, "\\1", $rule);
			if ( strncasecmp($rule, $reversed_form, strlen($rule)) == 0 ) {
				return $i;
			}
		}

		return -1;
	}


	/**
	 * Check the acceptability of a stem for a given language
	 *
	 * @param 	string 		$reversed_stem		the stem to check in reverse form
	 */
	function checkAcceptability($reversed_stem) {

		switch ($this->_language) {

			case 'fr': # French
				if (preg_match("/[aàâeèéêëiîïoôuûùy]$/", $reversed_stem)) {
					// if the form starts with a vowel then at least two letters must remain after stemming (e.g.: "étaient" --> "ét")
					return (strlen($reversed_stem) > 2);

				} else {

					// if the form starts with a consonant then at least two letters must remain after stemming
					if (strlen($reversed_stem) <= 2) {
						return false;
					}

					// and at least one of these must be a vowel or "y"
					return (preg_match("/[aàâeèéêëiîïoôuûùy]/", $reversed_stem));
				}
				break;

			case 'en': # English
				if (preg_match("/[aeiouy]$/", $reversed_stem)) {

					// if the form starts with a vowel then at least two letters must remain after stemming (e.g., "owed"/"owing" --> "ow", but not "ear" --> "e")
					return (strlen($reversed_stem) >= 2);

				} else {

					// if the form starts with a consonant then at least three letters must remain after stemming
					if (strlen($reversed_stem) < 3) {
						return False;
					}

					// and at least one of these must be a vowel or "y" (e.g., "saying" --> "say" and "crying" --> "cry", but not "string" --> "str", "meant" --> "me" or "cement" --> "ce")
					return (preg_match("/[aeiouy]/", $reversed_stem));
				}
				break;

			default:
				die("Error in checkAcceptability function: the language <i>" . $this->_language . "</i> is not supported.");
		}
	}


	/**
	 * The actual Paice/Husk stemmer which returns a stem for the given form
	 *
	 * @param 	string 		$word	the word for which we want the stem
	 * @return  string
	 */
	public function paiceHuskStemmer($word) {

		$intact 		= true;
		$stem_found 	= false;
		$reversed_form 	= strrev($word);
		$rule_number 	= 0;

		// that loop goes through the rules' array until it finds an ending one (ending by '.') or the last one ('end0.')
		while (true) {

			$rule_number = $this->getFirstRule($reversed_form, $rule_number);

			// no other rule can be applied => the stem has been found
			if ($rule_number == -1) {
				break;
			}

			$rule = $this->_paiceHuskStemmerRules[$rule_number];

			preg_match($this->_rulePattern, $rule, $matches);

			if ($matches[2] != '*' || $intact) {

				$reversed_stem = $matches[4] . substr($reversed_form, $matches[3], strlen($reversed_form) - $matches[3]);

				if ($this->checkAcceptability($reversed_stem)) {

					$reversed_form = $reversed_stem;

					if ($matches[5] == '.') {
						break;
					}

				} else {

					// go to another rule
					$rule_number++;
				}

			} else {

				// go to another rule
				$rule_number++;
			}
		}

		return strrev($reversed_form);
	}


	/**
	 * Supprime la ponctuation d'un texte
	 *
	 * @param 	string 		$text
	 * @return 	string
	 */
	public function supprPonctus($text)
	{
		// Découpage des mots, des poncutations et des espaces
		$erasePonctu = array();
		foreach($this->_ponctus as $ponctu) {
			$erasePonctu[] = '';
		}
		$text = str_replace($this->_ponctus, $erasePonctu, $text);

		// Suppression des apostrophes
		$text = str_replace("'", " ", $text);

		return $text;
	}


	/**
	 * Supprime les mots vides et retourne le tableau des mots
	 *
	 * @param 	string 		$text
	 * @return 	array
	 */
	public function supprStopWord($text)
	{
		// Suppression des espaces en trop, et des retours chariots
		$text = str_replace("  ", 		" ", $text);
		$text = str_replace("<br>", 	" ", $text);
		$text = str_replace("<br/>", 	" ", $text);
		$text = str_replace("<br />", 	" ", $text);
		$text = strip_tags($text);
		$text = str_replace(chr(10), 	" ", $text);
		$text = strtolower($text);

		$indexText = explode(' ', $text);

		$wordList = array();
		foreach ($indexText as $word) {
			if (! in_array($word, $wordList, true)  &&  ! in_array($word, $this->_stopList, true)  &&  ! empty($word)) {
				$wordList[] = $word;
			}
		}

		return $wordList;
	}


	/**
	 * Racinise les mots
	 * et retourne un tableau de résultats
	 *
	 * @param 	array 		$worlList
	 * @return 	array
	 */
	public function textPaiceHuskStemmer($worlList)
	{
		$newWordList = array();
		foreach ($worlList as $word) {
			$newWordList[] = $this->paiceHuskStemmer($word);
		}

		return $newWordList;
	}
}
