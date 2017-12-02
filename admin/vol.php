<?php
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

require_once '../../core/lib/admin.lib.php';
dol_include_once("/flightlog/class/bbctypes.class.php");

global $langs, $user, $db, $conf;

$langs->load("admin");
$langs->load("mymodule@flightlog");

const ACTION_SAVE = "save";

if (!$user->admin) {
    accessforbidden();
}

$flightType = new Bbctypes($db);
$action = GETPOST('action', 'alpha', 2);
$services = GETPOST('idprod', 'array', 2);

/*
 * Actions
 */
// Save
if($action === ACTION_SAVE){
    foreach($services as $flightTypeId => $serviceId){
        $res = $flightType->fetch($flightTypeId);
        if($res > 0 ){
            $flightType->fkService = $serviceId;
            $flightType->update($user);
        }
    }

    dolibarr_set_const($db, 'BBC_FLIGHT_TYPE_CUSTOMER', GETPOST('customer_product'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'BBC_FLIGHT_DEFAULT_CUSTOMER', GETPOST('defaultCustomer'), 'chaine', 0, '', $conf->entity);
}

/*
 * View
 */

$form = new Form($db);
$flightType->fetchAll();

llxHeader('', $langs->trans("FLightLogSetup"), $help_url);

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans("FLightLogSetup"), $linkback, 'title_setup');

?>

    <form method="POST">
        <input type="hidden" name="action" value="<?= ACTION_SAVE ?>"/>
        <!-- Service mapping -->
        <table class="noborder" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Types de vol.") ?></th>
                <th><?= $langs->trans("Service / produit") ?></th>
            </tr>

            <?php foreach ($flightType->lines as $flightTypeLine): ?>
                <!-- <?= $flightTypeLine->nom; ?>-->
                <tr class="<?= $flightTypeLine->id % 2 == 0 ? "pair" : "impair" ?>">
                    <td>(T<?= $flightTypeLine->numero ?>) - <?= $flightTypeLine->nom ?></td>
                    <td>
                        <?php $form->select_produits($flightTypeLine->fkService, 'idprod['.$flightTypeLine->id.']', $filtertype, $conf->product->limit_size, $buyer->price_level, 1, 2, '', 1, array(),$buyer->id); ?>
                    </td>
                </tr>
            <?php endforeach; ?>


            <tr class="impair">
                <td>
                    Vol client
                </td>
                <td>
                    <?php $form->select_produits($conf->global->BBC_FLIGHT_TYPE_CUSTOMER, 'customer_product', $filtertype, $conf->product->limit_size, $buyer->price_level, 1, 2, '', 1, array(),$buyer->id); ?>
                </td>
            </tr>

            <tr class="pair">
                <td>
                    Client par défaut
                </td>
                <td>
                    <?php echo $form->select_thirdparty_list($conf->global->BBC_FLIGHT_DEFAULT_CUSTOMER, 'defaultCustomer'); ?>
                </td>
            </tr>

        </table>
        <input type="submit" />
    </form>
<?php
llxFooter();
$db->close();
?>