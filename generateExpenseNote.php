<?php
/**
 * When a user generates the expense report for all pilots
 */
define("EXPENSE_REPORT_GENERATOR_ACTION_GENERATE", "generate");

/**
 * When a user has to select year and quartil
 */
define("EXPENSE_REPORT_GENERATOR_ACTION_SELECT", "select");

/**
 * \file    generateExpenseNote.php
 * \ingroup flightLog
 * \brief   Generate expense notes for a quartil
 *
 */

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

dol_include_once('/expensereport/class/expensereport.class.php');
dol_include_once("/flightLog/lib/flightLog.lib.php");

global $db, $langs, $user, $conf;

// Load translation files required by the page
$langs->load("mymodule@mymodule");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$year = GETPOST('year', 'int', 3);
$quarter = GETPOST('quarter', 'int');

$startDate = new \DateTime();
$startDate->setDate($year, (($quarter - 1) * 3) + 1, 1);

$endDate = new \DateTime();
$endDate->setDate($year, $quarter * 3, 1);

$currentYear = date('Y');
$currentQuarter = floor((date('n') - 1) / 3) + 1;

$tauxRemb = isset($conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM) ? $conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM : 0;
$flightYears = getFlightYears();

// Access control
if (!$conf->expensereport->enabled || !$user->rights->flightLog->flight->billable) {
    accessforbidden();
}

// Default action
if (empty($action)) {
    $action = EXPENSE_REPORT_GENERATOR_ACTION_SELECT;
}

llxHeader('', $langs->trans('Generate expense report'), '');
print load_fiche_titre("Générer note de frais");

/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */

if ($action == EXPENSE_REPORT_GENERATOR_ACTION_GENERATE) {

    if ($year < $currentYear || ($year == $currentYear && $quarter < $currentQuarter)) {
        $object = new ExpenseReport($db);
        $object->date_debut = $startDate->format("Y-m-d");
        $object->date_fin = $endDate->format("Y-m-d");

        $object->fk_statut = 1;
        $object->fk_user_validator = $user->id;
        $object->note_public = "TEST";
        $object->note_private = GETPOST('note_private');

        $result = $object->create($user);
        if ($result > 0) {
            dol_htmloutput_mesg("Notes de frais créées");
        }else{
            dol_htmloutput_errors("Note de frais non créée");
        }
    } else {
        //Quarter not yet finished
        dol_htmloutput_errors("Le quartil n'est pas encore fini !");
    }
}

/*
 * VIEW
 *
 * Put here all code to build page
 */


$form = new Form($db);


$tabLinks = [];
foreach($flightYears as $currentFlightYear){
    $tabLinks[] = [
        DOL_URL_ROOT."/flightLog/generateExpenseNote.php?year=".$currentFlightYear,
        $currentFlightYear,
        "tab_".$currentFlightYear
    ];
}

dol_fiche_head($tabLinks, "tab_".$year);

?>
    <form method="POST">

        <!-- action -->
        <input type="hidden" name="action" value="<?= EXPENSE_REPORT_GENERATOR_ACTION_GENERATE ?>">



        <?php printBbcKilometersByQuartil(bbcKilometersByQuartil($year), $tauxRemb); ?>

        <!-- Quarter -->
        <label for="field_quarter">Q : </label>
        <br/>
        <select name="quarter" id="field_quarter">
            <option value="1" <?= ($year == $currentYear && $currentQuarter <= 1) ? 'disabled="disabled"' : "" ?>>1</option>
            <option value="2" <?= ($year == $currentYear && $currentQuarter <= 2) ? 'disabled="disabled"' : "" ?>>2</option>
            <option value="3" <?= ($year == $currentYear && $currentQuarter <= 3) ? 'disabled="disabled"': "" ?>>3</option>
            <option value="4" <?= ($year == $currentYear && $currentQuarter <= 4) ? 'disabled="disabled"' : "" ?>>4</option>
        </select>
        <br/>

        <!-- Public note -->
        <label><?= $langs->trans("Note publique (commune à toutes les notes de frais)"); ?></label><br/>
        <textarea name="public_note" wrap="soft" class="quatrevingtpercent" rows="2"></textarea>
        <br/>

        <!-- Private note -->
        <label><?= $langs->trans("Note privée (commune à toutes les notes de frais)"); ?></label><br/>
        <textarea name="private_note" wrap="soft" class="quatrevingtpercent" rows="2"></textarea>
        <br/>

        <!-- Validate expense note -->
        <label><?= $langs->trans("Valider les notes de frais : "); ?></label>
        <input type="checkbox" value="1" name="validate"/>
        <br/>

        <button class="butAction" type="submit">Générer</button>
    </form>

<?php
llxFooter();
?>