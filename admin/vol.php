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
            $flightType->remboursement_km = price2num(GETPOST('remboursement_km_'.$flightTypeId));
            $flightType->points_pilote    = (int) GETPOST('points_pilote_'.$flightTypeId, 'int');
            $flightType->cout_pilote      = price2num(GETPOST('cout_pilote_'.$flightTypeId));
            $flightType->defraiement      = price2num(GETPOST('defraiement_'.$flightTypeId));
            $flightType->visible_graphique = GETPOST('visible_graphique_'.$flightTypeId) ? 1 : 0;
            $flightType->visible_tableau   = GETPOST('visible_tableau_'.$flightTypeId) ? 1 : 0;
            $flightType->update($user);
        }
    }

    dolibarr_set_const($db, 'BBC_FLIGHT_TYPE_CUSTOMER', GETPOST('customer_product'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'BBC_FLIGHT_DEFAULT_CUSTOMER', GETPOST('defaultCustomer'), 'chaine', 0, '', $conf->entity);

    dolibarr_set_const($db, 'BBC_POINTS_BONUS_ORGANISATOR', GETPOST('points_bonus_organisator'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'BBC_POINTS_BONUS_INSTRUCTOR', GETPOST('points_bonus_instructor'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'BBC_DEFAULT_BANK_ACCOUNT', GETPOST('default_bank_account'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'BBC_DEFAULT_PAYMENT_TERM_ID', GETPOST('bill_condition'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'BBC_DEFAULT_PAYMENT_TYPE_ID', GETPOST('bill_payment_type'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'BBC_DAMAGE_EMAILS', GETPOST('damage_emails'), 'chaine', 0, '', $conf->entity);
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
        <input type="hidden" name="token" value="<?php echo newToken(); ?>"/>

        <!-- Configuration par type de vol -->
        <h3><?= $langs->trans("Configuration par type de vol") ?></h3>
        <table class="noborder" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Type de vol") ?></th>
                <th><?= $langs->trans("Service / produit") ?></th>
                <th><?= $langs->trans("Remb. km (€/km)") ?></th>
                <th><?= $langs->trans("Points pilote") ?></th>
                <th><?= $langs->trans("Coût pilote (€)") ?></th>
                <th><?= $langs->trans("Défraiement (€)") ?></th>
                <th><?= $langs->trans("Visible graphique") ?></th>
                <th><?= $langs->trans("Visible tableau") ?></th>
            </tr>

            <?php foreach ($flightType->lines as $flightTypeLine): ?>
                <tr class="<?= $flightTypeLine->id % 2 == 0 ? "pair" : "impair" ?>">
                    <td>(T<?= $flightTypeLine->numero ?>) - <?= $flightTypeLine->nom ?></td>
                    <td>
                        <?php $form->select_produits($flightTypeLine->fkService, 'idprod['.$flightTypeLine->id.']', $filtertype, $conf->product->limit_size, $buyer->price_level, 1, 2, '', 1, array(),$buyer->id); ?>
                    </td>
                    <td>
                        <input type="number" step="0.001" name="remboursement_km_<?= $flightTypeLine->id ?>"
                               value="<?= $flightTypeLine->remboursement_km ?>" style="width:80px"/>
                    </td>
                    <td>
                        <input type="number" step="1" name="points_pilote_<?= $flightTypeLine->id ?>"
                               value="<?= $flightTypeLine->points_pilote ?>" style="width:70px"/>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="cout_pilote_<?= $flightTypeLine->id ?>"
                               value="<?= $flightTypeLine->cout_pilote ?>" style="width:80px"/>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="defraiement_<?= $flightTypeLine->id ?>"
                               value="<?= $flightTypeLine->defraiement ?>" style="width:80px"/>
                    </td>
                    <td style="text-align:center">
                        <input type="checkbox" name="visible_graphique_<?= $flightTypeLine->id ?>"
                               value="1" <?= $flightTypeLine->visible_graphique ? 'checked' : '' ?>/>
                    </td>
                    <td style="text-align:center">
                        <input type="checkbox" name="visible_tableau_<?= $flightTypeLine->id ?>"
                               value="1" <?= $flightTypeLine->visible_tableau ? 'checked' : '' ?>/>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr class="impair">
                <td>Vol client</td>
                <td colspan="7">
                    <?php $form->select_produits($conf->global->BBC_FLIGHT_TYPE_CUSTOMER, 'customer_product',
                        $filtertype, $conf->product->limit_size, $buyer->price_level, 1, 2, '', 1, array(),$buyer->id); ?>
                </td>
            </tr>

            <tr class="pair">
                <td>Client par défaut</td>
                <td colspan="7">
                    <?php echo $form->select_thirdparty_list($conf->global->BBC_FLIGHT_DEFAULT_CUSTOMER, 'defaultCustomer'); ?>
                </td>
            </tr>

        </table>


        <h3><?= $langs->trans("Points rôles spéciaux") ?></h3>
        <table class="noborder mt-2" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Champ") ?></th>
                <th><?= $langs->trans("Valeur") ?></th>
            </tr>

            <tr class="pair">
                <td><?php echo $langs->trans('Points organisateur')?></td>
                <td>
                    <input type="number" id="points_bonus_organisator" name="points_bonus_organisator"
                           value="<?php echo $conf->global->BBC_POINTS_BONUS_ORGANISATOR?>" />
                </td>
            </tr>

            <tr class="impar">
                <td><?php echo $langs->trans('Points instructeur')?></td>
                <td>
                    <input type="number" id="points_bonus_instructor" name="points_bonus_instructor"
                           value="<?php echo $conf->global->BBC_POINTS_BONUS_INSTRUCTOR?>" />
                </td>
            </tr>
        </table>


        <h3><?= $langs->trans("Paramètres de facturation") ?></h3>
        <table class="noborder mt-2" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Champ") ?></th>
                <th><?= $langs->trans("Valeur") ?></th>
            </tr>

            <tr class="pair">
                <td><?php echo $langs->trans('Compte en banque par défaut') ?></td>
                <td>
                    <?php $form->select_comptes($conf->global->BBC_DEFAULT_BANK_ACCOUNT, 'default_bank_account', 0, '', 1); ?>
                </td>
            </tr>

            <tr class="impair">
                <td><?php echo $langs->trans('Condition de vente par défaut') ?></td>
                <td>
                    <?php $form->select_conditions_paiements($conf->global->BBC_DEFAULT_PAYMENT_TERM_ID, 'bill_condition'); ?>
                </td>
            </tr>

            <tr class="pair">
                <td><?php echo $langs->trans('Type de payement par défaut') ?></td>
                <td>
                    <?php $form->select_types_paiements($conf->global->BBC_DEFAULT_PAYMENT_TYPE_ID, 'bill_payment_type'); ?>
                </td>
            </tr>
        </table>


        <h3><?= $langs->trans("Notifications") ?></h3>
        <table class="noborder mt-2" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Champ") ?></th>
                <th><?= $langs->trans("Valeur") ?></th>
            </tr>

            <tr class="impair">
                <td><?php echo $langs->trans('E-mail additionel sur les erreurs') ?></td>
                <td>
                    <textarea rows="4" cols="80" name="damage_emails"><?php echo $conf->global->BBC_DAMAGE_EMAILS; ?></textarea>
                    <br/><span class="text-muted">Separer par des ; </span>
                </td>
            </tr>

        </table>
        <input type="submit" />
    </form>
<?php
llxFooter();
$db->close();
?>
