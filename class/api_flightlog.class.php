<?php

use Luracast\Restler\RestException;
include_once 'View/Rest/Flight.php';

final class Flightlog extends DolibarrApi
{
    /**
     * @var DoliDB
     */
    private $connection;

    public function __construct($db = null, $cachedir = '', $refreshCache = false)
    {
        parent::__construct($db, $cachedir, $refreshCache);

        global $db;
        $this->connection = $db;
    }


    /**
     * @url GET /flightlogs/
     *
     * @return array
     *
     * @throws RestException
     */
    public function index(){
        $obj_ret = array();

        $sql = "SELECT ";
        $sql .= 'rowid,';
        $sql .= 'date,';
        $sql .= 'lieuD,';
        $sql .= 'lieuA,';
        $sql .= 'heureD,';
        $sql .= 'heureA,';
        $sql .= 'BBC_ballons_idBBC_ballons,';
        $sql .= 'nbrPax,';
        $sql .= 'remarque,';
        $sql .= 'incidents,';
        $sql .= 'fk_type,';
        $sql .= 'fk_pilot,';
        $sql .= 'fk_organisateur,';
        $sql .= 'is_facture,';
        $sql .= 'kilometers,';
        $sql .= 'cost,';
        $sql .= 'fk_receiver,';
        $sql .= 'justif_kilometers,';
        $sql .= 'date_creation,';
        $sql .= 'date_update,';
        $sql .= 'passenger_names';

        $sql.= " FROM ".MAIN_DB_PREFIX."bbc_vols";

        $sql.= ' WHERE 1=1 LIMIT 10';

        // Add sql filters

        $result = $this->connection->query($sql);

        if ($result)
        {
            $num = $this->connection->num_rows($result);
            $min = min($num, (-1 <= 0 ? $num : -1));
            $i=0;
            while ($i < $min)
            {
                $obj = $this->connection->fetch_object($result);

                $flight = new Flight();
                $flight->rowid = (int)$obj->rowid;
                $flight->date = $obj->date;
                $flight->lieuD = $obj->lieuD;
                $flight->lieuA = $obj->lieuA;
                $flight->heureD = $obj->heureD;
                $flight->heureA = $obj->heureA;
                $flight->balloon = (int)$obj->BBC_ballons_idBBC_ballons;
                $flight->nbrPax = (int)$obj->nbrPax;
                $flight->remarque = $obj->remarque;
                $flight->incidents = $obj->incidents;
                $flight->type = (int)$obj->fk_type;
                $flight->pilot = (int)$obj->fk_pilot;
                $flight->organisator = (int)$obj->fk_organisateur;
                $flight->billed = (bool)$obj->is_facture;
                $flight->cost = (int)$obj->cost;
                $flight->receiver = (int)$obj->fk_receiver;
                $flight->kilometers = (int)$obj->kilometers ;
                $flight->justifKilometers = $obj->justif_kilometers;
                $flight->createdAt = $obj->date_creation;
                $flight->updatedAt = $obj->date_update;

                $obj_ret[] = $flight;

                $i++;
            }
        }
        else {
            throw new RestException(500, 'Error when retrieve commande list : '.$this->connection->lasterror());
        }

        if( ! count($obj_ret)) {
            throw new RestException(404, 'No flight found');
        }

        return $obj_ret;
    }
}