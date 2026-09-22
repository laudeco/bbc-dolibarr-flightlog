<?php
/**
 *
 */

namespace flightlog\query;

use DoliDB;
use QuarterPilotMissionCollection;
use TypeMission;

/**
 * Returns pilots that have a mission in the year and quarter.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class GetPilotsWithMissionsQueryHandler
{

    /**
     * @var DoliDB
     */
    private $db;

    /**
     * @param DoliDB $db
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * @param GetPilotsWithMissionsQuery $query
     *
     * @return QuarterPilotMissionCollection
     */
    public function __invoke(GetPilotsWithMissionsQuery $query)
    {
        $sql = $this->generateSql($query);
        $resql = $this->db->query($sql);

        $flightTypes = \fetchBbcFlightTypesById();

        $result = new QuarterPilotMissionCollection();
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql); //vol
                    if ($obj) {

                        $pilotId = $obj->rowid;
                        $pilotLastname = $obj->lastname;
                        $pilotFirstname = $obj->firstname;

                        if($query->isPilotsOnly()){
                            $result->addPilot($pilotId, $pilotFirstname, $pilotLastname);
                        }else{
                            $flightTypeId = (int) $obj->flight_type;
                            $flightType = isset($flightTypes[$flightTypeId]) ? $flightTypes[$flightTypeId] : null;

                            $result->addMission($obj->quartil, $pilotId, $pilotFirstname, $pilotLastname,
                                new TypeMission(
                                    $flightTypeId,
                                    null === $flightType ? '' : $flightType->getLabel(),
                                    $obj->number_flights,
                                    $obj->total_kilometers,
                                    \bbcFlightTypeKmAllowance($flightType),
                                    \bbcFlightTypeMissionAllowance($flightType)
                                ));
                        }

                    }
                    $i++;
                }
            }
        }

        return $result;
    }

    /**
     * @param GetPilotsWithMissionsQuery $query
     *
     * @return string
     */
    private function generateSql(GetPilotsWithMissionsQuery $query)
    {
        $sql = "SELECT USR.rowid, USR.lastname, USR.firstname ";
        $sql .= " , VOL.fk_type as flight_type ";
        $sql .= " , SUM(VOL.kilometers) as total_kilometers ";
        $sql .= " , COUNT(VOL.idBBC_vols) as number_flights";

        if(!$query->isPilotsOnly()){
            $sql .= " , QUARTER(VOL.date) as quartil ";
        }

        $sql .= " FROM llx_bbc_vols as VOL";
        $sql .= " LEFT JOIN llx_user AS USR ON VOL.fk_pilot = USR.rowid";
        $sql .= " WHERE ";
        $sql .= " YEAR(VOL.date) = " . $query->getYear();
        $sql .= " AND VOL.fk_type IN (" . \bbcMissionFlightTypeIdsAsSqlList() . ") ";

        if ($query->hasQuarter()) {
            $sql .= " AND QUARTER(VOL.date) = " . $query->getQuarter();
        }

        // Grouped by type too : every type carries its own allowances.
        $sql .= " GROUP BY QUARTER(VOL.date), VOL.fk_pilot, VOL.fk_type";
        $sql .= ' HAVING total_kilometers > 0 OR number_flights > 0 ';
        $sql .= " ORDER BY QUARTER(VOL.date), VOL.fk_pilot";

        return $this->db->escape($sql);
    }
}