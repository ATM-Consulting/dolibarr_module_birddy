<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2016 ATM Consulting <support@atm-consulting.fr>
 * Copyright (C) 2016 Pierre-Henry Favre <phf@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
if (isset($_GET['DEBUG']))
{
	ini_set('display_errors', 1);
	error_reporting(E_ALL);	
}

DEFINE('INC_FROM_CRON_SCRIPT', true);
//chdir(dirname(__FILE__));
require '../config.php';

dol_include_once('/birddy/phpwebsocket/server/lib/SplClassLoader.php');

$classLoader = new SplClassLoader('WebSocket', __DIR__ . '/../phpwebsocket/server/lib');
$classLoader->register();

$server = new \WebSocket\Server('10.0.2.15', 8000, false);

// TODO server settings: mettre les valeurs en conf
$server->setMaxClients(100);
$server->setCheckOrigin(true);
$server->setAllowedOrigin('http://localhost'); // à appeler autant de fois que nécessaire pour autoriser +sieurs origines
$server->setMaxConnectionsPerIp(100);
$server->setMaxRequestsPerMinute(2000);

// Hint: Status application should not be removed as it displays usefull server informations:
//$server->registerApplication('status', \WebSocket\Application\StatusApplication::getInstance());
$server->registerApplication('demo', \WebSocket\Application\DemoApplication::getInstance());

$server->run();