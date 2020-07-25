<?php

namespace FlightLog\Domain\Damage\ValueObject;

final class DamageLabel
{

    /**
     * @var string
     */
    private $label;

    public function __construct($label)
    {
        $this->label = $label;
    }

    public function value(){
        return $this->label;
    }

}