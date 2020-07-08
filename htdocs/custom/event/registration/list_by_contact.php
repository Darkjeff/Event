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
 *   	\file       event/registration/list.php
 *		\ingroup    event
 *		\brief      List of registration for an event day
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");
require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/eventlevel.class.php");
require_once("../class/registration.class.php");
require_once("../lib/event.lib.php");
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/contact.lib.php';

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

// Get parameters
$id			= GETPOST('id','int'); // socpeople_id
$dayid		= GETPOST('dayid','int');
$ref		= GETPOST('ref','alpha');
$action		= GETPOST('action','alpha');
$confirm 	= GETPOST('confirm','alpha');

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("RegistrationList"),'');

$form=new Form($db);
$event = new Event($db);
$eventday = new Day($db);

dol_htmloutput_mesg($mesg,$mesgs);

/*
 * Event list
 */
if ($user->rights->event->read)
{
	if($id)
	{
		$regstat = new Registration($db);

		$object = new Contact($db);
		$object->fetch($id);

		$head = contact_prepare_head($object);

		dol_fiche_head($head, 'registration', $langs->trans("ContactsAddresses"), 0, 'contact');


		/*
		 * Fiche en mode visu
		*/
    
	    // dol_fiche_head($head, 'perso', $title, 0, 'contact');
	    
	    $linkback = '<a href="'.DOL_URL_ROOT.'/contact/list.php">'.$langs->trans("BackToList").'</a>';
	    
	    dol_banner_tab($object, 'id', $linkback, 1, 'rowid', 'ref', '');
	    
	    
	    print '<div class="fichecenter">';
	    
	    print '<div class="underbanner clearboth"></div>';
	    print '<table class="border centpercent">';

	    // Company
	    if (empty($conf->global->SOCIETE_DISABLE_CONTACTS))
	    {
	        if ($object->socid > 0)
	        {
	            $objsoc = new Societe($db);
	            $objsoc->fetch($object->socid);

	            print '<tr><td>'.$langs->trans("ThirdParty").'</td><td colspan="3">'.$objsoc->getNomUrl(1).'</td></tr>';
	        }

	        else
	        {
	            print '<tr><td>'.$langs->trans("ThirdParty").'</td><td colspan="3">';
	            print $langs->trans("ContactNotLinkedToCompany");
	            print '</td></tr>';
	        }
	    }

	    // Civility
	    print '<tr><td class="titlefield">'.$langs->trans("UserTitle").'</td><td colspan="3">';
	    print $object->getCivilityLabel();
	    print '</td></tr>';

        // Role
        print '<tr><td>'.$langs->trans("PostOrFunction").'</td><td>'.$object->poste.'</td></tr>';

		// Categories
		if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)) {
			print '<tr><td class="titlefield">' . $langs->trans("Categories") . '</td>';
			print '<td colspan="3">';
			print $form->showCategories( $object->id, 'contact', 1 );
			print '</td></tr>';
		}

	    print "</table>";

	    print '</div>';

	    dol_fiche_end();

		/*
		 * Liste des inscriptions du contact
		*/
		print_fiche_titre($langs->trans('RegistrationListOfContact'),'','event_registration@event').'<br />';

		$sql_reg = "SELECT r.rowid , r.fk_soc, r.fk_levelday, r.fk_statut, r.fk_event, r.fk_eventday, r.ref, r.datec, r.date_valid, r.fk_user_registered, r.paye";
		$sql_reg.=" ,e.label as event_label, ed.ref as day_ref, ed.date_event as event_date, el.label as event_level_label";
		$sql_reg.=" FROM ".MAIN_DB_PREFIX."event_registration AS r";
		$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."event AS e ON e.rowid=r.fk_event";
		$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."event_day AS ed ON ed.rowid=r.fk_eventday";
		$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."event_level AS el ON el.rowid=r.fk_levelday";
		$sql_reg.=" WHERE r.fk_user_registered = '".$id."'";

		if($object->user_id > 0)
		{
			$sql_reg.=" OR r.fk_user_create = '".$object->user_id."'";
		}

		$resql_reg=$db->query($sql_reg);
		if ($resql_reg) {
			$num2 = $db->num_rows($sql_reg);
			if ($num2) {
				$j=0;
				print "<form method=\"POST\" name=\"registration\" enctype=\"multipart/form-data\" action=\"".$_SERVER['PHP_SELF']."\">\n";
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

				print '<table class="border" width="100%" >';

				print '<tr class="liste_titre">';
				print '<th width="5%">'.$langs->trans('Status').'</th>';
				print '<th width="15%">'.$langs->trans('UserRegistrationInfos').'</th>';
				print '<th width="20%">'.$langs->trans('Event').'</th>';
				print '<th width="20%">'.$langs->trans('EventDay').'</th>';
				print '<th width="10%">'.$langs->trans('Group').'</th>';
				print '<th width="11%">'.$langs->trans('RegistrationDate').'</th>';
				print '<th width="14%">'.$langs->trans('ConfirmationDate').'</th>';
				print '<th width="5%">'.$langs->trans('Paid').'</th>';
				print '<th width="5%">'.$langs->trans('Edit').'</th>';
				print '</tr>';
				$t = 0;
				$count_reg = 0;
				while ($j < $num2)
				{
					$registration = $db->fetch_object($resql_reg);
					$contactstat = new Contact($db);
					$event->fetch($registration->fk_event);
					$eventday->fetch($registration->fk_eventday);
					$contactstat->fetch($registration->fk_user_registered);
					$eventday->id=$registration->fk_eventday;
					$eventday->ref = $registration->day_ref;
					
					print '<tr '.$style.'>';

					// Statut
					print '<td align="center">'. $regstat->LibStatut($registration->fk_statut,3).'</td>';

					// Name
					print '<td>'.$contactstat->getNomUrl(1).'</td>';

					// Event
					print '<td>'.$event->getNomUrl(1).'</td>';

					// Day
					print '<td>'.$eventday->getNomUrl(1).'</td>';

 					// Groupe
 					print '<td>'.$registration->event_level_label.'</td>';

					// Date create
					print '<td>'.dol_print_date($db->jdate($registration->datec),'dayhour').'</td>';
					
					// Date validate
					print '<td>'.dol_print_date($db->jdate($registration->date_valid),'dayhour').'</td>';

					// Paid
					print '<td align="center">';
					$paid = $registration->paye > 0 ? "on":"off";
					$trans_paid = $registration->paye > 0 ? "AlreadyPaid":"BillStatusNotPaid";
					if($eventday->total_ht=='0') print '&nbsp;';
					elseif($eventday->total_ht!='0') print img_picto($langs->trans($trans_paid),$paid);
					print '</td>';

					// Actions
					print '<td align="center">';
					print '<a href="fiche.php?id='.$registration->rowid.'">'.img_picto('View','detail').'</a>';
					print '</td>';
					print '</tr>';
					$statut = $registration->fk_statut;

					$j++;
				}
			}
		}
	}
}
