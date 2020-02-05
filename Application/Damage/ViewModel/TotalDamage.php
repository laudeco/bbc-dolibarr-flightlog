<?php


namespace FlightLog\Application\Damage\ViewModel;


use FlightLog\Application\Common\ViewModel\ViewModel;

final class TotalDamage extends ViewModel
{

    /**
     * @var float
     */
    private $totalAmount;

    /**
     * @var int
     */
    private $authorId;

    /**
     * @param float $totalAmount
     * @param int $authorId
     */
    public function __construct($totalAmount, $authorId)
    {
        $this->totalAmount = $totalAmount;
        $this->authorId = $authorId;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public static function fromArray(array $values)
    {
        return new self($values['total_amount'], $values['author']);
    }

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }


}