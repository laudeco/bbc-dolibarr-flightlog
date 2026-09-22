<?php

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
 * Return all the flight types, active or not, sorted by their number.
 *
 * @return BbctypesLine[]
 */
function fetchAllBbcFlightTypes()
{
    global $db;

    $bbcTypes = new Bbctypes($db);
    $bbcTypes->fetchAll('ASC', 'numero');

    return $bbcTypes->lines;
}

/**
 * Keep only the flight types flagged as a mission for the club.
 *
 * @param BbctypesLine[] $flightTypes
 *
 * @return BbctypesLine[]
 */
function filterBbcMissionFlightTypes($flightTypes)
{
    return array_filter($flightTypes, function (BbctypesLine $flightType) {
        return $flightType->isMission();
    });
}

/**
 * Keep only the flight types that are not a mission for the club.
 *
 * @param BbctypesLine[] $flightTypes
 *
 * @return BbctypesLine[]
 */
function filterBbcNonMissionFlightTypes($flightTypes)
{
    return array_filter($flightTypes, function (BbctypesLine $flightType) {
        return !$flightType->isMission();
    });
}

/**
 * Keep the types that have to be displayed in the recap tables: the active ones and
 * the disabled ones still having flights in the given result set.
 *
 * @param BbctypesLine[] $flightTypes
 * @param Pilot[]        $pilots
 *
 * @return BbctypesLine[]
 */
function filterBbcFlightTypesToDisplay($flightTypes, $pilots)
{
    $usedNumeros = [];
    foreach ($pilots as $pilot) {
        foreach ($pilot->getCounts() as $currentCount) {
            if ($currentCount->getCount() > 0) {
                $usedNumeros[(string) $currentCount->getType()] = true;
            }
        }
    }

    return array_filter($flightTypes, function (BbctypesLine $flightType) use ($usedNumeros) {
        return $flightType->getActive() || isset($usedNumeros[(string) $flightType->getNumero()]);
    });
}

/**
 * All the flight types indexed by their id.
 *
 * @return BbctypesLine[]
 */
function fetchBbcFlightTypesById()
{
    $flightTypes = [];

    foreach (fetchAllBbcFlightTypes() as $flightType) {
        $flightTypes[(int) $flightType->getId()] = $flightType;
    }

    return $flightTypes;
}

/**
 * Amount reimbursed to the pilot per kilometer for a flight type. The value configured
 * for the whole module is used when the type does not carry its own.
 *
 * @param BbctypesLine|null $flightType
 *
 * @return float
 */
function bbcFlightTypeKmAllowance($flightType)
{
    global $conf;

    if (null !== $flightType && null !== $flightType->getKmAllowance()) {
        return $flightType->getKmAllowance();
    }

    return isset($conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM) ? (float) $conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM : 0;
}

/**
 * Lump sum reimbursed to the pilot for one flight of a flight type. The value configured
 * for the whole module is used when the type does not carry its own.
 *
 * @param BbctypesLine|null $flightType
 *
 * @return float
 */
function bbcFlightTypeMissionAllowance($flightType)
{
    global $conf;

    if (null !== $flightType && null !== $flightType->getMissionAllowance()) {
        return $flightType->getMissionAllowance();
    }

    return isset($conf->global->BBC_FLIGHT_LOG_UNIT_PRICE_MISSION) ? (float) $conf->global->BBC_FLIGHT_LOG_UNIT_PRICE_MISSION : 0;
}

/**
 * Ids of the flight types having at least one flight.
 *
 * @return int[]
 */
function fetchBbcFlightTypeIdsWithFlights()
{
    global $db;

    $ids = [];

    $sql = 'SELECT DISTINCT fk_type FROM ' . MAIN_DB_PREFIX . 'bbc_vols WHERE fk_type IS NOT NULL';
    $resql = $db->query($sql);
    if (!$resql) {
        return $ids;
    }

    while ($obj = $db->fetch_object($resql)) {
        $ids[] = (int) $obj->fk_type;
    }

    $db->free($resql);

    return $ids;
}

