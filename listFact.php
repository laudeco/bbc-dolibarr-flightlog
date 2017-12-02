<?php
// Load Dolibarr environment
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

global $db, $langs, $user;

dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');
dol_include_once("/flightlog/lib/flightLog.lib.php");
dol_include_once("/flightballoon/bbc_ballons.class.php");

// Load translation files required by the page
$langs->load("mymodule@mymodule");

// Get parameters
$myparam = isset($_GET["myparam"]) ? $_GET["myparam"] : '';

// Protection if the user can't acces to the module
if (!$user->rights->flightlog->vol->detail && !$user->rights->flightlog->vol->status && !$user->admin) {
    accessforbidden();
}

// 1 = a facturer
// 2 = liste de tous les vols pour tous les pilotes et tous les ballons avec filtre sur les dates
$viewSelection = 1;
if ($_GET['view']) {
    if (!$user->rights->flightlog->vol->status && !$user->admin && $_GET['view'] == 1) {
        accessforbidden();
    }
    if (!$user->rights->flightlog->vol->detail && !$user->admin && $_GET['view'] == 2) {
        accessforbidden();

    }
    $viewSelection = $_GET['view'];
} else {
    accessforbidden("Erreur avec les parametres de la page.");
}


/* * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ****************************************************************** */


/* * *************************************************
 * PAGE
 *
 * Put here all code to build page
 * ************************************************** */
llxHeader('', 'Carnet de vol - readFlight', '');
//date
$datep = dol_mktime(-1, -1, -1, $_GET["apmonth"], $_GET["apday"], $_GET["apyear"]);
$datef = dol_mktime(-1, -1, -1, $_GET["p2month"], $_GET["p2day"], $_GET["p2year"]);


//DATE form
$form = new Form($db);
print '<!-- debut cartouche rapport -->
	<div class="tabs">
	    <a  id="'.($viewSelection == 1 ? 'active' : '').'" class="tab" href="listFact.php?view=1">Facturation</a>
	    <a  id="'.($viewSelection == 2 ? 'active' : '').'" class="tab" href="listFact.php?view=2">AVIABEL</a>
	</div>';
print '<div class="tabBar">';
print "<form name='listFact' action=\"listFact.php\" method=\"get\">\n";
print '<input type="hidden" name="mode" value="SELECT">';
print '<input type="hidden" name="view" value="' . $viewSelection . '">';
print '<table width="100%" class="border">';
// Date start
if (GETPOST('datep', 'int', 1)) {
    $datep = dol_stringtotime(GETPOST('datep', 'int', 1), 0);
}
print '<tr><td width="30%" nowrap="nowrap"><span>Debut</span></td><td>';
$form->select_date($datep, 'ap', 0, 0, 1, "readBalloon", 1, 1, 0, 0);
print '</td></tr>';

// Date end
if (GETPOST('datef', 'int', 1)) {
    $datef = dol_stringtotime(GETPOST('datef', 'int', 1), 0);
}
print '<tr><td>Fin</span></td><td>';
$form->select_date($datef, 'p2', 0, 0, 1, "readBalloon", 1, 1, 0, 0);
print '</td></tr>';
print '<tr><td colspan="4" align="center"><input type="submit" class="button" name="submit" value="Rafraichir"></td></tr></table>';
print '</form>';

if ($viewSelection == 2) {
    //Count per balloon
    //query
    $sql = "SELECT BAL.immat, count(rowid) as count ";
    $sql .= " FROM llx_bbc_vols,llx_bbc_ballons as BAL ";
    $sql .= " WHERE `BBC_ballons_idBBC_ballons` = BAL.rowid ";
    if ($datep) {
        $sql .= ' AND llx_bbc_vols.date >= \'' . dol_print_date($datep, 'dayrfc') . '\'';
    }
    if ($datef) {
        $sql .= ' AND llx_bbc_vols.date <= \'' . dol_print_date($datef, 'dayrfc') . '\'';
    }
    $sql .= " GROUP BY `BBC_ballons_idBBC_ballons`";

    //result
    $resql = $db->query($sql);
    if ($resql) {
        //display
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol
                print $obj->immat . ':' . $obj->count . ' vols<br/>';
                $i++;
            }
        }
    }
}
print '</div>';


//START QUERY
print '<table summary="" width="100%" border="0" class="notopnoleftnoright" style="margin-bottom: 2px;"><tbody><tr><td class="nobordernopadding" valign="middle"><div class="titre">Vols</div></td></tr></tbody></table>';

//tableau des facturations
$sql = "SELECT BAL.immat as ballon,"; //ballon
$sql .= " USR.lastname as nom, USR.firstname as prenom, "; //pilote
$sql .= " idBBC_vols as volid, fk_pilot,  llx_bbc_vols.date , heureD, is_facture as status "; // vol
$sql .= " FROM llx_bbc_ballons AS BAL, llx_user AS USR, llx_bbc_vols";

if ($viewSelection == 1) {
    $sql .= " LEFT JOIN llx_element_element ON llx_element_element.fk_source = llx_bbc_vols.idBBC_vols"; // is it linked
}

$sql .= " WHERE BBC_ballons_idBBC_ballons = BAL.rowid";

if ($viewSelection == 1) {
    $sql .= " AND fk_organisateur = USR.rowid";
    $sql .= " AND llx_element_element.rowid IS NULL";
    $sql .= " AND llx_bbc_vols.fk_type = 2";
}
if ($viewSelection == 2) {
    $sql .= " AND fk_pilot = USR.rowid ";
}
if ($datep) {
    $sql .= ' AND llx_bbc_vols.date >= \'' . dol_print_date($datep, 'dayrfc') . '\'';
}
if ($datef) {
    $sql .= ' AND llx_bbc_vols.date <= \'' . dol_print_date($datef, 'dayrfc') . '\'';
}


$sql .= " ORDER BY date ASC";

$resql = $db->query($sql);
if ($resql) {
    print '<table class="noborder" width="100%">';

    $num = $db->num_rows($resql);
    $i = 0;
    if ($num) {
        print '</tr>';
        print '<tr class="liste_titre">';

        print '<td class="liste_titre" > id vol </td>';
        print '<td class="liste_titre" > Date </td>';
        print '<td class="liste_titre" > Ballon </td>';
        print '<td class="liste_titre"> Type </td>';

        if ($viewSelection == 1) {
            print '<td class="liste_titre"> Organisateur </td>';
            print '<td class="liste_titre"> Actions </td>';
        }
        if ($viewSelection == 2) {
            print '<td class="liste_titre"> Pilote </td>';
        }
        print'</tr>';
        while ($i < $num) {
            $obj = $db->fetch_object($resql); //vol
            if ($obj) {
                $vol = new Bbcvols($db);
                $vol->fetch($obj->volid);

                $type = New Bbctypes($db);
                $type->fetch($vol->fk_type);

                print '<tr class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">';

                print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '"><a href="card.php?id=' . $obj->volid . '">' . $obj->volid . '</a></td>';
                print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->date . '</td>';
                print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->ballon . '</td>';
                print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $type->idType . '-' . $type->nom . '</td>';

                if ($viewSelection == 1) {
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->nom . ' ' . $obj->prenom . '</td>';
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . '</td>';
                }

                if ($viewSelection == 2) {
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->nom . ' ' . $obj->prenom . '</td>';
                }

                print'</tr class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">';
            }

            $i++;
        }
        print'</table>';
    }
}
llxFooter();
