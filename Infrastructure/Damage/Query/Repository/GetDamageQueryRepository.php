<?php


namespace FlightLog\Infrastructure\Damage\Query\Repository;


use FlightLog\Application\Damage\ViewModel\Damage;

final class GetDamageQueryRepository
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

    private function element($objectType, $rowid){
        $element = null;
        $subelement = null;

        if ($objectType != 'supplier_proposal' && $objectType != 'order_supplier' && $objectType != 'invoice_supplier'
            && preg_match('/^([^_]+)_([^_]+)/i', $objectType, $regs))
        {
            $element = $regs[1];
            $subelement = $regs[2];
        }

        $classpath = $element.'/class';

        if ($objectType == 'facture') {
            $classpath = 'compta/facture/class';
        }
        elseif ($objectType == 'facturerec') {
            $classpath = 'compta/facture/class';
        }
        elseif ($objectType == 'propal') {
            $classpath = 'comm/propal/class';
        }
        elseif ($objectType == 'supplier_proposal') {
            $classpath = 'supplier_proposal/class';
        }
        elseif ($objectType == 'shipping') {
            $classpath = 'expedition/class'; $subelement = 'expedition';
        }
        elseif ($objectType == 'delivery') {
            $classpath = 'livraison/class'; $subelement = 'livraison';
        }
        elseif ($objectType == 'invoice_supplier' || $objectType == 'order_supplier') {
            $classpath = 'fourn/class';
        }
        elseif ($objectType == 'fichinter') {
            $classpath = 'fichinter/class'; $subelement = 'fichinter';
        }
        elseif ($objectType == 'subscription') {
            $classpath = 'adherents/class';
        }

        // Set classfile
        $classfile = strtolower($subelement); $classname = ucfirst($subelement);

        if ($objectType == 'order') {
            $classfile = 'commande'; $classname = 'Commande';
        }
        elseif ($objectType == 'invoice_supplier') {
            $classfile = 'fournisseur.facture'; $classname = 'FactureFournisseur';
        }
        elseif ($objectType == 'order_supplier') {
            $classfile = 'fournisseur.commande'; $classname = 'CommandeFournisseur';
        }
        elseif ($objectType == 'supplier_proposal') {
            $classfile = 'supplier_proposal'; $classname = 'SupplierProposal';
        }
        elseif ($objectType == 'facturerec') {
            $classfile = 'facture-rec'; $classname = 'FactureRec';
        }
        elseif ($objectType == 'subscription') {
            $classfile = 'subscription'; $classname = 'Subscription';
        }

        dol_include_once('/'.$classpath.'/'.$classfile.'.class.php');
        if (!class_exists($classname))
        {
            return null;
        }

        $object = new $classname($this->db);
        $object->fetch($rowid);

        return $object;
    }

    /**
     * @param int $damageId
     *
     * @return Damage
     *
     * @throws \Exception
     */
    public function query($damageId){

        $sql = 'SELECT damage.rowid as id, damage.amount, author.login as author_name, damage.billed as invoiced';
        $sql.= ', element.rowid as element_id ,element.fk_target as element_fk_target, element.targettype as element_target_type';
        $sql.=' FROM '.MAIN_DB_PREFIX.'bbc_flight_damages as damage';
        $sql.=' INNER JOIN '.MAIN_DB_PREFIX.'user as author ON author.rowid = damage.author_id';
        $sql.=' LEFT JOIN '.MAIN_DB_PREFIX.'element_element as element ON (element.fk_source = damage.rowid AND element.sourcetype = "flightlog_damage")';
        $sql.=' WHERE damage.rowid = '.$damageId;

        $resql = $this->db->query($sql);
        if (!$resql) {
            throw new \Exception('damage not found');
        }

        $num = $this->db->num_rows($resql);
        if ($num == 0) {
            throw new \Exception('damage not found');
        }

        /** @var Damage $damage */
        $damage = null;
        for($i = 0; $i < $num ; $i++) {
            $properties = $this->db->fetch_array($resql);
            if(!$damage){
                $damage = Damage::fromArray($properties);
            }

            if(!isset($properties['element_id'])){
                continue;
            }

            $damage->addLink($properties['element_id'], $properties['element_target_type'], $this->element($properties['element_target_type'], $properties['element_fk_target']));
        }

        return $damage;
    }
}