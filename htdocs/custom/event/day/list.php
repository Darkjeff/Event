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
if ($page == -1)
{
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page -1;
$pagenext = $page +1;

$filter = array('t.date_event' => ($year>0?$year:date('Y')));

$eventdays = $object->fetch_all($sortorder,$sortfield, $limit, $offset,$arch,$filter);

if($eventdays < 0)
	dol_print_error($db,$object->error);

print_barre_liste($langs->trans('ListEventDay'), $page, 'list.php', $param, $sortfield, $sortorder, '', $eventdays,  $eventdays,'day_32@event');

if($year)
	$param_link = '&amp;year='.$year;
if($arch)
	print '<a href="'.$_SERVER['PHPSELF'].'?arch=0'.$param_link.'">'.$langs->trans('DayListShowActive').'</a>';
else
	print '<a href="'.$_SERVER['PHPSELF'].'?arch=1'.$param_link.'">'.$langs->trans('DayListShowClosedToo').'</a>';

print '<table width="100%" class="noborder">';

print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
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

if($year)
	$param .= '&amp;year='.$year;

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
			print '<a href="fiche.php?action=edit&amp;id='.$daystat->id.'">'.img_picto('','edit').' '.$langs->trans('Edit').'</a> ';
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
