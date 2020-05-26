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
require_once("../class/eventlevel_cal.class.php");
require_once("../lib/event.lib.php");
require_once("../core/modules/registration/modules_registration.php");
require_once("../core/modules/event/modules_event.php");
require_once("../lib/html.formregistration.class.php");


// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

global $conf,$user,$db;

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
$event_send_email	= GETPOST('event_send_email', 'int');
$event_send_pdf		= GETPOST('event_send_pdf', 'int');

$object=new Registration($db);

// Protection if external user
// if ($user->societe_id > 0)
// {
// 	accessforbidden();
// }

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

if ( $action == 'addd' ) //&& r->rights->registration->write
{

$userId = $user->id;
$error='';
$level=new Eventlevel($db);
$eventday = new Day($db);

$extrafields = new ExtraFields($db);
$extrafields_contact = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label('event_registration');
$extralabels_contact=$extrafields_contact->fetch_name_optionals_label('socpeople');

$object->datec = dol_now();

$object->fk_soc				= $_POST["fk_soc"];
$object->fk_user_registered	= trim($_POST["fk_user_registered"]);

if($conf->global->MAIN_FEATURES_LEVEL=='3') {
	print '<br />DROIT : '.$user->rights->registration->write;
	print '<br />ACTION : '.GETPOST('action');
	print '<br />registration_valid_after_create : '.GETPOST('registration_valid_after_create');
	print '<br />fk_user_create : '.GETPOST('fk_user_create');
	print '<br />fk_user_registered : '.GETPOST('fk_user_registered');
	print '<br />redirect_to : '.GETPOST('redirect_to');
	print '<br />fk_level : '.var_dump(GETPOST('fk_level'));
	}
//die(var_dump($fk_level));
if(!is_array($fk_level)) {
	// ADDD - Boucle si choix de plusieurs journées
	if(is_array(GETPOST('fk_eventday'))) {
		foreach( GETPOST('fk_eventday') as $key=>$value) {
			if (!($fk_level[0] == '-')){
			$eventday->fetch($value);
			$registration_to_create[$nb]['fk_event'] = $eventday->fk_event;
			$registration_to_create[$nb]['fk_eventday'] = $value;
			$registration_to_create[$nb]['fk_levelday'] = $fk_level;

			//On dédite les crédits.
			$calendrier = new Eventlevel_cal($db);
			$calendrier->fetch_all($fk_level, $eventday->id);
			$nombre = ($calendrier->lines[0]->heuref - $calendrier->lines[0]->heured) / 60;
			$admin_user = new User($db);
			$admin_user->fetch(1);
			$user->array_options['options_event_counter'] -= $nombre;
			$user->update($admin_user);
				}
			else{
				//Requete pour obtenir l'id de la registration
				$eventday->fetch($value);
				$fk_level = str_replace('-', '', $fk_level);
				$sql_reg = "SELECT r.rowid";
				$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r';
				$sql_reg.= ' WHERE r.fk_event='.$eventday->fk_event;
				$sql_reg.=' AND r.fk_eventday='.$value;
				$sql_reg.=' AND r.fk_levelday='.$fk_level;
				$sql_reg.=  ' ORDER BY r.fk_statut DESC, r.datec ASC;';
				$resql_reg=$db->query($sql_reg);
				$tmp = $resql_reg->fetch_assoc();
				$insc = $tmp['rowid'];

				$reg = new Registration($db);
				$ret = $reg->fetch($insc);
				$reg->delete($user);

				//on dédite les crédits
				$calendrier = new Eventlevel_cal($db);
				$calendrier->fetch_all($value, $eventday->id);
				$nombre = ($calendrier->lines[0]->heuref - $calendrier->lines[0]->heured) / 60;
				$admin_user = new User($db);
				$admin_user->fetch(1);
				$user->array_options['options_event_counter'] += $nombre;
				$user->update($admin_user);
			}
			$nb++;
			}
		}
		else
		{
			if (!($fk_level[0] == '-')){
			$eventday->fetch($value);
			$registration_to_create[$nb]['fk_event'] = $eventday->fk_event;
			$registration_to_create[$nb]['fk_eventday'] = $value;
			$registration_to_create[$nb]['fk_levelday'] = $fk_level;

			//On dédite les crédits.
			$calendrier = new Eventlevel_cal($db);
			$calendrier->fetch_all($fk_level, $eventday->id);
			$nombre = ($calendrier->lines[$i]->heuref - $calendrier->lines[$i]->heured) / 60;
			$admin_user = new User($db);
			$admin_user->fetch(1);
			$user->array_options['options_event_counter'] -= $nombre;
			$user->update($admin_user);
				}
			else{
				//Requete pour obtenir l'id de la registration
				$eventday->fetch($value);
				$fk_level = str_replace('-', '', $fk_level);
				$sql_reg = "SELECT r.rowid";
				$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r';
				$sql_reg.= ' WHERE r.fk_event='.$eventday->fk_event;
				$sql_reg.=' AND r.fk_eventday='.$value;
				$sql_reg.=' AND r.fk_levelday='.$fk_level;
				$sql_reg.=' AND r.fk_user_registered = '.$object->fk_user_registered;
				// $sql_reg.=  ' ORDER BY r.fk_statut DESC, r.datec ASC;';
				$resql_reg=$db->query($sql_reg);
				$tmp = $resql_reg->fetch_assoc();
				$insc = $tmp['rowid'];

				$reg = new Registration($db);
				$ret = $reg->fetch($insc);
				
				
									
				$reg->delete($user);

				//On re-crétdite les crédits.
				$calendrier = new Eventlevel_cal($db);
				$calendrier->fetch_all($value, $eventday->id);
				$nombre = ($calendrier->lines[0]->heuref - $calendrier->lines[0]->heured) / 60;
				$admin_user = new User($db);
				$admin_user->fetch(1);
				$user->array_options['options_event_counter'] += $nombre;
				$user->update($admin_user);
			}
			$nb++;
		}
	}
	else
	{
		$registration_created= array();
		// Pour chaque journée on inscrit dans groupe choisi
		foreach($fk_level as $journee => $groupe)
		{
			for($j=0; $j < count($groupe); $j++)
			{
				if (!($groupe[$j][0] == '-')){
				$eventday->fetch($journee);
				$registration_to_create[$nb]['fk_event'] = $eventday->fk_event;
				$registration_to_create[$nb]['fk_eventday'] = $journee;
				$registration_to_create[$nb]['fk_levelday'] = $groupe[$j];

				//On dédite les crédits.
				$calendrier = new Eventlevel_cal($db);
				$calendrier->fetch_all($groupe[$j], $eventday->id);
				$nombre = (int) ($calendrier->lines[0]->heuref - $calendrier->lines[0]->heured) / 60;
				$admin_user = new User($db);
				$admin_user->fetch(1);

                    print "RESERVATION DE COURS<br>";


				print "Ancien solde client";
				$old = (int) $user->array_options['options_event_counter'];
				//var_dump($old);

				print "Cout du cours";
				//var_dump($nombre);

				$user->array_options['options_event_counter'] -= $nombre;
                //$user->array_options['options_event_counter'] = 3000;

				$user->update($admin_user);
				$user->fetch($userId);

                    print "Solde du client après update.";
                    //var_dump((int) $user->array_options['options_event_counter']);

				$NEWnew = $user->array_options['options_event_counter'];

			}
				else{

					//Requete pour obtenir l'id de la registration
					$eventday->fetch($journee);
					$groupe[$j] = str_replace('-', '', $groupe[$j]);
					$sql_reg = "SELECT r.rowid";
					$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r';
					$sql_reg.= ' WHERE r.fk_event='.$eventday->fk_event;
					$sql_reg.=' AND r.fk_eventday='.$journee;
					$sql_reg.=' AND r.fk_levelday='.$groupe[$j];
					$sql_reg.=' AND r.fk_user_registered = '.$object->fk_user_registered;
					// $sql_reg.=  ' ORDER BY r.fk_statut DESC, r.datec ASC;';
					$resql_reg=$db->query($sql_reg);
					$tmp = $resql_reg->fetch_assoc();
					$insc = $tmp['rowid'];

					$reg = new Registration($db);
					$ret = $reg->fetch($insc);
					
					/// MAIL ANNULATION ///////////////
					
					//$reguser = new User($db);
					$event = new Event($db);
					$day = new Day($db);
					
					$event->fetch($eventday->fk_event);
					//$reguser->fetch($object->fk_user_registered);
					$day->fetch($eventday->fk_event);
					//$filedir=$conf->event->dir_output."/".dol_sanitizeFileName($reg->ref);
					$sendto = $user->email;
					
					//print_r($user);
					//exit(0);
					$sendtoid = 0;

					$sujet=$conf->global->EVENT_CANCELED_SUJET;

					$substit['__REGREF__'] = $ret->ref;
					$substit['__EVENEMENT__'] = $event->label;
					$substit['__JOURNEE__'] = $day->label;
					$substit['__DATEJOURNEE__'] = dol_print_date($day->date_event, 'day');
					$substit['__PARTICIPANT__'] = dolGetFirstLastname($user->firstname, $user->lastname);
					$substit['__LIEN_VALIDATION__'] = $url2;
					$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;
					$substit['__TIMESTART__'] = $day->time_start;
					$substit['__TIMEEND__'] = $day->time_end;

					$sujet= make_substitutions($sujet,$substit);
					$message= make_substitutions($conf->global->EVENT_CANCELED_MESSAGE, $substit);
					$reg->setConfirmed('1');
					$result = $reg->SendByEmail($day->ref, $sendto,$sendtoid,$sujet,$message,($conf->global->EVENT_SEND_PDF=='-1'?'':'1'), '', '');				
									
					////////////////////////////////////					

					$reg->delete($user);

					//On re-crétdite les crédits.
					$calendrier = new Eventlevel_cal($db);
					$calendrier->fetch_all($groupe[$j], $eventday->id);
					$nombre = (int) ($calendrier->lines[0]->heuref - $calendrier->lines[0]->heured) / 60;
					$admin_user = new User($db);
					$admin_user->fetch(1);

					//$ancienSolde = $user->array_options['options_event_counter'];
					//$nouveauSolde = $ancienSolde += $nombre;

                    //$user->array_options['options_event_counter'] = $nouveauSolde;
                    //$user->update($admin_user);

                    //$user->fetch($userId);

                    print "ANNULATION DE COURS<br>";

                    print "Ancien solde client";
                    $oldOLD = (int) $user->array_options['options_event_counter'];
                    //var_dump($oldOLD);

                    print "Cout du cours";
                    //var_dump($nombre);



					$user->array_options['options_event_counter'] += $nombre;
                    //$user->array_options['options_event_counter'] = 2000;

					$user->update($admin_user);

					$user->fetch($user->id);

                    print "Nouveau solde APRES update";

                    //$updatedUser = new User($db);
                    //$updatedUser->fetch($userId);

                    $user->fetch($userId);

                    //var_dump((int) $user->array_options['options_event_counter']);



                }
				if($conf->global->MAIN_FEATURES_LEVEL=='3') print '<br /><b>RECEIV</b> - EVENT : '.$registration_to_create[$nb]['fk_event'].' - JOUR : '. $journee . ' GRP : '.$groupe[$j];
				$nb++;
			}
		}
	}
	// ADDD - Création des inscriptions
	foreach($registration_to_create as $key => $registration_prop)
	{
		$object->fk_event 		= $registration_prop['fk_event'];
		$object->fk_eventday 	= $registration_prop['fk_eventday'];
		$object->fk_levelday 	= $registration_prop['fk_levelday'];
		if(MAIN_FEATURES_LEVEL=='3') print '<br /><b>RESERV</b> - EVENT : '.$object->fk_event.' JOUR : '. $object->fk_eventday . ' GRP : '.$object->fk_levelday;
		
		print '<br /><b>RESERV</b> - EVENT : '.$object->fk_event.' JOUR : '. $object->fk_eventday . ' GRP : '.$object->fk_levelday;

		// $extrafields->setOptionalsFromPost($extralabels, $object);

		//Code 42 Test si déjà inscrit
		$sql = "SELECT r.fk_user_registered, r.rowid FROM ".MAIN_DB_PREFIX."event_registration as r LEFT JOIN ".MAIN_DB_PREFIX."socpeople as p ON r.fk_user_registered = p.rowid WHERE r.fk_eventday = ".$object->fk_eventday." AND r.fk_levelday = ".$object->fk_levelday." AND r.fk_user_registered = ".$object->fk_user_registered;
		$resql = $db->query($sql);
		if ($resql)
			$res = $resql->fetch_assoc();

		if (empty($res['rowid'])) {
			$result=$object->create($user);
			if ($result > 0)
			{
				@$db->commit();
				@$object->setConfirmed('1');
				$nb_create++;
				$registration_created[] = $object->id;
			}
			else
			{
				$db->rollback();

				if ($object->error) $errmsgs[]=$object->error;
				else $errmsgs[]=$object->errors;
				$action = '';
			}
		}
	}

	foreach ($registration_created as $id){
		$reg = new Registration($db);
		$reguser = new User($db);
		$contact = new Contact($db);
		$event = new Event($db);
		$day = new Day($db);
		
		$reg->fetch($id);
		//print_r($reg);
		$event->fetch($reg->fk_event);
		print "<br/> regieteed : ".$reg->fk_user_registered.'<br/>';
		$reguser->fetch($reg->fk_user_created);
		$contact->fetch($reg->fk_user_registered);
		//print_r($reguser);
		$day->fetch($reg->fk_eventday);
		$filedir=$conf->event->dir_output."/".dol_sanitizeFileName($reg->ref);
		$sendto = $contact->email;
		$sendtoid = 0;
		
		
		$sujet=$conf->global->EVENT_PARTICIPATE_SUJET;

		$substit['__REGREF__'] = $reg->ref;
		$substit['__EVENEMENT__'] = $event->label;
		$substit['__JOURNEE__'] = $day->label;
        $substit['__DATEJOURNEE__'] = dol_print_date($day->date_event, 'day');
        $substit['__PARTICIPANT__'] = dolGetFirstLastname($contact->firstname, $contact->lastname);
		$substit['__LIEN_VALIDATION__'] = $url2;
		$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;
        $substit['__TIMESTART__'] = $day->time_start;
        $substit['__TIMEEND__'] = $day->time_end;

		$sujet= make_substitutions($sujet,$substit);
		$message= make_substitutions($conf->global->EVENT_PARTICIPATE_MESSAGE, $substit);
		$reg->setConfirmed('1');
		
		$result = $reg->SendByEmail($day->ref, $sendto,$sendtoid,$sujet,$message,($conf->global->EVENT_SEND_PDF=='-1'?'':'1'), '', ($conf->global->EVENT_MANAGE_ICS=='-1'?'':'1'));
		//PRINT "Mail à ".$sendto;
	}
	if(MAIN_FEATURES_LEVEL!='3') Header("Location: ".GETPOST(redirect_to));
}
elseif ( $action == 'add' ) //&& $user->rights->event->write
{
	$error='';
	$level=new Eventlevel($db);
	$eventday = new Day($db);

	$extrafields = new ExtraFields($db);
	$extrafields_contact = new ExtraFields($db);

	// fetch optionals attributes and labels
	$extralabels=$extrafields->fetch_name_optionals_label('event_registration');
	$extralabels_contact=$extrafields_contact->fetch_name_optionals_label('socpeople');

	$datenaiss='';
	$nb_create='';
	if (isset($_POST["naissday"]) && $_POST["naissday"]
			&& isset($_POST["naissmonth"]) && $_POST["naissmonth"]
			&& isset($_POST["naissyear"]) && $_POST["naissyear"])
	{
		$datenaiss=dol_mktime(12, 0, 0, $_POST["naissmonth"], $_POST["naissday"], $_POST["naissyear"]);
	}

	$object->datec = dol_now();

	$object->fk_soc				= $_POST["fk_soc"];
	$object->fk_user_registered	= trim($_POST["fk_user_registered"]);

	// Thirdparty & contact creation
	$create_thirdparty 		= GETPOST('create_thirdparty','int');
	$create_contact 		= GETPOST('create_contact','int');
	$civility_id 			= GETPOST('civility_id','int');

	$contact_name			= GETPOST('name','alpha');
	$contact_firstname		= GETPOST('firstname','alpha');
	$societe_name			= GETPOST('societe_name');

	if(empty($societe_name))
		$societe_name = $contact_name.' '.$contact_firstname;

	$societeaddress		 	= GETPOST('address','alpha');
	$contactaddress		 	= GETPOST('contact_address','alpha');
	$zip 					= GETPOST('zipcode','alpha');
	$town					= GETPOST('town','alpha');


	$phone_perso			= GETPOST('tel_phone','alpha');
	$phone_pro				= GETPOST('tel_pro','alpha');
	$phone_mobile			= GETPOST('tel_mobile','alpha');
	$mail					= GETPOST('mail','alpha');

	// Création tiers demandé
	if($create_thirdparty > 0) {
		$socstatic = new Societe($db);

		$socstatic->name = $societe_name;
		$socstatic->phone = $phone_pro;
		$socstatic->email = $mail;
		$socstatic->address=$societeaddress;
		$socstatic->zip=$zip;
		$socstatic->town=$town;
		$socstatic->client=1;

		$result = $socstatic->create($user);

		if (! $result >= 0)
		{
			$error=$socstatic->error; $errors=$socstatic->errors;
		}

		$object->fk_soc=$socstatic->id;

	}

	// Création du contact si demandé
	if($create_contact > 0) {

		$contact=new Contact($db);

		$contact->civility_id		= $civility_id;
		$contact->lastname			= $contact_name;
		$contact->firstname			= $contact_firstname;
		$contact->address			= $contactaddress;
		$contact->zip				= $zip;
		$contact->town				= $town;
		$contact->state_id      	= $state_id;
		$contact->country_id		= $objectcountry_id;
		$contact->socid				= $object->fk_soc;
		$contact->status			= 1;
		$contact->email				= $mail;
		$contact->phone_pro			= $phone_pro;
		$contact->phone_mobile		= $phone_mobile;
		$contact->poste				= $fonction;
		$contact->priv				= 0;

		$extrafields_contact->setOptionalsFromPost($extralabels_contact, $contact);

		$result=$contact->create($user);
		if (! $result >= 0)
		{
			$error=$contact->error; $errors=$contact->errors;
		}
		$object->fk_user_registered = $contact->id;
	}

	$object->message = GETPOST('message');

	//Code 42 Test si déjà inscrit
	$sql = "SELECT r.fk_user_registered, p.rowid FROM ".MAIN_DB_PREFIX."event_registration as r LEFT JOIN ".MAIN_DB_PREFIX."socpeople as p ON r.fk_user_registered = p.rowid WHERE r.fk_eventday = ".$dayid." AND r.fk_user_registered = ".$object->fk_user_registered;
	$resql = $db->query($sql);
	if ($resql)
		$res = $resql->fetch_assoc();
	if (!empty($res['rowid']))
	{
		$error="UserAlreadyCreate";
		$errmsgs[] = '<div class="error">'.$langs->trans('UserAlreadyCreate').'</div>';
		$action = '';
	}

	// Test si on a bien cliquer sur 'ajouter'
	if(GETPOST('add_registration','alpha') && !$error)
	{
		$error=0;

		/*
		 * An option allow to require choice of a level
		 */
		if($conf->global->EVENT_LEVEL_REQUIRED && empty($fk_level)) {
			$error++;
			$langs->load("errors");
			$errmsgs[]= '<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("EventLevel"))."<br></div>\n";
		}

		/*
		 * option pour rendre le mail obligatoire
		 */
		if ($conf->global->EVENT_REGISTRATION_MAIL_REQUIRED && ! isValidEMail(GETPOST('registration_email'))) {
			$error++;
			$langs->load("errors");
			$errmsgs[]= '<div class="error">'.$langs->trans("EventErrorBadEMail",GETPOST('registration_email'))."</div>\n";
		}

		if (! $error)
		{
			$nb_create=0;
			$db->begin();

			$registration_to_create = array();
			$nb=0;

			// ADD - Inscription sans groupe sélectionné
			if(!is_array($fk_level))
			{
				// Boucle si choix de plusieurs journées
				if(is_array(GETPOST('fk_eventday')))
				{
					foreach( GETPOST('fk_eventday') as $key=>$value)
					{
						$registration_to_create[$nb]['fk_event'] = GETPOST('fk_event');
						$registration_to_create[$nb]['fk_eventday'] = $value;
						$registration_to_create[$nb]['fk_levelday'] = $fk_level;

						$nb++;
					}
				}
				else
				{
					$registration_to_create[$nb]['fk_event'] = GETPOST('fk_event');
					$registration_to_create[$nb]['fk_eventday'] = GETPOST('fk_eventday');
					$registration_to_create[$nb]['fk_levelday'] = $fk_level;

					$nb++;
				}
			}
			else
			{
				$registration_created= array();

				// Pour chaque journée on inscrit dans groupe choisi
				foreach($fk_level as $journee => $groupe)
				{
					for($j=0; $j < count($groupe); $j++)
					{
						$registration_to_create[$nb]['fk_event'] = GETPOST('fk_event');
						$registration_to_create[$nb]['fk_eventday'] = $journee;
						$registration_to_create[$nb]['fk_levelday'] = $groupe[$j];

						$nb++;
					}
				}
			}
			// ADD - Création des inscriptions
			foreach($registration_to_create as $key => $registration_prop)
			{
				$object->fk_event 		= $registration_prop['fk_event'];
				$object->fk_eventday 	= $registration_prop['fk_eventday'];
				$object->fk_levelday 	= $registration_prop['fk_levelday'];

				$extrafields->setOptionalsFromPost($extralabels, $object);

				$result=$object->create($user);
				if ($result > 0)
				{
					$db->commit();
					$nb_create++;
					$registration_created[] = $object->id;
				}
				else
				{
					$db->rollback();

					if ($object->error) $errmsgs[]=$object->error;
					else $errmsgs[]=$object->errors;
					$action = '';
				}
			}

            $registration_valid_after_create = GETPOST('registration_valid_after_create','int');

			// ADD - Validation inscriptions et envoi mail
			if ($registration_valid_after_create == '1')   // We want registration validated
			{
				if(is_array($registration_created) && count($registration_created) > 0)
				{
					$regstat = new Registration($db);
					foreach($registration_created as $key => $registration_id)
					{
						$res = $regstat->fetch($registration_id);
						if($res > 0)
						{
							$regstat->actionmsg2=$langs->transnoentities("EventRegistrationValidated",$object->id);
							$result=$regstat->setValid($user);
							if ($result < 0)
							{
								$error++;
							}
							else
							{
								if ($event_send_email > 0)
								{
									if ($event_send_pdf > 0)
									{
										// Génération du PDF
										require_once("../core/modules/event/modules_event.php");
										require_once("../core/modules/registration/modules_registration.php");

										$langs->load('event@event');
										$result=event_pdf_create($db, $regstat, 'registration', $langs);
										if ($result <= 0)
										{
											$error++;
										}
									}
									// Send PDF
									require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
									require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');
									$contactforaction=new Contact($db);
									$societeforaction=new Societe($db);
									$user_create = new User($db);
									$contact_registered = new Contact($db);
									if ($regstat->fk_soc > 0)    $societeforaction->fetch($regstat->fk_soc);

									if($regstat->fk_user_create > 0)	$user_create->fetch($regstat->fk_user_create);
									if($regstat->fk_user_registered > 0)	$contact_registered->fetch($regstat->fk_user_registered);
									$sendtoid = 0;

									// Si c'est un user externe qui a fait l'inscription d'un invité on prend ses infos
									if(
									$user_create->societe_id > 0
									AND ($regstat->fk_user_create != $regstat->fk_user_registered)
									){
										$sendto = $user_create->email;
										$sendto_sms = $user_create->user_mobile;
										//$sendtoid = $user_create->id;
									}
									else // Dans les autres cas on prend les infos du participant
									{
										$sendto = $contact_registered->email;
										$sendto_sms = $contact_registered->phone_mobile;
										$sendtoid = $contact_registered->id;
									}
									$langs->load('event@event');
									$unique_key = $regstat->getValueFrom('event_registration', $regstat->id, 'unique_key');
									$url = DOL_URL_ROOT."/custom/event/registration/confirm_register.php?id=".$regstat->id."&key=".$unique_key;
									if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
										$url2 = '<a href="http://localhost'.$url.'">Lien</a>';
									else
										$url2 = '<a href="'.$conf->global->EVENT_MAIN_URL_REGISTRATION.$url.'">Lien</a>';

									if ($dayid)
									{
										$eventday = new Day($db);
										$eventday->fetch($dayid);
									}
									if ($eventid)
									{
										$event = new Event($db);
										$event->fetch($eventid);
									}

									$sujet=$langs->transnoentities('SendValidRegistration').' '.$eventday->label;

									$substit['__REGREF__'] = $regstat->ref;
	                				$substit['__EVENEMENT__'] = $event->label;
	                				$substit['__JOURNEE__'] = $eventday->label;
						            $substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event, 'day');
						            $substit['__PARTICIPANT__'] = dolGetFirstLastname($contact_registered->firstname, $contact_registered->lastname);
									$substit['__LIEN_VALIDATION__'] = $url2;
									$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;
						            $substit['__TIMESTART__'] = $eventday->time_start;
						            $substit['__TIMEEND__'] = $eventday->time_end;
	            					$url = DOL_URL_ROOT.'/custom/event/doc/'.$dayid;
									if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
										$url2 = 'http://localhost'.$url;
									else
										$url2 = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;
	            					$message.= make_substitutions($object->message, $substit);
									$now=dol_now();

									$result = $regstat->SendByEmail($day->ref, $sendto,$sendtoid,$sujet,$message,'','','');

									if($result) {
										dol_syslog('Envoi du mail de validation : OK');
									}
									else {
										$error++;
										dol_syslog("Echec de l'envoi du mail de validation : ".$regstat->error, LOG_ERR);
									}
								}
							}
						}
					}
				}
			}
			// We want registration activate
			elseif ($registration_valid_after_create == '2')
			{
				if(is_array($registration_created) && count($registration_created) > 0)
				{
					$regstat = new Registration($db);
					foreach($registration_created as $key => $registration_id)
					{
						$res = $regstat->fetch($registration_id);
						if($res > 0)
						{
							$regstat->actionmsg2=$langs->transnoentities("EventRegistrationValidated",$object->id);
							$result=$regstat->setConfirmed('1');
							if ($result < 0)
							{
								$error++;
							}
							else
							{
								if ($event_send_email > 0)
								{
									if ($event_send_pdf > 0)
									{
										// Génération du PDF
										require_once("../core/modules/event/modules_event.php");
										require_once("../core/modules/registration/modules_registration.php");

										$langs->load('event@event');
										$result=event_pdf_create($db, $regstat, 'registration', $langs);
										if ($result <= 0)
										{
											$error++;
										}
									}
									// Send PDF
									require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
									require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');
									$contactforaction=new Contact($db);
									$societeforaction=new Societe($db);
									$user_create = new User($db);
									$contact_registered = new Contact($db);
									if ($regstat->fk_soc > 0)    $societeforaction->fetch($regstat->fk_soc);

									if($regstat->fk_user_create > 0)	$user_create->fetch($regstat->fk_user_create);
									if($regstat->fk_user_registered > 0)	$contact_registered->fetch($regstat->fk_user_registered);
									$sendtoid = 0;

									// Si c'est un user externe qui a fait l'inscription d'un invité on prend ses infos
									if(
									$user_create->societe_id > 0
									AND ($regstat->fk_user_create != $regstat->fk_user_registered)
									){
										$sendto = $user_create->email;
										$sendto_sms = $user_create->user_mobile;
										//$sendtoid = $user_create->id;
									}
									else // Dans les autres cas on prend les infos du participant
									{
										$sendto = $contact_registered->email;
										$sendto_sms = $contact_registered->phone_mobile;
										$sendtoid = $contact_registered->id;
									}
									$langs->load('event@event');

									if ($dayid)
									{
										$eventday = new Day($db);
										$eventday->fetch($dayid);
									}
									if ($eventid)
									{
										$event = new Event($db);
										$event->fetch($eventid);
									}

									$sujet=$conf->global->EVENT_PARTICIPATE_SUJET;

									$substit['__REGREF__'] = $regstat->ref;
	                				$substit['__EVENEMENT__'] = $event->label;
	                				$substit['__JOURNEE__'] = $eventday->label;
						            $substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event, 'day');
						            $substit['__PARTICIPANT__'] = dolGetFirstLastname($contact_registered->firstname, $contact_registered->lastname);
									$substit['__LIEN_VALIDATION__'] = $url2;
									$substit['__LIEN_VALIDATION_IMG__'] = $conf->global->EVENT_MAIN_URL_REGISTRATION.$url;
						            $substit['__TIMESTART__'] = $eventday->time_start;
						            $substit['__TIMEEND__'] = $eventday->time_end;

	            					$sujet= make_substitutions($sujet,$substit);
									$message.= make_substitutions($conf->global->EVENT_PARTICIPATE_MESSAGE, $substit);
									$now=dol_now();

									$result = $regstat->SendByEmail($day->ref, $sendto,$sendtoid,$sujet,$message,($conf->global->EVENT_SEND_PDF=='-1'?'':'1'), '', ($conf->global->EVENT_MANAGE_ICS=='-1'?'':'1'));

									if($result) {
										dol_syslog('Envoi du mail de confirmation : OK');
									}
									else {
										$error++;
										dol_syslog("Echec de l'envoi du mail de validation : ".$regstat->error, LOG_ERR);
									}
								}
							}
						}
					}
				}
			}

			if ($nb_create > 0)
			{
				if($event_send_email > 0 && $error = NULL)
				{
					$errmsgs = 'EventRegSuccesswithMail';
					$action = '';
					setEventMessage($errmsgs);
					Header("Location: list.php?dayid=".$dayid);
				}
				else
					{
					$errmsgs = 'EventRegSuccesswitouthMail';
					$action = '';
					setEventMessage($errmsgs);
					Header("Location: list.php?dayid=".$dayid);
				}
			}
		}
		else {
			$action = '';
		}
	}
	else {
		$action = '';
	}
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

