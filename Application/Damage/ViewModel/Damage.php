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
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    public $id;

    /**
     * @var bool
     */
    private $invoiced;

    /**
     * @var array
     */
    public $linkedObjects;

    /**
     * @var int|null
     */
    private $flightId;

    /**
     * @var int
     */
    private $authorId;

    /**
     * @param int $authorId
     * @param string $authorName
     * @param float $amount
     * @param int $id
     * @param bool $invoiced
     * @param int $flightId
     * @param $label
     */
    private function __construct($authorId, $authorName, $amount, $id, $invoiced, $flightId, $label)
    {
        $this->authorName = $authorName;
        $this->amount = $amount;
        $this->id = $id;
        $this->invoiced = $invoiced;
        $this->linkedObjects = [];
        $this->flightId = $flightId;
        $this->authorId = $authorId;
        $this->label = $label;
    }

    /**
     * @param array $properties
     *
     * @return Damage
     */
    public static function fromArray(array $properties)
    {
        $authorId = $properties['author_id'];
        $author = $properties['author_name'];
        $label = $properties['label'];
        $amount = $properties['amount'];
        $id = $properties['id'];
        $flightId = isset($properties['flight_id']) ? $properties['flight_id'] : null;
        $invoiced = (bool)$properties['invoiced'];

        return new self($authorId, $author, $amount, $id, $invoiced, $flightId, $label);
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

    public function __call($name, $arguments)
    {}

    /**
     * @param int $elementId
     * @param string $elementType
     * @param \CommonObject $element
     */
    public function addLink($elementId, $elementType, $element)
    {
        $this->linkedObjects[$elementType][$elementId] = $element;
    }

    /**
     * @return int|null
     */
    public function getFlightId()
    {
        return $this->flightId;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }


}