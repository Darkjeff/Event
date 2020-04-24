<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012		JF FERRY			<jfefe@aternatik.fr>
 * Copyright (C) 2017		Eric GROULT			<eric@code42.fr>
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
 *   	\file       event/index.php
 *		\ingroup    event
 *		\brief      Index page of module event
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
require_once("../class/event.class.php");
require_once("../class/eventlevel.class.php");
require_once("../class/day.class.php");
require_once("../lib/event.lib.php");

// Load traductions files requiredby by page
$langs->load("admin");
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");
$langs->load("cron");

// Get parameters
$id			= GETPOST('id','int');
$dayid		= GETPOST('dayid','int');
$action		= GETPOST('action','alpha');
$value 		= GETPOST('value','alpha');

// Protection if external user or not an admin
if ($user->societe_id > 0 || !$user->rights->event->setup)
{
	accessforbidden();
}

/*
 * Actions
*/

if ($action == 'add_level' && $user->rights->event->write)
{
	$error=0;
	$mesg='';


	if (!GETPOST("label",'alpha'))
	{
		$mesg.='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Label")).'</div>';
		$error++;
	}


	if (! $error)
	{
		$object = new Eventlevel($db);

		$object->label				= $_POST["label"];
		$object->description		= $_POST["description"];
		$object->datec				= dol_now();
		$object->rang				= $_POST["rang"];


		$result = $object->create($user);
		if ($result > 0)
		{
			$mesg = '<div class="ok">'.$langs->trans('LevelCreatedSuccess').'</div>';
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
		$action = 'new';
	}
}

if($action == "update_level" && $user->rights->event->write)
{

	$level = new Eventlevel($db);

	$level->fetch($id);
	$level->rang = GETPOST('rang','int');
	$level->label = GETPOST('label','alpha');
	$level->description = GETPOST('description','alpha');	
	$result = $level->update($user);
	if ($result > 0)
	{
		$mesg = '<div class="ok">'.$langs->trans('LevelUpdatedSuccess').'</div>';
	}
	else
	{
		$langs->load("errors");
		$mesg='<div class="error">'.$langs->trans($object->error).'</div>';
		$action = '';
	}
}
if($action == "delete_level" && $user->rights->event->write)
{

	$level = new Eventlevel($db);

	$level->fetch($id);
	if ($result > 0)
	{
		if($level->delete($user) > 0) {
			setEventMessage($langs->trans('LevelDeletedSuccess'));
			header("Location: ".$_SERVER['PHP_SELF']);
			exit;
		}
		else
		{
			$langs->load("errors");
			setEventMessage($langs->trans($object->error));
			$action = '';
		}
	}
	else
	{
		$langs->load("errors");
		setEventMessage($langs->trans($object->error));
		$action = '';
	}
}
if ($action == 'set')
{
	$label = GETPOST('label','alpha');
	$scandir = GETPOST('scandir','alpha');

	$type='registration';
	$sql = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity, libelle, description)";
	$sql.= " VALUES ('".$db->escape($value)."','".$type."',".$conf->entity.", ";
	$sql.= ($label?"'".$db->escape($label)."'":'null').", ";
	$sql.= (! empty($scandir)?"'".$db->escape($scandir)."'":"null");
	$sql.= ")";
	if ($db->query($sql))
	{

	}
}
if ($action == 'setdoc')
{
	$label = GETPOST('label','alpha');
	$scandir = GETPOST('scandir','alpha');

	$db->begin();

	if (dolibarr_set_const($db, "EVENT_REGISTRATION_ADDON_PDF",$value,'chaine',0,'',$conf->entity))
	{
		$conf->global->EVENT_REGISTRATION_ADDON_PDF = $value;
	}

	// On active le modele
	$type='registration';
	$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."document_model";
	$sql_del.= " WHERE nom = '".$db->escape($value)."'";
	$sql_del.= " AND type = '".$type."'";
	$sql_del.= " AND entity = ".$conf->entity;
	$result1=$db->query($sql_del);

	$sql = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity, libelle, description)";
	$sql.= " VALUES ('".$value."', '".$type."', ".$conf->entity.", ";
	$sql.= ($label?"'".$db->escape($label)."'":'null').", ";
	$sql.= (! empty($scandir)?"'".$scandir."'":"null");
	$sql.= ")";
	$result2=$db->query($sql);
	if ($result1 && $result2)
	{
		$db->commit();
	}
	else
	{
		$db->rollback();
	}
}


