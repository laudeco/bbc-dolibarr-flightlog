<?php
/**
 *
 */

use UserGroup;

/**
 * PilotService class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class PilotService
{

    /**
     * @var DoliDB
     */
    private $db;

    /**
     * PilotService constructor.
     *
     * @param DoliDB $db
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isPilot($userId)
    {
        $pilot = new User($this->db);
        $pilot->fetch($userId);
        $pilot->getrights("flightLog");

        return isset($pilot->rights->flightLog->vol->add) && $pilot->rights->flightLog->vol->add;
    }
}