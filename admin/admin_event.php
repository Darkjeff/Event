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
	$level_mandatory=GETPOST('EVENT_LEVEL_REQUIRED','int');
	$res = dolibarr_set_const($db, 'EVENT_LEVEL_REQUIRED', $level_mandatory,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$mail_required=GETPOST('EVENT_REGISTRATION_MAIL_REQUIRED','int');
	$res = dolibarr_set_const($db, 'EVENT_REGISTRATION_MAIL_REQUIRED', $mail_required,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_main_url_registration=GETPOST('event_main_url_registration','alpha');
	$res = dolibarr_set_const($db, 'EVENT_MAIN_URL_REGISTRATION', $event_main_url_registration,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$sms_from=GETPOST('EVENT_SMS_NUMBER_FROM','alpha');
	$res = dolibarr_set_const($db, 'EVENT_SMS_NUMBER_FROM', $sms_from,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_send_email=GETPOST('event_send_email','int');
	$res = dolibarr_set_const($db, 'EVENT_SEND_EMAIL', $event_send_email,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_send_pdf=GETPOST('event_send_pdf','int');
	$res = dolibarr_set_const($db, 'EVENT_SEND_PDF', $event_send_pdf,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$limit_expire=GETPOST('EVENT_REGISTRATION_LIMIT_EXPIRE','alpha');
	$res = dolibarr_set_const($db, 'EVENT_REGISTRATION_LIMIT_EXPIRE', $limit_expire,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$default_level_dispo=GETPOST('EVENT_LEVEL_DEFAULT_LEVEL_DISPO','int');
	$res = dolibarr_set_const($db, 'EVENT_LEVEL_DEFAULT_LEVEL_DISPO', $default_level_dispo,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$default_level_limit=GETPOST('EVENT_LIMIT_LEVEL_PLACE','int');
	$res = dolibarr_set_const($db, 'EVENT_LIMIT_LEVEL_PLACE', $default_level_limit,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_hide_group=GETPOST('event_hide_group','int');
	$res = dolibarr_set_const($db, 'EVENT_HIDE_GROUP', $event_hide_group,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_hide_pdf_bill=GETPOST('event_hide_pdf_bill','int');
	$res = dolibarr_set_const($db, 'EVENT_HIDE_PDF_BILL', $event_hide_pdf_bill,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_manage_ics=GETPOST('event_manage_ics','int');
	$res = dolibarr_set_const($db, 'EVENT_MANAGE_ICS', $event_manage_ics,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_registration_block_tiers=GETPOST('event_registration_block_tiers','int');
	$res = dolibarr_set_const($db, 'EVENT_REGISTRATION_BLOCK_TIERS', $event_registration_block_tiers,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_expiration=GETPOST('event_expiration','int');
	$res = dolibarr_set_const($db, 'EVENT_EXPIRATION', $event_expiration,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$event_inscription_statement=GETPOST('event_inscription_statement','int');
	$res = dolibarr_set_const($db, 'EVENT_INSCRIPTION_STATEMENT', $event_inscription_statement,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_BLOCK_RELANCE_VALID
	$event_block_relance_valid=GETPOST('event_block_relance_valid','int');
	$res = dolibarr_set_const($db, 'EVENT_BLOCK_RELANCE_VALID', $event_block_relance_valid,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_BLOCK_RELANCE_WAITING
	$event_block_relance_waiting=GETPOST('event_block_relance_waiting','int');
	$res = dolibarr_set_const($db, 'EVENT_BLOCK_RELANCE_WAITING', $event_block_relance_waiting,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_BLOCK_REGISTRATION_TAG
	$event_block_registration_tag=GETPOST('event_block_registration_tag','int');
	$res = dolibarr_set_const($db, 'EVENT_BLOCK_REGISTRATION_TAG', $event_block_registration_tag,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_PUBLIC_REGISTRATION_LIMIT_DATE
	$event_public_registration_limit_date=GETPOST('EVENT_PUBLIC_REGISTRATION_LIMIT_DATE','alpha');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_REGISTRATION_LIMIT_DATE', $event_public_registration_limit_date,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE
	$event_public_unregistration_limit_date=GETPOST('EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE','alpha');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE', $event_public_unregistration_limit_date,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_PUBLIC_UNIT_NAME
	$event_public_unit_name=GETPOST('EVENT_PUBLIC_UNIT_NAME','alpha');
	$res = dolibarr_set_const($db, 'EVENT_PUBLIC_UNIT_NAME', $event_public_unit_name,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EVENT_PLACE_AVAILABLE
	$event_place_available=GETPOST('EVENT_PLACE_AVAILABLE','int');
	$res = dolibarr_set_const($db,'EVENT_PLACE_AVAILABLE',$event_place_available,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//Permettre les inscriptions / interdire
	$event_switch_register=GETPOST('EVENT_SWITCH_REGISTER','int');
	$res = dolibarr_set_const($db,'EVENT_SWITCH_REGISTER',$event_switch_register,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//event_PrefixNameEventDay
	$event_PrefixNameEventDay=GETPOST('event_PrefixNameEventDay','alpha');
	$res = dolibarr_set_const($db, 'PREFIX_NAME_EVENTDAY',$event_PrefixNameEventDay,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//event_EventActiveByDefault
	$event_EventActiveByDefault=GETPOST('event_EventActiveByDefault','int');
	$res = dolibarr_set_const($db, 'EVENT_ACTIVE_BY_DEFAULT',$event_EventActiveByDefault,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//event_DayActiveByDefault
	$event_DayActiveByDefault=GETPOST('event_DayActiveByDefault','int');
	$res = dolibarr_set_const($db, 'DAY_ACTIVE_BY_DEFAULT',$event_DayActiveByDefault,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//event_DisableCreate1stDayByDefaut
	$event_DisableCreate1stDayByDefaut=GETPOST('event_DisableCreate1stDayByDefaut','int');
	$res = dolibarr_set_const($db,'DISABLE_CREATE_1ST_DAY_BY_DEFAULT',$event_DisableCreate1stDayByDefaut,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//event_ActiveCloneFunc
	$event_ActiveCloneFunc=GETPOST('event_ActiveCloneFunc','int');
	$res = dolibarr_set_const($db, 'EVENT_ACTIVE_CLONE_FUNC',$event_ActiveCloneFunc,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//eventday_ActiveCloneFunc
	$eventday_ActiveCloneFunc=GETPOST('eventday_ActiveCloneFunc','int');
	$res = dolibarr_set_const($db, 'EVENTDAY_ACTIVE_CLONE_FUNC',$eventday_ActiveCloneFunc,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//Delay before relaunch Confirmed registration
	$event_Delaybeforlaunchconfirmed=GETPOST('EVENT_DELAY_BEFORE_RELAUNCH_CONFIRMED','int');
	$res = dolibarr_set_const($db, 'EVENT_DELAY_BEFORE_RELAUNCH_CONFIRMED',$event_Delaybeforlaunchconfirmed,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//Delay before relaunch Renew registration
	$event_Delaybeforlaunchwaiting=GETPOST('EVENT_DELAY_BEFORE_RELAUNCH_WAITING','int');
	$res = dolibarr_set_const($db, 'EVENT_DELAY_BEFORE_RELAUNCH_WAITING',$event_Delaybeforlaunchwaiting,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//ValidRegistrationAfterCreation
	$registration_valid_after_create=GETPOST('registration_valid_after_create','alpha');
	$res = dolibarr_set_const($db,'REGISTRATION_VALID_AFTER_CREATE',$registration_valid_after_create,'',0,'',$conf->entity);
	if (! $res > 0) $error++;

	//EventDefaultMail
	$EventDefaultMail=GETPOST('EventDefaultMail','alpha');
	$res= dolibarr_set_const($db,'EVENTDEFAULTMAIL',$EventDefaultMail,'',0,'',$conf->entity);
	if (!res > 0) $error++;

	//TEST ERROR
	if (! $error) {
		$mesg = "<div class=\"ok\">".$langs->trans("SetupSaved")."</div>";
	}
	else {
		$mesg = "<div class=\"error\">".$langs->trans("Error")."</div>";
	}
}

if ($action == 'add_level' && $user->rights->event->write)
{
	$error=0;
	$mesg='';


	if (!GETPOST("label",'alpha'))
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Label")).'</div>';
		$error++;
	}


	if (! $error)
	{
		$object = new Eventlevel($db);

		$object->label				= $_POST["label"];
		$object->description		= $_POST["description"];
		$object->datec				= dol_now();
		$object->rang				= $_POST["rang"];


		$result = $object->create($user);
		if ($result > 0)
		{
			$mesg = '<div class="ok">'.$langs->trans('LevelCreatedSuccess').'</div>';
		}
		else
		{
			$langs->load("errors");
			$mesg='<div class="error">'.$langs->trans($object->error).'</div>';
			$action = 'create';
		}
	}
	else
	{
		$action = 'new';
	}
}

if($action == "update_level" && $user->rights->event->write)
{

	$level = new Eventlevel($db);

	$level->fetch($id);
	$level->rang = GETPOST('rang','int');
	$level->label = GETPOST('label','alpha');
	$level->description = GETPOST('description','alpha');
	$result = $level->update($user);
	if ($result > 0)
	{
		$mesg = '<div class="ok">'.$langs->trans('LevelUpdatedSuccess').'</div>';
	}
	else
	{
		$langs->load("errors");
		$mesg='<div class="error">'.$langs->trans($object->error).'</div>';
		$action = '';
	}
}
if($action == "delete_level" && $user->rights->event->write)
{

	$level = new Eventlevel($db);

	$level->fetch($id);
	if ($result > 0)
	{
		if($level->delete($user) > 0) {
			setEventMessage($langs->trans('LevelDeletedSuccess'));
			header("Location: ".$_SERVER['PHP_SELF']);
			exit;
		}
		else
		{
			$langs->load("errors");
			setEventMessage($langs->trans($object->error));
			$action = '';
		}
	}
	else
	{
		$langs->load("errors");
		setEventMessage($langs->trans($object->error));
		$action = '';
	}
}
if ($action == 'set')
{
	$label = GETPOST('label','alpha');
	$scandir = GETPOST('scandir','alpha');

	$type='registration';
	$sql = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity, libelle, description)";
	$sql.= " VALUES ('".$db->escape($value)."','".$type."',".$conf->entity.", ";
	$sql.= ($label?"'".$db->escape($label)."'":'null').", ";
	$sql.= (! empty($scandir)?"'".$db->escape($scandir)."'":"null");
	$sql.= ")";
	if ($db->query($sql))
	{

	}
}
if ($action == 'setdoc')
{
	$label = GETPOST('label','alpha');
	$scandir = GETPOST('scandir','alpha');

	$db->begin();

	if (dolibarr_set_const($db, "EVENT_REGISTRATION_ADDON_PDF",$value,'chaine',0,'',$conf->entity))
	{
		$conf->global->EVENT_REGISTRATION_ADDON_PDF = $value;
	}

	// On active le modele
	$type='registration';
	$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."document_model";
	$sql_del.= " WHERE nom = '".$db->escape($value)."'";
	$sql_del.= " AND type = '".$type."'";
	$sql_del.= " AND entity = ".$conf->entity;
	$result1=$db->query($sql_del);

	$sql = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity, libelle, description)";
	$sql.= " VALUES ('".$value."', '".$type."', ".$conf->entity.", ";
	$sql.= ($label?"'".$db->escape($label)."'":'null').", ";
	$sql.= (! empty($scandir)?"'".$scandir."'":"null");
	$sql.= ")";
	$result2=$db->query($sql);
	if ($result1 && $result2)
	{
		$db->commit();
	}
	else
	{
		$db->rollback();
	}
}

/***************************************************
 * VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("EventSetupBackOfficeTitle"),'');

print_fiche_titre($langs->trans("EventSetupBackOfficeTitle"),$linkback,'setup');

// Configuration header
$head = event_admin_prepare_head();
dol_fiche_head($head, 'EventSetupBackOffice', $langs->trans("Module1680Name"), 0, 'event@event');

$form=new Form($db);

$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

dol_htmloutput_mesg($mesg,$mesgs);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvar">';

print load_fiche_titre($langs->trans('AdminEventBehaviour'));

print '<table class="border" width="100%">';

print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("AdminManageRuleInvitation").'</td>';
print "</tr>";

// Masquage group
if (isset($conf->global->EVENT_HIDE_GROUP))
{
	if ($conf->global->EVENT_HIDE_GROUP > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_hide_group','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td colspan="">'.$langs->trans('Valid_hide_group');
print '</td>';
print '<td>';
print '<input type="radio" id="event_hide_group_confirm" name="event_hide_group" value="1" '.$checkedYes.'/> <label for="event_hide_group_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_hide_group_cancel" name="event_hide_group" '.$checkedNo.' value="-1"/> <label for="event_hide_group_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// Rendre obligatoire les groupes pour les inscriptions
print '<tr class="impair">';
print '<td>'.$langs->trans("EventMakeLevelRequired").'</td>';
print '<td>';
$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
print $form->selectarray("EVENT_LEVEL_REQUIRED",$arrval,$conf->global->EVENT_LEVEL_REQUIRED);
print '</td>';
print '</tr>';

// Rendre obligatoire le mail pour les inscriptions
print '<tr class="pair">';
print '<td>'.$langs->trans("EventRegistrationMakeEmailRequired").'</td>';
print '<td>';
$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
print $form->selectarray("EVENT_REGISTRATION_MAIL_REQUIRED",$arrval,$conf->global->EVENT_REGISTRATION_MAIL_REQUIRED);
print '</td>';
print '</tr>';

// ValidRegistrationAfterCreation
if ($conf->global->REGISTRATION_VALID_AFTER_CREATE=='1') {
		$checked2='';
		$checked1='checked="checked"';
		$checked0='';
	}elseif($conf->global->REGISTRATION_VALID_AFTER_CREATE=='-1'){
		$checked2='';
		$checked1='';
		$checked0='checked="checked"';
	}elseif($conf->global->REGISTRATION_VALID_AFTER_CREATE=='2'){
		$checked2='checked="checked"';
		$checked1='';
		$checked0='';
	}

print '<tr class="pair"><td>'.$langs->trans('AdminValidRegistrationAfterCreationDefault');
print '</td>';
print '<td>';
print '<input type="radio" id="registration_valid_after_create_confirm" name="registration_valid_after_create" value="2" '.$checked2.'/> <label for="registration_valid_after_create">'.$langs->trans('ValidParticipationAfterCreation').'</label>';
print '<br/>';
print '<input type="radio" id="registration_valid_after_create_confirm" name="registration_valid_after_create" value="1" '.$checked1.'/> <label for="registration_valid_after_create">'.$langs->trans('ValidRegistrationAfterCreation').'</label>';
print '<br/>';
print '<input type="radio" id="registration_valid_after_create_confirm" name="registration_valid_after_create" value="-1" '.$checked0.'/> <label for="registration_valid_after_create">'.$langs->trans('StandbyRegistrationAfterCreation').'</label>';
print '</td>';
print '</tr>';

// Envoyer un Email d'invitation
if (isset($conf->global->EVENT_SEND_EMAIL))
{
	if ($conf->global->EVENT_SEND_EMAIL > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_send_email','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr class="impair"><td>'.$langs->trans('ValidSendEmail');
print '</td>';
print '<td>';
print '<input type="radio" id="send_email_confirm" name="event_send_email" value="1" '.$checkedYes.'/> <label for="send_email_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="send_email_cancel" name="event_send_email" '.$checkedNo.' value="-1"/> <label for="send_email_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// Envoyer un PDF en pièce jointe de l'invitation
if (isset($conf->global->EVENT_SEND_PDF))
{
	if ($conf->global->EVENT_SEND_PDF > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_send_pdf','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr class="pair"><td>'.$langs->trans('ValidSendPDF');
print '</td>';
print '<td>';
print '<input type="radio" id="send_pdf_confirm" name="event_send_pdf" value="1" '.$checkedYes.'/> <label for="send_pdf_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="send_pdf_cancel" name="event_send_pdf" '.$checkedNo.' value="-1"/> <label for="send_pdf_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// EVENT_MANAGE_ICS
if (isset($conf->global->EVENT_MANAGE_ICS))
{
	if ($conf->global->EVENT_MANAGE_ICS > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_manage_ics','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td colspan="">'.$langs->trans('registration_event_manage_ics');
print '</td>';
print '<td>';
print '<input type="radio" id="event_manage_ics_confirm" name="event_manage_ics" value="1" '.$checkedYes.'/> <label for="event_manage_ics_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_manage_ics_cancel" name="event_manage_ics" '.$checkedNo.' value="-1"/> <label for="event_manage_ics_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// Url publique pour la validation des invitations
print '<tr class="pair">';
print '<td>'.$langs->trans("MainUrlRegistration").'</td>';
print '<td>';
print '<input type="text" name="event_main_url_registration" value="'.$conf->global->EVENT_MAIN_URL_REGISTRATION.'"  size="40"/>';
print '</td>';
print '</tr>';

// EventDefaultMail
print '<tr class="pair">';
print '<td>'.$langs->trans("EventDefaultMail").'</td>';
print '<td>';
print '<input type="text" name="EventDefaultMail" value="'.$conf->global->EVENTDEFAULTMAIL.'"  size="40"/>';
print '</td>';
print '</tr>';

// event_block_registration_tag
if (isset($conf->global->EVENT_BLOCK_REGISTRATION_TAG))
{
	if ($conf->global->EVENT_BLOCK_REGISTRATION_TAG > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_block_registration_tag','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr><td colspan="">'.$langs->trans('Event_block_registration_tag');
print '</td>';
print '<td>';
print '<input type="radio" id="event_block_registration_tag_confirm" name="event_block_registration_tag" value="1" '.$checkedYes.'/> <label for="event_block_registration_tag_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_block_registration_tag_cancel" name="event_block_registration_tag" '.$checkedNo.' value="-1"/> <label for="event_block_registration_tag_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("AdminManageRuleReminder").'</td>';
print "</tr>";

// Délai d'expiration des inscriptions
print '<tr class="impair">';
print '<td>'.$langs->trans("EventRegistrationLimitToExpire").'</td>';
print '<td>';
print '<input type="text" name="EVENT_REGISTRATION_LIMIT_EXPIRE" value="'.$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE.'" size="3"/>'.' '.$langs->trans('Days');
print '</td>';
print '</tr>';

// Nombre de place disponible par défaut pour les groupe(s) (0=Places illimité)
print '<tr class="pair">';
print '<td>'.$langs->trans("EventRegistrationLevelDefaultNumber").'</td>';
print '<td>';
print '<input type="text" name="EVENT_LEVEL_DEFAULT_LEVEL_DISPO" value="'.$conf->global->EVENT_LEVEL_DEFAULT_LEVEL_DISPO.'" size="3"/>'.$langs->trans('Place');
print '</td>';
print '</tr>';

// Seuil pour le calcul de disponibilité des places
print '<tr class="impair">';
print '<td>'.$langs->trans("EventRegistrationLevelDefaultLimit").'</td>';
print '<td>';
print '<input type="text" name="EVENT_LIMIT_LEVEL_PLACE" value="'.$conf->global->EVENT_LIMIT_LEVEL_PLACE.'"  size="3"/>'.$langs->trans('Place');
print '</td>';
print '</tr>';

// event_block_relance_valid
if (isset($conf->global->EVENT_BLOCK_RELANCE_VALID))
{
	if ($conf->global->EVENT_BLOCK_RELANCE_VALID > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_block_relance_valid','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr><td colspan="">'.$langs->trans('Event_block_relance_valid');
print '</td>';
print '<td>';
print '<input type="radio" id="event_block_relance_valid_confirm" name="event_block_relance_valid" value="1" '.$checkedYes.'/> <label for="event_block_relance_valid_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_block_relance_valid_cancel" name="event_block_relance_valid" '.$checkedNo.' value="-1"/> <label for="event_block_relance_valid_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// event_block_relance_waiting
if (isset($conf->global->EVENT_BLOCK_RELANCE_WAITING))
{
	if ($conf->global->EVENT_BLOCK_RELANCE_WAITING > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_block_relance_waiting','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr><td colspan="">'.$langs->trans('Event_block_relance_waiting');
print '</td>';
print '<td>';
print '<input type="radio" id="event_block_relance_waiting_confirm" name="event_block_relance_waiting" value="1" '.$checkedYes.'/> <label for="event_block_relance_waiting_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_block_relance_waiting_cancel" name="event_block_relance_waiting" '.$checkedNo.' value="-1"/> <label for="event_block_relance_waiting_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

//Delaybeforlaunchwaiting
print '<tr class="pair">';
print '<td>'.$langs->trans("Delaybeforlaunchwaiting").' - <a href="'.DOL_URL_ROOT.'/cron/list.php" target="_blank">'.$langs->trans('Setup').' '.$langs->trans('CronList').'</a></td>';
print '<td>';
print '<input type="text" name="EVENT_DELAY_BEFORE_RELAUNCH_WAITING" value="'.$conf->global->EVENT_DELAY_BEFORE_RELAUNCH_WAITING.'" size="3"/>'.' '.$langs->trans('Hours');
print '</td>';
print '</tr>';

//Delaybeforlaunchconfirmed
print '<tr class="impair">';
print '<td>'.$langs->trans("Delaybeforlaunchconfirmed").' - <a href="'.DOL_URL_ROOT.'/cron/list.php" target="_blank">'.$langs->trans('Setup').' '.$langs->trans('CronList').'</a></td>';
print '<td>';
print '<input type="text" name="EVENT_DELAY_BEFORE_RELAUNCH_CONFIRMED" value="'.$conf->global->EVENT_DELAY_BEFORE_RELAUNCH_CONFIRMED.'" size="3"/>'.' '.$langs->trans('Hours');
print '</td>';
print '</tr>';

print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("AdminManageRule1stCreate").'</td>';
print "</tr>";

// Prefixe pour le nom de la 1ère journée crée
print '<tr class="pair">';
print '<td>'.$langs->trans("PrefixNameEventDay").'</td>';
print '<td>';
print '<input type="text" name="event_PrefixNameEventDay" value="'.$conf->global->PREFIX_NAME_EVENTDAY.'" size="40"/>';
print '</td>';
print '</tr>';

// Journée active par défaut à la création
if (isset($conf->global->DISABLE_CREATE_1ST_DAY_BY_DEFAULT))
{
	if ($conf->global->DISABLE_CREATE_1ST_DAY_BY_DEFAULT > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('DISABLE_CREATE_1ST_DAY_BY_DEFAULT','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr class="pair">';
print '<td>'.$langs->trans("Event_DisableCreate1stDayByDefaut").'</td>';
print '<td>';
print '<input type="radio" id="event_DisableCreate1stDayByDefaut_confirm" name="event_DisableCreate1stDayByDefaut" value="1" '.$checkedYes.'/> <label for="event_DayActiveByDefault_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_DisableCreate1stDayByDefaut_cancel" name="event_DisableCreate1stDayByDefaut" '.$checkedNo.' value="0"/> <label for="event_DisableCreate1stDayByDefaut_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// Evénement actif par défaut à la création
if (isset($conf->global->EVENT_ACTIVE_BY_DEFAULT))
{
	if ($conf->global->EVENT_ACTIVE_BY_DEFAULT > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('EVENT_ACTIVE_BY_DEFAULT','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr class="pair">';
print '<td>'.$langs->trans("EventActiveByDefault").'</td>';
print '<td>';
print '<input type="radio" id="event_EventActiveByDefault_confirm" name="event_EventActiveByDefault" value="5" '.$checkedYes.'/> <label for="event_EventActiveByDefault_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_EventActiveByDefault_cancel" name="event_EventActiveByDefault" '.$checkedNo.' value="0"/> <label for="event_EventActiveByDefault_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// Journée active par défaut à la création
if (isset($conf->global->DAY_ACTIVE_BY_DEFAULT))
{
	if ($conf->global->DAY_ACTIVE_BY_DEFAULT > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('DAY_ACTIVE_BY_DEFAULT','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr class="pair">';
print '<td>'.$langs->trans("DayActiveByDefault").'</td>';
print '<td>';
print '<input type="radio" id="event_DayActiveByDefault_confirm" name="event_DayActiveByDefault" value="4" '.$checkedYes.'/> <label for="event_DayActiveByDefault_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_DayActiveByDefault_cancel" name="event_DayActiveByDefault" '.$checkedNo.' value="0"/> <label for="event_DayActiveByDefault_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// event_ActiveCloneFunc
if (isset($conf->global->EVENT_ACTIVE_CLONE_FUNC))
{
	if ($conf->global->EVENT_ACTIVE_CLONE_FUNC > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_ActiveCloneFunc','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr><td colspan="">'.$langs->trans('Event_ActiveCloneFunc');
print '</td>';
print '<td>';
print '<input type="radio" id="event_ActiveCloneFunc_confirm" name="event_ActiveCloneFunc" value="1" '.$checkedYes.'/> <label for="event_ActiveCloneFunc">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_ActiveCloneFunc_cancel" name="event_ActiveCloneFunc" '.$checkedNo.' value="-1"/> <label for="event_ActiveCloneFunc">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// eventday_ActiveCloneFunc - EVENTDAY_ACTIVECLONEFUNC
if (isset($conf->global->EVENTDAY_ACTIVE_CLONE_FUNC))
{
	if ($conf->global->EVENTDAY_ACTIVE_CLONE_FUNC > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('eventday_ActiveCloneFunc','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
print '<tr><td colspan="">'.$langs->trans('EventDay_ActiveCloneFunc');
print '</td>';
print '<td>';
print '<input type="radio" id="eventday_ActiveClone_confirm" name="eventday_ActiveCloneFunc" value="1" '.$checkedYes.'/> <label for="eventday_ActiveCloneFunc">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="eventday_ActiveClone_cancel" name="eventday_ActiveCloneFunc" '.$checkedNo.' value="-1"/> <label for="eventday_ActiveCloneFunc">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

print '<tr class="pair"><td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
print '</table>';

print '<br/>';
print load_fiche_titre($langs->trans('Admin_menu_manage_interface'));
print '<table class="border" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Description").'</td>';
print '<td width="400px">'.$langs->trans("Valeur").'</td>';
print "</tr>\n";

// Hide pdf bill - event_hide_pdf_bill & EVENT_HIDE_PDF_BILL
if (isset($conf->global->EVENT_HIDE_PDF_BILL))
{
	if ($conf->global->EVENT_HIDE_PDF_BILL > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_hide_pdf_bill','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td colspan="">'.$langs->trans('registration_event_hide_pdf_bill');
print '</td>';
print '<td>';
print '<input type="radio" id="event_hide_pdf_bill_confirm" name="event_hide_pdf_bill" value="1" '.$checkedYes.'/> <label for="event_hide_pdf_bill_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_hide_pdf_bill_cancel" name="event_hide_pdf_bill" '.$checkedNo.' value="-1"/> <label for="event_hide_pdf_bill_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// Hide registration group
if (isset($conf->global->EVENT_REGISTRATION_BLOCK_TIERS))
{
	if ($conf->global->EVENT_REGISTRATION_BLOCK_TIERS > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_registration_block_tiers','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td colspan="">'.$langs->trans('registration_block_group');
print '</td>';
print '<td>';
print '<input type="radio" id="event_registration_block_group_confirm" name="event_registration_block_tiers" value="1" '.$checkedYes.'/> <label for="event_hide_group_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_registration_block_group_cancel" name="event_registration_block_tiers" '.$checkedNo.' value="-1"/> <label for="event_hide_group_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// Hide Page Expiration
if (isset($conf->global->EVENT_EXPIRATION))
{
	if ($conf->global->EVENT_EXPIRATION > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_expiration','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td colspan="">'.$langs->trans('RegistrationExpiration');
print '</td>';
print '<td>';
print '<input type="radio" id="event_expiration_confirm" name="event_expiration" value="1" '.$checkedYes.'/> <label for="event_expiration_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_expiration_cancel" name="event_expiration" '.$checkedNo.' value="-1"/> <label for="event_expiration_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// Inscription statement
if (isset($conf->global->EVENT_INSCRIPTION_STATEMENT))
{
	if ($conf->global->EVENT_INSCRIPTION_STATEMENT > 0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}
else
{
	if (GETPOST('event_inscription_statement','int')>0) {
		$checkedYes='checked="checked"';
		$checkedNo='';
	}else {
		$checkedYes='';
		$checkedNo='checked="checked"';
	}
}

print '<tr><td colspan="">'.$langs->trans('RegistrationInscriptionStatement');
print '</td>';
print '<td>';
print '<input type="radio" id="event_inscription_statement_confirm" name="event_inscription_statement" value="1" '.$checkedYes.'/> <label for="event_inscription_statement_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="event_inscription_statement_cancel" name="event_inscription_statement" '.$checkedNo.' value="-1"/> <label for="event_inscription_statement_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

// // SMS - numéro d'envoi
// print '<tr class="impair">';
// print '<td>'.$langs->trans("EventSmsNumberFrom").'</td>';
// print '<td>';
// if ($conf->global->MAIN_SMS_SENDMODE == 'ovh')        // For backward compatibility        @deprecated
// {
// 	dol_include_once('/ovh/class/ovhsms.class.php');
// 	if(class_exists('OvhSms'))
// 	{
// 		try
// 		{
// 			$sms = new OvhSms($db);
// 			if (empty($conf->global->OVHSMS_ACCOUNT))
// 			{
// 				$resultsender = 'ErrorOVHSMS_ACCOUNT not defined';
// 			}
// 			else
// 			{
// 				$resultsender = $sms->SmsSenderList();
// 			}
// 		}
// 		catch(Exception $e)
// 		{
// 			dol_print_error('','Error to get list of senders: '.$e->getMessage());
// 		}
// 	}
// }
// else if (!empty($conf->global->MAIN_SMS_SENDMODE))    // $conf->global->MAIN_SMS_SENDMODE looks like a value 'class@module'
// {
// 	$tmp=explode('@',$conf->global->MAIN_SMS_SENDMODE);
// 	$classfile=$tmp[0]; $module=(empty($tmp[1])?$tmp[0]:$tmp[1]);
// 	dol_include_once('/'.$module.'/class/'.$classfile.'.class.php');
// 	try
// 	{
// 		$classname=ucfirst($classfile);
// 		$sms = new $classname($db);
// 		$resultsender = $sms->SmsSenderList();
// 	}
// 	catch(Exception $e)
// 	{
// 		dol_print_error('','Error to get list of senders: '.$e->getMessage());
// 		exit;
// 	}
// }
// else {
// 	print $langs->trans("EventSmsNoModuleConfigured");
// }

// if (is_array($resultsender) && count($resultsender) > 0)
// {
// 	print '<select name="EVENT_SMS_NUMBER_FROM" id="valid" class="flat">';
// 	foreach($resultsender as $obj)
// 	{
// 		print '<option value="'.$obj->number.'">'.$obj->number.'</option>';
// 	}
// 	print '</select>';
// }
// else
// {
// 	print '<span class="error">'.$langs->trans("SmsNoPossibleRecipientFound");
// 	if (is_object($sms) && ! empty($sms->error)) print ' '.$sms->error;
// 	print '</span>';
// }
// print '</td>';
// print '</tr>';

print '<tr class="pair"><td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
print '</table>';

print '<br />';

// End of page
llxFooter();
$db->close();
