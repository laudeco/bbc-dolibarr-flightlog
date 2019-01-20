<?php
/* Copyright (C) 2007-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *    \file       flightlog/bbcvols_card.php
 *        \ingroup    flightlog
 *        \brief      This file is an example of a php page
 *                    Initialy built by build_class_from_table on 2017-02-09 11:10
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (!$res && file_exists("../main.inc.php")) {
    $res = @include '../main.inc.php';
}                    // to work if your module directory is into dolibarr root htdocs directory
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include '../../main.inc.php';
}            // to work if your module directory is into a subdir of root htdocs directory
if (!$res && file_exists("../../../dolibarr/htdocs/main.inc.php")) {
    $res = @include '../../../dolibarr/htdocs/main.inc.php';
}     // Used on dev env only
if (!$res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) {
    $res = @include '../../../../dolibarr/htdocs/main.inc.php';
}   // Used on dev env only
if (!$res) {
    die("Include of main fails");
}
// Change this following line to use the correct relative path from htdocs
include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');

dol_include_once('/flightlog/flightlog.inc.php');
dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');
dol_include_once('/flightlog/lib/card.lib.php');
dol_include_once('/flightlog/lib/PilotService.php');
dol_include_once('/flightballoon/class/bbc_ballons.class.php');
dol_include_once('/user/class/usergroup.class.php');

global $langs, $user, $conf;

const ACTION_FLAG_BILLED = 'action_flag_bill';
const ACTION_CONFIRM_FLAG_BILLED = 'confirm_flag_bill';

// Load traductions files requiredby by page
$langs->load("mymodule@flightlog");
$langs->load("other");

// Get parameters
$id = GETPOST('id', 'int') ?: GETPOST('idBBC_vols', 'int');
$action = GETPOST('action', 'alpha');
$cancel = GETPOST('cancel');
$backtopage = GETPOST('backtopage');
$myparam = GETPOST('myparam', 'alpha');

$isAllowedEdit = ($user->rights->flightlog->vol->advanced || $user->rights->flightlog->vol->edit || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id));
$isAllowedDelete = ($user->rights->flightlog->vol->delete || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id && !$object->is_facture));
$permissiondellink = $user->rights->flightlog->vol->financial;

$search_idBBC_vols = GETPOST('search_idBBC_vols', 'int');
$search_lieuD = GETPOST('search_lieuD', 'alpha');
$search_lieuA = GETPOST('search_lieuA', 'alpha');
$search_heureD = GETPOST('search_heureD', 'alpha');
$search_heureA = GETPOST('search_heureA', 'alpha');
$search_BBC_ballons_idBBC_ballons = GETPOST('search_BBC_ballons_idBBC_ballons', 'int');
$search_nbrPax = GETPOST('search_nbrPax', 'int');
$search_remarque = GETPOST('search_remarque', 'alpha');
$search_incidents = GETPOST('search_incidents', 'alpha');
$search_fk_type = GETPOST('search_fk_type', 'int');
$search_fk_pilot = GETPOST('search_fk_pilot', 'int');
$search_fk_organisateur = GETPOST('search_fk_organisateur', 'int');
$search_is_facture = GETPOST('search_is_facture', 'int');
$search_kilometers = GETPOST('search_kilometers', 'int');
$search_cost = GETPOST('search_cost', 'alpha');
$search_fk_receiver = GETPOST('search_fk_receiver', 'int');
$search_justif_kilometers = GETPOST('search_justif_kilometers', 'alpha');

$pageTitle = "Fiche vol " . $id;

if (!$user->rights->flightlog->vol->access) {
    accessforbidden($langs->trans("Tu n'as pas accès au vol"));
}

if (empty($action) && empty($id) && empty($ref)) {
    $action = 'view';
}

$object = new Bbcvols($db);
$extrafields = new ExtraFields($db);

$receiver = new User($db);

$pilotService = new PilotService($db);
$pilot = new User($db);

$organisator = new User($db);

$flightType = new Bbctypes($db);
$balloon = new Bbc_ballons($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php';  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

// Initialize technical object to manage hooks of modules. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('bbcvols'));
$object->ref = $object->idBBC_vols;
$receiver->fetch($object->fk_receiver);
$pilot->fetch($object->fk_pilot);
$organisator->fetch($object->fk_organisateur);
$flightType->fetch($object->fk_type);
$balloon->fetch($object->BBC_ballons_idBBC_ballons);

$formFlight = new \flightlog\form\FlightForm(new FlightValidator($langs, $db, $conf->global->BBC_FLIGHT_TYPE_CUSTOMER), $object, $db, $conf->global, $user);
$formFlight->bind($object);


if (($action == "update" || $action == "edit") && !($user->rights->flightlog->vol->advanced || $user->rights->flightlog->vol->edit || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id))) {
    setEventMessage("Ceci n'est pas un de tes vols tu ne peux l'editer ! ", 'errors');
    $action = 'view';
}

/*******************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 ********************************************************************/

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object,
    $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {

    // Action to update record
    if (($user->rights->flightlog->vol->advanced || $user->rights->flightlog->vol->edit || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id)) && $action == 'update') {

        $formFlight->setData($_POST);

        // action : edit
        if ($formFlight->validate()) {
            /** @var Bbcvols $object */
            $object = $formFlight->getObject();
            $result = $object->update($user);
            if ($result > 0) {
                $action = 'view';

                $object->id = $object->idBBC_vols;
                $receiver->fetch($object->fk_receiver);
                $pilot->fetch($object->fk_pilot);
                $organisator->fetch($object->fk_organisateur);
                $flightType->fetch($object->fk_type);
                $balloon->fetch($object->BBC_ballons_idBBC_ballons);

            } else {
                // Creation KO
                if (!empty($object->errors)) {
                    setEventMessages(null, $object->errors, 'errors');
                } else {
                    setEventMessages($object->error, null, 'errors');
                }
                $action = 'edit';
            }
        } else {
            $action = 'edit';
        }
    }

    // Action to delete
    if ($action == 'confirm_delete') {
        $result = $object->delete($user);
        if ($result > 0) {
            // Delete OK
            setEventMessages("RecordDeleted", null, 'mesgs');
            header("Location: " . dol_buildpath('/flightlog/list.php', 1));
            exit;
        } else {
            if (!empty($object->errors)) {
                setEventMessages(null, $object->errors, 'errors');
            } else {
                setEventMessages($object->error, null, 'errors');
            }
        }
    }

    // Action to delete
    if ($user->rights->flightlog->vol->financial && !$object->isBilled() && $action === ACTION_CONFIRM_FLAG_BILLED) {
        $result = $object
            ->bill()
            ->update($user);

        if ($result > 0) {
            setEventMessages("Facturé", null, 'mesgs');
            $action = 'show';
        } else {
            if (!empty($object->errors)) {
                setEventMessages(null, $object->errors, 'errors');
            } else {
                setEventMessages($object->error, null, 'errors');
            }
        }
    }
}


