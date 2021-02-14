<?php
require_once __DIR__ . '/../../../../../../bootstrap.php';

if (empty($_GET['spinid'])) {
    die('spinid ?');
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=export-spin-" . $_GET['spinid'] . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

use spin\process;

// Instance PDO
$dbh = \core\dbSingleton::getInstance();

// Récupération du XML
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

$bddtable   = $master->getAttribute('bddtable');
$bddid      = $master->getAttribute('bddid');

// Récupération de l'id projet
$req = "SELECT id_project FROM $bddtable WHERE id = :id";
$sql = $dbh->prepare($req);
$sql->execute( array( ':id'=>$bddid ));

if ($sql->rowCount() > 0) {
    $res = $sql->fetch();
    $idProject = $res->id_project;

    // Récupération du scope du projet
    $req = "SELECT objectif FROM projects WHERE id_project = :id_project";
    $sql = $dbh->prepare($req);
    $sql->execute( array( ':id_project'=>$idProject ));

    if ($sql->rowCount() > 0) {
        $res = $sql->fetch();
        $nbTirages  = $res->objectif;

        // Nouvelle instance de la classe gérant les tirages
        $process = new process();
        $process->hydrate( array( 'xml'=>$xml ));
        $potential = $process->getPotential();

        if ($potential != 'calcul' && $potential < $nbTirages) {
            $nbTirages = $potential;
        }

        for ($i=1; $i<=$nbTirages; $i++) {
            echo str_replace( chr(10), '', $process->version($i) ) . chr(10);
        }
    }
}
