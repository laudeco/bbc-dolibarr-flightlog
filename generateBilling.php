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

dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/adherents/class/adherent.class.php');
dol_include_once("/flightlog/lib/flightlog.lib.php");
dol_include_once("/flightlog/class/bbctypes.class.php");
dol_include_once("/product/class/product.class.php");
dol_include_once('/core/modules/facture/modules_facture.php');

global $db, $langs, $user, $conf;

// Load translation files required by the page
$langs->load("mymodule@mymodule");
$langs->load("trips");
$langs->load("bills");

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
$type = GETPOST("type", "int", 3);
$conditionReglement = GETPOST("cond_reglement_id", "int", 3);
$modeReglement = GETPOST("mode_reglement_id", "int", 3);
$bankAccount = GETPOST("fk_account", "int", 3);
$documentModel = GETPOST("model", "alpha", 3);

//variables
$currentYear = date('Y');

$t1 = new Bbctypes($db);
$t1->fetch(1);
$t2 = new Bbctypes($db);
$t2->fetch(2);
$t3 = new Bbctypes($db);
$t3->fetch(3);
$t4 = new Bbctypes($db);
$t4->fetch(4);
$t5 = new Bbctypes($db);
$t5->fetch(5);
$t6 = new Bbctypes($db);
$t6->fetch(6);
$t7 = new Bbctypes($db);
$t7->fetch(7);

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
if (!$conf->facture->enabled || !$user->rights->flightLog->vol->status || !$user->rights->flightLog->vol->financialGenerateDocuments) {
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

        if (empty($documentModel) || $conditionReglement == 0 || empty($conditionReglement) || $modeReglement == 0 || empty($modeReglement)) {
            dol_htmloutput_errors("Erreur de configuration !");
        } else {
            $table = sqlToArray($db, $sql, true, $year);
            $startYearTimestamp = (new \DateTime())->setDate($year, 1, 1)->getTimestamp();
            $endYearTimestamp = (new \DateTime())->setDate($year, 12, 31)->getTimestamp();

            foreach ($table as $currentMissionUserId => $value) {

                $expenseNoteUser = new User($db);
                $expenseNoteUser->fetch($currentMissionUserId);

                $adherent = new Adherent($db);
                $adherent->fetch($expenseNoteUser->fk_member);

                $addBonus = (int)$additionalBonus[$currentMissionUserId];
                if ($addBonus < 0) {
                    dol_htmloutput_mesg("Facture ignorée " . $adherent->getFullName($langs), '', 'warning');
                    continue;
                }

                $totalFlights = $value['1']['count'] + $value['2']['count'] + $value['orga']['count'] + $value['3']['count'] + $value['4']['count'] + $value['6']['count'] + $value['7']['count'];
                $totalBonus = $value['1']['count'] * $t1->service->price_ttc + $value['2']['count'] * $t2->service->price_ttc + $value['orga']['count'] * 25 + $addBonus;

                $totalFacture = $value['3']['count'] * $t3->service->price_ttc + $value['4']['count'] * $t4->service->price_ttc + $value['6']['count'] * $t6->service->price_ttc + $value['7']['count'] * $t7->service->price_ttc;

                $facturable = ($totalFacture - $totalBonus < 0 ? 0 : $totalFacture - $totalBonus);

                if ($facturable == 0) {
                    continue;
                }

                $object = new Facture($db);
                $object->fetch_thirdparty();

                $object->socid = $adherent->fk_soc;
                $object->type = $type;
                $object->number = "provisoire";
                $object->date = (new DateTime())->getTimestamp();
                $object->date_pointoftax = "";
                $object->note_public = $publicNote;
                $object->note_private = $privateNote;
                $object->ref_client = "";
                $object->ref_int = "";
                $object->modelpdf = $documentModel;
                $object->cond_reglement_id = $conditionReglement;
                $object->mode_reglement_id = $modeReglement;
                $object->fk_account = $bankAccount;

                $id = $object->create($user);

                $soc = new Societe($db);
                $soc->fetch($adherent->fk_soc);

                if ($id <= 0) {
                    setEventMessages($object->error, $object->errors, 'errors');
                }

                $localtax1_tx = get_localtax(0, 1, $object->thirdparty);
                $localtax2_tx = get_localtax(0, 2, $object->thirdparty);

                //T3
                $pu_ht = price2num($t3->service->price, 'MU');
                $pu_ttc = price2num($t3->service->price_ttc, 'MU');
                $pu_ht_devise = price2num($t3->service->price, 'MU');
                $qty = $value['3']['count'];

                $result = $object->addline(
                    $t3->service->description,
                    $pu_ht,
                    $qty,
                    0,
                    $localtax1_tx,
                    $localtax2_tx,
                    $t3->service->id,
                    0,
                    $startYearTimestamp,
                    $endYearTimestamp,
                    0,
                    0,
                    '',
                    'TTC',
                    $pu_ttc,
                    1,
                    -1,
                    0,
                    '',
                    0,
                    0,
                    '',
                    '',
                    $t3->service->label,
                    [],
                    100,
                    '',
                    0,
                    0
                );

                //T4
                $pu_ht = price2num($t4->service->price, 'MU');
                $pu_ttc = price2num($t4->service->price_ttc, 'MU');
                $pu_ht_devise = price2num($t4->service->price, 'MU');
                $qty = $value['4']['count'];

                $result = $object->addline(
                    $t4->service->description,
                    $pu_ht,
                    $qty,
                    0,
                    $localtax1_tx,
                    $localtax2_tx,
                    $t4->service->id,
                    0,
                    $startYearTimestamp,
                    $endYearTimestamp,
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
                    $t4->service->label,
                    [],
                    100,
                    '',
                    0,
                    0
                );

                //T6
                $pu_ht = price2num($t6->service->price, 'MU');
                $pu_ttc = price2num($t6->service->price, 'MU');
                $pu_ht_devise = price2num($t6->service->price, 'MU');
                $qty = $value['6']['count'];

                $result = $object->addline(
                    $t6->service->description,
                    $pu_ht,
                    $qty,
                    0,
                    $localtax1_tx,
                    $localtax2_tx,
                    $t6->service->id,
                    0,
                    $startYearTimestamp,
                    $endYearTimestamp,
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
                    $t6->service->label,
                    [],
                    100,
                    '',
                    0,
                    0
                );

                //T7
                $pu_ht = price2num($t7->service->price, 'MU');
                $pu_ttc = price2num($t7->service->price_ttc, 'MU');
                $pu_ht_devise = price2num($t7->service->price, 'MU');
                $qty = $value['7']['count'];

                $result = $object->addline(
                    $t7->service->description,
                    $pu_ht,
                    $qty,
                    0,
                    $localtax1_tx,
                    $localtax2_tx,
                    $t7->service->id,
                    0,
                    $startYearTimestamp,
                    $endYearTimestamp,
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
                    $t7->service->label,
                    [],
                    100,
                    '',
                    0,
                    0
                );

                //### DISCOUNTS

                //T1
                $pu_ht = price2num(50 * $value['1']['count'], 'MU');
                $desc = $year . " - " . $t1->service->label . " - (" . $value['1']['count'] . " * 50)";

                $discountid = $soc->set_remise_except($pu_ht, $user, $desc, 0);
                $object->insert_discount($discountid);

                //T2
                $pu_ht = price2num(50 * $value['2']['count'], 'MU');
                $desc = $year . " - " . $t2->service->label . "  - (" . $value['2']['count'] . " * 50)";

                $discountid = $soc->set_remise_except($pu_ht, $user, $desc, 0);
                $object->insert_discount($discountid);

                //Orga
                $pu_ht = price2num(25 * $value['orga']['count'], 'MU');
                $desc = $year . " - Vols dont vous êtes organisateur - (" . $value['orga']['count'] . " * 25)";

                $discountid = $soc->set_remise_except($pu_ht, $user, $desc, 0);
                $object->insert_discount($discountid);

                //Additional bonus
                if ((int)$addBonus > 0) {

                    $pu_ht = price2num($addBonus, 'MU');

                    $desc = sprintf("%s - %s", $year, GETPOST("additional_message", 3));

                    $discountid = $soc->set_remise_except($pu_ht, $user, $desc, 0);
                    $object->insert_discount($discountid);
                }

                $ret = $object->fetch($id);
                $result = $object->generateDocument($documentModel, $langs, $hidedetails, $hidedesc, $hideref);

                // Validate
                $object->fetch($id);
                $object->validate($user);

                // Generate document
                $object->fetch($id);
                $result = $object->generateDocument($documentModel, $langs, $hidedetails, $hidedesc, $hideref);

            }

            if ($result > 0) {
                dol_htmloutput_mesg("Facture créées");
            } else {
                dol_htmloutput_errors("Note de frais non créée");
            }
        }

    } else {
        //Quarter not yet finished
        dol_htmloutput_errors("L'année n'est pas encore finie !");
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
        DOL_URL_ROOT . "/flightlog/generateBilling.php?year=" . $currentFlightYear,
        $currentFlightYear,
        "tab_" . $currentFlightYear
    ];
}

