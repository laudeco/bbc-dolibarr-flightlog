<?php
/**
 *
 */

namespace flightlog\query;

use flightlog\model\missions\FlightMission;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightForQuarterAndPilotQueryHandler
{


    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @param \DoliDB $db
     */
    public function __construct(\DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * @param FlightForQuarterAndPilotQuery $query
     *
     * @return array|FlightMission[]
     */
    public function __invoke(FlightForQuarterAndPilotQuery $query)
    {
        $sql = $this->generateQuery($query);

        /** @var FlightMission[]|array $flights */
        $flights = [];
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $flight = $this->db->fetch_object($resql);
                    if ($flight) {
                        $flights[] = new FlightMission($flight->rowid, $flight->lieuD, $flight->lieuA,
                            $flight->justif_kilometers, $flight->kilometers, new \DateTime($flight->date));
                    }
                    $i++;
                }
            }
        }


        return $flights;
    }

    /**
     * @param FlightForQuarterAndPilotQuery $query
     *
     * @return string
     */
    private function generateQuery(FlightForQuarterAndPilotQuery $query)
    {
        $sql = "SELECT USR.rowid, USR.lastname, USR.firstname, QUARTER(VOL.date) as quartil ";
        $sql .= " , VOL.*";

        $sql .= " FROM llx_bbc_vols as VOL";
        $sql .= " LEFT OUTER JOIN llx_user AS USR ON VOL.fk_pilot = USR.rowid";
        $sql .= " WHERE ";
        $sql .= " YEAR(VOL.date) = " . $query->getYear();
        $sql .= " AND ( VOL.fk_type = 1 OR VOL.fk_type = 2 ) ";
        $sql .= " AND USR.rowid = " . $query->getPilotId();
        $sql .= " AND QUARTER(VOL.date) = " . $query->getQuarter();
        $sql .= " ORDER BY QUARTER(VOL.date), VOL.fk_pilot";

        return $this->db->escape($sql);
    }

}