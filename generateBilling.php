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
dol_include_once('/flightlog/flightlog.inc.php');
dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/adherents/class/adherent.class.php');
dol_include_once("/flightlog/lib/flightLog.lib.php");
dol_include_once("/flightlog/class/bbctypes.class.php");
dol_include_once("/product/class/product.class.php");
dol_include_once('/core/modules/facture/modules_facture.php');
dol_include_once('/flightlog/query/BillableFlightQuery.php');
dol_include_once('/flightlog/query/BillableFlightQueryHandler.php');
dol_include_once('/flightlog/command/CreatePilotYearBillCommand.php');
dol_include_once('/flightlog/command/CreatePilotYearBillCommandHandler.php');


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
$flightTypes = [
    '1' => $t1,
    '2' => $t2,
    '3' => $t3,
    '4' => $t4,
    '5' => $t5,
    '6' => $t6,
    '7' => $t7,
];

//Query
$flightYears = getFlightYears();

//pdf
$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

$object = new Facture($db);

//service
$tableQueryHandler = new BillableFlightQueryHandler($db, $conf->global);
$billHandler = new CreatePilotYearBillCommandHandler($db, $conf->global, $user, $langs, $flightTypes);

// Access control
if (!$conf->facture->enabled || !$user->rights->flightlog->vol->status || !$user->rights->flightlog->vol->financialGenerateDocuments) {
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
            /**
             * @var Pilot $value
             */
            $flights = $tableQueryHandler->__invoke(new BillableFlightQuery(true, $year));
            foreach ($flights as $currentMissionUserId => $value) {

                $addBonus = (int) $additionalBonus[$currentMissionUserId];
                if ($addBonus < 0) {
                    dol_htmloutput_mesg("Facture ignorée " . $value->getName(), '', 'warning');
                    continue;
                }

                if (!$value->isBillable(FlightBonus::zero()->addPoints(FlightPoints::create($addBonus)))) {
                    dol_htmloutput_mesg("Facture ignorée car à 0.00 €" . $value->getName(), '',
                        'warning');
                    continue;
                }

                $command = new CreatePilotYearBillCommand(
                    $value,
                    $type,
                    $privateNote,
                    $publicNote,
                    $documentModel,
                    $conditionReglement,
                    $modeReglement,
                    $bankAccount,
                    $year,
                    GETPOST("additional_message", 3),
                    $addBonus
                );
                $billHandler->__invoke($command);

            }
            dol_htmloutput_mesg("Facture créées");


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
    <div>
        <p>
            Pour ignorer une ligne, il faut mettre un montant négatif en points additionel.
        </p>
    </div>
    <form method="POST">

        <!-- action -->
        <input type="hidden" name="action" value="<?php echo EXPENSE_REPORT_GENERATOR_ACTION_GENERATE ?>">

        <?php

        //tableau par pilote

        print '<div class="tabBar">';
        print '<table class="border" width="100%">';

        print '<tr class="liste_titre">';
        print '<td colspan="2">Nom</td>';
        print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 1 : Sponsor") . '</td>';
        print '<td class="liste_titre" colspan="2">' . $langs->trans("Type 2 : Baptême") . '</td>';
        print '<td class="liste_titre" colspan="2">' . $langs->trans("Organisateur_(T1/T2)") . '</td>';
        print '<td class="liste_titre" colspan="2">' . $langs->trans("Instructeur") . '</td>';
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

        $total = 0;
        /**
         * @var int   $key
         * @var Pilot $pilot
         */
        foreach ($tableQueryHandler->__invoke(new BillableFlightQuery(true, $year)) as $key => $pilot) {
            $total += $pilot->getTotalBill()->getValue();

            print '<tr class="pair">';
            print '<td>' . $key;
            print sprintf('<input type="hidden" name="pilot[%s]" value="%s" />', $pilot->getId(), $pilot->getId());
            print '</td>';

            print '<td>' . $pilot->getName() . '</td>';

            print '<td>' . $pilot->getCountForType('1')->getCount() . '</td>';
            print '<td>' . $pilot->getCountForType('1')->getCost()->getValue() . '</td>';

            print '<td>' . $pilot->getCountForType('2')->getCount() . '</td>';
            print '<td>' . $pilot->getCountForType('2')->getCost()->getValue() . '</td>';

            print '<td>' . $pilot->getCountForType('orga')->getCount() . '</td>';
            print '<td>' . $pilot->getCountForType('orga')->getCost()->getValue() . '</td>';

            print '<td>' . $pilot->getCountForType('orga_T6')->getCount() . '</td>';
            print '<td>' . $pilot->getCountForType('orga_T6')->getCost()->getValue() . '</td>';

            print '<td><b>' . $pilot->getFlightBonus()->getValue() . '</b></td>';
            print '<td>' . sprintf('<input type="number" value="0" name="additional_bonus[%s]"/>',
                    $pilot->getId()) . '</b></td>';

            print '<td>' . $pilot->getCountForType('3')->getCount() . '</td>';
            print '<td>' . price($pilot->getCountForType('3')->getCost()->getValue()) . '€</td>';

            print '<td>' . $pilot->getCountForType('4')->getCount() . '</td>';
            print '<td>' . price($pilot->getCountForType('4')->getCost()->getValue()) . '€</td>';

            print '<td>' . $pilot->getCountForType('5')->getCount() . '</td>';

            print '<td>' . $pilot->getCountForType('6')->getCount() . '</td>';
            print '<td>' . price($pilot->getCountForType('6')->getCost()->getValue()) . '€</td>';

            print '<td>' . $pilot->getCountForType('7')->getCount() . '</td>';
            print '<td>' . price($pilot->getCountForType('7')->getCost()->getValue()) . '€</td>';

            print '<td>';
            print sprintf('<input type="hidden" value="%d" name="amout[%d]"/>', $pilot->getFlightsCost()->getValue(),
                $pilot->getId());
            print price($pilot->getFlightsCost()->getValue());
            print '€ </td>';

            print '<td><b>';
            print sprintf('<input type="hidden" value="%d" name="amoutDiscount[%d]"/>',
                $pilot->getTotalBill()->getValue(), $pilot->getId());
            print price($pilot->getTotalBill()->getValue());
            print '€</b></td>';
            print '</tr>';

        }


        ?>

        <tr>
            <td colspan='19'></td>
            <td>Total à reçevoir</td>
            <td><?= price($total) ?>€</td>
        </tr>

        </table>


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