<?php

require_once(DOL_DOCUMENT_ROOT.'/flightlog/class/flight/FlightPoints.php');

/**
 * A bonus won by a pilot
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightBonus
{
    /**
     * @var int
     */
    private $bonusAmount;

    /**
     * @param int $bonusAmount
     */
    private function __construct($bonusAmount)
    {
        $this->bonusAmount = $bonusAmount;
    }

    /**
     * @return FlightBonus
     */
    public static function zero()
    {
        return new FlightBonus(0);
    }

    /**
     * @param FlightPoints $points
     *
     * @return FlightBonus
     */
    public function addPoints(FlightPoints $points)
    {
        return new FlightBonus($this->bonusAmount + $points->getValue());
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->bonusAmount;
    }

}