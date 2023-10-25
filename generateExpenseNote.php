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
 * \ingroup flightlog
 * \brief   Generate expense notes for a quartil
 *
 */

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user, $conf;

dol_include_once('/expensereport/class/expensereport.class.php');
dol_include_once("/flightlog/flightlog.inc.php");

use flightlog\command\CreateExpenseNoteCommand;
use flightlog\command\CreateExpenseNoteCommandHandler;
use flightlog\exceptions\PeriodNotFinishedException;
use flightlog\query\GetPilotsWithMissionsQuery;
use flightlog\query\GetPilotsWithMissionsQueryHandler;

// Load translation files required by the page
$langs->load("mymodule@mymodule");
$langs->load("trips");
$langs->load("bills");
$langs->load("mails");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$year = GETPOST('year', 'int', 3);
$quarter = GETPOST('quarter', 'int');
$userValidatorId = GETPOST('fk_user_validator', 'int');
$userValidatorId = GETPOST('fk_user_validator', 'int');
$privateNote = GETPOST('private_note');
$publicNote = GETPOST('public_note');

$currentYear = date('Y');
$currentQuarter = floor((date('n') - 1) / 3) + 1;

$tauxRemb = isset($conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM) ? $conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM : 0;
$unitPriceMission = $conf->global->BBC_FLIGHT_LOG_UNIT_PRICE_MISSION;

$flightYears = getFlightYears();
$object = new ExpenseReport($db);
$vatrate = "0.000";

$commandHandler = new CreateExpenseNoteCommandHandler($db, $conf, $user, $langs, new \flightlog\query\GetPilotsWithMissionsQueryHandler($db), new \flightlog\query\FlightForQuarterAndPilotQueryHandler($db));

// Access control
if (!$conf->expensereport->enabled || !$user->rights->flightlog->vol->status || !$user->rights->flightlog->vol->financialGenerateDocuments) {
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

    try {
        $command = new CreateExpenseNoteCommand($year, $quarter, $userValidatorId, $privateNote, $publicNote);
        $commandHandler->__invoke($command);
    } catch(PeriodNotFinishedException $e){
        dol_htmloutput_errors("Le quadri n'est pas encore fini !");
    } catch(\Exception $e){
        dol_syslog($e->getMessage(), LOG_ERR);
        dol_htmloutput_errors('Error : ' . $e->getMessage());
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
        DOL_URL_ROOT."/flightlog/generateExpenseNote.php?year=".$currentFlightYear,
        $currentFlightYear,
        "tab_".$currentFlightYear
    ];
}

dol_fiche_head($tabLinks, "tab_".(empty($year)?$currentYear:$year));

?>
    <form method="POST">

        <!-- action -->
        <input type="hidden" name="action" value="<?= EXPENSE_REPORT_GENERATOR_ACTION_GENERATE ?>">
		<input type="hidden" name="token" value="<?php echo newToken();?>"/>

        <?php
            $queryHandler = new GetPilotsWithMissionsQueryHandler($db);
            $query = new GetPilotsWithMissionsQuery($year);

            printBbcKilometersByQuartil($queryHandler->__invoke($query), $tauxRemb, $unitPriceMission);
        ?>

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

        <!-- Validator -->
        <label><?= $langs->trans("Validateur de la note de frais")?></label><br/>
        <?php
            $include_users = $object->fetch_users_approver_expensereport();
            print $form->select_dolusers($user->id,"fk_user_validator",1,"",0,$include_users);
        ?>
        <br/>

        <!-- Public note -->
        <label><?= $langs->trans("Note publique (commune à toutes les notes de frais)"); ?></label><br/>
        <textarea name="public_note" wrap="soft" class="quatrevingtpercent" rows="2">Les frais pilotes regroupent tous les frais qu'à le pilote pour organiser son vol (Champagne, Téléphone, Diplômes, ...).
        </textarea>
        <br/>

        <!-- Private note -->
        <label><?= $langs->trans("Note privée (commune à toutes les notes de frais)"); ?></label><br/>
        <textarea name="private_note" wrap="soft" class="quatrevingtpercent" rows="2"></textarea>
        <br/>

        <button class="butAction" type="submit">Générer</button>
    </form>

<?php
llxFooter();
