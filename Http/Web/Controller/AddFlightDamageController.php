<?php


namespace FlightLog\Http\Web\Controller;

use FlightLog\Application\Damage\Command\CreateDamageCommand;
use FlightLog\Application\Damage\Command\CreateDamageCommandHandler;
use FlightLog\Http\Web\Form\DamageCreationForm;
use FlightLog\Infrastructure\Damage\Repository\FlightDamageRepository;

final class AddFlightDamageController extends WebController
{

    public function __construct(\DoliDB $db)
    {
        parent::__construct($db);
    }

    /**
     * Display the form.
     */
    public function view(){
        $id = $this->request->getParam('id');

        if($id === null){
            print '<p>Paramètre non fournis.</p>';
            return;
        }

        $flight = new \Bbcvols($this->db);
        if($flight->fetch($id) <= 0){
            print '<p>Vol non trouvé</p>';
            return;
        }

        $command = new CreateDamageCommand($flight->getPilotId());
        $command->setFlightId($flight->getId());

        $form = new DamageCreationForm('damage_creation', $this->db);
        $form->bind($command);

        if($this->request->isPost()){
            $form->setData($this->request->getPostParameters());

            if(!$form->validate()){
                return $this->render('flight_damage/form.php', [
                    'form' => $form,
                ]);
            }

            try{
                $this->handle($form->getObject());
            }catch(\Exception $e){
                print $e->getMessage();
                dol_syslog($e->getMessage(), LOG_ERR);
            }


            $this->redirect($_SERVER["PHP_SELF"].'?id='.$id);
            exit;
        }

        return $this->render('flight_damage/form.php', [
            'form' => $form,
        ]);
    }

    /**
     * @param CreateDamageCommand $command
     *
     * @throws \Exception
     */
    private function handle(CreateDamageCommand $command)
    {
        $this->getHandler()->__invoke($command);
    }

    /**
     * @return CreateDamageCommandHandler
     */
    private function getHandler(){
        return new CreateDamageCommandHandler($this->db, $this->getDamageRepository());
    }

    /**
     * @return FlightDamageRepository
     */
    private function getDamageRepository(){
        return new FlightDamageRepository($this->db);
    }

}