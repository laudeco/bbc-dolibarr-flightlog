<?php

require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/billing/FlightCost.php');

/**
 * Counter for one flight type.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightTypeCount
{

    /**
     * @var string
     */
    private $type;

    /**
     * @var float
     */
    private $count;

    /**
     * @var int
     */
    private $factor;

    /**
     * @param string $type
     * @param float    $count
     * @param int    $factor
     */
    public function __construct($type, $count = 0, $factor = 0)
    {
        $this->type = $type;
        $this->count = $count;
        $this->factor = (int) $factor;
    }

	public static function create(string $type):self{
		return new self($type);
	}

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

	public function isType(string $type):bool{
		return $this->type === $type;
	}

    /**
     * @return float
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function getFactor():int
    {
        return $this->factor;
    }

    /**
     * @param FlightTypeCount $flightTypeCount
     *
     * @return FlightTypeCount
     */
    public function add(FlightTypeCount $flightTypeCount)
    {
        return new FlightTypeCount($this->type, $this->count + $flightTypeCount->getCount(), $this->factor);
    }

    public function getCost(): FlightCost
    {
        return new FlightCost($this->count * $this->factor);
    }

	public function equals(FlightTypeCount $flightTypeCount):bool
	{
		return $this->type == $flightTypeCount->getType() && $this->factor === $flightTypeCount->getFactor();
	}

	public function copy():self
	{
		return new FlightTypeCount($this->type, $this->count, $this->factor);
	}
}
