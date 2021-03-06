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

    /**
     * @var array|string[]
     */
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
    public function showLinkToObjectBlock()
    {
        $this->results["flightlog_bbcvols"] = [
            'enabled' => 1,
            'perms' => 1,
            'label' => 'LinkToFlight',
            'sql' => $this->getSqlForLink(),
        ];

    }

    /**
     * @param array $params
     * @param CommonObject $object
     *
     * @return int
     */
    public function showLinkedObjectBlock(array $params = [], $object){
        if(!isset($object->linkedObjectsIds) || !isset($object->linkedObjectsIds['flightlog_damage'])){
            return 0;
        }

        /** @var DoliDB $db */
        global $db;

        dol_include_once('/flightlog/flightlog.inc.php');
        $queryRepository = new \FlightLog\Infrastructure\Damage\Query\Repository\GetDamageQueryRepository($db);

        foreach($object->linkedObjectsIds['flightlog_damage'] as $damageId){
            try {
                $object->linkedObjects['flightlog_damage'][$damageId] = $queryRepository->query($damageId);
            } catch (Exception $e) {
            }
        }

        return 0;
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

    public function completeListOfReferent(){
        dol_include_once('/flightlog/class/bbcvols.class.php');

        $this->results['flightlog'] = [
            'name'=>"Vols",
            'title'=>"Vols",
            'class'=>'bbcvols',
            'table'=>'bbc_vols',
            'datefieldname'=>'datev',
            'margin'=>'minus',
            'disableamount'=>0,
            'urlnew'=>'',
            'lang'=>'flightlog',
            'buttonnew'=>'Ajouter un vol',
            'testnew'=>true,
            'test'=>true,
            'project_field' => 'fk_project',
        ];
    }
}