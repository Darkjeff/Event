<?php

/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012-2016		JF FERRY			<jfefe@aternatik.fr>
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
 * 		\ingroup    event
 * 		\brief      Index page of module event
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (!$res && file_exists("../main.inc.php")) {
    $res = include("../main.inc.php");
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = include("../../main.inc.php");
}// for curstom directory
if (!$res) {
     die("Include of main fails");
}

// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
require_once("class/event.class.php");
require_once("class/registration.class.php");
require_once("class/day.class.php");
require_once(DOL_DOCUMENT_ROOT . "/contact/class/contact.class.php");
require_once(DOL_DOCUMENT_ROOT . "/core/class/html.formother.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$arch = GETPOST('arch', 'int');
$year = GETPOST("year", "int") ? GETPOST("year", "int") : date("Y");
$month = GETPOST("month", "int") ? GETPOST("month", "int") : date("m");
$week = GETPOST("week", "int") ? GETPOST("week", "int") : date("W");

$query = GETPOST('query');

if ($conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE == '')
    dolibarr_set_const($db, 'EVENT_REGISTRATION_LIMIT_EXPIRE', 7,'yesno',0,'',$conf->entity);
if ($conf->global->EVENT_LEVEL_DEFAULT_LEVEL_DISPO == '')
    dolibarr_set_const($db, 'EVENT_LEVEL_DEFAULT_LEVEL_DISPO', 0,'',0,'',$conf->entity);

// Protection if external user
if ($user->societe_id > 0) {
    //accessforbidden();
}

if ($action == 'search') {
    if (empty($query) && $query != '0') {
        $mesgs[] = '<div class="error">' . $langs->trans('ErrorRefRegistrationMustBeProvided') . '</div>';
        $action = '';
    }
}

/* * *************************************************
 * VIEW
 *
 * Put here all code to build page
 * ************************************************** */
$help_url = 'EN:Module_Event_/_Booking_EN|FR:Module_Évènements_et_inscriptions|ES:Module_Event_/_Booking_ES';
llxHeader('', $langs->trans("EventGestion"), $help_url);

$form = new Form($db);
$formother = new FormOther($db);
$object = new Event($db);

$now = dol_now();

dol_htmloutput_mesg($mesg, $mesgs);

/*
 * Event list
 */
if ($user->rights->event->read) {
    $sortfield = GETPOST("sortfield", 'alpha');
    $sortorder = GETPOST("sortorder", 'alpha');

    if (!$sortfield) {
        $sortfield = 't.date_start';
    }
    if (!$sortorder) {
        $sortorder = 'ASC';
    }
    $limit = $conf->liste_limit;

    $page = GETPOST("page", 'int');
    if ($page == -1) {
        $page = 0;
    }
    $offset = $limit * $page;
    $pageprev = $page - 1;
    $pagenext = $page + 1;

    $filter = array('t.date_start' => ($year > 0 ? $year : date('Y')));

    $events = $object->fetch_all($sortorder, $sortfield, $limit, $offset, $arch, $filter);
    if ($events < 0) {
        dol_print_error($db, $object->error);
    }

    $i = 0;
    $total = 0;

    if ($arch) {
        $param = '&amp;arch=1';
    }
    if ($year) {
        $param .= '&amp;year=' . $year;
    }

    if($query =='') {
        print_barre_liste($langs->trans('MenuListEvent'), $page, 'index.php', $param, $sortfield, $sortorder, '', $events[0], $events[1], 'img/event_32.png', 1);
        if ($year) {
            $param_link = '&amp;year=' . $year;
        }
        if ($arch) {
            print '<a href="' . $_SERVER['PHPSELF'] . '?arch=0' . $param_link . '">' . $langs->trans('EventListShowActive') . '</a>';
        } else {
            print '<a href="' . $_SERVER['PHPSELF'] . '?arch=1' . $param_link . '">' . $langs->trans('EventListShowClosedToo') . '</a>';
        }

        print '<table width="100%" class="noborder">';
        print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
        print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '" />';
        print '<input type="hidden" name="id" value="' . $object->id . '" />';
        print '<input type="hidden" name="action" value="addcontact" />';

        // Year
        print '<tr class="liste_titre"><td align="left">' . $langs->trans("Year") . '</td><td align="left">';

        print $formother->select_year($year, 'year');
        print '</td>';

        print '<td colspan="6">';
        print '<input type="submit" name="filter_year" value="' . $langs->trans('Search') . '" />';
        print '</td>';

        print '</tr>';
        print '</form>';
        print '</table>';

        print '<table width="100%" class="noborder">';

        print '<tr class="liste_titre" >';
        print_liste_field_titre($langs->trans('Status'), $_SERVER["PHP_SELF"], 't.fk_statut', '', $param, '', $sortfield, $sortorder);
        print_liste_field_titre($langs->trans('Label'), $_SERVER["PHP_SELF"], 't.label', '', $param, '', $sortfield, $sortorder);
        print_liste_field_titre($langs->trans('DateStart'), $_SERVER["PHP_SELF"], 't.date_start', '', $param, '', $sortfield, $sortorder);
        print_liste_field_titre($langs->trans('DateEnd'), $_SERVER["PHP_SELF"], 't.date_end', '', $param, '', $sortfield, $sortorder);

        if (!$socid) {
            print_liste_field_titre($langs->trans('Customer'), $_SERVER["PHP_SELF"], 't.fk_soc', '', $param, '', $sortfield, $sortorder);
        }
        print_liste_field_titre($langs->trans('Nb journée(s)'), '', '', '', $param, '', $sortfield, $sortorder);
        print_liste_field_titre($langs->trans('NumberRegistrationShort'), '', '', '', $param, '', $sortfield, $sortorder);
        print '</tr>';
        if (count($object->line) > 0) {

            // Tableau des journées
            foreach ($object->line as $eventday) {

                $societe = new Societe($db);
                $societe->fetch($eventday->socid);
                $var = !$var;

                $event = new Event($db);

                $event->id = $eventday->id;
                $event->ref = $eventday->ref;
                $event->label = $eventday->label;
                $event->fk_statut = $eventday->fk_statut;
                print "<tr $bc[$var]>";

                print '<td width="5%">' . $event->getLibStatut(3) . '</td>';

                // Link to event
                print '<td>' . $event->getNomUrl(1) . '</td>';

                // Start date
                print '<td>' . dol_print_date($eventday->date_start) . '</td>';

                // End date
                print '<td>' . dol_print_date($eventday->date_end) . '</td>';

                // Customer
                if (!$socid) {
                    print '<td>';
                    if ($eventday->socid > 0) {
                        print $societe->getNomUrl(1);
                    }

                    print'</td>';
                }

                // Nb day
                print '<td>'.$event->get_nb_days($event->id).'</td>';

                // Nb invitation
                print '<td>';
                print img_picto($langs->trans('Draft'),'statut0') . ' ' . $event->countRegistrationForEvent('0');
                print ' ' . img_picto($langs->trans('Waited'),'statut3') . ' ' . $event->countRegistrationForEvent('1');
                print ' ' . img_picto($langs->trans('Queued'),'statut1') . ' ' . $event->countRegistrationForEvent('8');
                print ' ' . img_picto($langs->trans('Confirmed'),'statut4') . ' ' . $event->countRegistrationForEvent('4');
                print ' ' . img_picto($langs->trans('Cancelled'),'statut8') . ' ' . $event->countRegistrationForEvent('5');

                print '</td>';

                print '</tr>';
                $i++;
            }
            echo "</table>";
        } else {
            print '<div class="warning">' . $langs->Trans('NoEventRegistered') . '</div>';
        }
    }

    /*
     * Registration search form
     */
    print '<br />';

    print_fiche_titre($langs->trans('RegistrationSearch'), '', 'event_registration@event');
    $htmlinputname = 'query';
    print '<p>' . $langs->trans('RegistrationSearchHelp') . '</p>';
    $ret.='<form action="' . dol_buildpath('/event/index.php', 1) . '" method="post">';
    $ret.='<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    $ret.='<input type="hidden" name="action" value="search">';

    $ret.='<input type="text" class="flat" ';
    if (!empty($conf->global->MAIN_HTML5_PLACEHOLDER)) {
        $ret.=' placeholder="' . $langs->trans("SearchOf") . ' ' . strip_tags($title) . '"';
    } else {
        $ret.=' title="' . $langs->trans("SearchOf") . ' ' . strip_tags($title) . '"';
    }
    if ($query) {
        $ret.=' value="' . $query . '"';
    }
    $ret.=' name="' . $htmlinputname . '" size="10" />&nbsp;';
    $ret.='<input type="submit" class="button" value="' . $langs->trans("Search") . '">';
    $ret.="</form>\n";
    print $ret;
    print '<br />';

    if ($action == "search") {
        $registration = new Registration($db);
        $result = $registration->search_by_ref_or_id($query,'',GETPOST('eventday'));

        // print_fiche_titre($langs->trans('RegistrationSearchResults', $query), '', 'event_registration@event');
        print_barre_liste($langs->trans('RegistrationSearchResults', $query), $page, 'index.php', $param, $sortfield, $sortorder, '', $num, $result, 'img/event_registration.png', 1);

        if ($result > 0) {
            $i = 0;
            print '<table width="100%" class="border">';
            print '<tr class="liste_titre">';
            print '<th width="3%" align="center">'.$langs->trans('Status').'</th>';
            print '<th width="17%">'.$langs->trans('UserRegistrationInfos').'</th>';
            print '<th width="20%">'.$langs->trans('Event').'</th>';
            print '<th width="20%">'.$langs->trans('EventDay').'</th>';
            if($conf->global->EVENT_HIDE_GROUP=='-1') print '<th width="10%">'.$langs->trans('Group').'</th>';
            print '<th width="11%">'.$langs->trans('RegistrationDate').'</th>';
            print '<th width="14%">'.$langs->trans('ConfirmationDate').'</th>';
            print '<th width="5%">'.$langs->trans('Paid').'</th>';
            print '<th width="5%">'.$langs->trans('Edit').'</th>';

            print '</tr>';

            while ($i < $result) {
                print '<tr>';
                $registrationstat = new Registration(db);
                $registrationstat->id = $registration->line[$i]->id;
              
                // Status
                print '<td align="center">'.$registration->LibStatut($registration->line[$i]->fk_statut,3).'</td>';

                // Nom Prénom
                print '<td>';
                if ($registration->line[$i]->fk_user_registered > 0) {
                    $contactstat = new Contact($db);
                    $res = $contactstat->fetch($registration->line[$i]->fk_user_registered);
                    print $contactstat->getNomUrl(1);
                }
                print '</td>';

                // Event
                print '<td>';
                $object->fetch($registration->line[$i]->fk_event);
                print $object->getNomUrl(1);
                print '</td>';

                // Day
                print '<td>';
                $registration_day = new Day($db);
                $registration_day->fetch($registration->line[$i]->fk_eventday);
                print $registration_day->getNomUrl(1);
                print '</td>';

                // Groupe
                if($conf->global->EVENT_HIDE_GROUP=='-1') print '<td>'.$registration->line[$i]->eventday_label_level.'</td>';

                // Date Invitation
                print '<td>'.$registration->line[$i]->datec.'</td>';

                // Date Validate
                print '<td>'.$registration->line[$i]->date_valid.'</td>';

                // Paid
                print '<td align="center">';
                $paid = $registration->line[$i]->paye > 0 ? "on":"off";
                $trans_paid = $registration->line[$i]->paye > 0 ? "AlreadyPaid":"BillStatusNotPaid";
                print img_picto($langs->trans($trans_paid),$paid);
                print '</td>';

                // Actions
                print '<td align="center">';

                // Confirm
                if ($registration->line[$i]->fk_statut == 1) {
                    print ' <a href="'.DOL_URL_ROOT.'/custom/event/registration/list.php?dayid='.$event->id.'&amp;action=confirm&id='.$registration->line[$i]->id.'">'.img_picto('Confirm','tick').'</a>&nbsp;';
                }
                // si au moins validé -> afficher PDF
                elseif ($registration->line[$i]->ref && $conf->global->EVENT_HIDE_PDF_BILL=='0') {
                    $filename=dol_sanitizeFileName($registration->ref);
                    $file = $filename.'/'.$filename.'.pdf';
                    $dir=$conf->event->dir_output.'/';
                    if (is_file($dir.$file))
                    {
                        print '<a href="'.DOL_URL_ROOT.'/document.php?modulepart=event&amp;file='.$file.'" alt="'.$legende.'" title="'.$langs->trans('DownloadRegistrationTicket').'">';
                        print img_picto('','pdf3');
                        print '</a>';
                    }
                }
                else print img_picto_common('','transparent','height="16" width="16"').'&nbsp;';
                
                // Visu
                print '<a href="registration/fiche.php?id='.$registration->line[$i]->id.'">'.img_picto('View','detail').'</a>';
                
                // Delete
                print ' <a href="'.DOL_URL_ROOT.'/custom/event/registration/list.php?dayid='.$event->id.'&amp;id='.$registration->line[$i]->id.'&action=delete">'.img_picto($langs->trans("Delete"),'delete').'</a>';

                print '</td>';
                print '</tr>';

                $i++;
            }

            print '</table>';
        } else {
            print $registration->error;
        }
    }
} else {
    accessforbidden();
}

// End of page
llxFooter();
$db->close();
