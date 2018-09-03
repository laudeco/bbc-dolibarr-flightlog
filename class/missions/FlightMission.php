<?php
/**
 *
 */

namespace flightlog\model\missions;

use DateTime;
use Webmozart\Assert\Assert;

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
     * FlightMission constructor.
     *
     * @param int      $id
     * @param string   $startPoint
     * @param string   $endPoint
     * @param string   $kilometersComment
     * @param int      $numberOfKilometers
     * @param DateTime $date
     */
    public function __construct($id, $startPoint, $endPoint, $kilometersComment, $numberOfKilometers, DateTime $date)
    {
        Assert::integerish($id);
        Assert::stringNotEmpty($startPoint);
        Assert::stringNotEmpty($endPoint);
        Assert::string($kilometersComment);
        Assert::integerish($numberOfKilometers);

        $this->id = (int)$id;
        $this->startPoint = $startPoint;
        $this->endPoint = $endPoint;
        $this->kilometersComment = $kilometersComment;
        $this->numberOfKilometers = $numberOfKilometers;
        $this->date = $date;
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