/**
 * Keep the types to show in the statistics: the active ones and the disabled ones that
 * still carry flights, so that the history of a retired type does not disappear.
 *
 * @param BbctypesLine[] $flightTypes
 *
 * @return BbctypesLine[]
 */
function filterBbcFlightTypesWithHistory($flightTypes)
{
    $idsWithFlights = fetchBbcFlightTypeIdsWithFlights();

    return array_filter($flightTypes, function (BbctypesLine $flightType) use ($idsWithFlights) {
        return $flightType->getActive() || in_array((int) $flightType->getId(), $idsWithFlights, true);
    });
}

/**
 * Build the flight type configuration used by the javascript of the flight forms.
 * Everything that drives the display of the form is configured on the type itself.
 *
 * @param BbctypesLine[] $flightTypes
 *
 * @return stdClass
 */
function bbcFlightTypesAsJsConfiguration($flightTypes)
{
    $configuration = [];

    foreach ($flightTypes as $flightType) {
        $configuration[(int) $flightType->getId()] = [
            'id' => (int) $flightType->getId(),
            'billable' => $flightType->isPaxRequired() ? 1 : 0,
            'expensable' => $flightType->isMission() ? 1 : 0,
            'instruction' => $flightType->isInstruction() ? 1 : 0,
        ];
    }

    return (object) $configuration;
}

/**
 * Label of a flight type used as column header.
 *
 * @param BbctypesLine $flightType
 *
 * @return string
 */
function bbcFlightTypeColumnLabel(BbctypesLine $flightType)
{
    $label = sprintf('%s :<br/>%s', $flightType->getShortLabel(), $flightType->getNom());

    if (!$flightType->getActive()) {
        $label .= '<br/><span class="text-muted">(inactif)</span>';
    }

    return $label;
}

/**
 * Return the flight types flagged as a mission for the club. Those flights give
 * points to the pilots and are the base of the expense notes.
 *
 * Inactive types are kept: a type may have been disabled while flights of the
 * past still have to be counted.
 *
 * @return BbctypesLine[]
 */
function fetchBbcMissionFlightTypes()
{
    return filterBbcMissionFlightTypes(fetchAllBbcFlightTypes());
}

/**
 * Return the flight types that are not a mission for the club.
 *
 * @return BbctypesLine[]
 */
function fetchBbcNonMissionFlightTypes()
{
    return filterBbcNonMissionFlightTypes(fetchAllBbcFlightTypes());
}

/**
 * Return the flight types flagged as an instruction flight.
 *
 * @return BbctypesLine[]
 */
function fetchBbcInstructionFlightTypes()
{
    return array_filter(fetchAllBbcFlightTypes(), function (BbctypesLine $flightType) {
        return $flightType->isInstruction();
    });
}

/**
 * Return the flight types that have to be invoiced to a customer.
 *
 * @return BbctypesLine[]
 */
function fetchBbcBillingRequiredFlightTypes()
{
    return array_filter(fetchAllBbcFlightTypes(), function (BbctypesLine $flightType) {
        return $flightType->isBillingRequired();
    });
}

/**
 * Build a safe SQL list (eg. "1,2") of flight type ids usable in a IN (...) clause.
 * When no type matches, returns "0" so that the condition never matches a flight.
 *
 * @param BbctypesLine[] $flightTypes
 *
 * @return string
 */
function bbcFlightTypeIdsAsSqlList($flightTypes)
{
    $ids = array_map(function (BbctypesLine $flightType) {
        return (int) $flightType->getId();
    }, array_values($flightTypes));

    if (empty($ids)) {
        return '0';
    }

    return implode(',', $ids);
}

/**
 * SQL list of the flight type ids considered as a mission for the club.
 *
 * @return string
 */
function bbcMissionFlightTypeIdsAsSqlList()
{
    return bbcFlightTypeIdsAsSqlList(fetchBbcMissionFlightTypes());
}

