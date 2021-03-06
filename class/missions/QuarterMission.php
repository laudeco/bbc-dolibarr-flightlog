<?php
/**
 *
 */

/**
 * QuarterMission class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class QuarterMission
{

    /**
     * @var int
     */
    private $quarter;

    /**
     * @var int
     */
    private $numberOfFlights;

    /**
     * @var int
     */
    private $numberOfKilometers;

    /**
     * QuarterMission constructor.
     *
     * @param int $quarter
     * @param int $numberOfFlights
     * @param int $numberOfKilometers
     */
    public function __construct($quarter, $numberOfFlights, $numberOfKilometers)
    {
        $this->quarter = (int) $quarter;
        $this->numberOfFlights = (int) $numberOfFlights;
        $this->numberOfKilometers = (int) $numberOfKilometers;
    }

    /**
     * @return int
     */
    public function getQuarter()
    {
        return $this->quarter;
    }

    /**
     * @return int
     */
    public function getNumberOfFlights()
    {
        return $this->numberOfFlights;
    }

    /**
     * @return int
     */
    public function getNumberOfKilometers()
    {
        return $this->numberOfKilometers;
    }
}