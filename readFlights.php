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
dol_include_once("/flightlog/flightlog.inc.php");

use flightlog\query\GetPilotsWithMissionsQuery;
use flightlog\query\GetPilotsWithMissionsQueryHandler;

$langs->load("mymodule@flightlog");

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
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=flightlog&amp;file=' . $fileurlnb;

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
$sqlYear = "SELECT DISTINCT(YEAR(llx_bbc_vols.date)) as annee FROM llx_bbc_vols ";
$resql_years = $db->query($sqlYear);

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
print '<table class="" width="100%">';

print '<tbody>';
print '<tr class="liste_titre">';
print '<td colspan="2">Nom</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Type 1 : <br/>Sponsor") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Type 2 : <br/>Baptême") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Orga. <br/>(T1/T2)") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Instructeur <br/>(orga T6)") . '</td>';
print '<td class="liste_titre _alignCenter" >' . $langs->trans("Total bonus") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Type 3 : <br/>Privé") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Type 4: <br/>Meeting") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="1">' . $langs->trans("Type 5: <br/>Chambley") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Type 6: <br/>instruction") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Type 7: <br/>vols < 50 ") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Réparations") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="1">' . $langs->trans("Facture") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="1">' . $langs->trans("A payer") . '</td>';
print '<tr>';

print '<tr class="liste_titre">';
print '<td colspan="2" class="liste_titre"></td>';

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> Pts </td>';

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> Pts </td>';

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> Pts </td>';

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> Pts </td>';

print '<td class="liste_titre"> Bonus</td>';

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> € </td>';

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> € </td>';

print '<td class="liste_titre"> # </td>';

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> € </td>';

// T7
print '<td class="liste_titre"> #</td>';
print '<td class="liste_titre"> €</td>';

// Damage
print '<td class="liste_titre"> €</td>';
print '<td class="liste_titre"> fact. €</td>';

print '<td class="liste_titre"> € </td>';
print '<td class="liste_titre"> Balance (A payer) €</td>';

print'</tr>';
$tableQuery = new BillableFlightQuery(true, (GETPOST("year") ?: date("Y")));
$tableQueryHandler = new BillableFlightQueryHandler($db, $conf->global);

$total = 0;
$totalT1 = 0;
$totalT2 = 0;
$totalT3 = 0;
$totalT4 = 0;
$totalT5 = 0;
$totalT6 = 0;
$totalT7 = 0;
/**
 * @var int   $key
 * @var Pilot $pilot
 */
foreach ($tableQueryHandler->__invoke($tableQuery) as $key => $pilot) {
    $total += $pilot->getTotalBill()->getValue();
    $totalT1 += $pilot->getCountForType('1')->getCount();
    $totalT2 += $pilot->getCountForType('2')->getCount();
    $totalT3 += $pilot->getCountForType('3')->getCount();
    $totalT4 += $pilot->getCountForType('4')->getCount();
    $totalT5 += $pilot->getCountForType('5')->getCount();
    $totalT6 += $pilot->getCountForType('6')->getCount();
    $totalT7 += $pilot->getCountForType('7')->getCount();

    print '<tr class="oddeven">';
    print '<td>' . $pilot->getId() . '</td>';
    print '<td>' . $pilot->getName() . '</td>';

    print '<td>' . $pilot->getCountForType('1')->getCount() . '</td>';
    print '<td>' . $pilot->getCountForType('1')->getCost()->getValue() . '</td>';

    print '<td>' . $pilot->getCountForType('2')->getCount() . '</td>';
    print '<td>' . $pilot->getCountForType('2')->getCost()->getValue() . '</td>';

    print '<td>' . $pilot->getCountForType('orga')->getCount() . '</td>';
    print '<td>' . $pilot->getCountForType('orga')->getCost()->getValue() . '</td>';

    print '<td>' . $pilot->getCountForType('orga_T6')->getCount() . '</td>';
    print '<td>' . $pilot->getCountForType('orga_T6')->getCost()->getValue() . '</td>';

    print sprintf('<td class="%s">', $pilot->getFlightBonus()->getValue() === 0?'text-muted':'text-bold'). $pilot->getFlightBonus()->getValue() . ' pts</td>';

    print '<td>' . $pilot->getCountForType('3')->getCount() . '</td>';
    print '<td>' . price($pilot->getCountForType('3')->getCost()->getValue()) . '€</td>';

    print '<td>' . $pilot->getCountForType('4')->getCount() . '</td>';
    print '<td>' . price($pilot->getCountForType('4')->getCost()->getValue()) . '€</td>';

    print '<td>' . $pilot->getCountForType('5')->getCount() . '</td>';

    print '<td>' . $pilot->getCountForType('6')->getCount() . '</td>';
    print '<td>' . price($pilot->getCountForType('6')->getCost()->getValue()) . '€</td>';

    print '<td>' . $pilot->getCountForType('7')->getCount() . '</td>';
    print '<td>' . price($pilot->getCountForType('7')->getCost()->getValue()) . '€</td>';

    print '<td>' . price($pilot->getCountForType('damage')->getCost()->getValue()) . '€</td>';
    print '<td>' . price($pilot->getCountForType('invoiced_damage')->getCost()->getValue()) . '€</td>';

    print sprintf('<td class="%s">', $pilot->getFlightsCost()->getValue() === 0?'text-muted':'text-bold'). price($pilot->getFlightsCost()->getValue()) . '€ </td>';
    print sprintf('<td class="%s">', $pilot->isBillable(FlightBonus::zero())?'text-bold':'text-muted'). price($pilot->getTotalBill()->getValue()) . '€</td>';
    print '</tr>';
}

print '<tr class="oddeven">';
print '<td></td>';
print '<td></td>';

print '<td>' . $totalT1 . '</td>';
print '<td></td>';

print '<td>' . $totalT2 . '</td>';
print '<td>' . '</td>';

print '<td>' . '</td>';
print '<td>' . '</td>';

print '<td>' . '</td>';
print '<td>' . '</td>';

print '<td><b>' . '</b></td>';

print '<td>' . $totalT3 . '</td>';
print '<td></td>';

print '<td>' . $totalT4. '</td>';
print '<td></td>';

print '<td>' . $totalT5 . '</td>';

print '<td>' . $totalT6 . '</td>';
print '<td></td>';

print '<td>' . $totalT7 . '</td>';
print '<td></td>';

print '<td></td>';
print '<td></td>';

print '<td>Total à reçevoir </td>';
print "<td>" . price($total) . "€</td>";
print '</tr>';


print '</tbody>';
print'</table>';


print '<br/>';
print '<h3>' . $langs->trans("Remboursement aux pilotes") . '</h3>';

//table km
$tauxRemb = isset($conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM) ? $conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM : 0;
$year = GETPOST("year", 'int');
if(empty($year)){
    $year = date('Y');
}

$queryHandler = new GetPilotsWithMissionsQueryHandler($db);
$query = new GetPilotsWithMissionsQuery($year);

printBbcKilometersByQuartil($queryHandler->__invoke($query), $tauxRemb, $unitPriceMission);

print '</div>';

print '<br/>';

print '<div class="tabsAction">';


if ($conf->facture->enabled && $user->rights->flightlog->vol->status && $user->rights->flightlog->vol->financialGenerateDocuments) {
    print '<a class="butAction" href="generateBilling.php?year=' . (GETPOST("year",
            'int') ?: date("Y")) . '">Générer Factures</a>';
}

if ($conf->expensereport->enabled && $user->rights->flightlog->vol->financialGenerateDocuments) {
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
