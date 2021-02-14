<?php
namespace spin;

/**
 * Retourne la barre d'outils d'un bloc de spin
 *
 * @author Daniel Gomes
 */
class spinTools
{
    /**
	 * Attributs
	 */
    private $_dbh;              // Instance PDO

    private $_dom;              // Rendu Dom HTML

    private $_masterspin;
    private $_tirage;

    private $_html;


	/**
	 * Constructeur
	 */
	public function __construct($masterspin, $tirage)
	{
        // Instance PDO
        $this->_dbh = \core\dbSingleton::getInstance();

        // Initialisation du dom
        $this->_dom = new \DOMDocument('1.0', 'utf-8');
        $this->_dom->loadHTML('<?xml encoding="UTF-8">');

        $this->_masterspin  = $masterspin;
        $this->_tirage      = $tirage;

        $this->create();
	}


    /**
     * Création de la barre de menus
     */
    private function create()
    {
        $secuSpinCalcOff = true;
        if ($this->_masterspin->hasAttribute('statut') && $this->_masterspin->getAttribute('statut') != 'edit') {
            $secuSpinCalcOff = false;
        }

        // Container
		$toolBar = $this->_dom->createElement('div');
		$toolBar->setAttribute('class', 'toolBar');
		$this->_dom->appendChild($toolBar);

		// table button
		$toolBarTable = $this->_dom->createElement('div');
		$toolBarTable->setAttribute('class', 'toolBarTable');
		$toolBar->appendChild($toolBarTable);

        if ($secuSpinCalcOff) {

            ////////////////////////////////////
            // Bouton : Trash
            $trash = $this->_dom->createElement('div');
            $trash->setAttribute('class', 'button fullScreenModal');
            $trash->setAttribute('align', 'center');
            $trash->setAttribute('fancy-width', '500px');
            $trash->setAttribute('fancy-height', '105px');
            $trash->setAttribute('fancy-type', 'ajax');
            $trash->setAttribute('fancy-href', '/vendor/vw/spin/lib/spin/iframe/eraseSpin.php?spinid=' . $this->_masterspin->getAttribute('spinid'));
            $trash->setAttribute('title', 'suppression du spin');
            $trashImg = $this->_dom->createElement('div');
            $trashImg->setAttribute('class', 'fa fa-trash-o');
            $trash->appendChild($trashImg);
            $toolBarTable->appendChild($trash);

            // Sep
            $toolBarTable->appendChild($this->sep());
        }

        ////////////////////////////////////
        // Bouton : Export CSV
        $exportCsv = $this->_dom->createElement('div');
        $exportCsv->setAttribute('class', 'button');
        $exportCsv->setAttribute('align', 'center');
        $exportCsv->setAttribute('title', 'Exporter en CSV | Sauvegarde avant export | pas de séparateur');
        $exportCsv->setAttribute('onclick', "window.open('/vendor/vw/spin/lib/spin/iframe/exportCsv.php?spinid=" . $this->_masterspin->getAttribute('spinid') . "')");
        $exportCsvImg = $this->_dom->createElement('div');
        $exportCsvImg->setAttribute('class', 'fa fa-download');
        $exportCsv->appendChild($exportCsvImg);
        $toolBarTable->appendChild($exportCsv);

        // Sep
        $toolBarTable->appendChild($this->sep());

		////////////////////////////////////
		// Bouton : Code XML
		$vueXML = $this->_dom->createElement('div');
		$vueXML->setAttribute('class', 'button fullScreenModal');
		$vueXML->setAttribute('align', 'center');
		$vueXML->setAttribute('fancy-type', 'iframe');
		$vueXML->setAttribute('fancy-href', '/vendor/vw/spin/lib/spin/iframe/xml.php?spinid=' . $this->_masterspin->getAttribute('spinid'));
		$vueXML->setAttribute('title', 'Vue XML');
		$vueXML_Img = $this->_dom->createElement('div');
		$vueXML_Img->setAttribute('class', 'fa fa-code');
		$vueXML->appendChild($vueXML_Img);
		$toolBarTable->appendChild($vueXML);

		// Sep
        $toolBarTable->appendChild($this->sep());

		////////////////////////////////////
		// Bouton : Infos
		$potential = $this->_masterspin->getAttribute('potential');
		if ($potential != 'calcul') {
			$potential = number_format($potential, 0, ',', ' ');
		}

		$infos = $this->_dom->createElement('div');
		$infos->setAttribute('id', 'potential__' . $this->_masterspin->getAttribute('spinid') );
		$infos->setAttribute('class', 'button');
		$infos->setAttribute('align', 'center');

        if ($secuSpinCalcOff === false) {

            if (! $this->_tirage->hasAttribute('nbCycle')) {

                // Attente des résultats d'un premier tirage
                $infos->setAttribute('title', 'En attente du premier tirage');

            } else {

                $infos->setAttribute('class', 'button fullScreenModal');
                $infos->setAttribute('fancy-width', '600px');
                $infos->setAttribute('fancy-height', '365px');
                $infos->setAttribute('fancy-type', 'ajax');
                $infos->setAttribute('fancy-href', '/vendor/vw/spin/lib/spin/iframe/spinCalcInfos.php?spinid=' . $this->_masterspin->getAttribute('spinid'));

                // Nombre de cycles / Meilleurs résultat
                $req = "SELECT (nbCycle - lastNbCycle) AS cycles FROM spin_calc WHERE spinid = :spinid";
                $sql = $this->_dbh->prepare($req);
                $sql->execute( array( ':spinid'=>$this->_masterspin->getAttribute('spinid') ));

                $cycles = '';
                if ($sql->rowCount() > 0) {
                    $res    = $sql->fetch();
                    $cycles = 'Cycles : ' . $res->cycles . ' | ';
                }

                $infosTitle = $cycles . 'Similarité : ' . $this->_tirage->getAttribute('simMoyPct') . '%';

                $infos->setAttribute('title', $infosTitle);
            }
        } else {
            $infos->setAttribute('title', $potential);
        }

		$infosImg = $this->_dom->createElement('div');
		$infosImg->setAttribute('class', 'fa fa-info-circle');
		$infos->appendChild($infosImg);
		$toolBarTable->appendChild($infos);

		// Sep
        $toolBarTable->appendChild($this->sep());

        if ($secuSpinCalcOff) {

    		////////////////////////////////////
    		// Bouton : Historique prev <
    		$prevAction = $this->historique('prev');

    		$histoP = $this->_dom->createElement('div');
    		$histoP->setAttribute('align', 'center');
    		if ($prevAction) {
    			$histoP->setAttribute('class', 'button');
    			$histoP->setAttribute('title', $prevAction['action']);
    			$histoP->setAttribute('onclick', "historique('" . $prevAction['spinid'] . "', '" . $prevAction['lastmodif'] . "');");
    		} else {
    			$histoP->setAttribute('class', 'button-off');
    			$histoP->setAttribute('title', '');
    		}
    		$histoP_img = $this->_dom->createElement('div');
    		$histoP_img->setAttribute('class', 'fa fa-undo');
    		$histoP->appendChild($histoP_img);
    		$toolBarTable->appendChild($histoP);

    		// Sep
            $toolBarTable->appendChild($this->sep());

    		////////////////////////////////////
    		// Bouton : Historique next >
    		$nextAction = $this->historique('next');

    		$histoN = $this->_dom->createElement('div');
    		$histoN->setAttribute('align', 'center');
    		if ($nextAction) {
    			$histoN->setAttribute('class', 'button');
    			$histoN->setAttribute('title', $nextAction['action']);
    			$histoN->setAttribute('onclick', "historique('" . $nextAction['spinid'] . "', '" . $nextAction['lastmodif'] . "');");
    		} else {
    			$histoN->setAttribute('class', 'button-off');
    			$histoN->setAttribute('title', '');
    		}
    		$histoN_img = $this->_dom->createElement('div');
    		$histoN_img->setAttribute('class', 'fa fa-repeat');
    		$histoN->appendChild($histoN_img);
    		$toolBarTable->appendChild($histoN);

    		// Sep
            $toolBarTable->appendChild($this->sep());

            ////////////////////////////////////
    		// Bouton : refresh du rendu de l'éditeur
            $refresh = $this->_dom->createElement('div');
    		$refresh->setAttribute('align', 'center');
            $refresh->setAttribute('class', 'button');
            $refresh->setAttribute('title', 'Rafraichir la vue de l\'éditeur');
            $refresh->setAttribute('onclick', "refreshRendu('" . $this->_masterspin->getAttribute('spinid') . "', '" . $this->_masterspin->getAttribute('lastmodif') . "');");
            $refresh_img = $this->_dom->createElement('div');
    		$refresh_img->setAttribute('class', 'fa fa-refresh');
    		$refresh->appendChild($refresh_img);
    		$toolBarTable->appendChild($refresh);
        }

		////////////////////////////////////
		// Bouton : Aperçu Tirages
		$apercuTirages = $this->_dom->createElement('div');
		$apercuTirages->setAttribute('class', 'button fullScreenModal');
		$apercuTirages->setAttribute('align', 'center');
		$apercuTirages->setAttribute('fancy-type', 'ajax');
		$apercuTirages->setAttribute('fancy-href', '/vendor/vw/spin/lib/spin/iframe/tirage.php?spinid=' . $this->_masterspin->getAttribute('spinid'));
		$apercuTirages->setAttribute('title', 'Aperçu des tirages');
		$apercuTirages_img = $this->_dom->createElement('div');
		$apercuTirages_img->setAttribute('class', 'fa fa-sitemap');
		$apercuTirages->appendChild($apercuTirages_img);
		$toolBarTable->appendChild($apercuTirages);

		// Sep
        $toolBarTable->appendChild($this->sep());

        if ($secuSpinCalcOff) {

    		////////////////////////////////////
    		// Bouton : nettoyage du spin
    		$cleanSpin = $this->_dom->createElement('div');
    		$cleanSpin->setAttribute('class', 'button fullScreenModal');
    		$cleanSpin->setAttribute('align', 'center');
    		$cleanSpin->setAttribute('fancy-width', '500px');
    		$cleanSpin->setAttribute('fancy-height', '105px');
    		$cleanSpin->setAttribute('fancy-type', 'ajax');
    		$cleanSpin->setAttribute('fancy-href', '/vendor/vw/spin/lib/spin/iframe/minify.php?spinid=' . $this->_masterspin->getAttribute('spinid'));
    		$cleanSpin->setAttribute('title', 'Minifier le masterspin');
    		$cleanSpin_img = $this->_dom->createElement('div');
    		$cleanSpin_img->setAttribute('class', 'fa fa-minus');
    		$cleanSpin->appendChild($cleanSpin_img);
    		$toolBarTable->appendChild($cleanSpin);

    		// Sep
    		$toolBarTable->appendChild($this->sep());

    		////////////////////////////////////
    		// Bouton : Scinder tous les champs text
    		$cleanSpin = $this->_dom->createElement('div');
    		$cleanSpin->setAttribute('class', 'button fullScreenModal');
    		$cleanSpin->setAttribute('align', 'center');
    		$cleanSpin->setAttribute('fancy-width', '500px');
    		$cleanSpin->setAttribute('fancy-height', '105px');
    		$cleanSpin->setAttribute('fancy-type', 'ajax');
    		$cleanSpin->setAttribute('fancy-href', '/vendor/vw/spin/lib/spin/iframe/splitAll.php?spinid=' . $this->_masterspin->getAttribute('spinid'));
    		$cleanSpin->setAttribute('title', 'Scinder tous les champs text');
    		$cleanSpin_img = $this->_dom->createElement('div');
    		$cleanSpin_img->setAttribute('class', 'fa fa-ellipsis-h');
    		$cleanSpin->appendChild($cleanSpin_img);
    		$toolBarTable->appendChild($cleanSpin);

    		// Sep
            $toolBarTable->appendChild($this->sep());
        }

		////////////////////////////////////
		// Envoyer un spin au calcul
		if ($potential == 'calcul') {

			$statut = $this->_masterspin->getAttribute('statut');

			switch ($statut) {
				case 'edit'			: $colorStatut = '#bbbbbb';		$txtStatut = "en cours d'édition";		break;
				case 'calcul_on'	: $colorStatut = '#c63632';		$txtStatut = "en cours de calcul";		break;
				case 'calcul_end'	: $colorStatut = '#00ac00';		$txtStatut = "calculé";					break;
			}

			$calculStatut = $this->_dom->createElement('div');
			$calculStatut->setAttribute('class', 'button fullScreenModal');
			$calculStatut->setAttribute('align', 'center');
			$calculStatut->setAttribute('fancy-width', '500px');
			$calculStatut->setAttribute('fancy-height', '105px');
			$calculStatut->setAttribute('fancy-type', 'ajax');
			$calculStatut->setAttribute('fancy-href', '/vendor/vw/spin/lib/spin/iframe/calculStatut.php?spinid=' . $this->_masterspin->getAttribute('spinid'));
			$calculStatut->setAttribute('title', 'Statut du spin calculé : ' . $txtStatut);
			$calculStatut_img = $this->_dom->createElement('div');
			$calculStatut_img->setAttribute('class', 'fa fa-clone');
			$calculStatut_img->setAttribute('style', "color:$colorStatut;");
			$calculStatut->appendChild($calculStatut_img);
			$toolBarTable->appendChild($calculStatut);

            // Sep
            $sep = $this->_dom->createElement('div');
            $sep->setAttribute('class', 'sep');
            $toolBarTable->appendChild($sep);
		}

		// Sauvergarde vue HTML
		$this->_html = $this->_dom->saveHTML($toolBar);
	}


