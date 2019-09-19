<?php

require_once __DIR__ . '/../exceptions/ContactNotAddedException.php';
require_once __DIR__ . '/../exceptions/CustomerNotFoundException.php';
require_once __DIR__ . '/../exceptions/FlightNotFoundException.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateFlightBillCommandHandler
{
    /**
     * @var \DoliDB
     */
    protected $db;

    /**
     * @var stdClass
     */
    private $conf;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Translate
     */
    private $langs;

    /**
     * @param DoliDB    $db
     * @param stdClass  $conf
     * @param User      $user
     * @param Translate $langs
     */
    public function __construct($db, $conf, $user, $langs)
    {
        $this->db = $db;
        $this->conf = $conf;
        $this->user = $user;
        $this->langs = $langs;
    }

    /**
     * @param CreateFlightBillCommand $command
     */
    public function handle(CreateFlightBillCommand $command)
    {
        $flightProduct = $this->getProduct();
        $flight = $this->getFlight($command->getFlightId());

        $object = new Facture($this->db);
        $object->fetch_thirdparty();

        $object->socid = $command->getCustomerId();
        $object->type = $command->getBillType();
        $object->number = "provisoire";
        $object->date = (new DateTime())->getTimestamp();
        $object->date_pointoftax = "";
        $object->note_public = $command->getPublicNote();
        $object->note_private = $command->getPrivateNote();
        $object->ref_client = "";
        $object->ref_int = "";
        $object->modelpdf = $command->getModelDocument();
        $object->cond_reglement_id = $command->getBillingCondition();
        $object->mode_reglement_id = $command->getBillingType();
        $object->fk_account = $command->getBankAccount();

        $id = $object->create($this->user);

        if ($id <= 0) {
            throw new \InvalidArgumentException('Error during bill creation');
        }

        $this->addOrderLine($object, $flightProduct, $flight, $command->getNbrPax());

        $this->addLinks($object, $flight);
        $this->addContacts($object, $flight);

        $this->generateBillDocument($command, $object, $id);

        $this->validates($object, $id);

        $this->generateBillDocument($command, $object, $id);
        $this->flagFlightAsBilled($flight);
    }

    /**
     * @return Product
     */
    private function getProduct()
    {
        $flightProduct = new Product($this->db);

        if ($flightProduct->fetch($this->conf->BBC_FLIGHT_TYPE_CUSTOMER) <= 0) {
            throw new \InvalidArgumentException('Default product not configured');
        }

        return $flightProduct;
    }

    /**
     * @param int $flightId
     *
     * @return Bbcvols
     *
     * @throws FlightNotFoundException
     */
    private function getFlight($flightId)
    {
        $flight = new Bbcvols($this->db);

        if ($flight->fetch($flightId) <= 0) {
            throw new FlightNotFoundException();
        }

        return $flight;
    }

    /**
     * @param Facture $object
     * @param Bbcvols $flight
     *
     * @throws ContactNotAddedException
     */
    private function addContacts($object, $flight)
    {
        $this->addContactOnBill($object, $flight->fk_pilot, 'BBC_PILOT');
        $this->addContactOnBill($object, $flight->fk_receiver, 'BBC_RECEIVER');
        $this->addContactOnBill($object, $flight->fk_organisateur, 'BBC_ORGANISATOR');
    }

    /**
     * @param Facture $bill
     * @param int     $contactId
     * @param string  $contactType
     *
     * @throws ContactNotAddedException
     */
    private function addContactOnBill(Facture $bill, $contactId, $contactType)
    {
        if ($bill->add_contact($contactId, $contactType, 'internal') < 0) {
            throw new ContactNotAddedException($contactType);
        }
    }

    /**
     * @param Facture $object
     * @param Bbcvols $flight
     */
    private function addLinks($object, $flight)
    {
        $object->add_object_linked('flightlog_bbcvols', $flight->getId());
    }

    /**
     * @param Facture $object
     * @param int     $id
     */
    private function validates($object, $id)
    {
        $object->fetch($id);
        $object->validate($this->user);
    }

    /**
     * @return int
     */
    private function isReferenceHidden()
    {
        return (!empty($this->conf->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0);
    }

    /**
     * @return int
     */
    private function isDescriptionHidden()
    {
        return (!empty($this->conf->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0);
    }

    /**
     * @return int
     */
    private function isDetailHidden()
    {
        return (!empty($this->conf->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0);
    }

    /**
     * @param CreateFlightBillCommand $command
     * @param Facture                 $object
     * @param int                     $id
     */
    private function generateBillDocument(CreateFlightBillCommand $command, $object, $id)
    {
        $object->fetch($id);
        $object->generateDocument(
            $command->getModelDocument(),
            $this->langs,
            $this->isDetailHidden(),
            $this->isDescriptionHidden(),
            $this->isReferenceHidden()
        );
    }

    /**
     * @param $flightProduct
     * @param $flight
     *
     * @return float|int
     */
    private function computeDiscounts($flightProduct, $flight)
    {
        return ($flightProduct->price_ttc - ($flight->cost / $flight->nbrPax)) * 100 / $flightProduct->price_ttc;
    }

    /**
     * @param Facture $object
     * @param Product $flightProduct
     * @param Bbcvols $flight
     */
    private function addOrderLine($object, $flightProduct, $flight, $nbrPax)
    {
        $localtax1_tx = get_localtax(0, 1, $object->thirdparty);
        $localtax2_tx = get_localtax(0, 2, $object->thirdparty);

        $pu_ht = price2num($flightProduct->price, 'MU');
        $pu_ttc = price2num($flightProduct->price_ttc, 'MU');
        $pu_ht_devise = price2num($flightProduct->price, 'MU');

        $discount = $this->computeDiscounts($flightProduct, $flight);

        $result = $object->addline(
            $flightProduct->description,
            $pu_ht,
            $nbrPax,
            $flightProduct->tva_tx,
            $localtax1_tx,
            $localtax2_tx,
            $flightProduct->id,
            $discount,
            $flight->date,
            $flight->date,
            0,
            0,
            '',
            'TTC',
            $pu_ttc,
            Facture::TYPE_STANDARD,
            -1,
            0,
            '',
            0,
            0,
            '',
            '',
            $flightProduct->label,
            [],
            100,
            '',
            0,
            $pu_ht_devise
        );

        if ($result <= 0) {
            throw new \InvalidArgumentException('Error during order line creation');
        }
    }

    /**
     * @param Bbcvols $flight
     */
    private function flagFlightAsBilled($flight)
    {
        $flight->is_facture = true;
        $flight->update($this->user);
    }
}