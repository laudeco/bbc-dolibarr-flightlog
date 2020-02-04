<?php
/**
 *
 */

namespace flightlog\form;

/**
 * Hidden class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class Hidden extends BaseInput
{

    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options = [])
    {
        parent::__construct($name, FormElementInterface::TYPE_HIDDEN, $options);
        $this->required();
    }
}