<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012-2016 JF FERRY             <jfefe@aternatik.fr>
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
 *   	\file       event/registration/list.php
 *		\ingroup    event
 *		\brief      List of registration for an event day
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php";
require_once DOL_DOCUMENT_ROOT."/commande/class/commande.class.php";
require_once DOL_DOCUMENT_ROOT."/core/class/html.formmail.class.php";
require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/registration.class.php");
require_once("../class/eventlevel.class.php");
require_once("../lib/event.lib.php");
require_once("../core/modules/registration/modules_registration.php");
require_once("../class/html.formevent.class.php");
require_once("../core/modules/event/modules_event.php");
require_once("../day/fonctions.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

// Get parameters
$id			= GETPOST('id','int');
$dayid		= GETPOST('dayid');
$ref		= GETPOST('ref','alpha');
$action		= GETPOST('action','alpha');
$confirm 	= GETPOST('confirm','alpha');
$sortfield  = GETPOST("sortfield", 'alpha');
$sortorder  = GETPOST("sortorder", 'alpha');
$sujet      = GETPOST("subject",'alpha');
$message    = GETPOST("message");


if (!$sortfield) {
    $sortfield = 'lastname';
}
if (!$sortorder) {
    $sortorder = 'ASC';
}
$limit = $conf->liste_limit;
$page = GETPOST("page", 'int');
if ($page == -1) {
    $page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

if ($action == 'confirm_registration' && $confirm == 'yes')
{
	$object = new Registration($db);
	$object->fetch($id);
	$contact = new Contact($db);
	$event = new Event($db);
	$day = new Day($db);
	$event->fetch($object->fk_event);
	$contact->fetch($object->fk_user_registered);
	$day->fetch($object->fk_eventday);
	$filedir=$conf->event->dir_output."/".dol_sanitizeFileName($object->ref);
	if($object->fk_levelday!='0') $level->fetch($object->fk_levelday);

	$unique_key = $object->getValueFrom('event_registration', $object->id, 'unique_key');
	$url = DOL_URL_ROOT."/custom/event/registration/confirm_register.php?id=".$object->id."&key=".$unique_key;
	if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
		$url2 = '<a href="http://localhost'.$url.'">Lien</a>';
	else
		$url2 = '<a href="'.$conf->global->EVENT_MAIN_URL_REGISTRATION.$url.'">Lien</a>';

	$sujet=$conf->global->EVENT_PARTICIPATE_SUJET;
	$substit['__REGREF__'] = $object->ref;
	$substit['__EVENEMENT__'] = $event->label;
	$substit['__JOURNEE__'] = $day->label;
	$substit['__DATEJOURNEE__'] = dol_print_date($day->date_event, 'day');
	$substit['__PARTICIPANT__'] = dolGetFirstLastname($contact->firstname, $contact->lastname);
	$substit['__TIMESTART__'] = $day->time_start;
	$substit['__TIMEEND__'] = $day->time_end;
	$substit['__LEVEL__'] = $level->label;
	$substit['__LIEN_VALIDATION__'] = $url2;
	$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;

	$sujet= make_substitutions($sujet,$substit);
	$message= make_substitutions($conf->global->EVENT_PARTICIPATE_MESSAGE, $substit);

	$result = $object->setConfirmed('1');
	$result = $object->SendByEmail($day->ref,$contact->email,$contact->id,$sujet,$message,($conf->global->EVENT_SEND_PDF=='-1'?'':'1'), '', ($conf->global->EVENT_MANAGE_ICS=='-1'?'':'1'));

	if ($result <= 0)
	{
		$mesg='<div class="error">'.$object->error.'</div>';
	}
	else
	{
		$outputlangs = $langs;
		if (GETPOST('lang_id'))
		{
			$outputlangs = new Translate("",$conf);
			$outputlangs->setDefaultLang(GETPOST('lang_id'));
		}
		$result = event_pdf_create($db, $object, GETPOST('model'), $outputlangs);
		$mesg='<div class="ok">'.$langs->trans('RegistrationConfirmed').'</div>';
		$action ='';
	}
}
elseif ($action == 'confirm_cancel' && $confirm == 'yes')
{
	$object = new Registration($db);
	$object->fetch($id);
	$contact = new Contact($db);
	$event = new Event($db);
	$day = new Day($db);
	$event->fetch($object->fk_event);
	$contact->fetch($object->fk_user_registered);
	$day->fetch($object->fk_eventday);
	$filedir=$conf->event->dir_output."/".dol_sanitizeFileName($object->ref);
	if($object->fk_levelday!='0') $level->fetch($object->fk_levelday);

	$unique_key = $object->getValueFrom('event_registration', $object->id, 'unique_key');
	$url = DOL_URL_ROOT."/custom/event/registration/confirm_register.php?id=".$object->id."&key=".$unique_key;
	if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
		$url2 = '<a href="http://localhost'.$url.'">Lien</a>';
	else
		$url2 = '<a href="'.$conf->global->EVENT_MAIN_URL_REGISTRATION.$url.'">Lien</a>';

	$sujet=$conf->global->EVENT_NOT_PARTICIPATE_SUJET;
	$substit['__REGREF__'] = $object->ref;
	$substit['__EVENEMENT__'] = $event->label;
	$substit['__JOURNEE__'] = $day->label;
	$substit['__DATEJOURNEE__'] = dol_print_date($day->date_event, 'day');
	$substit['__PARTICIPANT__'] = dolGetFirstLastname($contact->firstname, $contact->lastname);
	$substit['__TIMESTART__'] = $day->time_start;
	$substit['__TIMEEND__'] = $day->time_end;
	$substit['__LEVEL__'] = $level->label;
	$substit['__LIEN_VALIDATION__'] = $url2;
	$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;

	$sujet= make_substitutions($sujet,$substit);
	$message= make_substitutions($conf->global->EVENT_NOT_PARTICIPATE_MESSAGE, $substit);

	$result = $object->setCancelled('1');
	$result = $object->SendByEmail($day->ref,$contact->email,$contact->id,$sujet,$message,($conf->global->EVENT_SEND_PDF=='-1'?'':'1'), '', ($conf->global->EVENT_MANAGE_ICS=='-1'?'':'1'));

	if ($result <= 0)
	{
		$mesg='<div class="error">'.$object->error.'</div>';
	}
	else
	{
		$outputlangs = $langs;
		if (GETPOST('lang_id'))
		{
			$outputlangs = new Translate("",$conf);
			$outputlangs->setDefaultLang(GETPOST('lang_id'));
		}
		$mesg='<div class="ok">'.$langs->trans('CancelRegistration').'</div>';
		$action ='';
	}
}
// Set Note
elseif ($action == 'setnote'  && $confirm == 'yes' && $user->rights->event->write)
{
	$object = new Registration($db);
	$object->fetch($id);
	$result=$object->update_note(dol_html_entity_decode(GETPOST('note_private'), ENT_QUOTES));
	if ($result < 0) {
		dol_print_error($db,$object->error);
	}
	else {
		$set_paid_by_note = GETPOST('set_paid_by_note');
		if($set_paid_by_note == 'on') {

			$result = $object->set_paid($user);
			$object->actionmsg2=''; // reset action msg
			$result = $object->setConfirmed('1');
		}
		setEventMessage('<div class="ok">'.$langs->trans('NoteSuccessfullySaved').'</div>');
		Header("Location: list.php?dayid=".$dayid);
		exit;
	}
}
// Delete registration
elseif ($action == 'confirm_delete')
{
	$object = new Registration($db);
	$result = $object->fetch($id);

	if($result > 0) {
		$result = $object->delete($user);
		if($result > 0)
		{
			$action = '';
			setEventMessage('<div class="ok">'.$langs->trans('RegistrationDeletedSuccessfully').'</div>');
			Header("Location: list.php?dayid=".$object->fk_eventday);
			exit;
		}
		else {
			setEventMessage('<div class="error">'.$object->error.'</div>','errors');
		}
	}
	else {
		setEventMessage('<div class="error">'.$object->error.'</div>');
	}
}
//send_reminders_waiting
else if ($action == "send_reminders_waiting")
{
	$sql_reg = "SELECT r.rowid";
	$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r';
  	$sql_reg.= ' WHERE r.fk_statut=1';
	$sql_reg.=' AND r.fk_eventday='.$dayid;
	$sql_reg.=' AND	DATE_ADD(r.datec, INTERVAL '.$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE.' DAY) < NOW()';
	$sql_reg.=  ' ORDER BY r.fk_statut DESC, r.datec ASC;';
	$resql_reg=$db->query($sql_reg);
	if ($resql_reg)
	{
		$num2 = $db->num_rows($sql_reg);
		$i = 0;
		$registration = new Registration($db);
	    $contact = new Contact($db);
	    $level=new Eventlevel($db);
	    $event=new Event($db);
	    $eventday=new Day($db);

	    $nb_sent=0;
		while ($i < $num2)
		{
			$tmp = $resql_reg->fetch_assoc();
			$insc = $tmp['rowid'];
		    $ret = $registration->fetch($insc);
	        if ($ret)
	        {
	            // Infos participants
	            $ret = $contact->fetch($registration->fk_user_registered);
	            if ($ret)
	            {
	                $event->fetch($registration->fk_event);
	                $eventday->fetch($registration->fk_eventday);
	                $level->fetch($registration->fk_levelday);

	                $unique_key = $registration->getValueFrom('event_registration', $registration->id, 'unique_key');
					$url = DOL_URL_ROOT."/custom/event/registration/confirm_register.php?id=".$registration->id."&key=".$unique_key;
					if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
						$url2 = '<a href="http://localhost'.$url.'">Lien</a>';
					else
						$url2 = '<a href="'.$conf->global->EVENT_MAIN_URL_REGISTRATION.$url.'">Lien</a>';

	                $substit['__REGREF__'] = $registration->ref;
	                $substit['__EVENEMENT__'] = $event->label;
	                $substit['__JOURNEE__'] = $eventday->label;
	                $substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event,'day');
	                $substit['__PARTICIPANT__'] = dolGetFirstLastname($contact->firstname,$contact->lastname);
	                $substit['__TIMESTART__'] = $eventday->time_start;
					$substit['__TIMEEND__'] = $eventday->time_end;
					$substit['__LEVEL__'] = $level->label;
					$substit['__LIEN_VALIDATION__'] = $url2;
					$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;

	                $sujet_send=make_substitutions($sujet,$substit);
					$message_send=make_substitutions($message,$substit);
	                
	                if ( isValidEmail( $contact->email) )
	                {
	                    if ( $registration->SendByEmail($eventday->ref,$contact->email,$contact->id,$sujet_send,$message_send,'','AC_REMIND'))
	                    {
	                        setEventMessage( $langs->trans('ReminderWaitingSent' ));
	                        $nb_sent++;
	                    }
	                    else
	                    {
	                        setEventMessage( $langs->trans('ReminderNotSentTo', dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email),'errors');
	                    }
	                }
	            }
			}
			$i++;
		}
	Header("Location: list.php?dayid=".$dayid);
	exit;
	}
}
elseif ($action == "send_reminders_confirmed")
{
	$sql_reg = "SELECT r.rowid";
	$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r';
  	$sql_reg.= ' WHERE r.fk_statut=4';
	$sql_reg.=' AND r.fk_eventday='.$dayid;
	$sql_reg.=' AND	DATE_ADD(r.datec, INTERVAL '.$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE.' DAY) < NOW()';
	$sql_reg.=  ' ORDER BY r.fk_statut DESC, r.datec ASC;';
	$resql_reg=$db->query($sql_reg);
	if ($resql_reg)
	{
		$num2 = $db->num_rows($sql_reg);
		$i = 0;
		$registration = new Registration($db);
	    $contact = new Contact($db);
	    $level=new Eventlevel($db);
	    $event=new Event($db);
	    $eventday=new Day($db);

	    $nb_sent=0;

		while ($i < $num2)
		{
			$tmp = $resql_reg->fetch_assoc();
			$insc = $tmp['rowid'];
		    $ret = $registration->fetch($insc);
	        if ($ret)
	        {
	            // Infos participants
	            $ret = $contact->fetch($registration->fk_user_registered);
	            if ($ret)
	            {
	                $event->fetch($registration->fk_event);
	                $eventday->fetch($registration->fk_eventday);
	                $level->fetch($registration->fk_levelday);

	                $unique_key = $registration->getValueFrom('event_registration', $registration->id, 'unique_key');
					$url = DOL_URL_ROOT."/custom/event/registration/confirm_register.php?id=".$registration->id."&key=".$unique_key;
					if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
						$url2 = '<a href="http://localhost'.$url.'">Lien</a>';
					else
						$url2 = '<a href="'.$conf->global->EVENT_MAIN_URL_REGISTRATION.$url.'">Lien</a>';

	                $substit['__REGREF__'] = $registration->ref;
	                $substit['__EVENEMENT__'] = $event->label;
	                $substit['__JOURNEE__'] = $eventday->label;
	                $substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event,'day');
	                $substit['__PARTICIPANT__'] = dolGetFirstLastname($contact->firstname,$contact->lastname);
	                $substit['__TIMESTART__'] = $eventday->time_start;
					$substit['__TIMEEND__'] = $eventday->time_end;
					$substit['__LEVEL__'] = $level->label;
	                $substit['__LIEN_VALIDATION__'] = $url2;
					$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;

	                $sujet_send=make_substitutions($sujet,$substit);
	                $message_send=make_substitutions($message,$substit);

	                if ( isValidEmail( $contact->email) )
					{
	                    if ( $registration->SendByEmail($eventday->ref, $contact->email,$contact->id,$sujet_send,$message_send,($conf->global->EVENT_SEND_PDF=='-1'?'':'1'), 'AC_REMIND', ($conf->global->EVENT_MANAGE_ICS=='-1'?'':'1')))
	                    {

	                        setEventMessage( $langs->trans('ReminderConfirmedSent') );
	                        $nb_sent++;
	                    }
	                    else
	                    {
	                        setEventMessage( $langs->trans('ReminderNotSentTo', dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email),'errors');
	                    }
	                }
	            }
			}
			$i++;
		}
	Header("Location: list.php?dayid=".$dayid);
	exit;
	}
}
elseif (GETPOST('action_all','alpha'))
{
	$registration_selected = GETPOST('registration_select');
	$error=0;
	$level_changed=0;
	$deleted=0;
	if(GETPOST('registration_select')=='' AND $action != 'all_change_level' OR $action != 'all_delete') setEventMessage('<div class="ok">'.$langs->trans('ActionEmpty').'</div>');
	else {
		foreach ($registration_selected as $key => $id_registration)
		{
			if ($action == 'all_change_level')
			{
				$object = new Registration($db);
				$result = $object->fetch($id_registration);
				if($result > 0) {
					$result = $object->set_level(GETPOST('level'));
					if($result > 0) {
						$level_changed++;
					}
					else
					{
						$error++;
						$mesgs[]='<div class="error">'.$object->error.'</div>';
					}
				}
			}
			elseif ($action == 'all_delete')
			{
				$object = new Registration($db);
				$result = $object->fetch($id_registration);
				if($result > 0) {
					$result = $object->delete($user);
					if($result > 0)
					{
						$deleted++;
					}
					else
					{
						setEventMessage('<div class="error">'.$object->error.'</div>','errors');
					}
				}
			}
		}
	}
} else if ($action == "close_relance_waiting_auto") { // && $confirm == "yes"
    $eventday = new Day($db);
    $result = $eventday->fetch($dayid);
    if ($result) {
        $eventday->setReminederOpen('relance_waiting_auto',0);
        $mesg = '<div class="ok">' . $langs->trans('RegistrationIsNowOpen') . '</div>';
    }

	if(!$error) {
		if($deleted>0)
			setEventMessage('<div class="ok">'.$langs->trans('RegistrationMassDeletedSuccessfully',count($registration_selected)).'</div>');

		if($level_changed>0)
			setEventMessage('<div class="ok">'.$langs->trans('LevelMassModifiedSuccess',$level_changed).'</div>');

		Header("Location: list.php?dayid=".$eventday->id);
		exit;

	}
} else if ($action == "open_relance_waiting_auto") { // && $confirm == "yes"
    $eventday = new Day($db);
    $result = $eventday->fetch($dayid);
    if ($result) {
        $eventday->setReminederOpen('relance_waiting_auto',1);
        $mesg = '<div class="ok">' . $langs->trans('RegistrationIsNowOpen') . '</div>';
    }

	if(!$error) {
		if($deleted>0)
			setEventMessage('<div class="ok">'.$langs->trans('RegistrationMassDeletedSuccessfully',count($registration_selected)).'</div>');

		if($level_changed>0)
			setEventMessage('<div class="ok">'.$langs->trans('LevelMassModifiedSuccess',$level_changed).'</div>');

		Header("Location: list.php?dayid=".$eventday->id);
		exit;

	}
} else if ($action == "close_relance_confirmed_auto") { // && $confirm == "yes"
    $eventday = new Day($db);
    $result = $eventday->fetch($dayid);
    if ($result) {
        $eventday->setReminederOpen('relance_confirmed_auto',0);
        $mesg = '<div class="ok">' . $langs->trans('RegistrationIsNowOpen') . '</div>';
    }

	if(!$error) {
		if($deleted>0)
			setEventMessage('<div class="ok">'.$langs->trans('RegistrationMassDeletedSuccessfully',count($registration_selected)).'</div>');

		if($level_changed>0)
			setEventMessage('<div class="ok">'.$langs->trans('LevelMassModifiedSuccess',$level_changed).'</div>');

		Header("Location: list.php?dayid=".$eventday->id);
		exit;

	}
} else if ($action == "open_relance_confirmed_auto") { // && $confirm == "yes"
    $eventday = new Day($db);
    $result = $eventday->fetch($dayid);
    if ($result) {
        $eventday->setReminederOpen('relance_confirmed_auto',1);
        $mesg = '<div class="ok">' . $langs->trans('RegistrationIsNowOpen') . '</div>';
    }

	if(!$error) {
		if($deleted>0)
			setEventMessage('<div class="ok">'.$langs->trans('RegistrationMassDeletedSuccessfully',count($registration_selected)).'</div>');

		if($level_changed>0)
			setEventMessage('<div class="ok">'.$langs->trans('LevelMassModifiedSuccess',$level_changed).'</div>');

		Header("Location: list.php?dayid=".$eventday->id);
		exit;

	}
}


