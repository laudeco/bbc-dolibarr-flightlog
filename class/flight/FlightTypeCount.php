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
     * Does this type give points to the pilot (mission for the club) ?
     *
     * @var boolean
     */
    private $mission;

    /**
     * Is this type charged to the pilot ?
     *
     * @var boolean
     */
    private $charged;

    /**
     * @param string  $type
     * @param float   $count
     * @param int     $factor
     * @param boolean $mission true when the type is a mission for the club (points)
     * @param boolean $charged true when the type is charged to the pilot (euro)
     */
    public function __construct($type, $count = 0, $factor = 0, $mission = false, $charged = false)
    {
        $this->type = $type;
        $this->count = $count;
        $this->factor = (int) $factor;
        $this->mission = (bool) $mission;
        $this->charged = (bool) $charged;
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
     * Does this type give points to the pilot ?
     *
     * @return boolean
     */
    public function isMission()
    {
        return $this->mission;
    }

    /**
     * Is this type charged to the pilot ?
     *
     * @return boolean
     */
    public function isCharged()
    {
        return $this->charged;
    }

    /**
     * @param FlightTypeCount $flightTypeCount
     *
     * @return FlightTypeCount
     */
    public function add(FlightTypeCount $flightTypeCount)
    {
        return new FlightTypeCount($this->type, $this->count + $flightTypeCount->getCount(), $this->factor,
            $this->mission, $this->charged);
    }

    /**
     * @return FlightCost
     */
    public function getCost()
    {
        return new FlightCost($this->count * $this->factor);
    }
}