/***************************************************
 * VIEW
 *
 * Put here all code to build page
 ****************************************************/

llxHeader('', $pageTitle, '');

$form = new Form($db);

// Put here content of your page

print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';


// Part to edit record
if (($user->rights->flightlog->vol->edit || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id )) && ($id || $ref) && $action == 'edit'): ?>

    <?php $renderer = new \flightlog\form\SimpleFormRenderer(); ?>

    <div class="errors error-messages">
        <?php
        foreach ($formFlight->getErrorMessages() as $errorMessage) {
            print sprintf('<div class="error"><span>%s</span></div>', $errorMessage);
        }
        ?>
    </div>

    <form class="flight-form js-form" name='add' action="card.php?rowid=<?php echo $object->getId(); ?>" method="POST">
        <input type="hidden" name="action" value="update"/>

        <?php echo $renderer->render($formFlight->getElement('idBBC_vols')); ?>

        <?php if($formFlight->has('fk_type')): ?>
        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Type de vol'); ?></h1>
            <table class="border" width="50%">

                <tr>
                    <td class="fieldrequired" width="25%"> Type du vol</td>
                    <td ><?php echo $renderer->render($formFlight->getElement('fk_type')); ?></td>
                </tr>
            </table>
        </section>
        <?php endif; ?>

        <!-- Date et heures -->
        <?php if($formFlight->has('date')): ?>
            <section class="form-section">
                <h1 class="form-section-title"><?php echo $langs->trans('Date'); ?></h1>
                <table class="border" width="50%">
                    <tr>
                        <td class="fieldrequired" width="25%">Date du vol</td>
                        <td ><?php echo $renderer->render($formFlight->getElement('date')); ?></td>
                    </tr>
                </table>
            </section>
        <?php endif; ?>

        <?php if($formFlight->has('heureD') && $formFlight->has('heureA')): ?>
            <section class="form-section">
                <h1 class="form-section-title"><?php echo $langs->trans('Heures'); ?></h1>
                <table class="border" width="50%">
                    <tr>
                        <td class="fieldrequired" width="25%">Heure du décollage</td>
                        <td ><?php echo $renderer->render($formFlight->getElement('heureD')); ?></td>
                    </tr>
                    <tr>
                        <td class="fieldrequired" width="25%">Heure de l'atterrissage</td>
                        <td ><?php echo $renderer->render($formFlight->getElement('heureA')); ?></td>
                    </tr>
                </table>
            </section>
        <?php endif; ?>

        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Pilote & ballon') ?></h1>
            <table class="border" width="50%">

                <?php if($formFlight->has('fk_pilot')): ?>
                <tr>
                    <td class="fieldrequired"> Pilote </td>
                    <td><?php echo $renderer->render($formFlight->getElement('fk_pilot'),['ajax' => true]); ?></td>
                </tr>
                <?php endif; ?>

                <?php if($formFlight->has('BBC_ballons_idBBC_ballons')): ?>
                <tr>
                    <td width="25%" class="fieldrequired">Ballon</td>
                    <td><?php echo $renderer->render($formFlight->getElement('BBC_ballons_idBBC_ballons')); ?></td>
                </tr>
                <?php endif; ?>

                <tr>
                    <td>Il y'avait-il plusieurs ballons ?</td>
                    <td colspan="3"><input type="checkbox" value="1" name="grouped_flight"/> - Oui</td>
                </tr>
            </table>
        </section>

        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Lieux') ?></h1>
            <table class="border" width="100%">
                <?php

                //place start
                print "<tr>";
                print '<td class="fieldrequired">Lieu de d&#233;part </td><td width="25%" >';
                print $renderer->render($formFlight->getElement('lieuD'));
                print '</td>';

                //place end
                print '<td class="fieldrequired">Lieu d\'arriv&#233;e </td><td>';
                print $renderer->render($formFlight->getElement('lieuA'));
                print '</td></tr>';

                ?>

            </table>
        </section>

        <?php if($formFlight->has('fk_organisateur')): ?>
            <section class="form-section">
                <h1 class="form-section-title"><span class="js-organisator-field">Organisateur</span></h1>
                <table class="border" width="50%">
                    <tr>
                        <td class="fieldrequired"><span class="js-organisator-field">Organisateur</span></td>
                        <td>
                            <?php
                            //organisateur
                            print $renderer->render($formFlight->getElement('fk_organisateur'),['ajax' => true]);
                            ?>
                        </td>
                    </tr>
                </table>
            </section>
        <?php endif; ?>


        <section class="form-section js-expensable-field">
            <h1 class="form-section-title"><?php echo $langs->trans('Déplacements') ?></h1>
            <table class="border" width="50%">
                <?php if($formFlight->has('kilometers')): ?>
                    <!-- number of kilometers done for the flight -->
                    <tr>
                        <td class="fieldrequired">Nombre de kilometres effectués pour le vol</td>
                        <td>
                            <?php print $renderer->render($formFlight->getElement('kilometers')); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if($formFlight->has('justif_kilometers')): ?>
                    <!-- Justif Kilometers -->
                    <tr>

                        <td width="25%" class="fieldrequired">Justificatif des KM</td>
                        <td>
                            <?php print $renderer->render($formFlight->getElement('justif_kilometers')); ?>
                        </textarea>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </section>

        <!-- Passagers -->
        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Passager') ?></h1>
            <table class="border" width="50%">
                <?php if($formFlight->has('nbrPax')): ?>
                    <tr>
                        <td class="fieldrequired"><?php echo $langs->trans('Nombre de passagers'); ?></td>
                        <td>
                            <?php print $renderer->render($formFlight->getElement('nbrPax')); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if($formFlight->has('passengerNames')): ?>
                    <!-- passenger names -->
                    <tr>
                        <td width="25%" class="fieldrequired"><?php echo $langs->trans('Noms des passagers'); ?><br/>(Séparé
                            par des ; )
                        </td>
                        <td>
                            <?php print $renderer->render($formFlight->getElement('passengerNames')); ?>
                        </td>
                    </tr>
                <?php endif;?>
            </table>
        </section>

        <!-- billing information -->
        <section class="form-section">
            <h1 class="form-section-title js-billable-field"><?php echo $langs->trans('Facturation') ?></h1>
            <table class="border" width="50%">

                <?php if($formFlight->has('fk_receiver')): ?>
                    <!-- Money receiver -->
                    <tr class="js-hide-order js-billable-field">
                        <td class="fieldrequired"><?php echo $langs->trans('Qui a perçu l\'argent') ?></td>
                        <td>
                            <?php print $renderer->render($formFlight->getElement('fk_receiver'),['ajax' => true]); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if($formFlight->has('cost')): ?>
                    <!-- Flight cost -->
                    <tr class="js-hide-order js-billable-field">
                        <td class="fieldrequired">Montant perçu</td>
                        <td>
                            <?php print $renderer->render($formFlight->getElement('cost')); ?>
                            &euro;
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </section>

        <!-- comments -->
        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Commentaires') ?></h1>
            <table class="border" width="50%">

                <?php if($formFlight->has('remarque')): ?>
                    <!-- commentaires -->
                    <tr class="">
                        <td class="fieldrequired"> Commentaire</td>
                        <td>
                            <?php print $renderer->render($formFlight->getElement('remarque')); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if($formFlight->has('incidents')): ?>
                    <!-- incidents -->
                    <tr class="">
                        <td class="fieldrequired"> incidents</td>
                        <td>
                            <?php print $renderer->render($formFlight->getElement('incidents')); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </section>

        <button class="button" type="submit">Modifier</button>
        <a href="card.php?id=<?php echo $object->getId(); ?>" class="btn button button-a">Annuler</a>
    </form>

