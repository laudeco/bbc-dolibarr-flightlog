<?php
/**
 *
 */

/**
 * QuarterMission class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class QuarterMission
{

    /**
     * @var int
     */
    private $quarter;

    /**
     * @var array|TypeMission[] missions of the quarter, indexed by flight type
     */
    private $typeMissions;

    /**
     * QuarterMission constructor.
     *
     * @param int                  $quarter
     * @param array|TypeMission[]  $typeMissions
     */
    public function __construct($quarter, array $typeMissions = [])
    {
        $this->quarter = (int) $quarter;
        $this->typeMissions = [];

        foreach ($typeMissions as $typeMission) {
            $this->addTypeMission($typeMission);
        }
    }

    /**
     * @param TypeMission $typeMission
     */
    public function addTypeMission(TypeMission $typeMission)
    {
        $flightTypeId = $typeMission->getFlightTypeId();

        if (isset($this->typeMissions[$flightTypeId])) {
            $this->typeMissions[$flightTypeId] = $this->typeMissions[$flightTypeId]->add($typeMission);

            return;
        }

        $this->typeMissions[$flightTypeId] = $typeMission;
    }

    /**
     * @return int
     */
    public function getQuarter()
    {
        return $this->quarter;
    }

    /**
     * @return array|TypeMission[]
     */
    public function getTypeMissions()
    {
        return array_values($this->typeMissions);
    }

    /**
     * @return int
     */
    public function getNumberOfFlights()
    {
        $numberOfFlights = 0;

        foreach ($this->typeMissions as $typeMission) {
            $numberOfFlights += $typeMission->getNumberOfFlights();
        }

        return $numberOfFlights;
    }

    /**
     * @return int
     */
    public function getNumberOfKilometers()
    {
        $numberOfKilometers = 0;

        foreach ($this->typeMissions as $typeMission) {
            $numberOfKilometers += $typeMission->getNumberOfKilometers();
        }

        return $numberOfKilometers;
    }

    /**
     * Amount reimbursed for the kilometers, at the rate of each flight type.
     *
     * @return float
     */
    public function getKilometersAllowance()
    {
        $allowance = 0;

        foreach ($this->typeMissions as $typeMission) {
            $allowance += $typeMission->getKilometersAllowance();
        }

        return $allowance;
    }

    /**
     * Amount reimbursed for the flights, at the lump sum of each flight type.
     *
     * @return float
     */
    public function getFlightsAllowance()
    {
        $allowance = 0;

        foreach ($this->typeMissions as $typeMission) {
            $allowance += $typeMission->getFlightsAllowance();
        }

        return $allowance;
    }

    /**
     * @return float
     */
    public function getTotalAllowance()
    {
        return $this->getKilometersAllowance() + $this->getFlightsAllowance();
    }
}
