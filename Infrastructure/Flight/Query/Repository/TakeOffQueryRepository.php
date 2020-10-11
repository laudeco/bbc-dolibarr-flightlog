<?php


namespace FlightLog\Infrastructure\Flight\Query\Repository;


use FlightLog\Application\Flight\ViewModel\TakeOffPlace;

final class TakeOffQueryRepository
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
     * @param $pilot
     * @return array|TakeOffPlace[]
     */
    public function __invoke($pilot)
    {
        $sql = '
            SELECT 
              llx_bbc_vols.lieuD as place
            
            FROM
              llx_bbc_vols
            
            WHERE 
              fk_type IN (1,2)
              AND YEAR(NOW())-1 <= YEAR(llx_bbc_vols.date)
              -- AND llx_bbc_vols.fk_pilot = '.$pilot.'
            
            GROUP BY
              llx_bbc_vols.lieuD
            
            ORDER BY
              llx_bbc_vols.date DESC
              
            LIMIT 20';

        $resql = $this->db->query($sql);
        if (!$resql) {
            return [];
        }

        $num = $this->db->num_rows($resql);
        if ($num == 0) {
            return [];
        }

        $places = [];

        for($i = 0; $i < $num ; $i++) {
            $properties = $this->db->fetch_array($resql);
            $stat = new TakeOffPlace($properties['place']);
            $places[] = $stat;
        }

        return $places;
    }

}