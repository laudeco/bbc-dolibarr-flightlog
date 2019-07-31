<?php
/**
 *    \file       flightlog/bbcvols_list.php
 *        \ingroup    flightlog
 *        \brief      This file is an example of a php page
 *                    Initialy built by build_class_from_table on 2017-02-10 16:55
 */

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

// Change this following line to use the correct relative path from htdocs
require_once(DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
dol_include_once('/flightballoon/bbc_ballons.class.php');
dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');
dol_include_once('/flightlog/lib/flightLog.lib.php');

// Load traductions files requiredby by page
global $user, $langs, $conf;

$langs->load("mymodule@flightlog");
$langs->load("other");

$action = GETPOST('action', 'alpha');
$massaction = GETPOST('massaction', 'alpha');
$show_files = GETPOST('show_files', 'int');
$confirm = GETPOST('confirm', 'alpha');
$toselect = GETPOST('toselect', 'array');

$id = GETPOST('id', 'int');
$backtopage = GETPOST('backtopage');
$myparam = GETPOST('myparam', 'alpha');

$search_all = trim(GETPOST("sall"));
$search_idBBC_vols = GETPOST('search_idBBC_vols', 'int');
$search_date = GETPOST('search_date', 'alpha');
$search_lieuD = GETPOST('search_lieuD', 'alpha');
$search_lieuA = GETPOST('search_lieuA', 'alpha');
$search_heureD = GETPOST('search_heureD', 'alpha');
$search_heureA = GETPOST('search_heureA', 'alpha');
$search_BBC_ballons_idBBC_ballons = GETPOST('search_BBC_ballons_idBBC_ballons', 'int');
$search_nbrPax = GETPOST('search_nbrPax', 'alpha');
$search_remarque = GETPOST('search_remarque', 'alpha');
$search_incidents = GETPOST('search_incidents', 'alpha');
$search_fk_type = GETPOST('search_fk_type', 'int');
$search_fk_pilot = GETPOST('search_fk_pilot', 'int') ?: ($user->admin ? '' : $user->id);
$search_fk_organisateur = GETPOST('search_fk_organisateur', 'int');
$search_is_facture = GETPOST('search_is_facture', 'int') === ''? -1 : (int)GETPOST('search_is_facture', 'int');
if($search_is_facture === 0){
    $search_is_facture = '<=0';
}
$search_kilometers = GETPOST('search_kilometers', 'alpha');
$search_cost = GETPOST('search_cost', 'alpha');
$search_fk_receiver = GETPOST('search_fk_receiver', 'int');
$search_justif_kilometers = GETPOST('search_justif_kilometers', 'alpha');



$search_myfield = GETPOST('search_myfield');
$optioncss = GETPOST('optioncss', 'alpha');

// Load variable for pagination
$limit = GETPOST("limit") ? GETPOST("limit", "int") : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');
if ($page == -1) {
    $page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) {
    $sortfield = "t.date, t.heureD";
} // Set here default search field
if (!$sortorder) {
    $sortorder = "DESC";
}

// Protection if external user
$socid = 0;
if ($user->societe_id > 0) {
    $socid = $user->societe_id;
    //accessforbidden();
}

// Initialize technical object to manage context to save list fields
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'flightLoglist';

// Initialize technical object to manage hooks. Note that conf->hooks_modules contains array
$hookmanager->initHooks(array('flightLoglist'));
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('flightlog');
$search_array_options = $extrafields->getOptionalsFromPost($extralabels, '', 'search_');

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
    't.ref'         => 'Ref',
    't.note_public' => 'NotePublic',
);
if (empty($user->socid)) {
    $fieldstosearchall["t.note_private"] = "NotePrivate";
}

