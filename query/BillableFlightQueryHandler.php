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
     * @param DoliDb   $db
     * @param stdClass $conf
     */
    public function __construct(DoliDb $db, stdClass $conf)
    {
        $this->db = $db;
        $this->conf = $conf;
        $this->pilotDamageQueryRepository = new \FlightLog\Infrastructure\Damage\Query\Repository\GetPilotDamagesQueryRepository($db);
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
                            $this->getFactorByType($obj->type)
                        )
                    );

                    $i++;
                }
            }
        }

        if (!$query->isIncludeTotal()) {
            return $pilots;
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

                        if (!isset($pilots[$obj->rowid])) {
                            $name = $obj->firstname . ' ' . $obj->name;
                            $pilot = Pilot::create($name, $obj->rowid);
                            $pilots[$obj->rowid] = $pilot;
                        }

                        $pilots[$obj->rowid] = $pilots[$obj->rowid]->addCount(
                            new FlightTypeCount(
                                'orga',
                                (int)$obj->total,
                                $this->getFactorByType('orga')
                            )
                        );
                    }
                    $i++;
                }
            }
        }

        //total orga T6 - instructeur
        $sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON llx_user.rowid = fk_organisateur WHERE YEAR(date) = \'' . $query->getFiscalYear() . '\' AND fk_type = 6 GROUP BY fk_organisateur';
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
                                $this->getFactorByType('orga_T6')
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
     * Returns the financial factor (points or cost) for a flight type.
     * For bonus types (T1, T2): returns points_pilote, falling back to BBC_POINTS_BONUS_X.
     * For cost types (T3, T4, T6, T7): returns cout_pilote, falling back to service price_ttc.
     * For role types (orga, orga_T6): returns global constants.
     *
     * @param string $type
     *
     * @return float
     */
    private function getFactorByType($type)
    {
        switch ($type) {
            case 'orga':
                return (float)$this->conf->BBC_POINTS_BONUS_ORGANISATOR;
            case 'orga_T6':
                return (float)$this->conf->BBC_POINTS_BONUS_INSTRUCTOR;
        }

        $bbcType = new Bbctypes($this->db);
        $fetchResult = $bbcType->fetch((int)$type);

        if ($fetchResult <= 0) {
            return 0;
        }

        // Bonus types: T1, T2 → use points_pilote
        if (in_array((int)$type, [1, 2])) {
            if ($bbcType->points_pilote > 0) {
                return (float)$bbcType->points_pilote;
            }

            // Backward-compatibility: fall back to global constant
            $constVariableName = 'BBC_POINTS_BONUS_' . $type;
            if (!empty($this->conf->$constVariableName) && $this->conf->$constVariableName > 0) {
                return (float)$this->conf->$constVariableName;
            }

            return 0;
        }

        // Cost types: T3, T4, T6, T7 → use cout_pilote
        if ($bbcType->cout_pilote > 0) {
            return (float)$bbcType->cout_pilote;
        }

        // Backward-compatibility: fall back to service price
        if ($bbcType->fkService) {
            return (float)$bbcType->getService()->price_ttc;
        }

        return 0;
    }


}