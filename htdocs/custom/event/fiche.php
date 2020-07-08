<?php
/* Copyright (C) 2007-2010  Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012-2015  JF FERRY			<jfefe@aternatik.fr>
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
 *   	\file       event/fiche.php
 *		\ingroup    event
 *		\brief      Index page of module event
 */


// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;

if (! $res && file_exists("../main.inc.php")) { $res=include("../main.inc.php"); }
if (! $res && file_exists("../../main.inc.php")) { $res=include("../../main.inc.php"); }// for curstom directory
if (! $res) { die("Include of main fails"); }

// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
require_once("class/event.class.php");
require_once("class/day.class.php");
require_once("class/eventlevel_cal.class.php");

require_once("lib/event.lib.php");
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

// Get parameters
$id			= GETPOST('id','int');
$ref		= GETPOST('ref','alpha');
$action		= GETPOST('action');
$confirm	= GETPOST('confirm');

// Security check
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'event', $id);

$object = new Event($db);


/*
 * Actions
*/

if ($action == 'add' && $user->rights->event->write)
{
	$error=0;
	$mesg='';

	if (empty($ref))
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref")).'</div>';
		$error++;
	}
	if (!GETPOST("label",'alpha'))
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Label")).'</div>';
		$error++;
	}

	if ( ( isset($_POST['date_start']) && !GETPOST("date_start",'alpha') ) || empty($_POST['date_startday']) )
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("DateStart")).'</div>';
		$error++;
	}

	if (! $error)
	{
		$object->ref				= $ref;
		$object->label				= $_POST["label"];
		$object->socid				= $_POST["socid"];
		$object->description		= $_POST["description"];
		$object->note_public       	= $_POST["note_public"];
		$object->note       		= $_POST["note"];
		$object->datec				= dol_now();
		$object->date_start 		= dol_mktime(7,0,0,$_POST['date_startmonth'],$_POST['date_startday'],$_POST['date_startyear']);
		$object->date_end			= dol_mktime(19,0,0,$_POST['date_endmonth'],$_POST['date_endday'],$_POST['date_endyear']);
		$object->price_day			= $_POST["price_day"];
		$object->tva_tx				= $_POST["tva_tx"];

		if ($_POST['price_base_type'] == 'TTC')
		{
			$object->total_ttc = price2num($_POST["price"],'MU');
			$object->total_ht = price2num($_POST["price"]) / (1 + ($object->tva_tx / 100));
			$object->total_ht = price2num($object->total_ht,'MU');
		}
		else
		{
			$object->total_ht  = price2num($_POST["price"],'MU');
			$object->total_ttc = price2num($_POST["price"]) * (1 + ($object->tva_tx / 100));
			$object->total_ttc = price2num($object->total_ttc,'MU');
		}

		$object->registration_byday	= $_POST["registration_byday"];
		$object->registration_open	= $_POST["registration_open"];

		$result = $object->create($user);
		if ($result > 0)
		{
			// Add account manager
			$result = $object->add_contact($_POST["contactid"], 'EVENTMANAGER', 'internal');

			Header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
			exit;
		}
		else
		{
			$langs->load("errors");
			$mesg='<div class="error">'.$langs->trans($object->error).'</div>';
			$action = 'create';
		}
	}
	else
	{
		$action = 'create';
	}
}
else if ($action == 'update' && ! $_POST["cancel"] && $user->rights->event->write)
{
	$error=0;

	if (empty($ref))
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref")).'</div>';
		$error++;
	}
	if (!GETPOST("label",'alpha'))
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Label")).'</div>';
		$error++;
	}

	if (!GETPOST("date_start",'alpha'))
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("DateStart")).'</div>';
		$error++;
	}

	if (GETPOST("price") == NULL)
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("EventPriceHt")).'</div>';
		$error++;
	}
	if (! $error)
	{
		$object->fetch($id);

		$object->ref          		= $ref;
		$object->label        		= GETPOST("label");
		$object->socid        		= GETPOST("socid");
		$object->description  		= GETPOST("description");
		$object->note_public       	= GETPOST("note_public");
		$object->note       		= GETPOST("note");
		$object->date_start 		= dol_mktime(7,0,0,GETPOST('date_startmonth'),GETPOST('date_startday'),GETPOST('date_startyear'));
		$object->date_end			= dol_mktime(19,0,0,GETPOST('date_endmonth'),GETPOST('date_endday'),GETPOST('date_endyear'));
		$object->total_ht			= GETPOST("price");
		$object->price_day			= GETPOST("price_day");
		$object->tva_tx				= GETPOST("tva_tx");
		$object->registration_byday	= GETPOST("registration_byday");
		$object->registration_open	= GETPOST("registration_open");

		$result=$object->update($user);
		if ($result > 0)
		{
			Header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
			exit;
		}
		else
		{
			$mesg='<div class="error">'.$object->error.'</div>';

			$action='edit';
		}
	}
	else
	{
		$action='edit';
	}
}
else if ($action == 'confirm_validate' && $confirm == 'yes')
{
	$object->fetch($id);

	$result = $object->setStatut(5);
	if ($result <= 0)
	{
		$mesg='<div class="error">'.$object->error.'</div>';
	}
	else
	{
		// Checkbox to choose days validation
		if(GETPOST('valid_days') == "on") {
			$eventdays = new Day($db);
			$eventdaystatic = new Day($db);
			$res = $eventdays->fetch_all("DESC",'t.date_event',0,'',0,array('t.fk_event' => $id));
			$array_event = $eventdays->line;
			if($res > 0) {
				foreach ($array_event as $eventarray) {
					$res = $eventdaystatic->fetch($eventarray->id);
					if($res > 0)
					{
						$defaultref='';
						$obj = empty($conf->global->EVENTDAY_ADDON)?'mod_eventday_simple':$conf->global->EVENTDAY_ADDON;
						if (! empty($conf->global->EVENTDAY_ADDON) && is_readable(dol_buildpath("/event/core/models/num/".$conf->global->EVENTDAY_ADDON.".php")))
						{
							dol_include_once("/event/core/models/num/".$conf->global->EVENTDAY_ADDON.".php");
							$modEvent = new $obj;
							$defaultref = $modEvent->getNextValue($mysoc,$eventarray);
						}
						if (empty($defaultref)) $defaultref='';

						$eventdaystatic->setStatut(4);
					}
				}
			}
		}
	}
}
else if ($action == "confirm_registration_open" && $confirm == "yes")
{
	$result = $object->fetch($id);
	if ($result)
	{
		$object->setRegistrationOpen(1);

		// Checkbox to choose days validation
		if(GETPOST('open_days') == "on")
		{
			$eventdays = new Day($db);
			$eventdaystatic = new Day($db);
			$res = $eventdays->fetch_all("DESC",'t.date_event',0,'',0,array('t.fk_event' => $id));
			$array_event = $eventdays->line;
			if($res > 0) {
				foreach ($array_event as $eventarray) {
					$res = $eventdaystatic->fetch($eventarray->id);
					if($res > 0)
					{
						$eventdaystatic->setRegistrationOpen(1);
					}
				}
			}
		}
		$mesg='<div class="ok">'.$langs->trans('RegistrationIsNowOpen').'</div>';
	}
}
else if ($action == "confirm_registration_close" && $confirm == "yes")
{
	$result = $object->fetch($id);
	if ($result)
	{
		$object->setRegistrationOpen(0);
		$mesg='<div class="ok">'.$langs->trans('RegistrationIsNowClosed').'</div>';
	}
}
else if ($action == "open_registration_byday" )
{
	$result = $object->fetch($id);
	if ($result)
	{
		$object->setRegistrationByDay(1);
		$mesg='<div class="ok">'.$langs->trans('RegistrationIsNowAvailableByDay').'</div>';
	}
}
else if ($action == "close_registration_byday")
{
	$result = $object->fetch($id);
	if ($result)
	{
		$object->setRegistrationByDay(0);
		$mesg='<div class="ok">'.$langs->trans('RegistrationIsNowNotAvailableByDay').'</div>';
	}
}
else if ($action == 'setnote_public' && $user->rights->event->write)
{
	$object->fetch($id);
	$result=$object->update_note_public(dol_html_entity_decode(GETPOST('note_public'), ENT_QUOTES));
	if ($result < 0) dol_print_error($db,$object->error);
}
else if ($action == 'setnote' && $user->rights->event->write)
{
	$object->fetch($id);
	$result=$object->update_note(dol_html_entity_decode(GETPOST('note'), ENT_QUOTES));
	if ($result < 0) dol_print_error($db,$object->error);
}
else if ($action == 'confirm_close_event' && $user->rights->event->write)
{
	$object->fetch($id);
	$result=$object->setClotured($user);
	if ($result < 0) dol_print_error($db,$object->error);
	header("Location:./index.php");
}
else if ($action == 'confirm_clone' && $user->rights->event->write && $confirm == "yes")
{
	$result=$object->createFromClone($id);
	dol_syslog('Fiche.php :: createFromClone :'.$object->get_days($id),LOG_DEBUG);
	dol_syslog('Fiche.php :: valid_group :'.GETPOST('valid_group'),LOG_DEBUG);

	
	if(GETPOST('valid_days')=='on')
	{
		foreach($object->get_days($id) as $key=>$el)
		{	
			$day_to_clone = new day($db);
			$result2 = $day_to_clone->createFromClone($el,$result);

			dol_syslog('Fiche.php::createFromClone '.$day_to_clone->LoadLevelForDay($el,1),LOG_DEBUG);

			if(GETPOST('valid_group')=='on')
				{
					foreach ( $day_to_clone->LoadLevelForDay($el,1) as $key2=>$el2)
					{
					$Eventlevel_to_clone = new Eventlevel($db);
					$result3 = $Eventlevel_to_clone->DefLevelForDay($el2,$result2);
					}
				}
		}
	}
		
	if ($result < 0) dol_print_error($db,$objet->error);
	else
	{
		$mesg='<div class="ok">'.$langs->trans('CloneOK').'</div>';
		Header("Location: ".$_SERVER['PHP_SELF']."?id=".$result);
	}
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/


$form=new Form($db);

$userstatic=new User($db);

if ($action == 'create' && $user->rights->event->write)
{
	llxHeader('',$langs->trans("NewEvent"),'');

	print_fiche_titre($langs->trans("NewEvent"));

	dol_htmloutput_mesg($mesg,$mesgs,'error');


	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<table class="border" width="100%">';
	print '<input type="hidden" name="action" value="add">';

	$defaultref='';
	$obj = empty($conf->global->EVENT_ADDON)?'mod_event_simple':$conf->global->EVENT_ADDON;

	if (! empty($conf->global->EVENT_ADDON) && is_readable(dol_buildpath("/event/core/models/num/".$conf->global->EVENT_ADDON.".php")))
	{
		dol_include_once("/event/core/models/num/".$conf->global->EVENT_ADDON.".php");
		$modEvent = new $obj;
		$defaultref = $modEvent->getNextValue($soc,$object);
	}

	if (empty($defaultref)) $defaultref='';
	print '<input type="hidden" name="ref" value="'.(GETPOST("ref")?GETPOST("ref"):$defaultref).'">';

	// Label
	print '<tr><td><label for="label"><span class="fieldrequired">'.$langs->trans("Label").'</span></label></td><td><input size="30" type="text" name="label" id="label" value="'.GETPOST("label").'"></td></tr>';

	// Customer
	print '<tr><td><label for="socid">'.$langs->trans("EventSponsor").'</label></td><td>';
	print $form->select_company(GETPOST("socid"),'socid','',1,1);
	print '</td></tr>';

	// Event manager
	print '<tr><td>'.$langs->trans("EventManager").'</td><td>';
	$form->select_users($user->id,'contactid');
	print '</td></tr>';

	// Date start
	print '<tr><td><label for="date_start"><span class="fieldrequired">'.$langs->trans("DateStart").'</span></label></td><td>';
	print $form->select_date('','date_start');
	print '</td></tr>';

	// Date end
	print '<tr><td><label for="date_end">'.$langs->trans("DateEnd").'</label></td><td>';
	print $form->select_date(-1,'date_end');
	if($conf->global->DISABLE_CREATE_1ST_BAY_BY_DEFAULT=="0") print '<br>'.$langs->trans('EventNotifyCreationPlage');
	print '</td></tr>';

	// Description
	print '<tr><td valign="top"><label for="description">'.$langs->trans("Description").'</label></td>';
	print '<td>';
	$doleditor = new DolEditor('description', GETPOST("description"), '', 160, 'dolibarr_emailing', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 4, 80);
    $doleditor->Create();
	print '</td></tr>';

	// Inscription par journée
    $registration_by_day = GETPOST('registration_byday');
	print '<tr><td><label for="registration_byday">'.$langs->trans("RegistrationForeachDay").'</label></td><td>';
	print $form->selectyesno('registration_byday',isset($registration_by_day) ? $registration_by_day:1,1);
	print '</td></tr>';

	// Price of event
	print '<tr><td><label for="price">'.$langs->trans("EventPriceHt").'</label></td><td><input size="10" type="text" name="price" id="price" value="'.GETPOST("price").'">';
	print '&nbsp;'.$langs->trans("Currency".$conf->currency).'</td></tr>';

	// Price by day
	print '<tr><td><label for="price_day">'.$langs->trans("EventPriceHtByDay").'</label></td><td><input size="10" type="text" name="price_day" id="price_day" value="'.GETPOST("price_day").'">';
	print '&nbsp;'.$langs->trans("Currency".$conf->currency).'</td></tr>';

	// Price base
	print '<tr><td width="15%">';
	print $langs->trans('PriceBase');
	print '</td>';
	print '<td>';
	print $form->selectPriceBaseType($object->price_base_type, "price_base_type");
	print '</td>';
	print '</tr>';

	// VAT
	print '<tr><td>'.$langs->trans("VATRate").'</td><td>';
	print $form->load_tva("tva_tx",GETPOST("tva_tx"),$mysoc);
	print '</td></tr>';

	print '<tr><td colspan="2" align="center">';
	print '<input type="submit" class="button" name="add" value="'.$langs->trans("Add").'">';
	print ' &nbsp; &nbsp; ';
	print '<input type="button" class="button" value="'.$langs->trans("Cancel").'" onclick="location.href=\''.DOL_URL_ROOT.'/custom/event/index.php\'" >';
	print '</td></tr>';

	print '</table>';
	print '</form>';

} // action create

else if ($id || ! empty($ref))
{
	/*
	 * Show or edit
	*/
	llxHeader('',$langs->trans("Event"),'');


	$ret = $object->fetch($id,$ref);
	if ($ret)
	{
		dol_htmloutput_mesg($mesg,$mesgs);

		/*
		 * Confirmation de l'ouverture des inscriptions
		*/
		if ($_GET['action'] == 'open_registration')
		{
			$options_open = array(array('name' => 'open_days', 'type' => 'checkbox', 'label' => $langs->trans('EventAlsoOpenDays'), 'value' => "1"));
			$ret = $form->form_confirm($_SERVER['PHP_SELF'].'?id='.$object->id, $langs->trans("OpenRegistration"), $langs->trans("ConfirmOpenRegistration"), 'confirm_registration_open',$options_open,0,1);
			if ($ret == 'html')
				print '<br>';
		}
		/*
		 * Confirmation de la fermeture des inscriptions
		*/
		if ($_GET['action'] == 'close_registration')
		{
			$ret = $form->form_confirm($_SERVER['PHP_SELF'].'?id=' . $object->id, $langs->trans("CloseRegistration"), $langs->trans("ConfirmCloseRegistration"), 'confirm_registration_close','',0,1);
			if ($ret == 'html')
				print '<br>';
		}

		$soc = new Societe($db);
		$soc->fetch($object->socid);

		$return = restrictedArea($user, 'event', $id, 'event');
		$head = event_prepare_head($object);		
		dol_fiche_head($head, 'event', $langs->trans("EventSingular"),0,($object->public?'event@event':'event@event'));

		// Confirmation validation
		if ($action == 'validate')
		{
			$options_valid = array(array('name' => 'valid_days', 'type' => 'checkbox', 'label' => $langs->trans('EventAlsoValidateDay'), 'value' => "1"));
			$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ValidateEvent'), $langs->trans('ConfirmValidateEvent'), 'confirm_validate',$options_valid,0,1);
			if ($ret == 'html') print '<br>';
		}

		//Confirmation delete
		if ($action == 'delete')
		{
			$ret=$form->form_confirm($_SERVER["PHP_SELF"]."?id=".$object->id,$langs->trans("DeleteAnEvent"),$langs->trans("ConfirmDeleteAnEvent"),"confirm_delete",'','',1);
			if ($ret == 'html') print '<br>';
		}

		//Confirmation close_event
		if ($action == 'close_event')
		{
			$ret=$form->form_confirm($_SERVER["PHP_SELF"]."?id=".$object->id,$langs->trans("CloseAnEvent"),$langs->trans("ConfirmCloseAnEvent"),"confirm_close_event",'','',1);
			if ($ret == 'html') print '<br>';
		}

		//Confirmation clone
		if ($_GET['action'] == 'clone')
		{
			$options_valid = array();
			$option1=array('name' => 'valid_days', 'type' => 'checkbox', 'label' => $langs->trans('CloneDay'), 'value' => "1");
			array_push($options_valid,$option1);
			if($conf->global->EVENT_HIDE_GROUP=='-1') { 
				$option2=array('name' => 'valid_group', 'type' => 'checkbox', 'label' => $langs->trans('CloneGroup'), 'value' => "1");
				array_push($options_valid,$option2);
			}

			$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('CloneEvent'), $langs->trans('CloneConfirm'), "confirm_clone",$options_valid,0,1);
			if ($ret == 'html') print '<br>';
		}

		if ($action == 'edit' )
		{
			print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<input type="hidden" name="action" value="update">';
			print '<input type="hidden" name="id" value="'.$object->id.'">';
			print '<input type="hidden" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $object->ref).'">';

			print '<table class="border" width="100%">';

			// Label
			print '<tr><td><span class="fieldrequired">'.$langs->trans("Label").'</span></td>';
			print '<td><input size="30" name="label" value="'.(GETPOST('label') ? GETPOST('label') : $object->label).'"></td></tr>';

			// Customer
			print '<tr><td>'.$langs->trans("Company").'</td><td>';
			print $form->select_company((GETPOST('socid') ? GETPOST('socid') : $object->socid),'socid','',1,1);
			print '</td></tr>';

			// Date start
			print '<tr><td><span class="fieldrequired">'.$langs->trans("DateStart").'</span></td><td>';
			print $form->select_date($object->date_start,'date_start');
			print '</td></tr>';

			// End date
			print '<tr><td>'.$langs->trans("DateEnd").'</td><td>';
			print $form->select_date($object->date_end?$object->date_end:-1,'date_end');
			print '</td></tr>';

			// Inscription ouverte oui/non
			print '<tr><td><label for="registration_open">'.$langs->trans("RegistrationIsOpen").'</label></td><td>';
			print $form->selectyesno('registration_open',(GETPOST('registration_open') ? GETPOST('registration_open') : $object->registration_open),1);
			print '</td></tr>';

			// Inscription par jour possible
			print '<tr><td><label for="registration_byday">'.$langs->trans("RegistrationForeachDay").'</label></td><td>';
			print $form->selectyesno('registration_byday',(GETPOST('registration_byday') ? GETPOST('registration_byday') : $object->registration_byday),1);
			print '</td></tr>';

			// Price of event
			print '<tr><td><label for="price"><span class="fieldrequired">'.$langs->trans("EventPriceHt").'</span></label></td><td><input size="10" type="text" name="price" id="price" value="'.(GETPOST('price') ? (int)GETPOST('price') : (int)$object->total_ht).'">';
			print '&nbsp;'.$langs->trans("Currency".$conf->currency).'</td></tr>';

			// Price by day
			print '<tr><td><label for="price_day">'.$langs->trans("EventPriceHtByDay").'</span></label></td><td><input size="10" type="text" name="price_day" id="price_day" value="'.(GETPOST('price_day') ? (int)GETPOST('price_day') : (int)$object->price_day).'">';
			print '&nbsp;'.$langs->trans("Currency".$conf->currency).'</td></tr>';

			// VAT
			print '<tr><td>'.$langs->trans("VATRate").'</td><td>';
			print $form->load_tva("tva_tx",GETPOST("tva_tx"),$mysoc);
			print '</td></tr>';

			// Description
			print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
			print '<td>';
            $doleditor = new DolEditor('description', (GETPOST('description') ? GETPOST('description') : $object->description), '', 160, 'dolibarr_emailing', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 4, 80);
            $doleditor->Create();
			print '</td></tr>';

			// Public note
			print '<tr><td valign="top">'.$langs->trans("NotePublic").'</td>';
			print '<td>';
			print '<textarea name="note_public" cols="60" rows="' . ROWS_3 . '">' . (GETPOST('note_public') ? GETPOST('note_public') : $object->note_public) . "</textarea><br>";
			print "</td></tr>";

			// Private note
			if (! $user->societe_id)
			{
				print '<tr><td valign="top">'.$langs->trans("NotePrivate").'</td>';
				print '<td>';
				print '<textarea name="note" cols="60" rows="' . ROWS_3 . '">' . (GETPOST('note') ? GETPOST('note') : $object->note) . "</textarea><br>";
				print "</td></tr>";
			}

			print '<tr><td align="center" colspan="2">';
			print '<input name="update" class="button" type="submit" value="'.$langs->trans("Modify").'"> &nbsp; ';
			print '<input type="submit" class="button" name="cancel" Value="'.$langs->trans("Cancel").'"></td></tr>';
			print '</table>';
			print '</form>';

		}
		else
		{

			/*
			 * View of event
			 */
			print '<table class="border" width="100%">';

			// Label
			print '<tr><td valign="top" width="20%">';
			print $form->editfieldkey("Label",'label',$object->label,$object,$conf->global->MAIN_EDIT_ALSO_INLINE && $user->rights->event->write && $object->fk_statut != 9,'string');
			print '</td><td>';
			print $form->editfieldval("Label",'label',$object->label,$object,$conf->global->MAIN_EDIT_ALSO_INLINE && $user->rights->event->write && $object->fk_statut != 9,'string');
			print "</td></tr>";

			// Third party
			if ($object->socid > 0)
			{
				print '<tr><td>'.$langs->trans("EventSponsor").'</td><td>';
				print $soc->getNomUrl(1);
				print '</td></tr>';
			}

			// Start date
			print '<tr><td>';
			print $form->editfieldkey("DateStart",'date_start',$object->date_start,$object,$conf->global->MAIN_EDIT_ALSO_INLINE && $user->rights->event->write && $object->fk_statut != 9,'datepicker');
			print '</td><td>';
			print $form->editfieldval("DateStart",'date_start',$object->date_start,$object,$conf->global->MAIN_EDIT_ALSO_INLINE && $user->rights->event->write && $object->fk_statut != 9,'datepicker');
			print '</td></tr>';

			// End date
			print '<tr><td>';
			print $form->editfieldkey("DateEnd",'date_end',$object->date_end,$object,$conf->global->MAIN_EDIT_ALSO_INLINE && $user->rights->event->write && $object->fk_statut != 9,'datepicker');
			print '</td><td>';
			print $form->editfieldval("DateEnd",'date_end',$object->date_end,$object,$conf->global->MAIN_EDIT_ALSO_INLINE && $user->rights->event->write && $object->fk_statut != 9,'datepicker');
			print '</td></tr>';

			// Statut
			print '<tr><td>'.$langs->trans("Status").'</td><td>'.$object->getLibStatut(4).'</td></tr>';

			// Inscription ouverte oui/non
			print '<tr><td>'.$langs->trans("RegistrationIsOpen").'</td>';
			if ($object->registration_open > 0) {
				print '<td>';
				print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=close_registration">';
				print img_picto($langs->trans("Activated"),'switch_on');
				print '</a></td>'."\n";
			}
			else {
				print '<td>';
				print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=open_registration">';
				print img_picto($langs->trans("Desactivated"),'switch_off');
				print '</a></td>'."\n";
			}
			print '</td></tr>';

			// Inscription par journée
			print '<tr><td>'.$langs->trans("RegistrationForeachDay").'</td>';
			if ($object->registration_byday > 0) {
				print '<td>';
				print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=close_registration_byday">';
				print img_picto($langs->trans("Activated"),'switch_on');
				print '</a></td>'."\n";
			}
			else {
				print '<td>';
				print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=open_registration_byday">';
				print img_picto($langs->trans("Desactivated"),'switch_off');
				print '</a></td>'."\n";
			}
			print '</td></tr>';

			// Price
			print '<tr><td>';
			print $langs->trans('EventPriceHt');
			print '</td><td>';
			print price($object->total_ht).' '.$conf->currency;
			print '</td></tr>';

			// Price by day
			print '<tr><td>';
			print $langs->trans('EventPriceHtByDay');
			print '</td><td>';
			print price($object->price_day).' '.$conf->currency;
			print '</td></tr>';

			// Description
			print '<tr><td>';
			print $langs->trans('Description');
			print '</td><td>';
			print $object->description;
			print '</td></tr>';

			// Notes (must be a textarea and not html must be allowed (used in list view)
			print '<tr><td valign="top">';
			print $langs->trans("NotePublic");
			print '</td><td colspan="3">';
			print $form->editfieldval("NotePublic",'note_public',$object->note_public,$object,$user->rights->event->write,'textarea');
			print '</td>';
			print '</tr>';

			// Private note
			if (! $user->societe_id)
			{
				print '<tr><td valign="top">';
				print $langs->trans("NotePrivate");
				print '</td><td colspan="3">';
				print $form->editfieldval("NotePrivate",'note',$object->note,$object,$user->rights->event->write,'textarea');
				print '</td>';
				print '</tr>';
			}

			print '</table>';

		}
		print '</div>';

		/*
		 * Boutons actions
		*/
		print '<div class="tabsAction">';
		if ($action != "edit" )
		{
			// Modify
			if ($object->fk_statut != 9 && $user->rights->event->write)
			{
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>';
			}

			// Validate
			if ($object->fk_statut == 0 && $user->rights->event->write)
			{
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=validate">'.$langs->trans("Valid").'</a>';
			}

			// Event validated
			if ($object->fk_statut == 5 && $user->rights->event->write)
			{
				// Clone Event
				if($conf->global->EVENT_ACTIVE_CLONE_FUNC=="1")
					print '<a class="butAction" href="fiche.php?action=clone&id='.$object->id.'">'.$langs->trans("Clone").'</a>'; 
				// Mark event cancelled
				print '<a class="butAction" href="fiche.php?id='.$object->id.'&action=close_event">'.$langs->trans("CloseEvent").'</a>';
				// Add a registration
				print '<br /><br /><br /><a class="butAction" href="day/fiche.php?eventid='.$object->id.'&action=create">'.$langs->trans("NewDay").'</a>';
			}
		}

		print "</div>";

		/*
		 * List of event's day
		 */
		if ($user->rights->event->read)
		{
			$sortfield = GETPOST("sortfield", 'alpha');
			$sortorder = GETPOST("sortorder", 'alpha');

			if (!$sortfield)
				$sortfield = 'e.date_event';
			if (!$sortorder)
				$sortorder = 'ASC';
			$limit = $conf->liste_limit;

			$page = GETPOST("page", 'int');
			if ($page == -1)
			{
				$page = 0;
			}
			$offset = $limit * $page;
			$pageprev = $page -1;
			$pagenext = $page +1;
			$sql_liste = "SELECT e.rowid as id, e.ref, e.fk_soc, e.label, e.datec, e.fk_statut, e.date_event, e.registration_open";
			$sql_liste .= " FROM " . MAIN_DB_PREFIX . "event_day as e ";
			$sql_liste .= ' WHERE e.fk_event = ' . $object->id;
			$sql_liste .= ' ORDER BY ' . $sortfield . ' ' . $sortorder . ', e.fk_soc ASC';
			$nbtotalofrecords = 0;
			if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
			{
				$result = $db->query($sql_liste);
				$nbtotalofrecords = $db->num_rows($result);
			}
			$sql_liste .= $db->plimit($limit +1, $offset);
			$resql = $db->query($sql_liste);

			$param = '&amp;id=' . $id ;

			if ($resql)
			{
				$num = $db->num_rows($resql);
				$i = 0;
				$total = 0;

				if ($num)
				{

					print_barre_liste($langs->trans('ListOfEventDay'), $page, 'fiche.php', $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'img/day_32.png', 1);

					print '<table width="100%" class="noborder">';
					print '<tr class="liste_titre" >';
					print_liste_field_titre($langs->trans('Status'), $_SERVER["PHP_SELF"], 'e.fk_statut', '', $param, '', $sortfield, $sortorder);
					print_liste_field_titre($langs->trans('Label'), $_SERVER["PHP_SELF"], 'e.label', '', $param, '', $sortfield, $sortorder);
					print_liste_field_titre($langs->trans('Day'), $_SERVER["PHP_SELF"], 'e.date_event', '', $param, '', $sortfield, $sortorder);
					print_liste_field_titre($langs->trans('NbRegistered'), $_SERVER["PHP_SELF"], '', '', $param, '', $sortfield, $sortorder);

					if (!$socid)
						print_liste_field_titre($langs->trans('EventSponsor'), $_SERVER["PHP_SELF"], 'e.fk_soc', '', $param, '', $sortfield, $sortorder);

					print_liste_field_titre($langs->trans('Edit'), $_SERVER["PHP_SELF"], '', '', $param, '', $sortfield, $sortorder);
					print '</tr>';

					while ($i < min($num, $conf->liste_limit))
					{

						$obj = $db->fetch_object($resql);
						$societe = new Societe($db);
						$societe->fetch($obj->fk_soc);
						$var = !$var;

						$eventday = new Day($db);
						$eventday->fetch($obj->id);

						print "<tr $bc[$var]>";

						// Status
						print '<td>'.$eventday->getLibStatut(3).'</td>';

						// Link to event
						print '<td>'.$eventday->getNomUrl(1).'</td>';

						// Start date
						print '<td>' . dol_print_date($obj->date_event) . '</td>';

						// Nb registration
						print '<td>';

						// Drafted
						print img_picto($langs->trans('Draft'),'statut0').' '.$eventday->getNbRegistration(0);
						// Waited
						print ' '.img_picto($langs->trans('Waited'),'statut3').' '.$eventday->getNbRegistration(1);
						// Queued
						print ' '.img_picto($langs->trans('Queued'),'statut1').' '.$eventday->getNbRegistration(8);
						// Confirmed
						print ' '.img_picto($langs->trans('Confirmed'),'statut4').' '.$eventday->getNbRegistration(4);
						print ' '.img_picto($langs->trans('Cancelled'),'statut8').' '.$eventday->getNbRegistration(5);

						print '</td>';

						// Customer
						if (!$socid )
						{
							print '<td>';
							if ($obj->fk_soc > 0)
								print $societe->getNomUrl(1);

							print'</td>';
						}

						// Actions
						print '<td>';
						if($user->rights->event->day->delete)
							print '<a href="day/fiche.php?action=edit&amp;id='.$obj->id.'">'.img_picto('','edit').' '.$langs->trans('Edit').'</a> ';
						if($conf->global->EVENT_HIDE_GROUP=="-1") print '<a href="day/level.php?dayid='.$obj->id.'">'.img_picto('','object_group.png').' '.$langs->trans('EventLevels').'</a> ';
						print '<a href="registration/list.php?dayid='.$obj->id.'">'.img_picto('','object_event_registration.png@event').' '.$langs->trans('RegistrationList').'</a>';
						print '</td>';

						print '</tr>';
						$i++;
					}
					echo "</table>";
				}
				else
					print '<div class="info">'.$langs->trans('NoEventDayRegistered').'</div>';

				print "</div>";
			}
			else
				dol_print_error($db);
		}

	}
}


// End of page
llxFooter();
$db->close();
