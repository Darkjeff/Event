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
 *   	\file       event/registration/relance.php
 *		\ingroup    event
 *		\brief      List of registration which expires in 7 days
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';

require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/eventlevel.class.php");
require_once("../class/registration.class.php");

require_once("../lib/event.lib.php");
require_once("../class/html.formevent.class.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

// Get parameters
$eventid	= GETPOST('eventid','int');
$ref_event = GETPOST('ref_event','alpha');
$dayid		= GETPOST('dayid','int');
$action		= GETPOST('action','alpha');


// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

if ( $action == 'send_reminders')
{
    $registration = new Registration($db);
    $contact = new Contact($db);
    $level=new Eventlevel($db);
    $event=new Event($db);
    $eventday=new Day($db);

    $nb_sent=0;
    foreach ($_POST['reminders'] as $insc )
    {
        $ret = $registration->fetch($insc);
        if ( $ret )
        {
            // Infos participants
            $ret = $contact->fetch($registration->fk_user_registered);
            if ( $ret )
            {
                $event->fetch($registration->fk_event);
                $eventday->fetch($registration->fk_eventday);
                $level->fetch($registration->fk_levelday);

                $substit['__REGREF__'] = $registration->ref;
                $substit['__EVENEMENT__'] = $event->label;
                $substit['__JOURNEE__'] = $eventday->label;
                $substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event,'day');
                $substit['__PARTICIPANT__'] = dolGetFirstLastname($contact->firstname,$contact->lastname);
                $substit['__LEVEL__'] = $level->label;
                $substit['__TIMESTART__'] = $eventday->time_start;
                $substit['__TIMEEND__'] = $eventday->time_end;

                $sujet= GETPOST("subject",'alpha');
                $message= GETPOST("message",'alpha');

                $sujet=make_substitutions($sujet,$substit);
                $message=make_substitutions($message,$substit);
                $message=str_replace('\n',"\n",$message);

                if ( isValidEmail( $contact->email) )
                {
                    if ( $registration->SendByEmail($eventday->ref, $contact->email,$contact->id,$sujet,$message,1,'AC_REMIND') )
                    {
                        setEventMessage( $langs->trans('ReminderSentTo', dolGetFirstLastname( $contact->firstname,$contact->lastname ), $contact->email) );
                        $nb_sent++;
                    }
                    else
                    {
                        setEventMessage( $langs->trans('ReminderNotSentTo', dolGetFirstLastname( $contact->firstname,$contact->lastname ),$contact->email),'errors');
                    }
                }
            }
        }
    }

    if ( $nb_sent > 0 )
          $action = 'reminders_sent';
}


/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("RegistrationList"),'','','','',array('/event/js/event.js'));

$form=new Form($db);
$formevent = new FormEvent($db);

$langs->load('bills');


/*
 * Registration list
 */
