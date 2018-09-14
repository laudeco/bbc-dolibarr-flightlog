<?php
/**
 *
 */

/**
 * ActionsFlightlog class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class ActionsFlightlog
{

    public $results = [];

    /**
     * Add entry in search list
     *
     * @param array $searchInfo
     *
     * @return int
     */
    public function addSearchEntry($searchInfo)
    {
        global $langs;

        $langs->load("mymodule@flightlog");

        $this->results["flightlog"] = [
            'label' => $langs->trans("Search flight"),
            'text' => $langs->trans("Search flight"),
            'url' => DOL_URL_ROOT . '/flightlog/list.php?mainmenu=flightlog&sall=' . $searchInfo['search_boxvalue']
        ];
    }

    /**
     * @param $parameter
     * @param $object
     * @param $action
     */
    public function showLinkToObjectBlock($parameter, $object, $action)
    {
        $this->results["flightlog_bbcvols"] = [
            'enabled' => 1,
            'perms' => 1,
            'label' => 'LinkToFlight',
            'sql' => $this->getSqlForLink(),
        ];

    }

    /**
     * @return string
     */
    private function getSqlForLink()
    {
        $sql = "SELECT ";
        $sql .= " f.idBBC_vols as rowid ";
        $sql .= ", f.cost as total_ht ";
        $sql .= ", CONCAT('(ID : ',f.idBBC_vols, ') - ' ,f.date, ' - ',f.lieuD, ' => ', f.lieuA) as ref ";

        $sql .= " FROM ";
        $sql .= MAIN_DB_PREFIX . "bbc_vols as f ";

        $sql .= "WHERE YEAR(f.date) = (YEAR(NOW())) ";
        $sql .= " AND f.fk_type IN (1,2) ";
        $sql .= " ORDER BY date DESC";

        return $sql;
    }
}