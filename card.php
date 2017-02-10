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
 *    \file       flightLog/bbcvols_card.php
 *        \ingroup    flightLog
 *        \brief      This file is an example of a php page
 *                    Initialy built by build_class_from_table on 2017-02-09 11:10
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

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
dol_include_once('/flightLog/class/bbcvols.class.php');
dol_include_once('/flightLog/class/bbctypes.class.php');
dol_include_once('/flightLog/lib/flightLog.lib.php');
dol_include_once('/flightBalloon/bbc_ballons.class.php');

// Load traductions files requiredby by page
$langs->load("mymodule@flightLog");
$langs->load("other");

// Get parameters
$id = GETPOST('id', 'int')?: GETPOST('idBBC_vols', 'int');
$action = GETPOST('action', 'alpha');
$cancel = GETPOST('cancel');
$backtopage = GETPOST('backtopage');
$myparam = GETPOST('myparam', 'alpha');


$search_idBBC_vols = GETPOST('search_idBBC_vols', 'int');
$search_lieuD = GETPOST('search_lieuD', 'alpha');
$search_lieuA = GETPOST('search_lieuA', 'alpha');
$search_heureD = GETPOST('search_heureD', 'alpha');
$search_heureA = GETPOST('search_heureA', 'alpha');
$search_BBC_ballons_idBBC_ballons = GETPOST('search_BBC_ballons_idBBC_ballons', 'int');
$search_nbrPax = GETPOST('search_nbrPax', 'alpha');
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

if (empty($action) && empty($id) && empty($ref)) {
    $action = 'view';
}

// Protection if external user
if ($user->societe_id > 0) {
    //accessforbidden();
}
//$result = restrictedArea($user, 'flightLog', $id);


$object = new Bbcvols($db);
$extrafields = new ExtraFields($db);

$receiver = new User($db);

$pilot = new User($db);

$organisator = new User($db);

