<?php

require_once(DOL_DOCUMENT_ROOT.'/flightlog/class/flight/FlightBonus.php');

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightCost
{

    /**
     * @var int
     */
    private $cost;

    /**
     * @param int $cost
     */
    public function __construct($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return FlightCost
     */
    public static function zero()
    {
        return new FlightCost(0);
    }

    /**
     * @param FlightCost $cost
     *
     * @return FlightCost
     */
    public function addCost(FlightCost $cost)
    {
        return new FlightCost($this->cost + $cost->getValue());
    }

    /**
     * @param int $factor
     *
     * @return FlightCost
     */
    public function multiply($factor)
    {
        return new FlightCost($factor * $this->cost);
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->cost;
    }

    /**
     * @param FlightBonus $bonus
     *
     * @return FlightCost
     */
    public function minBonus(FlightBonus $bonus)
    {
        return new FlightCost($this->cost - $bonus->getValue());
    }

}