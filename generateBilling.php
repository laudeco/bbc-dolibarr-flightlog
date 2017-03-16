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

dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/adherents/class/adherent.class.php');
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

//post parameters
$additionalBonus = GETPOST('additional_bonus', 'array', 2);
$pilotIds = GETPOST('pilot', 'array', 2);
$amouts = GETPOST('amout', 'array', 2);
$amoutDiscounts = GETPOST('amoutDiscount', 'array', 2);
$publicNote = GETPOST('public_note', 'alpha', 2);
$privateNote = GETPOST('private_note', 'alpha', 2);

$currentYear = date('Y');

//Query
$sql = "SELECT USR.lastname AS nom , USR.firstname AS prenom ,COUNT(`idBBC_vols`) AS nbr,fk_pilot as pilot, TT.numero as type,SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(heureA,heureD)))) AS time";
$sql .= " FROM llx_bbc_vols, llx_user AS USR,llx_bbc_types AS TT ";
$sql .= " WHERE `fk_pilot`= USR.rowid AND fk_type = TT.idType AND YEAR(llx_bbc_vols.date) = " . $year;
$sql .= " GROUP BY fk_pilot,`fk_type`";

$flightYears = getFlightYears();

//pdf
$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

$object = new Facture($db);
$vatrate = "0.000";

// Access control
if (!$conf->expensereport->enabled || !$user->rights->flightLog->vol->status || !$user->rights->flightLog->vol->financialGenerateDocuments) {
    accessforbidden();
}

// Default action
if (empty($action)) {
    $action = EXPENSE_REPORT_GENERATOR_ACTION_SELECT;
}

llxHeader('', $langs->trans('Generate billing'), '');
print load_fiche_titre("Générer factures");

/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */

