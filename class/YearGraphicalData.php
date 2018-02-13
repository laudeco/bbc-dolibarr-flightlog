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
     * @return array
     */
    public function export()
    {
        $result = [$this->year];

        foreach ($this->graphData as $typeId => $data) {
            if(!in_array($typeId, [1,2,3,6])){
                continue;
            }

            $result[] = $data->getValue();
        }

        return $result;
    }
}