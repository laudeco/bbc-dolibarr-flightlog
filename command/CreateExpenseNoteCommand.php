<?php
/**
 *
 */

namespace flightlog\command;

use flightlog\exceptions\PeriodNotFinishedException;
use Webmozart\Assert\Assert;

/**
 * CreateExpenseNoteCommand class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateExpenseNoteCommand
{

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $quartile;

    /**
     * @var int
     */
    private $userValidatorId;

    /**
     * @var string
     */
    private $privateNote;

    /**
     * @var string
     */
    private $publicNote;

    /**
     * @param int    $year
     * @param int    $quartile
     * @param int    $userValidatorId
     * @param string $privateNote
     * @param string $publicNote
     *
     * @throws PeriodNotFinishedException
     */
    public function __construct($year, $quartile, $userValidatorId, $privateNote, $publicNote)
    {
        $currentYear = date('Y');
        $currentQuarter = floor((date('n') - 1) / 3) + 1;

        Assert::integerish($year);
        Assert::greaterThan($year, 0);

        Assert::notNull($quartile);
        Assert::integerish($quartile);
        Assert::greaterThan($quartile, 0);
        Assert::lessThanEq($quartile, 4);

        Assert::integerish($userValidatorId);
        Assert::greaterThan($userValidatorId, 0);

        if (!($year < $currentYear || ($year == $currentYear && $quartile < $currentQuarter))) {
            throw new PeriodNotFinishedException('');
        }

        $this->year = (int) $year;
        $this->quartile = (int) $quartile;
        $this->userValidatorId = (int) $userValidatorId;
        $this->privateNote = $privateNote;
        $this->publicNote = $publicNote;
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
    public function getQuartile()
    {
        return $this->quartile;
    }

    /**
     * @return int
     */
    public function getUserValidatorId()
    {
        return $this->userValidatorId;
    }

    /**
     * @return string
     */
    public function getPrivateNote()
    {
        return $this->privateNote;
    }

    /**
     * @return string
     */
    public function getPublicNote()
    {
        return $this->publicNote;
    }
}