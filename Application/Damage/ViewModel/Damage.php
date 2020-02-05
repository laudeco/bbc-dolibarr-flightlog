<?php


namespace FlightLog\Application\Damage\ViewModel;


final class Damage
{

    /**
     * @var string
     */
    private $authorName;

    /**
     * @var float
     */
    private $amount;

    /**
     * @param string $authorName
     * @param float $amount
     */
    public function __construct($authorName, $amount)
    {
        $this->authorName = $authorName;
        $this->amount = $amount;
    }

    /**
     * @param array $properties
     *
     * @return Damage
     */
    public static function fromArray(array $properties){
        $author = $properties['author_name'];
        $amount = $properties['amount'];

        return new self($author, $amount);
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }
}