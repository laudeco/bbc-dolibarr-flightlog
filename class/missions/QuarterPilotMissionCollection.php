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
     * @param $quarter
     * @param $pilotId
     * @param $pilotFirstname
     * @param $pilotLastname
     * @param $numberOfFlights
     * @param $numberOfKilometers
     */
    public function addMission(
        $quarter,
        $pilotId,
        $pilotFirstname,
        $pilotLastname,
        $numberOfFlights,
        $numberOfKilometers
    ) {
        Assert::integerish($pilotId);
        $pilotId = (int) $pilotId;

        if (!isset($this->items[$pilotId])) {
            $this->items[$pilotId] = new PilotMissions($pilotId, $pilotFirstname, $pilotLastname);
        }

        $this->items[$pilotId]->addQuarter($quarter, $numberOfFlights, $numberOfKilometers);
    }


    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}