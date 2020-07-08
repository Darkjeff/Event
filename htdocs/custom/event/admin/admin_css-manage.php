<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012		JF FERRY			<jfefe@aternatik.fr>
 * Copyright (C) 2017		Eric GROULT			<eric@code42.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 *   	\file       event/index.php
 *		\ingroup    event
 *		\brief      Index page of module event
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
require_once("../class/event.class.php");
require_once("../class/eventlevel.class.php");
require_once("../class/day.class.php");
require_once("../lib/event.lib.php");

// Load traductions files requiredby by page
$langs->load("admin");
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");
$langs->load("cron");

// Get parameters
$id			= GETPOST('id','int');
$dayid		= GETPOST('dayid','int');
$action		= GETPOST('action','alpha');
$value 		= GETPOST('value','alpha');

// Protection if external user or not an admin
if ($user->societe_id > 0 || !$user->rights->event->setup_text)
{
	accessforbidden();
}

/*
 * Actions
*/
if ($action == 'setvar')
{
	$event_public_active=GETPOST('EVENT_PUBLIC_ACTIVE','alpha');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_ACTIVE', $event_public_active,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//TEST ERROR
	if (! $error)
	{
		$mesg = "<div class=\"ok\">".$langs->trans("SetupSaved")."</div>";
	}
	else
	{
		$mesg = "<div class=\"error\">".$langs->trans("Error")."</div>";
	}
}


/***************************************************
 * VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("EventSetupPageStyle"),'');

print_fiche_titre($langs->trans("EventSetupPageStyle"),$linkback,'setup');

// Configuration header
$head = event_admin_text_prepare_head();
dol_fiche_head($head, 'EventSetupPageManageCSS', $langs->trans('EventSetupStyle'), 0, 'event@event');

$form=new Form($db);

$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

dol_htmloutput_mesg($mesg,$mesgs);


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans('CssTitreMail'));

print '<table class="border" width="100%">';

// SEPARATOR
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("css_menu").'</td>';
print "</tr>";

//CSS CONFIG
$url = DOL_URL_ROOT.'/custom/event/admin/admin_css-manage.php';
$file = '../css/custom.css';

// check if form has been submitted
if (isset($_POST['text']))
{
    // save the text contents
    file_put_contents($file, $_POST['text']);
	}

// read the textfile
$text = file_get_contents($file);

print '<form action="" method="post">';
print '<tr><td width="35%">'.$langs->trans("CssTitreMail").'</td><td>';
print '<textarea name="text" cols="90" rows="39">'.htmlspecialchars($text).'</textarea>';
print '<tr>';
print '<td colspan="2" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"><input type="reset" class="button" value="'.$langs->trans("css_reset").'"></td>';
print '</tr>';
print '</form>';

print '</td></tr></table>'."\n";

// End of page
llxFooter();
$db->close();
