<?php


namespace FlightLog\Application\Flight\ViewModel;


use FlightLog\Application\Common\ViewModel\ViewModel;

final class Statistic extends ViewModel
{

    /**
     * @var int
     */
    private $number;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @param int $number
     * @param int $month
     * @param int $year
     */
    public function __construct($number, $month, $year)
    {
        $this->number = $number;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return int|int
     */
    public function number()
    {
        return $this->number;
    }

    public function year(){
        return $this->year;
    }

    public function month(){
        return $this->month;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public static function fromArray(array $values, string $prefix = '')
    {
        return new self(
            $values['stat'],
            $values['month'],
            $values['year']
        );
    }
}