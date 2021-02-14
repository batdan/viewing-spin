<?php
require_once __DIR__ . '/../../../../../../bootstrap.php';

if (empty($_GET['spinid'])) {
    die('spinid ?');
}

// Instance PDO
$dbh = \core\dbSingleton::getInstance();

$req = "SELECT xml FROM spin_histo WHERE spinid = :spinid AND activ = 1 ORDER BY id DESC";
$sql = $dbh->prepare($req);
$sql->execute( array( ':spinid'=>$_GET['spinid'] ));
$res = $sql->fetch();
$xml = $res->xml;

// Récupération des informations du spin
$dom = new \DOMDocument('1.0', 'utf-8');
$dom->loadXML($xml, LIBXML_NOBLANKS);

$xpath	= new \DOMXPath($dom);

$req		= '//master';
$entries 	= $xpath->query($req);
$master	    = $entries->item(0);
$chp        = $master->getAttribute('chp');

$req		= '//masterspin';
$entries 	= $xpath->query($req);
$master	    = $entries->item(0);
$lastmodif  = $master->getAttribute('lastmodif');
$statut     = $master->getAttribute('statut');

$edit       = "spinCalculEdit('" . $_GET['spinid'] . "', '" . $lastmodif . "');";
$calculOn   = "spinCaclulOn('" . $_GET['spinid'] . "', '" . $lastmodif . "');";
$calculEnd  = "spinCaclulEnd('" . $_GET['spinid'] . "', '" . $lastmodif . "');";

$btn1 = '<button type="button" class="btn btn-default btn-sm" onclick="$.fancybox.close();">Annuler</button>';

switch ($statut) {

    case 'edit' :
        $btn2 = '<button type="button" class="btn btn-danger btn-sm" onclick="'.$calculOn.'">Envoi au calcul</button>';
        $btn3 = '';
        break;

    case 'calcul_on' :
        $btn2 = '<button type="button" class="btn btn-primary btn-sm" onclick="'.$edit.'">Retour à l\'édition</button>';
        $btn3 = '<button type="button" class="btn btn-success btn-sm" onclick="'.$calculEnd.'">Calcul terminé</button>';
        break;

    case 'calcul_end' :
        $btn2 = '<button type="button" class="btn btn-primary btn-sm" onclick="'.$edit.'">Retour à l\'édition</button>';
        $btn3 = '<button type="button" class="btn btn-danger btn-sm" onclick="'.$calculOn.'">Envoi au calcul</button>';
        break;
}

$html = <<<eof
<style type="text/css">
    .apercu {
        color: #444;
        font-family: Arial;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        padding: 15px;
    }
    .buttons {
        margin-top: 20px;
    }
    .champ {
        color: #C63632;
    }
</style>

<div class="apercu">
    <em>Gestion du spin calculé <span class="champ">$chp</span></em>

    <div class="buttons">
        $btn1
        $btn2
        $btn3
    </div>
</div>
eof;

echo $html;
