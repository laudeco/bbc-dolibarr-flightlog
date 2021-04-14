<?php


namespace FlightLog\Infrastructure\Pilot\Query\Repository;


use FlightLog\Application\Pilot\ViewModel\Pilot;
use FlightLog\Application\Pilot\ViewModel\PilotFlight;

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
        return $this->filter([]);
    }

    /**
     * @param int $id
     *
     * @return Pilot
     */
    public function byId($id){
        $pilots = $this->filter(['id' => $id]);
        if (empty($pilots)) {
            return Pilot::fromArray(['id' => $id]);
        }

        return $pilots[0];
    }

    private function filter(array $filters): array
    {
        $sql = 'SELECT 
            llx_user.lastname as name,
            llx_user.firstname as firstname,
            llx_user.email as email,
            llx_user.rowid as id,
            
            pilot.pilot_licence_number as licence_number,
            pilot.end_medical_date as medical_end_date,
            pilot.last_training_flight_date as last_training_flight_date,
            pilot.is_pilot_class_a as is_pilot_class_a,
            pilot.is_pilot_class_b as is_pilot_class_b,
            pilot.is_pilot_class_c as is_pilot_class_c,
            pilot.is_pilot_class_d as is_pilot_class_d,
            pilot.is_pilot_gaz as is_pilot_gaz,
            pilot.has_qualif_static as has_qualif_static,
            pilot.has_qualif_night as has_qualif_night,
            pilot.has_qualif_pro as has_qualif_pro,
            pilot.last_opc_date as last_opc_date,
            pilot.has_training_first_help as has_training_first_help,
            pilot.last_training_first_help_date as last_training_first_help_date,
            pilot.has_training_fire as has_training_fire,
            pilot.last_training_fire_date as last_training_fire_date,
            pilot.last_instructor_training_flight_date as last_instructor_training_date,
            pilot.is_pilot_training as is_pilot_training,
            
            flight.rowid as flight_id,
            flight.date as flight_date,
            flight.fk_type as flight_type,
            IF(UPPER(balloon.immat) = \'D-OCKM\', 1, 0) as flight_gaz_balloon,
            TIMESTAMPDIFF(MINUTE , flight.heureD, flight.heureA) as flight_duration
        FROM llx_user
        LEFT JOIN llx_bbc_pilots as pilot
            ON pilot.user_id = rowid
        LEFT JOIN llx_bbc_vols as flight
            ON flight.fk_pilot = llx_user.rowid
        LEFT JOIN llx_bbc_ballons as balloon
            ON flight.BBC_ballons_idBBC_ballons = balloon.rowid
        WHERE  llx_user.statut = 1 
        AND llx_user.firstname != \'\' 
        AND llx_user.employee = 1
        AND (flight.rowid IS NULL OR flight.rowid IN (
            SELECT f.rowid
            FROM llx_bbc_vols AS f
            WHERE f.fk_pilot = llx_user.rowid
            AND TIMESTAMPDIFF(MONTH, f.date, NOW()) <= 48
        ))
        ';

        if(isset($filters['id'])){
            $sql .= ' AND llx_user.rowid = '.$filters['id'];
        }
        $sql .= ' ORDER BY llx_user.firstname, llx_user.lastname, llx_user.rowid, flight.date';

        $resql = $this->db->query($sql);
        if (!$resql) {
            return [];
        }

        $num = $this->db->num_rows($resql);
        if ($num === 0) {
            return [];
        }

        /** @var Pilot[] $pilots */
        $pilots = [];
        for($i = 0; $i < $num ; $i++) {
            $values = $this->db->fetch_array($resql);
            $pilotId = $values['id'];

            if(!isset($pilots[$pilotId])){
                $pilots[$pilotId] = Pilot::fromArray($values);
            }

            $pilots[$pilotId]->addFlight(PilotFlight::fromArray($values, 'flight'));

        }

        return array_values($pilots);
    }

}