// Definition of fields for list
$arrayfields = array(

    't.idBBC_vols'                => array('label' => $langs->trans("FieldidBBC_vols"), 'checked' => 1),
    't.date'                     => array('label' => $langs->trans("FieldDate"), 'checked' => 1),
    't.lieuD'                     => array('label' => $langs->trans("FieldlieuD"), 'checked' => 1),
    't.lieuA'                     => array('label' => $langs->trans("FieldlieuA"), 'checked' => 1),
    't.heureD'                    => array('label' => $langs->trans("FieldheureD"), 'checked' => 1),
    't.heureA'                    => array('label' => $langs->trans("FieldheureA"), 'checked' => 1),
    't.BBC_ballons_idBBC_ballons' => array('label' => $langs->trans("FieldBBC_ballons_idBBC_ballons"), 'checked' => 1),
    't.nbrPax'                    => array('label' => $langs->trans("FieldnbrPax"), 'checked' => 1),
    //'t.remarque'                  => array('label' => $langs->trans("Fieldremarque"), 'checked' => 1),
    //'t.incidents'                 => array('label' => $langs->trans("Fieldincidents"), 'checked' => 1),
    't.fk_type'                   => array('label' => $langs->trans("Fieldfk_type"), 'checked' => 1),
    't.fk_pilot'                  => array('label' => $langs->trans("Fieldfk_pilot"), 'checked' => 1),
    't.fk_organisateur'           => array('label' => $langs->trans("Fieldfk_organisateur"), 'checked' => 1),
    't.is_facture'                => array('label' => $langs->trans("Fieldis_facture"), 'checked' => 1),
    't.kilometers'                => array('label' => $langs->trans("Fieldkilometers"), 'checked' => 0),
    't.cost'                      => array('label' => $langs->trans("Fieldcost"), 'checked' => 0),
    //'t.fk_receiver'               => array('label' => $langs->trans("Fieldfk_receiver"), 'checked' => 1),
    //'t.justif_kilometers'         => array('label' => $langs->trans("Fieldjustif_kilometers"), 'checked' => 1),
    //'t.entity'=>array('label'=>$langs->trans("Entity"), 'checked'=>1, 'enabled'=>(! empty($conf->multicompany->enabled) && empty($conf->multicompany->transverse_mode))),
    't.datec'                     => array('label'    => $langs->trans("DateCreationShort"),
                                           'checked'  => 0,
                                           'position' => 500
    ),
    't.tms'                       => array('label'    => $langs->trans("DateModificationShort"),
                                           'checked'  => 0,
                                           'position' => 500
    ),
    //'t.statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1, 'position'=>1000),
);
// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
    foreach ($extrafields->attribute_label as $key => $val) {
        $arrayfields["ef." . $key] = array('label'    => $extrafields->attribute_label[$key],
                                           'checked'  => $extrafields->attribute_list[$key],
                                           'position' => $extrafields->attribute_pos[$key],
                                           'enabled'  => $extrafields->attribute_perms[$key]
        );
    }
}


// Load object if id or ref is provided as parameter
$object = new Bbcvols($db);
if (($id > 0 || !empty($ref)) && $action != 'add') {
    $result = $object->fetch($id, $ref);
    if ($result < 0) {
        dol_print_error($db);
    }
}


/*******************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 ********************************************************************/

if (GETPOST('cancel')) {
    $action = 'list';
    $massaction = '';
}
if (!GETPOST('confirmmassaction') && $massaction != 'presend' && $massaction != 'confirm_presend') {
    $massaction = '';
}

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object,
    $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
    // Selection of new fields
    include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

    // Purge search criteria
    if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) // All tests are required to be compatible with all browsers
    {

        $search_idBBC_vols = '';
        $search_date = '';
        $search_lieuD = '';
        $search_lieuA = '';
        $search_heureD = '';
        $search_heureA = '';
        $search_BBC_ballons_idBBC_ballons = '';
        $search_nbrPax = '';
        $search_remarque = '';
        $search_incidents = '';
        $search_fk_type = '';
        $search_fk_pilot = '';
        $search_fk_organisateur = '';
        $search_is_facture = -1;
        $search_kilometers = '';
        $search_cost = '';
        $search_fk_receiver = '';
        $search_justif_kilometers = '';


        $search_date_creation = '';
        $search_date_update = '';
        $toselect = '';
        $search_array_options = array();
    }

    // Mass actions
    $objectclass = 'BbcVols';
    $objectlabel = 'Vol';
    $permtoread = $user->rights->bbcvols->read;
    $permtodelete = $user->rights->bbcvols->delete;
    $uploaddir = $conf->bbcvols->dir_output;
}


/***************************************************
 * VIEW
 *
 * Put here all code to build page
 ****************************************************/

$now = dol_now();

$form = new Form($db);

//$help_url="EN:Module_Customers_Orders|FR:Module_Commandes_Clients|ES:Módulo_Pedidos_de_clientes";
$help_url = '';
$title = $langs->trans('MyModuleListTitle');

// Put here content of your page


