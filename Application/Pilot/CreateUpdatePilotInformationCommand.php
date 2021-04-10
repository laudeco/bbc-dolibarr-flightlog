<?php


namespace FlightLog\Application\Pilot\Command;


use FlightLog\Domain\Pilot\Pilot;

final class CreateUpdatePilotInformationCommand
{

    /**
     * @var int
     */
    private $pilotId;

    /**
     * @var string
     */
    private $pilotLicenceNumber = '';

    /**
     * @var string
     */
    private $trainingPilotLicenceNumber = '';

    /**
     * @var string
     */
    private $lastTrainingFlightDate = '';

    /**
     * @var bool
     */
    private $isPilotClassA = false;

    /**
     * @var bool
     */
    private $isPilotClassB = false;
    /**
     * @var bool
     */
    private $isPilotClassC = false;
    /**
     * @var bool
     */
    private $isPilotClassD = false;
    /**
     * @var bool
     */
    private $isPilotGaz = false;
    /**
     * @var bool
     */
    private $isMedicalOwner = false;
    /**
     * @var string
     */
    private $endMedicalDate = '';
    /**
     * @var string
     */
    private $startMedicalDate = '';
    /**
     * @var bool
     */
    private $hasQualifStatic = false;
    /**
     * @var bool
     */
    private $hasQualifNight = false;
    /**
     * @var bool
     */
    private $hasQualifPro = false;
    /**
     * @var string
     */
    private $lastOpcDate = '';
    /**
     * @var string
     */
    private $lastProRefreshDate = '';
    /**
     * @var bool
     */
    private $hasQualifInstructor = false;
    /**
     * @var string
     */
    private $lastInstructorRefreshDate = '';
    /**
     * @var bool
     */
    private $hasQualifExaminator = false;
    /**
     * @var string
     */
    private $lastExaminatorRefreshDate = '';
    /**
     * @var bool
     */
    private $hasRadio = false;
    /**
     * @var string
     */
    private $radioLicenceNumber = '';
    /**
     * @var string
     */
    private $radioLicenceDate = '';
    /**
     * @var bool
     */
    private $hasTrainingFirstHelp = false;
    /**
     * @var string
     */
    private $lastTrainingFirstHelpDate = '';
    /**
     * @var string
     */
    private $certificationNumberTrainingFirstHelp = '';
    /**
     * @var bool
     */
    private $hasTrainingFire = false;
    /**
     * @var string
     */
    private $lastTrainingFireDate = '';
    /**
     * @var string
     */
    private $certificationNumberTrainingFire = '';

    public function __construct($pilotId)
    {
        $this->pilotId = $pilotId;
    }

    /**
     * @return mixed
     */
    public function getPilotId()
    {
        return $this->pilotId;
    }

    /**
     * @return string
     */
    public function getPilotLicenceNumber(): string
    {
        return $this->pilotLicenceNumber;
    }

