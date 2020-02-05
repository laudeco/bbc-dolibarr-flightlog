<?php


namespace FlightLog\Infrastructure\Damage\Query\Repository;


use FlightLog\Application\Damage\Query\GetDamagesForFlightQueryRepositoryInterface;
use FlightLog\Application\Damage\ViewModel\Damage;

final class GetDamagesForFlightQueryRepository implements GetDamagesForFlightQueryRepositoryInterface
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
     * @param int $flightId
     *
     * @return Damage[]|array
     */
    public function __invoke($flightId)
    {
        $sql = 'SELECT damage.amount, author.login as author_name';
        $sql.=' FROM '.MAIN_DB_PREFIX.'bbc_flight_damages as damage';
        $sql.=' INNER JOIN '.MAIN_DB_PREFIX.'user as author ON author.rowid = damage.author_id';
        $sql.=' WHERE damage.flight_id = '.$this->db->escape($flightId);

        $damages = [];

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            if ($num) {
                for($i = 0; $i < $num ; $i++) {
                    $properties = $this->db->fetch_array($resql);
                    $damages[] = Damage::fromArray($properties);
                }
            }
        }

        return $damages;
    }
}