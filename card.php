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

dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');
dol_include_once('/flightlog/lib/flightLog.lib.php');
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

$isAllowedEdit = ($user->rights->flightlog->vol->edit || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id));
$isAllowedDelete = ($user->rights->flightlog->vol->delete || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id && !$object->is_facture));
$permissiondellink=$user->rights->flightlog->vol->financial;

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
include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

// Initialize technical object to manage hooks of modules. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('bbcvols'));
$object->ref = $object->idBBC_vols;
$receiver->fetch($object->fk_receiver);
$pilot->fetch($object->fk_pilot);
$organisator->fetch($object->fk_organisateur);
$flightType->fetch($object->fk_type);
$balloon->fetch($object->BBC_ballons_idBBC_ballons);


if (($action == "update" || $action == "edit") && !($user->rights->flightlog->vol->edit || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id))) {
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
    if ($action == 'update') {
        $error = 0;

        $object->idBBC_vols = GETPOST('idBBC_vols', 'int');
        $object->id = $object->idBBC_vols;

        $object->lieuD = GETPOST('lieuD', 'alpha');
        $object->lieuA = GETPOST('lieuA', 'alpha');
        $object->heureD = GETPOST('heureD_h', 'int') . ":" . GETPOST('heureD_m', 'int') . ":00";
        $object->heureA = GETPOST('heureA_h', 'int') . ":" . GETPOST('heureA_m', 'int') . ":00";
        $object->BBC_ballons_idBBC_ballons = GETPOST('BBC_ballons_idBBC_ballons', 'int');

        $object->remarque = GETPOST('remarque', 'alpha');
        $object->incidents = GETPOST('incidents', 'alpha');
        $object->kilometers = GETPOST('kilometers', 'int') ?: $object->kilometers;
        $object->justif_kilometers = GETPOST('justif_kilometers', 'alpha') ?: $object->justif_kilometers;


        //validation
        if (empty($object->idBBC_vols)) {
            $error++;
            setEventMessages($langs->transnoentitiesnoconv("ErrorFieldRequired",
                $langs->transnoentitiesnoconv("idBBC_vols")),
                null, 'errors');
        }

        if (!dol_validElement($object->lieuD)) {
            $error++;
            setEventMessage("Erreur le champ : lieu de décollage", 'errors');
        }

        if (!dol_validElement($object->lieuA)) {
            $error++;
            setEventMessage("Erreur le champ : lieu d'atterissage", 'errors');
        }

        $dateD = date_create_from_format("H:i:s", $object->heureD);
        $dateA = date_create_from_format("H:i:s", $object->heureA);
        if ($dateA <= $dateD) {
            $error++;
            setEventMessage("Erreur avec les heures de vol", 'errors');
        }

        if (!is_numeric($object->nbrPax) || $object->nbrPax < 0) {
            $error++;
            setEventMessage("Erreur le champ : nombre de passagers", 'errors');
        }

        // action : edit
        if (!$error) {
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
if (($id || $ref) && $action == 'edit') {
    print load_fiche_titre($langs->trans("MyModule"));

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    print '<input type="hidden" name="idBBC_vols" value="' . $object->id . '">';

    dol_fiche_head();

    print '<table class="border centpercent">' . "\n";

    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldlieuD") . "</td><td><input class=\"flat\" type=\"text\" name=\"lieuD\" value=\"" . $object->lieuD . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldlieuA") . "</td><td><input class=\"flat\" type=\"text\" name=\"lieuA\" value=\"" . $object->lieuA . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldheureD") . "</td><td><input class=\"flat\" min=\"0\" max=\"23\" type=\"number\" name=\"heureD_h\" value=\"" . explode(":",
            $object->heureD)[0] . "\">h<input class=\"flat\" type=\"number\" min=\"0\" max=\"59\" name=\"heureD_m\" value=\"" . explode(":",
            $object->heureD)[1] . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldheureA") . "</td><td><input class=\"flat\" type=\"number\" min=\"0\" max=\"23\" name=\"heureA_h\" value=\"" . explode(":",
            $object->heureA)[0] . "\">h<input class=\"flat\" type=\"number\" min=\"0\" max=\"59\" name=\"heureA_m\" value=\"" . explode(":",
            $object->heureA)[1] . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldBBC_ballons_idBBC_ballons") . "</td><td>";
    select_balloons($object->BBC_ballons_idBBC_ballons, "BBC_ballons_idBBC_ballons");
    print "</td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldremarque") . "</td><td><textarea class=\"flat\" name=\"remarque\">" . $object->remarque . "</textarea></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldincidents") . "</td><td><textarea class=\"flat\" name=\"incidents\">" . $object->incidents . "</textarea></td></tr>";

    if ($user->rights->flightlog->vol->financial || $user->id == $object->fk_pilot) {
        print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldkilometers") . "</td><td><input class=\"flat\" type=\"number\" name=\"kilometers\" value=\"" . $object->kilometers . "\"></td></tr>";
        print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldjustif_kilometers") . "</td><td><textarea class=\"flat\" name=\"justif_kilometers\">" . $object->justif_kilometers . "</textarea></td></tr>";
    }
    print '</table>';

    dol_fiche_end();

    print '<div class="center"><input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
    print ' &nbsp; <input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
    print '</div>';

    print '</form>';
}


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
    }elseif ($user->rights->flightlog->vol->financial && !$object->isBilled() && $action == ACTION_FLAG_BILLED) {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('Marque comme facturé'),
            $langs->trans('Ce vol va être marqué comme facturé, est-ce bien le cas ?'), ACTION_CONFIRM_FLAG_BILLED, '', 0, 1);
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

    if ($user->rights->flightlog->vol->edit || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id)) {
        print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a></div>' . "\n";
    }

    if ($user->rights->flightlog->vol->delete || ($user->rights->flightlog->vol->add && $object->fk_pilot == $user->id && !$object->is_facture)) {
        print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a></div>' . "\n";
    }

    if($user->rights->flightlog->vol->financial && $object->fk_type == 2 && !$object->hasFacture() && $object->hasReceiver()){
        print '<div class="inline-block divButAction"><a class="butAction" href="' . DOL_URL_ROOT . '/flightlog/facture.php?id=' . $object->id.'">' . $langs->trans("Facturer") . '</a></div>' . "\n";
    }
    ?>

    <?php if($user->rights->flightlog->vol->financial && !$object->isBilled() ): ?>
        <div class="inline-block divButAction">
               <a class="butAction" href="<?php echo sprintf('%s?id=%s&action=%s' , $_SERVER["PHP_SELF"], $object->id, ACTION_FLAG_BILLED);?>">
                   <?php echo $langs->trans("Marqué comme facturé ") ?>
               </a>
        </div>
    <?php endif; ?>

    </div>
<?php
    if($user->rights->flightlog->vol->financial){
        print '<div class="fichecenter"><div class="fichehalfleft">';
        $form->showLinkedObjectBlock($object);
        print '</div></div>';
    }

}


// End of page
llxFooter();
$db->close();
