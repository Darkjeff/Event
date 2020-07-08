<?php
/* Copyright (C) 2010-2012 Regis Houssin  <regis@dolibarr.fr>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * or see http://www.gnu.org/
 */

/**
 *	    \file       htdocs/event/lib/event.lib.php
 *		\brief      Tab bar for event object
 *      \ingroup    event
 */
function event_prepare_head($object)
{
	global $langs, $conf, $user, $db;
	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/event/fiche.php',1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("EventSingular");
    $head[$h][2] = 'event';
	$h++;

	if($conf->global->EVENT_EXPIRATION=='1')
	{
		$head[$h][0] = dol_buildpath('/event/registration/relances.php',1).'?eventid='.$object->id;
		$head[$h][1] = $langs->trans("RegistrationExpires");
		$head[$h][2] = 'registration_expire';
		$h++;
	}

	$sql = "SELECT total_ht, price_day FROM llx_event WHERE rowid = ".$object->id;
	$resql = $db->query($sql);
	$res = $resql->fetch_assoc();
	if ($res['total_ht'] != 0)
	{
		$head[$h][0] = dol_buildpath('/event/do_payment.php',1).'?eventid='.$object->id;
		$head[$h][1] = $langs->trans("RegistrationPayments");
		$head[$h][2] = 'registration_payment';
		$h++;
	}
	
	if($conf->global->EVENT_INSCRIPTION_STATEMENT=='1')
	{
		$head[$h][0] = dol_buildpath('/event/orders_state.php',1).'?eventid='.$object->id;
		$head[$h][1] = $langs->trans("RegistrationOrdersState");
		$head[$h][2] = 'registration_orders';
		$h++;
	}

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
	complete_head_from_modules($conf,$langs,$object,$head,$h,'event');

	return $head;
}

/**
 *		\brief      Tab bar for eventday object
 *      \ingroup    event
 */
function eventday_prepare_head($object)
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/event/fiche.php',1).'?id='.$object->fk_event;
	$head[$h][1] = $langs->trans("EventSingular");
	$head[$h][2] = 'event';
	$h++;

	$head[$h][0] = dol_buildpath('/event/day/fiche.php',1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("EventDay");
	$head[$h][2] = 'eventday';
	$h++;

	if($conf->global->EVENT_HIDE_GROUP=='-1')
		{
		$head[$h][0] = dol_buildpath('/event/day/level.php',1).'?dayid='.$object->id;
		$head[$h][1] = $langs->trans("EventLevels");
		$head[$h][2] = 'level';
		$h++;
		}

	$head[$h][0] = dol_buildpath('/event/day/options.php',1).'?dayid='.$object->id;
	$head[$h][1] = $langs->trans("EventOptions");
	$head[$h][2] = 'options';
	$h++;

	$head[$h][0] = dol_buildpath('/event/registration/list.php',1).'?dayid='.$object->id;
	$head[$h][1] = $langs->trans("Registrations");
	$head[$h][2] = 'registration';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
	complete_head_from_modules($conf,$langs,$object,$head,$h,'event');

	return $head;
}


/**
 *
 * @param string $color	color of progress bar (green | orange | red)
 * @param int $percent	filling percent
 * @param string $infos	Text to add into progress bar
 */
function show_progress_bar($color,$percent=0,$infos='') {

	if($percent > 100 )
		$percent = 100;

	$out = '<div class="progress-bar ';
	$out.= $color;
	$out.= '">';
	$out.= '<span style="width: '.$percent.'%"> '.$infos.' </span>';
	$out.= '</div>';

	return $out;
}

/**
 *	Renvoie nombre de relances envoyées pour une inscription
 *
 *	@param		int		$id	            Id inscription
 *	@param		string	$fk_contact		Participant
 *	@return		int                     Nombre de relances envoyées
 */
function getNbReminders($id,$fk_contact)
{
    global $conf,$db;
    $sql = "SELECT count(a.id) as nb";
    $sql.= " FROM ".MAIN_DB_PREFIX."actioncomm as a";
    $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_actioncomm as t ON a.fk_action=t.id";
    $sql.= " WHERE t.code='EVE_REMIND'";
    $sql.= " AND a.elementtype='event_registration'";
    $sql.= " AND a.fk_element=".$id;
    $sql.= " AND a.fk_contact=".$fk_contact;
    $sql.= " AND a.entity = ".$conf->entity;

    dol_syslog("eventday::getNbReminders sql=".$sql);

    $resql=$db->query($sql);
    if ($resql)
    {
        $obj = $db->fetch_object($resql);
        if ($obj) $max = intval($obj->nb);
        else $max=0;
    }
    else
    {
        return -1;
    }
    return $max;
}

function event_admin_prepare_head()
{
    global $langs, $conf;

    $langs->load("event@event");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/event/admin/admin_event.php", 1);
    $head[$h][1] = $langs->trans("EventSetupBackOffice");
    $head[$h][2] = 'EventSetupBackOffice';
    $h++;

	$head[$h][0] = dol_buildpath("/event/admin/admin_public-page.php", 1);
    $head[$h][1] = $langs->trans("EventSetupPagePublic");
    $head[$h][2] = 'EventSetupPagePublic';
    $h++;

	$head[$h][0] = dol_buildpath("/event/admin/registration_extrafields.php", 1);
    $head[$h][1] = $langs->trans("EventSetupExtrafields");
    $head[$h][2] = 'EventSetupExtrafields';
    $h++;

	$head[$h][0] = dol_buildpath("/event/admin/admin_level.php", 1);
    $head[$h][1] = $langs->trans("EventSetupLevel");
    $head[$h][2] = 'EventSetupLevel';
    $h++;

    $head[$h][0] = dol_buildpath("/event/admin/about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'settings');

    return $head;
}

function event_admin_text_prepare_head()
{
    global $langs, $conf;

    $langs->load("event@event");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/event/admin/admin_manage-mail.php", 1);
    $head[$h][1] = $langs->trans("EventSetupTextMail");
    $head[$h][2] = 'EventSetupTextMail';
    $h++;

	$head[$h][0] = dol_buildpath("/event/admin/admin_css-manage.php", 1);
    $head[$h][1] = $langs->trans("EventSetupPageManageCSS");
    $head[$h][2] = 'EventSetupPageManageCSS';
    $h++;

    complete_head_from_modules($conf, $langs, $object, $head, $h, 'settings');

    return $head;
}
