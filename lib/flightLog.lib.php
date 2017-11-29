<?php
/**
 * @deprecated use the BillableFlightQueryHandler for that.
 *
 * @param DoliDb $db
 * @param string $sql
 * @param bool   $total
 * @param string $year
 *
 * @return array
 */
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

    //total orga
    $sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON rowid = fk_organisateur WHERE YEAR(date) = \'' . $year . '\' AND fk_type IN (1,2) GROUP BY fk_organisateur';
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

    //total orga T6 - instructeur
    $sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON rowid = fk_organisateur WHERE YEAR(date) = \'' . $year . '\' AND fk_type = 6 GROUP BY fk_organisateur';
    $resql = $db->query($sql);
    if ($resql && $total) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol

                if ($obj) {
                    $array[$obj->rowid]['name'] = $obj->firstname . ' ' . $obj->name;
                    $array[$obj->rowid]['orga_T6']['count'] = $obj->total;
                }
                $i++;
            }
        }
    }

    return $array;
}

/**
 * @param int $active
 *
 * @return BbctypesLine[]
 */
function fetchBbcFlightTypes($active = 1)
{
    global $db;

    $bbcTypes = new Bbctypes($db);

    $bbcTypes->fetchAll('', '', 0, 0, [
        "active" => $active
    ]);

    return $bbcTypes->lines;
}

/**
 * Return list of flight type
 *
 * @param   mixed $selected  Preselected type
 * @param   mixed $htmlname  Name of field in form
 * @param   mixed $showempty Add an empty field
 */
function select_flight_type($selected = '1', $htmlname = 'type', $showempty = false)
{

    global $langs;
    $langs->load("trips");

    $types = fetchBbcFlightTypes();

    print '<select class="flat" name="' . $htmlname . '">';

    if ($showempty) {
        print sprintf('<option selected="%s" value=""></option>', (($selected == "" || $selected == 0 || $selected == -1) ? "selected" : ""));
    }

    foreach ($types as $flightType) {
        print '<option value="' . $flightType->id . '"';
        if ($flightType->numero == $selected) {
            print ' selected="selected"';
        }
        print '>';
        echo "T" . $flightType->numero . '-' . $flightType->nom;
        print "</option>";
    }

    print '</select>';
}

/**
 * @param string $selected
 * @param string $htmlname
 * @param int    $showimmat
 * @param int    $showDeclasse
 */
function select_balloons($selected = '', $htmlname = 'ballon', $showimmat = 0, $showDeclasse = 1)
{

    global $db, $langs;

    $langs->load("trips");
    print '<!-- select_balloons in form class -->';
    print '<select class="flat" name="' . $htmlname . '">';

    print '<option value=""';
    if ($selected == -1 || $selected == '' || $selected == 0) {
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
 * @param null $year
 * @param null $pilotId
 * @param null $quarter
 * @param bool $groupBy
 *
 * @return string
 */
function generateQuarterQuery($year = null, $pilotId = null, $quarter = null, $groupBy = true)
{

    global $db;

    $sql = "SELECT USR.rowid, USR.lastname, USR.firstname, QUARTER(VOL.date) as quartil ";

    if ($groupBy) {
        $sql .= " , SUM(VOL.kilometers) as SUM";
        $sql .= " , COUNT(VOL.idBBC_vols) as nbrFlight";
    } else {
        $sql .= " , VOL.*";
    }

    $sql .= " FROM llx_bbc_vols as VOL";
    $sql .= " LEFT OUTER JOIN llx_user AS USR ON VOL.fk_pilot = USR.rowid";
    $sql .= " WHERE ";
    $sql .= " 1 = 1 ";
    $sql .= " AND YEAR(VOL.date) = " . ($year ?: 'YEAR(NOW())');
    $sql .= " AND ( VOL.fk_type = 1 OR VOL.fk_type = 2 ) ";

    if ($pilotId !== null) {
        $sql .= " AND USR.rowid = " . $pilotId;
    }

    if ($quarter !== null) {
        $sql .= " AND QUARTER(VOL.date) = " . $quarter;
    }

    if ($groupBy) {
        $sql .= " GROUP BY QUARTER(VOL.date), VOL.fk_pilot";
    }
    $sql .= " ORDER BY QUARTER(VOL.date), VOL.fk_pilot";

    return $db->escape($sql);
}

/**
 * @param int $pilotId
 * @param int $year
 * @param int $quarter
 *
 * @return array
 */
function findFlightByPilotAndQuarter($pilotId, $year, $quarter)
{
    global $db;

    $sql = generateQuarterQuery($year, $pilotId, $quarter, false);
    $flights = [];
    $resql = $db->query($sql);
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $flight = $db->fetch_object($resql);
                if ($flight) {
                    $flights[] = $flight;
                }
                $i++;
            }
        }
    }


    return $flights;
}

