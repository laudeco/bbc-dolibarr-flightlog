<?php
/**
 *
 */

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateFlightBillCommand
{
    /**
     * @var int
     */
    private $flightId;

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
    private $bankAccount;

    /**
     * @var int
     */
    private $nbrPax;

    /**
     * @param int    $flightId
     * @param int    $billingType
     * @param int    $billingCondition
     * @param int    $modelDocument
     * @param int    $billType
     * @param string $publicNote
     * @param string $privateNote
     * @param int    $bankAccount
     * @param int    $nbrPax
     */
    public function __construct(
        $flightId,
        $billingType,
        $billingCondition,
        $modelDocument,
        $billType,
        $publicNote,
        $privateNote,
        $bankAccount,
        $nbrPax
    ) {
        if (empty($flightId)) {
            throw new \InvalidArgumentException('Flight id is missing');
        }

        if (empty($billingType)) {
            throw new \InvalidArgumentException('Billing type is missing');
        }

        if (empty($billingCondition)) {
            throw new \InvalidArgumentException('Billing condition is missing');
        }

        if (empty($modelDocument)) {
            throw new \InvalidArgumentException('Model document is missing');
        }

        if(!isset($nbrPax) || !is_numeric($nbrPax) || $nbrPax != (int) $nbrPax){
            throw new \InvalidArgumentException('The number of pax is not correct, an integer is expected');
        }

        $this->flightId = $flightId;
        $this->billingType = $billingType;
        $this->billingCondition = $billingCondition;
        $this->modelDocument = $modelDocument;
        $this->billType = $billType;
        $this->publicNote = $publicNote;
        $this->privateNote = $privateNote;
        $this->bankAccount = $bankAccount;
        $this->nbrPax = $nbrPax;
    }

    /**
     * @return int
     */
    public function getFlightId()
    {
        return $this->flightId;
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
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @return int
     */
    public function getNbrPax()
    {
        return $this->nbrPax;
    }

}