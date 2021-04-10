<?php


namespace FlightLog\Domain\Pilot;


use FlightLog\Domain\Pilot\ValueObject\EndDate;
use FlightLog\Domain\Pilot\ValueObject\FireCertificationNumber;
use FlightLog\Domain\Pilot\ValueObject\FirstHelpCertificationNumber;
use FlightLog\Domain\Pilot\ValueObject\IsOwner;
use FlightLog\Domain\Pilot\ValueObject\LastTrainingDate;
use FlightLog\Domain\Pilot\ValueObject\PilotId;
use FlightLog\Domain\Pilot\ValueObject\PilotLicenceNumber;
use FlightLog\Domain\Pilot\ValueObject\PilotTrainingLicenceNumber;
use FlightLog\Domain\Pilot\ValueObject\RadioLicenceDate;
use FlightLog\Domain\Pilot\ValueObject\RadioLicenceNumber;
use FlightLog\Domain\Pilot\ValueObject\StartDate;

final class Pilot
{

    /**
     * @var PilotId
     */
    private $pilotId;
    /**
     * @var PilotLicenceNumber
     */
    private $pilotLicenceNumber;
    /**
     * @var PilotTrainingLicenceNumber
     */
    private $trainingPilotLicenceNumber;
    /**
     * @var LastTrainingDate
     */
    private $lastTrainingFlightDate;
    /**
     * @var IsOwner
     */
    private $isPilotClassA;
    /**
     * @var IsOwner
     */
    private $isPilotClassB;
    /**
     * @var IsOwner
     */
    private $isPilotClassC;
    /**
     * @var IsOwner
     */
    private $isPilotClassD;
    /**
     * @var IsOwner
     */
    private $isPilotCaz;
    /**
     * @var IsOwner
     */
    private $isMedicalOwner;
    /**
     * @var EndDate
     */
    private $endMedicalDate;

    /**
     * @var StartDate
     */
    private $startMedicalDate;
    /**
     * @var IsOwner
     */
    private $hasQualifStatic;
    /**
     * @var IsOwner
     */
    private $hasQualifNight;
    /**
     * @var IsOwner
     */
    private $hasQualifPro;
    /**
     * @var LastTrainingDate
     */
    private $lastOpcDate;
    /**
     * @var LastTrainingDate
     */
    private $lastProRefreshDate;
    /**
     * @var IsOwner
     */
    private $hasQualifInstructor;
    /**
     * @var LastTrainingDate
     */
    private $lastInstructorRefreshDate;
    /**
     * @var IsOwner
     */
    private $hasQualifExaminator;
    /**
     * @var LastTrainingDate
     */
    private $lastExaminatorRefreshDate;
    /**
     * @var IsOwner
     */
    private $hasRadio;
    /**
     * @var RadioLicenceNumber
     */
    private $radioLicenceNumber;
    /**
     * @var RadioLicenceDate
     */
    private $radioLicenceDate;
    /**
     * @var IsOwner
     */
    private $hasTrainingFirstHelp;
    /**
     * @var LastTrainingDate
     */
    private $lastTrainingFirstHelpDate;
    /**
     * @var FirstHelpCertificationNumber
     */
    private $certificationNumberTrainingFirstHelp;
    /**
     * @var IsOwner
     */
    private $hasTrainingFire;
    /**
     * @var LastTrainingDate
     */
    private $lastTrainingFireDate;
    /**
     * @var FireCertificationNumber
     */
    private $certificationNumberTrainingFire;

