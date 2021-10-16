<?php


namespace FlightLog\Application\Damage\Command;


final class CreateDamageCommand
{

    /**
     * @var int|null
     */
    private $flightId;

    /**
     * @var int
     */
    private $authorId;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $billId;

    public function __construct($authorId)
    {
        $this->authorId = $authorId;

        $this->billId = -1;
        $this->amount = 1;
    }

    public static function create()
    {
        return new self(0);
    }

    /**
     * @return int|null
     */
    public function getFlightId()
    {
        return $this->flightId;
    }

    /**
     * @param int $flightId
     */
    public function setFlightId($flightId)
    {
        $this->flightId = $flightId;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @param int $authorId
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getBillId()
    {
        return $this->billId;
    }

    /**
     * @param int $billId
     */
    public function setBillId($billId)
    {
        $this->billId = $billId;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

}