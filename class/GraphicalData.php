<?php
/**
 *
 */

/**
 * GraphicalData class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class GraphicalData
{

    /**
     * @var YearGraphicalData[]
     */
    private $data;

    /**
     * GraphicalData constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @param YearGraphicalData $pieceData
     */
    public function addData(YearGraphicalData $pieceData)
    {
        $this->data[$pieceData->getYear()] = $pieceData;
    }

    /**
     * @param int            $year
     * @param GraphicalValue $value
     *
     * @throws Exception
     */
    public function addValue($year, GraphicalValue $value)
    {
        if (!isset($this->data[$year])) {
            throw new \Exception("Year is not defined");
        }

        $this->data[$year]->addGraphicalValue($value);
    }

    /**
     * The types of the graph, in the same order as the values of each exported year.
     *
     * @return GraphicalType[]
     */
    public function getTypes()
    {
        foreach ($this->data as $year) {
            return $year->getTypes();
        }

        return [];
    }

    /**
     * @return array
     */
    public function export()
    {
        $result = [];

        foreach ($this->data as $year) {
            $result[] = $year->export();
        }

        return $result;
    }

}