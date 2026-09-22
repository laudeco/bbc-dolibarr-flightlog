<?php


namespace FlightLog\Http\Web\Controller;

use DolGraph;
use FlightLog\Application\Flight\Query\GetBillableFlightPerMonthQueryRepositoryInterface;
use FlightLog\Application\Flight\ViewModel\BillableFlightByYearMonth;
use FlightLog\Application\Flight\ViewModel\Statistic;
use FlightLog\Http\Web\Response\Response;
use FlightLog\Infrastructure\Flight\Query\Repository\GetBillableFlightPerMonthQueryRepository;
use GraphicalData;

final class StatisticalGraphController extends WebController
{
    /**
     * @return GetBillableFlightPerMonthQueryRepositoryInterface
     */
    private function billableFlightPerMonthQueryRepository()
    {
        return new GetBillableFlightPerMonthQueryRepository($this->db);

    }

    /**
     * @return Response
     *
     * @throws \Exception
     */
    public function billableFlightsPerMonth()
    {

        $graph = new DolGraph();

        $statistic = $this->billableFlightPerMonthQueryRepository()->query();

        $series = $this->series($statistic);
        $graphType = [];
        $color = [];

        $i = 10;
        foreach ($series[0] as $key => $year) {
            if ($key == 0) {
                continue;
            }

            $graphType[] = $key == 5 ? "lines" : 'linesnopoint';
            $color[] = $graph->datacolor[$key];

            $i += 30;
        }
        $color[count($color) - 1] = [255, 0, 0]; // Average is red

        $color[count($color) - 2] = [0, 100, 255]; //Current year
        $graphType[count($color) - 2] = 'bars';

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

    /**
     * @param BillableFlightByYearMonth $statistic
     *
     * @return array
     */
    private function series(BillableFlightByYearMonth $statistic)
    {
        $series = [];
        $average = [];
        $numberOfYears = count(array_keys($statistic->data()));

        $i = 1;
        foreach ($statistic->data() as $year => $months) {
            /**
             * @var int $month
             * @var Statistic $stat
             */
            foreach ($months as $month => $stat) {
                if (!isset($series[$month - 1])) {
                    $series[$month - 1] = [substr(\DateTime::createFromFormat('!m', $month)->format('F'), 0, 1)];
                };
                $series[$month - 1][$i] = $stat->number();

                if ($year === 2020) {
                    continue;
                }

                if (!isset($average[$month - 1])) {
                    $average[$month - 1] = 0;
                }
                $average[$month - 1] += $stat->number();
            }

            $i++;
        }

        foreach ($series as $monthIndex => $seriesValue) {
            $series[$monthIndex][] = $average[$monthIndex] / ($numberOfYears - 1);
        }

        return $series;
    }

    /**
     * Draw the graphs of the flights by type and by year, side by side, followed by one
     * single table holding the values of every graph.
     *
     * @param array $graphs list of ['title' => string, 'tag' => string, 'data' => GraphicalData],
     *                      the entries without any flight type being ignored
     *
     * @return Response
     */
    public function graphsByType(array $graphs)
    {
        $graphs = array_values(array_filter($graphs, function (array $graph) {
            /** @var GraphicalData $data */
            $data = $graph['data'];

            return count($data->getTypes()) > 0;
        }));

        $dolGraphs = [];
        $tables = [];
        $years = [];

        foreach ($graphs as $graph) {
            /** @var GraphicalData $data */
            $data = $graph['data'];
            $export = $data->export();

            $dolGraphs[] = $this->buildGraphByType($data, $graph['title'], $graph['tag'], count($graphs));

            $rows = [];
            foreach ($data->getTypes() as $index => $graphicalType) {
                $values = [];
                foreach ($export as $yearValues) {
                    $year = $yearValues[0];
                    $years[$year] = $year;
                    $values[$year] = isset($yearValues[$index + 1]) ? $yearValues[$index + 1] : 0;
                }

                $rows[] = [
                    'label' => $graphicalType->getTitle(),
                    'values' => $values,
                ];
            }

            $tables[] = [
                'title' => $graph['title'],
                'rows' => $rows,
            ];
        }

        ksort($years);

        return $this->render('statistical_graph/flights_per_type.phtml', [
            'flightTypeGraphs' => $dolGraphs,
            'flightTypeGraphYears' => array_values($years),
            'flightTypeGraphTables' => $tables,
        ]);
    }

    /**
     * @param GraphicalData $data           one serie per flight type
     * @param string        $title          title of the graph
     * @param string        $tag            identifier of the graph, unique within the page
     * @param int           $numberOfGraphs number of graphs shown side by side
     *
     * @return DolGraph
     */
    private function buildGraphByType(GraphicalData $data, $title, $tag, $numberOfGraphs = 1)
    {
        $graphByTypeAndYear = new DolGraph();

        $WIDTH = DolGraph::getDefaultGraphSizeForStats('width', 2000);
        $HEIGHT = DolGraph::getDefaultGraphSizeForStats('height');

        $graphByTypeAndYear->SetData($data->export());

        // One serie per flight type, in the very same order as the exported values.
        $legend = [];
        $graphByTypeAndYear->type = [];
        foreach ($data->getTypes() as $graphicalType) {
            $legend[] = $graphicalType->getTitle();
            $graphByTypeAndYear->type[] = "lines";
        }
        $graphByTypeAndYear->SetLegend($legend);
        $graphByTypeAndYear->SetMaxValue($graphByTypeAndYear->GetCeilMaxValue());
        $graphByTypeAndYear->SetWidth((int) ($WIDTH / max(1, $numberOfGraphs)));
        $graphByTypeAndYear->SetHeight($HEIGHT + 150);
        $graphByTypeAndYear->SetYLabel("#");
        $graphByTypeAndYear->SetShading(3);
        $graphByTypeAndYear->SetHorizTickIncrement(1);

        $graphByTypeAndYear->SetTitle($title);

        $graphByTypeAndYear->draw($tag . '_' . (new \DateTime())->getTimestamp());

        return $graphByTypeAndYear;
    }

}
