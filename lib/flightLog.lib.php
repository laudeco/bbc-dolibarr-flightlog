<?php

function sqlToArray(DoliDb $db, $sql, $total = true, $year = '')
{
    $resql = $db->query($sql);
    $array = array();
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol
                if ($obj) {
                    $array[$obj->pilot][$obj->type]['time'] = $obj->time;
                    $array[$obj->pilot][$obj->type]['count'] = $obj->nbr;
                    $array[$obj->pilot]['name'] = $obj->prenom . ' ' . $obj->nom;
                    $array[$obj->pilot]['id'] = $obj->pilot;
                }
                $i++;
            }
        }
    }

    //total time for pilot
    $sql = 'SELECT llx_user.name, llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON rowid = fk_organisateur WHERE YEAR(date) = \'' . $year . '\' AND fk_type IN (1,2) GROUP BY fk_organisateur';
    $resql = $db->query($sql);
    if ($resql && $total) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol

                if ($obj) {
                    $array[$obj->rowid]['name'] = $obj->firstname . ' ' . $obj->name;
                    $array[$obj->rowid]['orga']['count'] = $obj->total;
                }
                $i++;
            }
        }
    }

    return $array;
}

/**
 * Return list of flight type
 *
 * @param   mixed $selected  Preselected type
 * @param   mixed $htmlname  Name of field in form
 * @param   mixed $showempty Add an empty field
 */
function select_flight_type($selected = '1', $htmlname = 'type', $showempty = 0)
{

    global $db, $langs, $user;
    $langs->load("trips");

    print '<select class="flat" name="' . $htmlname . '">';

    $resql = $db->query("SELECT B.idType,B.numero,B.nom FROM llx_bbc_types as B WHERE active=1");
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql);
                if ($obj) {
                    print '<option value="' . $obj->idType . '"';
                    if ($obj->numero == $selected) {
                        print ' selected="selected"';
                    }
                    print '>';
                    echo $obj->numero . '-' . $obj->nom;
                    print "</option>";
                }
                $i++;
            }
        }
    }

    print '</select>';
}

/**
 *        Return list of Balloons
 *
 * @param   mixed  $selected  Preselected Balloon
 * @param   mixed  $htmlname  Name of field in form
 * @param    mixed $showempty Add an empty field
 */
function select_balloons($selected = '', $htmlname = 'ballon', $showempty = 0, $showimmat = 0, $showDeclasse = 1)
{

    global $db, $langs, $user;
    $langs->load("trips");
    print '<!-- select_balloons in form class -->';
    print '<select class="flat" name="' . $htmlname . '">';

    print '<option value="-1"';
    if ($selected == -1) {
        print ' selected="selected"';
    }
    print '>&nbsp;</option>';

    if (!$showDeclasse) {
        $resql = $db->query("SELECT B.immat,B.rowid FROM llx_bbc_ballons as B WHERE is_disable = false ");
    } else {
        $resql = $db->query("SELECT B.immat,B.rowid FROM llx_bbc_ballons as B");
    }

    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql);
                if ($obj) {
                    if ($showimmat) {
                        print '<option value="' . $obj->immat . '"';
                    } else {
                        print '<option value="' . $obj->rowid . '"';
                    }
                    if ($obj->rowid == $selected) {
                        print ' selected="selected"';
                    }
                    print '>';
                    echo strtoupper($obj->immat);
                    print "</option>";
                }
                $i++;
            }
        }
    }

    print '</select>';
}

/**
 * @param int $year
 *
 * @return array
 */
function bbcKilometersByQuartil($year)
{
    global $db;

    $sql = "SELECT USR.rowid, USR.lastname, USR.firstname , SUM(VOL.kilometers) as SUM, QUARTER(VOL.date) as quartil, COUNT(VOL.idBBC_vols) as nbrFlight";
    $sql .= " FROM llx_bbc_vols as VOL";
    $sql .= " LEFT OUTER JOIN llx_user AS USR ON VOL.fk_pilot = USR.rowid";
    $sql .= " WHERE ";
    $sql .= " YEAR(VOL.date) = " . ($year ?: 'YEAR(NOW())');
    $sql .= " AND ( VOL.fk_type = 1 OR VOL.fk_type = 2 ) ";
    $sql .= " GROUP BY QUARTER(VOL.date), VOL.fk_pilot";
    $sql .= " ORDER BY QUARTER(VOL.date), VOL.fk_pilot";

    $resql = $db->query($sql);

    $kmByQuartil = array();
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

    return $kmByQuartil;
}

