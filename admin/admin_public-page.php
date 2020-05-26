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

define('NOSCANPOSTFORINJECTION', 1);

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
if ($user->societe_id > 0 || !$user->rights->event->setup)
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

	$event_public_header=GETPOST('EVENT_PUBLIC_HEADER');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_HEADER', dol_htmlcleanlastbr($event_public_header),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_public_navbar=GETPOST('EVENT_PUBLIC_NAVBAR');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_NAVBAR', dol_htmlcleanlastbr($event_public_navbar),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_public_content=GETPOST('EVENT_PUBLIC_CONTENT');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_CONTENT', dol_htmlcleanlastbr($event_public_content),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_public_footer=GETPOST('EVENT_PUBLIC_FOOTER');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_FOOTER', dol_htmlcleanlastbr($event_public_footer),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_boutique_validation=GETPOST('EVENT_BOUTIQUE_VALIDATION');
	$res = dolibarr_set_const($db, 'EVENT_BOUTIQUE_VALIDATION', dol_htmlcleanlastbr($event_boutique_validation),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_boutique_cgv=GETPOST('EVENT_BOUTIQUE_CGV');
	$res = dolibarr_set_const($db, 'EVENT_BOUTIQUE_CGV', dol_htmlcleanlastbr($event_boutique_cgv),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_boutique_cgu=GETPOST('EVENT_BOUTIQUE_CGU');
	$res = dolibarr_set_const($db, 'EVENT_BOUTIQUE_CGU', dol_htmlcleanlastbr($event_boutique_cgu),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_mail_subject=GETPOST('EVENT_MAIL_SUBJECT');
	$res = dolibarr_set_const($db, 'EVENT_MAIL_SUBJECT', dol_htmlcleanlastbr($event_mail_subject),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_mail_content=GETPOST('EVENT_MAIL_CONTENT');
	$res = dolibarr_set_const($db, 'EVENT_MAIL_CONTENT', dol_htmlcleanlastbr($event_mail_content),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//Descriptions
	$event_public_description_1=GETPOST('EVENT_PUBLIC_DESCRIPTION_1');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_DESCRIPTION_1', dol_htmlcleanlastbr($event_public_description_1),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_public_description_boutique=GETPOST('EVENT_PUBLIC_DESCRIPTION_BOUTIQUE');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_DESCRIPTION_BOUTIQUE', dol_htmlcleanlastbr($event_public_description_boutique),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_public_description_2=GETPOST('EVENT_PUBLIC_DESCRIPTION_2');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_DESCRIPTION_2', dol_htmlcleanlastbr($event_public_description_2),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_public_description_3=GETPOST('EVENT_PUBLIC_DESCRIPTION_3');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_DESCRIPTION_3', dol_htmlcleanlastbr($event_public_description_3),'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_public_website=GETPOST('EVENT_PUBLIC_WEBSITE');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_WEBSITE', $event_public_website,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	// EVENT_PUBLIC_UNIT_NAME
	$event_public_unit_name=GETPOST('EVENT_PUBLIC_UNIT_NAME','alpha');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_UNIT_NAME', $event_public_unit_name,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//Nombre d'unité / nombre de place

	//event_place_available
	$event_place_available=GETPOST('EVENT_PLACE_AVAILABLE','int');
	$res = dolibarr_set_const($db,'EVENT_PLACE_AVAILABLE',$event_place_available,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//Permettre les inscriptions / interdire
	$event_switch_register=GETPOST('EVENT_SWITCH_REGISTER','int');
	$res = dolibarr_set_const($db,'EVENT_SWITCH_REGISTER',$event_switch_register,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_switch_unit=GETPOST('EVENT_SWITCH_UNIT','int');
	$res = dolibarr_set_const($db,'EVENT_SWITCH_UNIT',$event_switch_unit,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//Afficher la navbar
	$event_switch_navbar=GETPOST('EVENT_SWITCH_NAVBAR','int');
	$res = dolibarr_set_const($db,'EVENT_SWITCH_NAVBAR',$event_switch_navbar,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//SWITCH BOUTIQUE
	$event_switch_boutique=GETPOST('EVENT_SWITCH_BOUTIQUE','int');
	$res = dolibarr_set_const($db,'EVENT_SWITCH_BOUTIQUE',$event_switch_boutique,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_PUBLIC_REGISTRATION_LIMIT_DATE
	$event_public_registration_limit_date=GETPOST('EVENT_PUBLIC_REGISTRATION_LIMIT_DATE','alpha');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_REGISTRATION_LIMIT_DATE', $event_public_registration_limit_date,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE
	$event_public_unregistration_limit_date=GETPOST('EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE','alpha');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE', $event_public_unregistration_limit_date,'',0,'',$conf->entity);
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

llxHeader('',$langs->trans("EventSetupPagePublicTitle"),'');

print_fiche_titre($langs->trans("EventSetupPagePublicTitle"),$linkback,'setup');

// Configuration header
$head = event_admin_prepare_head();
dol_fiche_head($head, 'EventSetupPagePublic', $langs->trans("Module1680Name"), 0, 'event@event');

$form=new Form($db);

$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

dol_htmloutput_mesg($mesg,$mesgs);


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans('css_file'));

print '<table class="border" width="100%">';

// SEPARATOR
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("css_menu").'</td>';
print "</tr>";

//CSS CONFIG
$url = DOL_URL_ROOT.'/custom/event/admin/admin_public-page.php';
$file = '../public/public.css';

// check if form has been submitted
if (isset($_POST['text']))
{
    // save the text contents
    file_put_contents($file, $_POST['text']);
	}

// read the textfile
$text = file_get_contents($file);

print '<form action="" method="post">';
print '<tr><td width="35%">'.$langs->trans("css_titre").'</td><td>';
print '<textarea name="text" cols="90" rows="13">'.htmlspecialchars($text).'</textarea>';
print '<tr>';
print '<td colspan="2" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"><input type="reset" class="button" value="'.$langs->trans("css_reset").'"></td>';
print '</tr>';
print '</form>';

print '</td></tr></table>'."\n";

// SEPARATOR
print '<br />'.load_fiche_titre($langs->trans('AdminEventBehaviour'));

print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvar">';

print '<table class="border" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("AdminEventBehaviour").'</td>';
print "</tr>";

// EVENT_PUBLIC_ACTIVE
print '<tr>';
print '<td width="35%">'.$langs->trans("event_public_active").'</td>';
print '<td>';
$arrval=array('1'=>$langs->trans("Yes"),'0'=>$langs->trans("No"));
print $form->selectarray("EVENT_PUBLIC_ACTIVE",$arrval,$conf->global->EVENT_PUBLIC_ACTIVE);
print '</td>';
print '</tr>';

//SWITCH navbar
if (isset($conf->global->EVENT_SWITCH_NAVBAR))
{
	if ($conf->global->EVENT_SWITCH_NAVBAR > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('EVENT_SWITCH_NAVBAR','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td width="35%">'.$langs->trans('eventNavbarRegister');
print '</td>';
print '<td>';
print '<input type="radio" id="EVENT_SWITCH_NAVBAR_confirm" name="EVENT_SWITCH_NAVBAR" value="1" '.$checkedYes.'/> <label for="EVENT_SWITCH_NAVBAR_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="EVENT_SWITCH_NAVBAR_cancel" name="EVENT_SWITCH_NAVBAR" '.$checkedNo.' value="0"/> <label for="EVENT_SWITCH_NAVBAR_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

print '<tr><td width="35%">'.$langs->trans("EventPublicUnitName").'</td>';
print '<td>';
print '<input type="text" name="EVENT_PUBLIC_UNIT_NAME" value="'.$conf->global->EVENT_PUBLIC_UNIT_NAME.'" size="40"/>';
print '</td>';
print '</tr>';

if (isset($conf->global->EVENT_PLACE_AVAILABLE))
{
	if ($conf->global->EVENT_PLACE_AVAILABLE > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('EVENT_PLACE_AVAILABLE','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td width="35%">'.$langs->trans('eventPlaceAvailable');
print '</td>';
print '<td>';
print '<input type="radio" id="EVENT_PLACE_AVAILABLE_confirm" name="EVENT_PLACE_AVAILABLE" value="1" '.$checkedYes.'/> <label for="EVENT_PLACE_AVAILABLE_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="EVENT_PLACE_AVAILABLE_cancel" name="EVENT_PLACE_AVAILABLE" '.$checkedNo.' value="-1"/> <label for="EVENT_PLACE_AVAILABLE_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

//PUBLIC - Delaybeforeblockregistration
print '<tr><td>'.$langs->trans("EventPublicRegistrationLimitDate").'</td>';
print '<td>';
print '<input type="text" name="EVENT_PUBLIC_REGISTRATION_LIMIT_DATE" value="'.$conf->global->EVENT_PUBLIC_REGISTRATION_LIMIT_DATE.'" size="3"/>'.' '.$langs->trans('Hours');
print '</td>';
print '</tr>';

//PUBLIC - Delaybeforeblockunregistration
print '<tr><td>'.$langs->trans("EventPublicUnregistrationLimitDate").'</td>';
print '<td>';
print '<input type="text" name="EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE" value="'.$conf->global->EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE.'" size="3"/>'.' '.$langs->trans('Hours');
print '</td>';
print '</tr>';

// SAVE BUTTON
print '<tr><td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';

// SEPARATOR
print '</table>';
print '<br />';
print load_fiche_titre($langs->trans('SetupPagePublicTexte'));
print '<table class="border" width="100%">';

// INFO TITRE
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("SetupPagePublicTexte").'</td>';
print "</tr>";

// EVENT_PUBLIC_HEADER
print '<tr><td width="35%">'.$langs->trans("event_public_header").'</td><td colspan="2">';
print '<textarea name="EVENT_PUBLIC_HEADER" cols="90" rows="10">'.$conf->global->EVENT_PUBLIC_HEADER.'</textarea>';
print '</td></tr>'."\n";

// EVENT_PUBLIC_NAVBAR
print '<tr><td width="35%">'.$langs->trans("event_public_navbar").'</td><td colspan="2">';
print '<textarea name="EVENT_PUBLIC_NAVBAR" cols="90" rows="10">'.$conf->global->EVENT_PUBLIC_NAVBAR.'</textarea>';
print '</td></tr>'."\n";

// EVENT_PUBLIC_CONTENT
print '<tr><td width="35%">'.$langs->trans("event_public_content").'</td><td colspan="2" cols="90" rows="10">';
$doleditor = new DolEditor('EVENT_PUBLIC_CONTENT', (isset($conf->global->EVENT_PUBLIC_CONTENT)?$conf->global->EVENT_PUBLIC_CONTENT:''), '', 142, 'event_public_content', 'In', true, true, true, ROWS_4, 90);
$doleditor->Create();
print '</td></tr>'."\n";

// EVENT_PUBLIC_FOOTER
print '<tr><td width="35%">'.$langs->trans("event_public_footer").'</td><td colspan="2">';
print '<textarea name="EVENT_PUBLIC_FOOTER" cols="90" rows="10">'.$conf->global->EVENT_PUBLIC_FOOTER.'</textarea>';
print '</td></tr>'."\n";

// WEBSITE
print '<tr><td width="35%">'.$langs->trans("event_public_website").'</td><td colspan="2">';
print '<input type="text" name="EVENT_PUBLIC_WEBSITE" value="'.$conf->global->EVENT_PUBLIC_WEBSITE.'" size="40"/>';
print '</td></tr>'."\n";

// SAVE BUTTON
print '<tr><td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';

// SEPARATOR
print '</table>';
print '<br/>';
print load_fiche_titre($langs->trans('EVENT_MANAGE_ECOMMERCE'));
print '<table class="border" width="100%">';

// INFO TITRE
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("EVENT_MANAGE_ECOMMERCE").'</td>';
print "</tr>";

//SWITCH E-COMMERCE
if (isset($conf->global->EVENT_SWITCH_BOUTIQUE))
{
	if ($conf->global->EVENT_SWITCH_BOUTIQUE > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('EVENT_SWITCH_BOUTIQUE','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td>'.$langs->trans('EVENT_SWITCH_BOUTIQUE');
print '</td>';
print '<td>';
print '<input type="radio" id="EVENT_SWITCH_BOUTIQUE_confirm" name="EVENT_SWITCH_BOUTIQUE" value="1" '.$checkedYes.'/> <label for="EVENT_SWITCH_BOUTIQUE_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="EVENT_SWITCH_BOUTIQUE_cancel" name="EVENT_SWITCH_BOUTIQUE" '.$checkedNo.' value="0"/> <label for="EVENT_SWITCH_BOUTIQUE_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// E-COMMERCE
print '<tr><td width="35%">'.$langs->trans("EVENT_PUBLIC_DESCRIPTION_BOUTIQUE").'</td><td colspan="2">';
print '<textarea name="EVENT_PUBLIC_DESCRIPTION_BOUTIQUE" cols="90" rows="10">'.$conf->global->EVENT_PUBLIC_DESCRIPTION_BOUTIQUE.'</textarea>';
print '</td></tr>'."\n";

print '<tr><td width="35%">'.$langs->trans("EVENT_BOUTIQUE_VALIDATION").'</td><td colspan="2">';
print '<textarea name="EVENT_BOUTIQUE_VALIDATION" cols="90" rows="10">'.$conf->global->EVENT_BOUTIQUE_VALIDATION.'</textarea>';
print '</td></tr>'."\n";

print '<tr><td width="35%">'.$langs->trans("EVENT_BOUTIQUE_CGV").'</td><td colspan="2">';
print '<textarea name="EVENT_BOUTIQUE_CGV" cols="90" rows="1">'.$conf->global->EVENT_BOUTIQUE_CGV.'</textarea>';
print '</td></tr>'."\n";

print '<tr><td width="35%">'.$langs->trans("EVENT_BOUTIQUE_CGU").'</td><td colspan="2">';
print '<textarea name="EVENT_BOUTIQUE_CGU" cols="90" rows="1">'.$conf->global->EVENT_BOUTIQUE_CGU.'</textarea>';
print '</td></tr>'."\n";

// SEPARATOR
print '</table>';
print '<br/>';
print load_fiche_titre($langs->trans('EVENT_MANAGE_MAIL'));
print '<table class="border" width="100%">';

// INFO ITIRE
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("EVENT_MANAGE_MAIL").'</td>';
print "</tr>";

// MAIL SUBJECT
print '<tr><td width="35%">'.$langs->trans("EVENT_MAIL_SUBJECT").'</td><td colspan="2">';
print '<textarea name="EVENT_MAIL_SUBJECT" cols="90" rows="10" style="height: 30px;">'.$conf->global->EVENT_MAIL_SUBJECT.'</textarea>';
print '</td></tr>'."\n";

// MAIL CONTENT
print '<tr><td width="35%">'.$langs->trans("EVENT_MAIL_CONTENT").'</td><td colspan="2">';
$doleditor = new DolEditor('EVENT_MAIL_CONTENT', (isset($conf->global->EVENT_MAIL_CONTENT)?$conf->global->EVENT_MAIL_CONTENT:''), '', 142, 'EVENT_MAIL_CONTENT', 'In', true, true, true, ROWS_4, 90);
$doleditor->Create();
print '</td></tr>'."\n";
print '<tr><td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';

// SEPARATOR
print '</table>';
print '<br/>';
print load_fiche_titre($langs->trans('EVENT_MANAGE_PUBLIC_REGITRASTION'));
print '<table class="border" width="100%">';

// INFO TITRE
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("EVENT_MANAGE_PUBLIC_REGITRASTION").'</td>';
print "</tr>";

// PUBLIC REGISTRATION
if (isset($conf->global->EVENT_SWITCH_REGISTER))
{
	if ($conf->global->EVENT_SWITCH_REGISTER > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('EVENT_SWITCH_REGISTER','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td>'.$langs->trans('eventSwitchRegister');
print '</td>';
print '<td>';
print '<input type="radio" id="EVENT_SWITCH_REGISTER_confirm" name="EVENT_SWITCH_REGISTER" value="1" '.$checkedYes.'/> <label for="EVENT_SWITCH_REGISTER_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="EVENT_SWITCH_REGISTER_cancel" name="EVENT_SWITCH_REGISTER" '.$checkedNo.' value="0"/> <label for="EVENT_SWITCH_REGISTER_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

//descriptions
print '<tr><td width="35%">'.$langs->trans("EVENT_PUBLIC_DESCRIPTION_1").'</td><td colspan="2">';
$doleditor = new DolEditor('EVENT_PUBLIC_DESCRIPTION_1', (isset($conf->global->EVENT_PUBLIC_DESCRIPTION_1)?$conf->global->EVENT_PUBLIC_DESCRIPTION_1:''), '', 142, 'EVENT_PUBLIC_DESCRIPTION_1', 'In', true, true, true, ROWS_4, 90);
$doleditor->Create();
print '</td></tr>'."\n";

//descriptions
print '<tr><td width="35%">'.$langs->trans("EVENT_PUBLIC_DESCRIPTION_2").'</td><td colspan="2">';
$doleditor = new DolEditor('EVENT_PUBLIC_DESCRIPTION_2', (isset($conf->global->EVENT_PUBLIC_DESCRIPTION_2)?$conf->global->EVENT_PUBLIC_DESCRIPTION_2:''), '', 142, 'EVENT_PUBLIC_DESCRIPTION_2', 'In', true, true, true, ROWS_4, 90);
$doleditor->Create();
print '</td></tr>'."\n";

//description 3 :
print '<tr><td width="35%">'.$langs->trans("EVENT_PUBLIC_DESCRIPTION_3").'</td><td colspan="2">';
$doleditor = new DolEditor('EVENT_PUBLIC_DESCRIPTION_3', (isset($conf->global->EVENT_PUBLIC_DESCRIPTION_3)?$conf->global->EVENT_PUBLIC_DESCRIPTION_3:''), '', 142, 'EVENT_PUBLIC_DESCRIPTION_3', 'In', true, true, true, ROWS_4, 90);
$doleditor->Create();
print '</td></tr>'."\n";

print '<tr><td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
print '</tr>';

print '</table><br>';
print '</form>';



// End of page
llxFooter();
$db->close();
