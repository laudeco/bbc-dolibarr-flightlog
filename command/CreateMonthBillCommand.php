<?php

require_once __DIR__.'/CommandInterface.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateMonthBillCommand implements CommandInterface
{
    /**
     * @var int
     */
    private $billingType;

    /**
     * @var int
     */
    private $billType;

    /**
     * @var int
     */
    private $billingCondition;

    /**
     * @var int
     */
    private $modelDocument;

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
     * @param int    $billingType
     * @param int    $billType
     * @param int    $billingCondition
     * @param int    $modelDocument
     * @param string $publicNote
     * @param string $privateNote
     * @param int    $year
     * @param int    $month
     */
    public function __construct(
        $billingType,
        $billType,
        $billingCondition,
        $modelDocument,
        $publicNote,
        $privateNote,
        $year,
        $month
    ) {
        $this->billingType = $billingType;
        $this->billType = $billType;
        $this->billingCondition = $billingCondition;
        $this->modelDocument = $modelDocument;
        $this->publicNote = $publicNote;
        $this->privateNote = $privateNote;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return int
     */
    public function getBillingType()
    {
        return $this->billingType;
    }

    /**
     * @return int
     */
    public function getBillType()
    {
        return $this->billType;
    }

    /**
     * @return int
     */
    public function getBillingCondition()
    {
        return $this->billingCondition;
    }

    /**
     * @return int
     */
    public function getModelDocument()
    {
        return $this->modelDocument;
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