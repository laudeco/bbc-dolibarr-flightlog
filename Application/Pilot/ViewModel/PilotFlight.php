<?php


namespace FlightLog\Application\Pilot\ViewModel;


use FlightLog\Application\Common\ViewModel\ViewModel;

final class PilotFlight extends ViewModel
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    /**
     * @var int
     */
    private $type;

    /**
     * Duration in minutes.
     *
     * @var int
     */
    private $duration;

    /**
     * @var bool
     */
    private $gazBalloon;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return PilotFlight
     */
    public function setId(int $id): PilotFlight
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return PilotFlight
     */
    public function setDate(\DateTimeImmutable $date): PilotFlight
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return PilotFlight
     */
    public function setType(int $type): PilotFlight
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return PilotFlight
     */
    public function setDuration(int $duration): PilotFlight
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGazBalloon(): bool
    {
        return $this->gazBalloon;
    }

    /**
     * @param bool $gazBalloon
     * @return PilotFlight
     */
    public function setGazBalloon(bool $gazBalloon): PilotFlight
    {
        $this->gazBalloon = $gazBalloon;
        return $this;
    }



}