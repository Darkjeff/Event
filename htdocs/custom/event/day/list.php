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
 *   	\file       event/day/list.php
 *		\ingroup    event
 *		\brief      list event days
 */


// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
require_once("../class/event.class.php");
require_once("../class/registration.class.php");
require_once("../class/day.class.php");
require_once("../lib/event.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("bills");
$langs->load("event@event");

// Get parameters
$id			= GETPOST('id','int');
$eventid	= GETPOST('eventid','int');
$ref		= GETPOST('ref','alpha');
$action		= GETPOST('action');
$arch		= GETPOST('arch','int');
$confirm	= GETPOST('confirm');
$year		= GETPOST("year","int")?GETPOST("year","int"):date("Y");
$month		= GETPOST("month","int")?GETPOST("month","int"):date("m");
$week		= GETPOST("week","int")?GETPOST("week","int"):date("W");
$format		= GETPOST('format','alpha'); // calendar or list

$query = GETPOST('query');

// Security check
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'event', $id);

$event = new Event($db);
$object = new Day($db);



/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$form=new Form($db);
$formother=new FormOther($db);
$userstatic=new User($db);

llxHeader('',$langs->trans("ListEventDay"),'');

// Get parameters
$sortorder=GETPOST('sortorder')?GETPOST('sortorder'):"ASC";
$sortfield=GETPOST('sortfield')?GETPOST('sortfield'):"t.date_event";
if (!$sortfield)
	$sortfield = 't.date_event';
if (!$sortorder)
	$sortorder = 'ASC';
$limit = $conf->liste_limit;

$page = GETPOST("page", 'int');
if (empty($page) || $page == -1) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

$filter = array('t.date_event' => ($year>0?$year:date('Y')));
// var_dump($filter);
$eventdays = $object->fetch_all($sortorder,$sortfield, $limit, $offset,$arch,$filter);

if($eventdays < 0)
	dol_print_error($db,$object->error);

if($year)
	$param .= '&amp;year='.$year;

if($arch)
	$param .= '&amp;arch='.$arch;

if($format)
	$param .= '&amp;format='.$format;

print_barre_liste($langs->trans('ListEventDay'), $page, 'list.php', $param, $sortfield, $sortorder, '', $eventdays,  $eventdays,'day_32@event');

print '<div style="overflow: hidden; margin-bottom: 15px;">';
print '<p style="float: left;">';

$param_link = '';
if($year)
	$param_link .= '&amp;year='.$year;
if($format)
	$param_link .= '&amp;format='.$format;

if($arch)
	print '<a href="'.$_SERVER['PHPSELF'].'?arch=0'.$param_link.'" style="border: 2px solid #d2d0d0; padding: 10px 15px;">'.$langs->trans('DayListShowActive').'</a>';
else
	print '<a href="'.$_SERVER['PHPSELF'].'?arch=1'.$param_link.'" style="border: 2px solid #d2d0d0; padding: 10px 15px;">'.$langs->trans('DayListShowClosedToo').'</a>';

print '</p>';
print '<p style="float: right;">';

// format start link calendar
$param_link2 = '';
if($arch)
	$param_link2 .= '&amp;arch='.$arch;
if($year)
	$param_link2 .= '&amp;year='.$year;

if ( $format && $format == 'calendar' )
	print '<a href="'.$_SERVER['PHPSELF'].'?format=list'.$param_link2.'" style="border: 2px solid #d2d0d0; padding: 10px 15px;">'.$langs->trans('Liste').'</a>';
else
	print '<a href="'.$_SERVER['PHPSELF'].'?format=calendar'.$param_link2.'" style="border: 2px solid #d2d0d0; padding: 10px 15px;">'.$langs->trans('Calendrier').'</a>';
// end link calendar

print '</p>';
print '</div>';

print '<table width="100%" class="noborder">';


$param_link3 = '?token=12542token';
if($arch)
	$param_link3 .= '&amp;arch='.$arch;
if($format)
	$param_link3 .= '&amp;format='.$format;

