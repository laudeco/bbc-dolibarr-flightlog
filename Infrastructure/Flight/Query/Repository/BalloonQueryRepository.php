<?php


namespace FlightLog\Infrastructure\Flight\Query\Repository;


use FlightLog\Application\Flight\ViewModel\Balloon;
use FlightLog\Application\Flight\ViewModel\TakeOffPlace;

final class BalloonQueryRepository
{

    /**
     * @var \DoliDB
     */
    private $db;

    public function __construct(\DoliDB $db)
    {
        $this->db = $db;
    }

    public function query($params = []){
        $sql = '
            SELECT 
              balloon.rowid as id,
              balloon.immat as immat,
              count(llx_bbc_vols.rowid) as counter
            FROM
              llx_bbc_ballons as balloon
              LEFT JOIN llx_bbc_vols
                ON llx_bbc_vols.BBC_ballons_idBBC_ballons = balloon.rowid
            
            WHERE 
              balloon.is_disable = false
              AND llx_bbc_vols.fk_pilot = '.$params['pilot'].'
              
            ORDER  BY counter DESC
             
            LIMIT 1';

        $resql = $this->db->query($sql);
        if (!$resql) {
            return new Balloon(0);
        }

        $num = $this->db->num_rows($resql);
        if ($num == 0) {
            return new Balloon(0);
        }

        for($i = 0; $i < $num ; $i++) {
            $properties = $this->db->fetch_array($resql);
            return Balloon::fromArray($properties);
        }

        return new Balloon(0);
    }
}