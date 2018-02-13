<?php
/**
 *
 */

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class MonthlyFlightBill
{

    /**
     * @var Bbcvols[]|array
     */
    private $flights;

    /**
     * @var MoneyReceiver
     */
    private $moneyReceiver;

    /**
     * @param MoneyReceiver $moneyReceiver
     */
    public function __construct(MoneyReceiver $moneyReceiver)
    {
        $this->moneyReceiver = $moneyReceiver;
    }

    /**
     * @param Bbcvols $flight
     */
    public function addFlight(Bbcvols $flight)
    {
        $this->flights[] = $flight;
    }

    /**
     * @return string
     */
    public function getReceiver()
    {
        return $this->moneyReceiver->getDisplayName();
    }

    /**
     * @return int
     */
    public function getFlightsCount()
    {
        return count($this->flights);
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->flights as $flight) {
            $total += $flight->cost;
        }
        return $total;
    }

    /**
     * @return float
     */
    public function getAverageByPax()
    {
        $total = $this->getTotal();
        $numberOfPax = $this->getNumberOfPassagers();

        return $total / $numberOfPax;
    }

    /**
     * @return int
     */
    private function getNumberOfPassagers()
    {
        $total = 0;
        foreach ($this->flights as $flight) {
            $total += $flight->nbrPax;
        }
        return $total;
    }

    /**
     * @return int
     */
    public function getReceiverId()
    {
        return $this->moneyReceiver->getId();
    }

    /**
     * @return array|Bbcvols[]
     */
    public function getFlights()
    {
        return $this->flights;
    }


}