<?php

require_once __DIR__ . '/CommandHandlerInterface.php';

/**
 * Methods to create a bill.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
abstract class AbstractBillCommandHandler implements CommandHandlerInterface
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
     * @var Client
     */
    private $customer;

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
        $this->customer = null;
    }

    /**
     * @return Product
     */
    protected function getProduct()
    {
        $flightProduct = new Product($this->db);

        if ($flightProduct->fetch($this->conf->BBC_FLIGHT_TYPE_CUSTOMER) <= 0) {
            throw new \InvalidArgumentException('Default product not configured');
        }

        return $flightProduct;
    }

    /**
     * @param int|null $receiverId
     *
     * @return Client
     * @throws CustomerNotFoundException
     */
    protected function fetchCustomer($receiverId = null)
    {
        $user = new User($this->db);
        $res = $user->fetch($receiverId);
        if ($res <= 0) {
            throw new CustomerNotFoundException('User not found');
        }

        $member = new Adherent($this->db);
        $res = $member->fetch($user->fk_member);
        if ($res <= 0) {
            throw new CustomerNotFoundException('Member not found');
        }

        $this->customer = new Client($this->db);
        if ($this->customer->fetch($member->fk_soc) <= 0) {
            throw new CustomerNotFoundException();
        }

        return $this->customer;
    }

    /**
     * @return Client
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Facture $object
     * @param Bbcvols $flight
     */
    protected function addLinks($object, $flight)
    {
        $object->add_object_linked('flightlog_bbcvols', $flight->getId());
    }

    /**
     * @param Facture $object
     * @param int     $id
     */
    protected function validates($object, $id)
    {
        $object->fetch($id);
        $object->validate($this->user);
    }

    /**
     * @return int
     */
    protected function isReferenceHidden()
    {
        return (!empty($this->conf->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0);
    }

    /**
     * @return int
     */
    protected function isDescriptionHidden()
    {
        return (!empty($this->conf->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0);
    }

    /**
     * @return int
     */
    protected function isDetailHidden()
    {
        return (!empty($this->conf->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0);
    }

    /**
     * @param Facture $object
     * @param int     $id
     */
    protected function generateBillDocument($object, $id)
    {
        $object->fetch($id);
        $object->generateDocument(
            $this->getModelDocument(),
            $this->langs,
            $this->isDetailHidden(),
            $this->isDescriptionHidden(),
            $this->isReferenceHidden()
        );
    }

    /**
     * @param Product $flightProduct
     * @param Bbcvols $flight
     *
     * @return float|int
     */
    protected function computeDiscounts($flightProduct, $flight)
    {
        return ($flightProduct->price_ttc - ($flight->cost / $flight->nbrPax)) * 100 / $flightProduct->price_ttc;
    }

    /**
     * @param Facture $facture
     * @param Product $flightProduct
     * @param Bbcvols $flight
     */
    protected function addOrderLine($facture, $flightProduct, $flight)
    {
        $localtax1_tx = get_localtax(0, 1, $facture->thirdparty);
        $localtax2_tx = get_localtax(0, 2, $facture->thirdparty);

        $pu_ht = price2num($flightProduct->price, 'MU');
        $pu_ttc = price2num($flightProduct->price_ttc, 'MU');
        $pu_ht_devise = price2num($flightProduct->price, 'MU');

        $discount = $this->computeDiscounts($flightProduct, $flight);

        if (!is_numeric($flight->nbrPax)) {
            throw new \InvalidArgumentException(sprintf('%s is not a number', $flight->nbrPax));
        }

        $result = $facture->addline(
            $flight->getDescription(),
            $pu_ht,
            $flight->nbrPax,
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
    protected function flagFlightAsBilled($flight)
    {
        $flight->is_facture = true;
        $flight->update($this->user);
    }

    /**
     * @return int
     */
    protected function getModelDocument()
    {
        return $this->conf->FACTURE_ADDON_PDF;
    }
}