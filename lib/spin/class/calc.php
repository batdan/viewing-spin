<?php
namespace spin;

/**
 * Gestion des envois au calcul des spins
 *
 * @author Daniel Gomes
 */
class calc
{
	/**
	 * Attributs
	 */
	private $_dbh;				// Instance PDO

	private $_xml;				// Rendu XML
	private $_dom;				// Rendu Dom


	/**
	 * Constructeur
	 */
	public function __construct()
	{
		// Instance PDO
        $this->_dbh = \core\dbSingleton::getInstance();

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


	/**
	 * Getters
	 */
	public function getDom() {
		return $this->_dom;
	}


	/**
	 * Retour à l'édition d'un spin calculé
	 */
	public function spinCalculEdit()
	{
		$xpath = new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);
		$masterspin->setAttribute('statut', 'edit');

		// Suppression des attributs de la balise tirage
		$this->attributsTirage('remove');

		// Suppression des enfants de la balise tirage
		$this->removeChildsTirage();

		// Table de gestion des spins calculés -> retour à l'édition
		$spinid = $masterspin->getAttribute('spinid');
		$this->sendToEdit($spinid);

		// Sauvegarde du spin dans le formulaire
		$this->saveSpinInForm();

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Retour edtion'
		);
	}


	/**
	 * Envoi au calcul d'un spin calculé
	 */
	public function spinCaclulOn()
	{
		$xpath = new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//master';
		$entries 	= $xpath->query($req);
		$master		= $entries->item(0);

		$bddTable	= $master->getAttribute('bddtable');
		$bddChamp	= $master->getAttribute('chp');
		$bddId		= $master->getAttribute('bddid');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);
		$masterspin->setAttribute('statut', 'calcul_on');

		// Création des attributs manquants de la balise tirage
		$spinid = $masterspin->getAttribute('spinid');
		$this->attributsTirage();

		// Envoi dans la table de gestion des spins calculés
		$this->sendToCalc($spinid, $bddTable, $bddChamp, $bddId);

		// Sauvegarde du spin dans le formulaire
		$this->saveSpinInForm();

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Envoi calcul'
		);
	}


	/**
	 * Fin du calcul d'un spin calculé
	 */
	public function spinCaclulEnd()
	{
		$xpath = new \DOMXPath($this->_dom);

		$dateTime	= date('Y-m-d H:i:s');

		$req		= '//masterspin';
		$entries 	= $xpath->query($req);
		$masterspin	= $entries->item(0);
		$masterspin->setAttribute('lastmodif', $dateTime);
		$masterspin->setAttribute('statut', 'calcul_end');

		// Table de gestion des spins calculés -> passage en calcul terminé
		$spinid = $masterspin->getAttribute('spinid');
		$this->sendToCalcEnd($spinid);

		// Sauvegarde du spin dans le formulaire
		$this->saveSpinInForm();

		return array(
					'xml' 			=> $this->_dom->saveXML(),
					'lastmodif'		=> $dateTime,
					'labelAction'	=> 'Calcul terminé'
		);
	}


	/**
	 * Création des attributs manquants de la balise tirage
	 *
	 * @param 		string 		$action 	(init | remove)
	 */
	private function attributsTirage($action = 'init')
	{
		$xpath = new \DOMXPath($this->_dom);

		$req		= '//tirage';
		$entries 	= $xpath->query($req);
		$tirage		= $entries->item(0);

		$tirages = array(
						'nbCycle',
						'lastNbCycle',
						'nbEnvoiCalc',
						'nbMotsMoy',
						'simMoyNb',
						'simMoyPct',
						'meilleursCoupleNB',
						'meilleursCouplePct',
						'pireCoupleNb',
						'pireCouplePct',
						'sup20Pct',
						'sup30Pct',
						'sup40Pct',
						'sup50Pct',
						'sup60Pct',
						'sup70Pct',
						'process_deb',
						'process_fin',
					   );

		// Création et initialisation des attributs
		if ($action == 'init') {
			foreach ($tirages as $attribut) {
				if ($tirage->hasAttribute($attribut) === false) {
					$tirage->setAttribute($attribut, '0');
				}
			}
		}

		// Suppression des attributs
		if ($action == 'remove') {
			foreach ($tirages as $attribut) {
				if ($tirage->hasAttribute($attribut) === true) {
					$tirage->removeAttribute($attribut);
				}
			}
		}
	}


	/**
	 * Suppression des enfants de la balise tirage
	 */
	private function removeChildsTirage()
	{
		$xpath = new \DOMXPath($this->_dom);

		$req		= '//tirage/*';
		$entries 	= $xpath->query($req);

		foreach ($entries as $entry) {
			$entry->parentNode->removeChild($entry);
		}
	}


	/**
	 * Envoi à l'édition
	 *
	 * @param 		string 		$spinid 		Identifiant unique du spin
	 */
	private function sendToEdit($spinid)
	{
		$req = "UPDATE 			spin_calc

				SET 			statut 				= :statut,
								xml 				= :xml,
								nbMotsMoy			= 0,
								simMoyNb 			= 0,
								simMoyPct 			= 0,
								meilleursCoupleNB 	= 0,
								meilleursCouplePct 	= 0,
								pireCoupleNb 		= 0,
								pireCouplePct 		= 0,
								sup20Pct 			= 0,
								sup30Pct 			= 0,
								sup40Pct 			= 0,
								process_deb 		= NULL,
								process_fin 		= NULL

				WHERE 			spinid = :spinid";

		$sql = $this->_dbh->prepare($req);
		$sql->execute( array(
								':statut'	=> 'edit',
								':xml'		=> $this->_dom->saveXML(),
								':spinid'	=> $spinid,
							));
	}


	/**
	 * Envoi au calcul
	 *
	 * @param 		string 		$spinid 		Identifiant unique du spin
	 * @param 		string 		$bddTable 		Nom de la table
	 * @param 		string 		$bddChamp 		Nom du champ
	 * @param 		integer		$bddId 			id dans la table
	 */
	private function sendToCalc($spinid, $bddTable, $bddChamp, $bddId)
	{
		// Récupération de l'id du projet
		if ($bddTable == 'projects_actualites' || $bddTable == 'projects_dossiers' || $bddTable == 'projects_pages') {

			$req = "SELECT id_project FROM $bddTable WHERE id = :id";
			$sql = $this->_dbh->prepare($req);
			$sql->execute( array( ':id'=>$bddId ));

			if ($sql->rowCount() > 0) {

				$res = $sql->fetch();
				$idProject = $res->id_project;

				$req = "SELECT objectif FROM projects WHERE id_project = :id_project";

				$sql = $this->_dbh->prepare($req);
				$sql->execute( array( ':id_project'=>$idProject ));

				if ($sql->rowCount() > 0) {

					$res = $sql->fetch();
					$objectif = $res->objectif;
				}
			}
		}

		if (! isset($objectif)) {
			$objectif = 2000;
		}

		// On vérifie si ce spin a déjà été envoyé au calcul
		$req = "SELECT nbCycle, nbEnvoiCalc FROM spin_calc WHERE spinid = :spinid";
		$sql = $this->_dbh->prepare($req);
		$sql->execute( array( ':spinid'=>$spinid ));

		if ($sql->rowCount() == 0) {

			$req2 = "INSERT INTO spin_calc
					 (xml, statut, objectif, bdd_table, bdd_champ, bdd_id, spinid, nbCycle, lastNbCycle, nbEnvoiCalc)
					 VALUES
					 (:xml, :statut, :objectif, :bdd_table, :bdd_champ, :bdd_id, :spinid, 0, 0, 1)";

			$sql2 = $this->_dbh->prepare($req2);

			$sql2->execute( array(
									':xml'		=> $this->_dom->saveXML(),
									':statut'	=> 'calcul_on',
									':objectif'	=> $objectif,
									':bdd_table'=> $bddTable,
									':bdd_champ'=> $bddChamp,
									':bdd_id'	=> $bddId,
									':spinid'	=> $spinid,
								 ));
		} else {

			$res = $sql->fetch();

			$nbEnvoiCalc = $res->nbEnvoiCalc + 1;
			$lastNbCycle = $res->nbCycle;

			$req2 = "UPDATE 		spin_calc

					 SET 			xml 		= :xml,
					 				statut 		= :statut,
									nbEnvoiCalc = :nbEnvoiCalc,
					 				lastNbCycle = :lastNbCycle,
									process_deb = NULL,
									process_fin = NULL

					 WHERE 			spinid = :spinid";

			$sql2 = $this->_dbh->prepare($req2);
			$sql2->execute( array(
									':xml'		   => $this->_dom->saveXML(),
									':statut'	   => 'calcul_on',
									':lastNbCycle' => $lastNbCycle,
									':nbEnvoiCalc' => $nbEnvoiCalc,
									':spinid'	   => $spinid,
								 ));
		}
	}


	/**
	 * Fin du calcul
	 *
	 * @param 		string 		$spinid 		Identifiant unique du spin
	 */
	private function sendToCalcEnd($spinid)
	{
		$req = "UPDATE 			spin_calc

				SET				statut 		= :statut,
								xml 		= :xml,
								process_fin = process_deb

				WHERE			spinid = :spinid";

		$sql = $this->_dbh->prepare($req);
		$sql->execute( array(
								':statut'	=> 'calcul_end',
								':xml'		=> $this->_dom->saveXML(),
								':spinid'	=> $spinid,
							));
	}


	/**
	 * Sauvegarde du spin dans le formulaire
	 */
	private function saveSpinInForm()
	{
		$xpath = new \DOMXPath($this->_dom);

		$req		= '//master';
		$entries 	= $xpath->query($req);
		$master 	= $entries->item(0);

		$table  	= $master->getAttribute('bddtable');
		$champ 		= $master->getAttribute('chp');
		$id  		= $master->getAttribute('bddid');

		$req = "UPDATE $table SET $champ = :xml WHERE id = :id";
		$sql = $this->_dbh->prepare($req);
		$sql->execute( array( ':xml'=>$this->_dom->saveXML(), ':id'=>$id));
	}
}
