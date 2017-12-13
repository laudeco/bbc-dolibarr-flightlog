<?php
/**
 *
 */

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CustomerNotFoundException extends Exception
{
    /**
     * @inheritDoc
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Custom not found', 'customer.not.found', $previous);
    }

}