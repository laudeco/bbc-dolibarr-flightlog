<?php
/**
 *
 */

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class MonthBillCollection
{

    /**
     * @var MonthlyFlightBill[]
     */
    private $flights;

    public function __construct()
    {
        $this->flights = [];
    }

    /**
     * @param MoneyReceiver $receiver
     * @param Bbcvols       $flight
     */
    public function addFlight(MoneyReceiver $receiver, Bbcvols $flight)
    {
        if (!isset($this->flights[$receiver->getId()])) {
            $this->flights[$receiver->getId()] = new MonthlyFlightBill($receiver);
        }

        $this->flights[$receiver->getId()]->addFlight($flight);
    }

    /**
     * @return array|MonthlyFlightBill[]
     */
    public function getFlights()
    {
        return $this->flights;
    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->flights);
    }


}