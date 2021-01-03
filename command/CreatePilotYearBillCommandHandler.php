<?php
/**
 *
 */

use FlightLog\Domain\Damage\FlightDamageCount;
use FlightLog\Domain\Damage\FlightInvoicedDamageCount;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreatePilotYearBillCommandHandler
{
    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @var \stdClass
     */
    private $conf;

    /**
     * @var User
     */
    private $user;

    private $langs;

    /**
     * @var Bbctypes
     */
    private $t1;

    /**
     * @var Bbctypes
     */
    private $t2;

    /**
     * @var Bbctypes
     */
    private $t3;

    /**
     * @var Bbctypes
     */
    private $t4;

    /**
     * @var Bbctypes
     */
    private $t5;

    /**
     * @var Bbctypes
     */
    private $t6;

    /**
     * @var Bbctypes
     */
    private $t7;

    private $localtax1_tx;

    private $localtax2_tx;

    /**
     * @var Product
     */
    private $tOrganisator;

    /**
     * @var Product
     */
    private $tInstructor;

    /**
     * @param DoliDB           $db
     * @param stdClass         $conf
     * @param User             $user
     * @param                  $langs
     * @param array|Bbctypes[] $flightTypes
     */
    public function __construct(DoliDB $db, stdClass $conf, User $user, $langs, $flightTypes)
    {
        $this->db = $db;
        $this->conf = $conf;
        $this->user = $user;
        $this->langs = $langs;

        $this->t1 = $flightTypes['1'];
        $this->t2 = $flightTypes['2'];
        $this->t3 = $flightTypes['3'];
        $this->t4 = $flightTypes['4'];
        $this->t5 = $flightTypes['5'];
        $this->t6 = $flightTypes['6'];
        $this->t7 = $flightTypes['7'];

        $this->tOrganisator = new Product($this->db);
        $this->tOrganisator->label = 'Vols dont vous êtes organisateur';
        $this->tOrganisator->tva_tx = $this->t1->getService()->tva_tx;

        $this->tInstructor = new Product($this->db);
        $this->tInstructor->label = 'Vols dont vous êtes instructeur/examinateur';
        $this->tInstructor->tva_tx = $this->t1->getService()->tva_tx;
    }

    /**
     * @param CreatePilotYearBillCommand $command
     */
    public function __invoke(CreatePilotYearBillCommand $command)
    {
        $object = new Facture($this->db);
        $object->fetch_thirdparty();

        $object->socid = $this->getCompanyIdFromPilot($command->getPilot());
        $object->type = $command->getBillType();
        $object->number = "provisoire";
        $object->date = (new \DateTime())->setDate($command->getYear(), 12, 31)->getTimestamp();
        $object->date_pointoftax = "";
        $object->note_public = $command->getPublicNote();
        $object->note_private = $command->getPrivateNote();
        $object->ref_client = "";
        $object->ref_int = "";
        $object->modelpdf = $command->getModelPdf();
        $object->cond_reglement_id = $command->getReglementCondition();
        $object->mode_reglement_id = $command->getReglementMode();
        $object->fk_account = $command->getBankAccount();

        $id = $object->create($this->user);

        if ($id <= 0) {
            throw new \InvalidArgumentException('Error while creating order');
        }

        $this->localtax1_tx = get_localtax(0, 1, $object->thirdparty);
        $this->localtax2_tx = get_localtax(0, 2, $object->thirdparty);

        $startYearTimestamp = (new \DateTime())->setDate($command->getYear(), 1, 1)->getTimestamp();
        $endYearTimestamp = (new \DateTime())->setDate($command->getYear(), 12, 31)->getTimestamp();

        $amountPerRate = $this->getTotalPerRate($command->getPilot());
        krsort($amountPerRate);

        $remainingPoints = $command->getPilot()->getFlightBonus()->addPoints(FlightPoints::create($command->getAdditionalBonus()));

        foreach ($amountPerRate as $rate => $cost){
            if($cost->getValue() <= 0){
                continue;
            }

            if($cost->getValue() <= $remainingPoints->getValue()){
                $remainingPoints = $remainingPoints->minCosts($cost);
                continue;
            }

            $subject = 'vols';
            if((int)$rate === 21){
                $subject = 'réparations';
            }

            $this->addOrderLine($object, 'Cloture d\'année concernant les ' . $subject, $cost->minBonus($remainingPoints)->getValue(), (int)$rate, 1, $startYearTimestamp, $endYearTimestamp );
            $remainingPoints = FlightBonus::zero();

        }

        $object->fetch($id);
        $object->generateDocument($command->getModelPdf(), $this->langs, $command->isDetailsHidden(),
            $command->isDescriptionHidden(), $command->isReferenceHidden());

        // Validate
        $object->fetch($id);
        $object->validate($this->user);

        // Generate document
        $object->fetch($id);
        $object->generateDocument($command->getModelPdf(), $this->langs, $command->isDetailsHidden(),
            $command->isDescriptionHidden(), $command->isReferenceHidden());
    }

    /**
     * @param Pilot $pilot
     *
     * @return int
     */
    private function getCompanyIdFromPilot(Pilot $pilot)
    {
        $expenseNoteUser = new User($this->db);
        $expenseNoteUser->fetch($pilot->getId());

        $adherent = new Adherent($this->db);
        $adherent->fetch($expenseNoteUser->fk_member);

        return $adherent->fk_soc;
    }

    /**
     * @param Facture $object
     * @param string $title
     * @param float $priceTtc
     * @param int $rate
     * @param int     $qty
     * @param string  $startDate
     * @param string  $endDate
     */
    private function addOrderLine($object, $title, $priceTtc, $rate, $qty, $startDate, $endDate)
    {
        if($qty <= 0){
            return;
        }

        $ht = $priceTtc / (1 + $rate / 100);

        $pu_ht = price2num($ht, 'MU');
        $pu_ttc = price2num($priceTtc, 'MU');
        $pu_ht_devise = price2num($ht, 'MU');

        $object->addline(
            '',
            $pu_ht,
            $qty,
            $rate,
            $this->localtax1_tx,
            $this->localtax2_tx,
            0,
            0,
            $startDate,
            $endDate,
            0,
            0,
            '',
            'TTC',
            $pu_ttc,
            1,
            -1,
            0,
            '',
            0,
            0,
            '',
            '',
            $title,
            [],
            100,
            '',
            0,
            $pu_ht_devise
        );
    }

    /**
     * Get the Cost TTC per rate.
     *
     * @param Pilot $pilot
     *
     * @return array|FlightCost[]
     */
    private function getTotalPerRate(Pilot $pilot): array
    {
        $costs = [];

        //T3
        $rate = $this->t3->getService()->tva_tx;
        if(!isset($costs[$rate])){
            $costs[$rate] = FlightCost::zero();
        }
        $costs[$rate] = $costs[$rate]->addCost($pilot->getCountForType('3')->getCost());

        //T4
        $rate = $this->t4->getService()->tva_tx;
        if(!isset($costs[$rate])){
            $costs[$rate] = FlightCost::zero();
        }
        $costs[$rate] = $costs[$rate]->addCost($pilot->getCountForType('4')->getCost());

        //T6
        $rate = $this->t6->getService()->tva_tx;
        if(!isset($costs[$rate])){
            $costs[$rate] = FlightCost::zero();
        }
        $costs[$rate] = $costs[$rate]->addCost($pilot->getCountForType('6')->getCost());

        //T7
        $rate = $this->t7->getService()->tva_tx;
        if(!isset($costs[$rate])){
            $costs[$rate] = FlightCost::zero();
        }
        $costs[$rate] = $costs[$rate]->addCost($pilot->getCountForType('7')->getCost());

        //Damages
        $rate = 21;
        if(!isset($costs[$rate])){
            $costs[$rate] = FlightCost::zero();
        }
        $costs[$rate] = $costs[$rate]->addCost($pilot->damageCost()->minCost($pilot->invoicedDamageCost()->multiply(-1)));

        return $costs;
    }


}