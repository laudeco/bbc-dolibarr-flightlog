<?php
/**
 *
 */

/**
 * YearGraphicalData class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class YearGraphicalData
{

    /**
     * @var int
     */
    private $year;

    /**
     * @var GraphicalValueType[]
     */
    private $graphData;

    /**
     *
     * @param int $year
     */
    public function __construct($year)
    {
        $this->year = $year;
        $this->graphData = [];
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param GraphicalType $graphicalType
     */
    public function addType(GraphicalType $graphicalType)
    {

        $this->graphData[$graphicalType->getId()] = new GraphicalValueType($graphicalType,
            new GraphicalValue(0, $this->year, $graphicalType->getId()));
    }

    /**
     * All the types held by this year, in the order they were added.
     *
     * @return GraphicalType[]
     */
    public function getTypes()
    {
        $types = [];

        foreach ($this->graphData as $data) {
            $types[] = $data->getType();
        }

        return $types;
    }

    /**
     * @param GraphicalValue $graphicalValue
     */
    public function addGraphicalValue(GraphicalValue $graphicalValue)
    {
        if (!isset($this->graphData[$graphicalValue->getType()])) {
            return;
        }

        $this->graphData[$graphicalValue->getType()]->addValue($graphicalValue);
    }

    /**
     * One value per type, in the same order as getTypes().
     *
     * @return array
     */
    public function export()
    {
        $result = [$this->year];

        foreach ($this->graphData as $data) {
            $result[] = $data->getValue();
        }

        return $result;
    }
}