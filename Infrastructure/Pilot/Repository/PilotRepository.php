<?php


namespace FlightLog\Infrastructure\Pilot\Repository;


use FlightLog\Domain\Pilot\Pilot;
use FlightLog\Domain\Pilot\ValueObject\PilotId;
use FlightLog\Infrastructure\Common\Repository\AbstractDomainRepository;

final class PilotRepository extends AbstractDomainRepository
{
    public function __construct(\DoliDB $db)
    {
        parent::__construct($db, 'llx_bbc_pilots');
    }

    public function save(Pilot $pilot)
    {
        if ($this->exist($pilot->id())) {
            $this->update($pilot->id()->getId(), $pilot->state(), 'user_id');
            return;
        }

        $this->write($pilot->state());
    }

    public function exist(PilotId $pilotId): bool
    {
        return null !== $this->get($pilotId->getId(), 'user_id');
    }

    public function getById(PilotId $id): Pilot
    {
        $pilot = $this->get($id->getId(), 'user_id');
        if (null === $pilot) {
            throw new \Exception('Pilot not found');
        }

        return Pilot::fromState($pilot);
    }
}