/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("RegistrationList"),'');

$form=new Form($db);
$formevent=new FormEvent($db);

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("bills");
$langs->load("event@event");

//dol_htmloutput_mesg($mesg,$mesgs);

// Confirm registration
if ($action == 'registration_cancel')
	{
	$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?dayid='.$dayid.'&id='.$id, $langs->trans('CancelRegistration'), $langs->trans('ConfirmCancelRegistration'), 'confirm_cancel','',0,1);
	}

elseif ($action == 'registration_confirm')
	{
	$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?dayid='.$dayid.'&id='.$id, $langs->trans('ConfirmRegistration'), $langs->trans('ConfirmRegistrationMsg'), 'confirm_registration','',0,1);
}

// Set Note
elseif ($action == 'set_note_private')
{
	$object = new Registration($db);
	$object->fetch($id);

	if($object->total_ht=='0')
		$formquestionpaid = "";
	elseif(!$object->paye)
		$formquestionpaid = array("type" => 'checkbox','name' => 'set_paid_by_note','label' => $langs->trans('EventLabelMarkAsPaid').'Tarif :'.$object->total_ht);
	elseif($object->paye)
		$formquestionpaid = array('type' => 'other', 'label' => 'paiement', 'value' => $langs->trans('EventRegistrationAlreadyPaid'));

	$formquestion = array(
		array('type' => 'text', 'name'=>'note_private','label' => $langs->trans('Label'), 'size'=>'50','value' => $object->note_private),
		$formquestionpaid
	);
	$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?dayid='.$dayid.'&id='.$id, $langs->trans('ConfirmSetPrivateNote'), $langs->trans('ConfirmSetPrivateNoteMsg'), 'setnote',$formquestion,0,1,'250');
	if ($ret == 'html') print '<br>';
}
// Confirmation delete
elseif ($action == 'delete')
{
	$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?dayid='.$dayid.'&id='.$id,$langs->trans("DeleteRegistration"),$langs->trans("ConfirmDeleteRegistration"),"confirm_delete",'','',1);
	if ($ret == 'html') print '<br>';
}

