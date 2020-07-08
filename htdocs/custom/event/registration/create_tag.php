<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012-2016 JF FERRY			<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 *   	\file       event/registration/create.php
 *		\ingroup    event
 *		\brief      Page of module event for create a new registration
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");

require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/extrafields.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");



require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/registration.class.php");
require_once("../class/eventlevel.class.php");
require_once("../lib/event.lib.php");
require_once("../core/modules/registration/modules_registration.php");
require_once("../lib/html.formregistration.class.php");


// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

global $conf;

// Get parameters
$id					= GETPOST('id','int');
$eventid			= GETPOST('fk_event','int')>0?GETPOST('fk_event','int'):GETPOST('eventid','int');
$dayid				= GETPOST('fk_eventday','int')>0?GETPOST('fk_eventday','int'):GETPOST('dayid','int');
$action				= GETPOST('action','alpha',3);
$fk_level			= GETPOST('fk_level','',3);
$fk_soc				= GETPOST('fk_soc','int')?GETPOST('fk_soc','int'):GETPOST('socid');
$fk_user_registered	= GETPOST('fk_user_registered','int');
$confirm 			= GETPOST('confirm','alpha');
$select_contact 	= GETPOST('select_contact','alpha');
$event_send_email			= GETPOST('event_send_email', 'int');
$event_send_pdf			= GETPOST('event_send_pdf', 'int');
$select_tag			= GETPOST('select_tag');

$object=new Registration($db);

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

$enter_registration_check = false;

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
$hookmanager=new HookManager($db);
$hookmanager->initHooks(array('event_registration'));


/***************************************************
 * ACTIONS
*
****************************************************/
$parameters=array('socid'=>$socid);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

