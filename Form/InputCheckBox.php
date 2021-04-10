<?php


namespace flightlog\form;



final class InputCheckBox extends BaseInput
{
    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options = [])
    {
        parent::__construct($name, FormElementInterface::TYPE_CHECKBOX, $options);
    }

    public function checkedValue(){
        return 1;
    }
}