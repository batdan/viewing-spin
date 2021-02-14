<?php
require_once __DIR__ . '/../../../../../../bootstrap.php';

if (empty($_GET['spinid'])) {
    die('spinid ?');
}

use spin\process;

// Instance PDO
$dbh = \core\dbSingleton::getInstance();

// Récupération du XML
$req = "SELECT xml FROM spin_histo WHERE spinid = :spinid AND activ = 1 ORDER BY id DESC";
$sql = $dbh->prepare($req);
$sql->execute( array( ':spinid'=>$_GET['spinid'] ));
$res = $sql->fetch();

// Nouvelle instance de la classe gérant les tirages
$process = new process();
$process->hydrate( array( 'xml'=>$res->xml ));
$potential = $process->getPotential();
?>

<style type="text/css">
    hr {
        color: #ccc;
        background-color: #ccc;
        height: 1px;
        border: 0;
        margin: 20px 0 5px 0;
    }
    .nb {
        color: #777;
        font-style: italic;
        min-width: 25px;
        text-align: right;
    }
    #close {
        height: 28px;
    }
    #btnClose {
        margin: 3px;
    }
    #apercu {
        color: #444;
        font-family: Arial;
        font-size: 14px;
        padding: 15px;
        overflow-y: scroll;
    }
</style>


<div id="close">
    <button id="btnClose" type="button" class="btn btn-primary btn-xs pull-right" onclick="$.fancybox.close();">
        <i class="fa fa-times"></i>
    </button>
</div>

<div id="apercu">
    <?php
        $nbTirages = 40;
        if ($potential != 'calcul' && $potential < $nbTirages) {
            $nbTirages = $potential;
        }

        echo '<hr>';

        for ($i=1; $i<=$nbTirages; $i++) {
            echo '<div class="nb">' . $i . '</div>';
            echo $process->version($i);
            echo '<hr>';
        }
    ?>
</div>

<script type="text/javascript">
    var bodyHeight   = $('body').height();
    var apercuHeight = bodyHeight - 54;
    $('#apercu').css({ height: apercuHeight });
</script>
