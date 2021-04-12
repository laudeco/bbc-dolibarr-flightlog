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
            rowid as id,
            pilot.end_medical_date as medical_end_date
        FROM llx_user
        LEFT JOIN llx_bbc_pilots as pilot
            ON pilot.user_id = rowid
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

    public function byId($id){
        $sql = sprintf('SELECT 
            lastname as name,
            firstname as firstname,
            email as email,
            rowid as id,
            pilot.end_medical_date as medical_end_date
        FROM llx_user
        LEFT JOIN llx_bbc_pilots as pilot
            ON pilot.user_id = rowid
        WHERE  statut = 1 
        AND firstname != \'\' 
        AND employee = 1
        AND pilot.user_id = %s', $id);

        $resql = $this->db->query($sql);
        if (!$resql) {
            return Pilot::fromArray(['id' => $id]);
        }

        $num = $this->db->num_rows($resql);
        if ($num === 0) {
            return Pilot::fromArray(['id' => $id]);
        }

        for($i = 0; $i < $num ; $i++) {
            return Pilot::fromArray($this->db->fetch_array($resql));
        }
        return Pilot::fromArray(['id' => $id]);
    }

}