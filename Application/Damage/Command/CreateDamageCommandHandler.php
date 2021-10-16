<?php


namespace FlightLog\Application\Damage\Command;


use FlightLog\Domain\Damage\AuthorId;
use FlightLog\Domain\Damage\DamageAmount;
use FlightLog\Domain\Damage\FlightDamage;
use FlightLog\Domain\Damage\FlightId;
use FlightLog\Domain\Damage\ValueObject\DamageLabel;
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
        $damage = FlightDamage::waiting(new DamageAmount($command->getAmount()),
            AuthorId::create($command->getAuthorId()), new DamageLabel($command->getLabel()));
        if (null !== $command->getFlightId()) {
            $damage = FlightDamage::damage(FlightId::create($command->getFlightId()),
                new DamageLabel($command->getLabel()), new DamageAmount($command->getAmount()),
                AuthorId::create($command->getAuthorId()));
        }

        $id = $this->damageRepository->save($damage);

        $this->linkSupplierInvoice($id, $command->getBillId());
        $this->linkFlight($id, $command->getFlightId());
    }

    /**
     * @param int $damageId
     * @param int $invoiceId
     *
     * @throws \Exception
     */
    private function linkSupplierInvoice($damageId, $invoiceId)
    {
        if ($invoiceId <= 0) {
            return;
        }

        $this->insertLinks($damageId, $invoiceId, 'invoice_supplier');
    }

    /**
     * @param int $damageId
     * @param int $flightId
     */
    private function linkFlight($damageId, $flightId)
    {
        if (null === $flightId || $flightId <= 0) {
            return;
        }

        $this->insertLinks($damageId, $flightId, 'flightlog_bbcvols');
    }

    /**
     * @param int $damageId
     * @param int $targetId
     * @param string $targetType
     */
    private function insertLinks($damageId, $targetId, $targetType)
    {
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "element_element (";
        $sql .= "fk_source";
        $sql .= ", sourcetype";
        $sql .= ", fk_target";
        $sql .= ", targettype";
        $sql .= ") VALUES (";
        $sql .= $damageId;
        $sql .= ", 'flightlog_damage'";
        $sql .= ", " . $targetId;
        $sql .= ", '" . $targetType . "'";
        $sql .= ")";

        if ($this->db->query($sql)) {
            $this->db->commit();
            return;
        }

        $this->db->rollback();
    }

}