$sql = "SELECT";
$sql .= " t.idBBC_vols,";
$sql .= " t.date,";
$sql .= " t.lieuD,";
$sql .= " t.lieuA,";
$sql .= " t.heureD,";
$sql .= " t.heureA,";
$sql .= " t.BBC_ballons_idBBC_ballons,";
$sql .= " t.nbrPax,";
$sql .= " t.remarque,";
$sql .= " t.incidents,";
$sql .= " t.fk_type,";
$sql .= " t.fk_pilot,";
$sql .= " t.fk_organisateur,";
$sql .= " t.is_facture,";
$sql .= " t.kilometers,";
$sql .= " t.cost,";
$sql .= " t.fk_receiver,";
$sql .= " t.justif_kilometers, ";
$sql .= " t.date_creation, ";
$sql .= " t.date_update, ";
$sql .= " balloon.immat as bal, ";
$sql .= " CONCAT_WS(' ', 'T', flightType.numero,'-', flightType.nom) as flight_type, ";
$sql .= " CONCAT_WS(' ', pilot.firstname, pilot.lastname) as pilot, ";
$sql .= " CONCAT_WS(' ', organisator.firstname, organisator.lastname) as organisator, ";
$sql .= " CONCAT_WS(' ', receiver.firstname , receiver.lastname) as receiver";

// Add fields from extrafields
foreach ($extrafields->attribute_label as $key => $val) {
    $sql .= ($extrafields->attribute_type[$key] != 'separate' ? ",ef." . $key . ' as options_' . $key : '');
}
// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect',
    $parameters);    // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;
$sql .= " FROM " . MAIN_DB_PREFIX . "bbc_vols as t";
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
    $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "bbc_vols_extrafields as ef on (t.rowid = ef.fk_object)";
}
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'bbc_ballons as balloon on (t.BBC_ballons_idBBC_ballons = balloon.rowid)';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'bbc_types as flightType on (t.fk_type = flightType.idType)';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'user as pilot on (t.fk_pilot = pilot.rowid)';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'user as organisator on (t.fk_organisateur = organisator.rowid)';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'user as receiver on (t.fk_receiver = receiver.rowid)';

$sql .= " WHERE 1 = 1";

if($search_all){
    $sql .= natural_search(["idBBC_vols",  "lieuD", "lieuA", "pilot.lastname", "pilot.firstname", "balloon.immat"], $search_all);
}

if ($search_idBBC_vols) {
    $sql .= natural_search("idBBC_vols", $search_idBBC_vols);
}
if ($search_date) {
    $sql .= natural_search("date", $search_date);
}
if ($search_lieuD) {
    $sql .= natural_search("lieuD", $search_lieuD);
}
if ($search_lieuA) {
    $sql .= natural_search("lieuA", $search_lieuA);
}
if ($search_heureD) {
    $sql .= natural_search("heureD", $search_heureD);
}
if ($search_heureA) {
    $sql .= natural_search("heureA", $search_heureA);
}
if ($search_BBC_ballons_idBBC_ballons) {
    $sql .= natural_search("BBC_ballons_idBBC_ballons", $search_BBC_ballons_idBBC_ballons);
}
if ($search_nbrPax) {
    $sql .= natural_search("nbrPax", $search_nbrPax, 1);
}
if ($search_remarque) {
    $sql .= natural_search("remarque", $search_remarque);
}
if ($search_incidents) {
    $sql .= natural_search("incidents", $search_incidents);
}
if ($search_fk_type) {
    $sql .= natural_search("fk_type", $search_fk_type);
}
if ($search_fk_pilot && $search_fk_pilot != -1) {
    $sql .= natural_search("fk_pilot", $search_fk_pilot, 2);
}
if ($search_fk_organisateur && $search_fk_organisateur != -1) {
    $sql .= natural_search("fk_organisateur", $search_fk_organisateur);
}

if ($search_is_facture != -1) {
    $sql .= natural_search("is_facture", $search_is_facture,1);
}
if ($search_kilometers) {
    $sql .= natural_search("kilometers", $search_kilometers, 1);
}
if ($search_cost) {
    $sql .= natural_search("cost", $search_cost, 1);
}
if ($search_fk_receiver) {
    $sql .= natural_search("fk_receiver", $search_fk_receiver);
}
if ($search_justif_kilometers) {
    $sql .= natural_search("justif_kilometers", $search_justif_kilometers);
}


if ($sall) {
    $sql .= natural_search(array_keys($fieldstosearchall), $sall);
}
// Add where from extra fields
foreach ($search_array_options as $key => $val) {
    $crit = $val;
    $tmpkey = preg_replace('/search_options_/', '', $key);
    $typ = $extrafields->attribute_type[$tmpkey];
    $mode = 0;
    if (in_array($typ, array('int', 'double'))) {
        $mode = 1;
    }    // Search on a numeric
    if ($val && (($crit != '' && !in_array($typ, array('select'))) || !empty($crit))) {
        $sql .= natural_search('ef.' . $tmpkey, $crit, $mode);
    }
}
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere',
    $parameters);    // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;
$sql .= $db->order($sortfield, $sortorder);
//$sql.= $db->plimit($conf->liste_limit+1, $offset);

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
    $result = $db->query($sql);
    $nbtotalofrecords = $db->num_rows($result);
}

