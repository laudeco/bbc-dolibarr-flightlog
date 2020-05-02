<?php


namespace FlightLog\Application\Flight\ViewModel;


final class BillableFlightByYearMonth
{

    /**
     * [Year => [ Month => Statistic]]
     *
     * @var array
     */
    private $data = [];

    public function add(Statistic $stat)
    {
        if(!isset($this->data[$stat->year()])){
            $this->data[$stat->year()] = [];
            for($i = 1 ; $i <= 12 ; $i++){
                $this->data[$stat->year()][$i] = new Statistic(0, $stat->month(), $stat->year());
            }
        }

        $this->data[$stat->year()][$stat->month()] = $stat;
    }

    public function data(){
        return $this->data;
    }

    public function years(){
        return array_keys($this->data);
    }

}