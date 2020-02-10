<?php


namespace FlightLog\Infrastructure\Damage\Query\Repository;

use FlightLog\Application\Damage\Query\GetPilotDamagesQueryRepositoryInterface;
use FlightLog\Application\Damage\ViewModel\TotalDamage;

/**
 * Gets the flight damages for one year.
 *
 * @package FlightLog\Infrastructure\Damage\Query\Repository
 */
final class GetPilotDamagesQueryRepository implements GetPilotDamagesQueryRepositoryInterface
{

    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @param int $year
     *
     * @return TotalDamage[]|\Generator
     */
    public function query($year)
    {
        $sql = 'SELECT damage.amount as amount, damage.billed as billed, damage.author_id as author, author.firstname as author_name';
        $sql.=' FROM '.MAIN_DB_PREFIX.'bbc_flight_damages as damage';
        $sql.=' INNER JOIN '.MAIN_DB_PREFIX.'bbc_vols as flight ON flight.rowid = damage.flight_id';
        $sql.=' INNER JOIN '.MAIN_DB_PREFIX.'user as author ON author.rowid = damage.author_id';
        $sql.=' WHERE YEAR(flight.date) = '.$this->db->escape($year);

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            if ($num) {
                for($i = 0; $i < $num ; $i++) {
                    $properties = $this->db->fetch_array($resql);
                    $damage = TotalDamage::fromArray($properties);
                    yield $damage;
                }
            }
        }
    }
}