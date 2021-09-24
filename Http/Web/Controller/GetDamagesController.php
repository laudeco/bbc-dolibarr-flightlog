<?php


namespace FlightLog\Http\Web\Controller;


final class GetDamagesController extends WebController
{

    public function list()
    {
        return $this->render('damage/list.phtml');
    }

}