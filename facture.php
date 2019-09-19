<?php
/**
 * When a user generates the expense report for all pilots
 */
define("EXPENSE_REPORT_GENERATOR_ACTION_GENERATE", "generate");

/**
 * When a user has to select year and quartil
 */
define("EXPENSE_REPORT_GENERATOR_ACTION_CREATE", "select");

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

dol_include_once('/core/modules/facture/modules_facture.php');
dol_include_once('/adherents/class/adherent.class.php');
dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/flightballoon/bbc_ballons.class.php');
dol_include_once("/product/class/product.class.php");
dol_include_once('/fourn/class/fournisseur.class.php');
dol_include_once("/flightlog/flightlog.inc.php");

global $db, $langs, $user, $conf;

// Load translation files required by the page
$langs->load("trips");
$langs->load("bills");
$langs->load("mymodule@flightlog");
$langs->load("other");

// Get parameters
$id = GETPOST('id', 'int', 3);
$action = GETPOST('action', 'alpha');
$year = GETPOST('year', 'int', 3);

//post parameters
$customerId = GETPOST('customerid', 'int');
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
$flightProduct = new Product($db);
$flightProduct->fetch($conf->global->BBC_FLIGHT_TYPE_CUSTOMER);

$flight = new Bbcvols($db);
$flight->fetch($id);
$puFlight = $flight->getAmountPerPassenger();

$organisator = new User($db);
$organisator->fetch($flight->fk_organisateur);

$receiver = new User($db);
$receiver->fetch($flight->fk_receiver);
$memberReceiver = new Adherent($db);
$memberReceiver->fetch($receiver->fk_member);

$pilot = new User($db);
$pilot->fetch($flight->fk_pilot);

$adherent = new Adherent($db);
$adherent->fetch($pilot->fk_member);

$customer = new Fournisseur($db);
$customer->fetch($conf->global->BBC_FLIGHT_DEFAULT_CUSTOMER ?: $adherent->fk_soc);

$balloon = new Bbc_ballons($db);
$balloon->fetch($flight->BBC_ballons_idBBC_ballons);
$handler = CreateFlightBillCommandHandlerFactory::factory($db, $conf->global, $user, $langs);

//Query

//pdf
$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc',
    'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref', 'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));
$nbrPax = (GETPOST('nbr_pax', 'int') ? GETPOST('nbr_pax', 'int') : null);

$object = new Facture($db);
$vatrate = "0.000";

// Access control
if (!$conf->facture->enabled || !$user->rights->flightlog->vol->financial || !$user->rights->flightlog->vol->financialGenerateDocuments) {
    accessforbidden();
}

// Default action
if (empty($action)) {
    $action = EXPENSE_REPORT_GENERATOR_ACTION_CREATE;
}


/*
 * VIEW
 *
 * Put here all code to build page
 */
llxHeader('', $langs->trans('Generate billing'), '');


/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */
if ($action == EXPENSE_REPORT_GENERATOR_ACTION_GENERATE) {
    try{
        $command = new CreateFlightBillCommand($flight->getId(), $modeReglement, $conditionReglement, $documentModel, $type, $publicNote, $privateNote,$bankAccount, $nbrPax, $customerId);
        $handler->handle($command);
    }catch (\Exception $e){
        dol_syslog($e->getMessage(),LOG_ERR);
        dol_htmloutput_mesg("Facture non créée", '', 'error');
    }
}

print load_fiche_titre("Créer facture");
$form = new Form($db);

if (!$flightProduct) {
    dol_htmloutput_mesg("Le produit -vol- n'est pas configuré", '', 'warning');
}

if ($puFlight > $flightProduct->price_ttc) {
    dol_htmloutput_mesg("Le prix unitaire encodé pour ce vol est suppérieur au prix unitaire du produit", '',
        'warning');
}

if ($pilot->id != $receiver->id || $pilot->id != $organisator->id) {
    dol_htmloutput_mesg("L'organisateur / la personne ayant reçu l'argent n'est pas le pilote.", '',
        'warning');
}
if (!$flight->hasReceiver()) {
    dol_htmloutput_mesg("Personne n'aurait touché l'argent.", '',
        'error');
}

