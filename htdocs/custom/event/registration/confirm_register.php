<?php
define('NOLOGIN','1');
if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');  // Do not check anti CSRF attack test
if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Do not check anti POST attack test
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1'); // If there is no need to load and show top and left menu

$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");

require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/registration.class.php");
require_once("../class/eventlevel.class.php");
require_once("../lib/event.lib.php");
require_once("../core/modules/event/modules_event.php");
require_once("../core/modules/registration/modules_registration.php");
require_once("../lib/html.formregistration.class.php");
require_once("confirm_register.function.php");

$langs->load('event@event');

$id_url = GETPOST('id');
$key_url = GETPOST('key');
$action = GETPOST('action');
$error = 0;

if (empty($id_url) || empty($key_url))
	$error++;

if ($error == 0)
{
	$regstat = new Registration($db);
	$regstat->fetch($id_url);
	$key = get_info_from_table('unique_key', $id_url);
	$statut = get_info_from_table('fk_statut', $id_url);
	$contact_id = get_info_from_table('fk_user_registered', $id_url);
	$contact = new Contact($db);
	$contact->fetch($contact_id);
}

if($key_url == 'consult') {
	$eventday = new Day($db);
	$eventday->fetch($id_url);
	$event = new Event($db);
	$event->fetch($eventday->fk_event);
}
elseif($key_url != 'consult') {
	$eventday = new Day($db);
	$eventday->fetch($regstat->fk_eventday);
	$event = new Event($db);
	$event->fetch($regstat->fk_event);
	$regstat = new Registration($db);
	$regstat->fetch($id_url);
	$user = new Contact($db);
	$user->fetch($regstat->fk_user_registered);
	$unique_key = "&key=".$regstat->getValueFrom('event_registration', $regstat->id, 'unique_key');
	}

$url = DOL_URL_ROOT."/custom/event/registration/confirm_register.php?id=".$regstat->id.$unique_key;
if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
	$url2 = '<a href="http://localhost'.$url.'">Lien</a>';
else
	$url2 = '<a href="'.$conf->global->EVENT_MAIN_URL_REGISTRATION.$url.'">Lien</a>';

$substit['__REGREF__'] = $regstat->ref;
$substit['__EVENEMENT__'] = $event->label;
$substit['__JOURNEE__'] = $eventday->label;
$substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event, 'day');
$substit['__PARTICIPANT__'] = dolGetFirstLastname($user->firstname, $user->lastname);
$substit['__TIMESTART__'] = $eventday->time_start;
$substit['__TIMEEND__'] = $eventday->time_end;
$substit['__LIEN_VALIDATION__'] = $url2;
$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;