if (!$t1->service || !$t2->service || !$t3->service || !$t4->service || !$t5->service || !$t6->service || !$t7->service) {
    dol_htmloutput_mesg("Un service n'a pas été configuré", '', 'warning');
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


        <!-- Additional Point message -->
        <label>Message de réduction pour points supplémentaire (Commun à toutes les factures)</label><br/>
        <textarea name="additional_message" wrap="soft" class="quatrevingtpercent" rows="2">
            Points additionel (cf.annexe du ROI)
        </textarea>
        <br/>
        <br/>

        <!-- Billing type -->
        <label><?= $langs->trans("Type de facture"); ?></label><br/>
        <input type="radio" id="radio_standard" name="type" value="0" checked="checked"/>
        <?= $form->textwithpicto($langs->trans("InvoiceStandardAsk"), $langs->transnoentities("InvoiceStandardDesc"), 1,
            'help', '', 0, 3) ?>
        <br/>
        <br/>

        <!-- Payment mode -->
        <label><?= $langs->trans("Mode de payement"); ?></label><br/>
        <?php $form->select_types_paiements(0, 'mode_reglement_id', 'CRDT'); ?>
        <br/>
        <br/>

        <!-- Payment condition -->
        <label><?= $langs->trans("Condition de payement"); ?></label><br/>
        <?php $form->select_conditions_paiements(0, 'cond_reglement_id'); ?>
        <br/>
        <br/>

        <!-- bank account -->
        <label><?= $langs->trans("Compte en banque"); ?></label><br/>
        <?php $form->select_comptes(0, 'fk_account', 0, '', 1); ?>
        <br/>
        <br/>

        <!-- Public note -->
        <label><?= $langs->trans("Note publique (commune à toutes les factures)"); ?></label><br/>
        <textarea name="public_note" wrap="soft" class="quatrevingtpercent" rows="2">
            Les vols sont facturés comme le stipule l'annexe du ROI.
        </textarea>
        <br/>
        <br/>

        <!-- Private note -->
        <label><?= $langs->trans("Note privée (commune à toutes les factures)"); ?></label><br/>
        <textarea name="private_note" wrap="soft" class="quatrevingtpercent" rows="2">
            Aux points de vols, s'ajoutent une indemnité pour les membres du CA/CD de 300 points.
        </textarea>
        <br/>

        <!-- model document -->
        <label><?= $langs->trans("Model de document "); ?></label><br/>
        <?php $liste = ModelePDFFactures::liste_modeles($db); ?>
        <?= $form->selectarray('model', $liste, $conf->global->FACTURE_ADDON_PDF); ?>
        <br/>
        <br/>

        <?php if ($year >= $currentYear || !$t1->service || !$t2->service || !$t3->service || !$t4->service || !$t5->service || !$t6->service || !$t7->service) : ?>
            <a class="butActionRefused" href="#">Générer</a>
        <?php else: ?>
            <button class="butAction" type="submit">Générer</button>
        <?php endif; ?>

    </form>

<?php
llxFooter();