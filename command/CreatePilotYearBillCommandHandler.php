<?php
/**
 *
 */

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

        //T3
        $this->addOrderLine($object, $this->t3->getService(), $command->getPilot()->getCountForType('3')->getCount(),
            $startYearTimestamp, $endYearTimestamp);

        //T4
        $this->addOrderLine($object, $this->t4->getService(), $command->getPilot()->getCountForType('4')->getCount(),
            $startYearTimestamp, $endYearTimestamp);

        //T6
        $this->addOrderLine($object, $this->t6->getService(), $command->getPilot()->getCountForType('6')->getCount(),
            $startYearTimestamp, $endYearTimestamp);

        //T7
        $this->addOrderLine($object, $this->t7->getService(), $command->getPilot()->getCountForType('7')->getCount(),
            $startYearTimestamp, $endYearTimestamp);

        //Damages
        $this->addDamages($object, $command->getPilot()->getCountForType('damage'), $command->getPilot()->getCountForType('invoiced_damage'), $startYearTimestamp, $endYearTimestamp);

        $this->addOrderDiscount($object, $command->getPilot()->getCountForType('1'), $this->t1->getService(), $command->getYear());
        $this->addOrderDiscount($object, $command->getPilot()->getCountForType('2'), $this->t2->getService(), $command->getYear());
        $this->addOrderDiscount($object, $command->getPilot()->getCountForType('orga'), $this->tOrganisator, $command->getYear());
        $this->addOrderDiscount($object, $command->getPilot()->getCountForType('orga_T6'), $this->tInstructor, $command->getYear());

        //Additional bonus
        $this->addAdditionalBonusToOrder($command, $object);

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
     * @param Product $service
     * @param int     $qty
     * @param string  $startDate
     * @param string  $endDate
     */
    private function addOrderLine($object, $service, $qty, $startDate, $endDate)
    {
        $pu_ht = price2num($service->price, 'MU');
        $pu_ttc = price2num($service->price_ttc, 'MU');
        $pu_ht_devise = price2num($service->price, 'MU');

        $object->addline(
            $service->description,
            $pu_ht,
            $qty,
            $service->tva_tx,
            $this->localtax1_tx,
            $this->localtax2_tx,
            $service->id,
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
            $service->label,
            [],
            100,
            '',
            0,
            $pu_ht_devise
        );
    }

    /**
     * @param Facture         $order
     * @param FlightTypeCount $pilotFlightCount
     * @param Product         $service
     * @param string          $year
     */
    private function addOrderDiscount($order, $pilotFlightCount, $service, $year)
    {
        $pu_ht = price2num($pilotFlightCount->getCost()->getValue(), 'MU');
        $desc = $year . " - " . $service->label . " - (" . $pilotFlightCount->getCount() . " * " . $pilotFlightCount->getFactor() . ")";

        $discountid = $this->getCompany($order->socid)->set_remise_except($pu_ht, $this->user, $desc, $service->tva_tx);
        $order->insert_discount($discountid);
    }

    /**
     * @param int $id
     *
     * @return Societe
     */
    private function getCompany($id)
    {
        $soc = new Societe($this->db);
        $soc->fetch($id);

        return $soc;
    }

    /**
     * @param CreatePilotYearBillCommand $command
     * @param Facture                    $object
     */
    private function addAdditionalBonusToOrder(CreatePilotYearBillCommand $command, $object)
    {
        if ((int) $command->getAdditionalBonus() <= 0) {
            return;
        }

        $pointsHt = $command->getAdditionalBonus()/(1+6/100);
        $desc = sprintf("%s - %s", $command->getYear(), $command->getBonusAdditionalMessage());

        $discountid = $this->getCompany($object->socid)->set_remise_except($pointsHt, $this->user, $desc, 6);
        $object->insert_discount($discountid);
    }

    /**
     * Adds the damages.
     *
     * @param Facture $object
     * @param FlightTypeCount $damage
     * @param FlightTypeCount $invoicedDamage
     * @param string $start
     * @param string $end
     */
    private function addDamages(Facture $object, FlightTypeCount $damage, FlightTypeCount $invoicedDamage, $start, $end)
    {
        $price = $damage->getCost()->addCost($invoicedDamage->getCost())->getValue();

        if($price <= 0 ){
            return;
        }

        $tDamage = new Product($this->db);
        $tDamage->label = 'Réparations';
        $tDamage->tva_tx = 21;
        $tDamage->price_ttc = $price;
        $tDamage->price = $tDamage->price_ttc / (1 + $tDamage->tva_tx / 100);

        $this->addOrderLine($object, $tDamage, 1, $start, $end);
    }


}