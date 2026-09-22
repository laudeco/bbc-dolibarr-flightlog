<?php

use FlightLog\Domain\Damage\FlightDamageCount;
use FlightLog\Domain\Damage\FlightInvoicedDamageCount;

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
     * @var \FlightLog\Application\Damage\Query\GetPilotDamagesQueryRepositoryInterface
     */
    private $pilotDamageQueryRepository;

    /**
     * All the flight types indexed by their number.
     *
     * @var array|BbctypesLine[]
     */
    private $flightTypesByNumero;

    /**
     * @param DoliDb   $db
     * @param stdClass $conf
     */
    public function __construct(DoliDb $db, stdClass $conf)
    {
        $this->db = $db;
        $this->conf = $conf;
        $this->pilotDamageQueryRepository = new \FlightLog\Infrastructure\Damage\Query\Repository\GetPilotDamagesQueryRepository($db);

        $this->flightTypesByNumero = [];
        foreach (fetchAllBbcFlightTypes() as $currentFlightType) {
            $this->flightTypesByNumero[(string) $currentFlightType->getNumero()] = $currentFlightType;
        }
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
        /** @var Pilot[] $pilots */
        $pilots = [];
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql); //vol
                    if (!$obj) {
                        continue;
                    }

                    if (!isset($pilots[$obj->pilot])) {
                        $pilots[$obj->pilot] = Pilot::create($obj->prenom . ' ' . $obj->nom, $obj->pilot);
                    }

                    $pilots[$obj->pilot] = $pilots[$obj->pilot]->addCount(
                        new FlightTypeCount(
                            $obj->type,
                            (int)$obj->nbr,
                            $this->getFactorByType($obj->type),
                            $this->isMission($obj->type),
                            $this->isPilotCharged($obj->type)
                        )
                    );

                    $i++;
                }
            }
        }

        if (!$query->isIncludeTotal()) {
            return $pilots;
        }

        //total orga : every flight type flagged as a mission for the club
        $sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON llx_user.rowid = llx_bbc_vols.fk_organisateur WHERE YEAR(date) = \'' . $query->getFiscalYear() . '\' AND fk_type IN (' . bbcMissionFlightTypeIdsAsSqlList() . ') GROUP BY fk_organisateur';
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql); //vol

                    if ($obj) {

                        if (!isset($pilots[$obj->rowid])) {
                            $name = $obj->firstname . ' ' . $obj->name;
                            $pilot = Pilot::create($name, $obj->rowid);
                            $pilots[$obj->rowid] = $pilot;
                        }

                        $pilots[$obj->rowid] = $pilots[$obj->rowid]->addCount(
                            new FlightTypeCount(
                                'orga',
                                (int)$obj->total,
                                $this->getFactorByType('orga'),
                                true,
                                false
                            )
                        );
                    }
                    $i++;
                }
            }
        }

        //total orga instruction - instructeur
        $sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON llx_user.rowid = fk_organisateur WHERE YEAR(date) = \'' . $query->getFiscalYear() . '\' AND fk_type IN (' . bbcInstructionFlightTypeIdsAsSqlList() . ') GROUP BY fk_organisateur';
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql); //vol

                    if ($obj) {
                        if (!isset($pilots[$obj->rowid])) {
                            $name = $obj->firstname . ' ' . $obj->name;
                            $pilot = Pilot::create($name, $obj->rowid);
                            $pilots[$obj->rowid] = $pilot;
                        }

                        $pilots[$obj->rowid] = $pilots[$obj->rowid]->addCount(
                            new FlightTypeCount(
                                'orga_T6',
                                (int)$obj->total,
                                $this->getFactorByType('orga_T6'),
                                true,
                                false
                            )
                        );
                    }
                    $i++;
                }
            }
        }

        //Total damages
        $damages = $this->pilotDamageQueryRepository->query($query->getFiscalYear());
        foreach($damages as $currentDamage){

            //Pilot doesn't exist
            if (!isset($pilots[$currentDamage->getAuthorId()])) {
                $pilots[$currentDamage->getAuthorId()] = Pilot::create($currentDamage->getAuthorName(), $currentDamage->getAuthorId());
            }

            // Add all damage
            $pilots[$currentDamage->getAuthorId()]->addDamage(
                new FlightDamageCount('',  $currentDamage->getAmount())
            );

            // The damage is already invoiced. So not take into account.
            if($currentDamage->isInvoiced()){
                $pilots[$currentDamage->getAuthorId()]->addInvoicedDamage(
                    new FlightInvoicedDamageCount('',  $currentDamage->getAmount())
                );
            }
        }

        return $pilots;
    }

    /**
     * Returns the number of points/the amount configured on the flight type. When
     * nothing is configured, falls back on the (legacy) constant and then on the
     * price of the service linked to the type.
     *
     * @param string $type
     *
     * @return int|float
     */
    private function getFactorByType($type)
    {
        switch ($type) {
            case 'orga':
                return $this->conf->BBC_POINTS_BONUS_ORGANISATOR;
            case 'orga_T6':
                return $this->conf->BBC_POINTS_BONUS_INSTRUCTOR;
        }

        $flightType = $this->getFlightType($type);
        if (null !== $flightType && null !== $flightType->getPoints() && $flightType->getPoints() >= 0) {
            return $flightType->getPoints();
        }

        $constVariableName = 'BBC_POINTS_BONUS_' . $type;
        if (isset($this->conf->$constVariableName) && !empty($this->conf->$constVariableName) && $this->conf->$constVariableName >= 0) {
            return (int) $this->conf->$constVariableName;
        }

        return $this->getFactorForService($type);
    }

    /**
     * @param string $type flight type number
     *
     * @return float
     */
    private function getFactorForService($type)
    {
        $flightType = $this->getFlightType($type);

        if (null === $flightType) {
            throw new \InvalidArgumentException(sprintf('Flight type %s not found', $type));
        }

        if (empty($flightType->getFkService())) {
            return 0;
        }

        $service = new Product($this->db);
        if ($service->fetch($flightType->getFkService()) <= 0) {
            throw new \InvalidArgumentException('Service not found');
        }

        return $service->price_ttc;
    }

    /**
     * Is this flight type a mission for the club ?
     *
     * @param string $type flight type number
     *
     * @return boolean
     */
    private function isMission($type)
    {
        $flightType = $this->getFlightType($type);

        return null !== $flightType && $flightType->isMission();
    }

    /**
     * Is this flight type charged to the pilot ?
     *
     * @param string $type flight type number
     *
     * @return boolean
     */
    private function isPilotCharged($type)
    {
        $flightType = $this->getFlightType($type);

        return null !== $flightType && $flightType->isPilotCharged();
    }

    /**
     * @param string $type flight type number
     *
     * @return BbctypesLine|null
     */
    private function getFlightType($type)
    {
        $key = (string) $type;

        return isset($this->flightTypesByNumero[$key]) ? $this->flightTypesByNumero[$key] : null;
    }


}