$flightType = new Bbctypes($db);
$balloon = new Bbc_ballons($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

// Initialize technical object to manage hooks of modules. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('bbcvols'));

$receiver->fetch($object->fk_receiver);
$pilot->fetch($object->fk_pilot);
$organisator->fetch($object->fk_organisateur);
$flightType->fetch($object->fk_type);
$balloon->fetch($object->BBC_ballons_idBBC_ballons);

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
    if ($cancel) {
        if ($id > 0 || !empty($ref)) {
            $object->fetch($id);

            $receiver->fetch($object->fk_receiver);
            $pilot->fetch($object->fk_pilot);
            $organisator->fetch($object->fk_organisateur);
            $flightType->fetch($object->fk_type);
            $balloon->fetch($object->BBC_ballons_idBBC_ballons);
        }
        $action = '';
    }

    // Action to add record
    if ($action == 'add') {
        if (GETPOST('cancel')) {
            $urltogo = $backtopage ? $backtopage : dol_buildpath('/flightLog/list.php', 1);
            header("Location: " . $urltogo);
            exit;
        }

        $error = 0;

        /* object_prop_getpost_prop */

        $object->idBBC_vols = GETPOST('idBBC_vols', 'int');
        $object->lieuD = GETPOST('lieuD', 'alpha');
        $object->lieuA = GETPOST('lieuA', 'alpha');
        $object->heureD = GETPOST('heureD', 'alpha');
        $object->heureA = GETPOST('heureA', 'alpha');
        $object->BBC_ballons_idBBC_ballons = GETPOST('BBC_ballons_idBBC_ballons', 'int');
        $object->nbrPax = GETPOST('nbrPax', 'alpha');
        $object->remarque = GETPOST('remarque', 'alpha');
        $object->incidents = GETPOST('incidents', 'alpha');
        $object->fk_type = GETPOST('fk_type', 'int');
        $object->fk_pilot = GETPOST('fk_pilot', 'int');
        $object->fk_organisateur = GETPOST('fk_organisateur', 'int');
        $object->is_facture = GETPOST('is_facture', 'int');
        $object->kilometers = GETPOST('kilometers', 'int');
        $object->cost = GETPOST('cost', 'alpha');
        $object->fk_receiver = GETPOST('fk_receiver', 'int');
        $object->justif_kilometers = GETPOST('justif_kilometers', 'alpha');


        if (empty($object->idBBC_vols)) {
            $error++;
            setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("idBBC_vols")), null, 'errors');
        }

        if (!$error) {
            $result = $object->create($user);
            if ($result > 0) {
                // Creation OK
                $urltogo = $backtopage ? $backtopage : dol_buildpath('/flightLog/list.php', 1);
                header("Location: " . $urltogo);
                exit;
            }
            {
                // Creation KO
                if (!empty($object->errors)) {
                    setEventMessages(null, $object->errors, 'errors');
                } else {
                    setEventMessages($object->error, null, 'errors');
                }
                $action = 'create';
            }
        } else {
            $action = 'create';
        }
    }

    // Action to update record
    if ($action == 'update') {
        $error = 0;

        $object->date = dol_mktime(12, 0, 0, GETPOST("remonth"), GETPOST("reday"), GETPOST("reyear"));
        $object->idBBC_vols = GETPOST('idBBC_vols', 'int');
        $object->id = $object->idBBC_vols;
        $object->lieuD = GETPOST('lieuD', 'alpha');
        $object->lieuA = GETPOST('lieuA', 'alpha');
        $object->heureD = GETPOST('heureD_h', 'int').":".GETPOST('heureD_m', 'int').":00";
        $object->heureA = GETPOST('heureA_h', 'int').":".GETPOST('heureA_m', 'int').":00";
        $object->BBC_ballons_idBBC_ballons = GETPOST('BBC_ballons_idBBC_ballons', 'int');
        $object->nbrPax = GETPOST('nbrPax', 'alpha');
        $object->remarque = GETPOST('remarque', 'alpha');
        $object->incidents = GETPOST('incidents', 'alpha');
        $object->fk_type = GETPOST('fk_type', 'int');
        $object->fk_pilot = GETPOST('fk_pilot', 'int');
        $object->fk_organisateur = GETPOST('fk_organisateur', 'int');
        //$object->is_facture = GETPOST('is_facture', 'int');
        $object->kilometers = GETPOST('kilometers', 'int');
        $object->cost = GETPOST('cost', 'alpha');
        $object->fk_receiver = GETPOST('fk_receiver', 'int');
        $object->justif_kilometers = GETPOST('justif_kilometers', 'alpha');


        if (empty($object->idBBC_vols)) {
            $error++;
            setEventMessages($langs->transnoentitiesnoconv("ErrorFieldRequired", $langs->transnoentitiesnoconv("idBBC_vols")),
                null, 'errors');
        }

        if (!$error) {
            $result = $object->update($user);
            if ($result > 0) {
                $action = 'view';

                $receiver->fetch($object->fk_receiver);
                $pilot->fetch($object->fk_pilot);
                $organisator->fetch($object->fk_organisateur);
                $flightType ->fetch($object->fk_type);
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
            header("Location: " . dol_buildpath('/flightLog/list.php', 1));
            exit;
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

// Example : Adding jquery code
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


// Part to create
if ($action == 'create') {
    print load_fiche_titre($langs->trans("NewMyModule"));

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';

    dol_fiche_head();

    print '<table class="border centpercent">' . "\n";
    // print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td><input class="flat" type="text" size="36" name="label" value="'.$label.'"></td></tr>';
    //
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldidBBC_vols") . '</td><td><input class="flat" type="text" name="idBBC_vols" value="' . GETPOST('idBBC_vols') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldlieuD") . '</td><td><input class="flat" type="text" name="lieuD" value="' . GETPOST('lieuD') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldlieuA") . '</td><td><input class="flat" type="text" name="lieuA" value="' . GETPOST('lieuA') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldheureD") . '</td><td><input class="flat" type="text" name="heureD" value="' . GETPOST('heureD') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldheureA") . '</td><td><input class="flat" type="text" name="heureA" value="' . GETPOST('heureA') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldBBC_ballons_idBBC_ballons") . '</td><td><input class="flat" type="text" name="BBC_ballons_idBBC_ballons" value="' . GETPOST('BBC_ballons_idBBC_ballons') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("FieldnbrPax") . '</td><td><input class="flat" type="text" name="nbrPax" value="' . GETPOST('nbrPax') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldremarque") . '</td><td><input class="flat" type="text" name="remarque" value="' . GETPOST('remarque') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldincidents") . '</td><td><input class="flat" type="text" name="incidents" value="' . GETPOST('incidents') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_type") . '</td><td><input class="flat" type="text" name="fk_type" value="' . GETPOST('fk_type') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_pilot") . '</td><td><input class="flat" type="text" name="fk_pilot" value="' . GETPOST('fk_pilot') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_organisateur") . '</td><td><input class="flat" type="text" name="fk_organisateur" value="' . GETPOST('fk_organisateur') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldis_facture") . '</td><td><input class="flat" type="text" name="is_facture" value="' . GETPOST('is_facture') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldkilometers") . '</td><td><input class="flat" type="text" name="kilometers" value="' . GETPOST('kilometers') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldcost") . '</td><td><input class="flat" type="text" name="cost" value="' . GETPOST('cost') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_receiver") . '</td><td><input class="flat" type="text" name="fk_receiver" value="' . GETPOST('fk_receiver') . '"></td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldjustif_kilometers") . '</td><td><input class="flat" type="text" name="justif_kilometers" value="' . GETPOST('justif_kilometers') . '"></td></tr>';

    print '</table>' . "\n";

    dol_fiche_end();

    print '<div class="center"><input type="submit" class="button" name="add" value="' . $langs->trans("Create") . '"> &nbsp; <input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '"></div>';

    print '</form>';
}


