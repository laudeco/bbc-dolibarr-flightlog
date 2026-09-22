<?php
/**
 *
 */

namespace flightlog\model\missions;

use DateTime;

/**
 * Mission of type flight.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightMission
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $startPoint;

    /**
     * @var string
     */
    private $endPoint;

    /**
     * @var string
     */
    private $kilometersComment;

    /**
     * @var int
     */
    private $numberOfKilometers;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var int
     */
    private $flightTypeId;

    /**
     * @var float amount reimbursed per kilometer for this type of flight
     */
    private $kmAllowance;

    /**
     * @var float lump sum reimbursed for this type of flight
     */
    private $missionAllowance;

    /**
     * FlightMission constructor.
     *
     * @param int      $id
     * @param string   $startPoint
     * @param string   $endPoint
     * @param string   $kilometersComment
     * @param int      $numberOfKilometers
     * @param DateTime $date
     * @param int      $flightTypeId
     * @param float    $kmAllowance
     * @param float    $missionAllowance
     */
    public function __construct(
        $id,
        $startPoint,
        $endPoint,
        $kilometersComment,
        $numberOfKilometers,
        DateTime $date,
        $flightTypeId = 0,
        $kmAllowance = 0,
        $missionAllowance = 0
    ) {
        $this->id = (int)$id;
        $this->startPoint = $startPoint;
        $this->endPoint = $endPoint;
        $this->kilometersComment = $kilometersComment;
        $this->numberOfKilometers = $numberOfKilometers;
        $this->date = $date;
        $this->flightTypeId = (int)$flightTypeId;
        $this->kmAllowance = (float)$kmAllowance;
        $this->missionAllowance = (float)$missionAllowance;
    }

    /**
     * @return int
     */
    public function getFlightTypeId()
    {
        return $this->flightTypeId;
    }

    /**
     * Amount reimbursed per kilometer for this type of flight.
     *
     * @return float
     */
    public function getKmAllowance()
    {
        return $this->kmAllowance;
    }

    /**
     * Lump sum reimbursed for this type of flight.
     *
     * @return float
     */
    public function getMissionAllowance()
    {
        return $this->missionAllowance;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStartPoint()
    {
        return $this->startPoint;
    }

    /**
     * @return string
     */
    public function getEndPoint()
    {
        return $this->endPoint;
    }

    /**
     * @return string
     */
    public function getKilometersComment()
    {
        return $this->kilometersComment;
    }

    /**
     * @return int
     */
    public function getNumberOfKilometers()
    {
        return $this->numberOfKilometers;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}