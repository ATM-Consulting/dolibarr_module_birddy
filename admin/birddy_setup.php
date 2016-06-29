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

if ($action == 'launch_daemon')
{
	$return_var = 0;
	
	$dir = dol_buildpath('/birddy/script/daemon.php');
	system('sh '.dol_buildpath('/birddy/script/launcher.sh').' "'.$dir.'"', $return_var);
	
	if ($return_var == 0)
	{
		setEventMessages($langs->trans('birddy_daemon_started'), array());
	}
	else
	{
		setEventMessages($langs->trans('birddy_daemon_launch_error', $return_var), array(), 'errors');
	}
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


print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="launch_daemon">';
print '<div class="tabsAction">';
print '<input type="submit" class="button" value="'.$langs->trans("birddy_Launch_daemon").'">';
print '</div>';
print '</form><br />';


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

print '</table>';

llxFooter();

$db->close();