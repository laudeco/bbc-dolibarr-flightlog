<?php
/**
 *
 */

namespace flightlog\form;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
interface FormElementInterface
{

    /**
     * @return string
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     *
     * @return FormElementInterface
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getOptions();

}