<?php
/**
 *
 */

/**
 * MoneyReceiver class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class MoneyReceiver
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var int
     */
    private $id;

    /**
     * @param string $name
     * @param string $firstname
     * @param int    $id
     */
    public function __construct($name, $firstname, $id)
    {
        $this->name = $name;
        $this->firstname = $firstname;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->firstname . ' ' . $this->name;
    }

}