<?php


namespace FlightLog\Domain\Pilot\ValueObject;


trait LicenceNumberTrait
{
    /**
     * @var string
     */
    private $licence;

    private function __construct(string $licence)
    {
        $this->licence = $licence;
    }

    public function getLicence(): string{
        return $this->licence;
    }

    /**
     * @return LicenceNumberTrait|static
     */
    public static function empty(): self{
        return new self('');
    }

    /**
     * @param string $licence
     *
     * @return LicenceNumberTrait|static
     */
    public static function create(string $licence): self{
        return new self($licence);
    }

}