<?php
/**
 *
 */

namespace flightlog\exceptions;

use Exception;
use Throwable;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class NoMissionException extends Exception
{
    /**
     * @inheritDoc
     */
    public function __construct($message = "Pas de missions pour cette période", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


}