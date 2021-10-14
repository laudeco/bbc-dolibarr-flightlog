<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Infrastructure\Damage\Query\Repository\GetDamageQueryRepository;

final class GetDamagesController extends WebController
{

    /**
     * @var GetDamageQueryRepository|null
     */
    private $repository;

    /**
     * @return GetDamageQueryRepository
     */
    private function getDamageRepository()
    {
        if (null !== $this->repository) {
            return $this->repository;
        }

        $this->repository = new GetDamageQueryRepository($this->db);
        return $this->repository;
    }

    public function list()
    {
        return $this->render('damage/list.phtml', [
            'db' => $this->db,
            'damages' => $this->getDamageRepository()->listDamages(),
        ]);
    }

}