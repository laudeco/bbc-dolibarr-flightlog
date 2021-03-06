<?php

require_once __DIR__ . '/AbstractValidator.php';
require_once __DIR__ . '/../class/bbcvols.class.php';

/**
 * Validates a flight
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightValidator extends AbstractValidator
{
    /**
     * @var Product
     */
    private $defaultService;

    /**
     * @var int
     */
    private $userId;

    /**
     * @inheritDoc
     */
    public function __construct(Translate $langs, DoliDB $db, $defaultServiceId, $userId)
    {
        parent::__construct($langs, $db);
        $this->fetchService($defaultServiceId);
        $this->userId = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($flight, $context = [])
    {
        if (!($flight instanceof Bbcvols)) {
            throw new \InvalidArgumentException('Flight validator only accepts a flight');
        }

        $this->valid = true;

        $this
            ->checkFlightType($flight)
            ->checkBalloon($flight)
            ->checkFlightDate($flight)
            ->checkFlightInformation($flight)
            ->checkPassengersInformation($flight)
            ->checkInstructionInformation($flight, $context)
            ->checkBillingInformation($flight, $context)
            ->checkKilometers($flight)
            ->checkOrderInformation($flight);

        return $this->valid;
    }

    /**
     * @param array $context
     *
     * @return bool
     */
    private function isGroupedFlight($context)
    {
        return key_exists('grouped_flight', $context) && !empty($context['grouped_flight']);
    }

    /**
     * @param string $hour
     *
     * @return bool
     */
    private function isHourValid($hour)
    {
        $patern = '(([0-9]{4})|([0-9]{2}:[0-9]{2}(:[0-9]{2})?))';
        return !(preg_match($patern, $hour) == 0 || (strlen($hour) != 4 && strlen($hour) != 8 && strlen($hour) != 5));
    }

    /**
     * @param $defaultServiceId
     */
    private function fetchService($defaultServiceId)
    {
        $this->defaultService = new Product($this->db);
        $this->defaultService->fetch($defaultServiceId);
    }

    /**
     * Returns the minimum price.
     *
     * @return int
     */
    private function getMinPrice()
    {
        if ($this->defaultService->price_base_type == 'TTC') {
            return $this->defaultService->price_min;
        }
        return $this->defaultService->price_min_ttc;
    }

    /**
     * @param Bbcvols $vol
     * @param array $context
     *
     * @return $this
     */
    private function checkBillingInformation($vol, $context)
    {
        if ($vol->isLinkedToOrder() || $vol->isBilled()) {
            return $this;
        }

        if (
            (
                !$this->isGroupedFlight($context)
                || $vol->getPilotId() === $vol->getFkReceiver()
            )
            && $vol->getFlightType()->isBillingRequired()
            && $vol->isFree()
            && $this->userId === $vol->getFkReceiver()
        ) {
            $this->addError('cost', 'Erreur ce type de vol doit être payant.');
        }

        if ($vol->getFlightType()->isBillingRequired() && !$vol->hasReceiver()) {
            $this->addError('cost',
                'Erreur ce type de vol doit être payant, mais personne n\'a été signalé comme recepteur d\'argent.');
        }

        if (!$this->isGroupedFlight($context) && $this->userId === $vol->getFkReceiver() && $vol->getFlightType()->isBillingRequired() && ($vol->getAmountPerPassenger()) < $this->getMinPrice()) {
            $this->addError('cost',
                sprintf('Le montant demandé pour ce vol n\'est pas suffisant un minimum de %s euros est demandé',
                    $this->getMinPrice()));
        }

        return $this;
    }

    /**
     * @param Bbcvols $vol
     * @param array $context
     *
     * @return $this
     */
    private function checkInstructionInformation($vol, $context)
    {
        if ($vol->isInstruction()) {
            if ($vol->getPilotId() === $vol->getOrganisatorId()) {
                $this->addError('organisator',
                    'l\'organisateur d\'un vol d\'instruction doit être l\'instructeur et non le pilote');
            }

            if ($this->isGroupedFlight($context)) {
                $this->addError('alone', "Le vol d'instruction est un vol à un seul ballon.");
            }
        }

        return $this;
    }

    /**
     * @param Bbcvols $vol
     *
     * @return $this
     */
    private function checkPassengersInformation($vol)
    {
        if (!is_numeric($vol->nbrPax) || (int)$vol->nbrPax < 0) {
            $this->addError('nbrPax', 'Erreur le nombre de passager est un nombre négatif.');
        }

        if (!$vol->mustHavePax()) {
            return $this;
        }

        if (!$vol->hasPax()) {
            $this->addError('nbrPax', 'Erreur ce type de vol doit etre fait avec des passagers.');
        }

        if (empty(trim($vol->getPassengerNames()))) {
            $this->addError('passenger_names', 'Le nom des passagers est obligatoire.');
        }

        $passengers = explode(';', $vol->getPassengerNames());
        if (count($passengers) !== $vol->getNumberOfPassengers()) {
            $this->addError('passenger_names',
                'Le nombre de noms des passagers doit être égale au nombre de passagers.');
        }

        return $this;
    }

    /**
     * @param Bbcvols $vol
     *
     * @return $this
     */
    private function checkKilometers($vol)
    {
        if ($vol->hasKilometers() && !$vol->getKilometers() > 0) {
            $this->addError('kilometers',
                'Les kilometres doivent être un nombre positif');
        }

        if ($vol->hasKilometers() && !$vol->hasKilometersDescription()) {
            $this->addError('justif_kilometers',
                'Vous essayez d\'encoder des kilometres sans justificatif.');
        }

        return $this;
    }

    /**
     * @param Bbcvols $vol
     *
     * @return $this
     */
    private function checkFlightInformation($vol)
    {
        if(empty($vol->getHeureD()) || $vol->getHeureD() === '0000'){
            $this->addError('heureD', "L'heure depart est vide");
        }
        if (!$this->isHourValid($vol->heureD)) {
            $this->addError('heureD', "L'heure depart n'est pas correcte");
        }
        if(empty($vol->getHeureA()) || $vol->getHeureA() === '0000'){
            $this->addError('heureD', "L'heure d'arrivée est vide");
        }

        if (!$this->isHourValid($vol->heureA)) {
            $this->addError('heureA', 'L\'heure d\'arrivee n\'est pas correcte');
        }

        if (empty($this->errors) && ($vol->heureA - $vol->heureD) <= 0) {
            $this->addError('heures', 'L\'heure de depart est plus grande  que l\'heure d\'arrivee');
        }

        if (empty($vol->lieuD)) {
            $this->addError('lieuD', 'Le lieu de départ est vide');
        }

        if (empty($vol->lieuA)) {
            $this->addError('lieuA', 'Le lieu d\'arrivée est vide');
        }

        return $this;
    }

    /**
     * @param Bbcvols $vol
     *
     * @return $this
     */
    private function checkOrderInformation(Bbcvols $vol)
    {
        if (!$vol->isLinkedToOrder()) {
            return $this;
        }

        $totalPassenegrs = 0;
        foreach ($vol->getOrderIds() as $orderId => $nbrPassengers) {
            if ($nbrPassengers <= 0) {
                $this->addError('order_id', 'Le nombre de passager par commande doit être >= 0.');
            }

            $totalPassenegrs += (int)$nbrPassengers;
        }

        if (!$vol->hasReceiver() && $totalPassenegrs !== (int)$vol->getNumberOfPassengers()) {
            $this->addError('nbrPax', 'Le nombre de passagers ne correspond pas au nombre entré sur les commandes');
        }

        return $this;
    }

    /**
     * @param Bbcvols $flight
     *
     * @return $this
     */
    private function checkFlightDate(Bbcvols $flight)
    {
        $flightDate = $flight->getDate();
        $flightDate->setTime(0, 0, 0);

        if ($flightDate > new DateTimeImmutable()) {
            $this->addError('date', 'La date est plus grande que la date d\'aujourd\'hui');
        }

        return $this;
    }

    private function checkBalloon(Bbcvols $flight)
    {
        if($flight->getBBCBallonsIdBBCBallons() > 0){
            return $this;
        }

        $this->addError('balloon', 'Le ballon est manquant');

        return $this;
    }

    private function checkFlightType(Bbcvols $flight)
    {
        if ($flight->getFkType() > 0) {
            return $this;
        }

        $this->addError('type', 'Le type de vol est manquant.');

        return $this;
    }
}