<?php
//require '../../main.inc.php';

require '../../main.inc.php';

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

        </table>
        <input type="submit" />
    </form>
<?php
llxFooter();
$db->close();
?>