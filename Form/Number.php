<?php
/**
 *
 */

namespace flightlog\form;

use Webmozart\Assert\Assert;

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
        Assert::stringNotEmpty($step);

        return $this->setAttribute('step', $step);
    }
}