    private function __construct(
        PilotId $pilotId,
        PilotLicenceNumber $pilotLicenceNumber,
        PilotTrainingLicenceNumber $trainingPilotLicenceNumber,
        LastTrainingDate $lastTrainingFlightDate,
        IsOwner $isPilotClassA,
        IsOwner $isPilotClassB,
        IsOwner $isPilotClassC,
        IsOwner $isPilotClassD,
        IsOwner $isPilotCaz,
        IsOwner $isMedicalOwner,
        EndDate $endMedicalDate,
        StartDate $startMedicalDate,
        IsOwner $hasQualifStatic,
        IsOwner $hasQualifNight,
        IsOwner $hasQualifPro,
        LastTrainingDate $lastOpcDate,
        LastTrainingDate $lastProRefreshDate,
        IsOwner $hasQualifInstructor,
        LastTrainingDate $lastInstructorRefreshDate,
        IsOwner $hasQualifExaminator,
        LastTrainingDate $lastExaminatorRefreshDate,
        IsOwner $hasRadio,
        RadioLicenceNumber $radioLicenceNumber,
        RadioLicenceDate $radioLicenceDate,
        IsOwner $hasTrainingFirstHelp,
        LastTrainingDate $lastTrainingFirstHelpDate,
        FirstHelpCertificationNumber $certificationNumberTrainingFirstHelp,
        IsOwner $hasTrainingFire,
        LastTrainingDate $lastTrainingFireDate,
        FireCertificationNumber $certificationNumberTrainingFire
    ) {
        $this->pilotId = $pilotId;
        $this->pilotLicenceNumber = $pilotLicenceNumber;
        $this->trainingPilotLicenceNumber = $trainingPilotLicenceNumber;
        $this->lastTrainingFlightDate = $lastTrainingFlightDate;
        $this->isPilotClassA = $isPilotClassA;
        $this->isPilotClassB = $isPilotClassB;
        $this->isPilotClassC = $isPilotClassC;
        $this->isPilotClassD = $isPilotClassD;
        $this->isPilotCaz = $isPilotCaz;
        $this->isMedicalOwner = $isMedicalOwner;
        $this->endMedicalDate = $endMedicalDate;
        $this->startMedicalDate = $startMedicalDate;
        $this->hasQualifStatic = $hasQualifStatic;
        $this->hasQualifNight = $hasQualifNight;
        $this->hasQualifPro = $hasQualifPro;
        $this->lastOpcDate = $lastOpcDate;
        $this->lastProRefreshDate = $lastProRefreshDate;
        $this->hasQualifInstructor = $hasQualifInstructor;
        $this->lastInstructorRefreshDate = $lastInstructorRefreshDate;
        $this->hasQualifExaminator = $hasQualifExaminator;
        $this->lastExaminatorRefreshDate = $lastExaminatorRefreshDate;
        $this->hasRadio = $hasRadio;
        $this->radioLicenceNumber = $radioLicenceNumber;
        $this->radioLicenceDate = $radioLicenceDate;
        $this->hasTrainingFirstHelp = $hasTrainingFirstHelp;
        $this->lastTrainingFirstHelpDate = $lastTrainingFirstHelpDate;
        $this->certificationNumberTrainingFirstHelp = $certificationNumberTrainingFirstHelp;
        $this->hasTrainingFire = $hasTrainingFire;
        $this->lastTrainingFireDate = $lastTrainingFireDate;
        $this->certificationNumberTrainingFire = $certificationNumberTrainingFire;
    }

    public static function create(PilotId $id)
    {
        return new self(
            $id,
            PilotLicenceNumber::empty(),
            PilotTrainingLicenceNumber::empty(),
            LastTrainingDate::zero(),
            IsOwner::create(),
            IsOwner::create(),
            IsOwner::create(),
            IsOwner::create(),
            IsOwner::create(),
            IsOwner::create(),
            EndDate::zero(),
            StartDate::zero(),
            IsOwner::create(),
            IsOwner::create(),
            IsOwner::create(),
            LastTrainingDate::zero(),
            LastTrainingDate::zero(),
            IsOwner::create(),
            LastTrainingDate::zero(),
            IsOwner::create(),
            LastTrainingDate::zero(),
            IsOwner::create(),
            RadioLicenceNumber::empty(),
            RadioLicenceDate::zero(),
            IsOwner::create(),
            LastTrainingDate::zero(),
            FirstHelpCertificationNumber::empty(),
            IsOwner::create(),
            LastTrainingDate::zero(),
            FireCertificationNumber::empty()
        );
    }

