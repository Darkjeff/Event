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

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');	// If there is no menu to show
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');	// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');		// If this page is public (can be called outside logged session)

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
require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");

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



/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$help_url='EN:Module_Event_/_Booking_EN|FR:Module_Évènements_et_inscriptions|ES:Module_Event_/_Booking_ES';
llxHeader('',$langs->trans("RegistrationOrdersState"),$help_url);

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
		print_fiche_titre($langs->trans('RegistrationOrdersState'),'','event@event').'<br />';

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
			dol_fiche_head($head, 'registration_orders', $langs->trans("EventSingular"),0,($eventstat->public?'event@event':'event@event'));

			print '<table class="border" width="100%" >';

			// Ref
			print '<tr><td width="20%">'.$langs->trans("Ref").'</td><td>';
			print $form->showrefnav($eventstat,'ref_event','',1,'ref','ref');
			print '</td></tr>';
			
			// Label
			print '<tr><td valign="top">';
			print 'Label';
			print '</td><td>';
			print $eventstat->label;
			print "</td></tr>";

			// Third party
			if ($eventstat->socid > 0)
			{
				$soc = new Societe($db);
				$soc->fetch($eventstat->socid);
				print '<tr><td>'.$langs->trans("Company").'</td><td>';
				print $soc->getNomUrl(1);
				print '</td></tr>';
			}

			// Start date
			print '<tr><td>';
			print $langs->trans("DateStart");
			print '</td><td>';
			print dol_print_date($eventstat->date_start,'daytext');
			print '</td></tr>';

			// End date
			print '<tr><td>';
			print $langs->trans("DateEnd");
			print '</td><td>';
			print dol_print_date($eventstat->date_end,'daytext');
			print '</td></tr>';

			// Statut
			print '<tr><td>'.$langs->trans("Status").'</td><td>'.$eventstat->getLibStatut(4).'</td></tr>';

			print '</table>';


			print dol_fiche_end();


			$thirdparties = $eventstat->getThirdpartiesForEvent();
			
			if(is_array($thirdparties) && count($thirdparties) > 0)
			{
				//svar_dump($thirdparties);
				// Pour chaque tiers, liste des commandes
				foreach($thirdparties as $socid => $socname)
				{
					print '<h1 style="margin-top: 15px; font-size: 1.3em;">'.$socname.'</h1>';
					
					$registration->fetchRegistrationForThirdparty($eventid,$socid);

					if(is_array($registration->lines) && count($registration->lines) > 0)
					{
						//var_dump($registration->lines);
						print '<table class="border ">';

						print '<tr class="liste_titre">';
						print '<th>'.$langs->trans('RegisteredContact').'</th>';
						print '<th>'.$langs->trans('Status').'</th>';
						print '<th>'.$langs->trans('RegistrationAlreadyPaid').'</th>';
						print '<th>'.$langs->trans('Ref').'</th>';
						print '<th>'.$langs->trans('EventDay').'</th>';
						print '<th>'.$langs->trans('Options').'</th>';
						print '<th>'.$langs->trans('Notes').'</th>';
						print '</tr>';
						foreach($registration->lines as $registration_line)
						{
							print '<tr>';
							
							$contactstatic = new Contact($db);
							$contactstatic->fetch($registration_line->fk_user_registered);
							print '<td>'.$contactstatic->getNomUrl(0).'</td>';

							print '<td>'.$registration_line->getLibStatut(0).'</td>';

							// Paiement
							print '<td'.$border_style.' width="5%">';
							$paid = $registration_line->paye > 0 ? "on":"off";
							$trans_paid = $registration_line->paye > 0 ? "AlreadyPaid":"RegistrationStatusNotPaid";
							print img_picto($langs->trans($trans_paid),$paid);
							print '</td>';

							print '<td>'.$registration_line->ref.'</td>';

							

							// Date Journée
							print '<td>'.dol_print_date($registration_line->date_event,'day').'</td>';

							print '<td width="35%">';
							$registration_orders = $registration_line->getOrderForRegistration($registration_line->id);
							if(is_array($registration_orders) && count($registration_orders) > 0)
							{
								foreach ($registration_orders as $order_id => $order_ref)
								{
									$orderstat = new Commande($db);
									$orderstat->fetch($order_id);
									print $orderstat->getLibStatut(3) . ' ' . $orderstat->getNomUrl(0) .' ('.price($orderstat->total_ttc).$conf->currency.')<br />';
									foreach($orderstat->lines as $order_line)
									{
										print $order_line->ref . ' '.$order_line->libelle.' : '.price($order_line->total_ttc).'<br />';
									}
								}
							}								
							print '</td>';

							print '<td width="15%">'.$registration_line->note_private.'</td>';

							print '</tr>';
						}
						
						print '</table>';
					
					}
					
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

