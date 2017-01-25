#!/usr/bin/env php
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

set_time_limit(0);

if (isset($_GET['DEBUG']))
{
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}

chdir(__DIR__);

DEFINE('INC_FROM_CRON_SCRIPT', true);
require '../config.php';

require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

$conf->entity = $argv[1];

$pidfile_path = $conf->birddy->multidir_output[$conf->entity] . '/run/birddydaemon.pid';
$handler = fopen($pidfile_path, 'w');
if (!$handler)
{
	echo 'ERROR: Can not write file "'.$pidfile_path.'"';
	exit;
}

fwrite($handler, dol_getmypid());
fclose($handler);

// TODO reload $conf with the right entity ($argv[1])
$address = !empty($conf->global->BIRDDY_SERVER_ADDR) ? $conf->global->BIRDDY_SERVER_ADDR : '127.0.0.1';
$port = !empty($conf->global->BIRDDY_PORT) ? $conf->global->BIRDDY_PORT : '8000';
$checkOrigin = (bool) $conf->global->BIRDDY_CHECK_ORIGIN;
$TOrigin = !empty($conf->global->BIRDDY_ORIGINS_ALLOWED) ? explode(',', $conf->global->BIRDDY_ORIGINS_ALLOWED) : '';

if (empty($address) || empty($port))
{
	echo 'Warning : server address or port is not configured';
	exit;
}

if ($address == '127.0.0.1' && empty($TOrigin))
{
	$TOrigin = array('http://127.0.0.1', 'http://localhost');
}
elseif (empty($TOrigin))
{
	$TOrigin = array($address);
}

dol_include_once('/birddy/phpwebsocket/server/lib/SplClassLoader.php');
$classLoader = new SplClassLoader('WebSocket', __DIR__ . '/../phpwebsocket/server/lib');
$classLoader->register();

dol_include_once('/birddy/class/birddy.class.php');

//$server = new \WebSocket\Server($address, $port, false);
$server = new BirddyServer($pidfile_path, $address, $port, false);

// TODO server settings: mettre les valeurs en conf
$server->setMaxClients(100);
$server->setCheckOrigin($checkOrigin);
if ($checkOrigin)
{
	foreach ($TOrigin as $origin)
	{
		if (!empty($origin)) $server->setAllowedOrigin($origin);
	}	
}

$server->setMaxConnectionsPerIp(100);
$server->setMaxRequestsPerMinute(2000);

// Hint: Status application should not be removed as it displays usefull server informations:
//$server->registerApplication('status', \WebSocket\Application\StatusApplication::getInstance());
$server->registerApplication('birddy', Birddy::getInstance());

$server->run();
