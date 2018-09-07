<?php
/**
 *
 */

namespace flightlog\form;

/**
 * FlightForm class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightForm extends Form
{
    /**
     * @inheritDoc
     */
    public function __construct(\ValidatorInterface $validator, $baseObject)
    {
        parent::__construct('flight_form', FormInterface::METHOD_POST, []);

        $this->setValidator($validator)
            ->bind($baseObject);

        $this
            ->add(new Hidden('idBBC_vols'))
            ->add(new Input('lieuD'))
            ->add(new Input('lieuA'))
            ->add(new Select('BBC_ballons_idBBC_ballons'))
            ->add(new Number('nbrPax'))
            ->add(new InputTextarea('remarque'))
            ->add(new InputTextarea('incidents'))
            ->add(new InputTextarea('passengerNames'))
            ->add(new Select('fk_type'))
            ->add(new Select('fk_pilot'))
            ->add(new Select('fk_organisateur'))
            ->add(new Number('kilometers'))
            ->add(new Number('cost'))
            ->add(new Select('fk_receiver'))
            ->add(new InputTextarea('justif_kilometers'));
    }
}