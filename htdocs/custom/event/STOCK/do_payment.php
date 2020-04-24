<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012-2016		JF FERRY			<jfefe@aternatik.fr>
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
 *   	\file       event/do_payment.php
 *		\ingroup    event
 *		\brief      Page to mak payment on registration
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) { $res=include("../main.inc.php"); }
if (! $res && file_exists("../../main.inc.php")) { $res=include("../../main.inc.php"); }// for curstom directory
if (! $res) { die("Include of main fails"); }

// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
require_once("class/event.class.php");
require_once("class/registration.class.php");
require_once("class/html.formevent.class.php");
require_once("core/modules/event/modules_event.php");

require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");

dol_include_once("/formstyler/class/formstyler.class.php");

require_once("lib/event.lib.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("error");
$langs->load("event@event");
$langs->load('bills');


// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$socid		= GETPOST('socid','int');
$eventid	= GETPOST('eventid','int');
$ref_event = GETPOST('ref_event','alpha');

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}


if($action == 'search')
{
	if(empty($query))
	{
		$mesgs[] = '<div class="error">'.$langs->trans('ErrorRefRegistrationMustBeProvided').'</div>';
		$action ='';
	}
}

if ( GETPOST('do_payment') )
{
	$level=new Eventlevel($db);
	$event=new Event($db);
	$eventday=new Day($db);

	$error='';

	$amount 		= GETPOST('amount');
	$comment 		= GETPOST('comment');
	$paiementcode 	= GETPOST('paiementcode');

	if( empty($amount) || empty($paiementcode) )
	{
		$error++;
		setEventMessage('ErrorAmountOrPaiementRequired','errors');
	}

	if(!$error)
	{
		foreach ($_POST['topay'] as $insc )
		{
			$registration = new Registration($db);
			$ret = $registration->fetch($insc);
			if ( $ret )
			{
				// Infos participants
				$contact = new Contact($db);

				$ret = $contact->fetch($registration->fk_user_registered);
				if ( $ret > 0)
				{
					$txt_note = $paiementcode.' '.$amount.'  '.$conf->global->main_currency.' '.dol_print_date(dol_now(),'day').' - '.$comment;
					$result=$registration->update_note(dol_html_entity_decode($txt_note, ENT_QUOTES));
					if ($result < 0)
					{
						dol_print_error($db,$registration->error);
					}
					else
					{
						$result = $registration->set_paid($user);
						$registration->actionmsg2=''; // reset action msg
						if( GETPOST('do_confirmation' == "on"))
						{
							$result = $registration->setConfirmed();
							setEventMessage($langs->trans('RegistrationPaidAndConfirmed',$registration->ref));
						}
					}
				}
				else print 'no contact';
			}
		}
	}
}


/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$help_url='EN:Module_Event_/_Booking_EN|FR:Module_Évènements_et_inscriptions|ES:Module_Event_/_Booking_ES';
llxHeader('',$langs->trans("RegistrationPayments"),$help_url);

$form=new Form($db);
$formother=new FormOther($db);
$object = new Event($db);
$formevent = new FormEvent($db);
$registration = new Registration($db);


$now=dol_now();

/*
 * Event list
 */
