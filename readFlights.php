<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
dol_include_once("/flightLog/inc/other.php");

// Load translation files required by the page
$langs->load("mymodule@mymodule");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$myparam = GETPOST('myparam', 'alpha');

// Access control
if ($user->socid > 0) {
	// External user
	accessforbidden();
}

// Default action
if (empty($action) && empty($id) && empty($ref)) {
	$action='create';
}

// Load object if id or ref is provided as parameter
$object = new Bbcvols($db);
if (($id > 0 || ! empty($ref)) && $action != 'add') {
	$result = $object->fetch($id, $ref);
	if ($result < 0) {
		dol_print_error($db);
	}
}

/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */

if ($action == 'add') {
	$myobject = new Bbcvols($db);
	$myobject->prop1 = $_POST["field1"];
	$myobject->prop2 = $_POST["field2"];
	$result = $myobject->create($user);
	if ($result > 0) {
		// Creation OK
	} {
		// Creation KO
		$mesg = $myobject->error;
	}
}

/*
 * VIEW
 *
 * Put here all code to build page
 */

llxHeader('', $langs->trans('Read flights'), '');

$form = new Form($db);

// Put here content of your page
$data = array(); // array(array('abs1',valA1,valB1), array('abs2',valA2,valB2), ...)
$tmp = array();
$legend = array();

//si l'utilisateur n'a pas de droit sur la page
if (!$user->rights->flightLog->vol->detail && !$user->rights->flightLog->vol->status && !$user->admin) {
    exit;
}

//tableau par pilote
$sql = "SELECT USR.lastname AS nom , USR.firstname AS prenom ,COUNT(`idBBC_vols`) AS nbr,fk_pilot as pilot, TT.numero as type,SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(heureA,heureD)))) AS time";
$sql.= " FROM llx_bbc_vols, llx_user AS USR,llx_bbc_types AS TT ";
$sql.= " WHERE `fk_pilot`= USR.rowid AND fk_type = TT.idType AND YEAR(llx_bbc_vols.date) = ". (GETPOST("year") ? "'".GETPOST("year")."'" : 'YEAR(NOW())');
$sql.= " GROUP BY fk_pilot,`fk_type`";

$resql = $db->query($sql);

$sqlYear = "SELECT DISTINCT(YEAR(llx_bbc_vols.date)) as annee FROM llx_bbc_vols ";
$resql_years = $db->query($sqlYear);

