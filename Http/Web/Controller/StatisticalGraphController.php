<?php


namespace FlightLog\Http\Web\Controller;

use DolGraph;
use FlightLog\Application\Flight\Query\GetBillableFlightPerMonthQueryRepositoryInterface;
use FlightLog\Application\Flight\ViewModel\BillableFlightByYearMonth;
use FlightLog\Application\Flight\ViewModel\Statistic;
use FlightLog\Infrastructure\Flight\Query\Repository\GetBillableFlightPerMonthQueryRepository;

final class StatisticalGraphController extends WebController
{

    private function billableFlightPerMonthQueryRepository()
    {
        return new GetBillableFlightPerMonthQueryRepository($this->db);

    }

    public function billableFlightsPerMonth()
    {

        $graph = new DolGraph();

        $statistic = $this->billableFlightPerMonthQueryRepository()->query();

        $series = $this->series($statistic);
        $graphType = [];
        $color = [];

        $i = 10;
        foreach ($series[0] as $key => $year) {
            if($key == 0){
                continue;
            }

            $graphType[] = $key == 5 ? "lines" : 'linesnopoint';
            $color[] = $graph->datacolor[$key];

            $i+=30;
        }
        $color[count($color)-1] = [255,0,0]; // Average is red

        $color[count($color)-2] = [0,100,255]; //Current year
        $graphType[count($color)-2] = 'bars';

        $graph->SetDataColor($color);

        $graph->SetData($series);
        $graph->SetType($graphType);
        $graph->SetLegend(array_merge($statistic->years(), ['avg']));

        $graph->SetMaxValue($graph->GetCeilMaxValue());

        $WIDTH = DolGraph::getDefaultGraphSizeForStats('width', 768);
        $HEIGHT = 350;
        $graph->SetWidth($WIDTH + 100);
        $graph->SetHeight($HEIGHT);
        $graph->SetYLabel("#");
        $graph->SetShading(3);
        $graph->SetHorizTickIncrement(1);

        $graph->SetTitle("Vol payant par an/mois");

        $graph->draw('test');


        return $this->render('statistical_graph/billable_flights_per_month.phtml', [
            'graph' => $graph
        ]);
    }

    private function series(BillableFlightByYearMonth $statistic)
    {
        $series = [];
        $average = [];
        $numberOfYears = count(array_keys($statistic->data()));

        $i = 1;
        foreach ($statistic->data() as $year => $months) {
            /**
             * @var int $month
             * @var Statistic $stat */
            foreach($months as $month => $stat){
               if(!isset($series[$month-1])){
                   $series[$month-1] = [substr(\DateTime::createFromFormat('!m', $month)->format('F'),0,1)];
               };
               $series[$month-1][$i] = $stat->number();

               if($year === 2020){
                   continue;
               }

               if(!isset($average[$month-1])){
                   $average[$month-1] = 0;
               }
               $average[$month-1] += $stat->number();
            }

            $i++;
        }

        foreach($series as $monthIndex => $seriesValue){
            $series[$monthIndex][] = $average[$monthIndex] / ($numberOfYears-1);
        }

        return $series;
    }

}