<?php


namespace FlightLog\Application\Damage\Query;


use FlightLog\Application\Damage\ViewModel\Damage;

interface GetDamagesForFlightQueryRepositoryInterface
{

    /**
     * @param int $flightId
     *
     * @return Damage[]|array
     */
    public function __invoke($flightId);
}