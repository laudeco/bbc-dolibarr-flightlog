<?php


namespace FlightLog\Application\Pilot\Command;


use FlightLog\Domain\Pilot\Pilot;
use FlightLog\Domain\Pilot\ValueObject\EndDate;
use FlightLog\Domain\Pilot\ValueObject\FireCertificationNumber;
use FlightLog\Domain\Pilot\ValueObject\FirstHelpCertificationNumber;
use FlightLog\Domain\Pilot\ValueObject\LastTrainingDate;
use FlightLog\Domain\Pilot\ValueObject\PilotId;
use FlightLog\Domain\Pilot\ValueObject\PilotLicenceNumber;
use FlightLog\Domain\Pilot\ValueObject\PilotTrainingLicenceNumber;
use FlightLog\Domain\Pilot\ValueObject\RadioLicenceDate;
use FlightLog\Domain\Pilot\ValueObject\RadioLicenceNumber;
use FlightLog\Domain\Pilot\ValueObject\StartDate;
use FlightLog\Infrastructure\Pilot\Repository\PilotRepository;

final class CreateUpdatePilotInformationCommandHandler
{

    /**
     * @var PilotRepository
     */
    private $pilotRepository;

    public function __construct(PilotRepository $pilotRepository)
    {
        $this->pilotRepository = $pilotRepository;
    }

    /**
     * @param CreateUpdatePilotInformationCommand $command
     *
     * @throws \Exception
     */
    public function __invoke(CreateUpdatePilotInformationCommand $command)
    {
        $pilotId = PilotId::create($command->getPilotId());
        $pilot = $this->getOrCreatePilot($pilotId);


        if ($command->getIsMedicalOwner()) {
            $pilot->medical();
        } else {
            $pilot->removeMedical();
        }

        if ($command->getIsPilotClassA()) {
            $pilot->hasClassA();
        } else {
            $pilot->removeClassA();
        }
        if ($command->getIsPilotClassB()) {
            $pilot->hasClassB();
        } else {
            $pilot->removeClassB();
        }
        if ($command->getIsPilotClassC()) {
            $pilot->hasClassC();
        } else {
            $pilot->removeClassC();
        }
        if ($command->getIsPilotClassD()) {
            $pilot->hasClassD();
        } else {
            $pilot->removeClassD();
        }
        if ($command->getIsPilotGaz()) {
            $pilot->gazPilot();
        } else {
            $pilot->removeGazPilot();
        }
        if ($command->getIsHasQualifStatic()) {
            $pilot->staticQualif();
        } else {
            $pilot->removeStaticQualif();
        }
        if ($command->getIsHasQualifNight()) {
            $pilot->nightQualif();
        } else {
            $pilot->removeNightQualif();
        }
        if ($command->getIsHasQualifPro()) {
            $pilot->proQualif();
        } else {
            $pilot->removeProQualif();
        }
        if ($command->getIsHasQualifInstructor()) {
            $pilot->instructorQualif();
        } else {
            $pilot->removeInstructorQualif();
        }
        if ($command->getIsHasQualifExaminator()) {
            $pilot->examinatorQualif();
        } else {
            $pilot->removeExaminatorQualif();
        }
        if ($command->getIsHasRadio()) {
            $pilot->radio();
        } else {
            $pilot->removeRadio();
        }
        if ($command->getIsHasTrainingFirstHelp()) {
            $pilot->trainingFirstHelp();
        } else {
            $pilot->removeTrainingFirstHelp();
        }
        if ($command->getIsHasTrainingFire()) {
            $pilot->trainingFire();
        } else {
            $pilot->removeTrainingFire();
        }

        $pilot->attributePilotLicenceNumber(PilotLicenceNumber::create($command->getPilotLicenceNumber()));
        $pilot->attributeTrainingPilotLicenceNumber(PilotTrainingLicenceNumber::create($command->getTrainingPilotLicenceNumber()));
        $pilot->attributeRadioLicenceNumber(RadioLicenceNumber::create($command->getRadioLicenceNumber()));
        $pilot->attributeCertificationNumberTrainingFirstHelp(FirstHelpCertificationNumber::create($command->getCertificationNumberTrainingFirstHelp()));
        $pilot->attributeCertificationNumberTrainingFire(FireCertificationNumber::create($command->getCertificationNumberTrainingFire()));

        $pilot->attributeLastTrainingFlightDate(LastTrainingDate::fromString($command->getLastTrainingFlightDate()));
        $pilot->attributeEndMedicalDate(EndDate::fromString($command->getEndMedicalDate()));
        $pilot->attributeStartMedicalDate(StartDate::fromString($command->getStartMedicalDate()));
        $pilot->attributeLastOpcDate(LastTrainingDate::fromString($command->getLastOpcDate()));
        $pilot->attributeLastProRefreshDate(LastTrainingDate::fromString($command->getLastProRefreshDate()));
        $pilot->attributeLastInstructorRefreshDate(LastTrainingDate::fromString($command->getLastInstructorRefreshDate()));
        $pilot->attributeLastExaminatorRefreshDate(LastTrainingDate::fromString($command->getLastExaminatorRefreshDate()));
        $pilot->attributeLastTrainingFireDate(LastTrainingDate::fromString($command->getLastTrainingFireDate()));
        $pilot->attributeRadioLicenceDate(RadioLicenceDate::fromString($command->getRadioLicenceDate()));
        $pilot->attributeLastTrainingFirstHelpDate(LastTrainingDate::fromString($command->getLastTrainingFirstHelpDate()));

        $this->pilotRepository->save($pilot);
    }

    /**
     * @param PilotId $pilotId
     *
     * @return Pilot
     *
     * @throws \Exception
     */
    private function getOrCreatePilot(PilotId $pilotId): Pilot
    {
        if ($this->pilotRepository->exist($pilotId)) {
            return $this->pilotRepository->getById($pilotId);
        }

        return Pilot::create($pilotId);
    }


}