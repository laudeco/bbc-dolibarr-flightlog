<?php


namespace FlightLog\Application\Damage\ViewModel;


use FlightLog\Application\Common\ViewModel\ViewModel;

final class TotalDamage extends ViewModel
{

    /**
     * @var float
     */
    private $amount;

    /**
     * @var int
     */
    private $authorId;

    /**
     * @var string
     */
    private $authorName;

    /**
     * @var bool
     */
    private $invoiced;

    /**
     * @param float $amount
     * @param int $authorId
     * @param string $authorName
     * @param bool $invoiced
     */
    public function __construct($amount, $authorId, $authorName, $invoiced)
    {
        $this->amount = $amount;
        $this->authorId = $authorId;
        $this->authorName = $authorName;
        $this->invoiced = $invoiced;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public static function fromArray(array $values)
    {
        return new self($values['amount'], $values['author'], $values['author_name'], $values['billed']);
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
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @return bool
     */
    public function isInvoiced()
    {
        return $this->invoiced;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

}