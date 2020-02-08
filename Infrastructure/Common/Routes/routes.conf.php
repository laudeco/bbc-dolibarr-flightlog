<?php

use FlightLog\Http\Web\Controller\DamageController;
use FlightLog\Infrastructure\Common\Routes\Route;

global $db;

return [
    new Route('get_one_damage', DamageController::class, 'view'),
    new Route('get_list_damages', DamageController::class, 'listAction'),
];
?>