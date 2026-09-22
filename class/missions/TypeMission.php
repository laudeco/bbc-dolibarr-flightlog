<?php
/**
 *
 */

/**
 * Missions of one flight type, for one pilot and one quarter.
 *
 * The allowances are the ones configured on the flight type, the module wide values
 * having already been applied as a default.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class TypeMission
{

    /**
     * @var int
     */
    private $flightTypeId;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $numberOfFlights;

    /**
     * @var int
     */
    private $numberOfKilometers;

    /**
     * @var float amount reimbursed per kilometer
     */
    private $kmAllowance;

    /**
     * @var float lump sum reimbursed per flight
     */
    private $missionAllowance;

    /**
     * @param int    $flightTypeId
     * @param string $label
     * @param int    $numberOfFlights
     * @param int    $numberOfKilometers
     * @param float  $kmAllowance
     * @param float  $missionAllowance
     */
    public function __construct(
        $flightTypeId,
        $label,
        $numberOfFlights,
        $numberOfKilometers,
        $kmAllowance,
        $missionAllowance
    ) {
        $this->flightTypeId = (int) $flightTypeId;
        $this->label = $label;
        $this->numberOfFlights = (int) $numberOfFlights;
        $this->numberOfKilometers = (int) $numberOfKilometers;
        $this->kmAllowance = (float) $kmAllowance;
        $this->missionAllowance = (float) $missionAllowance;
    }

    /**
     * @return int
     */
    public function getFlightTypeId()
    {
        return $this->flightTypeId;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getNumberOfFlights()
    {
        return $this->numberOfFlights;
    }

    /**
     * @return int
     */
    public function getNumberOfKilometers()
    {
        return $this->numberOfKilometers;
    }

    /**
     * @return float
     */
    public function getKmAllowance()
    {
        return $this->kmAllowance;
    }

    /**
     * @return float
     */
    public function getMissionAllowance()
    {
        return $this->missionAllowance;
    }

    /**
     * Amount reimbursed for the kilometers of this type.
     *
     * @return float
     */
    public function getKilometersAllowance()
    {
        return $this->numberOfKilometers * $this->kmAllowance;
    }

    /**
     * Amount reimbursed for the flights of this type.
     *
     * @return float
     */
    public function getFlightsAllowance()
    {
        return $this->numberOfFlights * $this->missionAllowance;
    }

    /**
     * @return float
     */
    public function getTotalAllowance()
    {
        return $this->getKilometersAllowance() + $this->getFlightsAllowance();
    }

    /**
     * @param TypeMission $typeMission missions of the same flight type
     *
     * @return TypeMission
     */
    public function add(TypeMission $typeMission)
    {
        return new TypeMission(
            $this->flightTypeId,
            $this->label,
            $this->numberOfFlights + $typeMission->getNumberOfFlights(),
            $this->numberOfKilometers + $typeMission->getNumberOfKilometers(),
            $this->kmAllowance,
            $this->missionAllowance
        );
    }
}
