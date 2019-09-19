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
     * @param int $quarter
     * @param int $numberOfFlights
     * @param int $numberOfKilometers
     */
    public function addQuarter($quarter, $numberOfFlights, $numberOfKilometers){
        $quarter = (int)$quarter;
        $this->quarterMissions[$quarter] = new QuarterMission($quarter, $numberOfFlights, $numberOfKilometers);
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
     * Get the QuarterMission for a given quarter.
     *
     * @param int $quarter
     *
     * @return QuarterMission
     */
    private function getQuarterMission($quarter){
        if(!isset($this->quarterMissions[$quarter])){
            return new QuarterMission($quarter,0,0);
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