/**
 * SQL list of the flight type ids considered as an instruction flight.
 *
 * @return string
 */
function bbcInstructionFlightTypeIdsAsSqlList()
{
    return bbcFlightTypeIdsAsSqlList(fetchBbcInstructionFlightTypes());
}

/**
 * SQL list of the flight type ids that have to be invoiced to a customer.
 *
 * @return string
 */
function bbcBillingRequiredFlightTypeIdsAsSqlList()
{
    return bbcFlightTypeIdsAsSqlList(fetchBbcBillingRequiredFlightTypes());
}

/**
 * Human readable list of the mission types (eg. "T1 & T2").
 *
 * @return string
 */
function bbcMissionFlightTypesLabel()
{
    $labels = array_map(function (BbctypesLine $flightType) {
        return $flightType->getShortLabel();
    }, array_values(fetchBbcMissionFlightTypes()));

    if (empty($labels)) {
        return '';
    }

    return implode(' & ', $labels);
}

/**
 * @deprecated should use the form instead.
 *
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

    print '<select class="flat js-flight-type" name="' . $htmlname . '">';

    if ($showempty) {
        print sprintf('<option selected="%s" value=""></option>',
            (($selected == "" || $selected == 0 || $selected == -1) ? "selected" : ""));
    }

    foreach ($types as $flightType) {
        print '<option value="' . $flightType->id . '"';
        if ($flightType->id == $selected) {
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
 * @param int $showimmat
 * @param int $showDeclasse
 * @param bool $group
 */