if($action != 'addd') {
	llxHeader('',$langs->trans("RegistrationCreation"),'');

	$form=new Form($db);
	$form_register=new FormRegistration($db);

	dol_htmloutput_mesg($errmsg,$errmsgs);
}
// if(count($registration_created) > 0)
// {
// 	$regstat = new Registration($db);
// 	print '<div class="info"><h2>'.$langs->trans('NewRegistrationsCreated').'</h2>';

// 	print '<ul>';
// 	foreach($registration_created as $key => $registration_id)
// 	{
// 		$regstat->fetch($registration_id);
// 		print '<li>'.$regstat->getNomUrl(1).' - '.$regstat->getLibStatut(2).'</li>';
// 	}
// 	print '<ul>';

// 	print '</div>';
// }

/*
 * Event list
 */
if ( $action == "" && $user->rights->event->write )
{
	// On veut s'inscrire soit pour un évènement soit pout une journée
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

		$form_register->datec = $eventday->date_event;
		$form_register->fk_user_create = $user->id;

		$form_register->withsocid =1;
		$form_register->fk_soc = $fk_soc;

		$form_register->param = array(
				'fk_event' => $eventid,
				'fk_eventday' => $dayid,
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

		$form_register->show_form();

}
// else
// {
// 	accessforbidden("",0,0);
// }


?>
