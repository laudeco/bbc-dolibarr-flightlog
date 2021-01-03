<?php

require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/flight/FlightPoints.php');

/**
 * A bonus won by a pilot
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightBonus
{
    /**
     * @var float
     */
    private $bonusAmount;

    /**
     * @param float $bonusAmount
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
     * @param FlightCost $cost
     *
     * @return FlightBonus
     */
    public function minCosts(FlightCost $cost)
    {
        $bonusAmount = $this->bonusAmount - $cost->getValue();
        if($bonusAmount < 0){
            return FlightBonus::zero();
        }

        return new FlightBonus($bonusAmount);
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->bonusAmount;
    }

}