function select_balloons($selected = '', $htmlname = 'ballon', $showimmat = 0, $showDeclasse = 1, $group = false)
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
        $resql = $db->query("SELECT B.immat,B.rowid FROM llx_bbc_ballons as B WHERE is_disable = false  ORDER BY B.immat");
    } else {
        $resql = $db->query("SELECT B.immat,B.rowid FROM llx_bbc_ballons as B ORDER BY B.immat");
    }

    $lastValue = '';
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql);
                $currentValue = substr($obj->immat, 0,2);

                if ($obj) {
                    if(true === $group && $currentValue !== $lastValue){
                        print '<optgroup label="'.$currentValue.'">';
                    }

                    $lastValue = $currentValue;

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

                    if(true === $group && $currentValue !== $lastValue){
                        print '<optgroup label="'.$currentValue.'">';
                    }


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
    $sql .= " YEAR(VOL.date) = " . ($year ?: 'YEAR(NOW())');
    $sql .= " AND VOL.fk_type IN (" . bbcMissionFlightTypeIdsAsSqlList() . ") ";

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
 * Print the reimbursement of the pilots, quarter by quarter. Every amount is computed
 * with the allowances of the flight type of the missions.
 *
 * @param QuarterPilotMissionCollection $kmByQuartil
 */
function printBbcKilometersByQuartil($kmByQuartil)
{
    $quarters = [
        1 => 'Trimestre 1 (Jan - Mars)',
        2 => 'Trimestre 2 (Avr - Juin)',
        3 => 'Trimestre 3 (Juil - Sept)',
        4 => 'Trimestre 4 (Oct - Dec)',
    ];

    print '<table class="border" width="100%">';

    print '<tr>';
    print '<td></td>';
    print '<td></td>';

    foreach ($quarters as $quarterLabel) {
        print '<td class="liste_titre" colspan="5">' . $quarterLabel . '</td>';
    }

    print '<td class="liste_titre" >Total</td>';
    print '</tr>';

    print '<tr class="liste_titre">';
    print '<td class="liste_titre" > Nom </td>';
    print '<td class="liste_titre" > Prenom </td>';

    $missionTypesLabel = bbcMissionFlightTypesLabel();
    foreach (array_keys($quarters) as $quarter) {
        print '<td class="liste_titre" > # ' . $missionTypesLabel . '</td>';
        print '<td class="liste_titre" > Forfaits pil </td>';
        print '<td class="liste_titre" > Total des KM </td>';
        print '<td class="liste_titre" > Remb km €</td>';
        print '<td class="liste_titre" > Total € </td>';
    }

    print '<td class="liste_titre" > Total € </td>';
    print '</tr>';

    $totalPerQuarter = array_fill_keys(array_keys($quarters), 0);

    $curMonth = date("m", time());
    $curQuarter = ceil($curMonth / 3);
    $disableColor = 'style="background-color: lightyellow;" title="N/A" data-toggle="tooltip"';

    /** @var PilotMissions $pilotMission */
    foreach ($kmByQuartil as $pilotMission) {
        print '<tr>';

        print '<td>' . $pilotMission->getPilotLastname() . '</td>';
        print '<td>' . $pilotMission->getPilotFirstname() . '</td>';

        foreach (array_keys($quarters) as $quarter) {
            $amount = $pilotMission->getTotalAllowanceForQuarter($quarter);
            $totalPerQuarter[$quarter] += $amount;

            $cellAttributes = $curQuarter < $quarter ? $disableColor : '';

            print '<td ' . $cellAttributes . '>' . $pilotMission->getNumberOfFlightsForQuarter($quarter) . '</td>';
            print '<td ' . $cellAttributes . '>' . price($pilotMission->getFlightsAllowanceForQuarter($quarter)) . '€</td>';
            print '<td ' . $cellAttributes . '>' . $pilotMission->getTotalOfKilometersForQuarter($quarter) . '</td>';
            print '<td ' . $cellAttributes . '>' . price($pilotMission->getKilometersAllowanceForQuarter($quarter)) . '€</td>';
            print '<td ' . $cellAttributes . '><b>' . price($amount) . '€</b></td>';
        }

        print '<td><b>' . price($pilotMission->getTotalAllowance()) . '€</b></td>';

        print '</tr>';
    }

    print '<tr>';
    print '<td colspan="2" class="text-bold">Total</td>';

    foreach (array_keys($quarters) as $quarter) {
        print '<td colspan="4"></td>';
        print '<td class="text-bold">' . price($totalPerQuarter[$quarter]) . '€</td>';
    }

    print '<td class="text-bold">' . price(array_sum($totalPerQuarter)) . '€</td>';
    print '</tr>';

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
 * Data of a "by type and by year" graph : one serie per given flight type.
 *
 * @param BbctypesLine[]|null $flightTypes the types to draw, all of them by default
 *
 * @return GraphicalData
 */
function getGraphByTypeAndYearData($flightTypes = null)
{
    if (null === $flightTypes) {
        $flightTypes = filterBbcFlightTypesWithHistory(fetchAllBbcFlightTypes());
    }

    $flightYears = getFlightYears();
    sort($flightYears);

    $graphData = new GraphicalData();

    foreach ($flightYears as $flightYear) {
        $pieceData = new YearGraphicalData($flightYear);

        foreach ($flightTypes as $flightType) {
            $pieceData->addType(new GraphicalType($flightType->getId(), $flightType->getLabel()));
        }

        $graphData->addData($pieceData);
    }

    return fetchGraphByTypeAndYearData($graphData);
}

/**
 * Data of the graph of the missions of the club, one serie per mission type. The
 * retired types are kept as long as they carry flights.
 *
 * @return GraphicalData
 */
function getGraphMissionsByTypeAndYearData()
{
    return getGraphByTypeAndYearData(
        filterBbcMissionFlightTypes(filterBbcFlightTypesWithHistory(fetchAllBbcFlightTypes()))
    );
}

/**
 * Data of the graph of all the types that are not a mission for the club.
 *
 * @return GraphicalData
 */
function getGraphOtherFlightsByTypeAndYearData()
{
    return getGraphByTypeAndYearData(
        filterBbcNonMissionFlightTypes(filterBbcFlightTypesWithHistory(fetchAllBbcFlightTypes()))
    );
}