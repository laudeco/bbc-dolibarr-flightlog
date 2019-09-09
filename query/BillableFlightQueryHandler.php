<?php

require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/flight/Pilot.php');
require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/flight/FlightTypeCount.php');
require_once(DOL_DOCUMENT_ROOT . '/flightlog/query/BillableFlightQuery.php');

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class BillableFlightQueryHandler
{

    /**
     * @var DoliDb $db
     */
    private $db;

    /**
     * @var stdClass
     */
    private $conf;

    /**
     * @param DoliDb   $db
     * @param stdClass $conf
     */
    public function __construct(DoliDb $db, stdClass $conf)
    {
        $this->db = $db;
        $this->conf = $conf;
    }

    /**
     * @param BillableFlightQuery $query
     *
     * @return array
     */
    public function __invoke(BillableFlightQuery $query)
    {
        $sql = "SELECT USR.lastname AS nom , USR.firstname AS prenom ,COUNT(`idBBC_vols`) AS nbr,fk_pilot as pilot, TT.numero as type,SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(heureA,heureD)))) AS time";
        $sql .= " FROM llx_bbc_vols, llx_user AS USR,llx_bbc_types AS TT ";
        $sql .= " WHERE `fk_pilot`= USR.rowid AND fk_type = TT.idType AND YEAR(llx_bbc_vols.date) = " . ($query->hasYear() ? "'" . $query->getFiscalYear() . "'" : 'YEAR(NOW())');
        $sql .= " GROUP BY fk_pilot,`fk_type`";

        $resql = $this->db->query($sql);
        $array = array();
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql); //vol
                    if ($obj) {
                        if (!isset($array[$obj->pilot])) {
                            $name = $obj->prenom . ' ' . $obj->nom;
                            $pilot = Pilot::create($name, $obj->pilot);
                            $array[$obj->pilot] = $pilot;
                        }

                        $array[$obj->pilot] = $array[$obj->pilot]->addCount(
                            new FlightTypeCount(
                                $obj->type,
                                $obj->nbr,
                                $this->getFactorByType($obj->type)
                            )
                        );
                    }
                    $i++;
                }
            }
        }

        if (!$query->isIncludeTotal()) {
            return $array;
        }

        //total orga
        $sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON llx_user.rowid = llx_bbc_vols.fk_organisateur WHERE YEAR(date) = \'' . $query->getFiscalYear() . '\' AND fk_type IN (1,2) GROUP BY fk_organisateur';
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql); //vol

                    if ($obj) {

                        if (!isset($array[$obj->rowid])) {
                            $name = $obj->firstname . ' ' . $obj->name;
                            $pilot = Pilot::create($name, $obj->rowid);
                            $array[$obj->rowid] = $pilot;
                        }

                        $array[$obj->rowid] = $array[$obj->rowid]->addCount(
                            new FlightTypeCount(
                                'orga',
                                $obj->total,
                                $this->getFactorByType('orga')
                            )
                        );
                    }
                    $i++;
                }
            }
        }

        //total orga T6 - instructeur
        $sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON rowid = fk_organisateur WHERE YEAR(date) = \'' . $query->getFiscalYear() . '\' AND fk_type = 6 GROUP BY fk_organisateur';
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql); //vol

                    if ($obj) {
                        if (!isset($array[$obj->rowid])) {
                            $name = $obj->firstname . ' ' . $obj->name;
                            $pilot = Pilot::create($name, $obj->rowid);
                            $array[$obj->rowid] = $pilot;
                        }

                        $array[$obj->rowid] = $array[$obj->rowid]->addCount(
                            new FlightTypeCount(
                                'orga_T6',
                                $obj->total,
                                $this->getFactorByType('orga_T6')
                            )
                        );
                    }
                    $i++;
                }
            }
        }

        return $array;
    }

    /**
     * Returns the number of points if set in the config, if not return the price of the service.
     *
     * @param string $type
     *
     * @return int
     */
    private function getFactorByType($type)
    {
        switch ($type) {
            case 'orga':
                return $this->conf->BBC_POINTS_BONUS_ORGANISATOR;
            case 'orga_T6':
                return $this->conf->BBC_POINTS_BONUS_INSTRUCTOR;
        }

        $constVariableName = 'BBC_POINTS_BONUS_' . $type;
        if (!isset($this->conf->$constVariableName) || empty($this->conf->$constVariableName) || $this->conf->$constVariableName < 0) {
            return $this->getFactorForService($type);
        }

        return (int) $this->conf->$constVariableName;

    }

    /**
     * @param string $type
     *
     * @return float
     */
    private function getFactorForService($type)
    {
        $service = new Bbctypes($this->db);
        $fetchResult = $service->fetch($type);

        if ($fetchResult <= 0) {
            throw new \InvalidArgumentException('Service not found');
        }

        return $service->getService()->price_ttc;
    }


}