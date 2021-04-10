<?php


namespace FlightLog\Domain\Pilot\ValueObject;


trait DateTrait
{
    /**
     * @var \DateTimeImmutable|null
     */
    private $value;

    private function __construct(\DateTimeImmutable $value = null)
    {
        $this->value = $value;
    }

    /**
     * @param \DateTimeImmutable $value
     *
     * @return DateTrait|static
     */
    public static function create(\DateTimeImmutable $value): self
    {
        return new self($value);
    }

    /**
     * @return DateTrait|static
     */
    public static function zero(): self
    {
        return new self(null);
    }

    /**
     * @param string|null $date
     *
     * @return DateTrait|static
     */
    public static function fromString(string $date = null): self
    {
        if (null === $date || empty($date)) {
            return self::zero();
        }

        return new self(\DateTimeImmutable::createFromFormat('Y-m-d', $date));
    }

    public function asString()
    {
        if (null === $this->value) {
            return null;
        }

        return $this->value->format('Y-m-d');
    }

}