<?php
/**
 *
 */

/**
 * BillableFlightQuery class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class BillableFlightQuery
{

    /**
     * @var boolean
     */
    private $includeTotal;

    /**
     * @var int
     */
    private $fiscalYear;

    /**
     * @param bool $includeTotal
     * @param int  $fiscalYear
     */
    public function __construct($includeTotal = true, $fiscalYear = 0)
    {
        $this->includeTotal = $includeTotal;
        $this->fiscalYear = $fiscalYear;
    }

    /**
     * @return bool
     */
    public function isIncludeTotal()
    {
        return $this->includeTotal;
    }

    /**
     * @return int
     */
    public function getFiscalYear()
    {
        return $this->fiscalYear;
    }

    /**
     * @return bool
     */
    public function hasYear()
    {
        return $this->fiscalYear != 0;
    }

}