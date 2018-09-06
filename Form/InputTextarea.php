<?php
/**
 *
 */

namespace flightlog\form;

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
}