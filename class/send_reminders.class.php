<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012		JF FERRY			<jfefe@aternatik.fr>
 * Copyright (C) 2017		Eric GROUTL			<eric@code42.fr>

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
 *  \file       event/class/registration.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2012-07-06 00:18
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");
require_once('registration.class.php');
require_once("event.class.php");
require_once("day.class.php");
require_once("eventlevel.class.php");

class Reminders extends Registration
{

	var $db;
	
	/**
	 *	Constructor
	 *
	 * 	@param		DoliDB		$db		Database handler
	 */
	function __construct($db)
	{
        $this->db = $db;
		dol_syslog(get_class($this)."CRON :");
	}

	function send_reminders_confirmed() {
	    global $db,$conf,$langs,$user;

		$sql_reg = "SELECT r.rowid";
		$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r, '.MAIN_DB_PREFIX.'event_day as d';
	  	$sql_reg.= ' WHERE r.fk_statut=4';
		$sql_reg.= ' AND d.rowid = r.fk_eventday' ;
		$sql_reg.= ' AND TIMESTAMPDIFF(MINUTE ,NOW(),DATE_ADD(ADDTIME(d.date_event, d.time_start), INTERVAL -'.$conf->global->EVENT_DELAY_BEFORE_RELAUNCH_CONFIRMED.' HOUR)) BETWEEN -5 AND 0';
		$sql_reg.= ' AND d.relance_confirmed_auto =1';
		$sql_reg.= ' ORDER BY r.fk_statut DESC, r.datec ASC;';
		$resql_reg=$db->query($sql_reg);
		dol_syslog(get_class($this)."CRON - SQL :: ".$sql_reg);

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
                		//$eventday->setReminederOpen('relance_confirmed_auto',0);
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
		                $substit['__LEVEL__'] = $level->label;
	                	$substit['__TIMESTART__'] = $eventday->time_start;
						$substit['__TIMEEND__'] = $eventday->time_end;
						$substit['__LIEN_VALIDATION__'] = $url2;
						$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;

	                	$sujet= $conf->global->EVENT_RELANCE_CONFIRM_SUJET;
	                	$message= $conf->global->EVENT_RELANCE_CONFIRM_MESSAGE.'<br /><br />'.$event->description.'<br /><br />'.$conf->global->EVENT_REGISTRATION_SIGN_EMAIL;

		                $sujet=make_substitutions($sujet,$substit);
		                $message=make_substitutions($message,$substit);
		                $message=str_replace('\n',"\n",$message);

		                if ( isValidEmail( $contact->email) )
		                {
		                    if ( $registration->SendByEmail($eventday->ref,$contact->email,$contact->id,$sujet,$message,($conf->global->EVENT_SEND_PDF=='-1'?'':'1'), '', ($conf->global->EVENT_MANAGE_ICS=='-1'?'':'1')) )
		                    {
		                        setEventMessage( $langs->trans('ReminderConfirmedSent' ));
		                        $this->output = $langs->trans('ReminderConfirmedSent');
		                        dol_syslog(get_class($this)." ".$langs->trans('ReminderConfirmedSent',' '.dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email));
		                        $nb_sent++;
		                    }
		                    else
		                    {
		                        $error='1';
		                        setEventMessage( $langs->trans('ReminderNotSentTo', dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email),'errors');
		                        $this->output = $langs->trans('ReminderNotSentTo');
		                        dol_syslog(get_class($this)." ERROR ".$langs->trans('ReminderNotSentTo',' '.dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email));
		                    }
		                }
		            }
				}
				$i++;
			}
		(!isset($this->output))?$this->output="Pas de mail envoyé":$this->output=$nb_sent.' '.$langs->trans('ReminderConfirmedSent');;
		}
	return $error?$error:0;
	}

	function send_reminders_waiting() {
	    global $db,$conf,$langs,$user;

		$sql_reg = "SELECT r.rowid";
		$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r, '.MAIN_DB_PREFIX.'event_day as d';
	  	$sql_reg.= ' WHERE r.fk_statut=1';
		$sql_reg.= ' AND d.rowid = r.fk_eventday' ;
		$sql_reg.= ' AND TIMESTAMPDIFF(MINUTE ,NOW(),DATE_ADD(ADDTIME(d.date_event, d.time_start), INTERVAL -'.$conf->global->EVENT_DELAY_BEFORE_RELAUNCH_WAITING.' HOUR)) BETWEEN -5 AND 0';
		$sql_reg.= ' AND d.relance_waiting_auto =1';
		$sql_reg.=  ' ORDER BY r.fk_statut DESC, r.datec ASC;';
		$resql_reg=$db->query($sql_reg);
		dol_syslog(get_class($this)."CRON - SQL :: ".$sql_reg);

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
            			$eventday->setReminederOpen('relance_waiting_auto',0);	
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
		                $substit['__LEVEL__'] = $level->label;
	                	$substit['__TIMESTART__'] = $eventday->time_start;
						$substit['__TIMEEND__'] = $eventday->time_end;
		                $substit['__LIEN_VALIDATION__'] = $url2;
		                $substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;

		                $sujet= $conf->global->EVENT_RELANCE_WAITING_SUJET;
		                $message= $conf->global->EVENT_RELANCE_WAITING_MESSAGE.'<br /><br />'.$event->description.'<br /><br />'.$conf->global->EVENT_REGISTRATION_SIGN_EMAIL;

		                $sujet=make_substitutions($sujet,$substit);
		                $message=make_substitutions($message,$substit);
		                $message=str_replace('\n',"\n",$message);

		                if ( isValidEmail( $contact->email) )
		                {
		                    if ( $registration->SendByEmail($eventday->ref,$contact->email,$contact->id,$sujet,$message,'','AC_REMIND') )
		                    {
		                        setEventMessage( $langs->trans('ReminderWaitingSent' ));
		                        $this->output = $langs->trans('ReminderWaitingSent');
								dol_syslog(get_class($this)." ".$langs->trans('ReminderWaitingSent',' '.dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email));
								$nb_sent++;
		                    }
		                    else
		                    {
		                        $error='1';
		                        setEventMessage( $langs->trans('ReminderNotSentTo', dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email),'errors');
		                        $this->output = $langs->trans('ReminderNotSentTo');
		                        dol_syslog(get_class($this)." ERROR ".$langs->trans('ReminderNotSentTo',' '.dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email));
		                    }
		                }
		            }
				}
				$i++;
			}
		(!isset($this->output))?$this->output="Pas de mail envoyé":'';
		}
	return $error?$error:0;
	}
}
?>