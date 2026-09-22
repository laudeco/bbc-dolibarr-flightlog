<?php
/**
 *
 */

/**
 * QuarterPilotMissionCollection class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class QuarterPilotMissionCollection implements IteratorAggregate
{

    /**
     * @var array|QuarterMission[]
     */
    private $items;

    /**
     *
     */
    public function __construct()
    {
        $this->items = [];
    }

    /**
     * @param int         $quarter
     * @param int         $pilotId
     * @param string      $pilotFirstname
     * @param string      $pilotLastname
     * @param TypeMission $typeMission missions of one flight type for this quarter
     */
    public function addMission(
        $quarter,
        $pilotId,
        $pilotFirstname,
        $pilotLastname,
        TypeMission $typeMission
    )
    {
        $pilotId = (int)$pilotId;

        if (!isset($this->items[$pilotId])) {
            $this->items[$pilotId] = new PilotMissions($pilotId, $pilotFirstname, $pilotLastname);
        }

        $this->items[$pilotId]->addTypeMission($quarter, $typeMission);
    }

    /**
     * @param int $pilotId
     * @param string $pilotFirstname
     * @param string $pilotLastname
     */
    public function addPilot($pilotId, $pilotFirstname, $pilotLastname)
    {
        $pilotId = (int)$pilotId;

        if (isset($this->items[$pilotId])) {
            return;
        }

        $this->items[$pilotId] = new PilotMissions($pilotId, $pilotFirstname, $pilotLastname);
    }


    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return boolean
     */
    public function hasMission()
    {
        return !empty($this->items);
    }
}