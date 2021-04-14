<?php


namespace FlightLog\Application\Pilot\ViewModel;


use FlightLog\Application\Common\ViewModel\ViewModel;

final class Pilot extends ViewModel
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $email;

    /**
     * @var \DateTimeImmutable|null
     */
    private $medicalEndDate;

    /**
     * @var \DateTimeImmutable|null
     */
    private $lastTrainingFlightDate;

    /**
     * @var bool|null
     */
    private $isPilotClassA;

    /**
     * @var bool|null
     */
    private $isPilotClassB;

    /**
     * @var bool|null
     */
    private $isPilotClassC;

    /**
     * @var bool|null
     */
    private $isPilotClassD;

    /**
     * @var bool|null
     */
    private $isPilotGaz;

    /**
     * @var bool|null
     */
    private $hasQualifStatic;
    /**
     * @var bool|null
     */
    private $hasQualifNight;
    /**
     * @var bool|null
     */
    private $hasQualifPro;
    /**
     * @var \DateTimeImmutable|null
     */
    private $lastOpcDate;
    /**
     * @var bool|null
     */
    private $hasTrainingFirstHelp;
    /**
     * @var \DateTimeImmutable|null
     */
    private $lastTrainingFirstHelpDate;
    /**
     * @var bool|null
     */
    private $hasTrainingFire;
    /**
     * @var \DateTimeImmutable|null
     */
    private $lastTrainingFireDate;
    /**
     * @var \DateTimeImmutable|null
     */
    private $lastInstructorTrainingDate;
    /**
     * @var bool|null
     */
    private $isPilotTraining;

    /**
     * @var string|null
     */
    private $licenceNumber;


    /**
     * @var array|PilotFlight[]
     */
    private $flights = [];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function isPilotClassA(): bool
    {
        return null !== $this->isPilotClassA && $this->isPilotClassA;
    }

    /**
     * @return bool
     */
    public function isPilotClassB(): bool
    {
        return null !== $this->isPilotClassB && $this->isPilotClassB;
    }

    /**
     * @return bool
     */
    public function isPilotClassC(): bool
    {
        return null !== $this->isPilotClassC && $this->isPilotClassC;
    }

    /**
     * @return bool
     */
    public function isPilotClassD(): bool
    {
        return null !== $this->isPilotClassD && $this->isPilotClassD;
    }

    /**
     * @return bool
     */
    public function isPilotGaz(): bool
    {
        return null !== $this->isPilotGaz && $this->isPilotGaz;
    }

    private function hasQualifPro(): bool
    {
        return null !== $this->hasQualifPro && $this->hasQualifPro;
    }

    private function isTrainingFirstHelp(): bool
    {
        return null !== $this->hasTrainingFirstHelp && $this->hasTrainingFirstHelp;
    }

    private function isTrainingFire(): bool
    {
        return null !== $this->hasTrainingFire && $this->hasTrainingFire;
    }

    /**
     * @param int $id
     * @return Pilot
     */
    public function setId(int $id): Pilot
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return Pilot
     */
    public function setName(string $name): Pilot
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $firstname
     * @return Pilot
     */
    public function setFirstname(string $firstname): Pilot
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @param string $email
     * @return Pilot
     */
    public function setEmail(string $email): Pilot
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param \DateTimeImmutable|null $medicalEndDate
     * @return Pilot
     */
    public function setMedicalEndDate(?\DateTimeImmutable $medicalEndDate): Pilot
    {
        $this->medicalEndDate = $medicalEndDate;
        return $this;
    }

    /**
     * @param \DateTimeImmutable|null $lastTrainingFlightDate
     * @return Pilot
     */
    public function setLastTrainingFlightDate(?\DateTimeImmutable $lastTrainingFlightDate): Pilot
    {
        $this->lastTrainingFlightDate = $lastTrainingFlightDate;
        return $this;
    }

    /**
     * @param bool|null $isPilotClassA
     * @return Pilot
     */
    public function setIsPilotClassA(?bool $isPilotClassA): Pilot
    {
        $this->isPilotClassA = $isPilotClassA;
        return $this;
    }

    /**
     * @param bool|null $isPilotClassB
     * @return Pilot
     */
    public function setIsPilotClassB(?bool $isPilotClassB): Pilot
    {
        $this->isPilotClassB = $isPilotClassB;
        return $this;
    }

    /**
     * @param bool|null $isPilotClassC
     * @return Pilot
     */
    public function setIsPilotClassC(?bool $isPilotClassC): Pilot
    {
        $this->isPilotClassC = $isPilotClassC;
        return $this;
    }

    /**
     * @param bool|null $isPilotClassD
     * @return Pilot
     */
    public function setIsPilotClassD(?bool $isPilotClassD): Pilot
    {
        $this->isPilotClassD = $isPilotClassD;
        return $this;
    }

    /**
     * @param bool|null $isPilotGaz
     * @return Pilot
     */
    public function setIsPilotGaz(?bool $isPilotGaz): Pilot
    {
        $this->isPilotGaz = $isPilotGaz;
        return $this;
    }

    /**
     * @param bool|null $hasQualifStatic
     * @return Pilot
     */
    public function setHasQualifStatic(?bool $hasQualifStatic): Pilot
    {
        $this->hasQualifStatic = $hasQualifStatic;
        return $this;
    }

    /**
     * @param bool|null $hasQualifNight
     * @return Pilot
     */
    public function setHasQualifNight(?bool $hasQualifNight): Pilot
    {
        $this->hasQualifNight = $hasQualifNight;
        return $this;
    }

    /**
     * @param bool|null $hasQualifPro
     * @return Pilot
     */
    public function setHasQualifPro(?bool $hasQualifPro): Pilot
    {
        $this->hasQualifPro = $hasQualifPro;
        return $this;
    }

    /**
     * @param \DateTimeImmutable|null $lastOpcDate
     * @return Pilot
     */
    public function setLastOpcDate(?\DateTimeImmutable $lastOpcDate): Pilot
    {
        $this->lastOpcDate = $lastOpcDate;
        return $this;
    }

    /**
     * @param bool|null $hasTrainingFirstHelp
     * @return Pilot
     */
    public function setHasTrainingFirstHelp(?bool $hasTrainingFirstHelp): Pilot
    {
        $this->hasTrainingFirstHelp = $hasTrainingFirstHelp;
        return $this;
    }

    /**
     * @param \DateTimeImmutable|null $lastTrainingFirstHelpDate
     * @return Pilot
     */
    public function setLastTrainingFirstHelpDate(?\DateTimeImmutable $lastTrainingFirstHelpDate): Pilot
    {
        $this->lastTrainingFirstHelpDate = $lastTrainingFirstHelpDate;
        return $this;
    }

    /**
     * @param bool|null $hasTrainingFire
     * @return Pilot
     */
    public function setHasTrainingFire(?bool $hasTrainingFire): Pilot
    {
        $this->hasTrainingFire = $hasTrainingFire;
        return $this;
    }

    /**
     * @param \DateTimeImmutable|null $lastTrainingFireDate
     * @return Pilot
     */
    public function setLastTrainingFireDate(?\DateTimeImmutable $lastTrainingFireDate): Pilot
    {
        $this->lastTrainingFireDate = $lastTrainingFireDate;
        return $this;
    }

    /**
     * @param \DateTimeImmutable|null $lastInstructorTrainingDate
     * @return Pilot
     */
    public function setLastInstructorTrainingDate(?\DateTimeImmutable $lastInstructorTrainingDate): Pilot
    {
        $this->lastInstructorTrainingDate = $lastInstructorTrainingDate;
        return $this;
    }

    /**
     * @param bool|null $isPilotTraining
     * @return Pilot
     */
    public function setIsPilotTraining(?bool $isPilotTraining): Pilot
    {
        $this->isPilotTraining = $isPilotTraining;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLicenceNumber(): ?string
    {
        return $this->licenceNumber;
    }

    /**
     * @param string|null $licenceNumber
     * @return Pilot
     */
    public function setLicenceNumber(?string $licenceNumber): Pilot
    {
        $this->licenceNumber = $licenceNumber;
        return $this;
    }

    public function addFlight(PilotFlight $flight)
    {
        $this->flights[] = $flight;
    }

    public function getIconId(): string
    {
        if ($this->isDanger()) {
            return 'statut8';
        }

        return 'statut4';
    }

    public function isDanger()
    {
        return !$this->isMedicalValid()
            || !$this->isTrainingFlightValid()
            || !$this->isHoursAndTakeOffValidGroupA()
            || !$this->isProValid()
            || !$this->isProDateValid()
            || !$this->isTrainingFireValid()
            || !$this->isTrainingFirstHelpValid();

    }

    public function getReasons()
    {
        $reasons = '';

        $ok = '<span class="text-success text-italic">OK</span>';

        if($this->isPilot()){
            $reasons .= '<br> <span class="text-bold">Médical : </span> ' . ($this->isMedicalValid() ? $ok : 'L\'échéance est atteinte ou dépassée.');
            $reasons .= '<br> <span class="text-bold">Training flight : </span> ' . ($this->isTrainingFlightValid() ? $ok : 'Pas de vol avec un FI dans les 48 derniers mois.');
            $reasons .= '<br> <span class="text-bold">Exp. récente Gr. A: </span> ' . ($this->isHoursAndTakeOffValidGroupA() ? $ok : 'Pas 6H & 10TO dans les 24 derniers mois');

            if ($this->hasQualifPro()) {
                $reasons .= '<br><span class="text-bold">OPC / Refresh: </span> ' . ($this->isProDateValid() ? $ok : 'Pas de OPC dans les 24 derniers mois');
                $reasons .= '<br><span class="text-bold">Exp. récente Commercial: </span> ' . ($this->isProValid() ? $ok : 'Pas 3 vols dans les 6 derniers mois');
            }
        }


        if($this->isPilot() || $this->isTrainingFire()){
            $reasons .= '<br><span class="text-bold">Feu: </span> ' . ($this->isTrainingFireValid() ? $ok : 'Date expirée');
        }

        if($this->isPilot() || $this->isTrainingFirstHelp()){
            $reasons .= '<br><span class="text-bold">Premiers secours: </span> ' . ($this->isTrainingFirstHelpValid() ? $ok : 'Date expirée');
        }

        return '<u>Details:</u>' . $reasons;
    }

    private function isMedicalValid(): bool
    {
        if (!$this->isPilot()) {
            return true;
        }

        if(null === $this->medicalEndDate){
            return false;
        }

        return $this->medicalEndDate > new \DateTimeImmutable();
    }


    private function isTrainingFlightValid(): bool
    {
        if(!$this->isPilot()){
            return true;
        }

        if(null === $this->lastTrainingFlightDate){
            return false;
        }

        return $this->diffDateInMonths($this->lastTrainingFlightDate) < 48;
    }

    private function isHoursAndTakeOffValidGroupA(): bool
    {
        if(!$this->isPilot()){
            return true;
        }

        if (!$this->isPilotClassA()) {
            return true;
        }

        $totalHour = 0;
        $totalLanding = 0;
        $totalTakeOff = 0;

        foreach ($this->flights as $currentFlight) {
            if ($this->diffDateInMonths($currentFlight->getDate()) > 24) {
                continue;
            }

            $totalHour += $currentFlight->getDuration() / 60;
            $totalLanding++;
            $totalTakeOff++;
        }

        return $totalHour >= 6 && ($totalTakeOff >= 10 || $totalLanding >= 10);
    }

    private function isProDateValid(): bool
    {
        if(!$this->isPilot()){
            return true;
        }

        if (null === $this->lastOpcDate) {
            return !$this->hasQualifPro();
        }

        return $this->diffDateInMonths($this->lastOpcDate) < 48;

    }

    private function isProValid(): bool
    {
        if(!$this->isPilot()){
            return true;
        }

        if (!$this->hasQualifPro()) {
            return true;
        }

        $flight = $this->getAntepenultimateFlight();

        return $this->diffDateInMonths($flight->getDate()) < 6;
    }

    private function getAntepenultimateFlight(): ?PilotFlight
    {
        $flights = array_reverse($this->flights);
        if (count($flights) < 3) {
            return null;
        }

        return $flights[2];
    }

    private function diffDateInMonths(\DateTimeImmutable $dateA, \DateTimeImmutable $dateB = null): int{
        if(null === $dateB){
            $dateB = new \DateTimeImmutable();
        }

        $diff = $dateB->diff($dateA);
        return $diff->m + $diff->y * 12;
    }

    private function isTrainingFireValid()
    {
        if(!$this->isTrainingFire() && !$this->isPilot()){
            return true;
        }

        if(null === $this->lastTrainingFireDate){
            return false;
        }

        return $this->diffDateInMonths($this->lastTrainingFireDate) < 36;
    }

    private function isTrainingFirstHelpValid()
    {
        if(!$this->isTrainingFirstHelp() && !$this->isPilot() ){
            return true;
        }

        if(null === $this->lastTrainingFirstHelpDate){
            return false;
        }

        return $this->diffDateInMonths($this->lastTrainingFirstHelpDate) < 36;
    }

    private function isPilot(): bool
    {
        return $this->isPilotClassA() || $this->isPilotClassB() || $this->isPilotClassC() || $this->isPilotClassD();
    }
}