// Part to edit record
if (($id || $ref) && $action == 'edit') {
    print load_fiche_titre($langs->trans("MyModule"));

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    print '<input type="hidden" name="idBBC_vols" value="' . $object->id . '">';

    dol_fiche_head();

    print '<table class="border centpercent">' . "\n";

    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldDate") . "</td><td>";
    print $form->select_date($object->date, '', '', '', '', 'add', 1, 1);
    print "</td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldlieuD") . "</td><td><input class=\"flat\" type=\"text\" name=\"lieuD\" value=\"" . $object->lieuD . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldlieuA") . "</td><td><input class=\"flat\" type=\"text\" name=\"lieuA\" value=\"" . $object->lieuA . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldheureD") . "</td><td><input class=\"flat\" min=\"0\" max=\"23\" type=\"number\" name=\"heureD_h\" value=\"" . explode(":", $object->heureD)[0] . "\">h<input class=\"flat\" type=\"number\" min=\"0\" max=\"59\" name=\"heureD_m\" value=\"" . explode(":", $object->heureD)[1] . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldheureA") . "</td><td><input class=\"flat\" type=\"number\" min=\"0\" max=\"23\" name=\"heureA_h\" value=\"" . explode(":", $object->heureA)[0] . "\">h<input class=\"flat\" type=\"number\" min=\"0\" max=\"59\" name=\"heureA_m\" value=\"" . explode(":", $object->heureA)[1] . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldBBC_ballons_idBBC_ballons") . "</td><td>";
        select_balloons($object->BBC_ballons_idBBC_ballons, "BBC_ballons_idBBC_ballons");
    print "</td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("FieldnbrPax") . "</td><td><input class=\"flat\" type=\"number\" name=\"nbrPax\" value=\"" . $object->nbrPax . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldremarque") . "</td><td><textarea class=\"flat\" name=\"remarque\">".$object->remarque."</textarea></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldincidents") . "</td><td><textarea class=\"flat\" name=\"incidents\">".$object->incidents."</textarea></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldfk_type") . "</td><td>";
        select_flight_type($object->fk_type, "fk_type");
    print "</td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldfk_pilot") . "</td><td>".$form->select_dolusers($object->fk_pilot, "fk_pilot")."</td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldfk_organisateur") . "</td><td>".$form->select_dolusers($object->fk_organisateur, "fk_organisateur")."</td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldkilometers") . "</td><td><input class=\"flat\" type=\"number\" name=\"kilometers\" value=\"" . $object->kilometers . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldcost") . "</td><td><input class=\"flat\" type=\"number\" name=\"cost\" value=\"" . $object->cost . "\"></td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldfk_receiver") . "</td><td>".$form->select_dolusers($object->fk_receiver, "fk_receiver")."</td></tr>";
    print "<tr><td class=\"fieldrequired\">" . $langs->trans("Fieldjustif_kilometers") . "</td><td><textarea class=\"flat\" name=\"justif_kilometers\">".$object->justif_kilometers."</textarea></td></tr>";

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


    print load_fiche_titre($langs->trans($pageTitle));

    dol_fiche_head();

    if ($action == 'delete') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteMyOjbect'),
            $langs->trans('ConfirmDeleteMyObject'), 'confirm_delete', '', 0, 1);
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
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldremarque") . '</td><td>' . $object->remarque . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldincidents") . '</td><td>' . $object->incidents . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_type") . '</td><td>' . $object->fk_type . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_pilot") . '</td><td>' . $pilot->getNomUrl(1) . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_organisateur") . '</td><td>' . $organisator->getNomUrl(1) . '</td></tr>';

    if($user->rights->flightLog->vol->financial){
        print '<tr><td class="fieldrequired">' . $langs->trans("Fieldis_facture") . '</td><td>' . $object->is_facture . '</td></tr>';
    }

    if($user->rights->flightLog->vol->financial || $user->id == $object->fk_pilot) {
        print '<tr><td class="fieldrequired">' . $langs->trans("Fieldkilometers") . '</td><td>' . $object->kilometers . ' KM</td></tr>';
        print '<tr><td class="fieldrequired">' . $langs->trans("Fieldcost") . '</td><td>' . $object->cost ." ". $langs->getCurrencySymbol($conf->currency).'</td></tr>';
        print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_receiver") . '</td><td>' . $receiver->getNomUrl(1) . '</td></tr>';
        print '<tr><td class="fieldrequired">' . $langs->trans("Fieldjustif_kilometers") . '</td><td>' . $object->justif_kilometers . '</td></tr>';
    }

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

    if ($user->rights->flightLog->vol->edit || ($user->rights->flightLog->vol->add && $object->fk_pilot == $user->id)) {
        print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a></div>' . "\n";
    }

    if ($user->rights->flightLog->vol->delete && !$object->is_facture) {
        print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a></div>' . "\n";
    }
    print '</div>' . "\n";


    // Example 2 : Adding links to objects
    // Show links to link elements
    //$linktoelem = $form->showLinkToObjectBlock($object, null, array('bbcvols'));
    //$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

}


// End of page
llxFooter();
$db->close();
