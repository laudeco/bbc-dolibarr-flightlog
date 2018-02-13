<?php
/**
 * When a user generates the expense report for all pilots
 */
define("EXPENSE_REPORT_GENERATOR_ACTION_GENERATE", "generate");

/**
 * When a user changes dates (year / Month)
 */
define("EXPENSE_REPORT_GENERATOR_ACTION_CHANGE_DATES", "refresh");

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

dol_include_once('/flightlog/command/CommandHandler.php');
dol_include_once('/flightlog/command/CommandInterface.php');
dol_include_once('/flightlog/command/CreateMonthBillCommand.php');
dol_include_once('/flightlog/command/CreateMonthBillCommandHandler.php');
dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/adherents/class/adherent.class.php');
dol_include_once("/flightlog/lib/flightLog.lib.php");
dol_include_once("/flightlog/class/bbctypes.class.php");
dol_include_once("/product/class/product.class.php");
dol_include_once('/core/modules/facture/modules_facture.php');
dol_include_once('/flightlog/query/MonthlyBillableQuery.php');
dol_include_once('/flightlog/query/MonthlyBillableQueryHandler.php');

global $db, $langs, $user, $conf;

//variables
$currentYear = date('Y');
$currentMonth = date('m');

// Load translation files required by the page
$langs->load("mymodule@mymodule");
$langs->load("trips");
$langs->load("bills");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$year = GETPOST('year', 'int', 3) ?: $currentYear;
$month = GETPOST('month', 'int', 3) ?: $currentMonth - 1;

//post parameters
$publicNote = GETPOST('public_note', 'alpha', 2);
$privateNote = GETPOST('private_note', 'alpha', 2);
$type = GETPOST("type", "int", 3);

//Query
$queryHandler = new MonthlyBillableQueryHandler($db, $conf->global);
$query = new MonthlyBillableQuery($month, $year);
$queryResult = $queryHandler->__invoke($query);

$handler = new CreateMonthBillCommandHandler($db, $conf->global, $user, $langs);

//pdf
$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

//service

// Access control
if (!$conf->facture->enabled || !$user->rights->flightlog->vol->status || !$user->rights->flightlog->vol->financialGenerateDocuments) {
    accessforbidden();
}

// Default action
if (empty($action)) {
    $action = EXPENSE_REPORT_GENERATOR_ACTION_SELECT;
}

llxHeader('', $langs->trans('Generate billing'), '');
print load_fiche_titre("Générer factures pilote");
print '<div class="bbc-style">';

/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */
if ($action == EXPENSE_REPORT_GENERATOR_ACTION_GENERATE) {
    try {

        $command = new CreateMonthBillCommand( $type, $publicNote, $privateNote, $year, $month);
        $handler->handle($command);
        dol_htmloutput_mesg('Génération : OK');
    } catch (Exception $e) {
        dol_syslog($e->getMessage(), LOG_ERR);
        dol_htmloutput_mesg('Erreur pendant la génération.', '', 'error');
    }

}

/*
 * VIEW
 *
 * Put here all code to build page
 */

$form = new Form($db);

?>
    <section class="section">
        <h2 class="section-title"><?php echo $langs->trans('Période'); ?></h2>

        <form class="form-inline flight-form" method="POST">
            <section class="form-section">
                <input type="hidden" name="action" value="<?php echo EXPENSE_REPORT_GENERATOR_ACTION_CHANGE_DATES ?>">

                <!-- Year -->
                <div class="form-group">
                    <label><?php echo $langs->trans('Année') ?></label>
                    <select name="year">
                        <?php for ($selectYearOption = $currentYear; $selectYearOption >= $currentYear - 6; $selectYearOption--): ?>
                            <option value="<?php echo $selectYearOption; ?>" <?php echo $selectYearOption == $year ? 'selected' : '' ?>><?php echo $selectYearOption; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Year -->
                <div class="form-group">
                    <label><?php echo $langs->trans('Mois') ?></label>
                    <select name="month">
                        <?php for ($selectMonthOption = 1; $selectMonthOption <= 12; $selectMonthOption++): ?>
                            <option value="<?php echo $selectMonthOption; ?>" <?php echo $selectMonthOption == $month ? 'selected' : '' ?>><?php echo $selectMonthOption; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </section>

            <button class="butAction" type="submit"><?php echo $langs->trans('refresh'); ?></button>
        </form>
    </section>

    <section class="section">
        <h2 class="section-title"><?php echo $langs->trans('Generation des factures') ?></h2>

        <div>
            <p>
                <?php echo $langs->trans("Comprends les vols non facturés pour le mois demandé."); ?>
            </p>
        </div>

        <form action="#" method="POST">

            <!-- action -->
            <input type="hidden" name="action" value="<?php echo EXPENSE_REPORT_GENERATOR_ACTION_GENERATE ?>">
            <input type="hidden" name="month" value="<?php echo $month ?>">
            <input type="hidden" name="year" value="<?php echo $year ?>">

            <table class="border _width50">
                <thead>
                <tr>
                    <td><?php echo $langs->trans('pilote'); ?></td>
                    <td><?php echo $langs->trans('Nombre de vols'); ?></td>
                    <td><?php echo $langs->trans('Montant'); ?></td>
                    <td><?php echo $langs->trans('Moyenne / passager'); ?></td>
                </tr>
                </thead>

                <tbody>
                <?php if ($queryResult->isEmpty()): ?>
                    <tr>
                        <td colspan="4" class="_alignCenter _info"><?php echo $langs->trans('Nous n\'avons pas trouvé de vol pour la période demandée.'); ?></td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($queryResult->getFlights() as $monthlyFlightBill): ?>
                    <tr>
                        <td><?php echo $monthlyFlightBill->getReceiver(); ?></td>
                        <td><?php echo $monthlyFlightBill->getFlightsCount(); ?></td>
                        <td class="_alignRight"><?php echo price($monthlyFlightBill->getTotal(), 0, '', 1, 1); ?>€</td>
                        <td class="_alignRight"><?php echo price($monthlyFlightBill->getAverageByPax(), 0, '', 1, 1); ?>€</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>


            <!-- Bill type -->
            <label><?= $langs->trans("Type de facture"); ?></label><br/>
            <input type="radio" id="radio_standard" name="type" value="0" checked="checked"/>
            <?= $form->textwithpicto($langs->trans("InvoiceStandardAsk"),
                $langs->transnoentities("InvoiceStandardDesc"), 1,
                'help', '', 0, 3) ?>
            <br/>
            <br/>

            <!-- Public note -->
            <label><?= $langs->trans("Note publique (commune à toutes les factures)"); ?></label><br/>
            <textarea name="public_note" wrap="soft" class="quatrevingtpercent" rows="2"></textarea>
            <br/>
            <br/>

            <!-- Private note -->
            <label><?= $langs->trans("Note privée (commune à toutes les factures)"); ?></label><br/>
            <textarea name="private_note" wrap="soft" class="quatrevingtpercent" rows="2"></textarea>
            <br/>

            <?php if ($queryResult->isEmpty() || $year > $currentYear || ($year == $currentYear && $month >= $currentMonth)) : ?>
                <p class="warning">
                    <?php echo $langs->trans('La periode demandée n\'est pas cloturée.') ?>
                </p>
                <a class="butActionRefused" href="#">Générer</a>
            <?php else: ?>
                <button class="butAction" type="submit">Générer</button>
            <?php endif; ?>

        </form>
    </section>
    </div>
    <!-- end bbc style -->
<?php
llxFooter();