<?php


namespace FlightLog\Http\Web\Form;


use FlightLog\Application\Damage\Command\CreateDamageCommand;
use flightlog\form\Csrf;
use flightlog\form\Form;
use flightlog\form\FormInterface;
use flightlog\form\Hidden;
use flightlog\form\Number;
use flightlog\form\UserSelect;

final class DamageCreationForm extends Form
{

    /**
     * @param string $name
     * @param \DoliDB $db
     */
    public function __construct($name, \DoliDB $db)
    {
        parent::__construct($name, FormInterface::METHOD_POST);

        $this->add(new Hidden('flight_id'));
        $this->add(new Csrf('token'));
        $this->addAmount();

        $this->add(new SupplierBillSelect('bill_id', $db, [
            'show_empty' => true
        ]));
        $this->add(new UserSelect('author_id', [], $db));
    }

    /**
     * @return CreateDamageCommand|\stdClass|null
     */
    public function getObject()
    {
        /** @var CreateDamageCommand $obj */
        $obj = parent::getObject();
        return $obj;
    }

    private function addAmount(){
        $field = new Number('amount');
        $field->required();
        $field->setStep(0.01);
        $field->setMin(1);
        $field->setMax(10000);

        $this->add($field);
    }


}