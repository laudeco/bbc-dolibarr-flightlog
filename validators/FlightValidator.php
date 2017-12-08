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

        if (!$this->isHourValid($vol->heureD)) {
            $this->addError('heureD', "L'heure depart n'est pas correcte'");
        }

        if (!$this->isHourValid($vol->heureA)) {
            $this->addError('heureA', 'L\'heure d\'arrivee n\'est pas correcte');
        }

        if (empty($this->errors) && ($vol->heureA - $vol->heureD) <= 0) {
            $this->addError('heures', 'L\'heure de depart est plus grande  que l\'heure d\'arrivee');
        }

        if(empty($vol->lieuD)){
            $this->addError('lieuD', 'Le lieu de départ est vide');
        }

        if(empty($vol->lieuA)){
            $this->addError('lieuA', 'Le lieu d\'arrivée est vide');
        }

        // PAX
        if (!is_numeric($vol->nbrPax) || (int)$vol->nbrPax < 0) {
            $this->addError('nbrPax', 'Erreur le nombre de passager est un nombre négatif.');
        }

        if ($vol->mustHavePax() && !$vol->hasPax()) {
            $this->addError('nbrPax', 'Erreur ce type de vol doit etre fait avec des passagers.');
        }

        // verification billing
        if ($this->isGroupedFlight($context) && $vol->getFlightType()->isBillingRequired() && $vol->isFree()) {
            $this->addError('cost', 'Erreur ce type de vol doit être payant.');
        }

        if ($vol->getFlightType()->isBillingRequired() && !$vol->hasReceiver()) {
            $this->addError('cost',
                'Erreur ce type de vol doit être payant, mais personne n\'a été signalé comme recepteur d\'argent.');
        }

        if($vol->getFlightType()->isBillingRequired() && ($vol->getAmountPerPassenger()) < $this->getMinPrice()){
            $this->addError('cost',
                sprintf('Le montant demandé pour ce vol n\'est pas suffisant un minimum de %s euros est demandé', $this->getMinPrice()));
        }

        //Kilometers
        if($vol->hasKilometers() && !$vol->getKilometers() > 0){
            $this->addError('kilometers',
                'Les kilometres doivent être un nombre positif');
        }

        if($vol->hasKilometers() && !$vol->hasKilometersDescription()){
            $this->addError('justif_kilometers',
                'Vous essayez d\'encoder des kilometres sans justificatif.');
        }

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
        $patern = '#[0-9]{4}#';
        return !(preg_match($patern, $hour) == 0 || strlen($hour) != 4);
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
    public function getMinPrice(){
        if ($this->defaultService->price_base_type == 'TTC') {
            return $this->defaultService->price_min;
        }
        return $this->defaultService->price_min_ttc;
    }
}