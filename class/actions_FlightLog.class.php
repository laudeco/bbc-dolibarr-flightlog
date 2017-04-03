<?php
/**
 *
 */

/**
 * ActionsFlightLog class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class ActionsFlightLog
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

        $langs->load("mymodule@flightLog");

        $this->results["flightLog"] = [
            'label' => $langs->trans("Search flight"),
            'text'  => $langs->trans("Search flight"),
            'url'   => DOL_URL_ROOT . '/flightLog/list.php?mainmenu=flightLog&sall='.$searchInfo['search_boxvalue']
        ];
    }

    /**
     * @param $parameter
     * @param $object
     * @param $action
     */
    public function showLinkToObjectBlock($parameter, $object, $action){
        $this->results["flightlog_bbcvols"]= array('enabled'=>1, 'perms'=>1, 'label'=>'LinkToFlight', 'sql'=>"SELECT f.idBBC_vols as rowid, CONCAT(f.date, '-',f.lieuD, ' ; ', f.lieuA) as ref FROM ".MAIN_DB_PREFIX."bbc_vols as f WHERE YEAR(f.date) = (YEAR(NOW())) ORDER BY date DESC");

    }
}