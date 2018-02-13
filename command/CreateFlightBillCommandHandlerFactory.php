<?php

require_once __DIR__.'/CreateFlightBillCommandHandler.php';
require_once __DIR__.'/CreateFlightBillTransactionalCommandHandler.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateFlightBillCommandHandlerFactory
{
    /**
     * @return CreateFlightBillCommandHandler
     */
    public static function factory($db, $conf, $user, $langs){
        return new CreateFlightBillTransactionalCommandHandler($db, $conf, $user, $langs);
    }

}