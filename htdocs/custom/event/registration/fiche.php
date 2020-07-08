<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012		JF FERRY			<jfefe@aternatik.fr>
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
 *   	\file       event/registration/fiche.php
 *		\ingroup    event
 *		\brief      Index page of module event for registration
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
require_once("../core/modules/event/modules_event.php");
require_once("../core/modules/registration/modules_registration.php");
require_once("../lib/html.formregistration.class.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("bills");
$langs->load("event@event");

// Get parameters
$id					= GETPOST('id','int');
$dayid				= GETPOST('dayid','int');
$action				= GETPOST('action','alpha',3);
$fk_level			= GETPOST('fk_level','int',3);
$fk_soc				= GETPOST('fk_soc','int')?GETPOST('fk_soc','int'):GETPOST('socid');
$fk_user_registered	= GETPOST('fk_user_registered','int');
$confirm 			= GETPOST('confirm','alpha');

$object=new Registration($db);

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
$hookmanager=new HookManager($db);
$hookmanager->initHooks(array('eventregistration'));

/***************************************************
 * ACTIONS
*
****************************************************/
$parameters=array('socid'=>$socid);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

if ($action == 'confirm_validate' && $confirm == 'yes')
{
	$object->fetch($id);

	$result = $object->setValid($user);
	if ($result <= 0)
	{
		$mesg='<div class="error">'.$object->error.'</div>';
	}
	else
	{
		$action ='';
		$url = $PHP_SELF['_SERVER']."?id=".$id;
		header("Location: ".$url);
	}
}
else if ($action == 'confirm_registration' && $confirm == 'yes')
{
	$object->fetch($id);
	$result = $object->setConfirmed('1');
	if ($result <= 0)
	{
		$mesg='<div class="error">'.$object->error.'</div>';
	}
	else
	{
		$action ='';
		$url = $PHP_SELF['_SERVER']."?id=".$id;
		header("Location: ".$url);
	}
}
else if ($action == 'confirm_set_waiting' && $confirm == 'yes')
{
	$object->fetch($id);

	$result = $object->setWaiting();
	if ($result <= 0)
	{
		$mesg='<div class="error">'.$object->error.'</div>';
	}
	else
	{
		$action ='';
		$url = $PHP_SELF['_SERVER']."?id=".$id;
		header("Location: ".$url);
	}
}
if ($action == 'confirm_cancel' && $confirm == 'yes')
{
	$object->fetch($id);

	$result = $object->setCancelled('1');
	if ($result <= 0)
	{
		$mesg='<div class="error">'.$object->error.'</div>';
	}
	else
		$action ='';
}

// Add registration into invoice
elseif ($action == 'addinfacture' && $user->rights->facture->creer)
{
	$facture = New Facture($db);
	$result=$facture->fetch($_POST["factureid"]);
	if ($result <= 0)
	{
		dol_print_error($db,$facture->error);
		exit;
	}

	$soc = new Societe($db);
	$soc->fetch($facture->socid);
	if ($result <= 0)
	{
		dol_print_error($db,$soc->error);
		exit;
	}

	$registration = new Registration($db);
	$result = $registration->fetch($id,$ref);
	if ($result <= 0)
	{
		dol_print_error($db,$registration->error);
		exit;
	}

	$eventday = new Day($db);
	$result = $eventday->fetch($registration->fk_eventday);
	if ($result <= 0)
	{
		dol_print_error($db,$eventday->error);
		exit;
	}

	$level=new Eventlevel($db);
	$result=$level->fetch($registration->fk_levelday);

	$desc = $langs->trans('RegistrationToDay').' '.dol_print_date($eventday->date_event).' - '.$langs->trans('EventLevel').' '.$level->label;

	$tva_tx = get_default_tva($mysoc, $soc);
	$localtax1_tx= get_localtax($tva_tx, 1, $soc);
	$localtax2_tx= get_localtax($tva_tx, 2, $soc);

	$pu_ht = $registration->total_ht;
	$pu_ttc = $registration->total_ttc;
	$price_base_type = 'HT';

	// On reevalue prix selon taux tva car taux tva transaction peut etre different
	// de ceux du produit par defaut (par exemple si pays different entre vendeur et acheteur).
	if ($tva_tx != $registration->tva_tx)
	{
		if ($price_base_type != 'HT')
		{
			$pu_ht = price2num($pu_ttc / (1 + ($tva_tx/100)), 'MU');
		}
		else
		{
			$pu_ttc = price2num($pu_ht * (1 + ($tva_tx/100)), 'MU');
		}
	}

	$result = $facture->addline(
	  $facture->id,
	  $desc,
	  $pu_ht,
	  $_POST["qty"],
	  $tva_tx,
	  $localtax1_tx,
	  $localtax2_tx,
	  '',
	  $_POST["remise_percent"],
	  '',
	  '',
	  '',
	  '',
	  '',
	  $price_base_type,
	  $pu_ttc
	);



	if ($result > 0)
	{

		$facture->origin_id 	= $registration->id;
		$facture->origin 		= $registration->element;
		$ret = $facture->add_object_linked();

		if (!$error)
		{
			Header("Location: ".DOL_URL_ROOT."/compta/facture.php?facid=".$facture->id);
			exit;
		}
	}
}


// Build doc
elseif ($action == 'builddoc' && $user->rights->event->write)
{
	$object->fetch($id);

	$outputlangs = $langs;
	if (GETPOST('lang_id'))
	{
		$outputlangs = new Translate("",$conf);
		$outputlangs->setDefaultLang(GETPOST('lang_id'));
	}
	$result=event_pdf_create($db, $object, GETPOST('model'), $outputlangs);
	if ($result <= 0)
	{
		dol_print_error($db,$result);
		exit;
	}
	else
	{
		Header ('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id.(empty($conf->global->MAIN_JUMP_TAG)?'':'#builddoc'));
		exit;
	}
}
// Classify "paid"
else if ($action == 'confirm_paid' && $confirm == 'yes' && $user->rights->facture->paiement)
{
	// Mark as paid
	$result = $object->fetch($id);
	if($result > 0) {
		$result = $object->set_paid($user);

		// Confirm registration if asked
		if(GETPOST('valid_confirm')) {
			$result = $object->setConfirmed('1');

		}
		if($result > 0) {
			$action = '';

		}
		else
		{
			$mesgs[]='<div class="error">'.$object->error.'</div>';
		}
	}
}
// Classify "paid"
else if ($action == 'setlevel')
{
	$id = GETPOST('fk_registration','int');
	// Mark as paid
	$result = $object->fetch($id);
	if($result > 0) {
		$result = $object->set_level(GETPOST('level'));
		if($result > 0) {
			$action = '';
			$mesgs[]='<div class="ok">'.$langs->trans('LevelModifiedSuccess').'</div>';
			Header("Location: ".$_SERVER['PHP_SELF']."?id=".$id."");
			exit;
		}
		else
		{
			$mesgs[]='<div class="error">'.$object->error.'</div>';
		}
	}
}
// Classify thirdparty & contact
else if ($action == 'setcontact')
{
	$id = GETPOST('fk_registration','int');
	$result = $object->fetch($id);
	if($result > 0) {
		$contact = new Contact($db);
		$result = $contact->fetch(GETPOST('fk_registered'));
		if($result)
		{
			$result = $object->set_contact(GETPOST('fk_registered'),$contact->socid);
			if($result > 0) {
				$action = '';
				$mesgs[]='<div class="ok">'.$langs->trans('ContactModifiedSuccess').'</div>';
				Header("Location: ".$_SERVER['PHP_SELF']."?id=".$id."");
				exit;
			}
			else
			{
				$mesgs[]='<div class="error">'.$object->error.'</div>';
			}

		}
	}
}
// Classify thirdparty & contact
else if ($action == 'setfk_soc')
{
	$id = GETPOST('fk_registration','int');
	$result = $object->fetch($id);
	if($result > 0) {
		$soc = new Societe($db);
		$result = $soc->fetch(GETPOST('fk_soc'));
		if($result)
		{
			$result = $object->set_contact(GETPOST('fk_registered'),$soc->id);
			if($result > 0) {
				$action = '';
				$mesgs[]='<div class="ok">'.$langs->trans('ContactModifiedSuccess').'</div>';
				Header("Location: ".$_SERVER['PHP_SELF']."?id=".$id."");
				exit;
			}
			else
			{
				$mesgs[]='<div class="error">'.$object->error.'</div>';
			}

		}
	}
}
// Delete registration
else if ($action == 'confirm_delete')
{
	$result = $object->fetch($id);
	if($result > 0) {
		$result = $object->delete($user);
		if($result > 0)
		{
			$action = '';
			setEventMessage($langs->trans('RegistrationDeletedSuccessfully'));
			Header("Location: list.php?dayid=".$object->fk_eventday);
			exit;
		}
		else
		{
			$mesgs[]='<div class="error">'.$object->error.'</div>';
		}
	}
}


/*
 * Add file in email form
*/
if (GETPOST('addfile','alpha'))
{
	require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");

	// Set tmp user directory TODO Use a dedicated directory for temp mails files
	$vardir=$conf->user->dir_output."/".$user->id;
	$upload_dir_tmp = $vardir.'/temp';

	$mesg=dol_add_file_process($upload_dir_tmp,0,0);

	$action='presend';
}

/*
 * Remove file in email form
*/
if (GETPOST('removedfile','alpha'))
{
	require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");

	// Set tmp user directory
	$vardir=$conf->user->dir_output."/".$user->id;
	$upload_dir_tmp = $vardir.'/temp';

	$mesg=dol_remove_file_process(GETPOST('removedfile','alpha'),0);

	$action='presend';
}

/*
 * Send mail
*/
if ($action == 'send' && ! GETPOST('cancel','alpha') && (empty($conf->global->MAIN_USE_ADVANCED_PERMS) || $user->rights->ficheinter->ficheinter_advance->send))
{
	$langs->load('mails');

	if ($object->fetch($id) > 0)
	{

		$object->fetch_thirdparty();

		if (GETPOST('sendto','alpha'))
		{
			// Le destinataire a ete fourni via le champ libre
			$sendto = GETPOST('sendto','alpha');
			$sendtoid = 0;
		}
		elseif (GETPOST('receiver','alpha') != '-1')
		{
			// Recipient was provided from combo list
			if (GETPOST('receiver','alpha') == 'thirdparty') // Id of third party
			{
				$sendto = $object->client->email;
				$sendtoid = 0;
			}
			else    // Id du contact
			{
				$sendto  = $object->thirdparty->contact_get_property(GETPOST('receiver'),'email');
				$sendtoid = GETPOST('receiver','alpha');
			}
		}

		if (strlen(GETPOST('subject','alpha'))) $sujet = GETPOST('subject','alpha');
		else $sujet = $langs->transnoentities('RegistrationSentByMail').' '.$object->ref;



		if (dol_strlen($sendto))
		{
			$message = GETPOST('message','alpha');
			$url = DOL_URL_ROOT."/custom/event/registration/confirm_register.php?id=".$object->id."&key=".$object->getValueFrom('event_registration', $object->id, 'unique_key');
			if (!$conf->global->EVENT_MAIN_URL_REGISTRATION)
				$url2 = '<a href="http://localhost'.$url.'">Lien</a>';
			else
				$url2 = '<a href="'.$conf->global->EVENT_MAIN_URL_REGISTRATION.$url.'">Lien</a>';
			$eventday = new Day($db);
			$eventday->fetch($object->getValueFrom('event_registration', $object->id, 'fk_eventday'));
			$event = new Event($db);
			$event->fetch($object->getValueFrom('event_registration', $object->id, 'fk_event'));
			$contact_registered = new Contact($db);
			$contact_registered->fetch($object->getValueFrom('event_registration', $object->id, 'fk_user_registered'));
			$substit['__REGREF__'] = $object->ref;
            $substit['__EVENEMENT__'] = $event->label;
            $substit['__JOURNEE__'] = $eventday->label;
			$substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event, 'day');
			$substit['__PARTICIPANT__'] = dolGetFirstLastname($contact_registered->firstname, $contact_registered->lastname);
			$substit['__LIEN_VALIDATION__'] = $url2;
	        $message = make_substitutions($message, $substit);
			if($object->SendByEmail($eventday->ref, $sendto,$sendtoid,$sujet,$message))
			{
					$mesg='<div class="ok">'.$langs->trans('MailSuccessfulySent',$user->email,$sendto).'.</div>';
					$error=0;

					// Redirect here
					// This avoid sending mail twice if going out and then back to page
					Header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id.'&msg='.urlencode($mesg));
					exit;
			}
			else
			{
				$langs->load("other");
				$mesg='<div class="error">';
				if ($mailfile->error)
				{
					$mesg.=$langs->trans('ErrorFailedToSendMail',$from,$sendto);
					$mesg.='<br>'.$mailfile->error;
				}
				else
				{
					$mesg.='No mail sent. Feature is disabled by option MAIN_DISABLE_ALL_MAILS';
				}
				$mesg.='</div>';
			}
		}
		else
		{
			$langs->load("other");
			$mesg='<div class="error">'.$langs->trans('ErrorMailRecipientIsEmpty').' !</div>';
			dol_syslog('Recipient email is empty');
		}
	}
	else
	{
		$langs->load("other");
		$mesg='<div class="error">'.$langs->trans('ErrorFailedToReadEntity',$langs->trans("Intervention")).'</div>';
		dol_syslog('Impossible de lire les donnees de l\'inscription. Le fichier inscription n\'a peut-etre pas ete genere.');
	}

	$action='presend';
}



