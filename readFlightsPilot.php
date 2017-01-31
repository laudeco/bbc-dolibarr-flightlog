<?php
/**
 * \file    mypage.php
 * \ingroup mymodule
 * \brief   Example PHP page.
 *
 * read flights
 */

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user;

dol_include_once('/flightLog/class/bbcvols.class.php');
dol_include_once("/flightBalloon/bbc_ballons.class.php");
dol_include_once('/flightLog/class/bbctypes.class.php');
dol_include_once("/flightLog/lib/flightLog.lib.php");

// Load translation files required by the page
$langs->load("mymodule@mymodule");

// Get parameters
$myparam = isset($_GET["myparam"]) ? $_GET["myparam"] : '';

// Protection if external user
if ($user->societe_id > 0) {
    //accessforbidden();
}
// Protection if the user can't acces to the module
if (!$user->rights->flightLog->vol->access) {
    accessforbidden();
}

/*******************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 ********************************************************************/
if ($_POST["mode"] == 'SELECT') {
    $userid = $_POST['userid'];
} else {
    $userid = $user->id;
}

//balloon with ID
$bbcUser = New User($db);

if ($bbcUser->fetch($userid) == -1) {
    print "ERROR<br/>";
}

//date
$datep = dol_mktime(-1, -1, -1, $_POST["apmonth"], $_POST["apday"], $_POST["apyear"]);
$datef = dol_mktime(-1, -1, -1, $_POST["p2month"], $_POST["p2day"], $_POST["p2year"]);

//flight with balloon ID
$query = 'SELECT *,TIMEDIFF(heureA,heureD) AS time  FROM llx_bbc_vols WHERE 1=1';

if ($datep) {
    $query .= ' AND date >= \'' . dol_date('Y-m-d', $datep) . '\'';
}
if ($datef) {
    $query .= ' AND date <= \'' . dol_date('Y-m-d', $datef) . '\'';
}

$query .= ' AND `fk_pilot` = ' . $bbcUser->id;
$query .= ' ORDER BY date DESC';

$resql = $db->query($query);


$sqlByType = "SELECT USR.name AS nom , USR.firstname AS prenom ,COUNT(`idBBC_vols`) AS nbr,fk_pilot as pilot, TT.numero as type,SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(heureA,heureD)))) AS time ";
$sqlByType .= " FROM llx_bbc_vols, llx_user AS USR,llx_bbc_types AS TT WHERE `fk_pilot`= USR.rowid AND fk_type = TT.idType AND USR.rowid = " . $bbcUser->id;

if ($datep) {
    $sqlByType .= ' AND date >= \'' . dol_print_date($datep, 'dayrfc') . '\'';
}
if ($datef) {
    $sqlByType .= ' AND date <= \'' . dol_print_date($datef, 'dayrfc') . '\'';
}

$sqlByType .= " GROUP BY fk_pilot,`fk_type`";


$resqlByType = $db->query($sqlByType);


/***************************************************
 * PAGE
 *
 * Put here all code to build page
 ****************************************************/
llxHeader('', 'Carnet de vol', '');

$form = new Form($db);

print '<!-- debut cartouche rapport -->
<div class="tabs">
<a id="active" class="tab">Carnet de vol</a>
</div>';
print '<div class="tabBar">';
print "<form name='readBalloon' action=\"readFlightsPilot.php\" method=\"post\">\n";
print '<input type="hidden" name="mode" value="SELECT">';
print '<table width="100%" class="border">';
//Pilote
print '<tr><td>Pilote</td><td>';
if ($user->rights->flightLog->vol->detail) {
    print $form->select_dolusers($userid);
} else {
    print $user->nom . ' ' . $user->prenom;
}
print'</td></tr>';

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


$num = 0;
if ($resql) {
    $num = $db->num_rows($resql);
    //Nbr de vols
    print '<tr>';
    print '<td>Nombre de vol(s) total (suivant filtre) </td>';
    print '<td>' . $num . '</td>';
    print '</tr>';
}