if ($action == EXPENSE_REPORT_GENERATOR_ACTION_GENERATE) {

    if ($year < $currentYear) {

        $table = sqlToArray($db, $sql, true, $year);
        foreach ($table as $currentMissionUserId => $value) {

            $addBonus = (int)$additionalBonus[$currentMissionUserId];
            $totalFlights = $value['1']['count'] + $value['2']['count'] + $value['orga']['count'] + $value['3']['count'] + $value['4']['count'] + $value['6']['count'] + $value['7']['count'];
            $totalBonus = $value['1']['count'] * 50 + $value['2']['count'] * 50 + $value['orga']['count'] * 25 + $addBonus;

            $totalFacture = $value['3']['count'] * 150 + $value['4']['count'] * 100 + $value['6']['count'] * 50 + $value['7']['count'] * 75;

            $facturable = ($totalFacture - $totalBonus < 0 ? 0 : $totalFacture - $totalBonus);

            if ($facturable == 0) {
                continue;
            }

            $discount = ((int)((($totalFacture - $facturable) * 100 / $totalFacture)*10000))/10000;

            $expenseNoteUser = new User($db);
            $expenseNoteUser->fetch($currentMissionUserId);

            $adherent = new Adherent($db);
            $adherent->fetch($expenseNoteUser->fk_member);

            $object = new Facture($db);
            $object->fetch_thirdparty();

            $object->socid = $adherent->fk_soc;
            $object->type = Facture::TYPE_STANDARD;
            $object->number = "provisoire";
            $object->date = (new DateTime())->getTimestamp();
            $object->date_pointoftax = "";
            $object->note_public = $publicNote;
            $object->note_private = $privateNote;
            $object->ref_client = "";
            $object->ref_int = "";
            $object->modelpdf = "crabe";
            $object->cond_reglement_id = 2;
            $object->mode_reglement_id = 2;
            $object->fk_account = 5;

            $id = $object->create($user);

            if ($id <= 0) {
                setEventMessages($object->error, $object->errors, 'errors');
            }

            $localtax1_tx = get_localtax(0, 1, $object->thirdparty);
            $localtax2_tx = get_localtax(0, 2, $object->thirdparty);

            //T1
            $pu_ht = price2num(0, 'MU');
            $pu_ttc = price2num(0, 'MU');
            $pu_ht_devise = price2num(0, 'MU');
            $qty = $value['1']['count'];
            $desc = "Vols T1 (Sponsor) en " . $year;

            $result = $object->addline(
                $desc,
                $pu_ht,
                $qty,
                0,
                $localtax1_tx,
                $localtax2_tx,
                0,
                $discount,
                '',
                '',
                0,
                0,
                '',
                'HT',
                $pu_ttc,
                1,
                -1,
                0,
                '',
                0,
                0,
                '',
                '',
                '',
                [],
                100,
                '',
                0,
                0
            );

            //T2
            $pu_ht = price2num(0, 'MU');
            $pu_ttc = price2num(0, 'MU');
            $pu_ht_devise = price2num(0, 'MU');
            $qty = $value['3']['count'];
            $desc = "Vols T2 (Vol passagers) en " . $year;

            $result = $object->addline(
                $desc,
                $pu_ht,
                $qty,
                0,
                $localtax1_tx,
                $localtax2_tx,
                0,
                $discount,
                '',
                '',
                0,
                0,
                '',
                'HT',
                $pu_ttc,
                1,
                -1,
                0,
                '',
                0,
                0,
                '',
                '',
                '',
                [],
                100,
                '',
                0,
                0
            );

            //T3
            $pu_ht = price2num(150, 'MU');
            $pu_ttc = price2num(150, 'MU');
            $pu_ht_devise = price2num(150, 'MU');
            $qty = $value['3']['count'];
            $desc = "Vols T3 (privé) en " . $year;

            $result = $object->addline(
                $desc,
                $pu_ht,
                $qty,
                0,
                $localtax1_tx,
                $localtax2_tx,
                0,
                $discount,
                '',
                '',
                0,
                0,
                '',
                'HT',
                $pu_ttc,
                1,
                -1,
                0,
                '',
                0,
                0,
                '',
                '',
                '',
                [],
                100,
                '',
                0,
                0
            );

            //T4
            $pu_ht = price2num(100, 'MU');
            $pu_ttc = price2num(100, 'MU');
            $pu_ht_devise = price2num(100, 'MU');
            $qty = $value['4']['count'];
            $desc = "Vols T4 (meeting) en " . $year;

            $result = $object->addline(
                $desc,
                $pu_ht,
                $qty,
                0,
                $localtax1_tx,
                $localtax2_tx,
                0,
                $discount,
                '',
                '',
                0,
                0,
                '',
                'HT',
                $pu_ttc,
                1,
                -1,
                0,
                '',
                0,
                0,
                '',
                '',
                '',
                [],
                100,
                '',
                0,
                0
            );

            //T6
            $pu_ht = price2num(50, 'MU');
            $pu_ttc = price2num(50, 'MU');
            $pu_ht_devise = price2num(50, 'MU');
            $qty = $value['6']['count'];
            $desc = "Vols T6 (écolage) en " . $year;

            $result = $object->addline(
                $desc,
                $pu_ht,
                $qty,
                0,
                $localtax1_tx,
                $localtax2_tx,
                0,
                $discount,
                '',
                '',
                0,
                0,
                '',
                'HT',
                $pu_ttc,
                1,
                -1,
                0,
                '',
                0,
                0,
                '',
                '',
                '',
                [],
                100,
                '',
                0,
                0
            );

            //T7
            $pu_ht = price2num(75, 'MU');
            $pu_ttc = price2num(75, 'MU');
            $pu_ht_devise = price2num(75, 'MU');
            $qty = $value['7']['count'];
            $desc = "Vols T7(< 50 vols) en " . $year;

            $result = $object->addline(
                $desc,
                $pu_ht,
                $qty,
                0,
                $localtax1_tx,
                $localtax2_tx,
                0,
                $discount,
                '',
                '',
                0,
                0,
                '',
                'HT',
                $pu_ttc,
                1,
                -1,
                0,
                '',
                0,
                0,
                '',
                '',
                '',
                [],
                100,
                '',
                0,
                0
            );

            //Orga
            $pu_ht = price2num(0, 'MU');
            $pu_ttc = price2num(0, 'MU');
            $pu_ht_devise = price2num(0, 'MU');
            $qty = $value['orga']['count'];
            $desc = "Vols Organisateur  ";

            $result = $object->addline(
                $desc,
                $pu_ht,
                $qty,
                0,
                $localtax1_tx,
                $localtax2_tx,
                0,
                $discount,
                '',
                '',
                0,
                0,
                '',
                'HT',
                $pu_ttc,
                1,
                -1,
                0,
                '',
                0,
                0,
                '',
                '',
                '',
                [],
                100,
                '',
                0,
                0
            );

            $ret = $object->fetch($id);
            $result = $object->generateDocument("crabe", $langs, $hidedetails, $hidedesc, $hideref);
            $object->fetch($id);
            $object->validate($user);
            $object->fetch($id);
            $result = $object->generateDocument("crabe", $langs, $hidedetails, $hidedesc, $hideref);

        }


        if ($result > 0) {
            dol_htmloutput_mesg("Facture créées");
        } else {
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
foreach ($flightYears as $currentFlightYear) {
    $tabLinks[] = [
        DOL_URL_ROOT . "/flightLog/generateBilling.php?year=" . $currentFlightYear,
        $currentFlightYear,
        "tab_" . $currentFlightYear
    ];
}

dol_fiche_head($tabLinks, "tab_" . $year);

?>
    <form method="POST">

        <!-- action -->
        <input type="hidden" name="action" value="<?= EXPENSE_REPORT_GENERATOR_ACTION_GENERATE ?>">

        <?php

        //tableau par pilote

        $resql = $db->query($sql);
        $pilotNumberFlight = array();
        if ($resql):

            print '<div class="tabBar">';
            print '<table class="border" width="100%">';

            print '<tr class="liste_titre">';
            print '<td colspan="2">Nom</td>';
            print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 1 : Sponsor") . '</td>';
            print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 2 : Baptême") . '</td>';
            print '<td class="liste_titre" colspan="2">' . $langs->trans("Organisateur (T1/T2)") . '</td>';
            print '<td class="liste_titre" colspan="2">' . $langs->trans("Total bonus") . '</td>';
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
            print '<td class="liste_titre"> Bonus additional (ROI) </td>';

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
            $table = sqlToArray($db, $sql, true, $year);
            $total = 0;
            foreach ($table as $key => $value) {

                $totalBonus = $value['1']['count'] * 50 + $value['2']['count'] * 50 + $value['orga']['count'] * 25;
                $totalFacture = $value['3']['count'] * 150 + $value['4']['count'] * 100 + $value['6']['count'] * 50 + $value['7']['count'] * 75;
                $facturable = ($totalFacture - $totalBonus < 0 ? 0 : $totalFacture - $totalBonus);
                $total += $facturable;

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
                print '<td>' . $key;
                print sprintf('<input type="hidden" name="pilot[%s]" value="%s" />', $key, $key);
                print '</td>';
                print '<td>' . $value['name'] . '</td>';

                print '<td>' . $value['1']['count'] . '</td>';
                print '<td>' . $value['1']['count'] * 50 . '</td>';

                print '<td>' . $value['2']['count'] . '</td>';
                print '<td>' . $value['2']['count'] * 50 . '</td>';

                print '<td>' . $value['orga']['count'] . '</td>';
                print '<td>' . $value['orga']['count'] * 25 . '</td>';

                print '<td><b>' . ($totalBonus) . '</b></td>';
                print '<td>' . sprintf('<input type="number" value="0" name="additional_bonus[%s]"/>',
                        $key) . '</b></td>';

                print '<td>' . $value['3']['count'] . '</td>';
                print '<td>' . price($value['3']['count'] * 150) . '€</td>';

                print '<td>' . $value['4']['count'] . '</td>';
                print '<td>' . price($value['4']['count'] * 100) . '€</td>';

                print '<td>' . $value['5']['count'] . '</td>';

                print '<td>' . $value['6']['count'] . '</td>';
                print '<td>' . price($value['6']['count'] * 50) . '€</td>';

                print '<td>' . $value['7']['count'] . '</td>';
                print '<td>' . price($value['7']['count'] * 75) . '€</td>';

                print '<td>';
                print sprintf('<input type="hidden" value="%d" name="amout[%d]"/>', $totalFacture, $key);
                print price($totalFacture);
                print '€ </td>';


                print '<td>';
                print sprintf('<input type="hidden" value="%d" name="amoutDiscount[%d]"/>', $facturable, $key);
                print price($facturable);
                print '€ </td>';

                print '</tr>';

            }


            ?>

            <tr>
                <td colspan='19'></td>
                <td>Total à reçevoir</td>
                <td><?= price($total) ?>€</td>
            </tr>

            </table>

        <?php endif; ?>

        <!-- Public note -->
        <label><?= $langs->trans("Note publique (commune à toutes les factures)"); ?></label><br/>
        <textarea name="public_note" wrap="soft" class="quatrevingtpercent" rows="2">
            Les frais pilotes regroupent tous les frais qu'à le pilote pour organiser son vol (Champagne, Téléphone, Diplômes, ...).
        </textarea>
        <br/>

        <!-- Private note -->
        <label><?= $langs->trans("Note privée (commune à toutes les factures)"); ?></label><br/>
        <textarea name="private_note" wrap="soft" class="quatrevingtpercent" rows="2"></textarea>
        <br/>

        <button class="butAction" type="submit">Générer</button>
    </form>

<?php
llxFooter();