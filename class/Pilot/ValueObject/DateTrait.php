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

    public function asString():?string
    {
        if (null === $this->value) {
            return null;
        }

        return $this->value->format('Y-m-d');
    }

	public function equals(self $date):bool
	{
		if ($this->value === null && $date->value === null) {
			return true;
		}
		if ($this->value === null || $date->value === null) {
			return false;
		}
		return $this->value->format('Y-m-d') === $date->value->format('Y-m-d');
	}

	public function __toString():string
	{
		return $this->asString()?:'';
	}
}
