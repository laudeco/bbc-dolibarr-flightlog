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

        // PAX
        if (!is_integer($vol->nbrPax) || $vol->nbrPax < 0) {
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
}