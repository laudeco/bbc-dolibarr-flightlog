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
$langs->load("trips");
$langs->load("bills");
$langs->load("mails");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$year = GETPOST('year', 'int', 3);
$quarter = GETPOST('quarter', 'int');

$startDate = new \DateTime();
$startDate->setDate($year, (($quarter - 1) * 3) + 1, 1);

$endDate = new \DateTime();
$endDate->setDate($year, $quarter * 3, 1);
$endDate->add(new \DateInterval("P1M"))->sub(new \DateInterval("P1D"));

$currentYear = date('Y');
$currentQuarter = floor((date('n') - 1) / 3) + 1;

$tauxRemb = isset($conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM) ? $conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM : 0;
$unitPriceMission = $conf->global->BBC_FLIGHT_LOG_UNIT_PRICE_MISSION;

$flightYears = getFlightYears();

$object = new ExpenseReport($db);
$vatrate = "0.000";

// Access control
if (!$conf->expensereport->enabled || !$user->rights->flightLog->vol->status || !$user->rights->flightLog->vol->financial) {
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

        $missions = bbcKilometersByQuartil($year);

        foreach($missions as $currentMissionUserId => $currentMission){

            if($currentMission["quartil"][$quarter]["km"] == 0 && $currentMission["quartil"][$quarter]["flight"] == 0){
                continue;
            }

            $expenseNoteUser = new User($db);
            $expenseNoteUser->id = $currentMissionUserId;

            $object = new ExpenseReport($db);
            $object->date_debut = $startDate->format("Y-m-d");
            $object->date_fin = $endDate->format("Y-m-d");

            $object->fk_statut = 1;
            $object->fk_user_validator = GETPOST("fk_user_validator", 'int');
            $object->note_public = GETPOST('public_note', 'alpha');
            $object->note_private = GETPOST('private_note','alpha');

            $expenseNoteId = $object->create($expenseNoteUser);
            if($expenseNoteId < 0){
                dol_htmloutput_errors("Erreur lors de la création de la note de frais" , $object->errors);
                continue;
            }


            $flightsForQuarter = findFlightByPilotAndQuarter($currentMissionUserId, $year, $quarter);

            foreach($flightsForQuarter as $currentFlightForQuarter) {

                // Kilometers
                $object_ligne = new ExpenseReportLine($db);
                $object_ligne->comments = $langs->trans(sprintf("Vol (id: %d) %s à %s  détail: %s", $currentFlightForQuarter->idBBC_vols, $currentFlightForQuarter->lieuD, $currentFlightForQuarter->lieuA, $currentFlightForQuarter->justif_kilometers));
                $object_ligne->qty = $currentFlightForQuarter->kilometers;
                $object_ligne->value_unit = $tauxRemb;

                $object_ligne->date = $currentFlightForQuarter->date;

                $object_ligne->fk_c_type_fees = 2;
                $object_ligne->fk_expensereport = $expenseNoteId;
                $object_ligne->fk_projet = '';

                $object_ligne->vatrate = price2num($vatrate);

                $tmp = calcul_price_total($object_ligne->qty, $object_ligne->value_unit, 0, $vatrate, 0, 0, 0, 'TTC', 0,
                    0, '');
                $object_ligne->total_ttc = $tmp[2];
                $object_ligne->total_ht = $tmp[0];
                $object_ligne->total_tva = $tmp[1];

                $resultLine = $object_ligne->insert();

                // Missions
                $object_ligne = new ExpenseReportLine($db);
                $object_ligne->comments = sprintf("Vol (id: %d) %s à %s", $currentFlightForQuarter->idBBC_vols, $currentFlightForQuarter->lieuD, $currentFlightForQuarter->lieuA);
                $object_ligne->qty = 1;
                $object_ligne->value_unit = $unitPriceMission;

                $object_ligne->date = $currentFlightForQuarter->date;

                $object_ligne->fk_c_type_fees = 8;
                $object_ligne->fk_expensereport = $expenseNoteId;
                $object_ligne->fk_projet = '';

                $object_ligne->vatrate = price2num($vatrate);

                $tmp = calcul_price_total($object_ligne->qty, $object_ligne->value_unit, 0, $vatrate, 0, 0, 0, 'TTC', 0,
                    0, '');
                $object_ligne->total_ttc = $tmp[2];
                $object_ligne->total_ht = $tmp[0];
                $object_ligne->total_tva = $tmp[1];

                $resultLine = $object_ligne->insert();
            }

            $object->fetch($expenseNoteId);
            $object->setValidate($user);
            $object->setApproved($user);

            $object->fetch($expenseNoteId);
            $object->setDocModel($user, "standard");
            $result = $object->generateDocument($object->modelpdf, $langs);

        }


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

        <?php printBbcKilometersByQuartil(bbcKilometersByQuartil($year), $tauxRemb, $unitPriceMission); ?>

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
        <textarea name="public_note" wrap="soft" class="quatrevingtpercent" rows="2">
            Les frais pilotes regroupent tous les frais qu'à le pilote pour organiser son vol (Champagne, Téléphone, Diplômes, ...).
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