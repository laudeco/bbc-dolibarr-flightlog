<?php


namespace FlightLog\Infrastructure\Damage\Repository;

use FlightLog\Domain\Damage\FlightDamage;
use FlightLog\Infrastructure\Common\Repository\AbstractDomainRepository;

final class FlightDamageRepository extends AbstractDomainRepository
{

    /**
     * @param \DoliDB $db
     */
    public function __construct(\DoliDB $db)
    {
        parent::__construct($db, 'bbc_flight_damages');
    }

    /**
     * @param FlightDamage $flightDamage
     *
     * @throws \Exception
     */
    public function save(FlightDamage $flightDamage){
        $this->write([
            'flight_id' => $flightDamage->getFlightId()->getId(),
            'billed' => $flightDamage->isBilled(),
            'amount' => $flightDamage->amount()->getValue(),
        ]);
    }

}