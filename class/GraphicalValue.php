<?php
/**
 *
 */

/**
 * GraphicalValue class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class GraphicalValue
{

    /**
     * @var int
     */
    private $value;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $type;

    /**
     * GraphicalValue constructor.
     *
     * @param int $value
     * @param int $year
     * @param int $type
     */
    public function __construct($value, $year, $type)
    {
        $this->value = $value;
        $this->year = $year;
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param GraphicalValue $value
     *
     * @return GraphicalValue
     */
    public function add($value)
    {
        return new GraphicalValue($this->value + $value->getValue(), $this->year, $this->type);
    }

}