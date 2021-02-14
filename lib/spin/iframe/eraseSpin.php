<?php
require_once __DIR__ . '/../../../../../../bootstrap.php';

if (empty($_GET['spinid'])) {
    die('spinid ?');
}

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

$eraseSpin  = "eraseSpin('" . $_GET['spinid'] . "', '" . $lastmodif . "');";
?>
<style type="text/css">
    #apercu {
        color: #444;
        font-family: Arial;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        padding: 15px;
    }
    #buttons {
        margin-top: 20px;
    }
    .champ {
        color: #C63632;
    }
</style>

<div id="apercu">
    <em>Supprimer le contenu du masterspin <span class="champ">"<?php echo $chp; ?>"</span></em>

    <div id="buttons">
        <button type="button" class="btn btn-default btn-sm" onclick="$.fancybox.close();">Annuler</button>
        <button type="button" class="btn btn-danger  btn-sm" onclick="<?php echo $eraseSpin ?>">Supprimer</button>
    </div>
</div>
