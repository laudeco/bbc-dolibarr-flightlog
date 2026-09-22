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

dol_include_once("/flightlog/flightlog.inc.php");

use FlightLog\Infrastructure\Pilot\Query\Repository\PilotQueryRepository;
use flightlog\query\GetPilotsWithMissionsQuery;
use flightlog\query\GetPilotsWithMissionsQueryHandler;

$langs->load("mymodule@flightlog");

// Get parameters
//TODO get all parameters from here
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$myparam = GETPOST('myparam', 'alpha');

$unitPriceMission = $conf->global->BBC_FLIGHT_LOG_UNIT_PRICE_MISSION;

$ctrl = new \FlightLog\Http\Web\Controller\StatisticalGraphController($db);

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

try{
	$tableQuery = new BillableFlightQuery(true, (GETPOST("year") ?: date("Y")));
	$tableQueryHandler = new BillableFlightQueryHandler($db, $conf->global);
	$pilotQueryRepository = new PilotQueryRepository($db);

}catch(Exception $e){
	dol_syslog($e->getMessage(), LOG_ERR);
}

function pilotStatus($id){
    global $pilotQueryRepository;
    $member = $pilotQueryRepository->byId($id);
    if($member === null){
        return '';
    }

    return img_picto($member->getReasons(), $member->getIconId(), '', false, false, false, '', 'classfortooltip');
}

/** @var Pilot[] $pilots */
$pilots = $tableQueryHandler->__invoke($tableQuery);

//The flight types are fully configurable : the table is built from the configuration.
$flightTypes = filterBbcFlightTypesToDisplay(fetchAllBbcFlightTypes(), $pilots);
$missionFlightTypes = filterBbcMissionFlightTypes($flightTypes);
$otherFlightTypes = filterBbcNonMissionFlightTypes($flightTypes);

// mission types (# + pts) + organisator + instructor + sub total
$missionColumnCount = (2 * count($missionFlightTypes)) + 5;
// other types (# + €) + damages + sub total + balance
$otherColumnCount = (2 * count($otherFlightTypes)) + 4;

print '<table class="" width="100%">';

print '<tbody>';

// Group of columns : the missions of the club on one side, all the other types on the other side.
print '<tr class="liste_titre">';
print '<td colspan="2"></td>';
print '<td class="liste_titre _alignCenter" colspan="' . $missionColumnCount . '">' . $langs->trans("Missions du club (points)") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="' . $otherColumnCount . '">' . $langs->trans("Autres vols (€)") . '</td>';
print '</tr>';

print '<tr class="liste_titre">';
print '<td colspan="2">&nbsp;</td>';

foreach ($missionFlightTypes as $currentFlightType) {
    print '<td class="liste_titre _alignCenter" colspan="2">' . bbcFlightTypeColumnLabel($currentFlightType) . '</td>';
}

print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Orga.") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Instructeur") . '</td>';
print '<td class="liste_titre _alignCenter" >' . $langs->trans("Total bonus") . '</td>';

foreach ($otherFlightTypes as $currentFlightType) {
    print '<td class="liste_titre _alignCenter" colspan="2">' . bbcFlightTypeColumnLabel($currentFlightType) . '</td>';
}

print '<td class="liste_titre _alignCenter" colspan="2">' . $langs->trans("Réparations") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="1">' . $langs->trans("Facture") . '</td>';
print '<td class="liste_titre _alignCenter" colspan="1">' . $langs->trans("A payer") . '</td>';
print '</tr>';

print '<tr class="liste_titre">';
print '<td colspan="2" class="liste_titre"></td>';

// missions : number of flights and points
foreach ($missionFlightTypes as $currentFlightType) {
    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> Pts </td>';
}

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> Pts </td>';

print '<td class="liste_titre"> # </td>';
print '<td class="liste_titre"> Pts </td>';

print '<td class="liste_titre"> Pts</td>';

// other types : number of flights and amount
foreach ($otherFlightTypes as $currentFlightType) {
    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> € </td>';
}

// Damage
print '<td class="liste_titre"> €</td>';
print '<td class="liste_titre"> fact. €</td>';

print '<td class="liste_titre"> € </td>';
print '<td class="liste_titre"> Balance (A payer) €</td>';

print'</tr>';

$total = 0;

$totalCountByType = [];
$totalPointsByType = [];
$totalCostByType = [];
foreach ($flightTypes as $currentFlightType) {
    $totalCountByType[$currentFlightType->getNumero()] = 0;
    $totalPointsByType[$currentFlightType->getNumero()] = 0;
    $totalCostByType[$currentFlightType->getNumero()] = 0;
}

$totalWithoutPts = 0;

$totalCountOrga = 0;
$totalCountInstructor = 0;
$totalPtsOrga = 0;
$totalPtsInstructor = 0;
$totalPts = 0;
$totalDamage = 0;
$totalInvoicedDamage = 0;

/**
 * @var int   $key
 * @var Pilot $pilot
 */