$pilotNumberFlight = array();
if ($resql && ($user->rights->flightLog->vol->detail || $user->admin)) {

    $num = $db->num_rows($resql_years);
    $i = 0;
    if ($num) {
        print '<div class="tabs">';
        print '<a class="tabTitle"><img src="../theme/eldy/img/object_user.png" border="0" alt="" title=""> Recap / utilisateur </a>'; //title

        while ($i < $num) {
            $obj = $db->fetch_object($resql_years); //vol
            if($obj->annee)
                print '<a class="tab" id="'. (GETPOST("year") == $obj->annee || (!GETPOST("year") && $obj->annee == date("Y"))? 'active' : '').'" " href="readFlights.php?year=' . $obj->annee . '">'.$obj->annee.'</a>';
            $i++;
        }
        print '</div>';
    }


    print '<div class="tabBar">';
    print '<table class="border" width="100%">';

    print '<tr class="liste_titre">';
    print '<td colspan="2">Nom</td>';
    print '<td class="liste_titre" colspan="2">Type 1 : Sponsor</td>';
    print '<td class="liste_titre" colspan="2">Type 2 : Baptême</td>';
    print '<td class="liste_titre" colspan="2">Organisateur (T1/T2)</td>';
    print '<td class="liste_titre" >Total bonus</td>';
    print '<td class="liste_titre" colspan="2">Type 3 : Privé</td>';
    print '<td class="liste_titre" colspan="2">Type 4: Meeting</td>';
    print '<td class="liste_titre" colspan="1">Type 5: Chambley</td>';
    print '<td class="liste_titre" colspan="2">Type 6: instruction</td>';
    print '<td class="liste_titre" colspan="2">Type 7: vols < 50 </td>';
    print '<td class="liste_titre" colspan="1">Facture</td>';
    print '<td class="liste_titre" colspan="1">A payer</td>';
    print '<tr>';

    print '<tr class="liste_titre">';
    print '<td colspan="2" class="liste_titre"></td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> Pts </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> Pts </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> Pts </td>';

    print '<td class="liste_titre"> Bonus gagnés </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> € </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> € </td>';

    print '<td class="liste_titre"> # </td>';

    print '<td class="liste_titre"> # </td>';
    print '<td class="liste_titre"> € </td>';

    print '<td class="liste_titre"> #</td>';
    print '<td class="liste_titre"> €</td>';

    print '<td class="liste_titre"> € </td>';
    print '<td class="liste_titre"> Balance (A payer) €</td>';

    print'</tr>';
    $table = sqlToArray($db, $sql, true, (GETPOST("year") ?: date("Y")));
    foreach ($table as $key => $value) {

        $totalBonus = $value['1']['count']*50 + $value['2']['count']*50 + $value['orga']['count']*25;
        $totalFacture = $value['3']['count'] * 150 + $value['4']['count'] * 100+ $value['6']['count'] * 50 + $value['7']['count'] * 75;
        $facturable = $totalFacture-$totalBonus;

        $pilotNumberFlight[$value['id']] = array(
            "1" =>  $value['1']['count'],
            "2" =>  $value['2']['count'],
            "3" =>  $value['3']['count'],
            "4" =>  $value['4']['count'],
            "5" =>  $value['5']['count'],
            "6" =>  $value['6']['count'],
            "7" =>  $value['7']['count'],
        );

        print '<tr>';
        print '<td>' . $key . '</td>';
        print '<td>' . $value['name'] . '</td>';

        print '<td>' . $value['1']['count'] . '</td>';
        print '<td>' . $value['1']['count']*50 . '</td>';

        print '<td>' . $value['2']['count'] . '</td>';
        print '<td>' . $value['2']['count']*50 . '</td>';

        print '<td>' . $value['orga']['count'] . '</td>';
        print '<td>' . $value['orga']['count']*25 . '</td>';

        print '<td><b>' . ($totalBonus) . '</b></td>';

        print '<td>' . $value['3']['count'] . '</td>';
        print '<td>' . price($value['3']['count']*150) . '€</td>';

        print '<td>' . $value['4']['count'] . '</td>';
        print '<td>' . price($value['4']['count']*100) . '€</td>';

        print '<td>' . $value['5']['count'] . '</td>';

        print '<td>' . $value['6']['count'] . '</td>';
        print '<td>' . price($value['6']['count']*50) . '€</td>';

        print '<td>' . $value['7']['count'] . '</td>';
        print '<td>' . price($value['7']['count']*75) . '€</td>';

        print '<td>' . price($totalFacture) . '€ </td>';
        print '<td><b>' . price(($facturable < 0 ? 0 : $facturable )). '€</b></td>';
        print '</tr>';
    }
    print'</table>';


    print '<br/>';
    print '<h3>Remboursement aux pilotes</h3>';

    //table km
    $sql = "SELECT USR.rowid, USR.lastname, USR.firstname , SUM(VOL.kilometers) as SUM, QUARTER(VOL.date) as quartil, COUNT(VOL.idBBC_vols) as nbrFlight";
    $sql .= " FROM llx_bbc_vols as VOL";
    $sql .= " LEFT OUTER JOIN llx_user AS USR ON VOL.fk_pilot = USR.rowid";
    $sql.= " WHERE ";
    $sql.= " YEAR(VOL.date) = ". (GETPOST("year") ? "'".GETPOST("year")."'" : 'YEAR(NOW())');
    $sql.= " AND ( VOL.fk_type = 1 OR VOL.fk_type = 2 ) ";
    $sql .= " GROUP BY QUARTER(VOL.date), VOL.fk_pilot";
    $sql .= " ORDER BY QUARTER(VOL.date), VOL.fk_pilot";

    $resql = $db->query($sql);

    $kmByQuartil = array();
    $tauxRemb = isset($conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM) ? $conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM : 0;
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol
                if ($obj) {

                    $rowId = $obj->rowid;
                    $name = $obj->lastname;
                    $firstname = $obj->firstname;
                    $sum = $obj->SUM;
                    $quartil = $obj->quartil;

                    $kmByQuartil[$rowId]["name"] = $name;
                    $kmByQuartil[$rowId]["firstname"] = $firstname;

                    $kmByQuartil[$rowId]["quartil"][$quartil]["km"] = $sum;
                    $kmByQuartil[$rowId]["quartil"][$quartil]["flight"] = $obj->nbrFlight;


                }
                $i++;
            }
        }
    }

    print '<table class="border" width="100%">';

    print '<tr>';
    print '<td></td>';
    print '<td></td>';

    print '<td class="liste_titre" colspan="5">Trimestre 1 (Jan - Mars)</td>';
    print '<td class="liste_titre" colspan="5">Trimestre 2</td>';
    print '<td class="liste_titre" colspan="5">Trimestre 3</td>';
    print '<td class="liste_titre" colspan="5">Trimestre 4</td>';
    print '<td class="liste_titre" >Total</td>';

    print '</tr>';

    print '<tr class="liste_titre">';
    print '<td class="liste_titre" > Nom </td>';
    print '<td class="liste_titre" > Prenom </td>';


    print '<td class="liste_titre" > # T1 & T2</td>';
    print '<td class="liste_titre" > Forfaits pil </td>';
    print '<td class="liste_titre" > Total des KM </td>';
    print '<td class="liste_titre" > Remb km €</td>';
    print '<td class="liste_titre" > Total € </td>';

    print '<td class="liste_titre" > # T1 & T2</td>';
    print '<td class="liste_titre" > Forfaits pil </td>';
    print '<td class="liste_titre" > Total des KM </td>';
    print '<td class="liste_titre" > Remb km €</td>';
    print '<td class="liste_titre" > Total € </td>';

    print '<td class="liste_titre" > # T1 & T2</td>';
    print '<td class="liste_titre" > Forfaits pil </td>';
    print '<td class="liste_titre" > Total des KM </td>';
    print '<td class="liste_titre" > Remb km €</td>';
    print '<td class="liste_titre" > Total € </td>';

    print '<td class="liste_titre" > # T1 & T2</td>';
    print '<td class="liste_titre" > Forfaits pil </td>';
    print '<td class="liste_titre" > Total des KM </td>';
    print '<td class="liste_titre" > Remb km €</td>';
    print '<td class="liste_titre" > Total € </td>';

    print '<td class="liste_titre" > Total € </td>';
    print '</tr>';

    foreach ($kmByQuartil as $id => $rembKm){
        $name = $rembKm["name"];
        $firstname = $rembKm["firstname"];
        $sumQ1 = isset($rembKm["quartil"]["1"]["km"]) ? $rembKm["quartil"]["1"]["km"]: 0;
        $sumQ2 = isset($rembKm["quartil"]["2"]["km"]) ? $rembKm["quartil"]["2"]["km"]: 0;
        $sumQ3 = isset($rembKm["quartil"]["3"]["km"]) ? $rembKm["quartil"]["3"]["km"]: 0;
        $sumQ4 = isset($rembKm["quartil"]["4"]["km"]) ? $rembKm["quartil"]["4"]["km"]: 0;

        $flightsQ1 = isset($rembKm["quartil"]["1"]["flight"]) ? $rembKm["quartil"]["1"]["flight"]: 0;
        $flightsQ2 = isset($rembKm["quartil"]["2"]["flight"]) ? $rembKm["quartil"]["2"]["flight"]: 0;
        $flightsQ3 = isset($rembKm["quartil"]["3"]["flight"]) ? $rembKm["quartil"]["3"]["flight"]: 0;
        $flightsQ4 = isset($rembKm["quartil"]["4"]["flight"]) ? $rembKm["quartil"]["4"]["flight"]: 0;

        $sumKm = ($sumQ1 + $sumQ2 + $sumQ3 + $sumQ4);
        $sumFlights = ($flightsQ1 + $flightsQ2 + $flightsQ3 + $flightsQ4);

        print '<tr>';

        print '<td>' . $name . '</td>';
        print '<td>' . $firstname . '</td>';

        print '<td>' . ($flightsQ1) . '</td>';
        print '<td>' . ($flightsQ1 * 35) . '€</td>';
        print '<td>' . $sumQ1 . '</td>';
        print '<td>' . ($sumQ1 * $tauxRemb) . '</td>';
        print '<td><b>' . (($sumQ1 * $tauxRemb) + ($flightsQ1 * 35)) . '€</b></td>';

        print '<td>' . ($flightsQ2) . '</td>';
        print '<td>' . ($flightsQ2 * 35) . '€</td>';
        print '<td>' . $sumQ2 . '</td>';
        print '<td>' . ($sumQ2 * $tauxRemb) . '</td>';
        print '<td><b>' . (($sumQ2 * $tauxRemb) + ($flightsQ2 * 35)) . '€</b></td>';

        print '<td>' . ($flightsQ3) . '</td>';
        print '<td>' . ($flightsQ3 * 35) . '€</td>';
        print '<td>' . $sumQ3 . '</td>';
        print '<td>' . ($sumQ3 * $tauxRemb) . '</td>';
        print '<td><b>' . (($sumQ3 * $tauxRemb) + ($flightsQ3 * 35)) . '€</b></td>';

        print '<td>' . ($flightsQ4) . '</td>';
        print '<td>' . ($flightsQ4 * 35) . '€</td>';
        print '<td>' . $sumQ4 . '</td>';
        print '<td>' . ($sumQ4 * $tauxRemb) . '</td>';
        print '<td><b>' . (($sumQ4 * $tauxRemb) + ($flightsQ4 * 35)) . '€</b></td>';

        print '<td>' . (($sumFlights * 35) + ($sumKm * $tauxRemb)) . '€</td>';

        print '</tr>';
    }

    print '</table>';


    print '</div>';


}
print '<br/>';


