<?php


namespace FlightLog\Application\Damage\Command;


use FlightLog\Domain\Damage\AuthorId;
use FlightLog\Domain\Damage\DamageAmount;
use FlightLog\Domain\Damage\FlightDamage;
use FlightLog\Domain\Damage\FlightId;
use FlightLog\Infrastructure\Damage\Repository\FlightDamageRepository;

final class CreateDamageCommandHandler
{
    /**
     * @var FlightDamageRepository
     */
    private $damageRepository;

    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @param \DoliDB $db
     * @param FlightDamageRepository $damageRepository
     */
    public function __construct(\DoliDB $db, FlightDamageRepository $damageRepository)
    {
        $this->damageRepository = $damageRepository;
        $this->db = $db;
    }


    /**
     * @param CreateDamageCommand $command
     *
     * @throws \Exception
     */
    public function __invoke(CreateDamageCommand $command)
    {
        //Create the damage in the DB
        $damage = FlightDamage::damage(FlightId::create($command->getFlightId()), new DamageAmount($command->getAmount()), AuthorId::create($command->getAuthorId()));
        $this->damageRepository->save($damage);

        $this->linkSupplierInvoice($command->getFlightId(), $command->getBillId());
    }

    /**
     * @param int $flightId
     * @param int $invoiceId
     *
     * @throws \Exception
     */
    private function linkSupplierInvoice($flightId, $invoiceId)
    {
        if($invoiceId <= 0){
            return;
        }

        $flight = new \Bbcvols($this->db);
        $flight->fetch($flightId);
        $flight->add_object_linked('invoice_supplier', $invoiceId);
    }

}