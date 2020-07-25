<?php


namespace FlightLog\Domain\Damage;

use FlightCost;

final class FlightInvoicedDamageCount
{

    /**
     * @var string
     */
    private $label;

    /**
     * @var float
     */
    private $amount;

    public function __construct($label, $amount)
    {
        $this->label = $label;

        if ($amount > 0) {
            $amount *= -1;
        }

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