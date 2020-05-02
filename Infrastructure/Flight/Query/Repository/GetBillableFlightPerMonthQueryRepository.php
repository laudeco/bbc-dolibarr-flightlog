<?php


namespace FlightLog\Infrastructure\Flight\Query\Repository;


use FlightLog\Application\Flight\Query\GetBillableFlightPerMonthQueryRepositoryInterface;
use FlightLog\Application\Flight\Query\m;
use FlightLog\Application\Flight\ViewModel\BillableFlightByYearMonth;
use FlightLog\Application\Flight\ViewModel\Statistic;

final class GetBillableFlightPerMonthQueryRepository implements GetBillableFlightPerMonthQueryRepositoryInterface
{
    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @param \DoliDB $db
     */
    public function __construct(\DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        $sql = '

SELECT 
  COUNT(idBBC_vols) as stat,
  MONTH(llx_bbc_vols.date) as month,
  YEAR(llx_bbc_vols.date) as year

FROM
  llx_bbc_vols

WHERE 
  fk_type IN (1,2)
  AND YEAR(NOW())-3 <= YEAR(llx_bbc_vols.date)

GROUP BY
  MONTH(llx_bbc_vols.date),
  YEAR(llx_bbc_vols.date)


ORDER BY
  YEAR(llx_bbc_vols.date),
  MONTH(llx_bbc_vols.date)';

        $resql = $this->db->query($sql);
        if (!$resql) {
            throw new \Exception('No data');
        }

        $num = $this->db->num_rows($resql);
        if ($num == 0) {
            throw new \Exception('No data');
        }

        $stats = new BillableFlightByYearMonth();

        for($i = 0; $i < $num ; $i++) {
            $properties = $this->db->fetch_array($resql);
            $stat = Statistic::fromArray($properties);
            $stats->add($stat);
        }

        $stats->add(new Statistic(0,1,(new \DateTime())->format('Y')));

        return $stats;
    }
}