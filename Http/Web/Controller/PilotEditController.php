<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Application\Damage\Command\CreateDamageCommand;
use FlightLog\Application\Damage\Command\CreateDamageCommandHandler;
use FlightLog\Application\Pilot\Command\CreateUpdatePilotInformationCommand;
use FlightLog\Application\Pilot\Command\CreateUpdatePilotInformationCommandHandler;
use FlightLog\Domain\Pilot\ValueObject\PilotId;
use FlightLog\Http\Web\Form\DamageCreationForm;
use FlightLog\Http\Web\Form\PilotForm;
use FlightLog\Infrastructure\Pilot\Repository\PilotRepository;

final class PilotEditController extends WebController
{

    public function index(){
        $id = $this->request->getParam('id');

        if($id === null){
            $this->renderHtml('<p>Paramètre ID non fournis.</p>');
            return;
        }

        $command = new CreateUpdatePilotInformationCommand($id);

        if($this->getPilotRepository()->exist(PilotId::create($id))){
            $command->fromPilot($this->getPilotRepository()->getById(PilotId::create($id)));
        }

        $form = new PilotForm('pilot', $this->db);
        $form->bind($command);

        $user = new \User($this->db);
        $user->fetch($id);

        if($this->request->isPost()){
            $form->setData($this->request->getPostParameters());

            if(!$form->validate()){
                return $this->render('pilot/edit.phtml', [
                    'form' => $form,
                ]);
            }

            try{
                $this->handle($form->getObject());
            }catch(\Exception $e){
                print $e->getMessage();
                dol_syslog($e->getMessage(), LOG_ERR);
            }


            return $this->redirect($_SERVER["PHP_SELF"].'?id='.$id.'&r=edit_pilot');
        }

        return $this->render('pilot/edit.phtml', [
            'pilotForm' => $form,
            'pilot' => $user,
        ]);
    }

    /**
     * @param CreateUpdatePilotInformationCommand $command
     *
     * @throws \Exception
     */
    private function handle(CreateUpdatePilotInformationCommand $command)
    {
        $this->getHandler()->__invoke($command);
    }

    /**
     * @return CreateUpdatePilotInformationCommandHandler()
     */
    private function getHandler(){
        return new CreateUpdatePilotInformationCommandHandler($this->getPilotRepository());
    }

    private function getPilotRepository()
    {
        return new PilotRepository($this->db);
    }
}