$sql .= $db->plimit($limit + 1, $offset);

dol_syslog($script_file, LOG_DEBUG);
$resql = $db->query($sql);
if (!$resql) {
    dol_print_error($db);
    exit;
}

$num = $db->num_rows($resql);

// Direct jump if only one record found
if ($num == 1 && !empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $search_all) {
    $obj = $db->fetch_object($resql);
    $id = $obj->idBBC_vols;
    header("Location: " . DOL_URL_ROOT . '/flightlog/card.php?id=' . $id);
    exit;
}

llxHeader('', $title, $help_url);

$arrayofselected = is_array($toselect) ? $toselect : array();

$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
    $param .= '&contextpage=' . $contextpage;
}
if ($limit > 0 && $limit != $conf->liste_limit) {
    $param .= '&limit=' . $limit;
}
if ($search_idBBC_vols  != '') {
    $param .= '&amp;search_idBBC_vols=' . urlencode($search_idBBC_vols);
}
if ($search_date  != '') {
    $param .= '&amp;search_date=' . urlencode($search_date);
}
if ($search_lieuD  != '') {
    $param .= '&amp;search_lieuD=' . urlencode($search_lieuD);
}
if ($search_lieuA  != '') {
    $param .= '&amp;search_lieuA=' . urlencode($search_lieuA);
}
if ($search_heureD  != '') {
    $param .= '&amp;search_heureD=' . urlencode($search_heureD);
}
if ($search_heureA  != '') {
    $param .= '&amp;search_heureA=' . urlencode($search_heureA);
}
if ($search_BBC_ballons_idBBC_ballons  != '') {
    $param .= '&amp;search_BBC_ballons_idBBC_ballons=' . urlencode($search_BBC_ballons_idBBC_ballons);
}
if ($search_nbrPax  != '') {
    $param .= '&amp;search_nbrPax=' . urlencode($search_nbrPax);
}
if ($search_remarque  != '') {
    $param .= '&amp;search_remarque=' . urlencode($search_remarque);
}
if ($search_incidents  != '') {
    $param .= '&amp;search_incidents=' . urlencode($search_incidents);
}
if ($search_fk_type  != '') {
    $param .= '&amp;search_fk_type=' . urlencode($search_fk_type);
}
if ($search_fk_pilot != '') {
    $param .= '&amp;search_fk_pilot=' . urlencode($search_fk_pilot);
}
if ($search_fk_organisateur  != '') {
    $param .= '&amp;search_fk_organisateur=' . urlencode($search_fk_organisateur);
}
if ($search_is_facture  != -1) {
    $param .= '&amp;search_is_facture=' . urlencode($search_is_facture);
}
if ($search_kilometers  != '') {
    $param .= '&amp;search_kilometers=' . urlencode($search_kilometers);
}
if ($search_cost  != '') {
    $param .= '&amp;search_cost=' . urlencode($search_cost);
}
if ($search_fk_receiver  != '') {
    $param .= '&amp;search_fk_receiver=' . urlencode($search_fk_receiver);
}
if ($search_justif_kilometers  != '') {
    $param .= '&amp;search_justif_kilometers=' . urlencode($search_justif_kilometers);
}
if ($search_all != '') {
    $param .= '&amp;sall=' . urlencode($search_all);
}

if ($optioncss != '') {
    $param .= '&optioncss=' . $optioncss;
}
// Add $param from extra fields
foreach ($search_array_options as $key => $val) {
    $crit = $val;
    $tmpkey = preg_replace('/search_options_/', '', $key);
    if ($val != '') {
        $param .= '&search_options_' . $tmpkey . '=' . urlencode($val);
    }
}

$arrayofmassactions = array(
    'presend'  => $langs->trans("SendByMail"),
    'builddoc' => $langs->trans("PDFMerge"),
);
if ($user->rights->flightlog->supprimer) {
    $arrayofmassactions['delete'] = $langs->trans("Delete");
}
if ($massaction == 'presend') {
    $arrayofmassactions = array();
}
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

print '<form method="POST" id="searchFormList" action="' . $_SERVER["PHP_SELF"] . '">';
if ($optioncss != '') {
    print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
}
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="contextpage" value="' . $contextpage . '">';


$moreHtml = "";
if($search_all !== ""){
    $moreHtml = "<p>".$langs->trans(sprintf("La liste est en recherche globale sur : l'identifiant du vol, le nom du pilote, l'immat du ballon, le lieu de Décollage et le lieu d'atterissage : %s", $search_all))."</p>";
}

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords,
    'title_companies', 0, "", '', $limit);
