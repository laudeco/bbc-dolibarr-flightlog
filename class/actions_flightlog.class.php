<?php
/**
 *
 */

use FlightLog\Infrastructure\Pilot\Query\Repository\PilotQueryRepository;

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
     * @var string
     */
    public $resprints = '';

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
            'label' => $langs->trans("Vol"),
            'text' => $langs->trans("Vol"),
            'url' => DOL_URL_ROOT . '/flightlog/list.php?mainmenu=flightlog&sall=' . $searchInfo['search_boxvalue']
        ];
    }

    /**
     * @param $parameter
     * @param $object
     * @param $action
     */
    public function showLinkToObjectBlock($params = [], CommonObject $object = null)
    {
        $this->results["flightlog_bbcvols"] = [
            'enabled' => 1,
            'perms' => 1,
            'label' => 'Un vol',
            'sql' => $this->getSqlForLink($object),
        ];

    }

    /**
     * @param array $params
     * @param CommonObject $object
     *
     * @return int
     */
    public function showLinkedObjectBlock(array $params = [], $object)
    {
        if (!isset($object->linkedObjectsIds) || !isset($object->linkedObjectsIds['flightlog_damage'])) {
            return 0;
        }

        /** @var DoliDB $db */
        global $db;

        dol_include_once('/flightlog/flightlog.inc.php');
        $queryRepository = new \FlightLog\Infrastructure\Damage\Query\Repository\GetDamageQueryRepository($db);

        foreach ($object->linkedObjectsIds['flightlog_damage'] as $damageId) {
            try {
                $object->linkedObjects['flightlog_damage'][$damageId] = $queryRepository->query($damageId);
            } catch (Exception $e) {
            }
        }

        return 0;
    }

    /**
     * @param CommonObject|null $object
     * @return string
     */
    private function getSqlForLink(CommonObject $object = null)
    {
        $sql = "SELECT ";
        $sql .= " f.idBBC_vols as rowid ";
        $sql .= ", f.cost as total_ht ";
        $sql .= ", CONCAT('(ID : ',f.idBBC_vols, ') <br/> <b>Date : </b>' ,f.date, ' <br/> De ',f.lieuD, ' à ', f.lieuA) as ref ";
        $sql .= ", CONCAT('<b>Pilote : </b>', pilot.firstname, ' ', pilot.lastname ,'<br/> <b>Organisateur : </b>', pilot.firstname, ' ', pilot.lastname ,'<br/> <b>Receiver: </b>', pilot.firstname, ' ', pilot.lastname  ) as name ";

        $sql .= " FROM ";
        $sql .= MAIN_DB_PREFIX . "bbc_vols as f ";
        $sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . "user as pilot ON pilot.rowid =  f.fk_pilot ";
        $sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . "user as organisator ON organisator.rowid =  f.fk_organisateur ";
        $sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . "user as receiver ON receiver.rowid =  f.fk_receiver ";

        $sql .= "WHERE 1 = 1 ";

        if ($object instanceof FactureFournisseur) {
            $sql .= " AND (YEAR(f.date) = (YEAR(NOW())) OR YEAR(f.date) = (YEAR(NOW()) - 1))";
        } else {
            $sql .= " AND YEAR(f.date) = (YEAR(NOW())) ";
            $sql .= " AND f.fk_type IN (1,2) ";
            $sql .= " AND f.is_facture = 0 ";
        }


        $sql .= " ORDER BY date DESC";

        return $sql;
    }

    public function completeListOfReferent()
    {
        dol_include_once('/flightlog/class/bbcvols.class.php');

        $this->results['flightlog'] = [
            'name' => "Vols",
            'title' => "Vols",
            'class' => 'bbcvols',
            'table' => 'bbc_vols',
            'datefieldname' => 'datev',
            'margin' => 'minus',
            'disableamount' => 0,
            'urlnew' => '',
            'lang' => 'flightlog',
            'buttonnew' => 'Ajouter un vol',
            'testnew' => true,
            'test' => true,
            'project_field' => 'fk_project',
        ];
    }

    public function addOpenElementsDashboardGroup()
    {
        $this->results = [
            /*'pilots' =>
                array(
                    'groupName' => 'Pilotes',
                    'globalStatsKey' => 'Pilots',
                    'stats' => ['pilotInOrder', 'pilotNotInOrder'],
                ),*/
        ];
    }

    public function addOpenElementsDashboardLine()
    {
        $result = new WorkboardResponse();
        $result->label = 'En ordre';
        $result->nbtodo = 10;

        $resultNotInOrder = new WorkboardResponse();
        $resultNotInOrder->label = 'En défaut';
        $resultNotInOrder->nbtodo = 5;


        $this->results = [
            //'pilotInOrder' => $result,
            //'pilotNotInOrder' => $resultNotInOrder,
        ];
    }

    public function printTopRightMenu(array $parameters = [])
    {
        /** @var DoliDB $db */
        global $db, $user;

        @dol_include_once('/flightlog/flightlog.inc.php');

        $repository = new PilotQueryRepository($db);

        @$member = $repository->byId($user->id);
        $this->resprints = img_picto($member->getReasons(), $member->getIconId(), '', false, false, false, '',
            'classfortooltip');
        return 0;
    }

    public function printFieldListValue(array $parameters = [])
    {
        $this->resprints = '';

        if ($this->isOrderParameters($parameters)) {
            if ($this->isOrderPayed($parameters['obj']->rowid)) {
                $this->resprints .= '<td>Oui</td>';
                return 0;
            }


            $this->resprints .= '<td>Non</td>';
            return 0;
        }


        return 0;
    }

    public function printFieldListTitle(array $parameters = [])
    {
        $this->resprints = '';

        if ($this->isOrderParameters($parameters)) {
            $this->resprints .= '<td>Payé</td>';
            return 0;
        }


        return 0;
    }

    public function printFieldListFooter(array $parameters = [])
    {
        $this->resprints = '';

        if ($this->isOrderParameters($parameters)) {
            $this->resprints .= '<td></td>';
        }


        return 0;
    }

    public function printFieldListOption(array $parameters = [])
    {
        $this->resprints = '';

        if ($this->isOrderParameters($parameters)) {
            $this->resprints .= '<td></td>';
        }


        return 0;
    }

    private function isOrderParameters(array $parameters)
    {
        return isset($parameters['arrayfields']['c.facture']);
    }

    private function isOrderPayed(int $orderId): bool
    {
        global $db;

        $order = new Commande($db);
        $order->fetch($orderId);

        if (1 != $order->billed) {
            return false;
        }

        $order->fetchObjectLinked();
        if (!isset($order->linkedObjects['facture'])) {
            return false;
        }

        $invoiced = 0;
        $paiedInvoice = 0;
        /** @var Facture $invoice */
        foreach ($order->linkedObjects['facture'] as $invoice) {
            $invoiced += $invoice->total_ttc;
            if((int)$invoice->paye === 1){
                $paiedInvoice++;
            }
        }

        return $paiedInvoice === count($order->linkedObjects['facture'])
            || (int)($order->total_ttc * 1000) <= (int)($invoiced * 1000);
    }
}