<?php
/**
 *
 */

/**
 * GraphicalValueType class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class GraphicalValueType
{

    /**
     * @var GraphicalType
     */
    private $types;

    /**
     * @var GraphicalValue
     */
    private $counter;

    /**
     * GraphicalValueType constructor.
     *
     * @param GraphicalType  $types
     * @param GraphicalValue $graphicalValue
     */
    public function __construct(GraphicalType $types, GraphicalValue $graphicalValue)
    {
        $this->types = $types;
        $this->counter = $graphicalValue;
    }

    /**
     * @param GraphicalValue $value
     */
    public function addValue(GraphicalValue $value) {
        $this->counter = $this->counter->add($value);
    }

    /**
     * @return int
     */
    public function getValue() {
        return $this->counter->getValue();
    }

}