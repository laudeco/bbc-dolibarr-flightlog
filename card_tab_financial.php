<?php

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
dol_include_once('/flightballoon/bbc_ballons.class.php');
dol_include_once('/user/class/usergroup.class.php');

global $langs, $user;

// Load traductions files requiredby by page
$langs->load("mymodule@flightlog");
$langs->load("other");

$id = GETPOST('id', 'int') ?: GETPOST('idBBC_vols', 'int');
$action = GETPOST('action', 'alpha');
$permissiondellink=$user->rights->flightlog->vol->financial;

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
include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

// Initialize technical object to manage hooks of modules. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('bbcvols'));
$object->ref = $object->idBBC_vols;

if (!($user->rights->flightlog->vol->financial || $user->id == $object->fk_pilot)) {
    accessforbidden($langs->trans("Tu n'as pas accès au vol"));
}

$receiver->fetch($object->fk_receiver);
$pilot->fetch($object->fk_pilot);
$organisator->fetch($object->fk_organisateur);
$flightType->fetch($object->fk_type);
$balloon->fetch($object->BBC_ballons_idBBC_ballons);

llxHeader('', $langs->trans('financial of flight'), '');

$head = prepareFlightTabs($object);
dol_fiche_head($head, 'financial', $langs->trans("Vol"));

$linkback = '<a href="' . DOL_URL_ROOT . '/flightlog/list.php">' . $langs->trans("BackToList") . '</a>';
print $form->showrefnav($object, "idBBC_vols", $linkback, true, "idBBC_vols");

print '<table class="border centpercent">' . "\n";

print '<tr><td class="fieldrequired">' . $langs->trans("FieldidBBC_vols") . '</td><td>' . $object->idBBC_vols . '</td></tr>';
print '<tr><td class="fieldrequired">' . $langs->trans("Fielddate") . '</td><td>' . dol_print_date($object->date) . '</td></tr>';

if ($user->rights->flightlog->vol->financial) {
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldis_facture") . '</td><td>' . $object->getLibStatut(5). '</td></tr>';
}

print '<tr><td class="fieldrequired">' . $langs->trans("Fieldkilometers") . '</td><td>' . $object->kilometers . ' KM</td></tr>';
print '<tr><td class="fieldrequired">' . $langs->trans("Fieldjustif_kilometers") . '</td><td>' . $object->justif_kilometers . '</td></tr>';
print '<tr><td class="fieldrequired">' . $langs->trans("Fieldcost") . '</td><td>' . $object->cost . " " . $langs->getCurrencySymbol($conf->currency) . '</td></tr>';
print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_receiver") . '</td><td>' . $receiver->getNomUrl(1) . '</td></tr>';
print '</table>';

dol_fiche_end();

// Buttons
print '<div class="tabsAction">' . "\n";

if($user->rights->flightlog->vol->financial && $object->fk_type == 2 && !$object->hasFacture()){
    print '<div class="inline-block divButAction"><a class="butAction" href="' . DOL_URL_ROOT . '/flightlog/facture.php?id=' . $object->id.'">' . $langs->trans("Facturer") . '</a></div>' . "\n";
}

print '</div>' . "\n";

if($user->rights->flightlog->vol->financial){
    print '<div class="fichecenter"><div class="fichehalfleft">';
    $form->showLinkedObjectBlock($object);
    print '</div></div>';
}

// End of page
llxFooter();
$db->close();