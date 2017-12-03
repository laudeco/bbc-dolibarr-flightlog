<?php
require_once __DIR__ . '/ValidatorInterface.php';

/**
 * AbstractValidator class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
abstract class AbstractValidator implements ValidatorInterface
{

    /** @var Translate */
    private $langs;

    /**
     * @var string[]|array
     */
    protected $errors;

    /**
     * @var boolean
     */
    protected $valid;

    /**
     * AbstractValidator constructor.
     *
     * @param Translate $langs
     */
    public function __construct(Translate $langs)
    {
        $this->langs = $langs;
    }

    /**
     * @param string $field
     * @param string $message
     */
    protected function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
        $this->valid = false;
    }

    /**
     * @return array|string[]
     */
    public function getErrors()
    {
        $errors = [];
        foreach ($this->errors as $currentField) {
            foreach ($currentField as $fieldError) {
                $errors[] = $fieldError;
            }
        }

        return $errors;
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    public function hasError($field)
    {
        return isset($this->errors[$field]) && !empty($field);
    }

}