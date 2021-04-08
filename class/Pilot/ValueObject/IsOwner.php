<?php


namespace FlightLog\Domain\Pilot\ValueObject;


final class IsOwner
{

    /**
     * @var bool|null
     */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function fromValue($value)
    {
        if (null === $value) {
            return self::no();
        }

        if (is_numeric($value) || is_int($value) || (int)$value == $value) {
            return new self($value == 1);
        }

        if (is_string($value)) {
            return new self(in_array($value, ['y', 't']));
        }

        return new self($value);
    }

    public static function no(): self
    {
        return new self(false);
    }

    public static function create(): self
    {
        return self::no();
    }

    public static function yes(): self
    {
        return new self(true);
    }

    public function is(): bool
    {
        return $this->value;
    }


}