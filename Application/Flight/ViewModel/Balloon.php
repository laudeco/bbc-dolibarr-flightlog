<?php


namespace FlightLog\Application\Flight\ViewModel;


use FlightLog\Application\Common\ViewModel\ViewModel;

final class Balloon extends ViewModel
{

    /**
     * @var int
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public static function fromArray(array $values)
    {
        return new self($values['id']);
    }
}