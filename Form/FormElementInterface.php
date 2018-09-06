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

    const TYPE_TEXT = 'text';

    const TYPE_TEXTAREA = 'textarea';

    const TYPE_HIDDEN = 'hidden';

    const TYPE_SELECT = 'select';

    const TYPE_NUMBER = 'number';

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