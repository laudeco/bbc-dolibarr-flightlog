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

    const TYPE_TIME = 'time';

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getId();

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

    /**
     * @return boolean
     */
    public function isDisabled();

    /**
     * @return boolean
     */
    public function hasError();

    /**
     * Set the error messages.
     *
     * @param array|string[] $errors
     *
     * @return $this
     */
    public function setErrors($errors = []);

}