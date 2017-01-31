<?php
/* Copyright (C) 2007-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2016      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *   	\file       flightLog/bbcvols_list.php
 *		\ingroup    flightLog
 *		\brief      This file is an example of a php page
 *					Initialy built by build_class_from_table on 2017-01-31 17:53
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
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
dol_include_once('/flightLog/class/bbcvols.class.php');

// Load traductions files requiredby by page
$langs->load("flightLog");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$backtopage = GETPOST('backtopage');
$myparam	= GETPOST('myparam','alpha');


$search_idBBC_vols=GETPOST('search_idBBC_vols','int');
$search_lieuD=GETPOST('search_lieuD','alpha');
$search_lieuA=GETPOST('search_lieuA','alpha');
$search_heureD=GETPOST('search_heureD','alpha');
$search_heureA=GETPOST('search_heureA','alpha');
$search_BBC_ballons_idBBC_ballons=GETPOST('search_BBC_ballons_idBBC_ballons','int');
$search_nbrPax=GETPOST('search_nbrPax','alpha');
$search_remarque=GETPOST('search_remarque','alpha');
$search_incidents=GETPOST('search_incidents','alpha');
$search_fk_type=GETPOST('search_fk_type','int');
$search_fk_pilot=GETPOST('search_fk_pilot','int');
$search_fk_organisateur=GETPOST('search_fk_organisateur','int');
$search_is_facture=GETPOST('search_is_facture','int');
$search_kilometers=GETPOST('search_kilometers','int');
$search_cost=GETPOST('search_cost','alpha');
$search_fk_receiver=GETPOST('search_fk_receiver','int');
$search_justif_kilometers=GETPOST('search_justif_kilometers','alpha');


$search_myfield=GETPOST('search_myfield');
$optioncss = GETPOST('optioncss','alpha');

// Load variable for pagination
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');
if ($page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield="t.idBBC_vols"; // Set here default search field
if (! $sortorder) $sortorder="DESC";

// Protection if external user
$socid=0;
if ($user->societe_id > 0)
{
    $socid = $user->societe_id;
	//accessforbidden();
}

// Initialize technical object to manage hooks. Note that conf->hooks_modules contains array
$hookmanager->initHooks(array('bbcvolslist'));
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('flightLog');
$search_array_options=$extrafields->getOptionalsFromPost($extralabels,'','search_');

// Load object if id or ref is provided as parameter
$object=new Bbcvols($db);
if (($id > 0 || ! empty($ref)) && $action != 'add')
{
	$result=$object->fetch($id,$ref);
	if ($result < 0) dol_print_error($db);
}

// Definition of fields for list
$arrayfields=array(

't.idBBC_vols'=>array('label'=>$langs->trans("FieldidBBC_vols"), 'checked'=>1),
't.lieuD'=>array('label'=>$langs->trans("FieldlieuD"), 'checked'=>1),
't.lieuA'=>array('label'=>$langs->trans("FieldlieuA"), 'checked'=>1),
't.heureD'=>array('label'=>$langs->trans("FieldheureD"), 'checked'=>1),
't.heureA'=>array('label'=>$langs->trans("FieldheureA"), 'checked'=>1),
't.BBC_ballons_idBBC_ballons'=>array('label'=>$langs->trans("Ballon"), 'checked'=>1),
//'t.nbrPax'=>array('label'=>$langs->trans("FieldnbrPax"), 'checked'=>1),
't.remarque'=>array('label'=>$langs->trans("Fieldremarque"), 'checked'=>1),
't.incidents'=>array('label'=>$langs->trans("Fieldincidents"), 'checked'=>1),
't.fk_type'=>array('label'=>$langs->trans("Fieldfk_type"), 'checked'=>1),
't.fk_pilot'=>array('label'=>$langs->trans("Fieldfk_pilot"), 'checked'=>1),
't.fk_organisateur'=>array('label'=>$langs->trans("Fieldfk_organisateur"), 'checked'=>1),
//'t.is_facture'=>array('label'=>$langs->trans("Fieldis_facture"), 'checked'=>1),
't.kilometers'=>array('label'=>$langs->trans("Fieldkilometers"), 'checked'=>1),
//'t.cost'=>array('label'=>$langs->trans("Fieldcost"), 'checked'=>1),
//'t.fk_receiver'=>array('label'=>$langs->trans("Fieldfk_receiver"), 'checked'=>1),
't.justif_kilometers'=>array('label'=>$langs->trans("Fieldjustif_kilometers"), 'checked'=>1),
);
// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
{
   foreach($extrafields->attribute_label as $key => $val)
   {
       $arrayfields["ef.".$key]=array('label'=>$extrafields->attribute_label[$key], 'checked'=>$extrafields->attribute_list[$key], 'position'=>$extrafields->attribute_pos[$key], 'enabled'=>$extrafields->attribute_perms[$key]);
   }
}




/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

if (GETPOST('cancel')) { $action='list'; $massaction=''; }
if (! GETPOST('confirmmassaction')) { $massaction=''; }

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") ||GETPOST("button_removefilter")) // All test are required to be compatible with all browsers
{

$search_idBBC_vols='';
$search_lieuD='';
$search_lieuA='';
$search_heureD='';
$search_heureA='';
$search_BBC_ballons_idBBC_ballons='';
$search_nbrPax='';
$search_remarque='';
$search_incidents='';
$search_fk_type='';
$search_fk_pilot='';
$search_fk_organisateur='';
$search_is_facture='';
$search_kilometers='';
$search_cost='';
$search_fk_receiver='';
$search_justif_kilometers='';


	$search_date_creation='';
	$search_date_update='';
	$search_array_options=array();
}


if (empty($reshook))
{
    // Mass actions. Controls on number of lines checked
    $maxformassaction=1000;
    if (! empty($massaction) && count($toselect) < 1)
    {
        $error++;
        setEventMessages($langs->trans("NoLineChecked"), null, "warnings");
    }
    if (! $error && count($toselect) > $maxformassaction)
    {
        setEventMessages($langs->trans('TooManyRecordForMassAction',$maxformassaction), null, 'errors');
        $error++;
    }

	// Action to delete
	if ($action == 'confirm_delete')
	{
		$result=$object->delete($user);
		if ($result > 0)
		{
			// Delete OK
			setEventMessages("RecordDeleted", null, 'mesgs');
			header("Location: ".dol_buildpath('/flightLog/list.php',1));
			exit;
		}
		else
		{
			if (! empty($object->errors)) setEventMessages(null,$object->errors,'errors');
			else setEventMessages($object->error,null,'errors');
		}
	}
}




/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

$now=dol_now();

$form=new Form($db);

//$help_url="EN:Module_Customers_Orders|FR:Module_Commandes_Clients|ES:Módulo_Pedidos_de_clientes";
$help_url='';
$title = $langs->trans('Liste par pilote');
llxHeader('', $title, $help_url);

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
		$sql .= " t.justif_kilometers";


// Add fields for extrafields
foreach ($extrafields->attribute_label as $key => $val) $sql.=($extrafields->attribute_type[$key] != 'separate' ? ",ef.".$key.' as options_'.$key : '');
// Add fields from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListSelect',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql.= " FROM ".MAIN_DB_PREFIX."bbc_vols as t";
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."bbc_vols_extrafields as ef on (u.rowid = ef.fk_object)";
$sql.= " WHERE 1 = 1";
//$sql.= " WHERE u.entity IN (".getEntity('mytable',1).")";

if ($search_idBBC_vols) $sql.= natural_search("idBBC_vols",$search_idBBC_vols);
if ($search_lieuD) $sql.= natural_search("lieuD",$search_lieuD);
if ($search_lieuA) $sql.= natural_search("lieuA",$search_lieuA);
if ($search_heureD) $sql.= natural_search("heureD",$search_heureD);
if ($search_heureA) $sql.= natural_search("heureA",$search_heureA);
if ($search_BBC_ballons_idBBC_ballons) $sql.= natural_search("BBC_ballons_idBBC_ballons",$search_BBC_ballons_idBBC_ballons);
if ($search_nbrPax) $sql.= natural_search("nbrPax",$search_nbrPax);
if ($search_remarque) $sql.= natural_search("remarque",$search_remarque);
if ($search_incidents) $sql.= natural_search("incidents",$search_incidents);
if ($search_fk_type) $sql.= natural_search("fk_type",$search_fk_type);
if ($search_fk_pilot) $sql.= natural_search("fk_pilot",$search_fk_pilot);
if ($search_fk_organisateur) $sql.= natural_search("fk_organisateur",$search_fk_organisateur);
if ($search_is_facture) $sql.= natural_search("is_facture",$search_is_facture);
if ($search_kilometers) $sql.= natural_search("kilometers",$search_kilometers);
if ($search_cost) $sql.= natural_search("cost",$search_cost);
if ($search_fk_receiver) $sql.= natural_search("fk_receiver",$search_fk_receiver);
if ($search_justif_kilometers) $sql.= natural_search("justif_kilometers",$search_justif_kilometers);


if ($sall)          $sql.= natural_search(array_keys($fieldstosearchall), $sall);
// Add where from extra fields
foreach ($search_array_options as $key => $val)
{
    $crit=$val;
    $tmpkey=preg_replace('/search_options_/','',$key);
    $typ=$extrafields->attribute_type[$tmpkey];
    $mode=0;
    if (in_array($typ, array('int','double'))) $mode=1;    // Search on a numeric
    if ($val && ( ($crit != '' && ! in_array($typ, array('select'))) || ! empty($crit)))
    {
        $sql .= natural_search('ef.'.$tmpkey, $crit, $mode);
    }
}
// Add where from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListWhere',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql.=$db->order($sortfield,$sortorder);
//$sql.= $db->plimit($conf->liste_limit+1, $offset);

// Count total nb of records
$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
}

$sql.= $db->plimit($limit+1, $offset);


dol_syslog($script_file, LOG_DEBUG);
$resql=$db->query($sql);
if ($resql)
{
    $num = $db->num_rows($resql);

    $params='';
    if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;

if ($search_idBBC_vols != '') $params.= '&amp;search_idBBC_vols='.urlencode($search_idBBC_vols);
if ($search_lieuD != '') $params.= '&amp;search_lieuD='.urlencode($search_lieuD);
if ($search_lieuA != '') $params.= '&amp;search_lieuA='.urlencode($search_lieuA);
if ($search_heureD != '') $params.= '&amp;search_heureD='.urlencode($search_heureD);
if ($search_heureA != '') $params.= '&amp;search_heureA='.urlencode($search_heureA);
if ($search_BBC_ballons_idBBC_ballons != '') $params.= '&amp;search_BBC_ballons_idBBC_ballons='.urlencode($search_BBC_ballons_idBBC_ballons);
if ($search_nbrPax != '') $params.= '&amp;search_nbrPax='.urlencode($search_nbrPax);
if ($search_remarque != '') $params.= '&amp;search_remarque='.urlencode($search_remarque);
if ($search_incidents != '') $params.= '&amp;search_incidents='.urlencode($search_incidents);
if ($search_fk_type != '') $params.= '&amp;search_fk_type='.urlencode($search_fk_type);
if ($search_fk_pilot != '') $params.= '&amp;search_fk_pilot='.urlencode($search_fk_pilot);
if ($search_fk_organisateur != '') $params.= '&amp;search_fk_organisateur='.urlencode($search_fk_organisateur);
if ($search_is_facture != '') $params.= '&amp;search_is_facture='.urlencode($search_is_facture);
if ($search_kilometers != '') $params.= '&amp;search_kilometers='.urlencode($search_kilometers);
if ($search_cost != '') $params.= '&amp;search_cost='.urlencode($search_cost);
if ($search_fk_receiver != '') $params.= '&amp;search_fk_receiver='.urlencode($search_fk_receiver);
if ($search_justif_kilometers != '') $params.= '&amp;search_justif_kilometers='.urlencode($search_justif_kilometers);


    if ($optioncss != '') $param.='&optioncss='.$optioncss;
    // Add $param from extra fields
    foreach ($search_array_options as $key => $val)
    {
        $crit=$val;
        $tmpkey=preg_replace('/search_options_/','',$key);
        if ($val != '') $param.='&search_options_'.$tmpkey.'='.urlencode($val);
    }

    print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_companies', 0, '', '', $limit);


	print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">';
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="action" value="list">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

    if ($sall)
    {
        foreach($fieldstosearchall as $key => $val) $fieldstosearchall[$key]=$langs->trans($val);
        print $langs->trans("FilterOnInto", $all) . join(', ',$fieldstosearchall);
    }

    $moreforfilter = '';
    $moreforfilter.='<div class="divsearchfield">';
    $moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
    $moreforfilter.= '</div>';

	if (! empty($moreforfilter))
	{
		print '<div class="liste_titre liste_titre_bydiv centpercent">';
		print $moreforfilter;
    	$parameters=array();
    	$reshook=$hookmanager->executeHooks('printFieldPreListTitle',$parameters);    // Note that $action and $object may have been modified by hook
	    print $hookmanager->resPrint;
	    print '</div>';
	}

    $varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
    $selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields

	print '<table class="liste '.($moreforfilter?"listwithfilterbefore":"").'">';

    // Fields title
    print '<tr class="liste_titre">';
    // 
if (! empty($arrayfields['t.idBBC_vols']['checked'])) print_liste_field_titre($arrayfields['t.idBBC_vols']['label'],$_SERVER['PHP_SELF'],'t.idBBC_vols','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.lieuD']['checked'])) print_liste_field_titre($arrayfields['t.lieuD']['label'],$_SERVER['PHP_SELF'],'t.lieuD','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.lieuA']['checked'])) print_liste_field_titre($arrayfields['t.lieuA']['label'],$_SERVER['PHP_SELF'],'t.lieuA','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.heureD']['checked'])) print_liste_field_titre($arrayfields['t.heureD']['label'],$_SERVER['PHP_SELF'],'t.heureD','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.heureA']['checked'])) print_liste_field_titre($arrayfields['t.heureA']['label'],$_SERVER['PHP_SELF'],'t.heureA','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.BBC_ballons_idBBC_ballons']['checked'])) print_liste_field_titre($arrayfields['t.BBC_ballons_idBBC_ballons']['label'],$_SERVER['PHP_SELF'],'t.BBC_ballons_idBBC_ballons','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.nbrPax']['checked'])) print_liste_field_titre($arrayfields['t.nbrPax']['label'],$_SERVER['PHP_SELF'],'t.nbrPax','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.remarque']['checked'])) print_liste_field_titre($arrayfields['t.remarque']['label'],$_SERVER['PHP_SELF'],'t.remarque','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.incidents']['checked'])) print_liste_field_titre($arrayfields['t.incidents']['label'],$_SERVER['PHP_SELF'],'t.incidents','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_type']['checked'])) print_liste_field_titre($arrayfields['t.fk_type']['label'],$_SERVER['PHP_SELF'],'t.fk_type','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_pilot']['checked'])) print_liste_field_titre($arrayfields['t.fk_pilot']['label'],$_SERVER['PHP_SELF'],'t.fk_pilot','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_organisateur']['checked'])) print_liste_field_titre($arrayfields['t.fk_organisateur']['label'],$_SERVER['PHP_SELF'],'t.fk_organisateur','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.is_facture']['checked'])) print_liste_field_titre($arrayfields['t.is_facture']['label'],$_SERVER['PHP_SELF'],'t.is_facture','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.kilometers']['checked'])) print_liste_field_titre($arrayfields['t.kilometers']['label'],$_SERVER['PHP_SELF'],'t.kilometers','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.cost']['checked'])) print_liste_field_titre($arrayfields['t.cost']['label'],$_SERVER['PHP_SELF'],'t.cost','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_receiver']['checked'])) print_liste_field_titre($arrayfields['t.fk_receiver']['label'],$_SERVER['PHP_SELF'],'t.fk_receiver','',$params,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.justif_kilometers']['checked'])) print_liste_field_titre($arrayfields['t.justif_kilometers']['label'],$_SERVER['PHP_SELF'],'t.justif_kilometers','',$params,'',$sortfield,$sortorder);

    //if (! empty($arrayfields['t.field1']['checked'])) print_liste_field_titre($arrayfields['t.field1']['label'],$_SERVER['PHP_SELF'],'t.field1','',$params,'',$sortfield,$sortorder);
    //if (! empty($arrayfields['t.field2']['checked'])) print_liste_field_titre($arrayfields['t.field2']['label'],$_SERVER['PHP_SELF'],'t.field2','',$params,'',$sortfield,$sortorder);
	// Extra fields
	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
	{
	   foreach($extrafields->attribute_label as $key => $val)
	   {
           if (! empty($arrayfields["ef.".$key]['checked']))
           {
				$align=$extrafields->getAlignFlag($key);
				print_liste_field_titre($extralabels[$key],$_SERVER["PHP_SELF"],"ef.".$key,"",$param,($align?'align="'.$align.'"':''),$sortfield,$sortorder);
           }
	   }
	}
    // Hook fields
	$parameters=array('arrayfields'=>$arrayfields);
    $reshook=$hookmanager->executeHooks('printFieldListTitle',$parameters);    // Note that $action and $object may have been modified by hook
    print $hookmanager->resPrint;
	if (! empty($arrayfields['t.datec']['checked']))  print_liste_field_titre($arrayfields['t.datec']['label'],$_SERVER["PHP_SELF"],"t.datec","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
	if (! empty($arrayfields['t.tms']['checked']))    print_liste_field_titre($arrayfields['t.tms']['label'],$_SERVER["PHP_SELF"],"t.tms","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
	//if (! empty($arrayfields['t.status']['checked'])) print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"],"t.status","",$param,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
    print '</tr>'."\n";

    // Fields title search
	print '<tr class="liste_titre">';
	//
if (! empty($arrayfields['t.idBBC_vols']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_idBBC_vols" value="'.$search_idBBC_vols.'" size="10"></td>';
if (! empty($arrayfields['t.lieuD']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_lieuD" value="'.$search_lieuD.'" size="10"></td>';
if (! empty($arrayfields['t.lieuA']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_lieuA" value="'.$search_lieuA.'" size="10"></td>';
if (! empty($arrayfields['t.heureD']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_heureD" value="'.$search_heureD.'" size="10"></td>';
if (! empty($arrayfields['t.heureA']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_heureA" value="'.$search_heureA.'" size="10"></td>';
if (! empty($arrayfields['t.BBC_ballons_idBBC_ballons']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_BBC_ballons_idBBC_ballons" value="'.$search_BBC_ballons_idBBC_ballons.'" size="10"></td>';
if (! empty($arrayfields['t.nbrPax']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_nbrPax" value="'.$search_nbrPax.'" size="10"></td>';
if (! empty($arrayfields['t.remarque']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_remarque" value="'.$search_remarque.'" size="10"></td>';
if (! empty($arrayfields['t.incidents']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_incidents" value="'.$search_incidents.'" size="10"></td>';
if (! empty($arrayfields['t.fk_type']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_type" value="'.$search_fk_type.'" size="10"></td>';
if (! empty($arrayfields['t.fk_pilot']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_pilot" value="'.$search_fk_pilot.'" size="10"></td>';
if (! empty($arrayfields['t.fk_organisateur']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_organisateur" value="'.$search_fk_organisateur.'" size="10"></td>';
if (! empty($arrayfields['t.is_facture']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_is_facture" value="'.$search_is_facture.'" size="10"></td>';
if (! empty($arrayfields['t.kilometers']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_kilometers" value="'.$search_kilometers.'" size="10"></td>';
if (! empty($arrayfields['t.cost']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_cost" value="'.$search_cost.'" size="10"></td>';
if (! empty($arrayfields['t.fk_receiver']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_receiver" value="'.$search_fk_receiver.'" size="10"></td>';
if (! empty($arrayfields['t.justif_kilometers']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_justif_kilometers" value="'.$search_justif_kilometers.'" size="10"></td>';

	//if (! empty($arrayfields['t.field1']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_field1" value="'.$search_field1.'" size="10"></td>';
	//if (! empty($arrayfields['t.field2']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_field2" value="'.$search_field2.'" size="10"></td>';
	// Extra fields
	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
	{
        foreach($extrafields->attribute_label as $key => $val)
        {
            if (! empty($arrayfields["ef.".$key]['checked']))
            {
                $align=$extrafields->getAlignFlag($key);
                $typeofextrafield=$extrafields->attribute_type[$key];
                print '<td class="liste_titre'.($align?' '.$align:'').'">';
            	if (in_array($typeofextrafield, array('varchar', 'int', 'double', 'select')))
				{
				    $crit=$val;
    				$tmpkey=preg_replace('/search_options_/','',$key);
    				$searchclass='';
    				if (in_array($typeofextrafield, array('varchar', 'select'))) $searchclass='searchstring';
    				if (in_array($typeofextrafield, array('int', 'double'))) $searchclass='searchnum';
    				print '<input class="flat'.($searchclass?' '.$searchclass:'').'" size="4" type="text" name="search_options_'.$tmpkey.'" value="'.dol_escape_htmltag($search_array_options['search_options_'.$tmpkey]).'">';
				}
                print '</td>';
            }
        }
	}
    // Fields from hook
	$parameters=array('arrayfields'=>$arrayfields);
    $reshook=$hookmanager->executeHooks('printFieldListOption',$parameters);    // Note that $action and $object may have been modified by hook
    print $hookmanager->resPrint;
    if (! empty($arrayfields['t.datec']['checked']))
    {
        // Date creation
        print '<td class="liste_titre">';
        print '</td>';
    }
    if (! empty($arrayfields['t.tms']['checked']))
    {
        // Date modification
        print '<td class="liste_titre">';
        print '</td>';
    }
    /*if (! empty($arrayfields['u.statut']['checked']))
    {
        // Status
        print '<td class="liste_titre" align="center">';
        print $form->selectarray('search_statut', array('-1'=>'','0'=>$langs->trans('Disabled'),'1'=>$langs->trans('Enabled')),$search_statut);
        print '</td>';
    }*/
    // Action column
	print '<td class="liste_titre" align="right">';
    $searchpitco=$form->showFilterAndCheckAddButtons(0);
    print $searchpitco;
    print '</td>';
	print '</tr>'."\n";


	$i=0;
	$var=true;
	$totalarray=array();
    while ($i < min($num, $limit))
    {
        $obj = $db->fetch_object($resql);
        if ($obj)
        {
            $var = !$var;

            // Show here line of result
            print '<tr '.$bc[$var].'>';

            if (! empty($arrayfields['t.idBBC_vols']['checked']))
            {
                print '<td>'.$obj->idBBC_vols.'</td>';
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
                print '<td>'.$obj->BBC_ballons_idBBC_ballons.'</td>';
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
                print '<td>'.$obj->fk_type.'</td>';
    		    if (! $i) $totalarray['nbfield']++;
            }

            if (! empty($arrayfields['t.fk_pilot']['checked']))
            {
                print '<td>'.$obj->fk_pilot.'</td>';
    		    if (! $i) $totalarray['nbfield']++;
            }

            if (! empty($arrayfields['t.fk_organisateur']['checked']))
            {
                print '<td>'.$obj->fk_organisateur.'</td>';
    		    if (! $i) $totalarray['nbfield']++;
            }

            if (! empty($arrayfields['t.is_facture']['checked']))
            {
                print '<td>'.$obj->is_facture.'</td>';
    		    if (! $i) $totalarray['nbfield']++;
            }

            if (! empty($arrayfields['t.kilometers']['checked']))
            {
                print '<td>'.$obj->kilometers.'</td>';
    		    if (! $i) $totalarray['nbfield']++;
            }

            if (! empty($arrayfields['t.cost']['checked']))
            {
                print '<td>'.$obj->cost.'</td>';
    		    if (! $i) $totalarray['nbfield']++;
            }

            if (! empty($arrayfields['t.fk_receiver']['checked']))
            {
                print '<td>'.$obj->fk_receiver.'</td>';
    		    if (! $i) $totalarray['nbfield']++;
            }

            if (! empty($arrayfields['t.justif_kilometers']['checked']))
            {
                print '<td>'.$obj->justif_kilometers.'</td>';
    		    if (! $i) $totalarray['nbfield']++;
            }

            // Action column
            print '<td></td>';
            if (! $i) $totalarray['nbfield']++;

            print '</tr>';
        }
        $i++;
    }

    $db->free($resql);

	$parameters=array('sql' => $sql);
	$reshook=$hookmanager->executeHooks('printFieldListFooter',$parameters);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	print "</table>\n";
	print "</form>\n";

	$db->free($result);
}
else
{
    $error++;
    dol_print_error($db);
}


// End of page
llxFooter();
$db->close();