/***************************************************
 * VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("EventSetupLevelTitle"),'');

print_fiche_titre($langs->trans("EventSetupLevelTitle"),$linkback,'setup');

// Configuration header
$head = event_admin_prepare_head();
dol_fiche_head($head, 'EventSetupLevel', $langs->trans("Module1680Name"), 0, 'event@event');

$form=new Form($db);

$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

dol_htmloutput_mesg($mesg,$mesgs);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvar">';

print '<table class="border" width="100%">';

/*
 * GESTION DES GROUPES
*/

if($conf->global->EVENT_HIDE_GROUP=='-1')
	{
	// Confirm registration
if ($action == 'delete')
{
	$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$id, $langs->trans('DeleteLevel'), $langs->trans('ConfirmDeleteLevel'), 'delete_level','',0,1);
	if ($ret == 'html') print '<br>';
}

$sql = "SELECT rowid as id, label, description, fk_user_create, rang, statut FROM ".MAIN_DB_PREFIX."event_level ORDER BY rang ASC";
$resql=$db->query($sql);
if ($resql) {

	print_fiche_titre($langs->trans('LevelList'));

	$num = $db->num_rows($resql);
	if ($num) {
		$i = 0;
		print '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
		print '<input type="hidden" name="action" value="update_level" />';

		print '<table class="border" width="100%">';
		print '<tr   class="liste_titre">';
		// Rang
		print '<th>';
		print $langs->trans('GrpRang');
		print '</th>';
		// Groupe
		print '<th>';
		print $langs->trans('EventLevel');
		print '</th>';
		// Description
		print '<th>';
		print $langs->trans('Description');
		print '</th>';
		// Action
		print '<th>';
		print $langs->trans('Edit');
		print '</th>';
		print '</tr>';

		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			print '<tr>';

			// Rang
			print '<td width="15%">';
			if($action =="edit" && ($obj->id == $id))
				print '<input type="text" class="flat" name="rang" value="'.$obj->rang.'" />' ;
			else print $obj->rang;
			print '</td>';
			
			// Label
			print '<td  width="20%">';
			if($action =="edit" && ($obj->id == $id))
				print '<input type="flat" name="label" value="'.$obj->label.'" />' ;
			else print $obj->label;
			print '</td>';

			// Description
			print '<td  width="45%">';
			if($action =="edit" && ($obj->id == $id))
				print '<input type="flat" name="description" value="'.$obj->description.'" />' ;
			else print $obj->description;

			if($action =="edit" && ($obj->id == $id))
				print '<input type="hidden" name="id" value="'.$obj->id.'" />';
			print '</td>';

			// Action
			print '<td>';
			if($action =="edit") {
				print '<input type="submit" class="button" name="update" value="'.$langs->trans("Modify").'">';
				print ' &nbsp; &nbsp; ';
				print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
			}
			else {
				print '&nbsp; <a href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$obj->id.'">'.img_picto('','edit').'</a>';
				print '&nbsp; <a href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$obj->id.'">'.img_picto($langs->trans("Delete"),'delete').'</a>';
			}
			print '</td>';
			print '</tr>';

			$i++;
		}
		print '</table>';
		print '</form>';
	}
	else {
		print '<div class="error">'.$langs->trans('NoLevel').'</div>';
		$action='new'; /// To show create form
	}

	/*
	 * Boutons actions
	*/

	print '<div class="tabsAction">';
	if ($action != "new" )
	{
		print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=new">'.$langs->trans("CreateLevel").'</a>';
	}
	print "</div>";

	/*
	 * Level create form
	 */
		if ($action == "new")
		{

			print_titre($langs->trans('NewLevel'));

			print '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
			print '<input type="hidden" name="dayid" value="'.$dayid.'" />';
			print '<input type="hidden" name="action" value="add_level" />';
			print '<table class="border" width="100%">';

			// Rang
			print '<tr><td width="20%">'.$langs->trans("GrpRang").'</td><td>';
			print '<input type="text" name="rang" class="flat" value="'.$_POST['rang'].'" size="4"/>';
			print '</td></tr>';

			// Label
			print '<tr><td width="20%">'.$langs->trans("Label").'</td><td>';
			print '<input type="text" name="label" class="flat" value="'.$_POST['label'].'" size="30"/>';
			print '</td></tr>';

			// Description
			print '<tr><td width="20%">'.$langs->trans("Description").'</td><td>';
			print '<textarea name="description" class="flat" >'.$_POST['description'].'</textarea>';
			print '</td></tr>';



			print '<tr><td align="center" colspan="2">';
			print '<input name="update" class="button" type="submit" value="'.$langs->trans("Add").'"> &nbsp; ';
			print '<input type="submit" class="button" name="cancel" Value="'.$langs->trans("Cancel").'"></td></tr>';
			print '</table>';
			print '</form>';



		} // action new	
	}	
}

// End of page
llxFooter();
$db->close();
