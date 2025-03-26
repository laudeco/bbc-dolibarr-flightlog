<?php

use FlightLog\Domain\Damage\FlightDamageCount;
use FlightLog\Domain\Damage\FlightInvoicedDamageCount;

require_once(DOL_DOCUMENT_ROOT.'/flightlog/class/flight/Pilot.php');
require_once(DOL_DOCUMENT_ROOT.'/flightlog/class/flight/FlightTypeCount.php');
require_once(DOL_DOCUMENT_ROOT.'/flightlog/query/BillableFlightQuery.php');

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class BillableFlightQueryHandler
{

	/**
	 * @var DoliDb $db
	 */
	private $db;

	/**
	 * @var stdClass
	 */
	private $conf;

	/**
	 * @var \FlightLog\Application\Damage\Query\GetPilotDamagesQueryRepositoryInterface
	 */
	private $pilotDamageQueryRepository;

	/**
	 * @param DoliDb   $db
	 * @param stdClass $conf
	 */
	public function __construct(DoliDb $db, stdClass $conf)
	{
		$this->db = $db;
		$this->conf = $conf;
		$this->pilotDamageQueryRepository = new \FlightLog\Infrastructure\Damage\Query\Repository\GetPilotDamagesQueryRepository($db);
	}

	/**
	 * @param BillableFlightQuery $query
	 *
	 * @return array
	 */
	public function __invoke(BillableFlightQuery $query)
	{
		$sql = 'SELECT';
		$sql .= ' USR.firstname AS prenom ,';
		$sql .= ' USR.lastname AS nom ,';
		$sql .= ' fk_pilot as pilot,';
		$sql .= ' TT.numero as type,';
		$sql .= ' bal.rowid as balid,';
		$sql .= ' bal.immat,';
		$sql .= ' COUNT(`idBBC_vols`) AS nbr,';
		$sql .= ' SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(heureA,heureD)))) AS time';
		$sql .= ' FROM llx_bbc_vols';
		$sql .= ' INNER JOIN llx_user AS USR ON `fk_pilot`= USR.rowid';
		$sql .= ' INNER JOIN llx_bbc_types AS TT ON fk_type = TT.idType';
		$sql .= ' INNER JOIN llx_bbc_ballons as bal ON bal.rowid = llx_bbc_vols.BBC_ballons_idBBC_ballons';
		$sql .= ' WHERE YEAR(llx_bbc_vols.date) = '.($query->hasYear() ? "'".$query->getFiscalYear()."'" : 'YEAR(NOW())');
		$sql .= ' GROUP BY fk_pilot, bal.rowid, `fk_type`';
		$sql .= ' ORDER BY USR.firstname, USR.lastname, TT.numero, bal.immat';

		$resql = $this->db->query($sql);
		/** @var Pilot[] $pilots */
		$pilots = [];
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql); //vol
					if (!$obj) {
						continue;
					}

					if (!isset($pilots[$obj->pilot])) {
						$pilots[$obj->pilot] = Pilot::create($obj->prenom.' '.$obj->nom, $obj->pilot);
					}

					$pilots[$obj->pilot] = $pilots[$obj->pilot]->addCount(
						new FlightTypeCount(
							$obj->type,
							(int)$obj->nbr,
							$this->getFactorByBalloonAndType($obj->type, $obj->balid)
						)
					);

					$i++;
				}
			}
		}

		if (!$query->isIncludeTotal()) {
			return $pilots;
		}

		//total orga
		$sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON llx_user.rowid = llx_bbc_vols.fk_organisateur WHERE YEAR(date) = \''.$query->getFiscalYear().'\' AND fk_type IN (1,2) GROUP BY fk_organisateur';
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql); //vol

					if ($obj) {

						if (!isset($pilots[$obj->rowid])) {
							$name = $obj->firstname.' '.$obj->name;
							$pilot = Pilot::create($name, $obj->rowid);
							$pilots[$obj->rowid] = $pilot;
						}

						$pilots[$obj->rowid] = $pilots[$obj->rowid]->addCount(
							new FlightTypeCount(
								'orga',
								(int)$obj->total,
								$this->getFactorByType('orga')
							)
						);
					}
					$i++;
				}
			}
		}

		//total orga T6 - instructeur
		$sql = 'SELECT llx_user.lastname as name , llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON llx_user.rowid = fk_organisateur WHERE YEAR(date) = \''.$query->getFiscalYear().'\' AND fk_type = 6 GROUP BY fk_organisateur';
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql); //vol

					if ($obj) {
						if (!isset($pilots[$obj->rowid])) {
							$name = $obj->firstname.' '.$obj->name;
							$pilot = Pilot::create($name, $obj->rowid);
							$pilots[$obj->rowid] = $pilot;
						}

						$pilots[$obj->rowid] = $pilots[$obj->rowid]->addCount(
							new FlightTypeCount(
								'orga_T6',
								(int)$obj->total,
								$this->getFactorByType('orga_T6')
							)
						);
					}
					$i++;
				}
			}
		}

		//Total damages
		$damages = $this->pilotDamageQueryRepository->query($query->getFiscalYear());
		foreach ($damages as $currentDamage) {

			//Pilot doesn't exist
			if (!isset($pilots[$currentDamage->getAuthorId()])) {
				$pilots[$currentDamage->getAuthorId()] = Pilot::create($currentDamage->getAuthorName(), $currentDamage->getAuthorId());
			}

			// Add all damage
			$pilots[$currentDamage->getAuthorId()]->addDamage(
				new FlightDamageCount('', $currentDamage->getAmount())
			);

			// The damage is already invoiced. So not take into account.
			if ($currentDamage->isInvoiced()) {
				$pilots[$currentDamage->getAuthorId()]->addInvoicedDamage(
					new FlightInvoicedDamageCount('', $currentDamage->getAmount())
				);
			}
		}

		return $pilots;
	}

	/**
	 * Returns the number of points if set in the config, if not return the price of the service.
	 *
	 * @param string $type
	 *
	 * @return int
	 */
	private function getFactorByType(string $type):int
	{
		switch ($type) {
			case 'orga':
				return $this->conf->BBC_POINTS_BONUS_ORGANISATOR;
			case 'orga_T6':
				return $this->conf->BBC_POINTS_BONUS_INSTRUCTOR;
		}

		$constVariableName = 'BBC_POINTS_BONUS_'.$type;
		if (!isset($this->conf->$constVariableName) || empty($this->conf->$constVariableName) || $this->conf->$constVariableName < 0) {
			return $this->getFactorForService($type);
		}

		return (int)$this->conf->$constVariableName;
	}

	public function getFactorByBalloonAndType(string $type, int $balloonId):float{
		$typeBalloonService = new BbcBalloonTypeService($this->db);
		$typeBalloonService = $typeBalloonService->fetchByBalloonIdAndTypeId($balloonId, (int)$type);

		if(!$typeBalloonService->hasService()){
			return $this->getFactorByType($type);
		}

		$svc = new Product($this->db);
		$svc->fetch($typeBalloonService->getFkService());

		return $svc->price_ttc;
	}

	/**
	 * @param string $type
	 *
	 * @return float
	 */
	private function getFactorForService(string $type):float
	{
		$service = new Bbctypes($this->db);
		$fetchResult = $service->fetch($type);

		if ($fetchResult <= 0) {
			throw new \InvalidArgumentException('Service not found');
		}

		return $service->getService()->price_ttc;
	}


}