/**
 * @param $kmByQuartil
 * @param $tauxRemb
 */
function printBbcKilometersByQuartil($kmByQuartil, $tauxRemb, $unitPriceMission)
{
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

    foreach ($kmByQuartil as $id => $rembKm) {
        $name = $rembKm["name"];
        $firstname = $rembKm["firstname"];
        $sumQ1 = isset($rembKm["quartil"]["1"]["km"]) ? $rembKm["quartil"]["1"]["km"] : 0;
        $sumQ2 = isset($rembKm["quartil"]["2"]["km"]) ? $rembKm["quartil"]["2"]["km"] : 0;
        $sumQ3 = isset($rembKm["quartil"]["3"]["km"]) ? $rembKm["quartil"]["3"]["km"] : 0;
        $sumQ4 = isset($rembKm["quartil"]["4"]["km"]) ? $rembKm["quartil"]["4"]["km"] : 0;

        $flightsQ1 = isset($rembKm["quartil"]["1"]["flight"]) ? $rembKm["quartil"]["1"]["flight"] : 0;
        $flightsQ2 = isset($rembKm["quartil"]["2"]["flight"]) ? $rembKm["quartil"]["2"]["flight"] : 0;
        $flightsQ3 = isset($rembKm["quartil"]["3"]["flight"]) ? $rembKm["quartil"]["3"]["flight"] : 0;
        $flightsQ4 = isset($rembKm["quartil"]["4"]["flight"]) ? $rembKm["quartil"]["4"]["flight"] : 0;

        $sumKm = ($sumQ1 + $sumQ2 + $sumQ3 + $sumQ4);
        $sumFlights = ($flightsQ1 + $flightsQ2 + $flightsQ3 + $flightsQ4);

        print '<tr>';

        print '<td>' . $name . '</td>';
        print '<td>' . $firstname . '</td>';

        print '<td>' . ($flightsQ1) . '</td>';
        print '<td>' . ($flightsQ1 * $unitPriceMission) . '€</td>';
        print '<td>' . $sumQ1 . '</td>';
        print '<td>' . ($sumQ1 * $tauxRemb) . '</td>';
        print '<td><b>' . (($sumQ1 * $tauxRemb) + ($flightsQ1 * $unitPriceMission)) . '€</b></td>';

        print '<td>' . ($flightsQ2) . '</td>';
        print '<td>' . ($flightsQ2 * $unitPriceMission) . '€</td>';
        print '<td>' . $sumQ2 . '</td>';
        print '<td>' . ($sumQ2 * $tauxRemb) . '</td>';
        print '<td><b>' . (($sumQ2 * $tauxRemb) + ($flightsQ2 * $unitPriceMission)) . '€</b></td>';

        print '<td>' . ($flightsQ3) . '</td>';
        print '<td>' . ($flightsQ3 * $unitPriceMission) . '€</td>';
        print '<td>' . $sumQ3 . '</td>';
        print '<td>' . ($sumQ3 * $tauxRemb) . '</td>';
        print '<td><b>' . (($sumQ3 * $tauxRemb) + ($flightsQ3 * $unitPriceMission)) . '€</b></td>';

        print '<td>' . ($flightsQ4) . '</td>';
        print '<td>' . ($flightsQ4 * $unitPriceMission) . '€</td>';
        print '<td>' . $sumQ4 . '</td>';
        print '<td>' . ($sumQ4 * $tauxRemb) . '</td>';
        print '<td><b>' . (($sumQ4 * $tauxRemb) + ($flightsQ4 * $unitPriceMission)) . '€</b></td>';

        print '<td>' . (($sumFlights * $unitPriceMission) + ($sumKm * $tauxRemb)) . '€</td>';

        print '</tr>';
    }

    print '</table>';
}

/**
 * @return int[]
 */
function getFlightYears()
{
    global $db;

    $results = [];

    $sqlYear = "SELECT DISTINCT(YEAR(llx_bbc_vols.date)) as annee FROM llx_bbc_vols ";
    $resql_years = $db->query($sqlYear);

    $num = $db->num_rows($resql_years);
    $i = 0;
    if ($num) {
        while ($i < $num) {
            $obj = $db->fetch_object($resql_years);

            if($obj->annee){
                $results[] = $obj->annee;
            }

            $i++;
        }
    }

    return $results;
}