if ($user->rights->event->read)
{
	if ( ! $eventid && ! $ref_event )
	{
		/*
		 * Affichage sélecteur des journées
		 */
		print_fiche_titre($langs->trans('ListRegistrationToExpire'),'','event_registration@event').'<br />';

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
			dol_fiche_head($head, 'registration_expire', $langs->trans("EventSingular"),0,($eventstat->public?'event@event':'event@event'));

			/*
			 * View of event
			*/
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
			print '</div>';
		}


		$regstat = new Registration($db);

		print_titre($langs->trans('ListRegistrationToExpire'));

		if ( $action == 'reminders_sent')
		{
		    print '<div class="ok">'.$langs->trans('RemindersSuccessfullySent',$nb_sent).'</div>';
		}
		else
		{

		    /*
		     * Liste des inscriptions arrivant à expiration dans 7 jours
		    */

    		print '<p><em>'.$langs->trans('RegistrationToExpireInfo',$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE).'</em></p>';

    		print '<p><input type="checkbox" id="cocheTout"/> <label for="cocheTout"><span id="cocheText">Cocher tout</span></label></p>';

    		$sql_reg = "SELECT r.rowid , r.fk_soc, r.fk_statut, r.fk_eventday, r.ref, r.datec, r.date_valid, r. fk_user_registered, ed.fk_event";
    		$sql_reg.= ", ed.date_event, DATEDIFF( NOW(),DATE_ADD(r.datec, INTERVAL ".$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE." DAY)  ) as nbjours";
    		$sql_reg.=", DATE_ADD(r.datec, INTERVAL ".$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE." DAY) as date_limit";
    		$sql_reg.=", l.label";
    		$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r';
    		$sql_reg.=' LEFT JOIN '.MAIN_DB_PREFIX.'event_day AS ed ON ed.rowid=r.fk_eventday';
    		$sql_reg.=' LEFT JOIN '.MAIN_DB_PREFIX.'event_level AS l ON l.rowid=r.fk_levelday';
    		$sql_reg.= ' WHERE r.fk_statut IN (0,1)'; // validated only
    		$sql_reg.=' AND r.fk_event='.$eventid;

    		// Only registration expire in X days
    		$sql_reg.=' AND	DATE_ADD(r.datec, INTERVAL '.$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE.' DAY) < NOW()';
    		$sql_reg.=  ' ORDER BY r.fk_statut DESC, r.datec ASC;';

    		$resql_reg=$db->query($sql_reg);
    		if ($resql_reg)
    		{
    			$num2 = $db->num_rows($sql_reg);
    			if ($num2) {

    				$j=0;
    				print '<form method="POST" name="registration" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'">';
    				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    				print '<table class="border" width="100%" id="relances">';
    				print '<tr class="liste_titre">';
    				print '<th></th>';
    				print '<th align="center" width="90">'.$langs->trans('Sent').'</th>';
                    print '<th align="center">'.$langs->trans('Ref').'</th>';
    				print '<th align="center" width="20%">'.$langs->trans('Name').'</th>';
    				print '<th align="center">'.$langs->trans('Status').'</th>';
    				print '<th align="center" width="10%">'.$langs->trans('Level').'</th>';
                    print '<th align="center" width="10%">'.$langs->trans('RegistrationDate').'</th>';
                    print '<th align="center" width="10%">'.$langs->trans('EventDayDate').'</th>';
    				print '<th align="center" width="10%">'.$langs->trans('RegistrationLimitDate').'</th>';
    				print '<th align="center" width="5%">'.$langs->trans('NumberDayInLate').'</th>';
    				print '</tr>';

    				$count_reg = 0;
    				$j=0;
    				while ($j < $num2)
    				{
    					$registration = $db->fetch_object($resql_reg);
                        $regstat->fetch($registration->rowid);

    					print '<tr '.$style.'>';

    					print '<td align="center">';
    					print '<input type="checkbox" class="reminder" name="reminders[]" value="'.$registration->rowid.'"/>';
    					print '</td>';

    					// Nombre relances déjà envoyées
    					print '<td align="center">'.getNbReminders($registration->rowid, $registration->fk_user_registered).'</td>';

                        // Ref
                        print '<td align="center">'. $regstat->getNomUrl($registration->rowid, 'event_registration',1).'</td>';

    					// Nom Prénom
    					$contactstat = new Contact($db);
    					$contactstat->fetch($registration->fk_user_registered);
    					print '<td align="center">'.$contactstat->getNomUrl(3).'</td>';

    					// Statut
                        print '<td align="center" width="200">'. $regstat->LibStatut($registration->fk_statut,4).'</td>';
                        
                        // Level
                        print '<td align="center" width="10%">'.$registration->label.'</td>';
                        
                        // Date validation
                        print '<td align="center">'.dol_print_date($db->jdate($registration->datec),'dayhour').'</td>';

                        // Date Journée
    					print '<td align="center">'.dol_print_date($db->jdate($registration->date_event),'day').'</td>';
   					
    					// Date validation
    					print '<td align="center">'.dol_print_date($db->jdate($registration->date_limit),'day').'</td>';

    					// Nombre de jour
    					print '<td align="center">'.$registration->nbjours.'</td>';

    					print '</tr>';
    					$statut = $registration->fk_statut;
    					$j++;
    				}
    				print '</table><br />';


    				/*
    				 * Print form to send reminder
    				 */

    				print '<div>';

    				print print_fiche_titre($langs->trans('SendReminders'),'','call');

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

    				$form->withtopic='Relance inscription roulage __JOURNEE__';
    				$form->withbody= dol_nl2br($langs->trans('RegistrationReminderText'),1);


    				$form->param['action']="send_reminders";
    				$form->param['event_ref'] = $object->ref;
    				$form->param['eventid'] = $registration->fk_event;

    				$form->show_form();

    				$out= '<tr><td align="center" colspan="2"><center>';
    				$out.= '<input class="button" type="submit" id="sendmail" name="sendmail" value="'.$langs->trans("SendMail").'"';
    				$out.= ' />';
    			    $out.= ' &nbsp; &nbsp; ';
    			    $out.= '<input class="button" type="submit" id="cancel" name="cancel" value="'.$langs->trans("Cancel").'" />';
    				$out.= '</center></td></tr>'."\n";

    				print $out;

    				print '</form></div>';




    			}

    			else print '<div class="ok">'.$langs->trans('NoRegistrationToExpire').'</div><br />';

    		}
    		else dol_print_error($db);
		}
	}
}

// End of page
llxFooter();
$db->close();



