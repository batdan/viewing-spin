<?php
require_once __DIR__ . '/../../../../../../bootstrap.php';

if (empty($_GET['spinid'])) {
    die('spinid ?');
}

$dbh = \core\dbSingleton::getInstance();

// Récupération du XML
$req = "SELECT xml FROM spin_histo WHERE spinid = :spinid AND activ = 1 ORDER BY id DESC";
$sql = $dbh->prepare($req);
$sql->execute( array( ':spinid'=>$_GET['spinid'] ));
$res = $sql->fetch();
$xml = $res->xml;

// Réindentation du code
$dom = new DOMDocument();
$dom->loadXML($xml, LIBXML_NOBLANKS);
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$xml = $dom->saveXML();
?>
<html>
    <head>
        <link rel="stylesheet" href="/vendor/vw/framework/libExt/js/syntaxhighlighter/styles/shCoreDefault.css">
    </head>
    <body>
        <pre class="brush: xml;"><?php echo $xml; ?></pre>
        <script src="/vendor/vw/framework/libExt/js/syntaxhighlighter/scripts/shCore.js"></script>
        <script src="/vendor/vw/framework/libExt/js/syntaxhighlighter/scripts/shBrushXml.js"></script>
        <script type="text/javascript">SyntaxHighlighter.all();</script>
    </body>
</html>
