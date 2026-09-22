<?php
/**
 *
 */

/**
 * PilotMissions class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class PilotMissions
{

    /**
     * @var int
     */
    private $pilotId;

    /**
     * @var string
     */
    private $pilotFirstname;

    /**
     * @var string
     */
    private $pilotLastname;

    /**
     * @var array|QuarterMission[]
     */
    private $quarterMissions;

    /**
     * @param int    $pilotId
     * @param string $pilotFirstname
     * @param string $pilotLastname
     */
    public function __construct($pilotId, $pilotFirstname, $pilotLastname)
    {
        $this->pilotId = (int)$pilotId;
        $this->pilotFirstname = $pilotFirstname;
        $this->pilotLastname = $pilotLastname;

        $this->quarterMissions = [];
    }


    /**
     * @param int         $quarter
     * @param TypeMission $typeMission
     */
    public function addTypeMission($quarter, TypeMission $typeMission){
        $quarter = (int)$quarter;

        if (!isset($this->quarterMissions[$quarter])) {
            $this->quarterMissions[$quarter] = new QuarterMission($quarter);
        }

        $this->quarterMissions[$quarter]->addTypeMission($typeMission);
    }

    /**
     * @return int
     */
    public function getPilotId()
    {
        return $this->pilotId;
    }

    /**
     * @return string
     */
    public function getPilotFirstname()
    {
        return $this->pilotFirstname;
    }

    /**
     * @return string
     */
    public function getPilotLastname()
    {
        return $this->pilotLastname;
    }

    /**
     * @param int $quarter
     *
     * @return int
     */
    public function getTotalOfKilometersForQuarter($quarter)
    {
        return $this->getQuarterMission($quarter)->getNumberOfKilometers();
    }

    /**
     * @param int $quarter
     *
     * @return int
     */
    public function getNumberOfFlightsForQuarter($quarter)
    {
        return $this->getQuarterMission($quarter)->getNumberOfFlights();
    }

    /**
     * Amount reimbursed for the kilometers of a quarter, at the rate of each flight type.
     *
     * @param int $quarter
     *
     * @return float
     */
    public function getKilometersAllowanceForQuarter($quarter)
    {
        return $this->getQuarterMission($quarter)->getKilometersAllowance();
    }

    /**
     * Amount reimbursed for the flights of a quarter, at the lump sum of each flight type.
     *
     * @param int $quarter
     *
     * @return float
     */
    public function getFlightsAllowanceForQuarter($quarter)
    {
        return $this->getQuarterMission($quarter)->getFlightsAllowance();
    }

    /**
     * @param int $quarter
     *
     * @return float
     */
    public function getTotalAllowanceForQuarter($quarter)
    {
        return $this->getQuarterMission($quarter)->getTotalAllowance();
    }

    /**
     * Amount reimbursed over every quarter.
     *
     * @return float
     */
    public function getTotalAllowance()
    {
        $allowance = 0;

        foreach ($this->quarterMissions as $quarterMission) {
            $allowance += $quarterMission->getTotalAllowance();
        }

        return $allowance;
    }

    /**
     * @return int
     */
    public function getTotalOfKilometers()
    {
        $numberOfKilometers = 0;

        foreach ($this->quarterMissions as $quarterMission) {
            $numberOfKilometers += $quarterMission->getNumberOfKilometers();
        }

        return $numberOfKilometers;
    }

    /**
     * @return int
     */
    public function getNumberOfFlights()
    {
        $numberOfFlights = 0;

        foreach ($this->quarterMissions as $quarterMission) {
            $numberOfFlights += $quarterMission->getNumberOfFlights();
        }

        return $numberOfFlights;
    }

    /**
     * Get the QuarterMission for a given quarter.
     *
     * @param int $quarter
     *
     * @return QuarterMission
     */
    private function getQuarterMission($quarter){
        if(!isset($this->quarterMissions[$quarter])){
            return new QuarterMission($quarter);
        }

        return $this->quarterMissions[$quarter];
    }

    /**
     * @return string
     */
    public function getPilotName()
    {
        return $this->pilotFirstname.' '.$this->pilotLastname;
    }
}