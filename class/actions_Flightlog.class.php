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
            'text'  => $langs->trans("Search flight"),
            'url'   => DOL_URL_ROOT . '/flightlog/list.php?mainmenu=flightlog&sall='.$searchInfo['search_boxvalue']
        ];
    }

    /**
     * @param $parameter
     * @param $object
     * @param $action
     */
    public function showLinkToObjectBlock($parameter, $object, $action){
        $this->results["flightlog_bbcvols"]= array('enabled'=>1, 'perms'=>1, 'label'=>'LinkToFlight', 'sql'=>"SELECT f.idBBC_vols as rowid, CONCAT('(ID : ',f.idBBC_vols, ') -',f.date, '-',f.lieuD, ' ; ', f.lieuA) as ref FROM ".MAIN_DB_PREFIX."bbc_vols as f WHERE YEAR(f.date) = (YEAR(NOW())) ORDER BY date DESC");

    }
}