echo $moreHtml;
if ($sall) {
    foreach ($fieldstosearchall as $key => $val) {
        $fieldstosearchall[$key] = $langs->trans($val);
    }
    print $langs->trans("FilterOnInto", $sall) . join(', ', $fieldstosearchall);
}

$moreforfilter = '';
$moreforfilter .= '<div class="divsearchfield">';
//$moreforfilter .= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="' . dol_escape_htmltag($search_myfield) . '">';
$moreforfilter .= '</div>';

$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldPreListTitle',
    $parameters);    // Note that $action and $object may have been modified by hook
if (empty($reshook)) {
    $moreforfilter .= $hookmanager->resPrint;
} else {
    $moreforfilter = $hookmanager->resPrint;
}

if (!empty($moreforfilter)) {
    print '<div class="liste_titre liste_titre_bydiv centpercent">';
    print $moreforfilter;
    print '</div>';
}

$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields,
    $varpage);    // This also change content of $arrayfields

print '<div class="div-table-responsive">';
print '<table class="tagtable liste' . ($moreforfilter ? " listwithfilterbefore" : "") . '">' . "\n";

// Fields title
print '<tr class="liste_titre">';
// 
if (!empty($arrayfields['t.idBBC_vols']['checked'])) {
    print_liste_field_titre($arrayfields['t.idBBC_vols']['label'], $_SERVER['PHP_SELF'], 't.idBBC_vols', '', $params,
        '', $sortfield, $sortorder);
}
if (!empty($arrayfields['t.date']['checked'])) {
    print_liste_field_titre($arrayfields['t.date']['label'], $_SERVER['PHP_SELF'], 't.date', '', $params,
        '', $sortfield, $sortorder);
}
if (!empty($arrayfields['t.lieuD']['checked'])) {
    print_liste_field_titre($arrayfields['t.lieuD']['label'], $_SERVER['PHP_SELF'], 't.lieuD', '', $params, '',
        $sortfield, $sortorder);
}
if (!empty($arrayfields['t.lieuA']['checked'])) {
    print_liste_field_titre($arrayfields['t.lieuA']['label'], $_SERVER['PHP_SELF'], 't.lieuA', '', $params, '',
        $sortfield, $sortorder);
}
if (!empty($arrayfields['t.heureD']['checked'])) {
    print_liste_field_titre($arrayfields['t.heureD']['label'], $_SERVER['PHP_SELF'], 't.heureD', '', $params, '',
        $sortfield, $sortorder);
}
if (!empty($arrayfields['t.heureA']['checked'])) {
    print_liste_field_titre($arrayfields['t.heureA']['label'], $_SERVER['PHP_SELF'], 't.heureA', '', $params, '',
        $sortfield, $sortorder);
}
if (!empty($arrayfields['t.BBC_ballons_idBBC_ballons']['checked'])) {
    print_liste_field_titre($arrayfields['t.BBC_ballons_idBBC_ballons']['label'], $_SERVER['PHP_SELF'],
        't.BBC_ballons_idBBC_ballons', '', $params, '', $sortfield, $sortorder);
}
if (!empty($arrayfields['t.nbrPax']['checked'])) {
    print_liste_field_titre($arrayfields['t.nbrPax']['label'], $_SERVER['PHP_SELF'], 't.nbrPax', '', $params, '',
        $sortfield, $sortorder);
}
/*if (!empty($arrayfields['t.remarque']['checked'])) {
    print_liste_field_titre($arrayfields['t.remarque']['label'], $_SERVER['PHP_SELF'], 't.remarque', '', $params, '',
        $sortfield, $sortorder);
}
if (!empty($arrayfields['t.incidents']['checked'])) {
    print_liste_field_titre($arrayfields['t.incidents']['label'], $_SERVER['PHP_SELF'], 't.incidents', '', $params, '',
        $sortfield, $sortorder);
}*/
if (!empty($arrayfields['t.fk_type']['checked'])) {
    print_liste_field_titre($arrayfields['t.fk_type']['label'], $_SERVER['PHP_SELF'], 't.fk_type', '', $params, '',
        $sortfield, $sortorder);
}
if (!empty($arrayfields['t.fk_pilot']['checked'])) {
    print_liste_field_titre($arrayfields['t.fk_pilot']['label'], $_SERVER['PHP_SELF'], 't.fk_pilot', '', $params, '',
        $sortfield, $sortorder);
}
if (!empty($arrayfields['t.fk_organisateur']['checked'])) {
    print_liste_field_titre($arrayfields['t.fk_organisateur']['label'], $_SERVER['PHP_SELF'], 't.fk_organisateur', '',
        $params, '', $sortfield, $sortorder);
}

