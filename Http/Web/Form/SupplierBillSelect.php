<?php


namespace FlightLog\Http\Web\Form;


use flightlog\form\Select;

final class SupplierBillSelect extends Select
{
    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @param string $name
     * @param \DoliDB $db
     * @param array $options
     */
    public function __construct($name, \DoliDB $db, $options = [])
    {
        parent::__construct($name, $options);
        $this->db = $db;
        $this->buildOptions();
    }

    /**
     * Build the options of the select
     */
    private function buildOptions()
    {

        if ((bool) $this->getOption('show_empty')) {
            $this->addValueOption(-1, ' ');
        }

        $sql = 'SELECT f.rowid, f.ref as ref_supplier, f.total_ttc, society.nom';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'facture_fourn as f';
        $sql.= ' INNER JOIN '.MAIN_DB_PREFIX.'societe as society ON society.rowid = f.fk_soc';
        $sql.= ' ORDER BY f.datec DESC';

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql);

                    $this->addValueOption($obj->rowid, sprintf('(%s) %s (%s€)', $obj->ref_supplier, $obj->nom, $obj->total_ttc));
                    $i++;
                }
            }


        }
    }


}