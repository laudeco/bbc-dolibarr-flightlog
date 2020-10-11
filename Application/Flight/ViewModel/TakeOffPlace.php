<?php


namespace FlightLog\Application\Flight\ViewModel;


final class TakeOffPlace
{

    /**
     * @var string
     */
    private $place;

    public function __construct($place)
    {
        $this->place = $place;
    }

    /**
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    public function isValid()
    {
        return preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $this->place) === false;
    }

}