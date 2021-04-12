<?php


namespace FlightLog\Application\Pilot\ViewModel;


use FlightLog\Application\Common\ViewModel\ViewModel;

final class Pilot extends ViewModel
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $email;

    /**
     * @var \DateTimeImmutable|null
     */
    private $medicalEndDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getIconId(): string
    {
        if ($this->isDanger()) {
            return 'statut8';
        }

        return 'statut4';
    }

    public function isDanger()
    {
        if (!$this->isMedicalValid()) {
            return true;
        }

        return false;
    }

    public function getReasons()
    {
        $reasons = '';

        $reasons .= '<b>Médical : </b> '.($this->isMedicalValid() ? 'OK' : 'L\'échéance est atteinte ou dépassée.');

        return '<u>Details:</u><br>'.$reasons;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public static function fromArray(array $values)
    {
        $pilot = new self();

        $pilot->id = $values['id'];
        $pilot->name = $values['name'];
        $pilot->firstname = $values['firstname'];
        $pilot->email = $values['email'];
        
        if(null !== $values['medical_end_date']){
            $pilot->medicalEndDate = \DateTimeImmutable::createFromFormat('Y-m-d', $values['medical_end_date']);
        }

        return $pilot;
    }

    private function isMedicalValid(): bool
    {
        if(null === $this->medicalEndDate){
            return true;
        }

        return $this->medicalEndDate > new \DateTimeImmutable();
    }
}