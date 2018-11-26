<?php
/**
 *
 */

namespace flightlog\command;

use DoliDB;
use Exception;
use ExpenseReport;
use ExpenseReportLine;
use flightlog\exceptions\NoMissionException;
use flightlog\model\missions\FlightMission;
use flightlog\query\FlightForQuarterAndPilotQuery;
use flightlog\query\FlightForQuarterAndPilotQueryHandler;
use flightlog\query\GetPilotsWithMissionsQuery;
use flightlog\query\GetPilotsWithMissionsQueryHandler;
use PilotMissions;
use stdClass;
use Translate;
use User;
use Webmozart\Assert\Assert;

/**
 * Create the expense not for flights.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateExpenseNoteCommandHandler
{
    const FLIGHT_ELEMENT = 'flightlog_bbcvols';

    /**
     * @var stdClass
     */
    protected $conf;

    /**
     * @var Translate
     */
    protected $langs;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var DoliDB
     */
    protected $db;

    /**
     * @var GetPilotsWithMissionsQueryHandler
     */
    private $getMissionsQueryHandler;

    /**
     * @var FlightForQuarterAndPilotQueryHandler
     */
    private $flightForQuarterAndPilotHandler;


    /**
     * @param DoliDB                               $db
     * @param stdClass                             $conf
     * @param User                                 $user
     * @param Translate                            $langs
     * @param GetPilotsWithMissionsQueryHandler    $missionsQueryHandler
     * @param FlightForQuarterAndPilotQueryHandler $flightForQuarterAndPilotQueryHandler
     */
    public function __construct(
        $db,
        $conf,
        $user,
        $langs,
        GetPilotsWithMissionsQueryHandler $missionsQueryHandler,
        FlightForQuarterAndPilotQueryHandler $flightForQuarterAndPilotQueryHandler
    ) {
        $this->db = $db;
        $this->conf = $conf;
        $this->user = $user;
        $this->langs = $langs;
        $this->getMissionsQueryHandler = $missionsQueryHandler;
        $this->flightForQuarterAndPilotHandler = $flightForQuarterAndPilotQueryHandler;
    }

    /**
     * @param CreateExpenseNoteCommand $command
     *
     * @throws Exception
     */
    public function __invoke(CreateExpenseNoteCommand $command)
    {
        $missions = $this->getMissionsQueryHandler->__invoke(new GetPilotsWithMissionsQuery($command->getYear(), $command->getQuartile()));

        if (!$missions->hasMission()) {
            throw new NoMissionException();
        }

        /** @var PilotMissions $currentMission */
        foreach ($missions as $currentMission) {
            $expenseNote = $this->createExpenseNote($command);

            $flightsForQuarter = $this->flightForQuarterAndPilotHandler->__invoke(new FlightForQuarterAndPilotQuery($currentMission->getPilotId(),
                $command->getQuartile(), $command->getYear()));

            /** @var FlightMission $currentFlightForQuarter */
            foreach ($flightsForQuarter as $currentFlightForQuarter) {
                $expenseNote = $this->addKilometersLine($currentFlightForQuarter, $expenseNote);
                $expenseNote = $this->addMissionLine($currentFlightForQuarter, $expenseNote);
            }

            $expenseNote = $this->saveExpenseNote($currentMission->getPilotId(), $expenseNote);
            if (null === $expenseNote) {
                dol_htmloutput_errors("Erreur lors de la création de la note de frais", $expenseNote->errors);
                continue;
            }

            /** @var FlightMission $currentFlightForQuarter */
            foreach ($flightsForQuarter as $currentFlightForQuarter) {
                $expenseNote->add_object_linked(self::FLIGHT_ELEMENT, $currentFlightForQuarter->getId());
            }

            $expenseNote->fetch($expenseNote->id);
            $expenseNote->setValidate($this->user);
            $expenseNote->setApproved($this->user);
            $expenseNote->setDocModel($this->user, "standard");

            $error = false;
            //$error = $expenseNote->generateDocument($expenseNote->modelpdf, $this->langs) <= 0;

            if (!$error) {
                dol_htmloutput_mesg(sprintf("Notes de frais crée pour %s", $currentMission->getPilotName()));
                continue;
            }

            dol_htmloutput_errors(sprintf("Notes de frais non crée pour %s", $currentMission->getPilotName()));
        }
    }

    /**
     * @param FlightMission $currentFlightForQuarter
     * @param ExpenseReport $expenseNote
     *
     * @return ExpenseReport
     */
    private function addKilometersLine(FlightMission $currentFlightForQuarter, $expenseNote)
    {
        $object_ligne = new ExpenseReportLine($this->db);
        $object_ligne->comments = $this->langs->trans(sprintf("Vol (id: %d) %s à %s  détail: %s",
            $currentFlightForQuarter->getId(), $currentFlightForQuarter->getStartPoint(),
            $currentFlightForQuarter->getEndPoint(), $currentFlightForQuarter->getKilometersComment()));
        $object_ligne->qty = $currentFlightForQuarter->getNumberOfKilometers();
        $object_ligne->value_unit = $this->getAmountByKilometer();

        $object_ligne->date = $currentFlightForQuarter->getDate()->format('Y-m-d');

        $object_ligne->fk_c_type_fees = 2;
        $object_ligne->fk_projet = '';

        $object_ligne->vatrate = price2num($this->getVatRate());

        $tmp = calcul_price_total($object_ligne->qty, $object_ligne->value_unit, 0, $this->getVatRate(), 0, 0, 0, 'TTC',
            0,
            0, '');
        $object_ligne->total_ttc = $tmp[2];
        $object_ligne->total_ht = $tmp[0];
        $object_ligne->total_tva = $tmp[1];

        $expenseNote->lines[] = $object_ligne;

        return $expenseNote;
    }

    /**
     * @param FlightMission $currentFlightForQuarter
     * @param ExpenseReport $expenseReport
     *
     * @return ExpenseReport
     */
    private function addMissionLine(FlightMission $currentFlightForQuarter, ExpenseReport $expenseReport)
    {
        $object_ligne = new ExpenseReportLine($this->db);
        $object_ligne->comments = sprintf("Vol (id: %d) %s à %s", $currentFlightForQuarter->getId(),
            $currentFlightForQuarter->getStartPoint(), $currentFlightForQuarter->getEndPoint());
        $object_ligne->qty = 1;
        $object_ligne->value_unit = $this->getAmountByMission();

        $object_ligne->date = $currentFlightForQuarter->getDate()->format('Y-m-d');

        $object_ligne->fk_c_type_fees = 8;
        $object_ligne->fk_projet = '';

        $object_ligne->vatrate = price2num($this->getVatRate());

        $tmp = calcul_price_total($object_ligne->qty, $object_ligne->value_unit, 0, $this->getVatRate(), 0, 0, 0, 'TTC',
            0,
            0, '');
        $object_ligne->total_ttc = $tmp[2];
        $object_ligne->total_ht = $tmp[0];
        $object_ligne->total_tva = $tmp[1];

        $expenseReport->lines[] = $object_ligne;

        return $expenseReport;
    }

    /**
     * Get the unit price pe KM.
     *
     * @return int
     */
    private function getAmountByKilometer()
    {
        return isset($this->conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM) ? $this->conf->global->BBC_FLIGHT_LOG_TAUX_REMB_KM : 0;
    }

    /**
     * @return mixed
     */
    private function getAmountByMission()
    {
        return $this->conf->global->BBC_FLIGHT_LOG_UNIT_PRICE_MISSION;
    }

    /**
     * @return string
     */
    private function getVatRate()
    {
        return '0.000';
    }

    /**
     * @param CreateExpenseNoteCommand $command
     *
     * @return ExpenseReport
     * @throws Exception
     */
    private function createExpenseNote(CreateExpenseNoteCommand $command)
    {
        $startDate = new \DateTime();
        $startDate->setDate($command->getYear(), (($command->getQuartile() - 1) * 3) + 1, 1);

        $endDate = new \DateTime();
        $endDate->setDate($command->getYear(), $command->getQuartile() * 3, 1);
        $endDate->add(new \DateInterval("P1M"))->sub(new \DateInterval("P1D"));

        $object = new ExpenseReport($this->db);
        $object->date_debut = $startDate->format("Y-m-d");
        $object->date_fin = $endDate->format("Y-m-d");

        $object->fk_statut = 1;
        $object->fk_user_validator = $command->getUserValidatorId();
        $object->note_public = $command->getPublicNote();
        $object->note_private = $command->getPrivateNote();

        return $object;
    }

    /**
     * @param int           $currentMissionUserId
     * @param ExpenseReport $expenseNote
     *
     * @return ExpenseReport
     */
    private function saveExpenseNote($currentMissionUserId, ExpenseReport $expenseNote)
    {
        Assert::integerish($currentMissionUserId);

        $expenseNoteUser = new User($this->db);
        $expenseNoteUser->id = $currentMissionUserId;
        $id = $expenseNote->create($expenseNoteUser);
        if($id < 0){
            return null;
        }

        return $expenseNote;
    }

}