/*
 * Event list
 */
if ($user->rights->event->read) {
	if (!$dayid && !$ref) {
		/*
		 * Affichage sélecteur des journées
		 */
		print $formevent->select_eventdays('','dayid','1');
	}
	else
	{
		/*
		 * Liste des inscriptions pour la journée
		 */
		$event = new Day($db);
		$event->fetch($dayid,$ref);

		$regstat = new Registration($db);

		$head = eventday_prepare_head($event);
		dol_fiche_head($head, 'registration', $langs->trans("RegistrationList"),0,'event_registration@event');

  ///////////////////:: recherche jour pecedent jour suivant /////////////////////////////////
		 $js = jour_suivant($db,$event->fk_event,dol_print_date($event->date_event, '%Y-%m-%d'));
		 $jp = jour_precedent($db,$event->fk_event,dol_print_date($event->date_event, '%Y-%m-%d'));
		 
         //////////////////////////////////////////////////////////////////			

		   ?><div style="vertical-align: middle">
					<div class="pagination paginationref">
						<ul class="right">
						<!--<li class="noborder litext">
						<a href="/dolibarr/societe/list.php?restore_lastsearch_values=1">Retour liste</a>
						</li>-->
						<?php 
						if($jp!='')
							echo '<li class="pagination"><a accesskey="p" href="'.DOL_URL_ROOT.'/custom/event/registration/list.php?dayid='.$jp.'"><i class="fa fa-chevron-left"></i></a></li>';
						else
							echo '<li class="pagination"><span class="inactive"><i class="fa fa-chevron-left opacitymedium"></i></span></li>';
						if($js!='')
							echo '<li class="pagination"><a accesskey="p" href="'.DOL_URL_ROOT.'/custom/event/registration/list.php?dayid='.$js.'"><i class="fa fa-chevron-right"></i></a></li>';
						else
							echo '<li class="pagination"><span class="inactive"><i class="fa fa-chevron-right opacitymedium"></i></span></li>';
						?>
						</ul></div>
				</div>
				
		  <?php				
		
		print '<table class="border centpercent">';

		// Label
		print '<tr><td width="30%" valign="top">';
		print $langs->trans("LabelDay");
		print '</td><td>';
		print $event->label;
		print "</td></tr>";

		// Date
		$dayofweek = strftime("%w",$event->date_event);
		print '<tr><td>'.$langs->trans("EventDayDate").'</td><td>'. $langs->trans("Day".$dayofweek) . ' ' . dol_print_date($event->date_event,'daytext').'</td>';
		print '</tr>';

		// Time start
        print '<tr><td>' . $langs->trans("TimeStart") . '</td><td>'.$event->time_start .'</td></tr>';

        // Time end
        print '<tr><td>' . $langs->trans("TimeEnd") . '</td><td>' . $event->time_end . '</td></tr>';

        // Statut journée
		print '<tr><td>'.$langs->trans("Statut").'</td><td class="valeur">'.$event->getLibStatut(4).'&nbsp;</td>';
		print '</tr>';

        // Inscription ouverte oui/non
        print '<tr><td><label for="registration_open">' . $langs->trans("RegistrationIsOpen") . '</label></td><td>';
        if ($event->registration_open > 0) print img_picto($langs->trans("Activated"), 'switch_on'); else print img_picto($langs->trans("Desactivated"), 'switch_off');
    	print '</td></tr>';

        // relance_auto waiting oui/non
        if($conf->global->EVENT_DELAY_BEFORE_RELAUNCH_WAITING>'0'){
        	print '<tr><td>' . $langs->trans('Activate').' '.$langs->trans("Delaybeforlaunchwaiting").' '.$conf->global->EVENT_DELAY_BEFORE_RELAUNCH_WAITING.' '.$langs->trans('Hours').'</td>';
	        if ($event->relance_waiting_auto > 0) {
	            print '<td>';
	            print '<a href="' . $_SERVER['PHP_SELF'] . '?dayid=' . $event->id . '&amp;action=close_relance_waiting_auto">';
	            print img_picto($langs->trans("Activated"), 'switch_on');
	            print '</a></td>' . "\n";
	        } else {
	            print '<td>';
	            print '<a href="' . $_SERVER['PHP_SELF'] . '?dayid=' . $event->id . '&amp;action=open_relance_waiting_auto">';
	            print img_picto($langs->trans("Desactivated"), 'switch_off');
	            print '</a></td>' . "\n";
	        }
	        print '</td></tr>';
	    }

        // relance_confirmed_auto oui/non
        if($conf->global->EVENT_DELAY_BEFORE_RELAUNCH_CONFIRMED>'0'){
        	print '<tr><td>' . $langs->trans('Activate').' '.$langs->trans("Delaybeforlaunchconfirmed").' '.$conf->global->EVENT_DELAY_BEFORE_RELAUNCH_CONFIRMED.' '.$langs->trans('Hours').'</td>';
	        if ($event->relance_confirmed_auto > 0) {
	            print '<td>';
	            print '<a href="' . $_SERVER['PHP_SELF'] . '?dayid=' . $event->id . '&amp;action=close_relance_confirmed_auto">';
	            print img_picto($langs->trans("Activated"), 'switch_on');
	            print '</a></td>' . "\n";
	        } else {
	            print '<td>';
	            print '<a href="' . $_SERVER['PHP_SELF'] . '?dayid=' . $event->id . '&amp;action=open_relance_confirmed_auto">';
	            print img_picto($langs->trans("Desactivated"), 'switch_off');
	            print '</a></td>' . "\n";
	        }
	        print '</td></tr>';
    	}

		// Stats
		print '<tr><td>'.$langs->trans("NumberRegistrationShort");
		print '</td><td class="valeur">';
        print img_picto($langs->trans('Draft'),'statut0') . ' ' . $event->getNbRegistration('0');
        print ' ' . img_picto($langs->trans('Waited'),'statut3') . ' ' . $event->getNbRegistration('1');
        print ' ' . img_picto($langs->trans('Queued'),'statut1') . ' ' . $event->getNbRegistration('8');
        print ' ' . img_picto($langs->trans('Confirmed'),'statut4') . ' ' . $event->getNbRegistration('4');
        print ' ' . img_picto($langs->trans('Cancelled'),'statut8') . ' ' . $event->getNbRegistration('5');
        print '</td></tr>';

		print '</table>';
		print '</div>';

		/*
		 * Boutons actions
		*/
		print '<div class="tabsAction">';
		if ($event->fk_statut != 9) {
			if (strtotime(date("Y-m-d")) > $event->date_event) {
                print '<a class="butAction not-active" href="#">'.$langs->trans("AddRegistration").'</a>';
                print '<a class="butAction not-active" href="#">'.$langs->trans("AddRegistrationTag").'</a>';
               	print '<a class="butAction not-active" href="#">'.$langs->trans("RelanceWaiting").'</a>';
				print '<a class="butAction not-active" href="#">'.$langs->trans("RelanceConfirmed").'</a>';
                $msg = '<div class="warning">'.$langs->trans('DayAlreadyPast').'</div>';
                dol_htmloutput_mesg($msg);
			}
            elseif($event->registration_open=='0') {
                print '<a class="butAction not-active" href="#">'.$langs->trans("AddRegistration").'</a>';
                print '<a class="butAction not-active" href="#">'.$langs->trans("AddRegistrationTag").'</a>';
               	print '<a class="butAction not-active" href="#">'.$langs->trans("RelanceWaiting").'</a>';
				print '<a class="butAction not-active" href="#">'.$langs->trans("RelanceConfirmed").'</a>';
                $msg = '<div class="warning">'.$langs->trans('DayAlreadyPast').'</div>';
                dol_htmloutput_mesg($msg);
            }
            else {
            	print '<a class="butAction" href="../registration/create.php?dayid='.$event->id.'">'.$langs->trans("AddRegistration").'</a>';
				if($conf->global->EVENT_BLOCK_REGISTRATION_TAG=='1') print '<a class="butAction not-active" href="#">'.$langs->trans("AddRegistrationTag").'</a>';
					else print '<a class="butAction" href="../registration/create_tag.php?dayid='.$event->id.'">'.$langs->trans("AddRegistrationTag").'</a>';
				if($conf->global->EVENT_BLOCK_RELANCE_WAITING=='1' OR $event->getNbRegistration('1')=="") print '<a class="butAction not-active" href="#">'.$langs->trans("RelanceWaiting").'</a>';
					else print '<a class="butAction" href="../registration/list.php?dayid='.$event->id.'&action=relance_waiting">'.$langs->trans("RelanceWaiting").'</a>';
				if($conf->global->EVENT_BLOCK_RELANCE_VALID=='1' OR $event->getNbRegistration('4')=='0') print '<a class="butAction not-active" href="#">'.$langs->trans("RelanceConfirmed").'</a>';
					else print '<a class="butAction" href="../registration/list.php?dayid='.$event->id.'&action=relance_confirmed">'.$langs->trans("RelanceConfirmed").'</a>';
            }
		}
		print '<a class="butAction" target="_blank" href="list_print.php?dayid='.$event->id.'">'.$langs->trans("PrintVersion").'</a>';
		print '</div><br/>';


	    /*
	     * Recherche
	    */
		print_fiche_titre($langs->trans('RegistrationSearch'), '', 'event_registration@event');

		print '<p>' . $langs->trans('RegistrationSearchHelp') . '</p>';
	    $ret.='<form action="' . dol_buildpath('/event/index.php', 1) . '" method="post">';
	    $ret.='<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	    $ret.='<input type="hidden" name="action" value="search">';
	    $ret.='<input type="hidden" name="eventday" value="'.$event->id.'">';

	    $ret.='<input type="text" class="flat" name="query" size="10" />&nbsp;';
	    $ret.='<input type="submit" class="button" value="' . $langs->trans("Search") . '">';
	    $ret.="</form>\n";
	    print $ret;
	    print '<br />';

		/*
		 * Liste des inscriptions sans groupe
		*/
		print_fiche_titre($langs->trans('RegistrationForThisDay'),'','event_registration@event').'<br />';
		if($action=='' OR $action =='setdayid') {
			if($conf->global->EVENT_LEVEL_REQUIRED=="0") {
				$sql_reg = "SELECT r.rowid as id, r.fk_soc, r.fk_statut, r.paye, r.fk_eventday, r.ref, r.datec, r.date_valid, r.fk_user_registered, r.note_public, r.note_private, s.lastname";
				$sql_reg.=" FROM ".MAIN_DB_PREFIX."event_registration AS r";
				$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."socpeople AS s ON s.rowid=r.fk_user_registered";
				$sql_reg.=" WHERE r.fk_eventday = '".$event->id."' AND (r.fk_levelday IS NULL OR r.fk_levelday=0)";
				$sql_reg.=" ORDER BY ".$sortfield." ".$sortorder;
				$nbtotalofrecords = 0;
				if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
				{
					$result = $db->query($sql_reg);
					$nbtotalofrecords = $db->num_rows($result);
				}
		    	if($limit > 0) $sql_reg.= " ".$db->plimit( $limit + 1 ,$offset);

				$resql_reg=$db->query($sql_reg);
				if ($resql_reg)
				{
					$num2 = $db->num_rows($sql_reg);
					print_barre_liste($langs->trans('RegistratioWithoutLevelInfo'), $page, 'list.php', '&amp;dayid='.$dayid, $sortfield, $sortorder, '', $num2, $nbtotalofrecords, '', 1);
					if ($num2)
					{
						$j=0;
						print "
								<script language=\"JavaScript\">
								function toggle(source) {
								  checkboxes = document.getElementsByName('registration_select[]');
								  for(var i=0, n=checkboxes.length;i<n;i++) {
								    checkboxes[i].checked = source.checked;
								  }
								}
								</script>";

						print '<table class="liste centpercent">';

						print '<tr class="liste_titre">';
		    			print_liste_field_titre($langs->trans('Status'), $_SERVER["PHP_SELF"], 'r.fk_statut', '', '&amp;dayid='.$dayid, ' width="15" align="center"', $sortfield, $sortorder);
		    			print_liste_field_titre($langs->trans('Name'), $_SERVER["PHP_SELF"], 's.lastname', '', '&amp;dayid='.$dayid, ' width="250" align="center"', $sortfield, $sortorder);
		    			print_liste_field_titre($langs->trans('RegistrationDate'), $_SERVER["PHP_SELF"], 'r.datec', '', '&amp;dayid='.$dayid, ' width="200" align="center"', $sortfield, $sortorder);
		    			print_liste_field_titre($langs->trans('ConfirmationDate'), $_SERVER["PHP_SELF"], 'r.date_valid', '', '&amp;dayid='.$dayid, ' width="200" align="center"', $sortfield, $sortorder);
						print '<th width="80" align="center">'.$langs->trans('NotePrivate').'</th>';
						if($event->total_ht!='0') print '<th width="40" align="center">'.$langs->trans('Paid').'</th>';
						print '<th width="80" align="center">'.$langs->trans('Edit').'</th>';
						print '</tr>';

		                $var=True;
						$count_reg = 0;
						while ($j < min($num2, $conf->liste_limit))
						{
		                    $var=!$var;

							$registration = $db->fetch_object($resql_reg);
		                    if($statut != $registration->fk_statut) $count_reg = 0;
							$count_reg++;

							switch ($registration->fk_statut) {
								case "0": // brouillon
									$style = "style=\"background: #d7d7d7;";
									break;
								case "1": // Valid
									$style = "style=\"background: #efeec2;";
									break;
								case "4": // confirm
									$style = "style=\"background: #e2f9e3;";
									break;
								case "5": // annulée
									$style = "style=\"background: #d7d7d7;";
									break;
								case "6": //closed
									$style = "style=\"background: #f48383;";
									break;
								case "8": // waiting
									$style = "style=\"background: #f48383;";
									break;
							}
							$border_style='';
							if($id == $registration->id) {
								$style.= $border;
								$border_style = ' style="'.$border.'"';
							}
							$style.="\"";
							$var!=$var;
                            print "<tr ".$bc[$var]." ".$style.">";
							$ret.="\n";

							// Statut
							print '<td'.$border_style.' align="center">'.$regstat->LibStatut($registration->fk_statut,3).'</td>';

							// Nom Prénom
							$contactstat = new Contact($db);
							$result=$contactstat->fetch($registration->fk_user_registered);
							print '<td'.$border_style.'>'.$contactstat->getNomUrl(1).'</td>';

							// Date de création
							print '<td align="center">'.dol_print_date($db->jdate($registration->datec),'dayhour').'</td>';

							// Date de validation
							print '<td align="center">'.dol_print_date($db->jdate($registration->date_valid),'dayhour').'</td>';

							// Note interne
							print '<td'.$border_style.' align="center">';
							print '<a href="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'&action=set_note_private&id='.$registration->id.'">'.img_picto('EditNote','edit').'</a> ';
							print $registration->note_private;
							print '</td>';

							// Paiement
							if($event->total_ht!='0') {
							print '<td align="center" '.$border_style.'>';
							$paid = $registration->paye > 0 ? "on":"off";
							$trans_paid = $registration->paye > 0 ? "AlreadyPaid":"RegistrationStatusNotPaid";
							print img_picto($langs->trans($trans_paid),$paid);
							print '</td>';
							}

							// Actions
							print '<td align="center">';

							// Confirm
							if ($registration->fk_statut == '1' OR $registration->fk_statut == '5') {
								print ' <a href="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'&amp;action=registration_confirm&id='.$registration->id.'">'.img_picto('Confirm','tick').'</a>&nbsp;';
							}
							// si au moins validé -> afficher PDF
							elseif ($registration->ref && $conf->global->EVENT_HIDE_PDF_BILL=='0') {
								$filename=dol_sanitizeFileName($registration->ref);
								$file = $filename.'/'.$filename.'.pdf';
								$dir=$conf->event->dir_output.'/';
								if (is_file($dir.$file))
								{
									print '<a href="'.DOL_URL_ROOT.'/document.php?modulepart=event&amp;file='.$file.'" alt="'.$legende.'" title="'.$langs->trans('DownloadRegistrationTicket').'">';
									print img_picto('','pdf3');
									print '</a>';
								}
							}
							// else print img_picto_common('','transparent','height="16" width="16"').'&nbsp;';

							// Cancel
							if ($registration->fk_statut != '5') print ' <a href="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'&amp;action=registration_cancel&id='.$registration->id.'">'.img_picto('Cancel','stcomm-1').'</a>&nbsp;';

							// Visu
							print '<a href="fiche.php?id='.$registration->id.'">'.img_picto('View','detail').'</a>';

							// Delete
							print ' <a href="'.$_SERVER["PHP_SELF"].'?dayid='.$event->id.'&amp;id='.$registration->id.'&action=delete">'.img_picto($langs->trans("Delete"),'delete').'</a>';
							print '</td>';
							print '</tr>';
							$statut = $registration->fk_statut;
							$j++;
						}
						print '</table><br />';

						$level=new Eventlevel($db);

						$htmlname='level';

						print '<input type="hidden" name="dayid" value="'.$event->id.'">';
						print '</form>';

					}
					else print '<div class="ok">&nbsp; &nbsp;'.$langs->trans('NoRegistrationWithoutLevel').'</div><br />';
				}
			}

			/*
			 * Liste des inscriptions pour le groupe
			 */

			if($conf->global->EVENT_HIDE_GROUP=='-1') {
			$sql_level = "SELECT l.label, l.rowid, ld.place FROM ".MAIN_DB_PREFIX."event_level as l,".MAIN_DB_PREFIX."event_level_day as ld WHERE ld.fk_eventday='".$event->id."' AND l.rowid=ld.fk_level ORDER BY l.rang ASC";
			$resql=$db->query($sql_level);
			if ($resql) {
				$num3 = $db->num_rows($resql);
				if ($num3) {
					$i = 0;
					print '<div id="registration_list">';
					while ($i < $num3)
					{
						$level_day = $db->fetch_object($resql);

						$sql_reg = "SELECT r.rowid as id , r.fk_soc, r.fk_statut, r.paye, r.ref, r.datec, r.date_valid, r.fk_user_registered, r.note_public, r.note_private, s.lastname";
						$sql_reg.= " FROM ".MAIN_DB_PREFIX."event_registration AS r";
						$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."socpeople AS s ON s.rowid=r.fk_user_registered";
						$sql_reg.=" WHERE r.fk_eventday = '".$event->id."' AND r.fk_levelday='".$level_day->rowid."'";
						$sql_reg.=" ORDER BY ".$sortfield." ".$sortorder;
						$nbtotalofrecords3 = 0;
						if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
						{
							$result = $db->query($sql_reg);
							$nbtotalofrecords3 = $db->num_rows($result);
						}
				    	if($limit > 0) $sql_reg.= " ".$db->plimit( $limit + 1 ,$offset);

		    			$resql_reg=$db->query($sql_reg);
						if ($resql_reg) {
							$num4 = $db->num_rows($sql_reg);
							if ($num4) {
								$j=0;
								print '<br />';
								print_barre_liste($langs->trans('RegistrationForLevel').' '.$level_day->label, $page, 'list.php', '&amp;dayid='.$dayid, $sortfield, $sortorder, '', $num4, $nbtotalofrecords3, '', 1);
								print '<p><em>&nbsp;'.$langs->trans('NumberAvailableForThisLevel',$level_day->place).'</em></p>';

								print '<table class="liste centpercent">';
								print '<tr class="liste_titre">';
				    			print_liste_field_titre($langs->trans('Status'), $_SERVER["PHP_SELF"], 'r.fk_statut', 'bn', '&amp;dayid='.$dayid, ' width="15" align="center"', $sortfield, $sortorder);
				    			print_liste_field_titre($langs->trans('Name'), $_SERVER["PHP_SELF"], 's.lastname', '', '&amp;dayid='.$dayid, ' width="250" align="center"', $sortfield, $sortorder);
				    			print_liste_field_titre($langs->trans('RegistrationDate'), $_SERVER["PHP_SELF"], 'r.datec', '', '&amp;dayid='.$dayid, ' width="200" align="center"', $sortfield, $sortorder);
				    			print_liste_field_titre($langs->trans('ConfirmationDate'), $_SERVER["PHP_SELF"], 'r.date_valid', '', '&amp;dayid='.$dayid, ' width="200" align="center"', $sortfield, $sortorder);
								print '<th width="80" align="center">'.$langs->trans('NotePrivate').'</th>';
								if($event->total_ht!='0') print '<th width="40" align="center">'.$langs->trans('Paid').'</th>';
								print '<th width="80" align="center">'.$langs->trans('Edit').'</th>';
								print '</tr>';

								// On boucle pour chaque groupe de la journée
	                            $var=True;
								$count_reg = 0;
								while ($j < $num4)
								{
									$registration = $db->fetch_object($resql_reg);

									if($statut != $registration->fk_statut) $count_reg = 0;
									$count_reg++;

									switch ($registration->fk_statut) {
										case "0": // brouillon
											$style = "style=\"background: #d7d7d7;";
											break;
										case "1": // Valid
											$style = "style=\"background: #efeec2;";
											break;
										case "4": // confirm
											$style = "style=\"background: #e2f9e3;";
											break;
										case "5": // annulée
											$style = "style=\"background: #d7d7d7;";
											break;
										case "6": //closed
											$style = "style=\"background: #f48383;";
											break;
										case "8": // waiting
											$style = "style=\"background: #f48383;";
											break;
									}
									$border_style='';
									if($id == $registration->id) {
										$style.= $border;
										$border_style = ' style="'.$border.'"';
									}
									$style.="\"";
									$var!=$var;
	                                print "<tr ".$bc[$var]." ".$style.">";
									$ret.="\n";

									// Statut
									print '<td'.$border_style.'>'. $regstat->LibStatut($registration->fk_statut,3).'</td>';

									// Nom Prénom
									$contactstat = new Contact($db);
									$result=$contactstat->fetch($registration->fk_user_registered);
									print '<td'.$border_style.'>'.$contactstat->getNomUrl(1).'</td>';

									// Date de création
									print '<td'.$border_style.' align="center">'.dol_print_date($db->jdate($registration->datec),'dayhour').'</td>';

									// Date de validation
									print '<td'.$border_style.' align="center">'.dol_print_date($db->jdate($registration->date_valid),'dayhour').'</td>';

									// Note interne
									print '<td'.$border_style.' align="center">';
									print ' <a href="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'&action=set_note_private&id='.$registration->id.'">'.img_picto('EditNote','edit').'</a> ';
									print $registration->note_private;
									print '</td>';

									// Paiement
									if($event->total_ht!='0') {
									print '<td align="center" '.$border_style.'>';
									$paid = $registration->paye > 0 ? "on":"off";
									$trans_paid = $registration->paye > 0 ? "AlreadyPaid":"RegistrationStatusNotPaid";
									print img_picto($langs->trans($trans_paid),$paid);
									print '</td>';
									}

									// Actions
									print '<td'.$border_style.' align="center">';

									// Confirm
									if ($registration->fk_statut == '1' OR $registration->fk_statut == '5') {
										print ' <a href="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'&amp;action=registration_confirm&id='.$registration->id.'">'.img_picto('Confirm','tick').'</a>';
										}
									// si au moins validé -> afficher PDF
									elseif ($registration->ref && $conf->global->EVENT_HIDE_PDF_BILL=='0')
									{
										$filename=dol_sanitizeFileName($registration->ref);
										$file = $filename.'/'.$filename.'.pdf';
										$dir=$conf->event->dir_output.'/';
										if (is_file($dir.$file))
										{
											print ' <a href="'.DOL_URL_ROOT.'/document.php?modulepart=event&amp;file='.$file.'" alt="'.$legende.'" title="'.$langs->trans('DownloadRegistrationTicket').'">';
											print img_picto('','pdf3');
											print '</a>';
										}
									}
									// else print img_picto_common('','transparent','height="16" width="16"');

									// Cancel
									if ($registration->fk_statut != '5') print ' <a href="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'&amp;action=registration_cancel&id='.$registration->id.'">'.img_picto('Cancel','tick').'</a>';
									
									// Visu
									print '<a href="fiche.php?id='.$registration->id.'">'.img_picto('View','detail').'</a>';

									// Delete
									print '<a href="'.$_SERVER["PHP_SELF"].'?dayid='.$event->id.'&amp;id='.$registration->id.'&action=delete">'.img_picto($langs->trans("Delete"),'delete').'</a>';
									print '</td>';

									print '</form>';
									print '</tr>';
									$statut = $registration->fk_statut;
									$j++;
								}
								print '</table><br />';
							}
							else {
								print_barre_liste($langs->trans('RegistrationForLevelling').' '.$level_day->label, $page, 'list.php', '&amp;dayid='.$dayid, $sortfield, $sortorder, '', '', '', '', 1);
								print '&nbsp; &nbsp;'.$langs->trans('NoRegistrationGroupe').'<br /><br />';
							}
						}
						else {
							dol_print_error($db);
						}
						$i++;
					}
					print '</div><!-- registration_list -->';
					}
				}
			}
		}

		/* Relance Confirmed */
		elseif ($action == "relance_confirmed")
		{
			print print_fiche_titre($langs->trans('RelanceConfirmed'),'','call');
			if(EVENT_REGISTRATION_LIMIT_EXPIRE>'0' OR EVENT_REGISTRATION_LIMIT_EXPIRE!='') print $langs->trans('EventRegistrationLimitToExpireInfo',EVENT_REGISTRATION_LIMIT_EXPIRE);
			if($event->getNbRegistration('4')!='0')
			{
			print '<form method="POST" name="registration" action="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<br/><div>';

			$form = new FormMail($db);

			$form->fromtype = 'user';
			$form->fromid   = $user->id;
			$form->fromname = $user->getFullName($langs);
			$form->frommail = $user->email;
			$form->withfrom=1;
			$form->withfckeditor=1;
			$form->withto=0;
			$form->withtocc=0;
			$form->withform=0;
			$form->withcancel=0;
			$form->withbody=0;

			$form->withtopic=$conf->global->EVENT_RELANCE_CONFIRM_SUJET;
  			$form->withbody=$conf->global->EVENT_RELANCE_CONFIRM_MESSAGE.'<br /><br />'.$event->description.'<br /><br />'.$conf->global->EVENT_REGISTRATION_SIGN_EMAIL;

			$form->param['action']="send_reminders_confirmed";
			$form->param['event_ref'] = $object->ref;
			$form->param['eventid'] = $registration->fk_event;

			$form->show_form();
			$out.='</td></tr>';

			$out.= '<tr><td align="center" colspan="2"><center>';
			$out.= '<input class="button" type="submit" id="sendmail" name="sendmail" value="'.$langs->trans("SendMail").'"';
			$out.= ' />';
			$out.= ' &nbsp; &nbsp;';
			$out.= '<input type="button" class="button" name="cancel" onclick="history.go(-1);" value='.$langs->trans('Cancel').'>';
			$out.= '</center></td></tr>'."\n";

			print $out;

			print '</form></div>';
			}
			else print $langs->trans('RelanceConfirmedNONE');
		}
		/* relance_waiting */
		elseif ($action == "relance_waiting")
		{
			print print_fiche_titre($langs->trans('RelanceWaiting'),'','call');
			if(EVENT_REGISTRATION_LIMIT_EXPIRE>'0' OR EVENT_REGISTRATION_LIMIT_EXPIRE!='') print $langs->trans('EventRegistrationLimitToExpireInfo',EVENT_REGISTRATION_LIMIT_EXPIRE);if($event->getNbRegistration('1')!='0')
			{
				print '<form method="POST" name="registration" action="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'">';
    			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
				print '<br/><div>';

				$form = new FormMail($db);

    			$form->fromtype = 'user';
    			$form->fromid   = $user->id;
    			$form->fromname = $user->getFullName($langs);
    			$form->frommail = $user->email;
    			$form->withfrom=1;
    			$form->withfckeditor=1;
    			$form->withto=0;
    			$form->withtocc=0;
    			$form->withform=0;
    			$form->withcancel=0;
    			$form->withbody=0;

    			$form->withtopic=$conf->global->EVENT_RELANCE_WAITING_SUJET;
  				$form->withbody=$conf->global->EVENT_RELANCE_WAITING_MESSAGE.'<br /><br />'.$event->description.'<br /><br />'.$conf->global->EVENT_REGISTRATION_SIGN_EMAIL;
    			$form->param['action']="send_reminders_waiting";
    			$form->param['event_ref'] = $object->ref;
    			$form->param['eventid'] = $registration->fk_event;

    			$form->show_form();

    			$out= '<tr><td align="center" colspan="2"><center>';
    			$out.= '<input class="button" type="submit" id="sendmail" name="sendmail" value="'.$langs->trans("SendMail").'"';
    			$out.= ' />';
    			$out.= ' &nbsp; &nbsp; ';
    			$out.= '<input type="button" class="button" name="cancel" onclick="history.go(-1);" value='.$langs->trans('Cancel').'>';
    			$out.= '</center></td></tr>'."\n";

    			print $out;

    			print '</form></div>';
    		}
    		else print $langs->trans('RelanceWaitingNONE');
		}
	}
}

// End of page
llxFooter();
$db->close();
