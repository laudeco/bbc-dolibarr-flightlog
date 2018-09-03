<?php
/**
 *
 */

namespace flightlog\query;

use Webmozart\Assert\Assert;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class GetPilotsWithMissionsQuery
{

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $quarter;

    /**
     * @param int $year
     * @param int $quarter
     */
    public function __construct($year, $quarter = null)
    {
        Assert::integerish($year);
        Assert::greaterThan($year, 0);

        Assert::nullOrIntegerish($quarter);
        if($quarter !== null){
            Assert::greaterThan($quarter, 0);
            Assert::lessThanEq($quarter, 4);

        }

        $this->quarter = (int)$quarter;
        $this->year = (int)$year;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getQuarter()
    {
        return $this->quarter;
    }

    /**
     * @return bool
     */
    public function hasQuarter()
    {
        return !empty($this->quarter);
    }
}