if (!empty($arrayfields['t.is_facture']['checked'])) {
    print_liste_field_titre($arrayfields['t.is_facture']['label'], $_SERVER['PHP_SELF'], 't.is_facture', '', $params,
        '', $sortfield, $sortorder);
}
if (!empty($arrayfields['t.kilometers']['checked'])) {
    print_liste_field_titre($arrayfields['t.kilometers']['label'], $_SERVER['PHP_SELF'], 't.kilometers', '', $params,
        '', $sortfield, $sortorder);
}
if (!empty($arrayfields['t.cost']['checked'])) {
    print_liste_field_titre($arrayfields['t.cost']['label'], $_SERVER['PHP_SELF'], 't.cost', '', $params, '',
        $sortfield, $sortorder);
}
/*
if (!empty($arrayfields['t.fk_receiver']['checked'])) {
    print_liste_field_titre($arrayfields['t.fk_receiver']['label'], $_SERVER['PHP_SELF'], 't.fk_receiver', '', $params,
        '', $sortfield, $sortorder);
}
if (!empty($arrayfields['t.justif_kilometers']['checked'])) {
    print_liste_field_titre($arrayfields['t.justif_kilometers']['label'], $_SERVER['PHP_SELF'], 't.justif_kilometers',
        '', $params, '', $sortfield, $sortorder);
}*/

// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
    foreach ($extrafields->attribute_label as $key => $val) {
        if (!empty($arrayfields["ef." . $key]['checked'])) {
            $align = $extrafields->getAlignFlag($key);
            print_liste_field_titre($extralabels[$key], $_SERVER["PHP_SELF"], "ef." . $key, "", $param,
                ($align ? 'align="' . $align . '"' : ''), $sortfield, $sortorder);
        }
    }
}
// Hook fields
$parameters = array('arrayfields' => $arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListTitle',
    $parameters);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
if (!empty($arrayfields['t.datec']['checked'])) {
    print_liste_field_titre($arrayfields['t.datec']['label'], $_SERVER["PHP_SELF"], "t.datec", "", $param,
        'align="center" class="nowrap"', $sortfield, $sortorder);
}
if (!empty($arrayfields['t.tms']['checked'])) {
    print_liste_field_titre($arrayfields['t.tms']['label'], $_SERVER["PHP_SELF"], "t.tms", "", $param,
        'align="center" class="nowrap"', $sortfield, $sortorder);
}
//if (! empty($arrayfields['t.status']['checked'])) print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"],"t.status","",$param,'align="center"',$sortfield,$sortorder);
print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', 'align="right"', $sortfield, $sortorder,
    'maxwidthsearch ');
print '</tr>' . "\n";

