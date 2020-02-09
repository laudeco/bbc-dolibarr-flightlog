<?php


namespace FlightLog\Http\Web\Controller;


final class FlightController extends WebController
{
    public function view(){
        $id = $this->request->getParam('id');
        $this->redirect(DOL_URL_ROOT.'/flightlog/card.php?id='.$id);
    }

}