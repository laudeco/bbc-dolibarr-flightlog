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
     * @var string[]|array
     */
    private $warningMessages;

    /**
     * @var boolean
     */
    protected $valid;

    /**
     * @var DoliDB
     */
    protected $db;

    /**
     * AbstractValidator constructor.
     *
     * @param Translate $langs
     * @param DoliDB    $db
     */
    public function __construct(Translate $langs, DoliDB $db)
    {
        $this->langs = $langs;
        $this->db = $db;
        $this->errors = [];
        $this->warningMessages = [];
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
     * @param string $message
     *
     * @return AbstractValidator
     */
    protected function addWarning($message)
    {
        $this->warningMessages[] = $message;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getWarningMessages()
    {
        return $this->warningMessages;
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

    /**
     * @param string $field
     *
     * @return array|string[]
     */
    public function getError($field){
        if(!$this->hasError($field)){
            return [];
        }

        return $this->errors[$field];
    }

}