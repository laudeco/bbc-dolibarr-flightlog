<?php

/**
 * Validates an element.
 */
interface ValidatorInterface
{

    /**
     * Get a value as input and validates it. If an error occurs, it returns error messages.
     *
     * @param mixed $value
     * @param       $context
     *
     * @return bool
     */
    public function isValid($value, $context = []);

    /**
     * @return string[]| array
     */
    public function getErrors();

    /**
     * @param string $field
     *
     * @return array|string[]
     */
    public function getError($field);

}