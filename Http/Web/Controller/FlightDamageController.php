<?php


namespace FlightLog\Http\Web\Controller;


final class FlightDamageController extends WebController
{

    public function view(){
        $id = GETPOST('id', 'int') ?: GETPOST('idBBC_vols', 'int');

        $obj = new \Bbcvols($this->db);
        $obj->fetch($id);

        $receiver = new \User($this->db);
        $receiver->fetch($obj->fk_receiver);


        return $this->render('flight_damage/view.php', [
            'object' => $obj,
            'receiver' => $receiver,
        ]);
    }
}