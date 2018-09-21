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
     * @inheritDoc
     */
    public function __construct(Translate $langs, DoliDB $db, $defaultServiceId)
    {
        parent::__construct($langs, $db);
        $this->fetchService($defaultServiceId);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($vol, $context = [])
    {
        if (!($vol instanceof Bbcvols)) {
            throw new \InvalidArgumentException('Flight validator only accepts a flight');
        }

        $this->valid = true;

        $this->checkFlightInformation($vol)
            ->checkPassengersInformation($vol)
            ->checkInstructionInformation($vol, $context)
            ->checkBillingInformation($vol, $context)
            ->checkKilometers($vol);

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
        return !(preg_match($patern, $hour) == 0 || (strlen($hour) != 4 && strlen($hour) != 8));
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
     * @param array   $context
     *
     * @return $this
     */
    private function checkBillingInformation($vol, $context)
    {
        if($vol->isLinkedToOrder()){
            return $this;
        }

        if ($this->isGroupedFlight($context) && $vol->getFlightType()->isBillingRequired() && $vol->isFree()) {
            $this->addError('cost', 'Erreur ce type de vol doit être payant.');
        }

        if ($vol->getFlightType()->isBillingRequired() && !$vol->hasReceiver()) {
            $this->addError('cost',
                'Erreur ce type de vol doit être payant, mais personne n\'a été signalé comme recepteur d\'argent.');
        }

        if (!$this->isGroupedFlight($context) && $vol->getFlightType()->isBillingRequired() && ($vol->getAmountPerPassenger()) < $this->getMinPrice()) {
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
        if (!is_numeric($vol->nbrPax) || (int) $vol->nbrPax < 0) {
            $this->addError('nbrPax', 'Erreur le nombre de passager est un nombre négatif.');
        }

        if ($vol->mustHavePax()) {
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
        if (!$this->isHourValid($vol->heureD)) {
            $this->addError('heureD', "L'heure depart n'est pas correcte'");
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
}