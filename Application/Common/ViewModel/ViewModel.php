<?php


namespace FlightLog\Application\Common\ViewModel;


abstract class ViewModel
{

    /**
     * @param array $values
     *
     * @return mixed
     */
    public abstract static function fromArray(array $values);

}