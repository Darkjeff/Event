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
require_once("../core/modules/event/modules_event.php");
require_once("../core/modules/registration/modules_registration.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

// Get parameters
$id			= GETPOST('id','int');
$dayid		= GETPOST('dayid','int');
$action		= GETPOST('action','alpha');
$confirm 	= GETPOST('confirm','alpha');
$value = -1;
// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

if ($action == "change_statut")
	$value = GETPOST('statut_input');

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);	// Show html headers

main_area($langs->trans("RegistrationList"));
$form=new Form($db);

dol_htmloutput_mesg($mesg,$mesgs);

/*
 * Event list
 */
if ($user->rights->event->read)
{
	if (!$dayid)
	{
		/*
		 * Affichage sélecteur des journées
		 */
	}
	else
	{
		/*
		 * Liste des inscriptions pour la journée
		 */

		$event = new Day($db);
		$event->fetch($dayid);
		$regstat = new Registration($db);

		print_fiche_titre($langs->trans('RegistrationForThisDay').' - '.dol_print_date($event->date_event,'daytext'),'','').'<br />';
		//0 1 4 5 6 8
		$i = 0;
		$statut = array(0, 1, 4, 5, 6, 8);
		print '<form method="post" action="'.$_SERVER["PHP_SELF"].'?dayid='.$dayid.'&action=change_statut"><fieldset>';
		print '<input type="radio" name="statut_input" value="-1" id="-1" ';
		if (empty($value) ||$value == -1)
			print 'checked';
		print '/> <label for="-1">Tout afficher</label><br />';
		while ($i < sizeof($statut))
		{
			print '<input type="radio" name="statut_input" value="'.$statut[$i].'" id="'.$statut[$i].'" ';
			if ($value == $statut[$i])
				print 'checked';
			print '/> <label for="'.$statut[$i].'">';
			print $regstat->LibStatut($statut[$i], 4);
			print '</label><br />';
			$i++;
		}
		print '<input type="submit" value="Modifier" /></fieldset>';
		print '</form>';

		/*
		 * Liste des inscriptions sans groupe
		*/
		print_titre($langs->trans('RegistratioWithoutLevel'));
		print '<p><em>'.$langs->trans('RegistratioWithoutLevelInfo',$level_day->place).'</em></p>';
		print '<p><a class="butAction" href="export.php?dayid='.$event->id.'&statut='.$value.'">Export CSV</a></p>';
		$sql_reg = "SELECT r.rowid , r.fk_soc, r.fk_statut, r.fk_eventday, r.ref, r.datec, r.date_valid, r.fk_user_registered, sc.nom, s.poste, s.email";
		$sql_reg.=" FROM ".MAIN_DB_PREFIX."event_registration AS r";
		$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."socpeople AS s ON s.rowid=r.fk_user_registered";
		$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."societe AS sc ON sc.rowid=s.fk_soc";
		$sql_reg.=" WHERE r.fk_eventday = '".$event->id."' AND (r.fk_levelday IS NULL OR r.fk_levelday=0)";
		if ($value != -1)
			$sql_reg.=" AND r.fk_statut=".$_POST['statut_input'];
		$sql_reg.=" ORDER BY r.fk_statut DESC, s.lastname, r.datec ASC;";

		$resql_reg=$db->query($sql_reg);
		if ($resql_reg) {
			$num2 = $db->num_rows($sql_reg);
			if ($num2) {
				$j=0;

				print '<table class="border" width="100%" >';
		
				print '<tr class="liste_titre">';
				print '<th width="2%">&nbsp;</th>';
				print '<th width="2%">'.$langs->trans('NumberShort').'</th>';
				print '<th width="10%">'.$langs->trans('Status').'</th>';
				print '<th width="15%">'.$langs->trans('RegistrationDate').'</th>';
				print '<th width="40%">'.$langs->trans('Name').'</th>';
				print '<th>'.$langs->trans('Email').'</th>';
				print '<th>'.$langs->trans('Poste').'</th>';
				print '<th>'.$langs->trans('Societe').'</th>';
				print '</tr>';
		
				$count_reg = 0;
				while ($j < $num2)
				{
					$registration = $db->fetch_object($resql_reg);
					
					print '<tr '.$style.'>';
					print '<td><input type="checkbox" name="registration_select[]" value="'.$registration->rowid.'" /></td>';
					// Number
					print '<td>'.$registration->rowid.'</td>';
					// Statut
					print '<td>'. $regstat->LibStatut($registration->fk_statut,4).'</td>';
					// Date
					print '<td>'.dol_print_date($db->jdate($registration->datec),'dayhour').'</td>';
					// Nom Prénom
					$contactstat = new Contact($db);
					$result=$contactstat->fetch($registration->fk_user_registered);
					print '<td>'.$contactstat->getNomUrl(1).'</td>';
					//Email
					print '<td>'. $registration->email.'</td>';
					// Poste
					print '<td>'. $registration->poste.'</td>';
					// Company
					print '<td>'. $registration->nom.'</td>';
					print '</tr>';
					$statut = $registration->fk_statut;
					$j++;
				}
				print '</table><br />';
			}
				
			else print '<div class="ok">'.$langs->trans('NoRegistrationWithoutLevel').'</div><br />';
			
		}
		else  dol_print_error($db);

		/*
		 * Liste des inscriptions pour le groupe
		 */
		$sql_level = "SELECT l.label, l.rowid, ld.place FROM ".MAIN_DB_PREFIX."event_level as l,".MAIN_DB_PREFIX."event_level_day as ld WHERE ld.fk_eventday='".$dayid."' AND l.rowid=ld.fk_level ORDER BY l.rang ASC";
		$resql=$db->query($sql_level);
		if ($resql) {
			$num = $db->num_rows($resql);
			if ($num) {
				$i = 0;

				echo "<div id=\"registration_list\">\n";

				while ($i < $num)
				{
					$level_day = $db->fetch_object($resql);

					print_titre($level_day->label);
					print '<p><em>'.$langs->trans('NumberAvailableForThisLevel',$level_day->place).'</em></p>';
					$sql_reg = "SELECT r.rowid as id , r.fk_soc, r.fk_statut, r.ref, r.datec, r.date_valid, r.fk_user_registered, r.note_public, r.note_private";
					$sql_reg.= " FROM ".MAIN_DB_PREFIX."event_registration AS r";
					$sql_reg.= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople AS s ON s.rowid=r.fk_user_registered";
					$sql_reg.= " WHERE r.fk_eventday = '".$dayid."' AND r.fk_levelday='".$level_day->rowid."' ORDER BY r.fk_statut DESC, s.lastname ASC, r.datec ASC;";
					$resql_reg=$db->query($sql_reg);
					if ($resql_reg) {
						$num2 = $db->num_rows($sql_reg);
						if ($num2) {
							$j=0;

							print '<table class="border" width="100%" >';

							print '<tr class="liste_titre">';
							print '<th width="2%">'.$langs->trans('NumberShort').'</th>';
							print '<th width="10%">'.$langs->trans('Ref').'</th>';
							print '<th width="10%">'.$langs->trans('Status').'</th>';
							print '<th width="15%">'.$langs->trans('RegistrationDate').'</th>';
							print '<th width="15%">'.$langs->trans('ConfirmationDate').'</th>';
							print '<th width="30%">'.$langs->trans('Name').'</th>';
							print '<th width="10%">'.$langs->trans('Notes').'</th>';
							print '</tr>';

							// On boucle pour chaque groupe de la journée
							$count_reg = 0;
							while ($j < $num2)
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
										$style = "style=\"background: #f6b0b0;";
										break;
									case "6": //closed
										$style = "style=\"background: #f48383;";
										break;
									case "8": // waiting
										$style = "style=\"background: #f48383;";
										break;
								}

								$style.="\"";
								print '<tr '.$style.'>';
								$ret.="\n";

								// Number
								print '<td>'.$count_reg.'</td>';

								// Ref
								print '<td>'.$registration->ref.'</td>';

								// Statut
								print '<td>'. $regstat->LibStatut($registration->fk_statut,1).'</td>';

								// Date
								print '<td>'.dol_print_date($db->jdate($registration->datec),'dayhour').'</td>';

								// Date
								print '<td>'.dol_print_date($db->jdate($registration->date_valid),'dayhour').'</td>';

								// Nom Prénom
								$contactstat = new Contact($db);
								$result=$contactstat->fetch($registration->fk_user_registered);
								print '<td>'.$contactstat->getNomUrl(1).'</td>';

								// Note interne
								print '<td>';
								print $registration->note_private;
								print '</td>';

								print '</tr>';
								$statut = $registration->fk_statut;
								$j++;
							}

							print '</table><br />';

						}
						else print $langs->trans('NoRegistration').'<br />';


					}
					else
						dol_print_error($db);

					$i++;
				}

				print '</div><!-- registration_list -->';
			}
			else
				print $langs->trans('NoRegistration').'<br />';
		}
		else 
			dol_print_error($db);
	}
}




