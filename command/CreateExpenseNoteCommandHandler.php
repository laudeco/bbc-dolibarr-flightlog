<?php
/**
 *
 */

use Webmozart\Assert\Assert;

/**
 * Create the expense not for flights.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateExpenseNoteCommandHandler
{

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
     * @var \DoliDB
     */
    protected $db;

    /**
     * @param CreateExpenseNoteCommand $command
     *
     * @throws Exception
     */
    public function __invoke(CreateExpenseNoteCommand $command)
    {
        $missions = $this->getMissions($command);
        foreach ($missions as $currentMissionUserId => $currentMission) {

            if ($currentMission["quartil"][$command->getQuartile()]["km"] == 0 && $currentMission["quartil"][$command->getQuartile()]["flight"] == 0) {
                continue;
            }

            $expenseNote = $this->createExpenseNote($command);
            $expenseNoteId = $this->saveExpenseNote($currentMissionUserId, $expenseNote);
            if ($expenseNoteId < 0) {
                dol_htmloutput_errors("Erreur lors de la création de la note de frais", $expenseNote->errors);
                continue;
            }

            $flightsForQuarter = findFlightByPilotAndQuarter($currentMissionUserId, $command->getYear(), $command->getQuartile());

            foreach ($flightsForQuarter as $currentFlightForQuarter) {
                $this->addFlight($currentFlightForQuarter, $expenseNoteId);
            }

            $expenseNote->fetch($expenseNoteId);
            $expenseNote->setValidate($this->user);
            $expenseNote->setApproved($this->user);

            $expenseNote->fetch($expenseNoteId);
            $expenseNote->setDocModel($this->user, "standard");
            $result = $expenseNote->generateDocument($expenseNote->modelpdf, $this->langs);
        }


        if ($result > 0) {
            dol_htmloutput_mesg("Notes de frais créées");
        } else {
            dol_htmloutput_errors("Note de frais non créée");
        }
    }


    /**
     * @param CreateExpenseNoteCommand $command
     *
     * @return array
     */
    private function getMissions(CreateExpenseNoteCommand $command)
    {
        return bbcKilometersByQuartil($command->getYear());
    }

    /**
     * @param $currentFlightForQuarter
     * @param $expenseNoteId
     */
    private function addKilometersLine($currentFlightForQuarter, $expenseNoteId)
    {
        $object_ligne = new ExpenseReportLine($this->db);
        $object_ligne->comments = $this->langs->trans(sprintf("Vol (id: %d) %s à %s  détail: %s",
            $currentFlightForQuarter->idBBC_vols, $currentFlightForQuarter->lieuD,
            $currentFlightForQuarter->lieuA, $currentFlightForQuarter->justif_kilometers));
        $object_ligne->qty = $currentFlightForQuarter->kilometers;
        $object_ligne->value_unit = $this->getAmountByKilometer();

        $object_ligne->date = $currentFlightForQuarter->date;

        $object_ligne->fk_c_type_fees = 2;
        $object_ligne->fk_expensereport = $expenseNoteId;
        $object_ligne->fk_projet = '';

        $object_ligne->vatrate = price2num($this->getVatRate());

        $tmp = calcul_price_total($object_ligne->qty, $object_ligne->value_unit, 0, $this->getVatRate(), 0, 0, 0, 'TTC', 0,
            0, '');
        $object_ligne->total_ttc = $tmp[2];
        $object_ligne->total_ht = $tmp[0];
        $object_ligne->total_tva = $tmp[1];

        $resultLine = $object_ligne->insert();
    }

    /**
     * @param $currentFlightForQuarter
     * @param $expenseNoteId
     */
    private function addMissionLine($currentFlightForQuarter, $expenseNoteId)
    {
        $object_ligne = new ExpenseReportLine($this->db);
        $object_ligne->comments = sprintf("Vol (id: %d) %s à %s", $currentFlightForQuarter->idBBC_vols,
            $currentFlightForQuarter->lieuD, $currentFlightForQuarter->lieuA);
        $object_ligne->qty = 1;
        $object_ligne->value_unit = $this->getAmountByMission();

        $object_ligne->date = $currentFlightForQuarter->date;

        $object_ligne->fk_c_type_fees = 8;
        $object_ligne->fk_expensereport = $expenseNoteId;
        $object_ligne->fk_projet = '';

        $object_ligne->vatrate = price2num($this->getVatRate());

        $tmp = calcul_price_total($object_ligne->qty, $object_ligne->value_unit, 0, $this->getVatRate(), 0, 0, 0, 'TTC', 0,
            0, '');
        $object_ligne->total_ttc = $tmp[2];
        $object_ligne->total_ht = $tmp[0];
        $object_ligne->total_tva = $tmp[1];

        $resultLine = $object_ligne->insert();
    }

    /**
     * @param $currentFlightForQuarter
     * @param $expenseNoteId
     */
    private function addFlight($currentFlightForQuarter, $expenseNoteId)
    {
        $this->addKilometersLine($currentFlightForQuarter, $expenseNoteId);
        $this->addMissionLine($currentFlightForQuarter, $expenseNoteId);
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
    private function getVatRate(){
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
     * @param int $currentMissionUserId
     * @param ExpenseReport $expenseNote
     *
     * @return mixed
     */
    private function saveExpenseNote($currentMissionUserId, ExpenseReport $expenseNote)
    {
        Assert::integerish($currentMissionUserId);

        $expenseNoteUser = new User($this->db);
        $expenseNoteUser->id = $currentMissionUserId;
        return $expenseNote->create($expenseNoteUser);
    }

}