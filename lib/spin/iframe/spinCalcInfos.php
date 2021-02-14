<?php
require_once __DIR__ . '/../../../../../../bootstrap.php';

if (empty($_GET['spinid'])) {
    die('spinid ?');
} else {
    $spinid = $_GET['spinid'];
}

// Instance PDO
$dbh = \core\dbSingleton::getInstance();


$req = "SELECT * FROM spin_calc WHERE spinid = :spinid";
$sql = $dbh->prepare($req);
$sql->execute( array( ':spinid'=>$spinid ));

if ($sql->rowCount() > 0) {

    $res = $sql->fetch();

    $nbCycle            = $res->nbCycle;

    $nbMotsMoy          = number_format($res->nbMotsMoy, 2, '.', ' ');

    $simMoyNb           = number_format($res->simMoyNb, 2, '.', ' ');
    $simMoyPct          = number_format($res->simMoyPct, 2, '.', ' ') . '%';

    $meilleursCoupleNB  = number_format($res->meilleursCoupleNB, 2, '.', ' ');
    $meilleursCouplePct = number_format($res->meilleursCouplePct, 2, '.', ' ') . '%';

    $pireCoupleNb       = number_format($res->pireCoupleNb, 2, '.', ' ');
    $pireCouplePct      = number_format($res->pireCouplePct, 2, '.', ' ') . '%';

    // Pourcentages de similarité
    $supPctVal = array(
                        'sup20Pct',
                        'sup30Pct',
                        'sup40Pct',
                        'sup50Pct',
                        'sup60Pct',
                        'sup70Pct',
                      );

    $supPctAff = array();

    foreach ($supPctVal as $k => $v) {

        $supPctAff[$k] = '';
        $valPct = $res->{$v};

        if ($valPct > 0) {

            $affPct = preg_replace("/[^0-9\.]/", '', $v) . '%';

            $supPctAff[$k] = <<<eof
                                <tr>
                                    <td style="text-align:left; font-weight:bold;">Couples > $affPct similarité</td>
                                    <td></td>
                                    <td>$valPct</td>
                                </tr>
eof;
        }
    }

    $supPctAff = implode( chr(10), $supPctAff );

    $process_deb        = $res->process_deb;
    $process_fin        = $res->process_fin;

    $dateTimeFin = '';
    if (! empty($process_fin)) {

        $dateTime    = new \DateTime($process_fin);
        $dateTimeFin = '<i class="fa fa-calendar-o"></i>&nbsp;&nbsp;' . $dateTime->format('d-m-Y') . '&nbsp;&nbsp;&nbsp;&nbsp;';
        $dateTimeFin.= '<i class="fa fa-clock-o"></i> ' . $dateTime->format('H:i');
    }

    // Rendu HTML
    $html = <<<eof
<style type="text/css">
.spin-calc-result-title {
    font-weight: bold;
    margin-bottom: 10px;
    margin-left: 5px;
}
.spin-calc-result-title .spinid {
    color: #c63632;
}
.spin-calc-result-fin {
    font-size: 12px;
    font-style: italic;
    font-weight: normal;
}
.spin-calc-result {
    font-size: 12px;
    width: 100%;
    border-collapse: separate;
    border-spacing: 1px;
}
.spin-calc-result thead tr td {
    text-align: center;
    font-weight: bold;
    background-color: #AFC8C8;
    color: #0A1D1D;
    padding:10px 8px;
}
.spin-calc-result td {
    text-align: right;
    padding:5px 8px;
}
.spin-calc-result tbody tr:nth-child(2n+1) {
    background-color: #ececec;
}
.spin-calc-result tbody tr:nth-child(2n+2) {
    background-color: #ddd;
}
</style>

<div style="padding:20px;">

    <div class="spin-calc-result-title">Spinid : <span class="spinid">$spinid</span></div>

    <table class="spin-calc-result" style="">
        <thead>
            <tr>
                <td style="text-align:left;">$dateTimeFin</td>
                <td style="width:190px;">Nombre</td>
                <td style="width:190px;">Pourcentage</td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td style="text-align:left; font-weight:bold;">Nombre de cycles</td>
                <td>$nbCycle</td>
                <td></td>
            </tr>

            <tr>
                <td style="text-align:left; font-weight:bold;">Nombre de mots moyen</td>
                <td>$nbMotsMoy</td>
                <td></td>
            </tr>

            <tr>
                <td style="text-align:left; font-weight:bold; color:green;">Similarité moyenne</td>
                <td style="color:green;">$simMoyNb</td>
                <td style="font-weight:bold; color:green;">$simMoyPct</td>
            </tr>

            <tr>
                <td style="text-align:left; font-weight:bold; color:#0a81dc;">Meilleurs couple</td>
                <td style="color:#0a81dc;">$meilleursCoupleNB</td>
                <td style="font-weight:bold; color:#0a81dc;">$meilleursCouplePct</td>
            </tr>

            <tr>
                <td style="text-align:left; font-weight:bold; color:#c63632;">Pire couple</td>
                <td style="color:#c63632;">$pireCoupleNb</td>
                <td style="font-weight:bold; color:#c63632;">$pireCouplePct</td>
            </tr>

            $supPctAff

        </tbody>
    </table>

</div>
eof;


echo $html;
}
