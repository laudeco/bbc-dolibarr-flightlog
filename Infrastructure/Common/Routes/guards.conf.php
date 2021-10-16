<?php

use FlightLog\Http\Web\Controller\DamageController;
use FlightLog\Http\Web\Controller\FlightController;
use FlightLog\Infrastructure\Common\Routes\Guard;
use FlightLog\Infrastructure\Common\Routes\Route;

global $db;

return [
    new Guard('get_one_damage', function(User $user){
        return $user->rights->flightlog->vol->financial;
    }),
    new Guard('create_damage', function(User $user){
        return $user->rights->flightlog->vol->financial;
    }),
];
?>