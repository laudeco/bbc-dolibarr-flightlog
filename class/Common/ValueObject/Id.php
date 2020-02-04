<?php


namespace FlightLog\Domain\Common\ValueObject;


trait Id
{
    /**
     * @var int
     */
    private $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     *
     * @return Id
     */
    public static function create($id){
        return new self($id);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}