print '<form action="'. $_SERVER["PHP_SELF"] . $param_link3 . '" method="POST">';
print '<input type="hidden" name="token" value="'. $_SESSION['newtoken'].'" />';
print '<input type="hidden" name="id" value="'.$object->id.'" />';
print '<input type="hidden" name="action" value="search_event" />';
// Year
print '<tr class="liste_titre"><td align="left">'.$langs->trans("Year").'</td><td align="left">';

print $formother->select_year($year,'year');
print '</td>';

print '<td colspan="6">';
print '<input type="submit" name="filter_year" value="'.$langs->trans('Search').'" />';
print '</td>';

print '</tr>';
print '</form>';
print '</table>';

// if style page is calendar
if ( $format == 'calendar' ) { // &format=calendar

// -----------------------------------------------------------------------------------------------------

print '<link rel="stylesheet" type="text/css" href="/custom/event/css/fullcalendar/main.css">';
print '<link rel="stylesheet" type="text/css" href="/custom/event/css/fullcalendar/daygrid.css">';
print '<link rel="stylesheet" type="text/css" href="/custom/event/css/fullcalendar/timegrid.css">';
print '<link rel="stylesheet" type="text/css" href="/custom/event/css/fullcalendar/list.css">';
print '<link rel="stylesheet" type="text/css" href="/custom/event/css/fullcalendar/style.css">';
// <!-- jQuery Modal -->
print '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />';

// echo"<pre>"; print_r($object->line); die;
print "<div id='data_events_calendar' style='display: none'>". json_encode($object->line) ."</div>";
print "<br /> <br />";
print "<div id='events_calendar'></div>";

// <!-- Modal HTML embedded directly into document -->
print '<div id="create_event_bydate" class="modal">';


$event2_form = new Form($db);
$event2_list = $event->fetch_list($year);

if (!empty($event2_list)) {

	print '<form action="/custom/event/day/card.php?action=create&eventid='. key($event2_list) .'" method="POST" id="form_event_popup">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add_event2">';


    print '<table class="border" width="100%">';
	
	// Label
	print '<tr><td class="fieldrequired" colspan="2" style="border-bottom: 2px solid #cecece;"><label for="label">Êtes-Vous Sûr De Vouloir Créer Un Événement</label></td></tr>';
	
	// Label
	print '<tr><td class="fieldrequired"><label for="label">'.$langs->trans("Label").'</label></td><td><input size="30" type="text" name="label"></td></tr>';


	// Events manager
	print '<tr><td class="fieldrequired"><label for="label">'.$langs->trans("Événement").'</label></td><td>';
		echo $event2_form->selectarray('eventid', $event2_list, key($event2_list), 0);
	print '</td></tr>';

	// Date start
	print '<tr><td class="fieldrequired"><label for="date_start">'.$langs->trans("DateStart").'</label></td><td>';
	print $event2_form->select_date('','date_event');
	print '</td></tr>';

  print '<tr><td colspan="2" style="border-top: 2px solid #cecece;"><div class="center" style="padding:10px 0;"><a href="#" rel="modal:close" name="cancel" class="button">Annuler</a> &nbsp;&nbsp;&nbsp; <input type="submit" name="button" class="button" value="Confirmer"></div></td></tr>';
  
  print '</table>';
}
else{
	print '<div>Il faut Ajouter des événements</div>';
}
	
print '</div>';

print '<script src="/custom/event/js/fullcalendar/main.js"></script>';
print '<script src="/custom/event/js/fullcalendar/interaction.js"></script>';
print '<script src="/custom/event/js/fullcalendar/daygrid.js"></script>';
print '<script src="/custom/event/js/fullcalendar/timegrid.js"></script>';
print '<script src="/custom/event/js/fullcalendar/list.js"></script>';
print '<script src="/custom/event/js/fullcalendar/langue_fr.js"></script>';
print '<script src="/custom/event/js/fullcalendar/script.js"></script>';
print '<script src="/custom/event/js/fullcalendar/popper.js"></script>';
print '<script src="/custom/event/js/fullcalendar/tooltip.js"></script>';
// <!-- jQuery Modal -->
print '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>';


// -----------------------------------------------------------------------------------------------------

}
else { // if style page is list
	
print '<table width="100%" class="noborder">';
print '<tr class="liste_titre" >';
print_liste_field_titre($langs->trans('Status'), $_SERVER["PHP_SELF"], 't.fk_statut', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Label'), $_SERVER["PHP_SELF"], 't.label', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Day'), $_SERVER["PHP_SELF"], 't.date_event', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('NbRegistered'), $_SERVER["PHP_SELF"], '', '', $param, '', $sortfield, $sortorder);

