<?php


namespace FlightLog\Http\Web\Form;


use FlightLog\Application\Damage\Command\CreateDamageCommand;
use FlightLog\Application\Pilot\Command\CreateUpdatePilotInformationCommand;
use flightlog\form\Csrf;
use flightlog\form\Form;
use flightlog\form\FormInterface;
use flightlog\form\Hidden;
use flightlog\form\Input;
use flightlog\form\InputCheckBox;
use flightlog\form\InputDate;
use flightlog\form\Number;
use flightlog\form\UserSelect;

final class PilotForm extends Form
{

    /**
     * @param string $name
     * @param \DoliDB $db
     */
    public function __construct($name, \DoliDB $db)
    {
        parent::__construct($name, FormInterface::METHOD_POST);

        $this->add(new Hidden('pilot_id'));
        $this->add(new Csrf('token'));


        $this->add(new Input('pilot_licence_number'));

        $this->add(new InputDate('last_training_flight_date'));

        $this->add(new InputCheckBox('is_pilot_training'));
        $this->add(new InputCheckBox('is_pilot_class_a'));
        $this->add(new InputCheckBox('is_pilot_class_b'));
        $this->add(new InputCheckBox('is_pilot_class_c'));
        $this->add(new InputCheckBox('is_pilot_class_d'));

        $this->add(new InputCheckBox('is_pilot_gaz'));

        $this->add(new InputDate('end_medical_date'));

        $this->add(new InputCheckBox('has_qualif_static'));
        $this->add(new InputCheckBox('has_qualif_night'));
        $this->add(new InputCheckBox('has_qualif_pro'));

        $this->add(new InputDate('last_opc_date'));

        $this->add(new InputCheckBox('has_qualif_instructor'));
        $this->add(new InputDate('last_instructor_refresh_date'));
        $this->add(new InputDate('last_instructor_training_flight'));

        $this->add(new InputCheckBox('has_qualif_examinator'));
        $this->add(new InputDate('last_examinator_refresh_date'));

        $this->add(new InputCheckBox('has_radio'));
        $this->add(new Input('radio_licence_number'));
        $this->add(new InputDate('radio_licence_date'));

        $this->add(new InputCheckBox('has_training_first_help'));
        $this->add(new InputDate('last_training_first_help_date'));

        $this->add(new InputCheckBox('has_training_fire'));
        $this->add(new InputDate('last_training_fire_date'));
    }

    /**
     * @return CreateUpdatePilotInformationCommand|\stdClass|null
     */
    public function getObject()
    {
        /** @var CreateUpdatePilotInformationCommand $obj */
        $obj = parent::getObject();
        return $obj;
    }
}