<?php


namespace FlightLog\Application\Damage\Query;

use FlightLog\Application\Damage\ViewModel\TotalDamage;

interface GetPilotDamagesQueryRepositoryInterface
{

    /**
     * @param int $year
     *
     * @return TotalDamage[]|\Generator
     */
    public function query($year);
}