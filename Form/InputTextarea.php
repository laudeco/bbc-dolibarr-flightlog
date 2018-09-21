<?php
/**
 *
 */

namespace flightlog\form;

use Webmozart\Assert\Assert;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class InputTextarea extends BaseInput
{
    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options = [])
    {
        parent::__construct($name, FormElementInterface::TYPE_TEXTAREA, $options);
    }

    /**
     * @param int $number
     *
     * @return $this
     */
    public function col($number)
    {
        Assert::integer($number);

        $this->setAttribute('cols', $number);

        return $this;
    }

    /**
     * @param int $number
     *
     * @return $this
     */
    public function row($number)
    {
        Assert::integer($number);

        $this->setAttribute('rows', $number);

        return $this;
    }
}