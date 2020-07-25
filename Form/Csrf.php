<?php


namespace flightlog\form;


final class Csrf extends Hidden
{

    public function __construct($name, array $options = [])
    {
        parent::__construct($name, $options);
        $this->setValue(newToken());
    }


}