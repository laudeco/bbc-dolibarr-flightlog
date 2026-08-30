<?php
/**
 *
 */

/**
 * Number of points.
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
    public static function create(int $initialAmount)
    {
        return new FlightPoints($initialAmount);
    }

	public static function zero():self
	{
		return new FlightPoints(0);
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

	public function add(FlightPoints $points):self{
		return new FlightPoints($this->amount + $points->getValue());
	}

    public function getValue():int
    {
        return $this->amount;
    }

}
