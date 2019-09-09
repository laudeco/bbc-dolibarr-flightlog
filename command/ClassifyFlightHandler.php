<?php


namespace flightlog\command;

use Bbcvols;
use CommandHandlerInterface;
use CommandInterface;
use Conf;
use stdClass;
use Translate;
use User;

/**
 * @package flightlog\command
 */
final class ClassifyFlightHandler implements CommandHandlerInterface
{

    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @var Conf
     */
    private $conf;

    /**
     * @var Translate
     */
    private $langs;

    /**
     * @var User
     */
    private $user;

    /**
     * @param \DoliDB $db
     * @param stdClass $conf
     * @param Translate $langs
     * @param User $user
     */
    public function __construct(\DoliDB $db, Conf $conf, Translate $langs, User $user)
    {
        $this->db = $db;
        $this->conf = $conf;
        $this->langs = $langs;
        $this->user = $user;
    }

    /**
     * @param CommandInterface|ClassifyFlight $command
     *
     * @throws \Exception
     */
    public function handle(CommandInterface $command)
    {
        /** @var ClassifyFlight $command */
        
        $projectId = 'NULL';
        if (!empty($command->getProjectId())) {
            $projectId = $command->getProjectId();
        }

        $result = $this->db->query(sprintf('UPDATE %s%s SET fk_project=%s WHERE rowid=%s', MAIN_DB_PREFIX,
            Bbcvols::$table, $projectId, $command->getFlightId()));
        if (!$result) {
            throw new \Exception($this->db->error());
        }
    }
}