/**
 * @param int $year
 *
 * @return array
 */
function bbcKilometersByQuartil($year)
{
    global $db;

    $sql = generateQuarterQuery($year);
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
                    $kmByQuartil[$rowId]["name"] = $name;
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
    print '<td class="liste_titre" colspan="5">Trimestre 2 (Avr - Juin)</td>';
    print '<td class="liste_titre" colspan="5">Trimestre 3 (Juil - Sept)</td>';
    print '<td class="liste_titre" colspan="5">Trimestre 4 (Oct - Dec)</td>';
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

    $totalQ1 = 0;
    $totalQ2 = 0;
    $totalQ3 = 0;
    $totalQ4 = 0;

    $curMonth = date("m", time());
    $curQuarter = ceil($curMonth/3);
    $disableColor = 'style="background-color: lightyellow;" title="N/A" data-toggle="tooltip"';

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

        $amoutQ1 = ($sumQ1 * $tauxRemb) + ($flightsQ1 * $unitPriceMission);
        $amoutQ2 = ($sumQ2 * $tauxRemb) + ($flightsQ2 * $unitPriceMission);
        $amoutQ3 = ($sumQ3 * $tauxRemb) + ($flightsQ3 * $unitPriceMission);
        $amoutQ4 = ($sumQ4 * $tauxRemb) + ($flightsQ4 * $unitPriceMission);

        $totalQ1 += $amoutQ1;
        $totalQ2 += $amoutQ2;
        $totalQ3 += $amoutQ3;
        $totalQ4 += $amoutQ4;

        $sumKm = ($sumQ1 + $sumQ2 + $sumQ3 + $sumQ4);
        $sumFlights = ($flightsQ1 + $flightsQ2 + $flightsQ3 + $flightsQ4);

        print '<tr>';

        print '<td>' . $name . '</td>';
        print '<td>' . $firstname . '</td>';

        print '<td'.($curQuarter < 1 ? $disableColor: '').'>' . ($flightsQ1) . '</td>';
        print '<td'.($curQuarter < 1 ? $disableColor: '').'>' . ($flightsQ1 * $unitPriceMission) . '€</td>';
        print '<td'.($curQuarter < 1 ? $disableColor: '').'>' . $sumQ1 . '</td>';
        print '<td'.($curQuarter < 1 ? $disableColor: '').'>' . ($sumQ1 * $tauxRemb) . '</td>';
        print '<td'.($curQuarter < 1 ? $disableColor: '').'><b>' . $amoutQ1 . '€</b></td>';

        print '<td '.($curQuarter < 2 ? $disableColor: '').'>' . ($flightsQ2) . '</td>';
        print '<td '.($curQuarter < 2 ? $disableColor: '').'>' . ($flightsQ2 * $unitPriceMission) . '€</td>';
        print '<td '.($curQuarter < 2 ? $disableColor: '').'>' . $sumQ2 . '</td>';
        print '<td '.($curQuarter < 2 ? $disableColor: '').'>' . ($sumQ2 * $tauxRemb) . '</td>';
        print '<td '.($curQuarter < 2 ? $disableColor: '').'><b>' . $amoutQ2 . '€</b></td>';

        print '<td '.($curQuarter < 3 ? $disableColor: '').'>' . ($flightsQ3) . '</td>';
        print '<td '.($curQuarter < 3 ? $disableColor: '').'>' . ($flightsQ3 * $unitPriceMission) . '€</td>';
        print '<td '.($curQuarter < 3 ? $disableColor: '').'>' . $sumQ3 . '</td>';
        print '<td '.($curQuarter < 3 ? $disableColor: '').'>' . ($sumQ3 * $tauxRemb) . '</td>';
        print '<td '.($curQuarter < 3 ? $disableColor: '').'><b>' . $amoutQ3 . '€</b></td>';

        print '<td '.($curQuarter < 4 ? $disableColor: '').'>' . ($flightsQ4) . '</td>';
        print '<td '.($curQuarter < 4 ? $disableColor: '').'>' . ($flightsQ4 * $unitPriceMission) . '€</td>';
        print '<td '.($curQuarter < 4 ? $disableColor: '').'>' . $sumQ4 . '</td>';
        print '<td '.($curQuarter < 4 ? $disableColor: '').'>' . ($sumQ4 * $tauxRemb) . '</td>';
        print '<td '.($curQuarter < 4 ? $disableColor: '').'><b>' . $amoutQ4 . '€</b></td>';

        print '<td>' . (($sumFlights * $unitPriceMission) + ($sumKm * $tauxRemb)) . '€</td>';

        print '</tr>';
    }

    print "<td colspan='6'></td>";
    print "<td>".price($totalQ1)."€</td>";
    print "<td colspan='4'></td>";
    print "<td>".price($totalQ2)."€</td>";
    print "<td colspan='4'></td>";
    print "<td>".price($totalQ3)."€</td>";
    print "<td colspan='4'></td>";
    print "<td>".price($totalQ4)."€</td>";
    print "<td></td>";

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

            if ($obj->annee) {
                $results[] = $obj->annee;
            }

            $i++;
        }
    }

    return $results;
}

