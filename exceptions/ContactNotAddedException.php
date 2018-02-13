<?php
/**
 *
 */

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class ContactNotAddedException extends Exception
{
    /**
     * @inheritDoc
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Contact not added : ' . $message, $code, $previous);
    }
}