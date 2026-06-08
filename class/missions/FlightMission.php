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
    private $fkType;

    /**
     * FlightMission constructor.
     *
     * @param int      $id
     * @param string   $startPoint
     * @param string   $endPoint
     * @param string   $kilometersComment
     * @param int      $numberOfKilometers
     * @param DateTime $date
     * @param int      $fkType
     */
    public function __construct($id, $startPoint, $endPoint, $kilometersComment, $numberOfKilometers, DateTime $date, $fkType = 0)
    {
        $this->id = (int)$id;
        $this->startPoint = $startPoint;
        $this->endPoint = $endPoint;
        $this->kilometersComment = $kilometersComment;
        $this->numberOfKilometers = $numberOfKilometers;
        $this->date = $date;
        $this->fkType = (int)$fkType;
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

    /**
     * @return int
     */
    public function getFkType()
    {
        return $this->fkType;
    }
}