<?php


namespace FlightLog\Infrastructure\Common\Repository;


abstract class AbstractDomainRepository
{
    /**
     * @var \DoliDB
     */
    protected $db;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param \DoliDB $db
     * @param string $tableName
     */
    public function __construct(\DoliDB $db, $tableName)
    {
        $this->db = $db;
        $this->tableName = strpos($tableName, MAIN_DB_PREFIX) === 0 ? $tableName : MAIN_DB_PREFIX.$tableName;
    }

    /**
     * @param array $elements
     *
     * @return float|int
     *
     * @throws \Exception
     */
    protected function write(array $elements){
        $columns = join(',', array_keys($elements));
        $values = join(',', array_map([$this, 'escape'], array_values($elements)));

        $this->db->begin();
        $resql = $this->db->query(sprintf('INSERT INTO ' . $this->tableName . '(%s) VALUES (%s)', $columns, $values));

        if (!$resql) {
            $lasterror = $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . $lasterror, LOG_ERR);
            $this->db->rollback();
            throw new \Exception("Repository error - ".$lasterror);
        }

        $id = $this->db->last_insert_id($this->tableName);
        $this->db->commit();
        return $id;
    }

    /**
     * @param mixed $value
     *
     * @return int|string|null
     */
    private function escape($value){
        if(is_null($value)){
            return 'NULL';
        }

        if(is_bool($value)){
            return (int)$value;
        }

        return is_string($value) ? "'".$this->db->escape($value)."'" : $value ;
    }

    /**
     * @param int $id
     * @param array $elements
     *
     * @throws \Exception
     */
    protected function update($id, array $elements, $idCol = 'rowid')
    {
        $sqlModifications = [];

        foreach($elements as $field => $value){
            $sqlModifications[] = sprintf('%s=%s', $field, $this->escape($value));
        }

        $this->db->begin();
        $resql = $this->db->query(sprintf('UPDATE %s SET %s WHERE '.$idCol.' = %s ', $this->tableName, join(',',$sqlModifications), $id));

        if (!$resql) {
            $lasterror = $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . $lasterror, LOG_ERR);
            $this->db->rollback();
            throw new \Exception("Repository error - ".$lasterror);
        }

        $this->db->commit();
    }

    /**
     * Get the entity by its id.
     *
     * @param int $id
     * @param string $idCol
     *
     * @return array|null
     */
    protected function get($id, $idCol = 'rowid'){
        $sql = sprintf('SELECT * FROM %s WHERE '.$idCol.' = %s', $this->tableName, $id );

        $resql = $this->db->query($sql);
        if (!$resql) {
            return null;
        }

        $num = $this->db->num_rows($resql);
        if ($num === 0) {
            return null;
        }

        for($i = 0; $i < $num ; $i++) {
            return $this->db->fetch_array($resql);
        }

        return null;
    }

}