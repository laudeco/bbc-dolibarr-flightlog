<?php


namespace FlightLog\Infrastructure\Common\Repository;


abstract class AbstractDomainRepository
{
    /**
     * @var \DoliDB
     */
    private $db;

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
        $values = join(',', array_map(function($value){
                if(is_null($value)){
                    return null;
                }

                if(is_bool($value)){
                    return (int)$value;
                }

                return is_string($value) ? $this->db->escape($value) : $value ;
            }, array_values($elements)));

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


}