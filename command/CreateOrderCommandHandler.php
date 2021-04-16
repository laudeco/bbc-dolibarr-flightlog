<?php

require_once __DIR__ . '/CommandHandlerInterface.php';
require_once __DIR__ . '/CommandInterface.php';
require_once __DIR__ . '/CreateOrderCommand.php';
require_once __DIR__ . '/../../societe/class/societe.class.php';
require_once __DIR__ . '/../../commande/class/commande.class.php';
require_once __DIR__ . '/../../product/class/product.class.php';

/**
 * CreateOrderCommandHandler class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateOrderCommandHandler implements CommandHandlerInterface
{
    /**
     * @var DoliDB
     */
    private $db;

    /**
     * @var stdClass
     */
    private $conf;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Translate
     */
    private $langs;

    /**
     * @var ModeleThirdPartyCode
     */
    private $codeFournisseurGenerator;

    /**
     * @var ModeleThirdPartyCode
     */
    private $codeClientGenerator;

    /**
     * @var Societe
     */
    private $societe;

    /**
     * @var Commande
     */
    private $order;

    /**
     * @param DoliDB $db
     * @param stdClass $conf
     * @param User $user
     * @param Translate $langs
     * @param ModeleThirdPartyCode $codeClientGenerator
     * @param ModeleThirdPartyCode $codeFounrisseurGenerator
     */
    public function __construct(
        $db,
        $conf,
        $user,
        $langs,
        ModeleThirdPartyCode $codeClientGenerator,
        ModeleThirdPartyCode $codeFounrisseurGenerator
    ) {
        $this->db = $db;
        $this->conf = $conf->global;
        $this->user = $user;
        $this->langs = $langs;
        $this->codeClientGenerator = $codeClientGenerator;
        $this->codeFournisseurGenerator = $codeFounrisseurGenerator;
    }

    /**
     * @param CreateOrderCommand|CommandInterface $command
     *
     * @throws Exception
     */
    public function handle(CommandInterface $command)
    {
        $customerId = $this->createCustomer($command)->id;

        $this->createOrder($command, $customerId)
            ->addLine($command)
            ->addContacts()
            ->validateOrder();

        $this->order->fetch($this->order->id);

        $this->order->generateDocument('einstein', $this->langs);
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
     * @param Product $flightProduct
     * @param float|int $pricePerPax
     *
     * @return float|int
     */
    private function computeDiscounts($flightProduct, $pricePerPax = 0)
    {
        return ($flightProduct->price_ttc - ($pricePerPax)) * 100 / $flightProduct->price_ttc;
    }

    /**
     * @param CommandInterface|CreateOrderCommand $command
     *
     * @return Societe
     * @throws Exception
     */
    private function createCustomer(CommandInterface $command)
    {
        $this->societe = new Societe($this->db);
        if ($command->hasSocId()) {
            $this->societe->fetch($command->getSocid());
            return $this->societe;
        }

        $name = $command->getName() . ' ' . $command->getFirstname();

        $existingCustomers = $this->societe->fetch(null, $name);
        if ($existingCustomers > 0) {
            //$this->societe = $existingCustomers[0];
            return $this->societe;
        }

        $this->societe->particulier = 1;
        $this->societe->name = $name;
        $this->societe->civility_id = $command->getCivilityId();
        $this->societe->name_bis = $command->getName();
        $this->societe->firstname = $command->getFirstname();
        $this->societe->entity = $this->conf->entity;
        $this->societe->name_alias = '';
        $this->societe->address = GETPOST('address');
        $this->societe->zip = $command->getZip();
        $this->societe->town = $command->getTown();
        $this->societe->country_id = 2;
        $this->societe->state_id = $command->getState();
        $this->societe->phone = $command->getPhone();
        $this->societe->email = $command->getEmail();
        $this->societe->code_client = $this->codeClientGenerator->getNextValue($this->societe, 0);
        $this->societe->code_fournisseur = $this->codeFournisseurGenerator->getNextValue($this->societe, 1);
        $this->societe->tva_intra = $command->getTva();
        $this->societe->tva_assuj = empty($command->getTva()) ? 0 : 1;
        $this->societe->status = 1;
        $this->societe->client = 3; //prospect + customer
        $this->societe->fournisseur = 0;
        $this->societe->commercial_id = $this->user->id;
        $this->societe->default_lang = $command->getLanguage();

        $customerId = $this->societe->create($this->user);
        if ($customerId <= 0) {
            throw new Exception($this->societe->errorsToString(), $customerId);
        }

        return $this->societe;
    }

    /**
     * @param CommandInterface|CreateOrderCommand $command
     *
     * @return CreateOrderCommandHandler
     */
    private function addLine(CommandInterface $command)
    {
        $product = $this->getProduct();
        $pu_ht = price2num($product->price, 'MU');

        $this->order->addline(
            '',
            $pu_ht,
            $command->getNbrPax(),
            $product->tva_tx,
            0,
            0,
            $product->id,
            $this->computeDiscounts($product, ($command->getCost() / $command->getNbrPax()))
        );

        return $this;
    }

    /**
     * @param CommandInterface|CreateOrderCommand $command
     * @param int $customerId
     *
     * @return $this
     * @throws Exception
     */
    private function createOrder(CommandInterface $command, $customerId)
    {
        $this->order = new Commande($this->db);
        $this->order->note_public = $command->isCommentPublic() ? $command->getComment() : '';
        $this->order->note_private = $command->isCommentPublic() ? $command->getComment() : '';
        $this->order->socid = $customerId;
        $this->order->cond_reglement_id = 1; // reception
        $this->order->mode_reglement_id = 2; //virement
        $this->order->demand_reason_id = $command->getOrigine();
        $this->order->date = time();

        $orderId = $this->order->create($this->user);
        if ($orderId <= 0) {
            throw new Exception('Exception during the order creation ('.$this->order->error.')');
        }

        return $this;
    }

    /**
     * Validate the order
     *
     * @throws Exception
     */
    private function validateOrder()
    {
        if ($this->order->valid($this->user) < 0) {
            throw new Exception('Validation of order failed');
        }

        return $this;
    }

    /**
     * Add sales contact
     */
    private function addContacts()
    {
        $this->order->add_contact($this->user->id, 91, 'internal');
        return $this;
    }

    /**
     * @return Societe
     */
    public function getCustomer()
    {
        return $this->societe;
    }

    /**
     * @return Commande
     */
    public function getOrder()
    {
        return $this->order;
    }

}