    /**
     * @param string $pilotLicenceNumber
     * @return CreateUpdatePilotInformationCommand
     */
    public function setPilotLicenceNumber(string $pilotLicenceNumber): CreateUpdatePilotInformationCommand
    {
        $this->pilotLicenceNumber = $pilotLicenceNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrainingPilotLicenceNumber(): string
    {
        return $this->trainingPilotLicenceNumber;
    }

    /**
     * @param string $trainingPilotLicenceNumber
     * @return CreateUpdatePilotInformationCommand
     */
    public function setTrainingPilotLicenceNumber(string $trainingPilotLicenceNumber
    ): CreateUpdatePilotInformationCommand {
        $this->trainingPilotLicenceNumber = $trainingPilotLicenceNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastTrainingFlightDate(): string
    {
        return $this->lastTrainingFlightDate;
    }

    /**
     * @param string $lastTrainingFlightDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setLastTrainingFlightDate(string $lastTrainingFlightDate): CreateUpdatePilotInformationCommand
    {
        $this->lastTrainingFlightDate = $lastTrainingFlightDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPilotClassA(): bool
    {
        return $this->isPilotClassA;
    }

    /**
     * @return bool
     */
    public function getIsPilotClassA(): bool
    {
        return $this->isPilotClassA;
    }

    /**
     * @param bool $isPilotClassA
     * @return CreateUpdatePilotInformationCommand
     */
    public function setIsPilotClassA(bool $isPilotClassA): CreateUpdatePilotInformationCommand
    {
        $this->isPilotClassA = $isPilotClassA;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPilotClassB(): bool
    {
        return $this->isPilotClassB;
    }

    /**
     * @return bool
     */
    public function getIsPilotClassB(): bool
    {
        return $this->isPilotClassB;
    }

    /**
     * @param bool $isPilotClassB
     * @return CreateUpdatePilotInformationCommand
     */
    public function setIsPilotClassB(bool $isPilotClassB): CreateUpdatePilotInformationCommand
    {
        $this->isPilotClassB = $isPilotClassB;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPilotClassC(): bool
    {
        return $this->isPilotClassC;
    }

    /**
     * @return bool
     */
    public function getIsPilotClassC(): bool
    {
        return $this->isPilotClassC;
    }

    /**
     * @param bool $isPilotClassC
     * @return CreateUpdatePilotInformationCommand
     */
    public function setIsPilotClassC(bool $isPilotClassC): CreateUpdatePilotInformationCommand
    {
        $this->isPilotClassC = $isPilotClassC;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPilotClassD(): bool
    {
        return $this->isPilotClassD;
    }


    /**
     * @return bool
     */
    public function getIsPilotClassD(): bool
    {
        return $this->isPilotClassD;
    }

    /**
     * @param bool $isPilotClassD
     * @return CreateUpdatePilotInformationCommand
     */
    public function setIsPilotClassD(bool $isPilotClassD): CreateUpdatePilotInformationCommand
    {
        $this->isPilotClassD = $isPilotClassD;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPilotGaz(): bool
    {
        return $this->isPilotGaz;
    }

    /**
     * @return bool
     */
    public function getIsPilotGaz(): bool
    {
        return $this->isPilotGaz;
    }

    /**
     * @param bool $isPilotGaz
     * @return CreateUpdatePilotInformationCommand
     */
    public function setIsPilotGaz(bool $isPilotGaz): CreateUpdatePilotInformationCommand
    {
        $this->isPilotGaz = $isPilotGaz;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMedicalOwner(): bool
    {
        return $this->isMedicalOwner;
    }

    /**
     * @param bool $isMedicalOwner
     * @return CreateUpdatePilotInformationCommand
     */
    public function setIsMedicalOwner(bool $isMedicalOwner): CreateUpdatePilotInformationCommand
    {
        $this->isMedicalOwner = $isMedicalOwner;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndMedicalDate(): string
    {
        return $this->endMedicalDate;
    }

    /**
     * @param string $endMedicalDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setEndMedicalDate(string $endMedicalDate): CreateUpdatePilotInformationCommand
    {
        $this->endMedicalDate = $endMedicalDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartMedicalDate(): string
    {
        return $this->startMedicalDate;
    }

    /**
     * @param string $startMedicalDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setStartMedicalDate(string $startMedicalDate): CreateUpdatePilotInformationCommand
    {
        $this->startMedicalDate = $startMedicalDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHasQualifStatic(): bool
    {
        return $this->hasQualifStatic;
    }

    /**
     * @return bool
     */
    public function getHasQualifStatic(): bool
    {
        return $this->hasQualifStatic;
    }

    /**
     * @param bool $hasQualifStatic
     * @return CreateUpdatePilotInformationCommand
     */
    public function setHasQualifStatic(bool $hasQualifStatic): CreateUpdatePilotInformationCommand
    {
        $this->hasQualifStatic = $hasQualifStatic;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHasQualifNight(): bool
    {
        return $this->hasQualifNight;
    }

    /**
     * @return bool
     */
    public function getHasQualifNight(): bool
    {
        return $this->hasQualifNight;
    }

    /**
     * @param bool $hasQualifNight
     * @return CreateUpdatePilotInformationCommand
     */
    public function setHasQualifNight(bool $hasQualifNight): CreateUpdatePilotInformationCommand
    {
        $this->hasQualifNight = $hasQualifNight;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHasQualifPro(): bool
    {
        return $this->hasQualifPro;
    }

    /**
     * @return bool
     */
    public function getHasQualifPro(): bool
    {
        return $this->hasQualifPro;
    }

    /**
     * @param bool $hasQualifPro
     * @return CreateUpdatePilotInformationCommand
     */
    public function setHasQualifPro(bool $hasQualifPro): CreateUpdatePilotInformationCommand
    {
        $this->hasQualifPro = $hasQualifPro;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastOpcDate(): string
    {
        return $this->lastOpcDate;
    }

    /**
     * @param string $lastOpcDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setLastOpcDate(string $lastOpcDate): CreateUpdatePilotInformationCommand
    {
        $this->lastOpcDate = $lastOpcDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastProRefreshDate(): string
    {
        return $this->lastProRefreshDate;
    }

    /**
     * @param string $lastProRefreshDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setLastProRefreshDate(string $lastProRefreshDate): CreateUpdatePilotInformationCommand
    {
        $this->lastProRefreshDate = $lastProRefreshDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHasQualifInstructor(): bool
    {
        return $this->hasQualifInstructor;
    }

    /**
     * @return bool
     */
    public function getHasQualifInstructor(): bool
    {
        return $this->hasQualifInstructor;
    }

    /**
     * @param bool $hasQualifInstructor
     * @return CreateUpdatePilotInformationCommand
     */
    public function setHasQualifInstructor(bool $hasQualifInstructor): CreateUpdatePilotInformationCommand
    {
        $this->hasQualifInstructor = $hasQualifInstructor;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastInstructorRefreshDate(): string
    {
        return $this->lastInstructorRefreshDate;
    }

    /**
     * @param string $lastInstructorRefreshDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setLastInstructorRefreshDate(string $lastInstructorRefreshDate): CreateUpdatePilotInformationCommand
    {
        $this->lastInstructorRefreshDate = $lastInstructorRefreshDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHasQualifExaminator(): bool
    {
        return $this->hasQualifExaminator;
    }

    /**
     * @return bool
     */
    public function getHasQualifExaminator(): bool
    {
        return $this->hasQualifExaminator;
    }

    /**
     * @param bool $hasQualifExaminator
     * @return CreateUpdatePilotInformationCommand
     */
    public function setHasQualifExaminator(bool $hasQualifExaminator): CreateUpdatePilotInformationCommand
    {
        $this->hasQualifExaminator = $hasQualifExaminator;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastExaminatorRefreshDate(): string
    {
        return $this->lastExaminatorRefreshDate;
    }

    /**
     * @param string $lastExaminatorRefreshDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setLastExaminatorRefreshDate(string $lastExaminatorRefreshDate): CreateUpdatePilotInformationCommand
    {
        $this->lastExaminatorRefreshDate = $lastExaminatorRefreshDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHasRadio(): bool
    {
        return $this->hasRadio;
    }

    /**
     * @return bool
     */
    public function getHasRadio(): bool
    {
        return $this->hasRadio;
    }

    /**
     * @param bool $hasRadio
     * @return CreateUpdatePilotInformationCommand
     */
    public function setHasRadio(bool $hasRadio): CreateUpdatePilotInformationCommand
    {
        $this->hasRadio = $hasRadio;
        return $this;
    }

    /**
     * @return string
     */
    public function getRadioLicenceNumber(): string
    {
        return $this->radioLicenceNumber;
    }

    /**
     * @param string $radioLicenceNumber
     * @return CreateUpdatePilotInformationCommand
     */
    public function setRadioLicenceNumber(string $radioLicenceNumber): CreateUpdatePilotInformationCommand
    {
        $this->radioLicenceNumber = $radioLicenceNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getRadioLicenceDate(): string
    {
        return $this->radioLicenceDate;
    }

    /**
     * @param string $radioLicenceDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setRadioLicenceDate(string $radioLicenceDate): CreateUpdatePilotInformationCommand
    {
        $this->radioLicenceDate = $radioLicenceDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHasTrainingFirstHelp(): bool
    {
        return $this->hasTrainingFirstHelp;
    }

    /**
     * @return bool
     */
    public function getHasTrainingFirstHelp(): bool
    {
        return $this->hasTrainingFirstHelp;
    }

    /**
     * @param bool $hasTrainingFirstHelp
     * @return CreateUpdatePilotInformationCommand
     */
    public function setHasTrainingFirstHelp(bool $hasTrainingFirstHelp): CreateUpdatePilotInformationCommand
    {
        $this->hasTrainingFirstHelp = $hasTrainingFirstHelp;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastTrainingFirstHelpDate(): string
    {
        return $this->lastTrainingFirstHelpDate;
    }

    /**
     * @param string $lastTrainingFirstHelpDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setLastTrainingFirstHelpDate(string $lastTrainingFirstHelpDate): CreateUpdatePilotInformationCommand
    {
        $this->lastTrainingFirstHelpDate = $lastTrainingFirstHelpDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCertificationNumberTrainingFirstHelp(): string
    {
        return $this->certificationNumberTrainingFirstHelp;
    }

    /**
     * @param string $certificationNumberTrainingFirstHelp
     * @return CreateUpdatePilotInformationCommand
     */
    public function setCertificationNumberTrainingFirstHelp(string $certificationNumberTrainingFirstHelp
    ): CreateUpdatePilotInformationCommand {
        $this->certificationNumberTrainingFirstHelp = $certificationNumberTrainingFirstHelp;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHasTrainingFire(): bool
    {
        return $this->hasTrainingFire;
    }

    /**
     * @return bool
     */
    public function getHasTrainingFire(): bool
    {
        return $this->hasTrainingFire;
    }

    /**
     * @param bool $hasTrainingFire
     * @return CreateUpdatePilotInformationCommand
     */
    public function setHasTrainingFire(bool $hasTrainingFire): CreateUpdatePilotInformationCommand
    {
        $this->hasTrainingFire = $hasTrainingFire;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastTrainingFireDate(): string
    {
        return $this->lastTrainingFireDate;
    }

    /**
     * @param string $lastTrainingFireDate
     * @return CreateUpdatePilotInformationCommand
     */
    public function setLastTrainingFireDate(string $lastTrainingFireDate): CreateUpdatePilotInformationCommand
    {
        $this->lastTrainingFireDate = $lastTrainingFireDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCertificationNumberTrainingFire(): string
    {
        return $this->certificationNumberTrainingFire;
    }

    /**
     * @param string $certificationNumberTrainingFire
     * @return CreateUpdatePilotInformationCommand
     */
    public function setCertificationNumberTrainingFire(string $certificationNumberTrainingFire
    ): CreateUpdatePilotInformationCommand {
        $this->certificationNumberTrainingFire = $certificationNumberTrainingFire;
        return $this;
    }

    public function fromPilot(Pilot $pilot)
    {
        $state = $pilot->state();

        if (null !== $state['pilot_licence_number']) {
            $this->pilotLicenceNumber = $state['pilot_licence_number'];
        }
        if (null !== $state['training_pilot_licence_number']) {
            $this->trainingPilotLicenceNumber = $state['training_pilot_licence_number'];
        }
        if (null !== $state['last_training_flight_date']) {
            $this->lastTrainingFlightDate = $state['last_training_flight_date'];
        }
        if (null !== $state['is_pilot_class_a']) {
            $this->isPilotClassA = $state['is_pilot_class_a'];
        }
        if (null !== $state['is_pilot_class_b']) {
            $this->isPilotClassB = $state['is_pilot_class_b'];
        }
        if (null !== $state['is_pilot_class_c']) {
            $this->isPilotClassC = $state['is_pilot_class_c'];
        }
        if (null !== $state['is_pilot_class_d']) {
            $this->isPilotClassD = $state['is_pilot_class_d'];
        }
        if (null !== $state['is_pilot_gaz']) {
            $this->isPilotGaz = $state['is_pilot_gaz'];
        }
        if (null !== $state['is_medical_owner']) {
            $this->isMedicalOwner = $state['is_medical_owner'];
        }
        if (null !== $state['end_medical_date']) {
            $this->endMedicalDate = $state['end_medical_date'];
        }
        if (null !== $state['start_medical_date']) {
            $this->startMedicalDate = $state['start_medical_date'];
        }
        if (null !== $state['has_qualif_static']) {
            $this->hasQualifStatic = $state['has_qualif_static'];
        }
        if (null !== $state['has_qualif_night']) {
            $this->hasQualifNight = $state['has_qualif_night'];
        }
        if (null !== $state['has_qualif_pro']) {
            $this->hasQualifPro = $state['has_qualif_pro'];
        }
        if (null !== $state['last_opc_date']) {
            $this->lastOpcDate = $state['last_opc_date'];
        }
        if (null !== $state['last_pro_refresh_date']) {
            $this->lastProRefreshDate = $state['last_pro_refresh_date'];
        }
        if (null !== $state['has_qualif_instructor']) {
            $this->hasQualifInstructor = $state['has_qualif_instructor'];
        }
        if (null !== $state['last_instructor_refresh_date']) {
            $this->lastInstructorRefreshDate = $state['last_instructor_refresh_date'];
        }
        if (null !== $state['has_qualif_examinator']) {
            $this->hasQualifExaminator = $state['has_qualif_examinator'];
        }
        if (null !== $state['last_examinator_refresh_date']) {
            $this->lastExaminatorRefreshDate = $state['last_examinator_refresh_date'];
        }
        if (null !== $state['has_radio']) {
            $this->hasRadio = $state['has_radio'];
        }
        if (null !== $state['radio_licence_number']) {
            $this->radioLicenceNumber = $state['radio_licence_number'];
        }
        if (null !== $state['radio_licence_date']) {
            $this->radioLicenceDate = $state['radio_licence_date'];
        }
        if (null !== $state['has_training_first_help']) {
            $this->hasTrainingFirstHelp = $state['has_training_first_help'];
        }
        if (null !== $state['last_training_first_help_date']) {
            $this->lastTrainingFirstHelpDate = $state['last_training_first_help_date'];
        }
        if (null !== $state['certification_number_training_first_help']) {
            $this->certificationNumberTrainingFirstHelp = $state['certification_number_training_first_help'];
        }
        if (null !== $state['has_training_fire']) {
            $this->hasTrainingFire = $state['has_training_fire'];
        }
        if (null !== $state['last_training_fire_date']) {
            $this->lastTrainingFireDate = $state['last_training_fire_date'];
        }
        if (null !== $state['certification_number_training_fire']) {
            $this->certificationNumberTrainingFire = $state['certification_number_training_fire'];
        }
    }


}