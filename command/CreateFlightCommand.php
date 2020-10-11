<?php
/**
 *
 */

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateFlightCommand implements CommandInterface
{

    /**
     * @var DateTimeImmutable
     */
    private $date;
    private $lieuD;
    private $lieuA;
    /**
     * @var DateTimeImmutable
     */
    private $heureD;

    /**
     * @var DateTimeImmutable
     */
    private $heureA;
    private $BBC_ballons_idBBC_ballons;
    private $nbrPax;
    private $remarque;
    private $incidents;
    private $fk_type;
    private $fk_pilot;
    private $fk_organisateur;
    private $kilometers;
    private $cost;
    private $fk_receiver;
    private $justif_kilometers;
    private $passengerNames;
    private $groupedFlight;

    /**
     * @var array|int[]
     */
    private $orderIds;

    /**
     * @return DateTimeImmutable
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTimeImmutable $date
     *
     * @return CreateFlightCommand
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLieuD()
    {
        return $this->lieuD;
    }

    /**
     * @param mixed $lieuD
     *
     * @return CreateFlightCommand
     */
    public function setLieuD($lieuD)
    {
        $this->lieuD = $lieuD;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLieuA()
    {
        return $this->lieuA;
    }

    /**
     * @param mixed $lieuA
     *
     * @return CreateFlightCommand
     */
    public function setLieuA($lieuA)
    {
        $this->lieuA = $lieuA;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getHeureD()
    {
        return $this->heureD;
    }

    /**
     * @param DateTimeImmutable $heureD
     *
     * @return CreateFlightCommand
     */
    public function setHeureD($heureD)
    {
        $this->heureD = $heureD;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getHeureA()
    {
        return $this->heureA;
    }

    /**
     * @param DateTimeImmutable $heureA
     *
     * @return CreateFlightCommand
     */
    public function setHeureA($heureA)
    {
        $this->heureA = $heureA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBBCBallonsIdBBCBallons()
    {
        return $this->BBC_ballons_idBBC_ballons;
    }

    /**
     * @param mixed $BBC_ballons_idBBC_ballons
     *
     * @return CreateFlightCommand
     */
    public function setBBCBallonsIdBBCBallons($BBC_ballons_idBBC_ballons)
    {
        $this->BBC_ballons_idBBC_ballons = $BBC_ballons_idBBC_ballons;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNbrPax()
    {
        return $this->nbrPax;
    }

    /**
     * @param mixed $nbrPax
     *
     * @return CreateFlightCommand
     */
    public function setNbrPax($nbrPax)
    {
        $this->nbrPax = $nbrPax;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemarque()
    {
        return $this->remarque;
    }

    /**
     * @param mixed $remarque
     *
     * @return CreateFlightCommand
     */
    public function setRemarque($remarque)
    {
        $this->remarque = $remarque;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIncidents()
    {
        return $this->incidents;
    }

    /**
     * @param mixed $incidents
     *
     * @return CreateFlightCommand
     */
    public function setIncidents($incidents)
    {
        $this->incidents = $incidents;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFkType()
    {
        return $this->fk_type;
    }

    /**
     * @param mixed $fk_type
     *
     * @return CreateFlightCommand
     */
    public function setFkType($fk_type)
    {
        $this->fk_type = $fk_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFkPilot()
    {
        return $this->fk_pilot;
    }

    /**
     * @param mixed $fk_pilot
     *
     * @return CreateFlightCommand
     */
    public function setFkPilot($fk_pilot)
    {
        $this->fk_pilot = $fk_pilot;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFkOrganisateur()
    {
        return $this->fk_organisateur;
    }

    /**
     * @param mixed $fk_organisateur
     *
     * @return CreateFlightCommand
     */
    public function setFkOrganisateur($fk_organisateur)
    {
        $this->fk_organisateur = $fk_organisateur;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKilometers()
    {
        return $this->kilometers;
    }

    /**
     * @param mixed $kilometers
     *
     * @return CreateFlightCommand
     */
    public function setKilometers($kilometers)
    {
        $this->kilometers = $kilometers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param mixed $cost
     *
     * @return CreateFlightCommand
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFkReceiver()
    {
        return $this->fk_receiver;
    }

    /**
     * @param mixed $fk_receiver
     *
     * @return CreateFlightCommand
     */
    public function setFkReceiver($fk_receiver)
    {
        $this->fk_receiver = $fk_receiver;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJustifKilometers()
    {
        return $this->justif_kilometers;
    }

    /**
     * @param mixed $justif_kilometers
     *
     * @return CreateFlightCommand
     */
    public function setJustifKilometers($justif_kilometers)
    {
        $this->justif_kilometers = $justif_kilometers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassengerNames()
    {
        return $this->passengerNames;
    }

    /**
     * @param mixed $passengerNames
     *
     * @return CreateFlightCommand
     */
    public function setPassengerNames($passengerNames)
    {
        $this->passengerNames = $passengerNames;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isGroupedFlight()
    {
        return $this->groupedFlight;
    }

    /**
     * @param mixed $groupedFlight
     *
     * @return CreateFlightCommand
     */
    public function setGroupedFlight($groupedFlight)
    {
        $this->groupedFlight = $groupedFlight;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderIds()
    {
        return $this->orderIds;
    }

    /**
     * @param array|int[] $orderIds
     *
     * @return CreateFlightCommand
     */
    public function setOrderIds($orderIds)
    {
        $this->orderIds = $orderIds;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasOrderId(){
        return count($this->orderIds) > 0;
    }
}