    public static function fromState(array $state): self
    {
        return new self(
            PilotId::create($state['user_id']),
            PilotLicenceNumber::create($state['pilot_licence_number']),
            PilotTrainingLicenceNumber::create($state['training_pilot_licence_number']),
            LastTrainingDate::fromString($state['last_training_flight_date']),
            IsOwner::fromValue($state['is_pilot_class_a']),
            IsOwner::fromValue($state['is_pilot_class_b']),
            IsOwner::fromValue($state['is_pilot_class_c']),
            IsOwner::fromValue($state['is_pilot_class_d']),
            IsOwner::fromValue($state['is_pilot_gaz']),
            IsOwner::fromValue($state['is_medical_owner']),
            EndDate::fromString($state['end_medical_date']),
            StartDate::fromString($state['start_medical_date']),
            IsOwner::fromValue($state['has_qualif_static']),
            IsOwner::fromValue($state['has_qualif_night']),
            IsOwner::fromValue($state['has_qualif_pro']),
            LastTrainingDate::fromString($state['last_opc_date']),
            LastTrainingDate::fromString($state['last_pro_refresh_date']),
            IsOwner::fromValue($state['has_qualif_instructor']),
            LastTrainingDate::fromString($state['last_instructor_refresh_date']),
            IsOwner::fromValue($state['has_qualif_examinator']),
            LastTrainingDate::fromString($state['last_examinator_refresh_date']),
            IsOwner::fromValue($state['has_radio']),
            RadioLicenceNumber::create($state['radio_licence_number']),
            RadioLicenceDate::fromString($state['radio_licence_date']),
            IsOwner::fromValue($state['has_training_first_help']),
            LastTrainingDate::fromString($state['last_training_first_help_date']),
            FirstHelpCertificationNumber::create($state['certification_number_training_first_help']),
            IsOwner::fromValue($state['has_training_fire']),
            LastTrainingDate::fromString($state['last_training_fire_date']),
            FireCertificationNumber::create($state['certification_number_training_fire'])
        );
    }

    public function state(): array
    {
        return [
            'user_id' => $this->pilotId->getId(),
            'pilot_licence_number' => $this->pilotLicenceNumber->getLicence(),
            'training_pilot_licence_number' => $this->trainingPilotLicenceNumber->getLicence(),
            'last_training_flight_date' => $this->lastTrainingFlightDate->asString(),
            'is_pilot_class_a' => $this->isPilotClassA->is(),
            'is_pilot_class_b' => $this->isPilotClassB->is(),
            'is_pilot_class_c' => $this->isPilotClassC->is(),
            'is_pilot_class_d' => $this->isPilotClassD->is(),
            'is_pilot_gaz' => $this->isPilotCaz->is(),
            'is_medical_owner' => $this->isMedicalOwner->is(),
            'end_medical_date' => $this->endMedicalDate->asString(),
            'start_medical_date' => $this->startMedicalDate->asString(),
            'medical_validity_duration' => 0,
            'has_qualif_static' => $this->hasQualifStatic->is(),
            'has_qualif_night' => $this->hasQualifNight->is(),
            'has_qualif_pro' => $this->hasQualifPro->is(),
            'last_opc_date' => $this->lastOpcDate->asString(),
            'last_pro_refresh_date' => $this->lastProRefreshDate->asString(),
            'has_qualif_instructor' => $this->hasQualifInstructor->is(),
            'last_instructor_refresh_date' => $this->lastInstructorRefreshDate->asString(),
            'has_qualif_examinator' => $this->hasQualifExaminator->is(),
            'last_examinator_refresh_date' => $this->lastExaminatorRefreshDate->asString(),
            'has_radio' => $this->hasRadio->is(),
            'radio_licence_number' => $this->radioLicenceNumber->getLicence(),
            'radio_licence_date' => $this->radioLicenceDate->asString(),
            'has_training_first_help' => $this->hasTrainingFirstHelp->is(),
            'last_training_first_help_date' => $this->lastTrainingFirstHelpDate->asString(),
            'certification_number_training_first_help' => $this->certificationNumberTrainingFirstHelp->getLicence(),
            'has_training_fire' => $this->hasTrainingFire->is(),
            'last_training_fire_date' => $this->lastTrainingFireDate->asString(),
            'certification_number_training_fire' => $this->certificationNumberTrainingFire->getLicence(),
        ];
    }

    public function id(): PilotId
    {
        return $this->pilotId;
    }

    public function medical()
    {
        $this->isMedicalOwner = IsOwner::yes();
    }

    public function removeMedical()
    {
        $this->isMedicalOwner = IsOwner::no();
    }

    public function hasClassA()
    {
        $this->isPilotClassA = IsOwner::yes();
    }

    public function removeClassA()
    {
        $this->isPilotClassA = IsOwner::no();
    }

    public function hasClassB()
    {
        $this->isPilotClassB = IsOwner::yes();
    }

    public function removeClassB()
    {
        $this->isPilotClassB = IsOwner::no();
    }

