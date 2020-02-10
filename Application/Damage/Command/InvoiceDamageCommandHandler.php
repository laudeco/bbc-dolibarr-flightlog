<?php


namespace FlightLog\Application\Damage\Command;


use FlightLog\Domain\Damage\DamageId;
use FlightLog\Infrastructure\Damage\Repository\FlightDamageRepository;

final class InvoiceDamageCommandHandler
{

    /**
     * @var FlightDamageRepository
     */
    private $damageRepository;

    /**
     * @param FlightDamageRepository $damageRepository
     */
    public function __construct(FlightDamageRepository $damageRepository)
    {
        $this->damageRepository = $damageRepository;
    }

    /**
     * @param InvoiceDamageCommand $command
     *
     * @throws \Exception
     */
    public function __invoke(InvoiceDamageCommand $command){
        $damage = $this->damageRepository->getById(DamageId::create($command->getDamageId()));
        $damage = $damage->invoice();
        $this->damageRepository->save($damage);
    }


}