foreach ($pilots as $key => $pilot) {
    $total += $pilot->getTotalBill()->getValue();

    $totalPtsOrga += $pilot->getCountForType('orga')->getCost()->getValue();
    $totalPtsInstructor += $pilot->getCountForType('orga_T6')->getCost()->getValue();
    $totalCountOrga += $pilot->getCountForType('orga')->getCount();
    $totalCountInstructor += $pilot->getCountForType('orga_T6')->getCount();
    $totalPts += $pilot->getFlightBonus()->getValue();

    $totalWithoutPts += $pilot->getFlightsCost()->getValue();
    $totalDamage += $pilot->damageCost()->getValue();
    $totalInvoicedDamage += $pilot->invoicedDamageCost()->getValue();

    print '<tr class="oddeven">';
    print '<td>' . $pilot->getId() . '</td>';
    print '<td>' . pilotStatus($pilot->getId()) . $pilot->getName() . '</td>';

    // Missions of the club : the pilot wins points
    foreach ($missionFlightTypes as $currentFlightType) {
        $count = $pilot->getCountForType((string) $currentFlightType->getNumero());

        $totalCountByType[$currentFlightType->getNumero()] += $count->getCount();
        $totalPointsByType[$currentFlightType->getNumero()] += $count->getCost()->getValue();

        print '<td>' . $count->getCount() . '</td>';
        print '<td>' . $count->getCost()->getValue() . ' pts </td>';
    }

    print '<td>' . $pilot->getCountForType('orga')->getCount() . '</td>';
    print '<td>' . $pilot->getCountForType('orga')->getCost()->getValue() . ' pts </td>';

    print '<td>' . $pilot->getCountForType('orga_T6')->getCount() . '</td>';
    print '<td>' . $pilot->getCountForType('orga_T6')->getCost()->getValue() . 'pts </td>';

    //Sub total of the missions
    print sprintf('<td class="%s">', $pilot->getFlightBonus()->getValue() === 0?'text-muted':'text-bold'). $pilot->getFlightBonus()->getValue() . ' pts</td>';

    // All the other types : the pilot has to pay
    foreach ($otherFlightTypes as $currentFlightType) {
        $count = $pilot->getCountForType((string) $currentFlightType->getNumero());

        $totalCountByType[$currentFlightType->getNumero()] += $count->getCount();
        $totalCostByType[$currentFlightType->getNumero()] += $currentFlightType->isPilotCharged() ? $count->getCost()->getValue() : 0;

        print '<td>' . $count->getCount() . '</td>';
        print '<td>' . ($currentFlightType->isPilotCharged() ? price($count->getCost()->getValue()) . '€' : '-') . '</td>';
    }

    print '<td>' . price($pilot->damageCost()->getValue()) . '€</td>';
    print '<td>' . price($pilot->invoicedDamageCost()->getValue()) . '€</td>';

    //Sub total of the other types
    print sprintf('<td class="%s">', $pilot->getFlightsCost()->getValue() === 0?'text-muted':'text-bold'). price($pilot->getFlightsCost()->getValue()) . '€ </td>';
    print sprintf('<td class="%s">', $pilot->isBillable(FlightBonus::zero())?'text-bold':'text-muted'). price($pilot->getTotalBill()->getValue()) . '€</td>';
    print '</tr>';
}

print '<tr class="oddeven">';
print '<td colspan="2" class="text-bold">Total</td>';

foreach ($missionFlightTypes as $currentFlightType) {
    print '<td class="text-bold">' . $totalCountByType[$currentFlightType->getNumero()] . '</td>';
    print '<td class="text-bold">' . $totalPointsByType[$currentFlightType->getNumero()] . ' pts </td>';
}

print '<td class="text-bold">' . $totalCountOrga . '</td>';
print '<td class="text-bold">' . $totalPtsOrga . '</td>';

print '<td class="text-bold">' . $totalCountInstructor . '</td>';
print '<td class="text-bold">' . $totalPtsInstructor . '</td>';

print '<td class="text-bold"><b>' . $totalPts . '</b></td>';

foreach ($otherFlightTypes as $currentFlightType) {
    print '<td class="text-bold">' . $totalCountByType[$currentFlightType->getNumero()] . '</td>';
    print '<td class="text-bold">' . ($currentFlightType->isPilotCharged() ? price($totalCostByType[$currentFlightType->getNumero()]) . '€' : '-') . '</td>';
}

print '<td class="text-bold">' . price($totalDamage) . '€</td>';
print '<td class="text-bold">' . price($totalInvoicedDamage) . '€</td>';

print '<td class="text-bold"><b>' . price($totalWithoutPts) . '€</b></td>';
print '<td class="text-bold"><b>' . price($total) . "€</b></td>";
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

    <?php
    // One graph for the missions of the club, one for all the other types of flight.
    $missionGraphData = getGraphMissionsByTypeAndYearData();
    $otherFlightsGraphData = getGraphOtherFlightsByTypeAndYearData();
    ?>

    <?php if (!empty($missionGraphData->getTypes())): ?>
        <div class="fichecenter">
            <?php include $ctrl->graphByType($missionGraphData,
                "Missions du club par type et par année", 'per_mission_type')->getTemplate(); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($otherFlightsGraphData->getTypes())): ?>
        <div class="fichecenter">
            <?php include $ctrl->graphByType($otherFlightsGraphData,
                "Autres vols par type et par année", 'per_other_type')->getTemplate(); ?>
        </div>
    <?php endif; ?>

    <div class="fichecenter">
        <?php include $ctrl->billableFlightsPerMonth()->getTemplate(); ?>
    </div>

<?php
llxFooter();
