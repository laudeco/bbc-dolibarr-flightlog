<?php
/**
 *
 */

namespace flightlog\form;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class Number extends BaseInput
{

    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options = [])
    {
        parent::__construct($name, FormElementInterface::TYPE_NUMBER, $options);
    }

    /**
     * @param string $step
     *
     * @return $this
     */
    public function setStep($step)
    {
        return $this->setAttribute('step', $step);
    }

    /**
     * @param int $min
     *
     * @return $this
     */
    public function setMin($min)
    {
        return $this->setAttribute('min', $min);
    }

    /**
     * @param int $max
     *
     * @return $this
     */
    public function setMax($max)
    {
        return $this->setAttribute('max', $max);
    }
}