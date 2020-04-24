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
 *   	\file       event/index.php
 *		\ingroup    event
 *		\brief      Index page of module event
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/eventlevel.class.php");
require_once("../class/eventlevel_cal.class.php");
require_once("../class/html.formevent.class.php");
require_once("../lib/event.lib.php");
require_once("./fonctions.php");

require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';


// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

// Get parameters
$id			= GETPOST('id','int');
$ref		= GETPOST('ref','alpha');
$dayid		= GETPOST('dayid','int') ? GETPOST('dayid','int') : GETPOST('fk_event_day');
$fk_level	= GETPOST('fk_level','int');
$action		= GETPOST('action','alpha');

$confirm	= GETPOST('confirm','alpha');

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

// Modifications des groupes disponibles pour la journée
if($action == "update_levels" && is_array(GETPOST('level')) )
{
	$jID = GETPOST('jID','int');

	$level = new Eventlevel($db);
	// Enregistrement des groupes pour la journee
	if($level->raz_level($dayid)  ) {
		foreach(GETPOST('level') as $levelid) {
			$perso_res = $level->DefLevelForDay($levelid,$dayid);
		}
		$mesg = '<div class="ok">'.$langs->trans('LevelUpdatedSuccess').'</div>';
	}
	else
	{
		$langs->load("errors");
		$mesg='<div class="error">'.$langs->trans($object->error).'</div>';
		$action = '';
	}
}

// Update nombre de place et si full
if($action == "updateplace")
{
	$level = new Eventlevel($db);
	$level->id = $id;
	$level->place = GETPOST('place','int');
	$level->full = GETPOST('full','int');

	// Mise à jour des paramètres pour le groupe
	if($level->setLevel($dayid) > 0) {
		setEventMessage($langs->trans('LevelModifiedSuccess'));
	}
	else setEventMessage($langs->trans('NotModified'),'errors');

}

if($action == "edit") {

	if ($_POST ["period_add_x"]) {
		$error = 0;
		$error_message = '';

		// From template
		$idtemplate_array = GETPOST ( 'fromtemplate' );
		if (is_array ( $idtemplate_array )) {
			foreach ( $idtemplate_array as $idtemplate ) {

				$eventcal = new Eventlevel_cal ( $db );

				$eventcal->fk_event_day = GETPOST ( 'fk_event_day', 'int' );
				$eventcal->fk_level = GETPOST ( 'fk_level', 'int' );
				$eventcal->date_session = dol_mktime ( 0, 0, 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );

				$tmpl_calendar = new Eventlevel_cal ( $db );
				$result = $tmpl_calendar->fetch ( $idtemplate );
				$tmpldate = dol_mktime ( 0, 0, 0, GETPOST ( 'datetmplmonth', 'int' ), GETPOST ( 'datetmplday', 'int' ), GETPOST ( 'datetmplyear', 'int' ) );
				if ($tmpl_calendar->date_session != 1) {
					$tmpldate = dol_time_plus_duree ( $tmpldate, (($tmpl_calendar->date_session) - 1), 'd' );
				}

				$eventcal->date_session = $tmpldate;

				$heure_tmp_arr = explode ( ':', $tmpl_calendar->heured );
				$eventcal->heured = dol_mktime ( $heure_tmp_arr [0], $heure_tmp_arr [1], 0, dol_print_date ( $eventcal->date_session, "%m" ), dol_print_date ( $eventcal->date_session, "%d" ), dol_print_date ( $eventcal->date_session, "%Y" ) );

				$heure_tmp_arr = explode ( ':', $tmpl_calendar->heuref );
				$eventcal->heuref = dol_mktime ( $heure_tmp_arr [0], $heure_tmp_arr [1], 0, dol_print_date ( $eventcal->date_session, "%m" ), dol_print_date ( $eventcal->date_session, "%d" ), dol_print_date ( $eventcal->date_session, "%Y" ) );

				$result = $eventcal->create ( $user );
				if ($result < 0) {
					$error ++;
					$error_message .= $eventcal->error;
				}
			}
		} else {
			$eventcal = new Eventlevel_cal ( $db );

			$eventcal->fk_level = GETPOST ( 'fk_level', 'int' );
			$eventcal->fk_event_day = GETPOST ( 'fk_event_day', 'int' );
			$eventcal->date_session = dol_mktime ( 0, 0, 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );

			// From calendar selection
			$heure_tmp_arr = array ();

			$heured_tmp = GETPOST ( 'dated', 'alpha' );
			if (! empty ( $heured_tmp )) {
				$heure_tmp_arr = explode ( ':', $heured_tmp );
				$eventcal->heured = dol_mktime ( $heure_tmp_arr [0], $heure_tmp_arr [1], 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );
			}

			$heuref_tmp = GETPOST ( 'datef', 'alpha' );
			if (! empty ( $heuref_tmp )) {
				$heure_tmp_arr = explode ( ':', $heuref_tmp );
				$eventcal->heuref = dol_mktime ( $heure_tmp_arr [0], $heure_tmp_arr [1], 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );
			}
			$result = $eventcal->create ( $user );
			if ($result < 0) {
				$error ++;
				$error_message = $eventcal->error;
			}
		}

		if (! $error) {
			Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?dayid=" . $dayid );
			exit ();
		} else {
			setEventMessage ( $error_message, 'errors' );
		}
	}

	if ($_POST ["period_update_x"]) {
		$modperiod = GETPOST ( 'modperiod', 'int' );
		$date_session = dol_mktime ( 0, 0, 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );

		$heure_tmp_arr = array ();

		$heured_tmp = GETPOST ( 'dated', 'alpha' );
		if (! empty ( $heured_tmp )) {
			$heure_tmp_arr = explode ( ':', $heured_tmp );
			$heured = dol_mktime ( $heure_tmp_arr [0], $heure_tmp_arr [1], 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );
		}

		$heuref_tmp = GETPOST ( 'datef', 'alpha' );
		if (! empty ( $heuref_tmp )) {
			$heure_tmp_arr = explode ( ':', $heuref_tmp );
			$heuref = dol_mktime ( $heure_tmp_arr [0], $heure_tmp_arr [1], 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );
		}

		$calendar = new Eventlevel_cal ( $db );
		$result = $calendar->fetch ( $modperiod );

		if (! empty ( $modperiod ))
			$calendar->id = $modperiod;
		if (! empty ( $date_session ))
			$calendar->date_session = $date_session;
		if (! empty ( $heured ))
			$calendar->heured = $heured;
		if (! empty ( $heuref ))
			$calendar->heuref = $heuref;

		$result = $calendar->update ( $user );

		if ($result > 0) {
			Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?dayid=" . $fk_event_day );
			exit ();
		} else {
			setEventMessage ( $calendar->error, 'errors' );
		}
	}

}


