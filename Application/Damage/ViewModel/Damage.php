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
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $invoiced;

    /**
     * @param string $authorName
     * @param float $amount
     * @param int $id
     * @param bool $invoiced
     */
    public function __construct($authorName, $amount, $id, $invoiced)
    {
        $this->authorName = $authorName;
        $this->amount = $amount;
        $this->id = $id;
        $this->invoiced = $invoiced;
    }

    /**
     * @param array $properties
     *
     * @return Damage
     */
    public static function fromArray(array $properties){
        $author = $properties['author_name'];
        $amount = $properties['amount'];
        $id = $properties['id'];
        $invoiced = (bool)$properties['invoiced'];

        return new self($author, $amount, $id, $invoiced);
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isInvoiced()
    {
        return $this->invoiced;
    }
}