//Types
if ($resqlByType) {
    $table = sqlToArray($db, $sqlByType, false);
    print '<td>Nombre de vols par type</td><td></td></tr>';
    print '<tr>';
    print '<td>Type 1 - Sponsor </td>';
    print '<td>' . $table[$userid]['1']['count'] . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Type 2 - Baptême </td>';
    print '<td>' . $table[$userid]['2']['count'] . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Type 3 - Privé </td>';
    print '<td>' . $table[$userid]['3']['count'] . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Type 4 - Meeting </td>';
    print '<td>' . $table[$userid]['4']['count'] . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Type 5  - Chambley </td>';
    print '<td>' . $table[$userid]['5']['count'] . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Type 6 - Instruction </td>';
    print '<td>' . $table[$userid]['6']['count'] . '</td>';
    print '</tr>';

}


print '<tr><td colspan="2" align="center"><input type="submit" class="button" name="submit" value="Rafraichir"></td></tr></table>';
print '</form></div>';


if ($resql) {
    //fetch pilot

    print '<table class="border" width="100%">';
    $num = $db->num_rows($resql);
    $i = 0;
    if ($num) {
        print '<tr class="liste_titre">';
        print '<td class="liste_titre"> identifiant </td>';
        print '<td class="liste_titre"> Type </td>';
        print '<td class="liste_titre"> Date </td>';
        print '<td class="liste_titre"> Ballon </td>';
        print '<td class="liste_titre"> Pilote </td>';
        print '<td class="liste_titre"> Lieu depart </td>';
        print '<td class="liste_titre"> Lieu arrivee </td>';
        print '<td class="liste_titre"> Heure depart</td>';
        print '<td class="liste_titre"> Heure Arrivee </td>';
        print '<td class="liste_titre"> Duree (min) </td>';
        print '<td class="liste_titre"> Nbr Pax </td>';
        print '<td class="liste_titre"> Rem </td>';
        print '<td class="liste_titre"> Incidents </td>';
        print '<td class="liste_titre"> KM </td>';
        print '<td class="liste_titre"> Justificatif KM </td>';
        if ($user->rights->flightLog->vol->status) {
            print '<td class="liste_titre"> Statut </td>';
        }
        print'</tr>';
        while ($i < $num) {

            $obj = $db->fetch_object($resql); //vol
            $ballon = New Bbc_ballons($db);
            $ballon->fetch($obj->BBC_ballons_idBBC_ballons);
            print '<tr>';
            if ($obj) {
                print '<td><a href="fiche.php?vol=' . $obj->idBBC_vols . '">' . $obj->idBBC_vols . '</a></td>';
                print '<td>' . $obj->fk_type . '</td>';
                print '<td>' . $obj->date . '</td>';
                print '<td>' . $ballon->immat . '</td>';
                print '<td>' . $bbcUser->getFullName($langs) . '</td>';
                print '<td>' . $obj->lieuD . '</td>';
                print '<td>' . $obj->lieuA . '</td>';
                print '<td>' . $obj->heureD . '</td>';
                print '<td>' . $obj->heureA . '</td>';
                print '<td>' . $obj->time . 'min </td>';
                print '<td>' . $obj->nbrPax . '</td>';
                print '<td>' . $obj->remarque . '</td>';
                print '<td>' . $obj->incidents . '</td>';
                print '<td>' . $obj->kilometers . '</td>';
                print '<td>' . $obj->justif_kilometers . '</td>';
                if ($user->rights->flightLog->vol->status) {
                    $vol = new Bbcvols($db);
                    $vol->fetch($obj->idBBC_vols);
                    print '<td>' . $vol->getStatus() . '</td>';
                }
            }
            print'</tr>';
            $i++;
        }
    }
    print'</table>';
}

llxFooter();
