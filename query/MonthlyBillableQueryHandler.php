<?php
require_once __DIR__ . '/../class/billing/monthly/MonthBillCollection.php';
require_once __DIR__ . '/../class/billing/monthly/MonthlyFlightBill.php';
require_once __DIR__ . '/../class/billing/monthly/MoneyReceiver.php';
require_once __DIR__ . '/../class/bbcvols.class.php';
require_once __DIR__ . '/MonthlyBillableQuery.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class MonthlyBillableQueryHandler
{

    /**
     * @var DoliDB
     */
    private $db;

    /**
     * @var stdClass
     */
    private $config;

    /**
     * @param DoliDB   $db
     * @param stdClass $config
     */
    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * @param MonthlyBillableQuery $query
     *
     * @return MonthBillCollection
     */
    public function __invoke(MonthlyBillableQuery $query)
    {
        $sql = 'SELECT flights.*, usr.rowid as usrRowId, usr.lastname as lastname,  usr.firstname as firstname FROM llx_bbc_vols as flights INNER JOIN llx_user as usr ON flights.fk_receiver = usr.rowid';
        $sql .= ' WHERE ';
        $sql .= ' flights.fk_type = 2 ';
        $sql .= ' AND flights.is_facture = 0 ';
        $sql .= ' AND (flights.cost IS NOT NULL AND flights.cost > 0) ';
        $sql .= sprintf(' AND YEAR(flights.date) = %s ', $query->getYear());
        $sql .= sprintf(' AND MONTH(flights.date) = %s', $query->getMonth());

        $resql = $this->db->query($sql);
        $array = new MonthBillCollection();

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

                        $moneyReceiver = new MoneyReceiver($obj->firstname, $obj->lastname, $obj->usrRowId);
                        $array->addFlight($moneyReceiver, $flight);
                    }
                    $i++;
                }
            }
        }

        return $array;
    }

}