/**
 * @param array $results
 * @param int   $year
 * @param int   $type
 * @param int   $val
 *
 * @return array
 * @throws Exception
 */
function addValueForYear($results, $year, $type, $val)
{
    if (!is_array($results)) {
        return $results;
    }

    $countResults = count($results);

    for ($i = 0; $i < $countResults; $i++) {
        $resultLine = $results[$i];
        if (!is_array($resultLine)) {
            throw new \Exception("not an array ");
        }

        if (in_array($year, $resultLine)) {
            $results[$i][$type] = $val;
            return $results;
        }
    }

    //not found add a new entry
    $results[] = [
        $year,
        $type => $val
    ];

    return $results;

}

/**
 * @param GraphicalData $graphData
 *
 * @return GraphicalData
 */
function fetchGraphByTypeAndYearData(GraphicalData $graphData)
{
    global $db;

    $sql = "SELECT YEAR(date) as year, fk_type as type,COUNT(idBBC_vols) as val FROM llx_bbc_vols GROUP BY YEAR(date), fk_type ORDER BY year,fk_type";
    $resql = $db->query($sql);

    $num = $db->num_rows($resql);
    $i = 0;
    if ($num) {
        while ($i < $num) {
            $obj = $db->fetch_object($resql);

            if ($obj->year) {
                $graphData->addValue($obj->year, new GraphicalValue($obj->val, $obj->year, $obj->type));
            }

            $i++;
        }
    }

    return $graphData;
}

/**
 * @return GraphicalData
 */
function getGraphByTypeAndYearData()
{

    $flightTypes = fetchBbcFlightTypes();

    $graphData = new GraphicalData();

    foreach (getFlightYears() as $flightYear) {
        $pieceData = new YearGraphicalData($flightYear);

        foreach ($flightTypes as $flightType) {
            $pieceData->addType(new GraphicalType($flightType->id, $flightType->nom));
        }

        $graphData->addData($pieceData);
    }

    return fetchGraphByTypeAndYearData($graphData);
}