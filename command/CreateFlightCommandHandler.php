<?php

require_once __DIR__ . '/../exceptions/FlightNotFoundException.php';
require_once __DIR__ . '/../class/bbcvols.class.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateFlightCommandHandler implements CommandHandlerInterface
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
     * @var
     */
    private $validator;

    /**
     * @param DoliDB          $db
     * @param stdClass        $conf
     * @param User            $user
     * @param Translate       $langs
     * @param FlightValidator $validator
     */
    public function __construct(
        $db,
        $conf,
        $user,
        $langs,
        FlightValidator $validator
    ) {
        $this->db = $db;
        $this->conf = $conf->global;
        $this->user = $user;
        $this->langs = $langs;
        $this->validator = $validator;
    }

    /**
     * @param CreateFlightCommand|CommandInterface $command
     *
     * @return Bbcvols
     * @throws Exception
     */
    public function handle(CommandInterface $command)
    {
        $vol = new Bbcvols($this->db);
        $vol->date = $command->getDate()->getTimestamp();
        $vol->lieuD = $command->getLieuD();
        $vol->lieuA =$command->getLieuA();
        $vol->heureD = $command->getHeureD()->format('Hm');
        $vol->heureA = $command->getHeureA()->format('Hm');
        $vol->BBC_ballons_idBBC_ballons = $command->getBBCBallonsIdBBCBallons();
        $vol->nbrPax = $command->getNbrPax();
        $vol->remarque = $command->getRemarque();
        $vol->incidents = $command->getIncidents();
        $vol->fk_type = $command->getFkType();
        $vol->fk_pilot =$command->getFkPilot();
        $vol->fk_organisateur = $command->getFkOrganisateur();
        $vol->kilometers = $command->getKilometers();
        $vol->cost = $command->getCost();
        $vol->fk_receiver = $command->getFkReceiver();
        $vol->justif_kilometers = $command->getJustifKilometers();
        $vol->setPassengerNames($command->getPassengerNames());
        foreach($command->getOrderIds() as $orderId => $nbrPassengers){
            $vol->addOrderId($orderId, $nbrPassengers);
        }

        if (!$this->validator->isValid($vol, $_REQUEST)) {
            throw new Exception();
        }

        if(!$vol->isBillingRequired() || ($vol->isLinkedToOrder() && $vol->hasReceiver())){
            $vol->is_facture = true;
        }

        $result = $vol->create($this->user);
        if($result <= 0){
            throw new Exception();
        }

        $this->handleOrder($vol);

        return $vol;
    }

    /**
     * Handles the order of a flight.
     *
     * @param Bbcvols $flight
     *
     * @throws Exception
     */
    private function handleOrder($flight)
    {
        $flight->fetch($flight->id);

        if(!$flight->isLinkedToOrder()){
            return;
        }

        foreach($flight->getOrders() as $currentOrder){
            $currentOrder->add_object_linked('flightlog_bbcvols', $flight->getId());
            $currentOrder->fetch_lines();

            $qtyOrder = 0;
            /** @var OrderLine $currentOrderLine */
            foreach($currentOrder->lines as $currentOrderLine){
                $qtyOrder += (int)$currentOrderLine->qty;
            }

            $passangersCount = $this->numberOfPassengersLinkedToOrder($currentOrder->id);

            if($passangersCount < $qtyOrder){
                continue;
            }

            if($currentOrder->statut == Commande::STATUS_VALIDATED){
                $currentOrder->cloture($this->user);
            }
        }
    }

    /**
     * @param int $orderId
     *
     * @return int
     */
    private function numberOfPassengersLinkedToOrder($orderId)
    {
        $sql = sprintf('SELECT SUM(nbr_passengers) as total FROM `llx_bbc_vols_orders` WHERE order_id = %s', $orderId);

        $resql = $this->db->query($sql);

        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $result = $this->db->fetch_array($resql);
                    return $result['total'];
                }
            }
        }

        return 0;
    }
}