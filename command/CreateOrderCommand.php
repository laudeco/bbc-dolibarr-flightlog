<?php
/**
 *
 */

require_once __DIR__ . '/CommandInterface.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateOrderCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $zip;

    /**
     * @var string
     */
    private $town;

    /**
     * @var string
     */
    private $street;

    /**
     * @var int
     */
    private $state;

    /**
     * @var int
     */
    private $origine;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $tva;

    /**
     * @var int
     */
    private $nbrPax;

    /**
     * @var string
     */
    private $region;

    /**
     * @var int|float
     */
    private $cost;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var int
     */
    private $civilityId;

    /**
     * @var string
     */
    private $language;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var boolean
     */
    private $publicComment;

    /**
     * @var int
     */
    private $socid;

    /**
     * Construct the command from the form dto
     *
     * @param stdClass $form
     */
    public function __construct(stdClass $form, $userId)
    {
        $this->name = $form->name;
        $this->firstname = $form->firstname;
        $this->zip = $form->zip;
        $this->town = $form->town;
        $this->state = $form->state;
        $this->phone = $form->phone;
        $this->origine = $form->origine;
        $this->email = $form->email;
        $this->tva = $form->tva;
        $this->nbrPax = $form->nbrPax;
        $this->street = $form->street;
        $this->region = $form->region;
        $this->cost = $form->cost;
        $this->comment = $form->comment;
        $this->civilityId = $form->civilityId;
        $this->language = $form->language;
        $this->userId = $userId;
        $this->publicComment = $form->isCommentPublic == 1;
        $this->socid = (int)$form->socid;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getOrigine()
    {
        return $this->origine;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * @return int
     */
    public function getNbrPax()
    {
        return $this->nbrPax;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return trim($this->street);
    }

    /**
     * @return float|int
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return boolean
     */
    public function hasTVA()
    {
        return !empty($this->tva);
    }

    /**
     * @return int
     */
    public function getCivilityId()
    {
        return $this->civilityId;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return bool
     */
    public function isCommentPublic()
    {
        return $this->publicComment;
    }

    /**
     * @return int
     */
    public function getSocid()
    {
        return $this->socid;
    }

    /**
     * @return bool
     */
    public function hasSocId()
    {
        return $this->socid !== null && $this->socid > 0;
    }
}