<?php endif;


// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
    $res = $object->fetch_optionals($object->id, $extralabels);

    $head = prepareFlightTabs($object);

    dol_fiche_head($head, 'general', $langs->trans("Vol"));

    $linkback = '<a href="' . DOL_URL_ROOT . '/flightlog/list.php">' . $langs->trans("BackToList") . '</a>';
    print $form->showrefnav($object, "idBBC_vols", $linkback, true, "idBBC_vols");

    if ($action == 'delete') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteMyOjbect'),
            $langs->trans('êtes-vous sure de vouloir supprimer ce vol ?'), 'confirm_delete', '', 0, 1);
        print $formconfirm;
    } elseif ($user->rights->flightlog->vol->financial  && $action == ACTION_FLAG_BILLED) {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id,
            $langs->trans('Marque comme facturé'),
            $langs->trans('Ce vol va être marqué comme facturé, est-ce bien le cas ?'), ACTION_CONFIRM_FLAG_BILLED, '',
            0, 1);
        print $formconfirm;
    }

    print '<table class="border centpercent">' . "\n";


    print '<tr><td class="fieldrequired">' . $langs->trans("FieldidBBC_vols") . '</td><td>' . $object->idBBC_vols . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fielddate") . '</td><td>' . dol_print_date($object->date) . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldlieuD") . '</td><td>' . $object->lieuD . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldlieuA") . '</td><td>' . $object->lieuA . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldheureD") . '</td><td>' . $object->heureD . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldheureA") . '</td><td>' . $object->heureA . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldBBC_ballons_idBBC_ballons") . '</td><td>' . $balloon->immat . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldnbrPax") . '</td><td>' . $object->nbrPax . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Noms des passagers") . '</td><td>' . $object->getPassengerNames() . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_type") . '</td><td>' . $object->fk_type . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_pilot") . '</td><td>' . $pilot->getNomUrl(1) . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_organisateur") . '</td><td>' . $organisator->getNomUrl(1) . '</td></tr>';

    print '</table>';

    dol_fiche_end();

    // Buttons
    print '<div class="tabsAction">' . "\n";
    $parameters = array();
    $reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object,
        $action);    // Note that $action and $object may have been modified by hook
    if ($reshook < 0) {
        setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
    }

    if ($user->rights->flightlog->vol->advanced || $user->rights->flightlog->vol->edit || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id)) {
        print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a></div>' . "\n";
    }

    if ($user->rights->flightlog->vol->delete || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id && !$object->isBilled())) {
        print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a></div>' . "\n";
    }

    if ($user->rights->flightlog->vol->financial && $object->fk_type == 2 && !$object->hasFacture() && $object->hasReceiver()) {
        print '<div class="inline-block divButAction"><a class="butAction" href="' . DOL_URL_ROOT . '/flightlog/facture.php?id=' . $object->id . '">' . $langs->trans("Facturer") . '</a></div>' . "\n";
    }
    ?>

    <?php if ($user->rights->flightlog->vol->financial && !$object->isBilled()): ?>
        <div class="inline-block divButAction">
            <a class="butAction" href="<?php echo sprintf('%s?id=%s&action=%s', $_SERVER["PHP_SELF"], $object->id,
                ACTION_FLAG_BILLED); ?>">
                <?php echo $langs->trans("Marqué comme facturé ") ?>
            </a>
        </div>
    <?php endif; ?>

    </div>
    <?php
    if ($user->rights->flightlog->vol->financial) {
        print '<div class="fichecenter"><div class="fichehalfleft">';
        $form->showLinkedObjectBlock($object);
        print '</div></div>';
    }

}


// End of page
llxFooter();
$db->close();
