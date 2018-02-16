<?php
require_once __DIR__ . '/../class/bbcvols.class.php';
require_once __DIR__ . '/InstructionFlightQuery.php';

/**
 * Returns all instruction flights of a user.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class InstructionFlightQueryHandler
{
    /**
     * @var DoliDB
     */
    private $db;

    /**
     * InstructionFlightQueryHandler constructor.
     *
     * @param DoliDB $db
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * @param InstructionFlightQuery $query
     *
     * @return array|Bbcvols[]
     */
    public function __invoke(InstructionFlightQuery $query)
    {
        $sql = 'SELECT flights.* FROM llx_bbc_vols as flights';
        $sql .= ' WHERE ';
        $sql .= ' flights.fk_type = 6 ';
        $sql .= ' AND flights.fk_pilot = ' . $query->getStudentId();
        $sql .= ' ORDER BY flights.date ';

        $resql = $this->db->query($sql);
        $array = [];

        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql);
                    if ($obj) {

                        $flight = new Bbcvols($this->db);
                        $flight->id = $obj->idBBC_vols;
                        $flight->idBBC_vols = $obj->idBBC_vols;
                        $flight->date = $this->db->jdate($obj->date);
                        $flight->lieuD = $obj->lieuD;
                        $flight->lieuA = $obj->lieuA;
                        $flight->heureD = $obj->heureD;
                        $flight->heureA = $obj->heureA;
                        $flight->BBC_ballons_idBBC_ballons = $obj->BBC_ballons_idBBC_ballons;
                        $flight->nbrPax = $obj->nbrPax;
                        $flight->remarque = $obj->remarque;
                        $flight->incidents = $obj->incidents;
                        $flight->fk_type = $obj->fk_type;
                        $flight->fk_pilot = $obj->fk_pilot;
                        $flight->fk_organisateur = $obj->fk_organisateur;
                        $flight->is_facture = $obj->is_facture;
                        $flight->kilometers = $obj->kilometers;
                        $flight->cost = $obj->cost;
                        $flight->fk_receiver = $obj->fk_receiver;
                        $flight->justif_kilometers = $obj->justif_kilometers;
                        $flight->date_creation = $obj->date_creation;
                        $flight->date_update = $obj->date_update;

                        $array[] = $flight;
                    }
                    $i++;
                }
            }
        }

        return $array;
    }

}