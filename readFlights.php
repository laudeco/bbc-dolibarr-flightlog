<?php
/**
 * \file    mypage.php
 * \ingroup mymodule
 * \brief   Example PHP page.
 *
 * read flights
 */

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user, $conf;

dol_include_once('/core/class/dolgraph.class.php');
dol_include_once('/flightLog/class/bbcvols.class.php');
dol_include_once('/flightLog/class/bbctypes.class.php');
dol_include_once('/flightLog/class/GraphicalData.php');
dol_include_once('/flightLog/class/GraphicalType.php');
dol_include_once('/flightLog/class/GraphicalValue.php');
dol_include_once('/flightLog/class/GraphicalValueType.php');
dol_include_once('/flightLog/class/YearGraphicalData.php');

dol_include_once("/flightLog/lib/flightLog.lib.php");

$langs->load("mymodule@flightLog");

// Get parameters
//TODO get all parameters from here
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$myparam = GETPOST('myparam', 'alpha');

$unitPriceMission = $conf->global->BBC_FLIGHT_LOG_UNIT_PRICE_MISSION;

//variables
$WIDTH = DolGraph::getDefaultGraphSizeForStats('width', 768);
$HEIGHT = DolGraph::getDefaultGraphSizeForStats('height');

$year = strftime("%Y", dol_now());
$dir = $conf->expensereport->dir_temp;

$filenamenb = $dir . "/test2-" . $year . ".png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=flightLog&amp;file=' . $fileurlnb;

$graphByTypeAndYear = new DolGraph();
$mesg = $graphByTypeAndYear->isGraphKo();
if (!$mesg) {
    $data = getGraphByTypeAndYearData();

    $graphByTypeAndYear->SetData($data->export());
    $graphByTypeAndYear->SetPrecisionY(0);

    $legend = [];
    $graphByTypeAndYear->type = [];
    foreach (fetchBbcFlightTypes() as $flightType) {

        if (!in_array($flightType->numero, [1, 2, 3, 6])) {
            continue;
        }

        $legend[] = $flightType->nom;
        $graphByTypeAndYear->type[] = "lines";
    }
    $graphByTypeAndYear->SetLegend($legend);
    $graphByTypeAndYear->SetMaxValue($graphByTypeAndYear->GetCeilMaxValue());
    $graphByTypeAndYear->SetWidth($WIDTH + 100);
    $graphByTypeAndYear->SetHeight($HEIGHT);
    $graphByTypeAndYear->SetYLabel($langs->trans("YEAR"));
    $graphByTypeAndYear->SetShading(3);
    $graphByTypeAndYear->SetHorizTickIncrement(1);
    $graphByTypeAndYear->SetPrecisionY(0);

    $graphByTypeAndYear->SetTitle($langs->trans("Par type et par année"));

    $graphByTypeAndYear->draw($filenamenb, $fileurlnb);
}

// Default action
if (empty($action) && empty($id) && empty($ref)) {
    $action = 'create';
}

// Load object if id or ref is provided as parameter
$object = new Bbcvols($db);
if (($id > 0 || !empty($ref)) && $action != 'add') {
    $result = $object->fetch($id, $ref);
    if ($result < 0) {
        dol_print_error($db);
    }
}

/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */

/*
 * VIEW
 *
 * Put here all code to build page
 */

llxHeader('', $langs->trans('Read flights'), '');

$form = new Form($db);

// Put here content of your page
$data = array();
$tmp = array();
$legend = array();

//tableau par pilote
$sql = "SELECT USR.lastname AS nom , USR.firstname AS prenom ,COUNT(`idBBC_vols`) AS nbr,fk_pilot as pilot, TT.numero as type,SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(heureA,heureD)))) AS time";
$sql .= " FROM llx_bbc_vols, llx_user AS USR,llx_bbc_types AS TT ";
$sql .= " WHERE `fk_pilot`= USR.rowid AND fk_type = TT.idType AND YEAR(llx_bbc_vols.date) = " . (GETPOST("year") ? "'" . GETPOST("year") . "'" : 'YEAR(NOW())');
$sql .= " GROUP BY fk_pilot,`fk_type`";

$resql = $db->query($sql);

$sqlYear = "SELECT DISTINCT(YEAR(llx_bbc_vols.date)) as annee FROM llx_bbc_vols ";
$resql_years = $db->query($sqlYear);

