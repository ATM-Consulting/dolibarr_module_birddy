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

/**
 * 	\file		admin/birddy.php
 * 	\ingroup	birddy
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/birddy.lib.php';

// Translations
$langs->load("birddy@birddy");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}
	
if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

if (in_array($action, array('start_daemon', 'stop_daemon', 'restart_daemon')))
{
	$output = array();
	$return_var = 0;
	
	$dir = dol_buildpath('/birddy/script/daemon.php');
	$dir = dirname($dir);
	
	switch ($action) {
		case 'start_daemon':
			exec('sh '.dol_buildpath('/birddy/script/launcher.sh').' start '.$dir, $output, $return_var);
			break;
		case 'stop_daemon':
			exec('sh '.dol_buildpath('/birddy/script/launcher.sh').' stop '.$dir, $output, $return_var);
			break;
		case 'restart_daemon':
			exec('sh '.dol_buildpath('/birddy/script/launcher.sh').' restart '.$dir, $output, $return_var);
			break;
		
		default:
			$return_var = -1;
			break;
	}
	
	if (!empty($output) && $return_var == 0)
	{
		setEventMessages('', $output);
	}
	elseif ($return_var > 0)
	{
		setEventMessages('birddy_error_'.$action, array(), 'errors');
	}

	header('Location: '.dol_buildpath('/birddy/admin/birddy_setup.php', 2));
	exit;
}

/*
 * View
 */
$page_name = "birddySetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = birddyAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module104021Name"),
    0,
    "birddy@birddy"
);

// Setup page goes here
$form=new Form($db);

print '<div class="warning">';
print ''; // TODO fournir les indications concernant les droits pour l'utilisateur www-data
print '</div>';

// # Conf server
$var=false;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("birddy_ServerParameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="100">'.$langs->trans("Value").'</td>'."\n";

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("BIRDDY_SERVER_ADDR").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_BIRDDY_SERVER_ADDR">';
print '<input type="text" name="BIRDDY_SERVER_ADDR" value="'.$conf->global->BIRDDY_SERVER_ADDR.'" placeholder="127.0.0.1" size="15" />';
print '&nbsp;<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</form>';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("BIRDDY_SERVER_ADDR").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_BIRDDY_PORT">';
print '<input type="text" name="BIRDDY_PORT" value="'.$conf->global->BIRDDY_PORT.'" placeholder="8000" size="5" />';
print '&nbsp;<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</form>';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$form->textwithpicto($langs->trans("BIRDDY_ORIGINS_ALLOWED"), $langs->trans("BIRDDY_ORIGINS_ALLOWED_help_info")).'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_BIRDDY_ORIGINS_ALLOWED">';
print '<input name="BIRDDY_ORIGINS_ALLOWED" placeholder="127.0.0.1,localhost" value="'.$conf->global->BIRDDY_ORIGINS_ALLOWED.'" />';
print '&nbsp;<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</form>';
print '</td></tr>';


print '</table>';

print '<div class="tabsAction">';

print '<div class="inline-block divButAction">';
print '<a href="'.dol_buildpath('/birddy/admin/birddy_setup.php', 1).'?action=start_daemon" class="butAction">'.$langs->trans("birddy_Start_daemon").'</a>';
print '</div>';

print '<div class="inline-block divButAction">';
print '<a href="'.dol_buildpath('/birddy/admin/birddy_setup.php', 1).'?action=stop_daemon" class="butActionDelete">'.$langs->trans("birddy_Stop_daemon").'</a>';
print '</div>';

print '<div class="inline-block divButAction">';
print '<a href="'.dol_buildpath('/birddy/admin/birddy_setup.php', 1).'?action=restart_daemon" class="butActionDelete">'.$langs->trans("birddy_Restart_daemon").'</a>';
print '</div>';
		

		
print '</div>';

// # Conf chat
$var=false;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("birddy_ChatParameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="100">'.$langs->trans("Value").'</td>'."\n";

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("BIRDDY_SHOW_USER_PICTO").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print ajax_constantonoff('BIRDDY_SHOW_USER_PICTO');
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("BIRDDY_USER_CAN_SPEAK_WITH_OTHER_ENTITY").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print ajax_constantonoff('BIRDDY_USER_CAN_SPEAK_WITH_OTHER_ENTITY');
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("BIRDDY_USER_CAN_SPEAK_WITH_ENTITY_ZERO").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print ajax_constantonoff('BIRDDY_USER_CAN_SPEAK_WITH_ENTITY_ZERO');
print '</td></tr>';


print '</table>';

llxFooter();

$db->close();