<?php
//require '../../main.inc.php';

require '../../main.inc.php';

require_once '../../core/lib/admin.lib.php';
dol_include_once("/flightlog/class/bbctypes.class.php");

global $langs, $user, $db, $conf;

$langs->load("admin");
$langs->load("mymodule@flightlog");

const ACTION_SAVE = "save";
const ACTION_ADD = "add";
const ACTION_DELETE = "delete";

if (!$user->admin) {
    accessforbidden();
}

$flightType = new Bbctypes($db);
$action = GETPOST('action', 'alpha', 2);

/** @var string[] $setupMessages */
$setupMessages = [];

/** @var string[] $setupErrors */
$setupErrors = [];

$services = GETPOST('idprod', 'array', 2);
$names = GETPOST('flight_type_name', 'array', 2);
$numbers = GETPOST('flight_type_number', 'array', 2);
$points = GETPOST('flight_type_points', 'array', 2);
$missions = GETPOST('flight_type_mission', 'array', 2);
$paxRequirements = GETPOST('flight_type_pax', 'array', 2);
$billingRequirements = GETPOST('flight_type_billing', 'array', 2);
$instructions = GETPOST('flight_type_instruction', 'array', 2);
$pilotCharges = GETPOST('flight_type_charged', 'array', 2);
$actives = GETPOST('flight_type_active', 'array', 2);

/**
 * Number of flights linked to a flight type.
 *
 * @param int $flightTypeId
 *
 * @return int
 */
function countFlightsForType($flightTypeId)
{
    global $db;

    $sql = 'SELECT COUNT(idBBC_vols) as total FROM ' . MAIN_DB_PREFIX . 'bbc_vols WHERE fk_type = ' . (int) $flightTypeId;
    $resql = $db->query($sql);
    if (!$resql) {
        return 0;
    }

    $obj = $db->fetch_object($resql);

    return $obj ? (int) $obj->total : 0;
}

/*
 * Actions
 */
// Save all the flight types and the configuration of the module
if ($action === ACTION_SAVE) {
    $flightType->fetchAll();

    // The number of a type identifies it everywhere (points, tables, ...), it has to stay unique.
    $usedNumbers = [];
    $duplicatedNumbers = [];
    foreach ($flightType->getLines() as $flightTypeLine) {
        $flightTypeId = $flightTypeLine->getId();
        $number = isset($numbers[$flightTypeId]) && (int) $numbers[$flightTypeId] > 0
            ? (int) $numbers[$flightTypeId]
            : (int) $flightTypeLine->getNumero();

        if (isset($usedNumbers[$number])) {
            $duplicatedNumbers[$number] = $number;
        }

        $usedNumbers[$number] = $flightTypeId;
    }

    if (!empty($duplicatedNumbers)) {
        $setupErrors[] = sprintf("Le numéro d'un type de vol doit être unique (T%s en double). Aucune modification n'a été enregistrée.",
            implode(', T', $duplicatedNumbers));
        $action = '';
    }
}