if ($action == 'confirm_delete_period' && $confirm == "yes" && $user->rights->event->write) {
	$modperiod = GETPOST ( 'modperiod', 'int' );

	$calendar = new Eventlevel_cal ( $db );
	$result = $calendar->remove ( $modperiod );

	if ($result > 0) {
		setEventMessage($langs->trans('EventPeriodSuccessfullyDeleted'));
		Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?dayid=" . $dayid );
		exit ();
	} else {
		setEventMessage ( $calendar->error, 'errors' );
	}
}

/*
 * Update calendar
 * perso
 */
if ($_POST ["period_update_x"]) {
	$modperiod = GETPOST ( 'modperiod', 'int' );
	$date_session = dol_mktime ( 0, 0, 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );

	$heure_tmp_arr = array ();

	$heured_tmp = GETPOST ( 'dated', 'alpha' );
	if (! empty ( $heured_tmp )) {
		$heure_tmp_arr = explode ( ':', $heured_tmp );
		$heured = dol_mktime ( $heure_tmp_arr [0], $heure_tmp_arr [1], 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );
	}

	$heuref_tmp = GETPOST ( 'datef', 'alpha' );
	if (! empty ( $heuref_tmp )) {
		$heure_tmp_arr = explode ( ':', $heuref_tmp );
		$heuref = dol_mktime ( $heure_tmp_arr [0], $heure_tmp_arr [1], 0, GETPOST ( 'datemonth', 'int' ), GETPOST ( 'dateday', 'int' ), GETPOST ( 'dateyear', 'int' ) );
	}

	$calendar = new Eventlevel_cal ( $db );
	$result = $calendar->fetch ( $modperiod );

	if (! empty ( $modperiod ))
		$calendar->id = $modperiod;
	if (! empty ( $date_session ))
		$calendar->date_session = $date_session;
	if (! empty ( $heured ))
		$calendar->heured = $heured;
	if (! empty ( $heuref ))
		$calendar->heuref = $heuref;

	$result = $calendar->update ( $user );

	if ($result > 0) {
		Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?dayid=" . $dayid );
		exit ();
	} else {
		setEventMessage ( $calendar->error, 'errors' );
	}
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans("LevelGestion"),'');

