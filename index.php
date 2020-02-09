<?php
/* Copyright (C) 2007-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *    \file       flightlog/bbcvols_card.php
 *        \ingroup    flightlog
 *        \brief      This file is an example of a php page
 *                    Initialy built by build_class_from_table on 2017-02-09 11:10
 */

$res = 0;
if (!$res && file_exists("../main.inc.php")) {
    $res = @include '../main.inc.php';
}                    // to work if your module directory is into dolibarr root htdocs directory
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include '../../main.inc.php';
}            // to work if your module directory is into a subdir of root htdocs directory
if (!$res && file_exists("../../../dolibarr/htdocs/main.inc.php")) {
    $res = @include '../../../dolibarr/htdocs/main.inc.php';
}     // Used on dev env only
if (!$res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) {
    $res = @include '../../../../dolibarr/htdocs/main.inc.php';
}   // Used on dev env only
if (!$res) {
    die("Include of main fails");
}
// Change this following line to use the correct relative path from htdocs
include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');

dol_include_once('/flightlog/flightlog.inc.php');

$langs->load("mymodule@flightlog");
$langs->load("other");

global $db, $user;

$routes = require './Infrastructure/Common/Routes/routes.conf.php';
$routesGuards = require './Infrastructure/Common/Routes/guards.conf.php';

$response = null;
try{
    $routeName = GETPOST('r');

    $routeManager = new \FlightLog\Infrastructure\Common\Routes\RouteManager($db);
    $routeManager->load($routes);
    $routeManager->loadGuards($routesGuards);

    $response = $routeManager->__invoke($routeName, $user);

    if($response instanceof \FlightLog\Http\Web\Response\Redirect){
        if (headers_sent()) {
            echo(sprintf("<script>location.href='%s'</script>", $response->getUrl()));
            exit;
        }

        header(sprintf("Location: %s", $response->getUrl()));
        exit;
    }

}catch (\Exception $e){
    dol_syslog($e->getMessage(), LOG_ERR);
    $response = new \FlightLog\Http\Web\Response\Response($e->getMessage());
}

llxHeader('', 'Carnet de vol', '');

include $response->getTemplate();

// End of page
llxFooter();
$db->close();
