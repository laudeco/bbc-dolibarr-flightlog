<?php
/**
 *
 */

namespace flightlog\form;

/**
 * Input class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class Input extends BaseInput
{
    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options = [])
    {
        parent::__construct($name, FormElementInterface::TYPE_TEXT, $options);
    }


}