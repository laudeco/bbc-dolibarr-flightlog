<?php

class BbcBalloonTypeService extends CommonObject
{

	/**
	 * @var string Id to identify managed objects
	 */
	public $element = 'bbcballoontypeproduct';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'bbc_balloon_type_product';

	/**
	 * @var int
	 */
	public $idBalloon;

	/**
	 * @var int
	 */
	public $idType;

	/**
	 * @var int
	 */
	public $fkService = -1	;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	public function getIdBalloon(): int
	{
		return $this->idBalloon;
	}

	public function setIdBalloon(int $idBalloon): void
	{
		$this->idBalloon = $idBalloon;
	}

	public function getIdType(): int
	{
		return $this->idType;
	}

	public function setIdType(int $idType): void
	{
		$this->idType = $idType;
	}

	public function getFkService(): int
	{
		return $this->fkService;
	}

	public function setFkService(int $fkService): void
	{
		$this->fkService = $fkService;
	}

	public function fetchByBalloonIdAndTypeId(int $idBalloon, int $idType): self
	{
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . $this->table_element . " WHERE fk_bbc_balloon = " . $idBalloon . " AND fk_bbc_flight_type = " . $idType;
		$resql = $this->db->query($sql);
		$obj = new BbcBalloonTypeService($this->db);

		while ($res = $this->db->fetch_object($resql)) {
			$obj->fkService = $res->fk_product;
			$obj->idBalloon = $res->fk_bbc_balloon;
			$obj->idType = $res->fk_bbc_flight_type;

			return $obj;
		}

		return $obj;
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		$error = 0;
		dol_syslog(__METHOD__, LOG_DEBUG);

		// Update request
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';

		$sql .= ' fk_bbc_flight_type = ' . $this->idBalloon . ',';
		$sql .= ' fk_bbc_balloon = ' . $this->idBalloon . ',';
		$sql .= ' fk_product = ' . $this->fkService ;

		$sql .= ' WHERE fk_bbc_flight_type=' . $this->idType . ' AND fk_bbc_balloon=' . $this->idBalloon;

		$this->db->begin();

		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}

		if (!$error && !$notrigger) {
			$this->call_trigger('BBC_FLIGHT_TYPE_MODIFY', $user);
		}

		// Commit or rollback
		if ($error) {
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

	public function delete(User $user, $notrigger = false){
		$error = 0;

		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= ' WHERE fk_bbc_flight_type=' . $this->idType . ' AND fk_bbc_balloon=' . $this->idBalloon;

		$this->db->begin();

		dol_syslog(get_class($this)."::delete sql=".$sql);
		$resql = $this->db->query($sql);
		if (!$resql) { $error++; $this->errors[] = "Error ".$this->db->lasterror(); }

		// Commit or rollback
		if ($error)
		{
			foreach ($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 *
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$error = 0;

		$sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element . '(';

		$sql .= 'fk_bbc_flight_type,';
		$sql .= 'fk_bbc_balloon,';
		$sql .= 'fk_product';


		$sql .= ') VALUES (';

		$sql .= ' ' . $this->idType . ',';
		$sql .= ' ' . $this->idBalloon. ',';
		$sql .= ' ' . $this->fkService . ' ';
		$sql .= ')';

		$this->db->begin();

		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}

		if (!$notrigger) {
//			$this->call_trigger('BBC_FLIGHT_TYPE_CREATE', $user);
		}

		if (!$error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);
		}

		// Commit or rollback
		if ($error) {
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return $this->id;
		}
	}

	public function hasService():bool
	{
		return $this->fkService > 0;
	}
}
