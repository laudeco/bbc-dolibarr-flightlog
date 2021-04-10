<?php


namespace FlightLog\Application\Pilot\ViewModel;


use FlightLog\Application\Common\ViewModel\ViewModel;

final class Pilot extends ViewModel
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $email;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public static function fromArray(array $values)
    {
        $pilot = new self();

        $pilot->id = $values['id'];
        $pilot->name = $values['name'];
        $pilot->firstname = $values['firstname'];
        $pilot->email = $values['email'];

        return $pilot;
    }
}