if ($user->rights->event->read)
{

	if ( ! $eventid && ! $ref_event )
	{
		/*
		 * Affichage sélecteur des journées
		*/
		print_fiche_titre($langs->trans('RegistrationPayments'),'','event@event').'<br />';

		$eventstat=new Event($db);
		print $formevent->select_event('','eventid',1);

	}
	else
	{
		if($eventid || $ref_event)
		{
			$eventstat = new Event($db);
			$eventstat->fetch($eventid,$ref_event);
			$eventid=$eventstat->id;

			$return = restrictedArea($user, 'event', $eventid, 'event');
			$head = event_prepare_head($eventstat);
			dol_fiche_head($head, 'registration_payment', $langs->trans("EventSingular"),0,($eventstat->public?'event@event':'event@event'));

			print '<table class="border" width="100%" >';

			// Ref
			print '<tr><td width="20%">'.$langs->trans("Ref").'</td><td>';
			print $form->showrefnav($eventstat,'ref_event','',1,'ref','ref');
			print '</td></tr>';

			print '</table>';


			print dol_fiche_end();


			if(!$socid)
			{
				$thirdparties = $eventstat->getThirdpartiesForEvent();
				print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form'.$htmlname.'">';
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
				print '<input type="hidden" name="eventid" value="'.$eventid.'">';
				// select thirdparty
				print '<p><label for="socid">'.$langs->trans('SelectCompany').'</label>';
				print $form->selectarray('socid', $thirdparties);
				print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
				print '</p>';
				print '</form>';

			}
			else
			{
				$registration->fetchRegistrationForThirdparty($eventid,$socid);

				if(is_array($registration->lines) && count($registration->lines) > 0)
				{
					print_titre($langs->trans('MakePaymentOnRegistrations'));


					print '<form method="POST" name="registration" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" class="dol_form">';
					print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
					print '<input type="hidden" name="eventid" value="'.$eventid.'">';
					print '<input type="hidden" name="socid" value="'.$socid.'">';


					// Comment
					$value='Paiement le '.dol_print_date(dol_now(),'daytext');
					FormStyler::printInputField('comment',$langs->trans('CommentOnPayment'),$value,1,'text');

					// Amount
					FormStyler::printInputField('amount',$langs->trans('PaymentAmount'),GETPOST('amount'),1,'','price');

					// Paiements list
					FormStyler::printSelectList('paiementcode',$langs->trans('PaymentMode'),GETPOST('paiementcode'),'',1,'selectpaiementcode','types_paiements');

					FormStyler::printCheckbox( 'do_confirmation', $langs->trans('ConfirmRegistrationAfterPayment'), 'on', false);


					FormStyler::printClearBlock();

					print '<table class="border liste">';

					print '<tr class="liste_titre">';
					print '<th>'.$langs->trans('MarkAsPaid').'</th>';
					print '<th>'.$langs->trans('Status').'</th>';
					print '<th>'.$langs->trans('RegistrationAlreadyPaid').'</th>';
					print '<th>'.$langs->trans('Ref').'</th>';
					print '<th>'.$langs->trans('RegisteredContact').'</th>';
					print '<th width="10%">'.$langs->trans('RegistrationDate').'</th>';
					print '<th width="10%">'.$langs->trans('EventDayDate').'</th>';
					if($conf->global->EVENT_HIDE_GROUP=='-1') print '<th>'.$langs->trans('EventLevel').'</th>';
					print '<th>'.$langs->trans('Notes').'</th>';
					print '</tr>';
					foreach($registration->lines as $registration_line)
					{
						print '<tr>';

						print '<td width="8%"><input type="checkbox" name="topay[]" value="'.$registration_line->id.'" /></td>';

						print '<td>'.$registration_line->getLibStatut(2).'</td>';

						// Paiement
						print '<td'.$border_style.' width="5%">';
						$paid = $registration_line->paye > 0 ? "on":"off";
						$trans_paid = $registration_line->paye > 0 ? "AlreadyPaid":"RegistrationStatusNotPaid";
						print img_picto($langs->trans($trans_paid),$paid);
						print '</td>';

						print '<td>'.$registration_line->getNomUrl(1).'</td>';

						$contactstatic = new Contact($db);
						$contactstatic->fetch($registration_line->fk_user_registered);
						print '<td>'.$contactstatic->getNomUrl(1).'</td>';

						print '<td>'.dol_print_date($registration_line->datec,'dayhour').'</td>';

						// Date Journée
						print '<td>'.dol_print_date($registration_line->date_event,'day').'</td>';

						print '<td width="">'.$registration_line->level_label.'</td>';


						print '<td width="35%">'.$registration_line->note_private.'</td>';

						print '</tr>';
					}

					print '<tr><td align="center" colspan="9"><center>';
					print '<input class="button" type="submit" id="do_payment" name="do_payment" value="'.$langs->trans("RegistrationDoPayement").'"';
					print ' />';
					print ' &nbsp; &nbsp; ';
					print '<input class="button" type="submit" id="cancel" name="cancel" value="'.$langs->trans("Cancel").'" />';
					print '</center></td></tr>'."\n";

					print '</table>';
					print '</form>';

				}
				else {
					print '<div class="info">'.$langs->trans('NoResult').'</div>';
				}
			}
		}
	}

}
else accessforbidden();




// End of page
llxFooter();
$db->close();
?>

