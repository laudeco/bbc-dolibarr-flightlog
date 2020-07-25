<?php


namespace FlightLog\Domain\Damage;


use FlightCost;

final class FlightDamageCount
{

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $label;

    public function __construct($label, $amount)
    {
        $this->label = $label;

        $this->amount = $amount;
    }


    /**
     * @return FlightCost
     */
    public function getCost()
    {
        return new FlightCost($this->amount);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}