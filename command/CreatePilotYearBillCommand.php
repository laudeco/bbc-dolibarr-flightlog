<?php

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreatePilotYearBillCommand
{
    /**
     * @var Pilot
     */
    private $pilot;

    /**
     * @var int
     */
    private $billType;

    /**
     * @var string
     */
    private $privateNote;

    /**
     * @var string
     */
    private $publicNote;

    /**
     * @var string
     */
    private $modelPdf;

    /**
     * @var int
     */
    private $reglementCondition;

    /**
     * @var int
     */
    private $reglementMode;

    /**
     * @var int
     */
    private $bankAccount;

    /**
     * @var string
     */
    private $year;

    /**
     * @var string
     */
    private $bonusAdditionalMessage;

    /**
     * @var int
     */
    private $additionalBonus;

    /**
     * CreatePilotYearBillCommand constructor.
     *
     * @param Pilot $pilot
     * @param int $billType
     * @param string $privateNote
     * @param string $publicNote
     * @param string $modelPdf
     * @param int $reglementCondition
     * @param int $reglementMode
     * @param int $bankAccount
     * @param string $year
     * @param string $bonusAdditionalMessage
     * @param int $additionalBonus
     */
    public function __construct(
        $pilot,
        $billType,
        $privateNote,
        $publicNote,
        $modelPdf,
        $reglementCondition,
        $reglementMode,
        $bankAccount,
        $year,
        $bonusAdditionalMessage,
        $additionalBonus
    ) {
        $this->pilot = $pilot;
        $this->billType = $billType;
        $this->privateNote = $privateNote;
        $this->publicNote = $publicNote;
        $this->modelPdf = $modelPdf;
        $this->reglementCondition = $reglementCondition;
        $this->reglementMode = $reglementMode;
        $this->bankAccount = $bankAccount;
        $this->year = $year;
        $this->bonusAdditionalMessage = $bonusAdditionalMessage;
        $this->additionalBonus = $additionalBonus;
    }


    /**
     * @return Pilot
     */
    public function getPilot()
    {
        return $this->pilot;
    }

    /**
     * @return int
     */
    public function getBillType()
    {
        return $this->billType;
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

    /**
     * @return string
     */
    public function getModelPdf()
    {
        return $this->modelPdf;
    }

    /**
     * @return int
     */
    public function getReglementCondition()
    {
        return $this->reglementCondition;
    }

    /**
     * @return int
     */
    public function getReglementMode()
    {
        return $this->reglementMode;
    }

    /**
     * @return int
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return string
     */
    public function getBonusAdditionalMessage()
    {
        return $this->bonusAdditionalMessage;
    }

    /**
     * @return int
     */
    public function getAdditionalBonus()
    {
        return $this->additionalBonus;
    }

    /**
     * @return boolean
     */
    public function isReferenceHidden()
    {
    }

    /**
     * @return boolean
     */
    public function isDescriptionHidden()
    {
    }

    /**
     * @return boolean
     */
    public function isDetailsHidden()
    {
    }

}