if ( $action == 'add' && $user->rights->event->write )
{
	$sql = "SELECT sp.rowid, sp.lastname, sp.firstname";
    $sql.= " FROM ".MAIN_DB_PREFIX."socpeople as sp INNER JOIN ".MAIN_DB_PREFIX."categorie_contact as c ON sp.rowid=c.fk_socpeople";
    $sql.= " WHERE ";
    $sql.= " c.fk_categorie=".$select_tag;
    $sql.= " ORDER BY sp.lastname";
// echo $sql;
    $resql=$db->query($sql);
    if ($resql)
    {
   		$num = $db->num_rows($resql);
   		$i = 0;

   	while ($i < $num)
    		{
    			$obj = $db->fetch_object($resql);

    			//code de base - START
    			$error='';
				$level=new Eventlevel($db);
				$eventday = new Day($db);
				$extrafields = new ExtraFields($db);
				$extrafields_contact = new ExtraFields($db);

				// fetch optionals attributes and labels
				$extralabels=$extrafields->fetch_name_optionals_label('event_registration');
				$extralabels_contact=$extrafields_contact->fetch_name_optionals_label('socpeople');

				$datenaiss='';
				// $nb_create='';
				if (isset($_POST["naissday"]) && $_POST["naissday"]
						&& isset($_POST["naissmonth"]) && $_POST["naissmonth"]
						&& isset($_POST["naissyear"]) && $_POST["naissyear"])
				{
					$datenaiss=dol_mktime(12, 0, 0, $_POST["naissmonth"], $_POST["naissday"], $_POST["naissyear"]);
				}

				$object->datec  = dol_now();
				$object->fk_soc	= $_POST["fk_soc"];
				
				$object->fk_user_registered	= $obj->rowid;

				// // Thirdparty & contact creation
				// $create_thirdparty 		= GETPOST('create_thirdparty','int');
				// $create_contact 		= GETPOST('create_contact','int');
				// $civility_id 			= GETPOST('civility_id','int');


				// $contact_name			= GETPOST('name','alpha');
				// $contact_firstname		= GETPOST('firstname','alpha');
				// $societe_name			= GETPOST('societe_name');

				// if(empty($societe_name))
				// 	$societe_name = $contact_name.' '.$contact_firstname;

				// $societeaddress		 	= GETPOST('address','alpha');
				// $contactaddress		 	= GETPOST('contact_address','alpha');
				// $zip 					= GETPOST('zipcode','alpha');
				// $town					= GETPOST('town','alpha');


				// $phone_perso			= GETPOST('tel_phone','alpha');
				// $phone_pro				= GETPOST('tel_pro','alpha');
				// $phone_mobile			= GETPOST('tel_mobile','alpha');
				// $mail					= GETPOST('mail','alpha');

				$object->message			= GETPOST('message');

				// Test si on a bien cliquer sur 'ajouter'
				if(GETPOST('add_registration','alpha') && !$error) {
                    $enter_registration_check = true;
                    if (!isRegister($dayid, $object->fk_user_registered, $db)) {
                        $error = 0;
                        /*
                         * An option allow to require choice of a level
                         */
                        // if($conf->global->EVENT_LEVEL_REQUIRED && empty($fk_level)) {
                        // 	$error++;
                        // 	$langs->load("errors");
                        // 	$errmsgs[]= '<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("EventLevel"))."<br></div>\n";
                        // }

                        /*
                         * option pour rendre le mail obligatoire
                         */
                        // if ($conf->global->EVENT_REGISTRATION_MAIL_REQUIRED && ! isValidEMail(GETPOST('registration_email'))) {
                        // 	$error++;
                        // 	$langs->load("errors");
                        // 	$errmsgs[]= '<div class="error">'.$langs->trans("ErrorBadEMail",$object->email_registration)."</div>\n";
                        // }

                        if (!$error) {
                            // $nb_create=0;
                            $db->begin();

                            $registration_to_create = array();
                            $nb = 0;

                            // Inscription sans groupe sélectionné
                            if (!is_array($fk_level)) {
                                // Boucle si choix de plusieurs journées
                                if (is_array(GETPOST('fk_eventday'))) {
                                    foreach (GETPOST('fk_eventday') as $key => $value) {
                                        $registration_to_create[$nb]['fk_event'] = GETPOST('fk_event');
                                        $registration_to_create[$nb]['fk_eventday'] = $value;
                                        $registration_to_create[$nb]['fk_levelday'] = $fk_level;

                                        $nb++;
                                    }
                                } else {
                                    $registration_to_create[$nb]['fk_event'] = GETPOST('fk_event');
                                    $registration_to_create[$nb]['fk_eventday'] = GETPOST('fk_eventday');
                                    $registration_to_create[$nb]['fk_levelday'] = $fk_level;

                                    $nb++;
                                }
                            } else {
                                $registration_created = array();

                                // Pour chaque journée on inscrit dans groupe choisi
                                foreach ($fk_level as $journee => $groupe) {
                                    for ($j = 0; $j < count($groupe); $j++) {
                                        $registration_to_create[$nb]['fk_event'] = GETPOST('fk_event');
                                        $registration_to_create[$nb]['fk_eventday'] = $journee;
                                        $registration_to_create[$nb]['fk_levelday'] = $groupe[$j];

                                        $nb++;
                                    }
                                }
                            }

                            // Création des inscriptions
                            foreach ($registration_to_create as $key => $registration_prop) {
                                $object->fk_event = $registration_prop['fk_event'];
                                $object->fk_eventday = $registration_prop['fk_eventday'];
                                $object->fk_levelday = $registration_prop['fk_levelday'];

                                $extrafields->setOptionalsFromPost($extralabels, $object);

                                $result = $object->create($user);
                                if ($result > 0) {
                                    $db->commit();
                                    $nb_create++;
                                    $registration_created[] = $object->id;
                                } else {
                                    $db->rollback();

                                    if ($object->error) $errmsgs[] = $object->error;
                                    else $errmsgs[] = $object->errors;
                                    $action = '';
                                }
                            }

                            // Validation inscriptions et envoi mail
                            if (empty($registration_valid_after_create))
                                $registration_valid_after_create = GETPOST('registration_valid_after_create', 'int');
                            else
                                $registration_valid_after_create++;

                            if ($registration_valid_after_create > 0)   // We want registration validated
                            {

                                if (is_array($registration_created) && count($registration_created) > 0) {
                                    $regstat = new Registration($db);
                                    $registration_id = $registration_created[sizeof($registration_created) - 1];
                                    $res = $regstat->fetch($registration_id);


                                    if ($res > 0) {
                                        $regstat->actionmsg2 = $langs->transnoentities("EventRegistrationValidated", $object->id);
                                        $result = $regstat->setValid($user);
                                        if ($result < 0) {
                                            $error++;
                                        } else {
                                            if ($event_send_email > 0) {
                                                if ($event_send_pdf > 0) {
                                                    // Génération du PDF
                                                    require_once("../core/modules/event/modules_event.php");
                                                    require_once("../core/modules/registration/modules_registration.php");

                                                    $langs->load('event@event');
                                                    $result = event_pdf_create($db, $regstat, 'registration', $langs);
                                                    if ($result <= 0) {
                                                        $error++;
                                                    }
                                                }

                                                // Send PDF
                                                require_once(DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
                                                require_once(DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php');
                                                $contactforaction = new Contact($db);
                                                $societeforaction = new Societe($db);
                                                $user_create = new User($db);
                                                $contact_registered = new Contact($db);
                                                if ($regstat->fk_soc > 0) $societeforaction->fetch($regstat->fk_soc);

                                                if ($regstat->fk_user_create > 0) $user_create->fetch($regstat->fk_user_create);
                                                if ($regstat->fk_user_registered > 0) $contact_registered->fetch($regstat->fk_user_registered);
                                                $sendtoid = 0;

                                                // Si c'est un user externe qui a fait l'inscription d'un invité on prend ses infos
                                                if (
                                                    $user_create->societe_id > 0
                                                    AND ($regstat->fk_user_create != $regstat->fk_user_registered)
                                                ) {
                                                    $sendto = $user_create->email;
                                                    $sendto_sms = $user_create->user_mobile;
                                                    //$sendtoid = $user_create->id;
                                                } else // Dans les autres cas on prend les infos du participant
                                                {
                                                    $sendto = $contact_registered->email;
                                                    $sendto_sms = $contact_registered->phone_mobile;
                                                    $sendtoid = $contact_registered->id;
                                                }
                                                $langs->load('event@event');
                                                $unique_key = $regstat->getValueFrom('event_registration', $regstat->id, 'unique_key');
                                                $url = DOL_URL_ROOT . "/custom/event/registration/confirm_register.php?id=" . $regstat->id . "&key=" . $unique_key;

                                                if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
                                                    $url2 = '<a href="http://localhost' . $url . '">Lien</a>';
                                                else
                                                    $url2 = '<a href="' . $conf->global->EVENT_MAIN_URL_REGISTRATION . $url . '">Lien</a>';

                                                if ($dayid) {
                                                    $eventday = new Day($db);
                                                    $eventday->fetch($dayid);
                                                }
                                                if ($eventid) {
                                                    $event = new Event($db);
                                                    $event->fetch($eventid);
                                                }

                                                $sujet = $langs->transnoentities('SendValidRegistration') . ' ' . $eventday->label;

                                                $substit['__REGREF__'] = $regstat->ref;
                                                $substit['__EVENEMENT__'] = $event->label;
                                                $substit['__JOURNEE__'] = $eventday->label;
                                                $substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event, 'day');
                                                $substit['__PARTICIPANT__'] = dolGetFirstLastname($contact_registered->firstname, $contact_registered->lastname);
                                                $substit['__LIEN_VALIDATION__'] = $url2;
                                                $substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION . $url;
                                                $substit['__TIMESTART__'] = $eventday->time_start;
                                                $substit['__TIMEEND__'] = $eventday->time_end;

                                                $message .= make_substitutions($object->message, $substit);

                                                $now = dol_now();

                                                $result = $regstat->SendByEmail($day->ref, $sendto, $sendtoid, $sujet, $message, '', '');

                                                if ($result) {
                                                    dol_syslog('Envoi du mail de validation : OK' . $sendto);
                                                } else {
                                                    $error++;
                                                    dol_syslog("Echec de l'envoi du mail de validation : KO" . $sendto . ' ' . $regstat->error, LOG_ERR);
                                                }
                                                $message = "";
                                                $sento = "";
                                                $url2 = "";
                                                $sendtoid = "";
                                                $sujet = "";
                                                unset($contactforaction);
                                                unset($societeforaction);
                                                unset($user_create);
                                                unset($contact_registered);
                                            }
                                        }
                                    }
                                }
                                unset($regstat);
                            }
                        }
                        unset($level);
                        unset($eventday);
                        unset($extrafields);
                        unset($extrafields_contact);
                    }
                    //code de base - END
                }
                $i++;
		    }
    }
    if ($nb_create > 0)
	{
		if($event_send_email > 0)
		{
			$errmsgs = 'EventRegSuccesswithMail';
			$action = '';
			header("Location: ".DOL_URL_ROOT."/custom/event/day/fiche.php?id=".$dayid."&message_alert=".$errmsgs);
		}
		else
			{
			$errmsgs = 'EventRegSuccesswitouthMail';
			$action = '';
			header("Location: ".DOL_URL_ROOT."/custom/event/day/fiche.php?id=".$dayid."&message_alert=".$errmsgs);
		}
	}
	else if ($enter_registration_check) // check if all users of tag are register
    {
        $error="UsersTagAlreadyRegister";
        $errmsgs[] = '<div class="error">'.$langs->trans('UsersTagAlreadyRegister').'</div>';
        $action = '';
    }
}

/**
 * Check if user $userid is already register to the the day $dayid
 *
 * @param   int             $dayid          Id of the day
 * @param   int             $userid         Id of the user
 * @param   Database        $db             The object given by dolibarr global (global $db)
 * @return  bool                            true if user register, false if user not register
 */
function isRegister($dayid, $userid, $db)
{
    $sql = "SELECT r.fk_user_registered, p.rowid FROM ".MAIN_DB_PREFIX."event_registration as r LEFT JOIN ".MAIN_DB_PREFIX."socpeople as p ON r.fk_user_registered = p.rowid WHERE r.fk_eventday = ".$dayid." AND r.fk_user_registered = ".$userid;
    $resql = $db->query($sql);
    if ($resql)
        $res = $resql->fetch_assoc();
    if (!empty($res['rowid']))
    {
        dol_syslog("Code 42: User " . $userid . " already register for day " . $dayid);
        return true;
    }
    return false;
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("RegistrationCreation"),'');

$form=new Form($db);
$form_register=new FormRegistration($db);

dol_htmloutput_mesg($errmsg,$errmsgs);
if(count($registration_created) > 0)
{
	$regstat = new Registration($db);
	print '<div class="info"><h2>'.$langs->trans('NewRegistrationsCreated').'</h2>';
	print '<ul>';
	foreach($registration_created as $key => $registration_id)
	{
		$regstat->fetch($registration_id);
		print '<li>'.$regstat->getNomUrl(1).' - '.$regstat->getLibStatut(2).'</li>';
	}
	print '<ul>';
	print '</div>';
}

/*
 * Event list
 */
if ( $user->rights->event->write )
{
		$errors='';
		
		print_fiche_titre($langs->trans('RegistrationCreate'),'','event_registration@event');
		if($eventid) {
			$event = new Event($db);
			$event->fetch($eventid);
		}
		if($dayid) {
			$eventday = new Day($db);
			$eventday->fetch($dayid);
			$eventid=$eventday->fk_event;

			$event = new Event($db);
			$event->fetch($eventid);
		}

		if(!$eventid) {
			$error++;
			$mesgs[]='<div class="warning">'.$langs->trans("PleaseSelectAnEventForRegistration").'</div>';
		}

		// Check parameters
		$form_register->allowedregistration = 1;

		if ( !$error && !$event->registration_open )
		{
			$form_register->allowedregistration = $user->admin?1:0; // Allowed for admin
			$form_register->withcancel = 1;
			$error++;
			$mesgs[]='<div class="error">'.$langs->trans("RegistrationAreNotOpenForThisEvent").'</div>';
		}
		if( !$error && !$event->registration_byday)
		{
			$form_register->registrationbyday = 0;
			$error++;
			$mesgs[]='<div class="warning">'.$langs->trans("RegistrationArePossibleButNotOpenForThisEventDay").'</div>';
		}

		$form_register->withevent=1;
		$form_register->fk_event = (!empty($event->id) ? $event->id:$eventday->fk_event);

		$form_register->witheventday=1;
		// Si inscription par jour désactivé alors on met l'id journée à -1
		$form_register->fk_eventday = ( $event->registration_byday ? $dayid : -1);
		$form_register->select_tag = $select_tag;

		$form_register->datec = $eventday->date_event;
		$form_register->fk_user_create = $user->id;

		$form_register->withsocid =1;
		$form_register->fk_soc = $fk_soc;

		$form_register->param = array(
				'fk_event' => $eventid,
				'fk_eventday' => $dayid,
				'select_tag' => $select_tag,
		);


		if($user->societe_id > 0)
		{
			$form_register->withuserregistered=0;
			$form_register->param['fk_user_registered']=$user->contactid;

		}
		else
		{
			$form_register->withuserregistered=1;
			$form_register->fk_user_registered=$fk_user_registered;
		}

		$form_register->withlevel =1;
		$form_register->fk_level = $fk_level;

		$form_register->withinfos =1;
		$form_register->civility_id=0;
		$form_register->lastname='';
		$form_register->firstname='';
		// $form_register->action='select_tag';

		if($conf->global->EVENT_REGISTRATION_MAIL_REQUIRED)
			$form_register->mailrequired=true;

		//message
		$regstat = new Registration($db);

		// $message = '<p>'.$langs->transnoentities('PredefinedMailContentSendRegistration').'</p>';
		// $message .= '<p> '.$langs->transnoentities('SendValidRegistrationBodyPrint').'</p>';
		// Code 42 Add description
		$sql = "SELECT ed.description FROM ".MAIN_DB_PREFIX."event_day AS ed WHERE ed.rowid = ".$dayid;
		$resql = $db->query($sql);
		if ($resql)
		{
			$res = $resql->fetch_assoc();
			if (!empty($res))
				// $message.= '<p>Description de la journée : <br/>'.$res['description'].'</p>';
				$message.= '<p>'.$res['description'].'</p>';
		}

		// Add sign
		$message.= $conf->global->EVENT_REGISTRATION_SIGN_EMAIL;

		$form_register->message = $message;

		dol_htmloutput_mesg($mesg,$mesgs);

		// $form_register->backtopage='custom/event/registration/list.php?dayid=';
		//$form_register->backtopage=$_SERVER['PHP_SELF'].'?'.($eventid>0?'fk_event='.$eventid.'&':'').'dayid='.$dayid.'&action=create&socid='.$fk_soc;

		$form_register->show_form_tag();

}
else
{
	accessforbidden("",0,0);
}


?>