print '<div class="fichetwothirdright"><div class="ficheaddleft">';

//tableau des facturations
//TODO BBC : ajout bouton facturation to the fiche action
if ($user->rights->flightLog->vol->status || $user->admin) {

    $sql = "SELECT BAL.immat as ballon,"; //ballon
    $sql.= " USR.lastname as nom, USR.firstname as prenom, "; //pilote
    $sql.= " idBBC_vols as volid, fk_pilot,  llx_bbc_vols.date , heureD, is_facture as status"; // vol
    $sql .= " FROM llx_bbc_ballons AS BAL, llx_user AS USR, llx_bbc_vols";
    $sql.=" WHERE BBC_ballons_idBBC_ballons = BAL.rowid";
    $sql.=" AND fk_organisateur = USR.rowid";
    $sql.=" AND is_facture = 0";
    $sql.=" ORDER BY date ASC";
    $sql.=" LIMIT 10";
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
//            print '<td class="liste_titre" > heure </td>';
            print '<td class="liste_titre" > Ballon </td>';
            print '<td class="liste_titre"> Organisateur </td>';
//            print '<td class="liste_titre"> Statut </td>';
            print '<td class="liste_titre"> Actions </td>';
            print'</tr>';
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol
                if ($obj) {
                    $vol = new Bbcvols($db);
                    $vol->fetch($obj->volid);

                    print '<tr class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">';

                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '"><a href="fiche.php?vol=' . $obj->volid . '">' . $obj->volid . '</a></td>';
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->date . '</td>';
//                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->heureD . '</td>';
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->ballon . '</td>';
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $obj->nom . ' ' . $obj->prenom . '</td>';
//                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . $vol->getStatus() . '</td>';
                    print '<td class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">' . '<a href="fiche.php?action=fact&vol=' . $obj->volid . '">' . img_action("default", 1) . '</a>' . '</td>';
                    print'</tr class="' . ($i % 2 == 0 ? 'pair' : 'impair') . '">';
                }

                $i++;
            }
            print'</table>';
        }
    }
}
print '<div class="tabsAction">';

if ($user->rights->flightLog->vol->status) {
    print '<a class="butAction" href="listFact.php?view=1">List facturation</a>';
}

if ($user->rights->flightLog->vol->detail) {
    print '<a class="butAction" href="listFact.php?view=2">List Aviabel</a>';
}

print '</div>';

print '</div>';
print '</div>';

llxFooter();
