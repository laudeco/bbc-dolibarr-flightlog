<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Application\Pilot\Command\CreateUpdatePilotInformationCommand;
use FlightLog\Application\Pilot\Command\CreateUpdatePilotInformationCommandHandler;
use FlightLog\Domain\Pilot\ValueObject\PilotId;
use FlightLog\Http\Web\Form\PilotForm;
use FlightLog\Infrastructure\Pilot\Query\Repository\PilotQueryRepository;
use FlightLog\Infrastructure\Pilot\Repository\PilotRepository;

final class PilotListController extends WebController
{

    public function index(){

        $members = $this->getPilotRepository()->query();

        return $this->render('pilot/index.phtml', [
            'members' => $members,
        ]);
    }

    private function getPilotRepository(): PilotQueryRepository
    {
        return new PilotQueryRepository($this->db);
    }
}