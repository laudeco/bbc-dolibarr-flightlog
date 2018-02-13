<?php

require_once __DIR__ . '/CommandInterface.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateMonthBillCommand implements CommandInterface
{

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
     * @param int    $billType
     * @param string $publicNote
     * @param string $privateNote
     * @param int    $year
     * @param int    $month
     */
    public function __construct(
        $billType,
        $publicNote,
        $privateNote,
        $year,
        $month
    ) {
        $this->billType = $billType;
        $this->publicNote = $publicNote;
        $this->privateNote = $privateNote;
        $this->year = $year;
        $this->month = $month;
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