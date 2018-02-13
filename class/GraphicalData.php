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
        if(!isset($this->data[$year])){
            throw new \Exception("Year is not defined");
        }

        $this->data[$year]->addGraphicalValue($value);
    }

    /**
     * @return array
     */
    public function export()
    {
        $result = [];

        foreach($this->data as $year){
            $result[] = $year->export();
        }

        return $result;
    }

}