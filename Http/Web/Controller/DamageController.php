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

            $this->render('damage/view.php', [
                'damage' => $damage,
                'form' => new Form($this->db),
            ]);
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }
}