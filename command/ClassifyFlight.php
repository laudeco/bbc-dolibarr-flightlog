<?php

namespace flightlog\command;

use CommandInterface;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
final class ClassifyFlight implements CommandInterface
{

    /**
     * @var int
     */
    private $flightId;

    /**
     * @var int
     */
    private $projectId;

    /**
     * @param int $flightId
     * @param int $projectId
     */
    public function __construct($flightId, $projectId)
    {
        $this->flightId = (int)$flightId;
        $this->projectId = (int)$projectId;
    }

    /**
     * @return int
     */
    public function getFlightId(): int
    {
        return $this->flightId;
    }

    /**
     * @return int
     */
    public function getProjectId(): int
    {
        return $this->projectId;
    }

}