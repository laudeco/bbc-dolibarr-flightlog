<?php


namespace FlightLog\Domain\Damage;

final class FlightDamage
{
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
     * @param FlightId $flightId
     * @param DamageAmount $amount
     * @param $billed
     * @param AuthorId $authorId
     */
    private function __construct(FlightId $flightId, DamageAmount $amount, $billed, AuthorId $authorId)
    {
       $this->flight = $flightId;
       $this->amount = $amount;
       $this->billed = $billed;
       $this->author = $authorId;
    }

    /**
     * @param FlightId $flightId
     * @param DamageAmount $amount
     * @param AuthorId $authorId
     *
     * @return FlightDamage
     */
    public static function damage(FlightId $flightId, DamageAmount $amount, AuthorId $authorId){
        return new self($flightId, $amount, false, $authorId);
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
    public function getFlightId(){
        return $this->flight;
    }

    /**
     * @return DamageAmount
     */
    public function amount(){
        return $this->amount;
    }

    /**
     * @return AuthorId
     */
    public function getAuthor()
    {
        return $this->author;
    }

}