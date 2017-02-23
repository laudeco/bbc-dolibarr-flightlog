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
}