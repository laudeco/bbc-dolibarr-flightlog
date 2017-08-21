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

dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/adherents/class/adherent.class.php');
dol_include_once("/flightlog/lib/flightLog.lib.php");
dol_include_once("/flightlog/class/bbctypes.class.php");
dol_include_once("/flightlog/class/bbcvols.class.php");
dol_include_once("/product/class/product.class.php");
dol_include_once('/core/modules/facture/modules_facture.php');
dol_include_once('/fourn/class/fournisseur.class.php');

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
$puFlight = $flight->cost / $flight->nbrPax;

$organisator = new User($db);
$organisator->fetch($flight->fk_organisateur);

$receiver = new User($db);
$receiver->fetch($flight->fk_receiver);

$pilot = new User($db);
$pilot->fetch($flight->fk_pilot);

$adherent = new Adherent($db);
$adherent->fetch($pilot->fk_member);

$customer = new Fournisseur($db);
$customer->fetch($conf->global->BBC_FLIGHT_DEFAULT_CUSTOMER ?: $adherent->fk_soc);

//Query

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
if (!$conf->facture->enabled || !$user->rights->flightlog->vol->financial || !$user->rights->flightlog->vol->financialGenerateDocuments) {
    accessforbidden();
}

// Default action
if (empty($action)) {
    $action = EXPENSE_REPORT_GENERATOR_ACTION_CREATE;
}

/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */

if ($action == EXPENSE_REPORT_GENERATOR_ACTION_GENERATE) {
    if (empty($documentModel) || $conditionReglement == 0 || empty($conditionReglement) || $modeReglement == 0 || empty($modeReglement) || !$flight) {
        dol_htmloutput_errors("Erreur de configuration !");
    } else {
        $object = new Facture($db);
        $object->fetch_thirdparty();

        $object->socid = $customer->id;
        $object->type = $type;
        $object->number = "provisoire";
        $object->date = $flight->date;
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

        if ($id <= 0) {
            setEventMessages($object->error, $object->errors, 'errors');
        }

        $localtax1_tx = get_localtax(0, 1, $object->thirdparty);
        $localtax2_tx = get_localtax(0, 2, $object->thirdparty);

        $pu_ht = price2num($flightProduct->price, 'MU');
        $pu_ttc = price2num($flightProduct->price_ttc, 'MU');
        $pu_ht_devise = price2num($flightProduct->price, 'MU');


        $discount = ($flightProduct->price_ttc - $puFlight) * 100 / $flightProduct->price_ttc;

        $result = $object->addline(
            $flightProduct->description,
            $pu_ht,
            $flight->nbrPax,
            0,
            $localtax1_tx,
            $localtax2_tx,
            $t3->service->id,
            $discount,
            $flight->date,
            $flight->date,
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
            $flightProduct->label,
            [],
            100,
            '',
            0,
            0
        );

        $object->add_object_linked('flightlog_bbcvols', $flight->getId());

        $ret = $object->fetch($id);
        $result = $object->generateDocument($documentModel, $langs, $hidedetails, $hidedesc, $hideref);

        // Validate
        $object->fetch($id);
        $object->validate($user);

        // Generate document
        $object->fetch($id);
        $result = $object->generateDocument($documentModel, $langs, $hidedetails, $hidedesc, $hideref);

        if ($result > 0) {
            dol_htmloutput_mesg("Facture créées");

            $flight->is_facture = true;
            $flight->update($user);

            Header("Location: card.php?id=" . $flight->getId());
            return;
        } else {
            dol_htmloutput_errors("Facture non créée");
        }
    }
}

/*
 * VIEW
 *
 * Put here all code to build page
 */
llxHeader('', $langs->trans('Generate billing'), '');
print load_fiche_titre("Créer facture");


$form = new Form($db);

if (!$flightProduct) {
    dol_htmloutput_mesg("Le produit -vol- n'est pas configuré", '', 'warning');
}

if ($puFlight > $flightProduct->total_ttc) {
    dol_htmloutput_mesg("Le prix unitaire encodé pour ce vol est suppérieur au prix unitaire du produit", '',
        'warning');
}

if ($pilot->id != $receiver->id || $pilot->id != $organisator->id) {
    dol_htmloutput_mesg("L'organisateur / la personne ayant reçu l'argent n'est pas le pilote.", '',
        'warning');
}

?>

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
            <td> <?php echo $flight->nbrPax ?> </td>
        </tr>

        <tr>
            <td class="fieldrequired"><?php echo $langs->trans("Fieldis_facture") ?> </td>
            <td> <?php echo $flight->getLibStatut(5) ?> </td>
        </tr>

        <tr>
            <td class="fieldrequired">Prix standard</td>
            <td> <?php echo $flightProduct->total_ttc . " " . $langs->getCurrencySymbol($conf->currency) ?> </td>
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

    <br/>
    <br/>

    <form method="POST">

        <!-- action -->
        <input type="hidden" name="action" value="<?= EXPENSE_REPORT_GENERATOR_ACTION_GENERATE ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <!-- Billing type -->
        <label><?= $langs->trans("Type de facture"); ?></label><br/>
        <input type="radio" id="radio_standard" name="type" value="0" checked="checked"/>
        <?= $form->textwithpicto($langs->trans("InvoiceStandardAsk"), $langs->transnoentities("InvoiceStandardDesc"), 1,
            'help', '', 0, 3) ?>
        <br/>
        <br/>

        <!-- Payment mode -->
        <label><?= $langs->trans("Mode de payement"); ?></label><br/>
        <?php $form->select_types_paiements($customer->mode_reglement_id, 'mode_reglement_id', 'CRDT'); ?>
        <br/>
        <br/>

        <!-- Payment condition -->
        <label><?= $langs->trans("Condition de payement"); ?></label><br/>
        <?php $form->select_conditions_paiements($customer->cond_reglement_id, 'cond_reglement_id'); ?>
        <br/>
        <br/>

        <!-- bank account -->
        <label><?= $langs->trans("Compte en banque"); ?></label><br/>
        <?php $form->select_comptes($customer->fk_account, 'fk_account', 0, '', 1); ?>
        <br/>
        <br/>

        <!-- Public note -->
        <label><?= $langs->trans("Note publique"); ?></label><br/>
        <textarea name="public_note" wrap="soft" class="quatrevingtpercent" rows="2">
        Vol (identifiant : <?php echo $flight->getId(); ?>) de <?php echo $flight->lieuD; ?>
            à <?php echo $flight->lieuA; ?> avec <?php echo $pilot->getFullName($langs); ?>
        </textarea>
        <br/>
        <br/>

        <!-- Private note -->
        <label><?= $langs->trans("Note privée"); ?></label><br/>
        <textarea name="private_note" wrap="soft" class="quatrevingtpercent" rows="2">
        </textarea>
        <br/>

        <!-- model document -->
        <label><?= $langs->trans("Model de document "); ?></label><br/>
        <?php $liste = ModelePDFFactures::liste_modeles($db); ?>
        <?= $form->selectarray('model', $liste, $conf->global->FACTURE_ADDON_PDF); ?>
        <br/>
        <br/>

        <?php if (!$flightProduct) : ?>
            <a class="butActionRefused" href="#">Générer</a>
        <?php else: ?>
            <button class="butAction" type="submit">Générer</button>
        <?php endif; ?>

        <a class="butAction" href="<?php echo DOL_URL_ROOT . '/flightlog/card.php?id=' . $flight->id; ?>">Retour au
            vol</a>

    </form>

<?php
llxFooter();