<?php


namespace FlightLog\Infrastructure\Damage\Repository;

use FlightLog\Domain\Damage\AuthorId;
use FlightLog\Domain\Damage\DamageAmount;
use FlightLog\Domain\Damage\DamageId;
use FlightLog\Domain\Damage\FlightDamage;
use FlightLog\Domain\Damage\FlightId;
use FlightLog\Domain\Damage\ValueObject\DamageLabel;
use FlightLog\Infrastructure\Common\Repository\AbstractDomainRepository;

final class FlightDamageRepository extends AbstractDomainRepository
{

    /**
     * @param \DoliDB $db
     */
    public function __construct(\DoliDB $db)
    {
        parent::__construct($db, 'bbc_flight_damages');
    }

    /**
     * @param FlightDamage $flightDamage
     *
     * @throws \Exception
     */
    public function save(FlightDamage $flightDamage)
    {
        $fields = [
            'flight_id' => $flightDamage->getFlightId()->getId(),
            'billed' => $flightDamage->isBilled(),
            'amount' => $flightDamage->amount()->getValue(),
            'author_id' => $flightDamage->getAuthor()->getId(),
            'label' => $flightDamage->getLabel()->value(),
        ];

        if($flightDamage->getId()){
            $this->update($flightDamage->getId()->getId(), $fields);
            return $flightDamage->getId()->getId();
        }

        return $this->write($fields);
    }

    /**
     * @param DamageId $id
     *
     * @return FlightDamage
     *
     * @throws \Exception
     */
    public function getById(DamageId $id){
        $damage = $this->get($id->getId());

        if(null === $damage){
            throw new \Exception('Damage not found');
        }

        return FlightDamage::load(
            FlightId::create($damage['flight_id']),
            new DamageAmount($damage['amount']),
            new DamageLabel($damage['label']),
            $damage['billed'],
            AuthorId::create($damage['author_id']),
            DamageId::create($damage['rowid'])
        );
    }
}