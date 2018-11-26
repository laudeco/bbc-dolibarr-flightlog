<?php
/**
 *
 */

use Webmozart\Assert\Assert;

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
     * @param int $quarter
     * @param int $pilotId
     * @param string $pilotFirstname
     * @param string $pilotLastname
     * @param int $numberOfFlights
     * @param int $numberOfKilometers
     */
    public function addMission(
        $quarter,
        $pilotId,
        $pilotFirstname,
        $pilotLastname,
        $numberOfFlights,
        $numberOfKilometers
    )
    {
        Assert::integerish($pilotId);
        $pilotId = (int)$pilotId;

        if (!isset($this->items[$pilotId])) {
            $this->items[$pilotId] = new PilotMissions($pilotId, $pilotFirstname, $pilotLastname);
        }

        $this->items[$pilotId]->addQuarter($quarter, $numberOfFlights, $numberOfKilometers);
    }

    /**
     * @param int $pilotId
     * @param string $pilotFirstname
     * @param string $pilotLastname
     */
    public function addPilot($pilotId, $pilotFirstname, $pilotLastname)
    {
        Assert::integerish($pilotId);
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