<?php
/**
 *
 */

namespace flightlog\form;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
interface FormRenderer
{

    /**
     * @param FormElementInterface|FormInterface $element
     *
     * @return string
     */
    public function render($element);

}