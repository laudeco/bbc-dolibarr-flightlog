<?php

/**
 *    \file       flightLog/bbcvols_card.php
 *        \ingroup    flightLog
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
dol_include_once('/flightLog/class/bbcvols.class.php');
dol_include_once('/flightLog/class/bbctypes.class.php');
dol_include_once('/flightLog/lib/flightLog.lib.php');
dol_include_once('/flightLog/lib/card.lib.php');
dol_include_once('/flightLog/lib/PilotService.php');
dol_include_once('/flightBalloon/bbc_ballons.class.php');
dol_include_once('/user/class/usergroup.class.php');

global $langs, $user;

// Load traductions files requiredby by page
$langs->load("mymodule@flightLog");
$langs->load("other");

$id = GETPOST('id', 'int') ?: GETPOST('idBBC_vols', 'int');

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
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

// Initialize technical object to manage hooks of modules. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('bbcvols'));
$object->ref = $object->idBBC_vols;

if (!($user->rights->flightLog->vol->financial || $user->id == $object->fk_pilot)) {
    accessforbidden($langs->trans("Tu n'as pas accès au vol"));
}

$receiver->fetch($object->fk_receiver);
$pilot->fetch($object->fk_pilot);
$organisator->fetch($object->fk_organisateur);
$flightType->fetch($object->fk_type);
$balloon->fetch($object->BBC_ballons_idBBC_ballons);

llxHeader('', $langs->trans('financial of flight'), '');

$head = prepareFlightTabs($object);
dol_fiche_head($head, 'follow', $langs->trans("Vol"));

$linkback = '<a href="' . DOL_URL_ROOT . '/flightLog/list.php">' . $langs->trans("BackToList") . '</a>';
print $form->showrefnav($object, "idBBC_vols", $linkback, true, "idBBC_vols");

print '<table class="border centpercent">' . "\n";

print '<tr><td class="fieldrequired">' . $langs->trans("FieldidBBC_vols") . '</td><td>' . $object->idBBC_vols . '</td></tr>';
print '<tr><td class="fieldrequired">' . $langs->trans("FieldDateCreation") . '</td><td>' . dol_print_date($object->date_creation) . '</td></tr>';
print '<tr><td class="fieldrequired">' . $langs->trans("FieldDateUpdate") . '</td><td>' . dol_print_date($object->date_update) . '</td></tr>';

print '</table>';

dol_fiche_end();

// Buttons
print '<div class="tabsAction">' . "\n";
print '</div>' . "\n";

if($user->rights->flightLog->vol->financial){
    print '<div class="fichecenter"><div class="fichehalfleft">';
    $form->showLinkedObjectBlock($object);
    print '</div></div>';
}

// End of page
llxFooter();
$db->close();