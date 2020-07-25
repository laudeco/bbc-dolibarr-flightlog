<?php


namespace FlightLog\Domain\Damage;

use FlightLog\Domain\Damage\ValueObject\DamageLabel;

final class FlightDamage
{
    /**
     * @var DamageId|null
     */
    private $id;

    /**
     * @var FlightId
     */
    private $flight;

    /**
     * @var DamageAmount
     */
    private $amount;

    /**
     * @var bool
     */
    private $billed;

    /**
     * @var AuthorId
     */
    private $author;

    /**
     * @var DamageLabel
     */
    private $label;

    /**
     * @param DamageAmount $amount
     * @param $billed
     * @param AuthorId $authorId
     * @param DamageLabel $label
     * @param FlightId $flightId
     * @param DamageId|null $id
     */
    private function __construct(
        DamageAmount $amount,
        $billed,
        AuthorId $authorId,
        DamageLabel $label,
        FlightId $flightId = null,
        DamageId $id = null
    ) {
        $this->flight = $flightId;
        $this->amount = $amount;
        $this->billed = $billed;
        $this->label = $label;
        $this->author = $authorId;
        $this->id = $id;
    }

    /**
     * @param FlightId $flightId
     * @param DamageAmount $amount
     * @param $billed
     * @param AuthorId $authorId
     * @param DamageId|null $id
     *
     * @return FlightDamage
     */
    public static function load(
        FlightId $flightId,
        DamageAmount $amount,
        DamageLabel $label,
        $billed,
        AuthorId $authorId,
        DamageId $id = null
    ) {
        return new self($amount, $billed, $authorId, $label, $flightId, $id);
    }

    /**
     * @param FlightId $flightId
     * @param DamageLabel $label
     * @param DamageAmount $amount
     * @param AuthorId $authorId
     *
     * @return FlightDamage
     */
    public static function damage(FlightId $flightId, DamageLabel $label, DamageAmount $amount, AuthorId $authorId)
    {
        return new self($amount, false, $authorId, $label, $flightId);
    }

    /**
     * @param DamageAmount $amount
     * @param AuthorId $authorId
     * @param DamageLabel $label
     *
     * @return FlightDamage
     */
    public static function waiting(DamageAmount $amount, AuthorId $authorId, DamageLabel $label)
    {
        return new self($amount, false, $authorId, $label);
    }

    /**
     * @return bool
     */
    public function isBilled()
    {
        return $this->billed;
    }

    /**
     * @return FlightId
     */
    public function getFlightId()
    {
        return $this->flight;
    }

    /**
     * @return DamageAmount
     */
    public function amount()
    {
        return $this->amount;
    }

    /**
     * @return AuthorId
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Invoice the damage.
     *
     * @return FlightDamage
     */
    public function invoice()
    {
        return new self($this->amount, true, $this->author, $this->label, $this->flight, $this->id);
    }

    /**
     * @return DamageId|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DamageLabel
     */
    public function getLabel()
    {
        return $this->label;
    }
}