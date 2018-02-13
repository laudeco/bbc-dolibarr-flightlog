<?php

require_once __DIR__ . '/../class/billing/monthly/MonthlyFlightBill.php';
require_once __DIR__ . '/CommandInterface.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateReceiverMonthBillCommand implements CommandInterface
{
    /**
     * @var MonthlyFlightBill
     */
    private $monthlyFlightBill;

    /**
     * @var int
     */
    private $billType;

    /**
     * @var string
     */
    private $publicNote;

    /**
     * @var string
     */
    private $privateNote;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @param MonthlyFlightBill $monthlyFlightBill
     * @param int               $billType
     * @param string            $publicNote
     * @param string            $privateNote
     * @param int               $year
     * @param int               $month
     */
    public function __construct(
        MonthlyFlightBill $monthlyFlightBill,
        $billType,
        $publicNote,
        $privateNote,
        $year,
        $month
    ) {
        $this->monthlyFlightBill = $monthlyFlightBill;
        $this->billType = $billType;
        $this->publicNote = $publicNote;
        $this->privateNote = $privateNote;
        $this->year = $year;
        $this->month = $month;
    }


    /**
     * @return int
     */
    public function getReceiverId()
    {
        return $this->monthlyFlightBill->getReceiverId();
    }

    /**
     * @return array|Bbcvols[]
     */
    public function getFlights()
    {
        return $this->monthlyFlightBill->getFlights();
    }

    /**
     * @return MonthlyFlightBill
     */
    public function getMonthlyFlightBill()
    {
        return $this->monthlyFlightBill;
    }

    /**
     * @return int
     */
    public function getBillType()
    {
        return $this->billType;
    }

    /**
     * @return string
     */
    public function getPublicNote()
    {
        return $this->publicNote;
    }

    /**
     * @return string
     */
    public function getPrivateNote()
    {
        return $this->privateNote;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }
}