    public function hasClassC()
    {
        $this->isPilotClassC = IsOwner::yes();
    }

    public function removeClassC()
    {
        $this->isPilotClassC = IsOwner::no();
    }

    public function hasClassD()
    {
        $this->isPilotClassD = IsOwner::yes();
    }

    public function removeClassD()
    {
        $this->isPilotClassD = IsOwner::no();
    }

    public function gazPilot()
    {
        $this->isPilotCaz = IsOwner::yes();
    }

    public function removeGazPilot()
    {
        $this->isPilotCaz = IsOwner::no();
    }

    public function staticQualif()
    {
        $this->hasQualifStatic = IsOwner::yes();
    }

    public function removeStaticQualif()
    {
        $this->hasQualifStatic = IsOwner::no();
    }

    public function nightQualif()
    {
        $this->hasQualifNight = IsOwner::yes();
    }

    public function removeNightQualif()
    {
        $this->hasQualifNight = IsOwner::no();
    }

    public function proQualif()
    {
        $this->hasQualifPro = IsOwner::yes();
    }

    public function removeProQualif()
    {
        $this->hasQualifPro = IsOwner::no();
    }

    public function instructorQualif()
    {
        $this->hasQualifInstructor = IsOwner::yes();
    }

    public function removeInstructorQualif()
    {
        $this->hasQualifInstructor = IsOwner::no();
    }

    public function examinatorQualif()
    {
        $this->hasQualifExaminator = IsOwner::yes();
    }

    public function removeExaminatorQualif()
    {
        $this->hasQualifExaminator = IsOwner::no();
    }

    public function radio()
    {
        $this->hasRadio = IsOwner::yes();
    }

    public function removeRadio()
    {
        $this->hasRadio = IsOwner::no();
    }

    public function trainingFirstHelp()
    {
        $this->hasTrainingFirstHelp = IsOwner::yes();
    }

    public function removeTrainingFirstHelp()
    {
        $this->hasTrainingFirstHelp = IsOwner::no();
    }

    public function trainingFire()
    {
        $this->hasTrainingFire = IsOwner::yes();
    }

    public function removeTrainingFire()
    {
        $this->hasTrainingFire = IsOwner::no();
    }

    public function attributePilotLicenceNumber(PilotLicenceNumber $value)
    {
        $this->pilotLicenceNumber = $value;
    }

    public function attributeTrainingPilotLicenceNumber(PilotTrainingLicenceNumber $value)
    {
        $this->trainingPilotLicenceNumber = $value;
    }

    public function attributeRadioLicenceNumber(RadioLicenceNumber $value)
    {
        $this->radioLicenceNumber = $value;
    }

    public function attributeCertificationNumberTrainingFirstHelp(FirstHelpCertificationNumber $value)
    {
        $this->certificationNumberTrainingFirstHelp = $value;
    }

    public function attributeCertificationNumberTrainingFire(FireCertificationNumber $value)
    {
        $this->certificationNumberTrainingFire = $value;
    }

    public function attributeLastTrainingFlightDate(LastTrainingDate $value)
    {
        $this->lastTrainingFlightDate = $value;
    }

    public function attributeEndMedicalDate(EndDate $value)
    {
        $this->endMedicalDate = $value;
    }

    public function attributeStartMedicalDate(StartDate $value)
    {
        $this->startMedicalDate = $value;
    }

    public function attributeLastOpcDate(LastTrainingDate $value)
    {
        $this->lastOpcDate = $value;
    }

    public function attributeLastProRefreshDate(LastTrainingDate $value)
    {
        $this->lastProRefreshDate = $value;
    }

    public function attributeLastInstructorRefreshDate(LastTrainingDate $value)
    {
        $this->lastInstructorRefreshDate = $value;
    }

    public function attributeLastExaminatorRefreshDate(LastTrainingDate $value)
    {
        $this->lastExaminatorRefreshDate = $value;
    }

    public function attributeLastTrainingFireDate(LastTrainingDate $value)
    {
        $this->lastTrainingFireDate = $value;
    }

    public function attributeRadioLicenceDate(RadioLicenceDate $value)
    {
        $this->radioLicenceDate =  $value;
    }

    public function attributeLastTrainingFirstHelpDate(LastTrainingDate $value)
    {
        $this->lastTrainingFirstHelpDate = $value;
    }
}