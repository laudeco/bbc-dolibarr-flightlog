<?php
/**
 *
 */

namespace flightlog\form;

/**
 * FlightTypeSelect class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightTypeSelect extends Select
{
    /**
     * @var \Bbctypes
     */
    private $flightType;

    /**
     * @inheritDoc
     */
    public function __construct($name, array $options = [], \DoliDB $db)
    {
        parent::__construct($name, $options);
        $this->flightType = new \Bbctypes($db);
        $this->flightType->fetchAll(
            'ASC',
            'numero',
            0,
            0,
            [
                "active" => 1
            ]
        );

        $this->buildOptions();

    }

    /**
     * Build the options of the select
     */
    private function buildOptions()
    {
        foreach($this->flightType->getLines() as $currentFlightType){
            $this->addValueOption($currentFlightType->getId(), $currentFlightType->getLabel());
        }
    }


}