<?php


namespace FlightLog\Infrastructure\Pilot\Query\Repository;


use FlightLog\Application\Pilot\ViewModel\Pilot;

final class PilotQueryRepository
{
    /**
     * @var \DoliDB
     */
    private $db;

    public function __construct(\DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * @return array|Pilot[]
     */
    public function query():array{
        $sql = sprintf('SELECT 
            lastname as name,
            firstname as firstname,
            email as email,
            rowid as id
        FROM llx_user
        WHERE  statut = 1 
        AND firstname != \'\' 
        AND employee = 1
        ORDER BY lastname, firstname');

        $resql = $this->db->query($sql);
        if (!$resql) {
            return [];
        }

        $num = $this->db->num_rows($resql);
        if ($num === 0) {
            return [];
        }

        $pilots = [];
        for($i = 0; $i < $num ; $i++) {
            $pilots[] = Pilot::fromArray($this->db->fetch_array($resql));
        }

        return $pilots;
    }

}