<?php
/*
header("Content-Type: application/xml; charset=utf-8");

require_once __DIR__ . '/../../../../../bootstrap.php';

// Instance PDO
$dbh = core\dbSingleton::getInstance();

$req = "SELECT xml FROM spin_histo WHERE spinid = :spinid ORDER BY id DESC LIMIT 0,1";
$sql = $dbh->prepare($req);
$sql->execute( array( ':spinid'=>'1b3b6bedb0d785f22b43b2391e674e658265c5ae' ));
$res = $sql->fetch();

echo $res->xml;
*/
