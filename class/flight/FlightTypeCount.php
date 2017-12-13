<?php

require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/billing/FlightCost.php');

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightTypeCount
{

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $factor;

    /**
     * @param string $type
     * @param int    $count
     * @param int    $factor
     */
    public function __construct($type, $count = 0, $factor = 0)
    {
        $this->type = $type;
        $this->count = (int) $count;
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
     * @return int
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