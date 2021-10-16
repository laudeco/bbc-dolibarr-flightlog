<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Application\Damage\Command\CreateDamageCommand;
use FlightLog\Application\Damage\Command\CreateDamageCommandHandler;
use FlightLog\Http\Web\Form\DamageCreationForm;
use FlightLog\Infrastructure\Damage\Repository\FlightDamageRepository;

final class AddDamageController extends WebController
{

    /**
     * Display the form.
     */
    public function view(){
        $damageCreationForm = new DamageCreationForm('damage_creation', $this->db);
        $damageCreationForm->bind(CreateDamageCommand::create());

        if($this->request->isPost()){
            $damageCreationForm->setData($this->request->getPostParameters());

            if(!$damageCreationForm->validate()){
                return $this->render('damage/form.php', [
                    'form' => $damageCreationForm,
                ]);
            }
            $this->handle($damageCreationForm->getObject());
            try{

            }catch(\Exception $e){
                print $e->getMessage();
                dol_syslog($e->getMessage(), LOG_ERR);
            }


            return $this->redirect($_SERVER["PHP_SELF"].'?r=get_damages');
        }

        return $this->render('damage/form.php', [
            'damageCreationForm' => $damageCreationForm,
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