    /**
     * Rendu HTML
     */
    public function render()
    {
        return $this->_html;
    }


    /**
     * Séparateurs de boutons
     */
    private function sep()
    {
        $sep = $this->_dom->createElement('div');
		$sep->setAttribute('class', 'sep');

        return $sep;
    }


    /**
     * Gestion de l'historique
     *
     * @param 		string		$sibling		historique précédent ou suivant (prev|next)
     * @return
     */
    private function historique($sibling)
    {
        $spinid		= $this->_masterspin->getAttribute('spinid');
        $lastmodif 	= $this->_masterspin->getAttribute('lastmodif');

        $result = null;

        switch ($sibling) {

            case 'prev' :

                $req = "SELECT lastmodif FROM spin_histo WHERE spinid = :spinid AND lastmodif < :lastmodif ORDER BY id DESC";
                $sql = $this->_dbh->prepare($req);
                $sql->execute( array( ':spinid'=>$spinid, ':lastmodif'=>$lastmodif ));
                if ($sql->rowCount() > 0) {
                    $res = $sql->fetch();
                    $result = array('lastmodif'	=> $res->lastmodif,
                                    'spinid'	=> $spinid);

                    $req = "SELECT action FROM spin_histo WHERE spinid = :spinid AND lastmodif = :lastmodif ORDER BY id DESC";
                    $sql = $this->_dbh->prepare($req);
                    $sql->execute( array( ':spinid'=>$spinid, ':lastmodif'=>$lastmodif ));
                    if ($sql->rowCount() > 0) {
                        $res = $sql->fetch();
                        $result['action'] = 'Annul : ' . $res->action;
                    }
                }

                break;

            case 'next' :

                $req = "SELECT action, lastmodif FROM spin_histo WHERE spinid = :spinid AND lastmodif > :lastmodif ORDER BY id ASC";
                $sql = $this->_dbh->prepare($req);
                $sql->execute( array( ':spinid'=>$spinid, ':lastmodif'=>$lastmodif ));
                if ($sql->rowCount() > 0) {
                    $res = $sql->fetch();
                    $result = array('action'	=> 'Retour : ' . $res->action,
                                    'lastmodif'	=> $res->lastmodif,
                                    'spinid'	=> $spinid);
                }

                break;
        }

        return $result;
    }
}
