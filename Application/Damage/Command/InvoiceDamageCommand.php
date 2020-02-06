<?php


namespace FlightLog\Application\Damage\Command;


final class InvoiceDamageCommand
{
    /**
     * @var int
     */
    private $damageId;

    /**
     * @param int $damageId
     */
    public function __construct($damageId)
    {
        $this->damageId = $damageId;
    }

    /**
     * @return int
     */
    public function getDamageId()
    {
        return $this->damageId;
    }
}