<?php

use FlightLog\Http\Web\Controller\DamageController;
use FlightLog\Http\Web\Controller\FlightController;
use FlightLog\Infrastructure\Common\Routes\Route;

global $db;

return [
    new Route('get_one_damage', DamageController::class, 'view'),
    new Route('invoice_damage', DamageController::class, 'invoice'),
    new Route('get_one_flight', FlightController::class, 'view'),
];
?>