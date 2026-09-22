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
                continue;
            }

            $types[] = new FlightTypeCount($currentType->getType(), $currentType->getCount(),
                $currentType->getFactor(), $currentType->isMission(), $currentType->isCharged());
        }

        if (!$found) {
            $types[] = new FlightTypeCount($flightTypeCount->getType(), $flightTypeCount->getCount(),
                $flightTypeCount->getFactor(), $flightTypeCount->isMission(), $flightTypeCount->isCharged());
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
     * Sum of the points won on every type flagged as a mission for the club
     * (including the organisator and instructor bonuses).
     *
     * @return FlightBonus
     */
    public function getFlightBonus()
    {
        $bonus = FlightBonus::zero();

        foreach ($this->getMissionCounts() as $missionCount) {
            $bonus = $bonus->addPoints(FlightPoints::create($missionCount->getCost()->getValue()));
        }

        return $bonus;
    }

    /**
     * Get the total of cost for the pilot : every type charged to the pilot plus the damages.
     */
    public function getFlightsCost()
    {
        $flightsCost = FlightCost::zero();

        foreach ($this->getChargedCounts() as $chargedCount) {
            $flightsCost = $flightsCost->addCost($chargedCount->getCost());
        }

        $flightsCost = $flightsCost->addCost($this->totalDamageCost());

        return $flightsCost;
    }

    /**
     * All the counts of the pilot.
     *
     * @return array|FlightTypeCount[]
     */
    public function getCounts()
    {
        return array_values($this->flightTypeCounts);
    }

    /**
     * All the counts giving points to the pilot.
     *
     * @return array|FlightTypeCount[]
     */
    public function getMissionCounts()
    {
        return array_values(array_filter($this->flightTypeCounts, function (FlightTypeCount $count) {
            return $count->isMission();
        }));
    }

    /**
     * All the counts charged to the pilot.
     *
     * @return array|FlightTypeCount[]
     */
    public function getChargedCounts()
    {
        return array_values(array_filter($this->flightTypeCounts, function (FlightTypeCount $count) {
            return $count->isCharged();
        }));
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

}