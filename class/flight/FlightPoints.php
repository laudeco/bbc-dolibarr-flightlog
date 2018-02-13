<?php
/**
 *
 */

/**
 * Number of points
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightPoints
{

    /**
     * @var int
     */
    private $amount;

    /**
     * @param int $amount
     */
    private function __construct($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @param int $initialAmount
     *
     * @return FlightPoints
     */
    public static function create($initialAmount)
    {
        return new FlightPoints($initialAmount);
    }

    /**
     * @param int $factor
     *
     * @return FlightPoints
     */
    public function multiply($factor)
    {
        return new FlightPoints($this->amount * $factor);
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->amount;
    }

}