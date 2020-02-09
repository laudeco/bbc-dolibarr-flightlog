<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Infrastructure\Damage\Query\Repository\GetDamageQueryRepository;
use Form;

final class DamageController extends WebController
{

    /**
     * @return GetDamageQueryRepository
     */
    private function getDamageRepository(){
        return new GetDamageQueryRepository($this->db);
    }

    public function view(){
        $damageId = $this->request->getParam('id');

        try{
            $damage = $this->getDamageRepository()->query($damageId);

            $flight = new \Bbcvols($this->db);
            $flight->fetch($damage->getFlightId());

            return $this->render('damage/view.php', [
                'damage' => $damage,
                'form' => new Form($this->db),
                'flight' => $flight
            ]);
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    public function invoice(){
        $damageId = $this->request->getParam('id');



        return $this->redirect($_SERVER["PHP_SELF"].'?id='.$damageId);
    }
}