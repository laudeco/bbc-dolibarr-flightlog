<?php
/**
 *
 */

namespace flightlog\form;

use User;

/**
 * FlightForm class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class FlightForm extends Form
{

    /**
     * @var User
     */
    private $user;

    /**
     * @param \ValidatorInterface $validator
     * @param \Bbcvols $baseObject
     * @param \DoliDB $db
     * @param array $options
     * @param \User $user
     */
    public function __construct(\ValidatorInterface $validator, $baseObject, \DoliDB $db, $options,\User $user)
    {

        parent::__construct('flight_form', FormInterface::METHOD_POST, $this->buildOptionsfromConfiguration($options));

        $this->user = $user;

        $this->setValidator($validator)
            ->bind($baseObject);

        $this
            ->add(new Hidden('idBBC_vols'))
            ->add((new InputDate('date'))->required())
            ->add((new Input('lieuD'))->required())
            ->add((new Input('lieuA'))->required())
            ->add((new InputTime('heureD'))->required())
            ->add((new InputTime('heureA'))->required())
            ->add((new BalloonSelect('BBC_ballons_idBBC_ballons', $db))->required())
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

        foreach ($options as $value) {
            $values[] = $value;
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function bind($object)
    {
        /** @var \Bbcvols $flight */
        $flight = $object;

        // Quick fix - Fixme by a factory on this form.
        if($this->user->rights->flightlog->vol->advanced){
            return parent::bind($object);
        }

        if ($flight->isBilled()) {
            $this
                ->remove('fk_receiver')
                ->remove('cost')
                ->remove('nbrPax')
                ->remove('passengerNames');
        }

        $endOfYearDate = $flight->getDate()->setDate($flight->getDate()->format('Y'), 12, 31);
        if (new \DateTime() >= $endOfYearDate) {
            $this
                ->remove('fk_receiver')
                ->remove('cost')
                ->remove('BBC_ballons_idBBC_ballons')
                ->remove('nbrPax')
                ->remove('passengerNames')
                ->remove('fk_type')
                ->remove('fk_pilot')
                ->remove('fk_organisateur')
                ->remove('kilometers')
                ->remove('heureD')
                ->remove('heureA')
                ->remove('kilometers')
                ->remove('justif_kilometers');
        }

        return parent::bind($object);
    }


}