/*### Action ###*/
if ($action == 'participate')
{
	$sujet=$conf->global->EVENT_PARTICIPATE_SUJET;
	$sujet= make_substitutions($sujet,$substit);

	$message=$langs->trans('HTML_HEADER').$conf->global->EVENT_PARTICIPATE_MESSAGE.$langs->trans('HTML_FOOTER');
	$message= make_substitutions($message, $substit);
	$message.='<br /><br />'.$conf->global->EVENT_REGISTRATION_SIGN_EMAIL;

	$regstat->setConfirmed('1');
	$regstat->SendByEMail($eventday->ref,$contact->email,$contact->id,$sujet,$message,($conf->global->EVENT_SEND_PDF=='-1'?'':'1'), '', ($conf->global->EVENT_MANAGE_ICS=='-1'?'':'1')); // 'ics' OU 'pdf');
	$url = $_SERVER['PHP_SELF'].'?id='.$id_url.'&key='.$key.'&action=after_modif';
	header('Location: '.$url);
}
else if ($action == 'not_participate')
{
	$sujet=$conf->global->EVENT_NOT_PARTICIPATE_SUJET;
	$sujet= make_substitutions($sujet,$substit);

	$message=$langs->trans('HTML_HEADER').$conf->global->EVENT_NOT_PARTICIPATE_MESSAGE.$langs->trans('HTML_FOOTER');
	$message= make_substitutions($message, $substit);
	$message.='<br /><br />'.$conf->global->EVENT_REGISTRATION_SIGN_EMAIL;

	$regstat->setCancelled('1');
	$regstat->SendByEMail($eventday->ref,$contact->email,$contact->id,$sujet,$message); // 'ics' OU 'pdf');
	$url = $_SERVER['PHP_SELF'].'?id='.$id_url.'&key='.$key.'&action=after_modif';
	header('Location: '.$url);
}
else
{

/*### View ###*/

print '
<html><head>
<title>'.$langs->trans('ValidateEvent').'</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';

print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/custom/event/css/registration.css">';
print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/custom/event/css/custom.css">';
print '</head>';
print '<body>';
print '<meta name="viewport" content="width=device-width"/>';
$form=new Form($db);
// print '<div align=center style="width:100%;background-color:white;">';

// LOGO HEADER
print '<div class="logo logo_footer_phone">';
print '</div>';

//DEBUG
if(MAIN_FEATURES_LEVEL=='3')
{
	print '<br /><br /><bold>DEBUG</bold>';
	print '<br />'.$id_url;
	print '<br />event :'.$event->ref;
	print '<br />eventday ref :'.$eventday->ref;
	print '<br />FK STATUT :'.$eventday->fk_statut;
	print '<br />registration_open :'.$eventday->registration_open;
	print '<br />eventday label :'.$eventday->label;
	print '<br />user registred :'.$user->lastname;
	print '<br />date_event :'.date("Ymd",$eventday->date_event);
	print '<br >Note private :'.$eventday->note;
	print '<br />now :'.date("Ymd");
	if (file_exists(DOL_DOCUMENT_ROOT."/custom/event/ics/event_".$eventday->ref.".ics"))  print '<br />ICS FILE EXIST';
	else print "<br />".DOL_DOCUMENT_ROOT."/custom/event/ics/event_".$eventday->ref.".ics".'ICS FILE NOT EXIST';
}

// DISPLAY
print '<div class="titre_blue"><h3>'.$langs->trans('DayDescription').'</h3></div>';

if ($key_url == 'consult') {
		$page = make_substitutions($eventday->description_web,$substit);
		show_description($page);
	}
elseif ($eventday->fk_statut != 9) {
	if ($key_url == $key) {
		// Display page content
		$page = make_substitutions($eventday->description_web,$substit);
		show_description($page);

		if(strtotime(date("Ymd",$eventday->date_event)) < strtotime(date("Ymd")))
		{
			print '<br />'.$langs->trans('DayAlreadyPast');
		}
		elseif ($eventday->registration_open =='0')
		{
			print '<br />'.$langs->trans('RegistrationIsNowClosed');
		}
		elseif ($statut == 4)
		{
			if ($action == "after_modif")
				print '<p class="ok"><strong>'.$langs->trans('ValidRegistration').'</strong></p>';
			else
				print '<p class="ok"><strong>'.$langs->trans('AlreadyConfirm').'</strong></p>';
			show_button($key, $id_url, 2);
		}
		elseif ($statut == 5)
		{
			if ($action == "after_modif")
				print '<p class="ok"><strong>'.$langs->trans('CancelRegistration').'</strong></p>';
			else
				print '<p class="warning"><strong>'.$langs->trans('AlreadyRefused').'</strong></p>';
			show_button($key, $id_url, 1);
		}
		elseif ($statut == 8)
		{
			print '<p class="warning"><strong>'.$langs->trans('WaitingList').'</strong></p>';
			show_button($key, $id_url, 2);
		}
		else
			show_button($key, $id_url, 3);
	}
	else
		print '<p class="warning"><strong>'.$langs->trans('NoValidInformation', $conf->global->EVENTDEFAULTMAIL).'</strong></p>';
}
else print '<p class="warning"><strong>'.$langs->trans('DayClose', $conf->global->EVENTDEFAULTMAIL).'</strong></p>';

print '<div class="logo_footer logo_footer_phone">';
print '</div>';

print '</div>';
print '</body></html>';
}


?>
