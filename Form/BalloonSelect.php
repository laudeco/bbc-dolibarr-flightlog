<?php
/**
 *
 */

namespace flightlog\form;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class BalloonSelect extends Select
{
    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @inheritDoc
     */
    public function __construct($name, \DoliDB $db, array $options = [])
    {
        parent::__construct($name, $options);
        $this->db = $db;
        $this->buildOptions();
    }

    /**
     * Builds the options values of the select.
     */
    private function buildOptions()
    {
        $sql = "SELECT";
        $sql.= " t.rowid,";
        $sql.= " t.immat,";
        $sql.= " t.is_disable";
        $sql.= " FROM llx_bbc_ballons as t";
        $sql.= " WHERE t.is_disable = 0";
        $sql.= " ORDER BY t.immat";

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql);
                    $this->addValueOption($obj->rowid, $obj->immat);
                    $i++;
                }
            }
        }
    }
}