$form=new Form($db);
$formevent=new FormEvent($db);

if ( ($dayid || !empty($ref))  && $user->rights->event->read )
{
	$event = new Day($db);
	$event->fetch($dayid,$ref);
	$dayid=$event->id;
	$head = eventday_prepare_head($event);
	dol_fiche_head($head, 'level', $langs->trans("EventDay"),0,($object->public?'event@event':'event@event'));
	
	
         ///////////////////:: recherche jour pecedent jour suivant /////////////////////////////////
		 $js = jour_suivant($db,$event->fk_event,dol_print_date($event->date_event, '%Y-%m-%d'));
		 $jp = jour_precedent($db,$event->fk_event,dol_print_date($event->date_event, '%Y-%m-%d'));
		 
         //////////////////////////////////////////////////////////////////			

		   ?><div style="vertical-align: middle">
					<div class="pagination paginationref">
						<ul class="right">
						<!--<li class="noborder litext">
						<a href="/dolibarr/societe/list.php?restore_lastsearch_values=1">Retour liste</a>
						</li>-->
						<?php 
						if($jp!='')
							echo '<li class="pagination"><a accesskey="p" href="'.DOL_URL_ROOT.'/custom/event/day/level.php?dayid='.$jp.'"><i class="fa fa-chevron-left"></i></a></li>';
						else
							echo '<li class="pagination"><span class="inactive"><i class="fa fa-chevron-left opacitymedium"></i></span></li>';
						if($js!='')
							echo '<li class="pagination"><a accesskey="p" href="'.DOL_URL_ROOT.'/custom/event/day/level.php?dayid='.$js.'"><i class="fa fa-chevron-right"></i></a></li>';
						else
							echo '<li class="pagination"><span class="inactive"><i class="fa fa-chevron-right opacitymedium"></i></span></li>';
						?>
						</ul></div>
				</div>
				
		  <?php
	
	print '<table class="border" width="100%">';

	// Label
	print '<tr><td valign="top">';
	print $langs->trans("LabelDay");
	print '</td><td>';
	print $event->label;
	print "</td></tr>";

	// Third party
	if ($object->socid > 0)
	{
		print '<tr><td>'.$langs->trans("Company").'</td><td>';
		print $soc->getNomUrl(1);
		print '</td></tr>';
	}

	// Date
	$dayofweek = strftime("%w",$event->date_event);
	print '<tr><td>'.$langs->trans("EventDayDate").'</td><td>'. $langs->trans("Day".$dayofweek) . ' ' . dol_print_date($event->date_event,'daytext').'</td>';
	print '</tr>';

	// Statut
	print '<tr><td>'.$langs->trans("Status").'</td><td>'.$event->getLibStatut(4).'</td></tr>';

	// Inscription ouverte oui/non
	print '<tr><td>'.$langs->trans("RegistrationIsOpen").'</td>';
	// print $event->registration_open;
	if ($event->registration_open > 0) {
                print '<td>';
                print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=close_registration">';
                print img_picto($langs->trans("Activated"), 'switch_on');
                print '</a></td>' . "\n";
            } else {
                print '<td>';
                print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=open_registration">';
                print img_picto($langs->trans("Desactivated"), 'switch_off');
                print '</a></td>' . "\n";
            }
    print '</tr>';

	print '</table>';
	print '</div>';

	print_titre($langs->trans('LevelGestionByDay'));
	/*
	 * Select level for this day
	 */
	print '<br />';
	print_fiche_titre($langs->trans('LevelAvailableForThisDay'));
	print '<div class="warning">'.$langs->trans('InfoLevelConfigWillErased').'</div>';
	print '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
	print '<input type="hidden" name="dayid" value="'.$dayid.'" />';

	$sql_level = "SELECT rowid, label, description, rang, statut  FROM ".MAIN_DB_PREFIX."event_level ORDER BY rang ASC";
	$resql=$db->query($sql_level);
	if ($resql) {
		$num = $db->num_rows($resql);
		if ($num) {
			$i = 0;
			// Affichage des dates
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);

				$sql_groupe_journee = "SELECT place, full, fk_event FROM ".MAIN_DB_PREFIX."event_level_day WHERE fk_eventday='".$dayid."' AND fk_level='".$obj->rowid."';";
				$resql2=$db->query($sql_groupe_journee);
				if ($resql2) {
					$num2 = $db->num_rows($resql2);
					if ($num2 == "1")
					{
						$check = "checked=\"checked\"";
					}
					else
					{
						$check ="";
					}
					echo "<p><input type=\"checkbox\" name=\"level[]\" value=\"".$obj->rowid."\" ".$check." />&nbsp;<strong>".$obj->label."</strong> - <i> ".$obj->description."</i></p>";
				}

				$i++;
			}

			print '<input type="hidden" name="action" value="update_levels" />';
			print '<br /><p><input type="submit" class="button" value="'.$langs->trans('Save').'" /></p><br />';
			print '</form>';
		}
	}


	/*
	 * Level for this day
	 */
	$sql = "SELECT l.rowid as levelid, ld.rowid, l.label, ld.place, ld.full FROM ".MAIN_DB_PREFIX."event_level as l,".MAIN_DB_PREFIX."event_level_day as ld WHERE fk_eventday = '".$dayid."' AND l.rowid=ld.fk_level;";
	$resql=$db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		if ($num) {
			print_fiche_titre($langs->trans('LevelParameters')).'<br />';
			$i = 0;
			print '<table <table width="100%" class="noborder">
			<tr class="liste_titre">
			<th>'.$langs->trans('EventLevel').'</th>
			<th>'.$langs->trans('PlaceAvailable').'</th>
			<th>'.$langs->trans('NbRegistered').'</th>
			<th>'.$langs->trans('LevelFull').'</th>
			<th>'.$langs->trans('Edit').'</th>
			</tr>';

			// On boucle pour chaque groupe de la journée
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);

				if($action == "editplace" && $id == $obj->rowid) {

					$selected = "";

					print '<form action="level.php" method="post">';
					print '<tr>';
					print '<td>'.$obj->label.'</td>';
					print '<td><input type="text" name="place" value="'.$obj->place .'" size="5" /></td>';
					print '<td>';
					$level=new Eventlevel($db);
					print $level->countRegistrationForLevel($dayid,$obj->levelid);
					print '</td>';
					print '<td>';
					$selected = ($obj->full == "1") ? 'checked="checked"' : "";
					print '<input type="radio" name="full" value="1" '.$selected.' /> Oui';
					$selected = ($obj->full == "0") ? 'checked="checked"' : "";
					print '<input type="radio" name="full" value="0" '.$selected.' /> Non </td>';
					print '<td><input type="submit" class="button" value="Enregistrer"  /></td>';
					print '<input type="hidden" name="action" value="updateplace"  />';
					print '<input type="hidden" name="dayid" value="'.$dayid.'" />';
					print '<input type="hidden" name="id" value="'.$obj->rowid.'" />';
					print '</tr>';
					print '</form>';

				}
				else  {
					print '<tr>';
					print '<td>'.$obj->label.'</td>';
					print '<td>'.$obj->place.'</td>';
					// Calcul nombre d'inscrits
					print '<td>';
					$groupe=new Eventlevel($db);
					$groupe->countRegistrationForLevel($dayid,$obj->levelid);

					// Brouillon
					print img_picto($langs->trans('Draft'),'statut0').' '.$groupe->countRegistrationForLevel($dayid,$obj->levelid,0);
					// Valid
					print ' '.img_picto($langs->trans('Validated'),'statut1').' '.$groupe->countRegistrationForLevel($dayid,$obj->levelid,1);
					// Confirmed
					print ' '.img_picto($langs->trans('Confirmed'),'statut4').' '.$groupe->countRegistrationForLevel($dayid,$obj->levelid,4);
					// Waiting
					print ' '.img_picto($langs->trans('Waiting'),'statut8').' '.$groupe->countRegistrationForLevel($dayid,$obj->levelid,8);
					// Cancelled
					print ' '.img_picto($langs->trans('Cancelled'),'statut5').' '.$groupe->countRegistrationForLevel($dayid,$obj->levelid,5);
					print '</td>';

					print '<td>';
					print ($obj->full == "1" ? "Oui" : "Non") ;
					print '</td>';
					print '<td><a href="'.$_SERVER['PHP_SELF'].'?action=editplace&dayid='.$dayid.'&id='.$obj->rowid.'">'.img_picto('','edit').'&nbsp;</a>';
					print ' <a href="'.$_SERVER['PHP_SELF'].'?action=set_timing&dayid='.$dayid.'&id='.$obj->rowid.'&fk_level='.$obj->levelid.'">'.img_picto($langs->trans('EventButtonPeriods'),'edit_add').'&nbsp;</a>';
					print '</td>';
					print '</tr>';
				}
			$i++;
			}
			echo "</table>";
		}

	}


    /*
     * Set timing for level
     */
    $newperiod = GETPOST ( 'newperiod', 'int' );

    /*
    * Calendar management
    */
    print '<br />';
    print_fiche_titre( $langs->trans ( "EventCalendar" ));

    /*
     * Confirm delete calendar
     */
    if ($_POST ["period_remove_x"]) {
        $ret = $form->form_confirm ( $_SERVER ['PHP_SELF'] . '?modperiod=' . $_POST ["modperiod"] . '&dayid=' . $event->id.'&fk_level='.$fk_level, $langs->trans ( "EventDeletePeriod" ), $langs->trans ( "EventDeletePeriodConfirm" ), "confirm_delete_period", '', '', 1 );
        if ($ret == 'html')
            print '<br>';
    }


    $sql = "SELECT l.rowid as levelid, ld.rowid, l.label, ld.place, ld.full FROM ".MAIN_DB_PREFIX."event_level as l,".MAIN_DB_PREFIX."event_level_day as ld WHERE fk_eventday = '".$event->id."' AND l.rowid=ld.fk_level;";
	$resql=$db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		if ($num) {
            $j = 0;
            // On boucle pour chaque groupe de la journée
            while ($j < $num)
            {
                $obj = $db->fetch_object($resql);

                $calendrier = new Eventlevel_cal ( $db );
                $calendrier->fetch_all ( $obj->levelid, $event->id );
                $blocNumber = count ( $calendrier->lines );

                print_titre($obj->label);

                if ($blocNumber < 1 && ! (empty ( $newperiod ))) {
                    print '<div class="tagtr">';
                    print '<div>' . $langs->trans ( "EventNoCalendar" ) . '</div>';
                    print '</div>';
                } else {
                    $old_date = 0;
                    $duree = 0;

                    for($i = 0; $i < $blocNumber; $i ++) {
                        print '&nbsp;';
                        print '<form name="obj_update_' . $i . '" action="' . $_SERVER ['PHP_SELF'] . '"  method="POST">' . "\n";
                        print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
                        print '<input type="hidden" name="action" value="set_timing">' . "\n";
                        print '<input type="hidden" name="fk_event_day" value="' . $calendrier->lines [$i]->fk_event_day . '">' . "\n";
                        print '<input type="hidden" name="fk_level" value="' . $calendrier->lines [$i]->fk_level . '">' . "\n";
                        print '<input type="hidden" name="modperiod" value="' . $calendrier->lines [$i]->id . '">' . "\n";
                        print '<div class="tagtable centpercent noborder noshadow allwidth">';

                        if ($calendrier->lines [$i]->id == $_POST ["modperiod"] && $_POST ["period_remove_x"]) {
                            print '<div class="tagtr" style="background-color: #d5baa8">' . "\n";
                        } else {
                            print '<div class="tagtr">' . "\n";
                        }

                        if ($calendrier->lines [$i]->id == $_POST ["modperiod"] && ! $_POST ["period_remove_x"]) {
                        	//Empécher d'afficher l'ajout de périodes
                        	$action='';
                            print '<div class="tagtd" style="width:20%">' . $langs->trans ( "EventPeriodDate" ) . ' ';
                            $form->select_date ( $calendrier->lines [$i]->date_session, 'date', '', '', '', 'obj_update_' . $i );
                            print '</div>';

                            print '<div class="tagtd">' . $langs->trans ( "EventPeriodTimeB" ) . ' ';
                            //Selection de la date
                            print $formevent->select_time ( dol_print_date ( $calendrier->lines [$i]->heured, 'hour' ), 'dated' );
                            print ' - ' . $langs->trans ( "EventPeriodTimeE" ) . ' ';
                            print $formevent->select_time ( dol_print_date ( $calendrier->lines [$i]->heuref, 'hour' ), 'datef' );
                            print '</div>';

                            //Add
                            print '<div class="tagtd">';
                            if ($user->rights->event->write) {
                                // print '<input type="button" class="button" value='.$langs->trans("Save").' name="period_update" action="submit">';
                                print '<input type="image" class="button" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/edit_add.png" border="0" name="period_update" alt="' . $langs->trans ( "Save" ) . '">';
                            }
                            print '</div>';
                        } else {
                            print '<div class="tagtd" style="width:33%">&nbsp;' . dol_print_date ( $calendrier->lines [$i]->date_session, 'daytext' ) . '</div>';
                            print '<div class="tagtd" style="width:33%"><i>'.$langs->trans('Plagehoraire').' :</i> '. dol_print_date ( $calendrier->lines [$i]->heured, 'hour' ) . ' - ' . dol_print_date ( $calendrier->lines [$i]->heuref, 'hour' ).'</div>';
                            $actionstat = new Actioncomm($db);
                            // print '<div class="tagtd"  style="width:20%">';
                            // if($actionstat->fetch($calendrier->lines[$i]->fk_actioncomm) > 0) {
                            //     print $actionstat->getNomUrl(1);
                            // } else {
                            //     print $langs->trans('None');
                            // }
                            // print '</div>';

                            print '<div class="tagtd" style="width:33%">';
                            if ($user->rights->event->write) {
                                print '<input type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/edit.png" border="0" name="period_edit" alt="' . $langs->trans ( "Edit" ) . '">';
                            }
                            print '&nbsp;';
                            if ($user->rights->event->write) {
                                print '<input type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/delete.png" border="0" name="period_remove" alt="' . $langs->trans ( "Save" ) . '">';
                            }
                            print '</div>';
                        }
                        // We calculated the total session duration time
                        $duree += ($calendrier->lines [$i]->heuref - $calendrier->lines [$i]->heured);

                        print "</div><!-- / tagtr -->";
                        print "</div><!-- / tagtable -->";
                        print '</form>' . "\n";
                    }

                    if($blocNumber === 0) {
                        print '<div class="info">&nbsp;' . $langs->trans ( "EventNoPlannedTiming" ) . '</div>';
                    }
                    print '<br />';
                }
                $j++;
            }
        }
    }

    if($action == 'set_timing') {

        print_titre($langs->trans('EventLevelSetTiming'));

		if (! empty ( $newperiod )) {
			print '<form name="newperiod" action="' . $_SERVER ['PHP_SELF'] . '?action=set_timing&dayid=' . $fk_event_day . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="newperiod" value="1">' . "\n";
			print '<table style="border:0;" width="100%">';
			print '<tr><td align="right">';
			print '<input type="submit" class="butAction" value="' . $langs->trans ( "EventPeriodAdd" ) . '">';
			print '</td></tr>';
			print '</table>';
			print '</form>';
		} else {

			print '<form name="obj_update_' . ($i + 1) . '" action="' . $_SERVER ['PHP_SELF'] . '?action=edit&dayid=' . $fk_event_day . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="fk_event_day" value="' . $event->id . '">' . "\n";
			print '<input type="hidden" name="fk_level" value="' . $fk_level . '">' . "\n";
			print '<input type="hidden" id="datetmplday"   name="datetmplday"   value="' . dol_print_date ( $event->date_event, "%d" ) . '">' . "\n";
			print '<input type="hidden" id="datetmplmonth" name="datetmplmonth" value="' . dol_print_date ( $event->date_event, "%m" ) . '">' . "\n";
			print '<input type="hidden" id="datetmplyear"  name="datetmplyear"  value="' . dol_print_date ( $event->date_event, "%Y" ) . '">' . "\n";


            print '<div class="tagtable centpercent noborder noshadow allwidth">';
			print '<div class="tagtr">';

			print '<div class="tagtd">' . $langs->trans ( "EventPeriodDate" ) . ' ';
			//print ': '.dol_print_date($event->date_event);
			 $form->select_date ( $event->date_event, 'date', '', '', '', 'newperiod' );
			print '</div>';
			print '<div class="tagtd">' . $langs->trans ( "EventPeriodTimeB" ) . ' ';
			print $formevent->select_time ( '08:00', 'dated' );
			print '</div>';
			print '<div class="tagtd">' . $langs->trans ( "EventPeriodTimeE" ) . ' ';
			print $formevent->select_time ( '18:00', 'datef' );
			print '</div>';
			if ($user->rights->event->write) {
				print '<div class="tagtd"><input type="image" class="button" src="' . dol_buildpath ( "/theme/".$conf->theme."/img/edit_add.png", 1 ) . '" border="0" align="absmiddle" name="period_add" alt="' . $langs->trans ( "Save" ) . '" "></div>';
			}
            print '</div>' . "\n";
            print '</div>';
            print '</form>';
		}
	}
}

llxFooter ();
$db->close ();