if ($action === ACTION_SAVE) {
    foreach ($flightType->getLines() as $flightTypeLine) {
        $flightTypeId = $flightTypeLine->getId();

        $currentFlightType = new Bbctypes($db);
        if ($currentFlightType->fetch($flightTypeId) <= 0) {
            continue;
        }

        if (isset($numbers[$flightTypeId]) && (int) $numbers[$flightTypeId] > 0) {
            $currentFlightType->numero = (int) $numbers[$flightTypeId];
        }

        if (isset($names[$flightTypeId]) && trim($names[$flightTypeId]) !== '') {
            $currentFlightType->nom = trim($names[$flightTypeId]);
        }

        $currentFlightType->fkService = isset($services[$flightTypeId]) ? $services[$flightTypeId] : null;
        $currentFlightType->points = isset($points[$flightTypeId]) && $points[$flightTypeId] !== '' ? (int) $points[$flightTypeId] : null;
        $currentFlightType->isMission = isset($missions[$flightTypeId]);
        $currentFlightType->paxRequired = isset($paxRequirements[$flightTypeId]);
        $currentFlightType->billingRequired = isset($billingRequirements[$flightTypeId]);
        $currentFlightType->isInstructionType = isset($instructions[$flightTypeId]);
        $currentFlightType->isPilotCharged = isset($pilotCharges[$flightTypeId]);
        $currentFlightType->active = isset($actives[$flightTypeId]) ? 1 : 0;

        if ($currentFlightType->update($user) < 0) {
            $setupErrors[] = $langs->trans("Error") . ' : ' . implode(', ', $currentFlightType->errors);
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

    $setupMessages[] = $langs->trans("SetupSaved");
}

// Add a new flight type
if ($action === ACTION_ADD) {
    $newNumber = (int) GETPOST('new_flight_type_number', 'int', 2);
    $newName = trim(GETPOST('new_flight_type_name', 'alphanohtml', 2));

    $existingTypes = new Bbctypes($db);
    $existingTypes->fetchAll();

    $numberAlreadyUsed = false;
    foreach ($existingTypes->getLines() as $existingType) {
        if ((int) $existingType->getNumero() === $newNumber) {
            $numberAlreadyUsed = true;
        }
    }

    if ($newNumber <= 0 || $newName === '') {
        $setupErrors[] = "Le numéro et le nom du type de vol sont obligatoires.";
    } elseif ($numberAlreadyUsed) {
        $setupErrors[] = sprintf("Le numéro T%d est déjà utilisé par un autre type de vol.", $newNumber);
    } else {
        $newFlightType = new Bbctypes($db);
        $newFlightType->numero = $newNumber;
        $newFlightType->nom = $newName;
        $newFlightType->fkService = GETPOST('new_idprod', 'int', 2) ?: null;
        $newFlightType->points = GETPOST('new_flight_type_points', 'alphanohtml', 2) !== '' ? (int) GETPOST('new_flight_type_points',
            'int', 2) : null;
        $newFlightType->isMission = (bool) GETPOST('new_flight_type_mission', 'int', 2);
        $newFlightType->paxRequired = (bool) GETPOST('new_flight_type_pax', 'int', 2);
        $newFlightType->billingRequired = (bool) GETPOST('new_flight_type_billing', 'int', 2);
        $newFlightType->isInstructionType = (bool) GETPOST('new_flight_type_instruction', 'int', 2);
        $newFlightType->isPilotCharged = (bool) GETPOST('new_flight_type_charged', 'int', 2);
        $newFlightType->active = 1;

        if ($newFlightType->create($user) > 0) {
            $setupMessages[] = sprintf("Type de vol T%d - %s créé.", $newNumber, $newName);
        } else {
            $setupErrors[] = $langs->trans("Error") . ' : ' . implode(', ', $newFlightType->errors);
        }
    }
}

// Delete a flight type that is not used by any flight
if ($action === ACTION_DELETE) {
    $flightTypeId = GETPOST('id', 'int', 2);

    $flightTypeToDelete = new Bbctypes($db);
    if ($flightTypeToDelete->fetch($flightTypeId) <= 0) {
        $setupErrors[] = "Type de vol introuvable.";
    } elseif (countFlightsForType($flightTypeId) > 0) {
        $setupErrors[] = sprintf("Le type de vol %s est utilisé par des vols, il ne peut pas être supprimé. Il peut être désactivé.",
            $flightTypeToDelete->getLabel());
    } elseif ($flightTypeToDelete->delete($user) > 0) {
        $setupMessages[] = sprintf("Type de vol %s supprimé.", $flightTypeToDelete->getLabel());
    } else {
        $setupErrors[] = $langs->trans("Error") . ' : ' . implode(', ', $flightTypeToDelete->errors);
    }
}

/*
 * View
 */

$form = new Form($db);
$flightType->fetchAll('ASC', 'numero');

llxHeader('', $langs->trans("FLightLogSetup"), $help_url);

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans("FLightLogSetup"), $linkback, 'title_setup');

if (!empty($setupErrors)) {
    dol_htmloutput_errors(implode('<br/>', $setupErrors));
}

if (!empty($setupMessages)) {
    dol_htmloutput_mesg(implode('<br/>', $setupMessages));
}

?>

    <form method="POST">
        <input type="hidden" name="action" value="<?= ACTION_SAVE ?>"/>
        <input type="hidden" name="token" value="<?php echo newToken(); ?>"/>

        <!-- Flight types -->
        <table class="noborder" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Numéro") ?></th>
                <th><?= $langs->trans("Nom") ?></th>
                <th><?= $langs->trans("Service / produit") ?></th>
                <th title="Points gagnés (mission) ou montant facturé au pilote par vol. Vide = prix du service.">
                    <?= $langs->trans("Points / montant") ?>
                </th>
                <th title="Le vol est une mission pour le club : il rapporte des points au pilote et entre dans les notes de frais.">
                    <?= $langs->trans("Mission") ?>
                </th>
                <th title="Le nombre de passagers est obligatoire.">
                    <?= $langs->trans("Pax") ?>
                </th>
                <th title="Le vol doit être facturé à un client.">
                    <?= $langs->trans("A facturer") ?>
                </th>
                <th title="Vol d'instruction : l'organisateur est l'instructeur.">
                    <?= $langs->trans("Instruction") ?>
                </th>
                <th title="Le vol est facturé au pilote sur sa facture annuelle.">
                    <?= $langs->trans("Facturé au pilote") ?>
                </th>
                <th><?= $langs->trans("Actif") ?></th>
                <th></th>
            </tr>

            <?php foreach ($flightType->lines as $flightTypeLine): ?>
                <?php $numberOfFlights = countFlightsForType($flightTypeLine->getId()); ?>
                <tr class="<?= $flightTypeLine->id % 2 == 0 ? "pair" : "impair" ?>">
                    <td>
                        T<input type="number" min="1" size="3"
                                name="flight_type_number[<?= $flightTypeLine->getId() ?>]"
                                value="<?= $flightTypeLine->getNumero() ?>"/>
                    </td>
                    <td>
                        <input type="text" maxlength="64"
                               name="flight_type_name[<?= $flightTypeLine->getId() ?>]"
                               value="<?= dol_escape_htmltag($flightTypeLine->getNom()) ?>"/>
                    </td>
                    <td>
                        <?php $form->select_produits($flightTypeLine->getFkService(),
                            'idprod[' . $flightTypeLine->getId() . ']', $filtertype, $conf->product->limit_size,
                            $buyer->price_level, 1, 2, '', 1, array(), $buyer->id); ?>
                    </td>
                    <td>
                        <input type="number" name="flight_type_points[<?= $flightTypeLine->getId() ?>]"
                               value="<?= $flightTypeLine->getPoints() === null ? '' : $flightTypeLine->getPoints() ?>"
                               placeholder="prix du service"/>
                    </td>
                    <td>
                        <input type="checkbox" value="1"
                               name="flight_type_mission[<?= $flightTypeLine->getId() ?>]"
                            <?= $flightTypeLine->isMission() ? 'checked="checked"' : '' ?> />
                    </td>
                    <td>
                        <input type="checkbox" value="1"
                               name="flight_type_pax[<?= $flightTypeLine->getId() ?>]"
                            <?= $flightTypeLine->isPaxRequired() ? 'checked="checked"' : '' ?> />
                    </td>
                    <td>
                        <input type="checkbox" value="1"
                               name="flight_type_billing[<?= $flightTypeLine->getId() ?>]"
                            <?= $flightTypeLine->isBillingRequired() ? 'checked="checked"' : '' ?> />
                    </td>
                    <td>
                        <input type="checkbox" value="1"
                               name="flight_type_instruction[<?= $flightTypeLine->getId() ?>]"
                            <?= $flightTypeLine->isInstruction() ? 'checked="checked"' : '' ?> />
                    </td>
                    <td>
                        <input type="checkbox" value="1"
                               name="flight_type_charged[<?= $flightTypeLine->getId() ?>]"
                            <?= $flightTypeLine->isPilotCharged() ? 'checked="checked"' : '' ?> />
                    </td>
                    <td>
                        <input type="checkbox" value="1"
                               name="flight_type_active[<?= $flightTypeLine->getId() ?>]"
                            <?= $flightTypeLine->getActive() ? 'checked="checked"' : '' ?> />
                    </td>
                    <td>
                        <?php if ($numberOfFlights > 0): ?>
                            <span class="text-muted"><?= sprintf('%d vol(s)', $numberOfFlights) ?></span>
                        <?php else: ?>
                            <a class="butActionDelete"
                               href="<?= $_SERVER['PHP_SELF'] ?>?action=<?= ACTION_DELETE ?>&id=<?= $flightTypeLine->getId() ?>&token=<?= newToken() ?>"
                               onclick="return confirm('<?= dol_escape_js(sprintf('Supprimer le type de vol %s ?', $flightTypeLine->getLabel())) ?>');">
                                <?= $langs->trans("Delete") ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        </table>

        <table class="noborder mt-2" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Champ") ?></th>
                <th><?= $langs->trans("Valeur") ?></th>
            </tr>

            <tr class="impair">
                <td>
                    Vol client
                </td>
                <td>
                    <?php $form->select_produits($conf->global->BBC_FLIGHT_TYPE_CUSTOMER, 'customer_product',
                        $filtertype, $conf->product->limit_size, $buyer->price_level, 1, 2, '', 1, array(),
                        $buyer->id); ?>
                </td>
            </tr>

            <tr class="pair">
                <td>
                    Client par défaut
                </td>
                <td>
                    <?php echo $form->select_thirdparty_list($conf->global->BBC_FLIGHT_DEFAULT_CUSTOMER,
                        'defaultCustomer'); ?>
                </td>
            </tr>

            <tr class="impar">
                <td>
                    <?php echo $langs->trans('Points organisateur') ?>
                    <br/><span class="text-muted">Pour chaque vol de type mission dont le membre est organisateur.</span>
                </td>

                <td>
                    <input type="number" id="points_bonus_organisator" name="points_bonus_organisator"
                           value="<?php echo $conf->global->BBC_POINTS_BONUS_ORGANISATOR ?>"/>
                </td>
            </tr>

            <tr class="pair">
                <td>
                    <?php echo $langs->trans('Points instructeur') ?>
                    <br/><span class="text-muted">Pour chaque vol d'instruction dont le membre est instructeur.</span>
                </td>

                <td>
                    <input type="number" id="points_bonus_instructor" name="points_bonus_instructor"
                           value="<?php echo $conf->global->BBC_POINTS_BONUS_INSTRUCTOR ?>"/>
                </td>
            </tr>
        </table>


        <table class="noborder mt-2" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Champ") ?></th>
                <th><?= $langs->trans("Valeur") ?></th>
            </tr>

            <tr class="pair">
                <td>
                    <?php echo $langs->trans('Compte en banque par défaut') ?>
                </td>
                <td>
                    <?php $form->select_comptes($conf->global->BBC_DEFAULT_BANK_ACCOUNT, 'default_bank_account', 0, '',
                        1); ?>
                </td>
            </tr>

            <tr class="impair">
                <td>
                    <?php echo $langs->trans('Condition de vente par défaut') ?>
                </td>
                <td>
                    <?php $form->select_conditions_paiements($conf->global->BBC_DEFAULT_PAYMENT_TERM_ID,
                        'bill_condition'); ?>
                </td>
            </tr>

            <tr class="pair">
                <td>
                    <?php echo $langs->trans('Type de payement par défaut') ?>
                </td>
                <td>
                    <?php $form->select_types_paiements($conf->global->BBC_DEFAULT_PAYMENT_TYPE_ID,
                        'bill_payment_type'); ?>
                </td>
            </tr>
        </table>


        <table class="noborder mt-2" width="100%">
            <tr class="liste_titre">
                <th><?= $langs->trans("Champ") ?></th>
                <th><?= $langs->trans("Valeur") ?></th>
            </tr>

            <tr class="impair">
                <td>
                    <?php echo $langs->trans('E-mail additionel sur les erreurs') ?>
                </td>
                <td>
                    <textarea rows="4" cols="80" name="damage_emails"><?php echo $conf->global->BBC_DAMAGE_EMAILS; ?></textarea>
                    <br/><span class="text-muted">Separer par des ; </span>
                </td>
            </tr>

        </table>
        <input type="submit" value="<?= $langs->trans("Save") ?>"/>
    </form>

    <!-- New flight type -->
    <form method="POST">
        <input type="hidden" name="action" value="<?= ACTION_ADD ?>"/>
        <input type="hidden" name="token" value="<?php echo newToken(); ?>"/>

        <table class="noborder mt-2" width="100%">
            <tr class="liste_titre">
                <th colspan="10"><?= $langs->trans("Ajouter un type de vol") ?></th>
            </tr>
            <tr class="liste_titre">
                <th><?= $langs->trans("Numéro") ?></th>
                <th><?= $langs->trans("Nom") ?></th>
                <th><?= $langs->trans("Service / produit") ?></th>
                <th><?= $langs->trans("Points / montant") ?></th>
                <th><?= $langs->trans("Mission") ?></th>
                <th><?= $langs->trans("Pax") ?></th>
                <th><?= $langs->trans("A facturer") ?></th>
                <th><?= $langs->trans("Instruction") ?></th>
                <th><?= $langs->trans("Facturé au pilote") ?></th>
                <th></th>
            </tr>
            <tr class="impair">
                <td>T<input type="number" min="1" size="3" name="new_flight_type_number" value=""/></td>
                <td><input type="text" maxlength="64" name="new_flight_type_name" value=""/></td>
                <td>
                    <?php $form->select_produits('', 'new_idprod', $filtertype, $conf->product->limit_size,
                        $buyer->price_level, 1, 2, '', 1, array(), $buyer->id); ?>
                </td>
                <td><input type="number" name="new_flight_type_points" value="" placeholder="prix du service"/></td>
                <td><input type="checkbox" value="1" name="new_flight_type_mission"/></td>
                <td><input type="checkbox" value="1" name="new_flight_type_pax"/></td>
                <td><input type="checkbox" value="1" name="new_flight_type_billing"/></td>
                <td><input type="checkbox" value="1" name="new_flight_type_instruction"/></td>
                <td><input type="checkbox" value="1" name="new_flight_type_charged" checked="checked"/></td>
                <td>
                    <button class="butAction" type="submit"><?= $langs->trans("Ajouter") ?></button>
                </td>
            </tr>
        </table>
    </form>
<?php
llxFooter();
$db->close();
?>
