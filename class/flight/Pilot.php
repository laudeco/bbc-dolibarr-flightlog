<?php

use FlightLog\Domain\Damage\FlightDamageCount;
use FlightLog\Domain\Damage\FlightInvoicedDamageCount;

require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/flight/FlightBonus.php');
require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/flight/FlightPoints.php');
require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/flight/FlightTypeCount.php');
require_once(DOL_DOCUMENT_ROOT . '/flightlog/class/billing/FlightCost.php');

/**
 * All financial information for one pilot.
 * This class is immutable.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
final class Pilot
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $id;

    /**
     * @var FlightTypeCount[]
     */
    private array $flightTypeCounts;

    /**
     * @var array|\FlightLog\Domain\Damage\FlightDamageCount[]|\FlightLog\Domain\Damage\FlightInvoicedDamageCount[]
     */
    private $damages = [];

    /**
     * @param string $name
     * @param int    $id
     * @param array  $flightTypeCounts
     */
    private function __construct($name, $id, $flightTypeCounts)
    {
        $this->name = $name;
        $this->id = $id;
        $this->flightTypeCounts = $flightTypeCounts;
    }

    /**
     * @param string $name
     * @param int    $id
     *
     * @return Pilot
     */
    public static function create($name, $id)
    {
        return new Pilot($name, $id, []);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param FlightTypeCount $flightTypeCount
     *
     * @return Pilot
     */
    public function addCount(FlightTypeCount $flightTypeCount)
    {
        $types = [];

        $found = false;
        /** @var FlightTypeCount $currentType */
        foreach ($this->flightTypeCounts as $currentType) {
            if ($currentType->equals($flightTypeCount)) {
                $found = true;
                $types[] = $currentType->add($flightTypeCount); // Add to the matching counter.
                break;
            }

            $types[] = $currentType->copy(); // Copy all existing values
        }

        if (!$found) {
            $types[] = $flightTypeCount->copy(); // Add the counter since didn't exist.
        }

        return new Pilot($this->name, $this->id, $types);
    }

    public function addDamage(FlightDamageCount $damage){
        $this->damages[] = $damage;
    }

    public function addInvoicedDamage(FlightInvoicedDamageCount $damage){
        $this->damages[] = $damage;
    }

    public function getCountForType(string $type):FlightTypeCount
    {
		$count = FlightTypeCount::create($type);

        foreach ($this->flightTypeCounts as $flightTypeCount) {
            if ($flightTypeCount->isType($type)) {
                $count = $count->add($flightTypeCount);
            }
        }

        return $count;
    }

    public function getFlightBonus():FlightBonus
    {
        $bonus = FlightBonus::zero();

        $bonus = $bonus->addPoints($this->getFlightPoints('1'));
        $bonus = $bonus->addPoints($this->getFlightPoints('2'));
        $bonus = $bonus->addPoints($this->getFlightPoints('orga'));
        $bonus = $bonus->addPoints($this->getFlightPoints('orga_T6'));

        return $bonus;
    }

    /**
     * Get the total of cost for the pilot
     */
    public function getFlightsCost()
    {
        $flightsCost = FlightCost::zero();

        $flightsCost = $flightsCost->addCost($this->getFlightCost('3'));
        $flightsCost = $flightsCost->addCost($this->getFlightCost('4'));
        $flightsCost = $flightsCost->addCost($this->getFlightCost('6'));
        $flightsCost = $flightsCost->addCost($this->getFlightCost('7'));
        $flightsCost = $flightsCost->addCost($this->totalDamageCost());

        return $flightsCost;
    }

    public function totalDamageCost(){
        $flightCost = FlightCost::zero();

        foreach($this->damages as $damage){
            $flightCost = $flightCost->addCost($damage->getCost());
        }

        return $flightCost;
    }

    public function damageCost(){
        $flightCost = FlightCost::zero();

        foreach($this->damages as $damage){
            if(!$damage instanceof FlightDamageCount){
                continue;
            }

            $flightCost = $flightCost->addCost($damage->getCost());
        }

        return $flightCost;
    }

    public function invoicedDamageCost(){
        $flightCost = FlightCost::zero();

        foreach($this->damages as $damage){
            if(!$damage instanceof FlightInvoicedDamageCount){
                continue;
            }

            $flightCost = $flightCost->addCost($damage->getCost());
        }

        return $flightCost;
    }

    public function damages(){
        return $this->damages;
    }

    public function getTotalBill():FlightCost
    {
        $totalBill = $this->getFlightsCost()->minBonus($this->getFlightBonus());
        if ($totalBill->getValue() < 0) {
            return FlightCost::zero();
        }

        return $totalBill;
    }

    /**
     * @param FlightBonus $extraBonus
     *
     * @return boolean
     */
    public function isBillable(FlightBonus $extraBonus)
    {
        return $this->getTotalBill()->minBonus($extraBonus)->getValue() > 0;
    }

    /**
     * @param string $type
     *
     * @return FlightPoints
     */
    public function getFlightPoints(string $type): FlightPoints
    {
		$count = FlightPoints::zero();
		foreach ($this->flightTypeCounts as $flightTypeCount) {
			if (!$flightTypeCount->isType($type)) {
				continue;
			}
			$count = $count->add(FlightPoints::create($flightTypeCount->getCost()->getValue()));
		}
		return $count;
    }

    /**
     * Get the flight cost for a type.
     *
     * @param string $type
     *
     * @return FlightCost
     */
    public function getFlightCost(string $type):FlightCost
    {
		$count = FlightCost::zero();
		foreach ($this->flightTypeCounts as $flightTypeCount) {
			if (!$flightTypeCount->isType($type)) {
				continue;
			}
			$count = $count->addCost($flightTypeCount->getCost());
		}
		return $count;
    }


}
