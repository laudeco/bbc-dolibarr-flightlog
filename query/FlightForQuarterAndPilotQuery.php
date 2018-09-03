<?php
/**
 *
 */

namespace flightlog\query;

use Webmozart\Assert\Assert;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightForQuarterAndPilotQuery
{

    /**
     * @var int
     */
    private $pilotId;

    /**
     * @var int
     */
    private $quarter;

    /**
     * @var int
     */
    private $year;

    /**
     * @param int $pilotId
     * @param int $quarter
     * @param int $year
     */
    public function __construct($pilotId, $quarter, $year)
    {
        Assert::integerish($pilotId);
        Assert::integerish($quarter);
        Assert::integerish($year);

        $this->pilotId = (int)$pilotId;
        $this->quarter = (int)$quarter;
        $this->year = (int)$year;
    }

    /**
     * @return int
     */
    public function getPilotId()
    {
        return $this->pilotId;
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
    public function getYear()
    {
        return $this->year;
    }
}