// Fields title search
print '<tr class="liste_titre">';
// 
if (!empty($arrayfields['t.idBBC_vols']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_idBBC_vols" value="' . $search_idBBC_vols . '" size="10"></td>';
}
if (!empty($arrayfields['t.date']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_date" value="' . $search_date . '" size="10"></td>';
}
if (!empty($arrayfields['t.lieuD']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_lieuD" value="' . $search_lieuD . '" size="10"></td>';
}
if (!empty($arrayfields['t.lieuA']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_lieuA" value="' . $search_lieuA . '" size="10"></td>';
}
if (!empty($arrayfields['t.heureD']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_heureD" value="' . $search_heureD . '" size="10"></td>';
}
if (!empty($arrayfields['t.heureA']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_heureA" value="' . $search_heureA . '" size="10"></td>';
}
if (!empty($arrayfields['t.BBC_ballons_idBBC_ballons']['checked'])) {

    print '<td class="liste_titre">';
    select_balloons($search_BBC_ballons_idBBC_ballons, "search_BBC_ballons_idBBC_ballons");
    print '</td>';
}
if (!empty($arrayfields['t.nbrPax']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_nbrPax" value="' . $search_nbrPax . '" size="10"></td>';
}
/*if (!empty($arrayfields['t.remarque']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_remarque" value="' . $search_remarque . '" size="10"></td>';
}
if (!empty($arrayfields['t.incidents']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_incidents" value="' . $search_incidents . '" size="10"></td>';
}*/
if (!empty($arrayfields['t.fk_type']['checked'])) {
    print '<td class="liste_titre fk_type">';
        select_flight_type($search_fk_type, 'search_fk_type', true);
    print '</td>';
}
if (!empty($arrayfields['t.fk_pilot']['checked'])) {
    print '<td class="liste_titre">';
        print $form->select_dolusers($search_fk_pilot, "search_fk_pilot", true, null, 0, '', '', 0,0,0,'',0,'','', true);
    print '</td>';
}
if (!empty($arrayfields['t.fk_organisateur']['checked'])) {
    print '<td class="liste_titre">';
        print $form->select_dolusers($search_fk_organisateur, "search_fk_organisateur", true, null, 0, '', '', 0,0,0,'',0,'','', true);
    print '</td>';
}

if (!empty($arrayfields['t.is_facture']['checked'])) {
    print '<td class="liste_titre">';
    print '<select name="search_is_facture"><option value="-1" '.($search_is_facture != 1 && $search_is_facture != 0 && $search_is_facture != '<=0'? 'selected' : '' ).'></option><option value="1" '.($search_is_facture == 1 ? 'selected' : '' ).'>Facturé</option><option value="0" '.($search_is_facture == 0 || $search_is_facture == '<=0' ? 'selected' : '' ).'>Ouvert</option></select>';
    print '</td>';
}

if (!empty($arrayfields['t.kilometers']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_kilometers" value="' . $search_kilometers . '" size="10"></td>';
}
if (!empty($arrayfields['t.cost']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_cost" value="' . $search_cost . '" size="10"></td>';
}
/*if (!empty($arrayfields['t.fk_receiver']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_receiver" value="' . $search_fk_receiver . '" size="10"></td>';
}
if (!empty($arrayfields['t.justif_kilometers']['checked'])) {
    print '<td class="liste_titre"><input type="text" class="flat" name="search_justif_kilometers" value="' . $search_justif_kilometers . '" size="10"></td>';
}*/

// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
    foreach ($extrafields->attribute_label as $key => $val) {
        if (!empty($arrayfields["ef." . $key]['checked'])) {
            $align = $extrafields->getAlignFlag($key);
            $typeofextrafield = $extrafields->attribute_type[$key];
            print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '">';
            if (in_array($typeofextrafield, array('varchar', 'int', 'double', 'select'))) {
                $crit = $val;
                $tmpkey = preg_replace('/search_options_/', '', $key);
                $searchclass = '';
                if (in_array($typeofextrafield, array('varchar', 'select'))) {
                    $searchclass = 'searchstring';
                }
                if (in_array($typeofextrafield, array('int', 'double'))) {
                    $searchclass = 'searchnum';
                }
                print '<input class="flat' . ($searchclass ? ' ' . $searchclass : '') . '" size="4" type="text" name="search_options_' . $tmpkey . '" value="' . dol_escape_htmltag($search_array_options['search_options_' . $tmpkey]) . '">';
            }
            print '</td>';
        }
    }
}


// Fields from hook
$parameters = array('arrayfields' => $arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListOption',
    $parameters);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
if (!empty($arrayfields['t.datec']['checked'])) {
    // Date creation
    print '<td class="liste_titre">';
    print '</td>';
}
if (!empty($arrayfields['t.tms']['checked'])) {
    // Date modification
    print '<td class="liste_titre">';
    print '</td>';
}


// Action column
print '<td class="liste_titre" align="right">';
$searchpitco = $form->showFilterAndCheckAddButtons($massactionbutton ? 1 : 0, 'checkforselect', 1);
print $searchpitco;
print '</td>';
print '</tr>' . "\n";


$i = 0;
$var = true;
$totalarray = array();
$flight = new Bbcvols($db);
while ($i < min($num, $limit)) {
    $obj = $db->fetch_object($resql);

    if ($obj) {
        $var = !$var;

        $flight->idBBC_vols = $obj->idBBC_vols;
        $flight->date = $obj->date;
        $flight->heureA = $obj->heureA;
        $flight->heureD = $obj->heureD;
        $flight->setRef($obj->idBBC_vols);
        $flight->fk_pilot = $obj->pilot;

        // Show here line of result
        print '<tr ' . $bc[$var] . '>';

        if (! empty($arrayfields['t.idBBC_vols']['checked']))
        {
            print '<td>'.$flight->getNomUrl(0).'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.date']['checked']))
        {
            print '<td>';
                print dol_print_date($db->jdate($obj->date), '%d-%m-%y');
            print '</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.lieuD']['checked']))
        {
            print '<td>'.$obj->lieuD.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.lieuA']['checked']))
        {
            print '<td>'.$obj->lieuA.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.heureD']['checked']))
        {
            print '<td>'.$obj->heureD.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.heureA']['checked']))
        {
            print '<td>'.$obj->heureA.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.BBC_ballons_idBBC_ballons']['checked']))
        {
            print '<td>'.$obj->bal.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.nbrPax']['checked']))
        {
            print '<td>'.$obj->nbrPax.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.remarque']['checked']))
        {
            print '<td>'.$obj->remarque.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.incidents']['checked']))
        {
            print '<td>'.$obj->incidents.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.fk_type']['checked']))
        {
            print '<td>'.$obj->flight_type.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.fk_pilot']['checked']))
        {
            print '<td>'.$obj->pilot.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.fk_organisateur']['checked']))
        {
            print '<td>'.$obj->organisator.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.is_facture']['checked']))
        {
            $flight->is_facture = $obj->is_facture;
            print '<td>'.$flight->getLibStatut(3).'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.kilometers']['checked']))
        {
            if($user->rights->flightlog->vol->financial || $user->id == $flight->fk_pilot){
                print '<td>'.$obj->kilometers.' KM</td>';
            }else{
                print '<td> - Km</td>';
            }

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.cost']['checked']))
        {
            if(($user->rights->flightlog->vol->financial || $user->id == $flight->fk_pilot) && $obj->cost > 0){
                $nbrPax = $obj->nbrPax > 0 ? $obj->nbrPax : 1;
                print sprintf('<td>%s - (%s/pax)</td>', price($obj->cost, 0, $langs, 0, 0, -1, $conf->currency), price($obj->cost/$nbrPax, 0, $langs, -1, -1, -1, $conf->currency));
            }else{
                print '<td> - €</td>';
            }

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.fk_receiver']['checked']))
        {
            print '<td>'.$obj->receiver.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }
        if (! empty($arrayfields['t.justif_kilometers']['checked']))
        {
            print '<td>'.$obj->justif_kilometers.'</td>';

            if (! $i) $totalarray['nbfield']++;
        }

        // Extra fields
        if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
            foreach ($extrafields->attribute_label as $key => $val) {
                if (!empty($arrayfields["ef." . $key]['checked'])) {
                    print '<td';
                    $align = $extrafields->getAlignFlag($key);
                    if ($align) {
                        print ' align="' . $align . '"';
                    }
                    print '>';
                    $tmpkey = 'options_' . $key;
                    print $extrafields->showOutputField($key, $obj->$tmpkey);
                    print '</td>';
                    if (!$i) {
                        $totalarray['nbfield']++;
                    }
                }
            }
        }
        // Fields from hook
        $parameters = array('arrayfields' => $arrayfields, 'obj' => $obj);
        $reshook = $hookmanager->executeHooks('printFieldListValue',
            $parameters);    // Note that $action and $object may have been modified by hook
        print $hookmanager->resPrint;
        // Date creation
        if (!empty($arrayfields['t.datec']['checked'])) {
            print '<td align="center">';
            print dol_print_date($db->jdate($obj->date_creation), 'day');
            print '</td>';
            if (!$i) {
                $totalarray['nbfield']++;
            }
        }
        // Date modification
        if (!empty($arrayfields['t.tms']['checked'])) {
            print '<td align="center">';
            print dol_print_date($db->jdate($obj->date_update), 'day');
            print '</td>';
            if (!$i) {
                $totalarray['nbfield']++;
            }
        }

        // Action column
        print '<td class="nowrap" align="center">';
        if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
        {
            $selected = 0;
            if (in_array($obj->idBBC_vols, $arrayofselected)) {
                $selected = 1;
            }
            print '<input id="cb' . $obj->idBBC_vols . '" class="flat checkforselect" type="checkbox" name="toselect[]" value="' . $obj->idBBC_vols . '"' . ($selected ? ' checked="checked"' : '') . '>';
        }
        print '</td>';
        if (!$i) {
            $totalarray['nbfield']++;
        }

        print '</tr>';
    }
    $i++;
}

// Show total line
if (isset($totalarray['totalhtfield'])) {
    print '<tr class="liste_total">';
    $i = 0;
    while ($i < $totalarray['nbfield']) {
        $i++;
        if ($i == 1) {
            if ($num < $limit) {
                print '<td align="left">' . $langs->trans("Total") . '</td>';
            } else {
                print '<td align="left">' . $langs->trans("Totalforthispage") . '</td>';
            }
        } elseif ($totalarray['totalhtfield'] == $i) {
            print '<td align="right">' . price($totalarray['totalht']) . '</td>';
        } elseif ($totalarray['totalvatfield'] == $i) {
            print '<td align="right">' . price($totalarray['totalvat']) . '</td>';
        } elseif ($totalarray['totalttcfield'] == $i) {
            print '<td align="right">' . price($totalarray['totalttc']) . '</td>';
        } else {
            print '<td></td>';
        }
    }
    print '</tr>';
}

$db->free($resql);

$parameters = array('arrayfields' => $arrayfields, 'sql' => $sql);
$reshook = $hookmanager->executeHooks('printFieldListFooter',
    $parameters);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print '</table>' . "\n";
print '</div>' . "\n";

print '</form>' . "\n";


// End of page
llxFooter();
$db->close();
