<?php
/* Copyright (C) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2011 Regis Houssin        <regis@dolibarr.fr>
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
 *      \file       event/core/triggers/interface_modEvent_EventEmail.class.php
 *      \ingroup    event
 *      \brief      Envoi de mails suivant les acttions sur les inscriptions (module event)
 */
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");

/**
 *      \class      InterfaceEventEmail
 *      \brief      Class of triggers for event module
*/
class InterfaceEventEmail
{
	var $db;

	/**
	 *   Constructor.
	 *   @param      DB      Database handler
	 */
	function __construct($db)
	{
		$this->db = $db ;

		$this->name = preg_replace('/^Interface/i','',get_class($this));
		$this->family = "event";
		$this->description = "Triggers of this module are empty functions. They have no effect. They are provided for tutorial purpose only.";
		$this->version = 'dolibarr';            // 'development', 'experimental', 'dolibarr' or version
		$this->picto = 'event@event';
	}


	/**
	 *   Return name of trigger file
	 *   @return     string      Name of trigger file
	 */
	function getName()
	{
		return $this->name;
	}

	/**
	 *   Return description of trigger file
	 *   @return     string      Description of trigger file
	 */
	function getDesc()
	{
		return $this->description;
	}

	/**
	 *   Return version of trigger file
	 *   @return     string      Version of trigger file
	 */
	function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') return $langs->trans("Development");
		elseif ($this->version == 'experimental') return $langs->trans("Experimental");
		elseif ($this->version == 'dolibarr') return DOL_VERSION;
		elseif ($this->version) return $this->version;
		else return $langs->trans("Unknown");
	}

	/**
	 *      Function called when a Dolibarrr business event is done.
	 *      All functions "run_trigger" are triggered if file is inside directory htdocs/includes/triggers
	 *      @param      action      Code de l'evenement
	 *      @param      object      Objet concerne
	 *      @param      user        Objet user
	 *      @param      langs       Objet langs
	 *      @param      conf        Objet conf
	 *      @return     int         <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	function run_trigger($action,$object,$user,$langs,$conf)
	{
		global $mysoc;

		if ($action == 'EVENT_REGISTRATION_CONFIRM')
		{
			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

			require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
			require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');
			$contactforaction=new Contact($object->db);
			$societeforaction=new Societe($object->db);
			$contact_create = new Contact($object->db);
			$contact_registered = new Contact($object->db);
			if ($object->fk_soc > 0)    $societeforaction->fetch($object->fk_soc);

			if($object->fk_user_create > 0)	$contact_create->fetch($object->fk_user_create);
			if($object->fk_user_registered > 0)	$contact_registered->fetch($object->fk_user_registered);
			$sendtoid = 0;
			
			// Si c'est un user externe qui a fait l'inscription d'un invité on prend ses infos
			if( 
				$contact_create->societe_id > 0
				AND ($object->fk_user_create != $object->fk_user_registered)
			){
				$sendto = $contact_create->email;
				$sendto_sms = $contact_create->phone_mobile;
				$sendtoid = $contact_create->id;
			}
			else // Dans les autres cas on prend les infos du participant
			{
				$sendto = $contact_registered->email;
				$sendto_sms = $contact_registered->phone_mobile;
				$sendtoid = $contact_registered->id;
			}
			
			
			$langs->load('event@event');
			$sujet=$langs->transnoentities('SendConfirmRegistration',$object->getValueFrom('event', $object->fk_event, 'label'));
			$message= $langs->transnoentities('EventHello');
			$message.= "\n\n";
			$message .= $langs->transnoentities('SendConfirmRegistrationBody',dol_print_date($object->getValueFrom('event_day', $object->fk_eventday, 'date_event'),'daytext'),$object->getValueFrom('event', $object->fk_event, 'label'),$object->getValueFrom('event_level', $object->fk_levelday, 'label'));
			$message.= "\n\n";
			$message .= $langs->transnoentities('SendConfirmRegistrationBodyTrainee',strtoupper($contact_registered->lastname).' '.ucwords($contact_registered->firstname));
			$message.= "\n\n";
			// Add sign
			$message.= "\n--\n";
			$message.= $conf->global->EVENT_REGISTRATION_SIGN_EMAIL;
			
			$message_sms = $langs->transnoentities('SendSMSConfirmRegistrationBody',dol_print_date($object->getValueFrom('event_day', $object->fk_eventday, 'date_event'),'daytext'),$object->getValueFrom('event', $object->fk_event, 'label'),$object->getValueFrom('event_level', $object->fk_levelday, 'label'));
			$message_sms.= "\n-- ".$mysoc->name;
			
			$ok = 1;
		}

		/*
		 *  Send Email & SMS
		 */
		if ($ok)
		{
			$now=dol_now();
			
			$message = dol_nl2br($message,0,1);
            // Send email without attached file
			$result = $object->SendByEmail('',$sendto,$sendtoid,$sujet,$message,0);
				
			if($result) {
				dol_syslog('Envoi du mail de confirmation : OK');
			}
			else {
				$error++;
				dol_syslog($this->name." : ".$object->error, LOG_ERR);
			}
			if($sendto_sms) 
			{
				$result = $object->SendBySms($sendto_sms,$sendtoid,$message_sms);
				if($result) {
					dol_syslog('Envoi du SMS de confirmation : OK');
				
				}
				else {
					$error++;
					dol_syslog($this->name." : ".$object->error, LOG_ERR);
				}
			}
			
			
			if($error)
			{
				dol_syslog($this->name." : ".$this->error, LOG_ERR);
				return -1;
			}
			else 
				return 1;
		}
		
		return 0;
	}

}
