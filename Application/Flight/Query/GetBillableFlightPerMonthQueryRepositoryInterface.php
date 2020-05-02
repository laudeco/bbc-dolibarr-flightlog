<?php


namespace FlightLog\Application\Flight\Query;


use FlightLog\Application\Flight\ViewModel\BillableFlightByYearMonth;

interface GetBillableFlightPerMonthQueryRepositoryInterface
{

    /**
     * @return BillableFlightByYearMonth
     */
    public function query();

}