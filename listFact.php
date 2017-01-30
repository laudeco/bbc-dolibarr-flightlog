<?php
// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user;

dol_include_once('/flightLog/class/bbcvols.class.php');
dol_include_once('/flightLog/class/bbctypes.class.php');
dol_include_once("/flightLog/inc/other.php");
dol_include_once("/flightBalloon/bbc_ballons.class.php");

// Load translation files required by the page
$langs->load("mymodule@mymodule");

// Get parameters
$myparam = isset($_GET["myparam"]) ? $_GET["myparam"] : '';

// Protection if the user can't acces to the module
if (!$user->rights->flightLog->vol->detail && !$user->rights->flightLog->vol->status && !$user->admin) {
    accessforbidden();
}

// 1 = a facturer
// 2 = liste de tous les vols pour tous les pilotes et tous les ballons avec filtre sur les dates
$viewSelection = 1;
if ($_GET['view']) {
    if (!$user->rights->flightLog->vol->status && !$user->admin && $_GET['view'] == 1) {
        accessforbidden();
    }
    if (!$user->rights->flightLog->vol->detail && !$user->admin && $_GET['view'] == 2) {
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
print '<table summary="" width="100%" border="0" class="notopnoleftnoright" style="margin-bottom: 2px;"><tbody><tr><td class="nobordernopadding" valign="middle"><div class="titre">Vols a facturer</div></td></tr></tbody></table>';

//tableau des facturations
$sql = "SELECT BAL.immat as ballon,"; //ballon
$sql .= " USR.lastname as nom, USR.firstname as prenom, "; //pilote
$sql .= " idBBC_vols as volid, fk_pilot,  llx_bbc_vols.date , heureD, is_facture as status "; // vol
$sql .= " FROM llx_bbc_ballons AS BAL, llx_user AS USR, llx_bbc_vols";
$sql .= " WHERE BBC_ballons_idBBC_ballons = BAL.rowid";

if ($viewSelection == 1) {
    $sql .= " AND fk_organisateur = USR.rowid";
    $sql .= " AND is_facture = 0";
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
        print '<tr class="liste_titre">';
        print '<td colspan="7"> Les 10 premiers Vols a facturer.';

        print '</td>';
        print '</tr>';
        print '<tr class="liste_titre">';

        print '<td class="liste_titre" > id vol </td>';
        print '<td class="liste_titre" > Date </td>';
        print '<td class="liste_titre" > Ballon </td>';
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

                print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '"><a href="fiche.php?vol=' . $obj->volid . '">' . $obj->volid . '</a></td>';
                print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->date . '</td>';
                print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->ballon . '</td>';
                print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $type->idType . '-' . $type->nom . '</td>';
                if ($viewSelection == 1) {
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->nom . ' ' . $obj->prenom . '</td>';
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . '<a href="fiche.php?action=fact&vol=' . $obj->volid . '">' . img_action("default",
                            1) . '</a>' . '</td>';
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
