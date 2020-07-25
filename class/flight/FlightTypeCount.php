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

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
    public function getFactor()
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

    /**
     * @return FlightCost
     */
    public function getCost()
    {
        return new FlightCost($this->count * $this->factor);
    }
}