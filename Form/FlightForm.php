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
     * @param \ValidatorInterface $validator
     * @param \Bbcvols            $baseObject
     * @param \DoliDB             $db
     */
    public function __construct(\ValidatorInterface $validator, $baseObject, \DoliDB $db, $options)
    {

        parent::__construct('flight_form', FormInterface::METHOD_POST, $this->buildOptionsfromConfiguration($options));

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
            ->add((new FlightTypeSelect('fk_type', [], $db)))
            ->add(new UserSelect('fk_pilot', $this->getOptions(), $db))
            ->add(new UserSelect('fk_organisateur', $this->getOptions(), $db))
            ->add(new UserSelect('fk_receiver', $this->getOptions(), $db))
            ->add(new Number('kilometers'))
            ->add(new Number('cost'))
            ->add(new InputTextarea('justif_kilometers'));
    }

    /**
     * @param \stdClass $options
     *
     * @return array
     */
    private function buildOptionsfromConfiguration($options)
    {
        $values = [];

        foreach ($options as $value){
            $values[] = $value;
        }

        return $values;

    }
}