if (!$socid)
	print_liste_field_titre($langs->trans('EventSponsor'), $_SERVER["PHP_SELF"], 't.fk_soc', '', $param, '', $sortfield, $sortorder);

print_liste_field_titre($langs->trans('Edit'), $_SERVER["PHP_SELF"], '', '', $param, '', $sortfield, $sortorder);

print '</tr>';

if(count($object->line)>0) 
{
	// Tableau des journées
	foreach($object->line as $eventday) 
	{
	
		
		$societe = new Societe($db);
		$societe->fetch($eventday->fk_soc);
		$var = !$var;
	
		$daystat = new Day($db);
		$daystat->id = $eventday->id;
		$daystat->ref = $eventday->ref;
		$daystat->fk_statut = $eventday->fk_statut;
	
		$event->fetch($eventday->fk_event);
		
		$daystat->label = $eventday->label;
		print "<tr $bc[$var]>";
		// Status
		print '<td>'.$daystat->getLibStatut(3).'</td>';

		// Link to event
		print '<td>'.$daystat->getNomUrl(1).'</td>';

		// Start date
		print '<td>' . dol_print_date($eventday->date_event,'day') . '</td>';

		// Nb registration
		print '<td>';

		// Drafted
		print img_picto($langs->trans('Draft'),'statut0').' '.$daystat->getNbRegistration(0);
		// Waited
		print ' '.img_picto($langs->trans('Waited'),'statut3').' '.$daystat->getNbRegistration(1);
		// Queued
		print ' '.img_picto($langs->trans('Queued'),'statut1').' '.$daystat->getNbRegistration(8);
		// Confirmed
		print ' '.img_picto($langs->trans('Confirmed'),'statut4').' '.$daystat->getNbRegistration(4);
		print ' '.img_picto($langs->trans('Cancelled'),'statut8').' '.$daystat->getNbRegistration(5);

		print '</td>';

		// Customer
		if (!$socid )
		{
			print '<td>';
			if ($eventday->fk_soc > 0)
				print $societe->getNomUrl(1);

			print'</td>';
		}

		// Actions
		print '<td>';
		if($user->rights->event->day->delete)
			print '<a href="card.php?action=edit&amp;id='.$daystat->id.'">'.img_picto('','edit').' '.$langs->trans('Edit').'</a> ';
		if($conf->global->EVENT_HIDE_GROUP=='-1') print '<a href="level.php?dayid='.$daystat->id.'">'.img_picto('','object_group.png').' '.$langs->trans('EventLevels').'</a> ';
		print '<a href="../registration/list.php?dayid='.$daystat->id.'">'.img_picto('','object_event_registration.png@event').' '.$langs->trans('RegistrationList').'</a>';
		print '</td>';

		print '</tr>';

		$i++;
	}
}
else 
{
	print '<div class="warning">' . $langs->Trans('NoDayRegistered') . '</div>';
}
echo "</table>";

} //end if 

/*
 * Registration search form
 */
print '<br />';

print_fiche_titre($langs->trans('RegistrationSearch'), '', 'event_registration@event');
print '<p>' . $langs->trans('RegistrationSearchHelp') . '</p>';
$ret.='<form action="' . dol_buildpath('/event/index.php', 1) . '" method="post">';
$ret.='<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
$ret.='<input type="hidden" name="action" value="search">';
// $ret.='<input type="hidden" name="eventday" value="'.$event->id.'">';

$ret.='<input type="text" class="flat" name="query" size="10" />&nbsp;';
$ret.='<input type="submit" class="button" value="' . $langs->trans("Search") . '">';
$ret.="</form>\n";
print $ret;
print '<br />';


// End of page
llxFooter();
$db->close();
?>