?>

    <form method="POST">
        <table class="border centpercent">

            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("FieldidBBC_vols") ?> </td>
                <td> <?php echo $flight->idBBC_vols ?> </td>
            </tr>
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("Fielddate") ?> </td>
                <td> <?php echo dol_print_date($flight->date) ?> </td>
            </tr>
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("FieldBBC_ballons_idBBC_ballons") ?> </td>
                <td> <?php echo $balloon->immat ?> </td>
            </tr>

            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("Fieldfk_pilot") ?> </td>
                <td> <?php echo $pilot->getNomUrl() ?> </td>
            </tr>
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("Fieldfk_organisateur") ?> </td>
                <td> <?php echo $organisator->getNomUrl() ?> </td>
            </tr>
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("Fieldfk_receiver") ?> </td>
                <td> <?php echo $receiver->getNomUrl() ?> </td>
            </tr>

            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("FieldnbrPax") ?> </td>
                <td>
                    <input type="number" name="nbr_pax" value="<?php echo $flight->nbrPax ?>" />
                </td>
            </tr>

            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("Fieldis_facture") ?> </td>
                <td> <?php echo $flight->getLibStatut(5) ?> </td>
            </tr>

            <tr>
                <td class="fieldrequired">Prix standard</td>
                <td> <?php echo $flightProduct->price_ttc . " " . $langs->getCurrencySymbol($conf->currency) ?> </td>
            </tr>
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("Fieldcost") ?> </td>
                <td> <?php echo $flight->cost . " " . $langs->getCurrencySymbol($conf->currency) ?> </td>
            </tr>
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans("UnitPrice") ?> </td>
                <td> <?php echo $puFlight . " " . $langs->getCurrencySymbol($conf->currency) ?> </td>
            </tr>
        </table>

        <br>
        <br>

        <!-- action -->
        <input type="hidden" name="action" value="<?= EXPENSE_REPORT_GENERATOR_ACTION_GENERATE ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <table>
            <tr>
                <td class="">
                    <?php echo $langs->trans('Commanditaire'); ?>
                </td>

                <td>
                    <?php print $form->select_company($memberReceiver->fk_soc, 'customerid', '((s.client = 1 OR s.client = 3) AND s.status=1)', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300'); ?>
                </td>

            </tr>

            <!-- Billing type -->
            <tr>
                <td><label for="type"><?= $langs->trans("Type de facture"); ?></label></td>

                <td><input type="radio" id="radio_standard" name="type" value="0" checked="checked"/>
                    <?php echo $form->textwithpicto($langs->trans("InvoiceStandardAsk"), $langs->transnoentities("InvoiceStandardDesc"), 1,
                        'help', '', 0, 3) ?>
                </td>
            </tr>

            <!-- Payment mode -->
            <tr>
                <td><label><?php echo $langs->trans("Mode de payement"); ?></label></td>
                <td><?php $form->select_types_paiements($customer->mode_reglement_id, 'mode_reglement_id', 'CRDT'); ?></td>
            </tr>

            <!-- Payment condition -->
            <tr>
                <td>
                    <label><?= $langs->trans("Condition de payement"); ?></label>
                </td>
                <td>
                    <?php $form->select_conditions_paiements($customer->cond_reglement_id, 'cond_reglement_id'); ?>
                </td>
            </tr>

            <!-- bank account -->
            <tr>
                <td>
                    <label><?php echo $langs->trans("Compte en banque"); ?></label>
                </td>

                <td>
                    <?php $form->select_comptes($customer->fk_account, 'fk_account', 0, '', 1); ?>
                </td>

            </tr>


            <!-- Public note -->
            <tr>
                <td>
                    <label><?= $langs->trans("Note publique"); ?></label>
                </td>

                <td>
                <textarea name="public_note" wrap="soft" class="quatrevingtpercent" rows="2">
                    Vol (identifiant : <?php echo $flight->getId(); ?>) de <?php echo $flight->lieuD; ?>
                    à <?php echo $flight->lieuA; ?> avec <?php echo $pilot->getFullName($langs); ?>
                </textarea>
                </td>


            </tr>

            <!-- Private note -->
            <tr>
                <td>
                    <label><?= $langs->trans("Note privée"); ?></label>
                </td>

                <td>
                    <textarea name="private_note" wrap="soft" class="quatrevingtpercent" rows="2"></textarea>
                </td>
            </tr>

            <!-- model document -->
            <tr>
                <td>
                    <label><?= $langs->trans("Model de document "); ?></label>
                </td>
                <td>
                    <?php $liste = ModelePDFFactures::liste_modeles($db); ?>
                    <?= $form->selectarray('model', $liste, $conf->global->FACTURE_ADDON_PDF); ?>
                </td>
            </tr>

        </table>


        <?php if (!$flightProduct || !$flight->hasReceiver()) : ?>
            <a class="butActionRefused" href="#">Générer</a>
        <?php else: ?>
            <button class="butAction" type="submit">Générer</button>
        <?php endif; ?>

        <a class="butAction" href="<?php echo DOL_URL_ROOT . '/flightlog/card.php?id=' . $flight->id; ?>">Retour au
            vol</a>

    </form>

<?php
llxFooter();