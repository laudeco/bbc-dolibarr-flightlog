<?php
/**
 *
 */

namespace flightlog\form;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
abstract class BaseInput implements FormElementInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string|int
     */
    private $value;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     */
    public function __construct($name, $type, array $options = [])
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        $this->name = $name;
        $this->options = $options;
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param string     $name
     * @param int|string $value
     *
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->options['attr'][$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function required(){
        $this->options['attr']['required'] = 'required';

        return $this;
    }

    /**
     * @return $this
     */
    public function disable(){
        $this->options['attr']['disabled'] = 'disabled';
        return $this;
    }

}