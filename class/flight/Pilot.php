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
     * @var array|FlightTypeCount[]
     */
    private $flightTypeCounts;

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
            if ($currentType->getType() === $flightTypeCount->getType()) {
                $found = true;
                $types[] = $currentType->add($flightTypeCount);
                break;
            }

            $types[] = new FlightTypeCount($currentType->getType(), $currentType->getCount(),
                $currentType->getFactor());
        }

        if (!$found) {
            $types[] = new FlightTypeCount($flightTypeCount->getType(), $flightTypeCount->getCount(),
                $flightTypeCount->getFactor());
        }

        return new Pilot($this->name, $this->id, $types);
    }

    public function addDamage(FlightDamageCount $damage){
        $this->damages[] = $damage;
    }

    public function addInvoicedDamage(FlightInvoicedDamageCount $damage){
        $this->damages[] = $damage;
    }

    /**
     * @param string $type
     *
     * @return FlightTypeCount
     */
    public function getCountForType($type)
    {
        foreach ($this->flightTypeCounts as $flightTypeCount) {
            if ($flightTypeCount->getType() === $type) {
                return $flightTypeCount;
            }
        }

        return new FlightTypeCount($type);
    }

    /**
     * @return FlightBonus
     */
    public function getFlightBonus()
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

    /**
     * @return FlightCost
     */
    public function getTotalBill()
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
    private function getFlightPoints($type)
    {
        return FlightPoints::create($this->getCountForType($type)->getCost()->getValue());
    }

    /**
     * Get the flight cost for a type.
     *
     * @param string $type
     *
     * @return FlightCost
     */
    private function getFlightCost($type)
    {
        return $this->getCountForType($type)->getCost();
    }


}