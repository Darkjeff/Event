<?php
require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/registration.class.php");
require_once("../class/eventlevel.class.php");
require_once("../lib/event.lib.php");
require_once("../core/modules/event/modules_event.php");
require_once("../core/modules/registration/modules_registration.php");
require_once("../lib/html.formregistration.class.php");

	function get_info_from_table($str, $id)
	{
		global $db;

		$sql = "SELECT ".$str." FROM ".MAIN_DB_PREFIX."event_registration AS er WHERE er.rowid = ".$id;
		$resql = $db->query($sql);
		if ($resql)
		{
			$res = $resql->fetch_assoc();
			return ($res[$str]);
		}
		else
			return NULL;
	}

	function show_button($key, $id, $check)
	{
		global $langs;

		$url1 = $_SERVER['PHP_SELF'].'?id='.$id.'&key='.$key.'&action=participate';
		$url2 = $_SERVER['PHP_SELF'].'?id='.$id.'&key='.$key.'&action=not_participate';
		print '<p>';
		if ($check == 1)
			print '<input type="button" class="button" value="'.$langs->trans("Participate").'" onclick="location.href=\''.$url1.'\';"> &nbsp; ';
		else if ($check == 2)
			print '<input type="button" class="button" value="'.$langs->trans("NotParticipate").'" onclick="location.href=\''.$url2.'\';"> &nbsp; ';
		else
		{
			print '<input type="button" class="button" value="'.$langs->trans("Participate").'" onclick="location.href=\''.$url1.'\';"> &nbsp; ';
			print '<input type="button" class="button" value="'.$langs->trans("NotParticipate").'" onclick="location.href=\''.$url2.'\';"> &nbsp; ';
		}
		print '</p>';
	}

	function show_description($page)
	{
		global $langs;

		print '<div class="tabBar" style="width:80%;text-align:left;"><fieldset>';
		if ($page)
			print $page;
		else
			print $langs->trans('NoDescriptionAvailable');
		print '</fieldset></div>';
	}

	function show_information($form, $regstat)
	{
		global $db, $langs, $users, $conf;

		print '<div class="tabBar" style="width:80%;"">';
		print '<table class="border">';

		$eventday=new Day($db);
		$result=$eventday->fetch($regstat->fk_eventday);
		print '<tr class="liste_titre liste_titre_napf_red">';
		print '<td class="liste_titre" colspan="2"><strong>';
		print $langs->trans('RegistrationInfos');
		print '</strong></td>';
		print '</tr>';

		// Réf
		if($regstat->ref != '')
		{
			print '<tr><td width="30%">'.$langs->trans("Ref").'</td>';
			print '<td class="valeur">';
			print $regstat->ref;
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
			print $eventstat->label;
			print '</td>';
			print '</tr>';

		}

		// Journée
		print '<tr><td width="30%">'.$langs->trans("EventDay").'</td>';
		print '<td class="valeur">';
		print $eventday->label;
		print ' - '.dol_print_date($eventday->date_event,'daytext');
		print '</td>';
		print '</tr>';

		// Level
		print '<tr><td width="30%">';
		print $langs->trans("EventLevel");
		print '</td>';
		print '<td class="valeur">';
		$level=new Eventlevel($db);
		$result=$level->fetch($regstat->fk_levelday);
		print (empty($level->label)?'Aucun':$level->label);

		print '</td>';
		print '</tr>';

		// Statut
		print '<tr><td>'.$langs->trans("Statut").'</td><td class="valeur">'.$regstat->getLibStatut(4).'&nbsp;</td>';
		print '</tr>';

		// Date
		print '<tr><td>'.$langs->trans("RegistrationDate").'</td><td class="valeur">'.dol_print_date($regstat->datec,'dayhour').'&nbsp;</td>';
		print '</tr>';


		// User who made registration
		print '<tr><td>'.$langs->trans("ContactWhoMadeRegistration").'</td>';
		if (!class_exists('User'))
			require_once DOL_DOCUMENT_ROT.'/user/class/user.class.php';
		$userstat = new User($db);
		$userstat->fetch($regstat->fk_user_create);
		print '<td class="valeur">'.$userstat->lastname.'&nbsp;</td>';
		print '</tr>';


		if($regstat->fk_statut > 0)
		{
			// Date valid
			print '<tr><td>'.$langs->trans("DateValid").'</td><td class="valeur">'.dol_print_date($regstat->date_valid,'dayhour').'&nbsp;</td>';
			print '</tr>';

			// User who valid registration
			print '<tr><td>'.$langs->trans("ContactWhoValidRegistration").'</td>';
			$userstat->fetch($regstat->fk_user_valid);
			print '<td class="valeur">'.$userstat->lastname.'&nbsp;</td>';
			print '</tr>';
		}

		// Notes (must be a textarea and not html must be allowed (used in list view)
		print '<tr><td valign="top">';
		print $langs->trans("NotePublic");
		print '</td><td colspan="3">';
		print $regstat->note_public;
		print '</td>';
		print '</tr>';

		print '<tr><td valign="top">';
		print $langs->trans("NotePrivate");
		print '</td><td colspan="3">';
		print $regstat->note_private;
		print '</td>';
		print '</tr>';


		print '<tr class="liste_titre liste_titre_napf_red">';
		print '<td class="liste_titre" colspan="2"><strong>';
		print $langs->trans('UserRegistrationInfos');
		print '</strong></td>';
		print '</tr>';

		// Third party Dolibarr
		if ($conf->societe->enabled)
		{
			print '<tr><td>';
			print $langs->trans("LinkedToDolibarrThirdParty");
			print '</td><td colspan="2" class="valeur">';
			if ($regstat->fk_soc > 0)
			{
				$company=new Societe($db);
				$result=$company->fetch($regstat->fk_soc);
				print $company->nom;
			}
			else
			{
				print $langs->trans("NoThirdPartyAssociatedToRegistration");
			}
			print '</td></tr>';
		}

		// User registered
		print '<tr><td>';
		print $langs->trans("Contact");
		print '</td>';
		if($regstat->fk_user_registered > 0 && $action != 'editcontact')
		{
			$contactstat=new Contact($db);
			$res = $contactstat->fetch($regstat->fk_user_registered);
			if($res > 0 || !$action)
			{
				print '<td class="valeur">'.$contactstat->firstname.' '.$contactstat->lastname.'&nbsp;</td>';
			}
		}
		print '</tr>';
		print "</table>";
		print "</div>";
	}
?>