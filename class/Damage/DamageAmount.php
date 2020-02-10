<?php


namespace FlightLog\Domain\Damage;


final class DamageAmount
{

    /**
     * @var float
     */
    private $value;

    /**
     * @param float $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}