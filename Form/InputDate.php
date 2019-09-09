<?php

namespace flightlog\form;


class InputDate extends BaseInput
{
    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options = [])
    {
        parent::__construct($name, FormElementInterface::TYPE_DATE, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        if($value instanceof \DateTime){
            return parent::setValue($value->format('Y-m-d'));
        }

        return parent::setValue($value);

    }


}