<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Application\Damage\Query\GetDamagesForFlightQueryRepositoryInterface;
use FlightLog\Infrastructure\Damage\Query\Repository\GetDamagesForFlightQueryRepository;

final class FlightDamageController extends WebController
{

    /**
     * @return GetDamagesForFlightQueryRepositoryInterface
     */
    private function getDamagesRepository()
    {
        return new GetDamagesForFlightQueryRepository($this->db);
    }

    public function view()
    {
        $flightId = $this->request->getParam('id');

        $this->render('flight_damage/view.php', [
            'damages' => $this->getDamagesRepository()->__invoke($flightId),
            'flightId' => $flightId,
        ]);
    }
}