/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("RegistrationGestion"),'');

$form=new Form($db);
$formfile=new FormFile($db);
$form_register=new FormRegistration($db);

$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label('event_registration');

dol_htmloutput_mesg($errmsg,$errmsgs);

/*
 * Event list
 */
if ($user->rights->event->read)
{

	if ($id && $action != 'edit')
	{
		dol_htmloutput_mesg($mesg);

		// Confirmation validation
		if ($action == 'validate')
		{
			$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$id, $langs->trans('ValidateRegistration'), $langs->trans('ConfirmValidateRegistration'), 'confirm_validate','',0,1);
			if ($ret == 'html') print '<br>';
		}

		// Confirm registration
		if ($action == 'confirm')
		{
			$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$id, $langs->trans('ConfirmRegistration'), $langs->trans('ConfirmRegistrationMsg'), 'confirm_registration','',0,1);
			if ($ret == 'html') print '<br>';
		}

		// Confirmation delete
		if ($action == 'delete')
		{
			$ret=$form->form_confirm($_SERVER["PHP_SELF"]."?id=".$id,$langs->trans("DeleteRegistration"),$langs->trans("ConfirmDeleteRegistration"),"confirm_delete",'','',1);
			if ($ret == 'html') print '<br>';
		}

		// Confirmation set waiting
		if ($action == 'set_waiting')
		{
			$ret=$form->form_confirm($_SERVER["PHP_SELF"]."?id=".$id,$langs->trans("SetWaiting"),$langs->trans("ConfirmSetWaiting"),"confirm_set_waiting",'','',1);
			if ($ret == 'html') print '<br>';
		}

		// Confirmation du classement paye
		if ($action == 'mark_as_paid')
		{
			$object->fetch($id);
			if($object->fk_statut != '4')
				$formquestion = array(array('label'=> 'Confirmer ?', 'name'=>'valid_confirm', 'type'=> 'checkbox', 'value'=>'1' ));
			$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$id,$langs->trans('ClassifyPaid'),$langs->trans('ConfirmClassifyPaidRegistration',$object->ref),'confirm_paid',$formquestion,'',1);
			if ($ret == 'html') print '<br>';
		}

		// Confirmation cancel
		if ($action == 'registration_cancel')
		{
			$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$id, $langs->trans('CancelRegistration'), $langs->trans('ConfirmCancelRegistration'), 'confirm_cancel','',0,1);
		}


		/*
		 *
		 * Fiche inscription
		 *
		 */

		$res=$object->fetch($id);
		if ($res < 0) {
			dol_print_error($db,$object->error); exit;
		}
		$res=$object->fetch_optionals($object->id,$extralabels);
		if ($res < 0) {
			dol_print_error($db); exit;
		}

		/*
		 * Affichage onglets
		*/
		$eventday=new Day($db);
		$result=$eventday->fetch($object->fk_eventday);
		$head = eventday_prepare_head($eventday);
		dol_fiche_head($head, 'registration', $langs->trans("RegistrationTicket"), 0, 'user');

		if($conf->global->EVENT_LEVEL_REQUIRED && !$object->fk_levelday)
		{
			$errmsg='<div class="error">'.$langs->trans('NoLevelOnThisRegistration').'</div>';
		}

		dol_htmloutput_errors($errmsg,$errmsgs);


		print '<table class="border" width="100%">';

		print '<tr class="liste_titre liste_titre_napf">';
		print '<td class="liste_titre" colspan="2"><strong>';
		print $langs->trans('RegistrationInfos');
		print '</strong></td>';
		print '</tr>';

		// Réf
		if($object->ref != '')
		{
			print '<tr><td width="30%">'.$langs->trans("Ref").'</td>';
			print '<td class="valeur">';
			print $object->getNomUrl(1);
			print '</td>';
			print '</tr>';
		}

		// Evénement
		if($eventday->fk_event)
		{
			$eventstat=new Event($db);
			$eventstat->fetch($eventday->fk_event);
			print '<tr><td width="30%">'.$langs->trans("Event").'</td>';
			print '<td class="valeur">';
			print $eventstat->getNomUrl(1).' '.$eventstat->label;
			print '</td>';
			print '</tr>';

		}


		// Journée
		print '<tr><td width="30%">'.$langs->trans("EventDay").'</td>';
		print '<td class="valeur">';
		print $eventday->getNomUrl(1);
		print ' - '.dol_print_date($eventday->date_event,'daytext');
		print ' <a href="list.php?dayid='.$eventday->id.'"><small>'.$langs->trans('RegistrationsForThisDay').'</small></a>';
		print '</td>';
		print '</tr>';

		if($conf->global->EVENT_HIDE_GROUP=='-1')
			{
			// Level
			print '<tr><td width="30%">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans("EventLevel");
			print '</td>';
			if ($action != 'edit_level') print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=edit_level&amp;id='.$object->id.'">'.img_edit($langs->trans('SetLinkToThirdParty'),1).'</a></td>';
			print '</tr></table>';
			print '</td>';
			print '<td class="valeur">';
			if($object->fk_levelday && !$action)
			{
				$level=new Eventlevel($db);
				$result=$level->fetch($object->fk_levelday);
				print $level->label;

			}
			else if(!$object->fk_levelday || $action == 'edit_level' )
			{
				$level=new Eventlevel($db);
				$form_register->fk_eventday = $dayid;
				$level->fk_registration = $id;
				$htmlname='level';
				print $level->print_select_level($eventday->id,$object->fk_levelday,$htmlname,1);

			}
			print '</td>';
			print '</tr>';
		}

		// Statut
		print '<tr><td>'.$langs->trans("Statut").'</td><td class="valeur">'.$object->getLibStatut(4).'&nbsp;</td>';
		print '</tr>';

		// Paid
		$sql = "SELECT total_ht, price_day FROM llx_event WHERE rowid = ".$object->id;
		$resql = $db->query($sql);
		$res = $resql->fetch_assoc();
		if ($res['total_ht'] != 0)
		{
			print '<tr><td>'.$langs->trans("Paid").'</td><td class="valeur">';
			$paid = $object->paye > 0 ? "on":"off";
			$trans_paid = $object->paye > 0 ? "AlreadyPaid":"BillStatusNotPaid";
			print img_picto($langs->trans($trans_paid),$paid);
			print '&nbsp;'.$langs->trans($object->paye?'Yes':'No').'&nbsp;</td>';
			print '</tr>';
		}

		// Date
		print '<tr><td>'.$langs->trans("RegistrationDate").'</td><td class="valeur">'.dol_print_date($object->datec,'dayhour').'&nbsp;</td>';
		print '</tr>';


		// User who made registration
		print '<tr><td>'.$langs->trans("ContactWhoMadeRegistration").'</td>';
		if (!class_exists('User'))
			require_once DOL_DOCUMENT_ROT.'/user/class/user.class.php';
		$userstat = new User($db);
		$userstat->fetch($object->fk_user_create);
		print '<td class="valeur">'.$userstat->getNomUrl(1).'&nbsp;</td>';
		print '</tr>';


		if($object->fk_statut > 0)
		{
			// Date valid
			print '<tr><td>'.$langs->trans("DateValid").'</td><td class="valeur">'.dol_print_date($object->date_valid,'dayhour').'&nbsp;</td>';
			print '</tr>';

			// User who valid registration
			print '<tr><td>'.$langs->trans("ContactWhoValidRegistration").'</td>';
			$userstat->fetch($object->fk_user_valid);
			print '<td class="valeur">'.$userstat->getNomUrl(1).'&nbsp;</td>';
			print '</tr>';
		}

        // LINK PAGE CONFIRM
        print '<tr><td>'.$langs->trans('EventPageLink').'</td>';
        print '<td><a href="../registration/confirm_register.php?id='.$object->id.'&key='.$object->getValueFrom('event_registration', $object->id, 'unique_key').'" target="_blank">Lien</a></td></tr>';

        // Notes (must be a textarea and not html must be allowed (used in list view)
		print '<tr><td valign="top">';
		print $form->editfieldkey("NotePublic",'note_public',$object->note_public,$object,$user->rights->event->write,'textarea');
		print '</td><td colspan="3">';
		print $form->editfieldval("NotePublic",'note_public',$object->note_public,$object,$user->rights->event->write,'textarea');
		print '</td>';
		print '</tr>';

		print '<tr><td valign="top">';
		print $form->editfieldkey("NotePrivate",'note_private',$object->note_private,$object,$user->rights->event->write,'textarea');
		print '</td><td colspan="3">';
		print $form->editfieldval("NotePrivate",'note_private',$object->note_private,$object,$user->rights->event->write,'textarea');
		print '</td>';
		print '</tr>';


		print '<tr class="liste_titre liste_titre_napf">';
		print '<td class="liste_titre" colspan="2"><strong>';
		print $langs->trans('UserRegistrationInfos');
		print '</strong></td>';
		print '</tr>';

		// Third party Dolibarr
		if ($conf->societe->enabled)
		{
			print '<tr><td>';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans("LinkedToDolibarrThirdParty");
			print '</td>';
			if ($_GET['action'] != 'editthirdparty' && $user->rights->societe->creer) print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=editthirdparty&amp;id='.$object->id.'">'.img_edit($langs->trans('SetLinkToThirdParty'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="2" class="valeur">';
			if ($_GET['action'] == 'editthirdparty')
			{
				$htmlname='fk_soc';
				//print '<table class="nobordernopadding" width="100%"><tr><td>';
				print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form'.$htmlname.'">';
				print '<input type="hidden" name="fk_registration" value="'.$object->id.'">';
				print '<input type="hidden" name="action" value="set'.$htmlname.'">';
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
				print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
				print '<tr><td>';
				print  $form->select_company($object->fk_soc,'fk_soc','',1);
				print '</td>';
				print '<td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
				print '</tr></table></form>';
			}
			else
			{
				if ($object->fk_soc)
				{
					$company=new Societe($db);
					$result=$company->fetch($object->fk_soc);
					print $company->getNomUrl(1);
				}
				else
				{
					print $langs->trans("NoThirdPartyAssociatedToRegistration");
				}
			}
			print '</td></tr>';
		}

		// User registered
		print '<tr><td>';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans("Contact");
		if ($_GET['action'] != 'editcontact' && $user->rights->societe->creer) print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=editcontact&amp;id='.$object->id.'">'.img_edit($langs->trans('SetLinkToContact'),1).'</a></td>';
		print '</tr></table>';
		print '</td>';
		if($object->fk_user_registered > 0 && $action != 'editcontact')
		{
			$contactstat=new Contact($db);
			$res = $contactstat->fetch($object->fk_user_registered);
			if($res > 0 || !$action)
			{
				print '<td class="valeur">'.$contactstat->getNomUrl(1).'&nbsp;</td>';
			}
		}
		else		{
			print '<td>';
			print '<table class="nobordernopadding" width="100%">';
			$htmlname='contact';
			print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form'.$htmlname.'">';
			print '<input type="hidden" name="fk_registration" value="'.$object->id.'">';
			print '<input type="hidden" name="action" value="set'.$htmlname.'">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
			$ret =  $form->select_contacts($object->fk_soc,$object->fk_user_registered,'fk_registered','','','','','',1);
			print '</td>';
			print '<td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
			print '</tr></table></form>';
			print '</td>';
		}
		print '</tr>';


		// Other attributes
		$parameters=array();
		$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
		if (empty($reshook) && ! empty($extrafields->attribute_label))
		{
			foreach($extrafields->attribute_label as $key=>$label)
			{
				$value=$object->array_options["options_$key"];
				print "<tr><td>".$label."</td><td>";
				print $extrafields->showOutputField($key,$value);
				print "</td></tr>\n";
			}
		}
		print "</table>\n";
		print "</div>\n";

		/*
		 * Barre d'actions
		*
		*/
		print '<div class="tabsAction">';

		// Validate && mark as waiting if draft
		if ($object->fk_statut == '0' || $object->fk_statut == '5')
		{
			// print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=validate">'.$langs->trans("MarkAsValid").'</a>';
			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm">'.$langs->trans("ValidInvitation").'</a>';
			$show_link_waiting=true;
		}

		if ($object->fk_statut != '5') print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=registration_cancel">'.$langs->trans("CancelledInvitation").'</a>';

		// Confirm if valid
		if ($object->fk_statut == '1') {
			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm">'.$langs->trans("ValidInvitation").'</a>';

			if ($conf->facture->enabled && $user->rights->facture->creer)
			{
				$sql = "SELECT total_ht, price_day FROM llx_event WHERE rowid = ".$object->id;
				$resql = $db->query($sql);
				$res = $resql->fetch_assoc();
				if ($res['total_ht'] != 0)
					print '<a class="butAction" href="'.dol_buildpath('/compta/facture.php',1).'?action=create&amp;origin=event_registration&amp;originid='.$object->id.'">'.$langs->trans("CreateBill").'</a>';
			}
			$show_link_paid=true;
			$show_link_waiting=true;
		}

		// Confirmed
		if ($object->fk_statut == '4')
		{
			$show_link_paid=true;
		}

		if ($object->fk_statut == '8') // Confirm if waiting
		{
			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm">'.$langs->trans("Confirm").'</a>';
		}

		// Send
		if ($object->fk_statut >= 1)
		{

				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=presend&amp;mode=init">'.$langs->trans('SendByMail').'</a>';

		}

		if ($show_link_paid)
		{
			if (!$object->paye)
			{
				$sql = "SELECT total_ht, price_day FROM llx_event WHERE rowid = ".$object->id;
				$resql = $db->query($sql);
				$res = $resql->fetch_assoc();
				if ($res['total_ht'] != 0)
				{
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=mark_as_paid">'.$langs->trans("MarkAsPaid").'</a>';
				}
			}
		}
		if ($show_link_waiting)
		{
			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=set_waiting">'.$langs->trans("MarkAsWaiting").'</a>';
		}

		print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete">'.$langs->trans("Delete").'</a>';

		print '</div>';


		/*
		 *  Affichage des documents si statut non brouillon
		*/
		if ($_GET['action'] != 'presend')
		{

			if($object->fk_statut > 0 && !empty($object->ref))
			{
				$formfile = new FormFile($db);

				print '<table width="100%"><tr><td width="50%" valign="top">';
				print '<a name="builddoc"></a>'; // ancre


				/*
				 * Documents generes
				*/
				$filename=dol_sanitizeFileName($object->ref);
				$filedir=$conf->event->dir_output . "/" . dol_sanitizeFileName($object->ref);

				$urlsource=$_SERVER["PHP_SELF"]."?id=".$object->id;
				$genallowed=$user->rights->event->write;
				$delallowed=$user->rights->event->delete;

				$var=true;
				$file = $object->ics_create();
				include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/event.class.php");
				$event = new Event($db);
				$event->fetch($object->fk_event);
//				rename($file, $filedir.'/'.$event->ref.'.ics');
				$somethingshown=$formfile->show_documents('event',$object->ref,$filedir,$urlsource,$genallowed,$delallowed,'',0,0,0,28,0,'',0,'',$object->default_lang);
				print '</td><td valign="top" width="50%">';
				print '</td></tr></table>';
			}

		/*	print '<table class="centpercent notopnoleftnoright" style="margin-bottom: 2px;">';
			print '<tbody>';
				print '<tr>';
				print '<td class="nobordernopadding" valign="middle">';
				print '<div class="titre">Fichier ICS</div>';
				print '</td>';
				print '</tr>';
			print '</tbody>'; //titre
			print '<table class="liste formdoc noborder">';
			print '<tbody>';
			print '<tr class="liste_titre"></tr>';
			print '</tbody>';
			print '</table>';//ics file table
			print '</table>'; //table ics file*/
			// List of actions on element
			include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php');
			$formactions=new FormActions($db);
			$somethingshown=$formactions->showactions($object,'event_registration',$socid);





			/*
			 * Linked object block
			*/
			$somethingshown=$object->showLinkedObjectBlock();


			$sql = "SELECT total_ht, price_day FROM llx_event WHERE rowid = ".$object->id;
			$resql = $db->query($sql);
			$res = $resql->fetch_assoc();
			if ($res['total_ht'] != 0)
			{
				/*
				 * Liste des factures pour ajout si inscription non confirmée
				*/
				if($object->fk_statut != 0 && $object->fk_statut < 4)
				{
					// Factures
					if ($conf->facture->enabled && $user->rights->facture->creer)
					{
						$langs->load('bill');
						print '<br /><table width="100%" class="noborder">';
						print '<tr class="liste_titre liste_titre_napf"><td width="50%" class="liste_titre">';
						print $langs->trans("AddToMyBills").'</td>';
						if ($user->rights->societe->client->voir)
						{
							print '<td width="50%" class="liste_titre">';
							print $langs->trans("AddToOtherBills").'</td>';
						}
						else
						{
							print '<td width="50%" class="liste_titre">&nbsp;</td>';
						}

						print '</tr>';

						// Liste de Mes factures
						print '<tr><td'.($user->rights->societe->client->voir?' width="50%"':'').' valign="top">';

						$sql = "SELECT s.nom, s.rowid as socid, f.rowid as factureid, f.facnumber, f.datef as df";
						$sql.= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture as f";
						$sql.= " WHERE f.fk_soc = s.rowid";
						$sql.= " AND f.entity = ".$conf->entity;
						$sql.= " AND f.fk_statut = 0";
						$sql.= " AND f.fk_user_author = ".$user->id;
						$sql.= " ORDER BY f.datec DESC, f.rowid DESC";

						$result=$db->query($sql);
						if ($result)
						{
							$num = $db->num_rows($result);
							$var=true;
							print '<table class="nobordernopadding" width="100%">';
							if ($num)
							{
								$i = 0;
								while ($i < $num)
								{
									$objp = $db->fetch_object($result);
									$var=!$var;
									print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
									print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
									print '<input type="hidden" name="action" value="addinfacture">';
									print "<tr $bc[$var]>";
									print "<td nowrap>";
									print "<a href=\"../compta/facture.php?facid=".$objp->factureid."\">".img_object($langs->trans("ShowBills"),"bill")." ".$objp->facnumber."</a></td>\n";
									print "<td><a href=\"../comm/fiche.php?socid=".$objp->socid."\">".dol_trunc($objp->nom,18)."</a></td>\n";
									print "<td nowrap=\"nowrap\">".dol_print_date($db->jdate($objp->df),"%d %b")."</td>\n";
									print '<td><input type="hidden" name="factureid" value="'.$objp->factureid.'">';
									print '<input type="text" class="flat" name="qty" size="1" value="1"></td><td nowrap>'.$langs->trans("ReductionShort");
									print '<input type="text" class="flat" name="remise_percent" size="1" value="0">%';
									print '</td><td align="right">';
									print '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
									print '</td>';
									print '</tr>';
									print '</form>';
									$i++;
								}
							}
							else {
								print "<tr ".$bc[!$var]."><td>";
								print $langs->trans("NoDraftBills");
								print '</td></tr>';
							}
							print "</table>";
							$db->free($result);
						}
						else
						{
							dol_print_error($db);
						}

						print '</td>';

						if ($user->rights->societe->client->voir)
						{

							$facture = new Facture($db);

							print '<td width="50%" valign="top">';

							// Liste de Autres factures
							$var=true;

							$sql = "SELECT s.nom, s.rowid as socid, f.rowid as factureid, f.facnumber, f.datef as df";
							$sql.= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture as f";
							$sql.= " WHERE f.fk_soc = s.rowid";
							$sql.= " AND f.entity = ".$conf->entity;
							$sql.= " AND f.fk_statut = 0";
							$sql.= " AND f.fk_user_author <> ".$user->id;
							$sql.= " ORDER BY f.datec DESC, f.rowid DESC";

							$result=$db->query($sql);
							if ($result)
							{
								$num = $db->num_rows($result);
								$var=true;
								print '<table class="nobordernopadding" width="100%">';
								if ($num)
								{
									$i = 0;
									while ($i < $num)
									{
										$objp = $db->fetch_object($result);

										$var=!$var;
										print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
										print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
										print '<input type="hidden" name="action" value="addinfacture">';
										print "<tr ".$bc[$var].">";
										print "<td><a href=\"../compta/facture.php?facid=".$objp->factureid."\">$objp->facnumber</a></td>\n";
										print "<td><a href=\"../comm/fiche.php?socid=".$objp->socid."\">".dol_trunc($objp->nom,24)."</a></td>\n";
										print "<td colspan=\"2\">".$langs->trans("Qty");
										print "</td>";
										print '<td><input type="hidden" name="factureid" value="'.$objp->factureid.'">';
										print '<input type="text" class="flat" name="qty" size="1" value="1"></td><td nowrap>'.$langs->trans("ReductionShort");
										print '<input type="text" class="flat" name="remise_percent" size="1" value="0">%';
										print '</td><td align="right">';
										print '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
										print '</td>';
										print '</tr>';
										print '</form>';
										$i++;
									}
								}
								else
								{
									print "<tr ".$bc[!$var]."><td>";
									print $langs->trans("NoOtherDraftBills");
									print '</td></tr>';
								}
								print "</table>";
								$db->free($result);
							}
							else
							{
								dol_print_error($db);
							}
							print '</td>';
						}
						print '</tr>';
					}
					print '</table>';
				}
			}
		}

		/*
		 * Action presend
		*/
		if ($action == 'presend')
		{
			$ref = dol_sanitizeFileName($object->ref);
			include_once(DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php');
			$fileparams = dol_most_recent_file($conf->event->dir_output . '/' . $ref);
			$file=$fileparams['fullname'];

			// Build document if it not exists
			if (! $file || ! is_readable($file))
			{
				// Define output language
				$outputlangs = $langs;
				$newlang='';
				if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id'])) $newlang=$_REQUEST['lang_id'];
				if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang=$object->client->default_lang;
				if (! empty($newlang))
				{
					$outputlangs = new Translate("",$conf);
					$outputlangs->setDefaultLang($newlang);
				}

				$result=registration_pdf_create($db, $object, GETPOST('model')?GETPOST('model'):$object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref, $hookmanager);
				if ($result <= 0)
				{
					dol_print_error($db,$result);
					exit;
				}
				$fileparams = dol_most_recent_file($conf->event->dir_output . '/registration/' . $ref);
				$file=$fileparams['fullname'];
			}

			print '<br>';
			print_titre($langs->trans('SendRegistrationByMail'));

			// Create form object
			include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php');
			$contact_registered = new Contact($db);
			$contact_registered->fetch($object->getValueFrom('event_registration', $object->id, 'fk_user_registered'));
			$formmail = new FormMail($db);
			$formmail->fromtype = 'user';
			$formmail->fromid   = $user->id;
			$formmail->fromname = $user->getFullName($langs);
			$formmail->frommail = $user->email;
			$formmail->withfrom=1;
			$formmail->withto=(!GETPOST('sendto','alpha'))?$contact_registered->email:GETPOST('sendto','alpha');
			$formmail->withtosocid=$object->socid;
			$formmail->withtocc=1;
			$formmail->withtoccsocid=0;
			$formmail->withtoccc=$conf->global->MAIN_EMAIL_USECCC;
			$formmail->withtocccsocid=0;
			$event = new Event($db);
			$event->fetch($object->getValueFrom('event_registration', $object->id, 'fk_event'));
			$formmail->withtopic=$langs->trans('SendRegistrationRef',$event->label);
			$formmail->withfile=2;
			$formmail->withbody=$langs->trans('PredefinedMailContentSendRegistration','',dol_print_date($eventday->date_event,'daytext')).'<br><br>'.$langs->trans('SendValidRegistrationBodyPrint');
			$formmail->withbody.='<br />'.$langs->trans('DayDescription');
			$formmail->withbody.='<br />'.$eventday->description;;
			$formmail->withdeliveryreceipt=1;
			$formmail->withcancel=1;

			// Tableau des substitutions
			//$formmail->substit['__REGISTRATIONREF__']=$object->ref;
			$formmail->substit['__SIGNATURE__']=$user->signature;
			$formmail->substit['__PERSONALIZED__']='';
			// Tableau des parametres complementaires
			$formmail->param['action']='send';
			$formmail->param['models']='registration_send';
			$formmail->param['registration_id']=$object->id;
			$formmail->param['returnurl']=$_SERVER["PHP_SELF"].'?id='.$object->id;

			// Init list of files
			if (GETPOST("mode")=='init')
			{
				$formmail->clear_attached_files();
				if($conf->global->EVENT_SEND_PDF != "-1") $formmail->add_attached_files($file,basename($file),dol_mimetype($file));
			}

			$formmail->show_form();

			print '<br>';
		}
	}
}


// End of page
llxFooter();
$db->close();
