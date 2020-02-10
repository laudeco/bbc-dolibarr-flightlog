<?php

/**
 *    \file       flightlog/bbcvols_card.php
 *        \ingroup    flightlog
 *        \brief      This file is an example of a php page
 *                    Initialy built by build_class_from_table on 2017-02-09 11:10
 */

// Change this following line to use the correct relative path (../, ../../, etc)
use FlightLog\Http\Web\Controller\AddFlightDamageController;
use FlightLog\Http\Web\Controller\FlightDamageController;

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
dol_include_once('/flightlog/lib/card.lib.php');
dol_include_once('/flightlog/lib/PilotService.php');
dol_include_once('/flightballoon/bbc_ballons.class.php');
dol_include_once('/user/class/usergroup.class.php');

global $langs, $user;

const FLIGHTLOG_ACTION_ADD_DAMAGE = 'add_damage';
const FLIGHTLOG_ACTION_HANDLE_ADD_DAMAGE = 'handle_add_damage';
const FLIGHTLOG_ACTION_BILL_DAMAGE = 'bill_damage';
const FLIGHTLOG_ACTION_CONFIRM_BILL_DAMAGE = 'confirm_bill_damage';

// Load traductions files requiredby by page
$langs->load("mymodule@flightlog");
$langs->load("other");

$id = GETPOST('id', 'int') ?: GETPOST('idBBC_vols', 'int');
$action = GETPOST('action', 'alpha');
$permissiondellink=$user->rights->flightlog->vol->financial;

$object = new Bbcvols($db);

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

llxHeader('', $langs->trans('Flight Damage'), '');

$head = prepareFlightTabs($object);
dol_fiche_head($head, 'damage', $langs->trans("Vol"));

$linkback = '<a href="' . DOL_URL_ROOT . '/flightlog/list.php">' . $langs->trans("BackToList") . '</a>';
print $form->showrefnav($object, "idBBC_vols", $linkback, true, "idBBC_vols");

?>

<?php
    $routes = [
        '' => [FlightDamageController::class, 'view'],
        FLIGHTLOG_ACTION_ADD_DAMAGE => [AddFlightDamageController::class, 'view'],
        FLIGHTLOG_ACTION_HANDLE_ADD_DAMAGE => [AddFlightDamageController::class, 'view'],
    ];

    if(isset($routes[$action])){
        $controllerName = $routes[$action][0];
        $actionName = $routes[$action][1];

        $response = call_user_func([new $controllerName($db), $actionName]);

        if($response instanceof \FlightLog\Http\Web\Response\Redirect){
            if (headers_sent()) {
                echo(sprintf("<script>location.href='%s'</script>", $response->getUrl()));
                exit;
            }

            header(sprintf("Location: %s", $response->getUrl()));
            exit;
        }

        include $response->getTemplate();
    }else{
        echo 'Route non trouvée.';
    }
?>


<?php
dol_fiche_end();

// Buttons
print '<div class="tabsAction">' . "\n";

if($user->rights->flightlog->vol->financial){
    print '<div class="inline-block divButAction"><a class="butAction" href="' . DOL_URL_ROOT . '/flightlog/card_tab_damage.php?id=' . $object->id.'&action='.FLIGHTLOG_ACTION_ADD_DAMAGE.'">' . $langs->trans("Ajouter") . '</a></div>' . "\n";
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