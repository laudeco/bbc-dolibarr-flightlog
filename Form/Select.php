<?php
/**
 *
 */

namespace flightlog\form;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class Select extends BaseInput
{

    /**
     * @var array
     */
    private $valueOptions;

    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, $options = [])
    {
        parent::__construct($name, FormElementInterface::TYPE_SELECT, $options);
        $this->valueOptions = [];
    }

    /**
     * @return array
     */
    public function getValueOptions()
    {
        return $this->valueOptions;
    }

    /**
     * @param array $valueOptions
     *
     * @return Select
     */
    public function setValueOptions($valueOptions)
    {
        $this->valueOptions = $valueOptions;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        parent::setValue($value);
        $this->valueOptions[$value] = $value;
    }


}