$pilotNumberFlight = array();
if ($resql) {

    $num = $db->num_rows($resql_years);
    $i = 0;
    if ($num) {
        print '<div class="tabs">';
        print '<a class="tabTitle"><img src="../theme/eldy/img/object_user.png" border="0" alt="" title=""> Recap / utilisateur </a>'; //title

        while ($i < $num) {
            $obj = $db->fetch_object($resql_years); //vol
            if ($obj->annee) {
                print '<a class="tab" id="' . (GETPOST("year") == $obj->annee || (!GETPOST("year") && $obj->annee == date("Y")) ? 'active' : '') . '" " href="readFlights.php?year=' . $obj->annee . '">' . $obj->annee . '</a>';
            }
            $i++;
        }
        print '</div>';
    }


    print '<div class="tabBar">';
    print '<table class="border" width="100%">';

    print '<tr class="liste_titre">';
    print '<td colspan="2">Nom</td>';
    print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 1 : Sponsor") . '</td>';
    print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 2 : Baptême") . '</td>';
    print '<td class="liste_titre" colspan="2">' . $langs->trans("Organisateur (T1/T2)") . '</td>';
    print '<td class="liste_titre" >'            . $langs->trans("Total bonus") . '</td>';
    print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 3 : Privé") . '</td>';
    print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 4: Meeting") . '</td>';
    print '<td class="liste_titre" colspan="1">' . $langs->trans("Type 5: Chambley") . '</td>';
    print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 6: instruction") . '</td>';
    print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 7: vols < 50 ") . '</td>';
    print '<td class="liste_titre" colspan="1">' . $langs->trans("Facture") . '</td>';
    print '<td class="liste_titre" colspan="1">' . $langs->trans("A payer") . '</td>';
    print '<tr>';

    print '<tr class="liste_titre">';
    print '<td colspan="2" class="liste_titre"></td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> Pts </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> Pts </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> Pts </td>';

    print '<td class="liste_titre"> Bonus gagnés </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> € </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> € </td>';

    print '<td class="liste_titre"> # </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> € </td>';

    print '<td class="liste_titre"> #</td>';
    print '<td class="liste_titre"> €</td>';

    print '<td class="liste_titre"> € </td>';
    print '<td class="liste_titre"> Balance (A payer) €</td>';

    print'</tr>';
    $table = sqlToArray($db, $sql, true, (GETPOST("year") ?: date("Y")));
    foreach ($table as $key => $value) {

        if (!$user->rights->flightLog->vol->detail && $user->id != $value["id"]) {
            continue;
        }

        $totalBonus = $value['1']['count'] * 50 + $value['2']['count'] * 50 + $value['orga']['count'] * 25;
        $totalFacture = $value['3']['count'] * 150 + $value['4']['count'] * 100 + $value['6']['count'] * 50 + $value['7']['count'] * 75;
        $facturable = $totalFacture - $totalBonus;

        $pilotNumberFlight[$value['id']] = array(
            "1" => $value['1']['count'],
            "2" => $value['2']['count'],
            "3" => $value['3']['count'],
            "4" => $value['4']['count'],
            "5" => $value['5']['count'],
            "6" => $value['6']['count'],
            "7" => $value['7']['count'],
        );

        print '<tr>';
        print '<td>' . $key . '</td>';
        print '<td>' . $value['name'] . '</td>';

        print '<td>' . $value['1']['count'] . '</td>';
        print '<td>' . $value['1']['count'] * 50 . '</td>';

        print '<td>' . $value['2']['count'] . '</td>';
        print '<td>' . $value['2']['count'] * 50 . '</td>';

        print '<td>' . $value['orga']['count'] . '</td>';
        print '<td>' . $value['orga']['count'] * 25 . '</td>';

        print '<td><b>' . ($totalBonus) . '</b></td>';

        print '<td>' . $value['3']['count'] . '</td>';
        print '<td>' . price($value['3']['count'] * 150) . '€</td>';

        print '<td>' . $value['4']['count'] . '</td>';
        print '<td>' . price($value['4']['count'] * 100) . '€</td>';

        print '<td>' . $value['5']['count'] . '</td>';

        print '<td>' . $value['6']['count'] . '</td>';
        print '<td>' . price($value['6']['count'] * 50) . '€</td>';

        print '<td>' . $value['7']['count'] . '</td>';
        print '<td>' . price($value['7']['count'] * 75) . '€</td>';

        print '<td>' . price($totalFacture) . '€ </td>';
        print '<td><b>' . price(($facturable < 0 ? 0 : $facturable)) . '€</b></td>';
        print '</tr>';
    }
    print'</table>';


    print '<br/>';
    print '<h3>' . $langs->trans("Remboursement aux pilotes") . '</h3>';

    //table km
    $tauxRemb = isset($conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM) ? $conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM : 0;
    $year = GETPOST("year", 'int');

    $kmByQuartil = bbcKilometersByQuartil($year);

    printBbcKilometersByQuartil($kmByQuartil, $tauxRemb, $unitPriceMission);

    print '</div>';


}
print '<br/>';

print '<div class="tabsAction">';


if (false && $conf->expensereport->enabled && $user->rights->flightLog->vol->financial) {
    print '<a class="butAction" href="generateExpenseNote.php?year=' . (GETPOST("year",
            'int') ?: date("Y")) . '">Générer notes de frais</a>';
}

print '</div>';


?>


    <div class="fichecenter">
        <?php print $graphByTypeAndYear->show(); ?>
    </div>

<?php
llxFooter();
