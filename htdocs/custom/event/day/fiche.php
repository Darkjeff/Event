<?php

/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012-2016	JF FERRY			<jfefe@aternatik.fr>
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
 *   	\file       event/day/fiche.php
 * 		\ingroup    event
 * 		\brief      manage days of an event
 */
//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');	// If there is no menu to show
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');	// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');		// If this page is public (can be called outside logged session)
// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (!$res && file_exists("../../main.inc.php")) {
    $res = include("../../main.inc.php");
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = include("../../../main.inc.php");
}// for curstom directory

if (!$res) {
     die("Include of main fails");
}
// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../lib/event.lib.php");
require_once("../class/eventlevel_cal.class.php");
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once("./fonctions.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("bills");
$langs->load("event@event");

// Get parameters
$id = GETPOST('id', 'int');
$eventid = GETPOST('eventid', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action');
$confirm = GETPOST('confirm');

// Security check
if ($user->societe_id) {
    $socid = $user->societe_id;
}
$result = restrictedArea($user, 'event', $id, 'event_day', 'day');

$event = new Event($db);
$object = new Day($db);


/*
 * Actions
 */
if ($action == 'add' && $user->rights->event->write) {

    $error = 0;
    $mesg = '';

    if (!GETPOST("label", 'alpha')) {
        $mesg_errors[] = $langs->trans("ErrorFieldRequired", $langs->transnoentities("Label"));
        $error++;
    }

    if (!GETPOST("date_event", 'alpha')) {
        $mesg_errors[] = $langs->trans("ErrorFieldRequired", $langs->transnoentities("Date"));
        $error++;
    }

    if (GETPOST("price", 'alpha') == NULL) {
        $mesg_errors[] = $langs->trans("ErrorFieldRequired", $langs->transnoentities("DayPriceHt"));
        $error++;
    }

    if (GETPOST("time_start") == NULL) {
        $mesg_errors[] = $langs->trans("ErrorFieldRequired", $langs->transnoentities("TimeStart"));
        $error++;
    }

    if (GETPOST("time_end") == NULL) {
        $mesg_errors[] = $langs->trans("ErrorFieldRequired", $langs->transnoentities("TimeEnd"));
        $error++;
    }


    if (!$error) {
        $object->ref = $ref;
        $object->label = GETPOST("label");
        $object->fk_event = GETPOST("eventid");
        $object->fk_soc = GETPOST("socid");
        $object->description = GETPOST("description");
        $object->description_web = GETPOST("description_web");
        $object->note_public = GETPOST("note_public");
        $object->note = GETPOST("note");
        $object->datec = dol_now();
        $object->date_event = dol_mktime(12, 0, 0, GETPOST('date_eventmonth'), GETPOST('date_eventday'), GETPOST('date_eventyear'));
        $object->time_start = GETPOST('time_start')?GETPOST('time_start'):"09:00:00";
        $object->time_end = GETPOST('time_end')?GETPOST('time_end'):"17:00:00";
        $object->price_base_type = GETPOST("price_base_type");
        $object->tva_tx = GETPOST("tva_tx");
        $object->registration_open = GETPOST("registration_open");
        $pric_base_type = GETPOST('price_base_type');
        if ($price_base_type == 'TTC') {
            print 'ttc';
            $object->total_ttc = price2num(GETPOST("price"), 'MU');
            $object->total_ht = price2num(GETPOST("price")) / (1 + ($object->tva_tx / 100));
            $object->total_ht = price2num($object->total_ht, 'MU');
        } else {
            $object->total_ht = price2num(GETPOST("price"), 'MU');
            $object->total_ttc = price2num(GETPOST("price")) * (1 + ($object->tva_tx / 100));
            $object->total_ttc = price2num($object->total_ttc, 'MU');
        }

        $result = $object->create($user);

        if ($result > 0) {
            // Add account manager
            $result = $object->add_contact(GETPOST("contactid"), 'EVENTDAYMANAGER', 'internal');

            Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id);
            exit;
        } else {
            $langs->load("errors");
            $mesg_errors[] = $langs->trans($object->error);
            $action = 'create';
        }
        if ($error) {
            setEventMessage($mesg_errors, 'errors');
        }
    } else {
        setEventMessage($mesg_errors, 'errors');
        $action = 'create';
    }
} else if ($action == 'update' && !$_POST["cancel"] && $user->rights->event->write) {
    $error = 0;

    if (empty($ref)) {
        setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), 'errors');
        $error++;
    }
    if (!GETPOST("label", 'alpha')) {
        setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("Label")), 'errors');
        $error++;
    }

    if (!GETPOST('date_eventday') || ( isset($_GET['date_event']) && !GETPOST("date_event", 'alpha'))) {
        setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("DateStart")), 'errors');
        $error++;
    }

    if (GETPOST("price", 'alpha') == NULL) {
        setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("EventPriceHt")), 'errors');
        $error++;
    }
    if (GETPOST("time_start") == NULL) {
        $mesg_errors[] = $langs->trans("ErrorFieldRequired", $langs->transnoentities("TimeStart"));
        $error++;
    }

    if (GETPOST("time_end") == NULL) {
        $mesg_errors[] = $langs->trans("ErrorFieldRequired", $langs->transnoentities("TimeEnd"));
        $error++;
    }
    if (!$error) {

        $object->fetch($id);

        $object->ref = $ref;
        $object->label = GETPOST("label");
        $object->fk_soc = GETPOST("socid");
        $object->description = GETPOST("description");
        $object->description_web = GETPOST("description_web");
        $object->note_public = GETPOST("note_public");
        $object->note = GETPOST("note");
        $object->date_event = dol_mktime(12, 0, 0, GETPOST('date_eventmonth'), GETPOST('date_eventday'), GETPOST('date_eventyear'));
        $object->registration_open = GETPOST("registration_open");
        $object->time_start = GETPOST('time_start')?GETPOST('time_start'):"09:00:00";
        $object->time_end = GETPOST('time_end')?GETPOST('time_end'):"17:00:00";
        $object->tva_tx = GETPOST("tva_tx");

        if ($_POST['price_base_type'] == 'TTC') {
            print 'ttc';
            $object->total_ttc = price2num(GETPOST("price"), 'MU');
            $object->total_ht = price2num(GETPOST("price")) / (1 + ($object->tva_tx / 100));
            $object->total_ht = price2num($object->total_ht, 'MU');
        } else {
            $object->total_ht = price2num(GETPOST("price"), 'MU');
            $object->total_ttc = price2num(GETPOST("price")) * (1 + ($object->tva_tx / 100));
            $object->total_ttc = price2num($object->total_ttc, 'MU');
        }

        $result = $object->update($user);

        if ($result > 0) {
            Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id);
            exit;
        } else {
            setEventMessage($object->error, 'errors');
            $action = 'edit';
        }
    } else {
        setEventMessage($msg, 'errors');
        $action = 'edit';
    }
} else if ($_POST['action'] == 'setlabel') {
    $result = $object->fetch($id);
    $object->label = GETPOST("label");
    $result = $object->update($object->id, $user, 1);
    if ($result < 0) {
        $mesg = join(',', $object->errors);
    }
    $action = '';
} else if ($_POST['action'] == 'settotal_ht') {
    $result = $object->fetch($id);
    $object->total_ht = GETPOST("total_ht");
    $result = $object->update($object->id, $user, 1);
    if ($result < 0) {
        $mesg = join(',', $object->errors);
    }
    $action = '';
} else if ($action == 'setnote_public' && $user->rights->event->write) {
    $object->fetch($id);
    $result = $object->update_note_public(dol_html_entity_decode(GETPOST('note_public'), ENT_QUOTES));
    if ($result < 0) {
        dol_print_error($db, $object->error);
    }
} else if ($action == 'setnote' && $user->rights->event->write) {
    $object->fetch($id);
    $result = $object->update_note(dol_html_entity_decode(GETPOST('note'), ENT_QUOTES));
    if ($result < 0) {
        dol_print_error($db, $object->error);
    }
} else if ($action == "confirm_registration_open" && $confirm == "yes") {
    $result = $object->fetch($id);
    if ($result) {
        $object->setRegistrationOpen(1);
        $mesg = '<div class="ok">' . $langs->trans('RegistrationIsNowOpen') . '</div>';
    }
} else if ($action == "confirm_registration_close" && $confirm == "yes") {
    $result = $object->fetch($id);
    if ($result) {
        $object->setRegistrationOpen(0);
        $mesg = '<div class="ok">' . $langs->trans('RegistrationIsNowClosed') . '</div>';
    }
} else if ($action == 'confirm_validate' && $confirm == 'yes') {
    $object->fetch($id);

    $result = $object->setStatut(4);
    if ($result <= 0) {
        $mesg = '<div class="error">' . $object->error . '</div>';
    }
} else if ($action == confirm_delete_day && $confirm="yes") {
    $sql="DELETE FROM llx_event_day WHERE llx_event_day.rowid =".$id;
    $db->query($sql);
    header("Location: ".DOL_URL_ROOT."/custom/event/day/list.php");
    }
else if ($action == 'confirm_close_day' && $confirm == "yes")
    {
    $result = $object->fetch($id);
    if ($result) {
        $object->setClotured($id);
        $mesg = '<div class="ok">' . $langs->trans('CloseDayDone') . '</div>';
        }
    }
else if ($action == 'confirm_clone_day' && $confirm == "yes")
    {
    $result = $object->fetch($id);
    if ($result) {
        $ret=$object->createFromClone($id,GETPOST('event_id'));

        if(GETPOST('valid_group')=='on') {
            foreach ( $object->LoadLevelForDay($id,1) as $key2=>$el2) {
                $Eventlevel_to_clone = new Eventlevel($db);
                $result3 = $Eventlevel_to_clone->DefLevelForDay($el2,$ret);
                }
            }
        header("Location: ".DOL_URL_ROOT."/custom/event/day/fiche.php?id=".$ret);
        $mesg = '<div class="ok">' . $langs->trans('CloneDayOK') . '</div>';
        }
    }

/* * *************************************************
 * VIEW
 *
 * Put here all code to build page
 * ************************************************** */


$form = new Form($db);

$userstatic = new User($db);


if ($action == 'create' && $user->rights->event->write) {
    llxHeader('', $langs->trans("NewEventDay"), '');

    print_fiche_titre($langs->trans("NewEventDay"));

    $event = new Event($db);
    $event->fetch($eventid);

    print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST" enctype="multipart/form-data">';
    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '" />';
    print '<table class="border" width="100%">';
    print '<input type="hidden" name="action" value="add" />';
    print '<input type="hidden" name="eventid" value="' . $eventid . '" />';

    // Ref event
    print '<tr><td><label for="ref">' . $langs->trans("RefEvent") . '</label></td><td>' . $event->getNomUrl(1) . '</td></tr>';

    $defaultref = '';
    $obj = empty($conf->global->EVENTDAY_ADDON) ? 'mod_eventday_simple' : $conf->global->EVENTDAY_ADDON;
    if (!empty($conf->global->EVENTDAY_ADDON) && is_readable(dol_buildpath("/event/core/models/num/" . $conf->global->EVENTDAY_ADDON . ".php"))) {
        dol_include_once("/event/core/models/num/" . $conf->global->EVENTDAY_ADDON . ".php");
        $modEvent = new $obj;
        $defaultref = $modEvent->getNextValue($soc, $object);
    }

    if (empty($defaultref)) {
        $defaultref = '';
    }

    print '<input type="hidden" name="ref" value="' . ($_POST["ref"]?$_POST["ref"]:$defaultref) . '" />';
    // Label
    print '<tr><td><label for="label"><span class="fieldrequired">' . $langs->trans("LabelDay") . '</span></label></td><td><input size="30" type="text" name="label" id="label" value="' . $_POST["label"] . '"></td></tr>';

    // Customer
    print '<tr><td><label for="socid">' . $langs->trans("EventSponsor") . '</label></td><td>';
    print $form->select_company((GETPOST("socid") ? GETPOST("socid") : $event->socid), 'socid', '', 1, 1);
    print '</td></tr>';

    // Event manager
    print '<tr><td>' . $langs->trans("EventManager") . '</td><td>';
    $form->select_users($user->id, 'contactid');
    print '</td></tr>';

    // Date start
    $date_event = dol_mktime(12, 0, 0, $_POST['date_eventmonth'], $_POST['date_eventday'], $_POST['date_eventyear']);
    print '<tr><td><label for="date__event"><span class="fieldrequired">' . $langs->trans("Date") . '</span></label></td><td>';
    print $form->select_date($date_event, 'date_event');
    print '</td></tr>';

    //Time start
    print '<tr><td><label for="label"><span class="fieldrequired">' . $langs->trans("TimeStart") . '</span></label></td><td><input size="10" type="text" name="time_start" id="time_start" value="09:00:00"></td></tr>';

    //Time end
    print '<tr><td><label for="label"><span class="fieldrequired">' . $langs->trans("TimeEnd") . '</span></label></td><td><input size="10" type="text" name="time_end" id="time_end" value="17:00:00"></td></tr>';

    //DescriptionMail
    print '<tr><td><label for="description">' . $langs->trans("DescriptionMail") . '</label></td>';
    print '<td>';
    $doleditor = new DolEditor('description', GETPOST("description"), '', 400, 'dolibarr_emailing', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 8, 160);
    $doleditor->Create();
    print '</td></tr>';

    //DescriptionWeb
    print '<tr><td><label for="description_web">' . $langs->trans("DescriptionWeb") . '</label></td>';
    print '<td>';
    $doleditor = new DolEditor('description_web', GETPOST("description_web"), '', 400, 'dolibarr_emailing', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 8, 160);
    $doleditor->Create();
    print '</td></tr>';

    // Price HT of event
    print '<tr><td><label for="price"><span class="fieldrequired">' . $langs->trans("DayPriceHt") . '</span></label></td><td><input size="10" type="text" name="price" id="price" value="' . (GETPOST("price") ? GETPOST('price') : price2num($event->price_day) ) . '">';
    print '&nbsp;' . $langs->trans("Currency" . $conf->currency) . '</td></tr>';

    // Price base
    print '<tr><td width="15%">';
    print $langs->trans('PriceBase');
    print '</td>';
    print '<td>';
    print $form->selectPriceBaseType($object->price_base_type, "price_base_type");
    print '</td>';
    print '</tr>';

    // VAT
    print '<tr><td>' . $langs->trans("VATRate") . '</td><td>';
    print $form->load_tva("tva_tx", GETPOST("tva_tx"), $mysoc);
    print '</td></tr>';

    print '<tr><td colspan="2" align="center">';
    print '<input type="submit" class="button" name="add" value="' . $langs->trans("Add") . '">';
    print ' &nbsp; &nbsp; ';
    print '<input type="button" class="button" value="'.$langs->trans("Cancel").'" onclick="location.href=\''.DOL_URL_ROOT.'/custom/event/fiche.php?id='.$eventid.'\'" >';
    print '</td></tr>';

    print '</table>';
    print '</form>';

} // action create
else if ($id || !empty($ref)) {
    /*
     * Show or edit
     */
    llxHeader('', $langs->trans("EventDay"), '');

    dol_htmloutput_mesg($mesg,$mesgs,'error');

    $object = new Day($db);
	
	 

    $ret = $object->fetch($id, $ref);

    if ($ret > 0) {
        $soc = new Societe($db);
        $soc->fetch($object->socid);

        $head = eventday_prepare_head($object);
        dol_fiche_head($head, 'eventday', $langs->trans("EventDay"), 0, ($object->public ? 'event@event' : 'event@event'));

        // Confirmation validation
        if ($action == 'validate') {
            $ret = $form->form_confirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ValidateEventDay'), $langs->trans('ConfirmValidateEventDay'), 'confirm_validate', '', 0, 1);
            if ($ret == 'html') {
                print '<br>';
            }
        }

        // Confirmation delete event
        if ($action == 'delete') {
            $ret = $form->form_confirm($_SERVER["PHP_SELF"] . "?id=" . $object->id, $langs->trans("DeleteAnEvent"), $langs->trans("ConfirmDeleteAnEvent"), "confirm_delete", '', '', 1);
            if ($ret == 'html') {
                print '<br />';
            }
        }

        // Confirmation delete day
        if ($action == 'delete_day') {
            $ret = $form->form_confirm($_SERVER["PHP_SELF"] . "?id=" . $object->id, $langs->trans("DeleteAnDay"), $langs->trans("ConfirmDeleteAnDay"), "confirm_delete_day", '', '', 1);
            if ($ret == 'html') {
                print '<br />';
            }
        }

        // Confirmation clone day
        if ($action == 'CloneDay') {
            if($conf->global->EVENT_HIDE_GROUP!='1') $options_valid = array(array('name' => 'valid_group', 'type' => 'checkbox', 'label' => $langs->trans('CloneGroup'), 'value' => "1"));
            $ret = $form->form_confirm($_SERVER["PHP_SELF"] . "?id=".$object->id.'&event_id='.GETPOST('event_id'),$langs->trans("CloneDay"),$langs->trans("CloneDayConfirm"),"confirm_clone_day",$options_valid,0,1);
            if ($ret == 'html') print '<br>';
        }

        // Confirmation close day
        if ($action == 'Clotured') {
           $ret = $form->form_confirm($_SERVER["PHP_SELF"] . "?id=".$object->id,$langs->trans("CloseADay"),$langs->trans("ConfirmCloseADay"),"confirm_close_day",'','',1);
            if ($ret == 'html') print '<br>';
        }

        if ($action == 'edit') {
            print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST" enctype="multipart/form-data">';
            print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="id" value="' . $object->id . '">';

            print '<table class="border" width="100%">';

            if (empty($object->ref)) {
                $defaultref = '';
                $obj = empty($conf->global->EVENTDAY_ADDON) ? 'mod_eventday_simple' : $conf->global->EVENTDAY_ADDON;
                if (!empty($conf->global->EVENTDAY_ADDON) && is_readable(dol_buildpath("/event/core/models/num/" . $conf->global->EVENTDAY_ADDON . ".php"))) {
                    dol_include_once("/event/core/models/num/" . $conf->global->EVENTDAY_ADDON . ".php");
                    $modEvent = new $obj;
                    $defaultref = $modEvent->getNextValue($soc, $object);
                }

                if (empty($defaultref)) {
                    $defaultref = $bject->ref;
                }
            } else
                $defaultref = (GETPOST('ref') ? $_POST['ref'] : $object->ref);

            print '<input type="hidden" name="ref" value="' . (GETPOST('ref') ? GETPOST('ref') : $defaultref) . '">';

            // Label
            print '<tr><td><span class="fieldrequired">' . $langs->trans("LabelDay") . '</span></td>';
            print '<td><input size="30" name="label" value="' . (GETPOST('label') ? GETPOST('label') : $object->label) . '"></td></tr>';

            // Customer
            print '<tr><td>' . $langs->trans("Company") . '</td><td>';
            print $form->select_company((GETPOST('socid', 'int') ? GETPOST('socid', 'int') : $object->fk_soc), 'socid', '', 1, 1);
            print '</td></tr>';

            // Date
            print '<tr><td><span class="fieldrequired">' . $langs->trans("EventDayDate") . '</span></td><td>';
            print $form->select_date($object->date_event, 'date_event');			
            print '</td></tr>';

            //Time start
            print '<tr><td><label for="label"><span class="fieldrequired">' . $langs->trans("TimeStart") . '</span></label></td><td><input size="10" type="text" name="time_start" id="time_start" value="'.(GETPOST('time_start') ? GETPOST('time_start') : $object->time_start).'"></td></tr>';

            //Time end
            print '<tr><td><label for="label"><span class="fieldrequired">' . $langs->trans("TimeEnd") . '</span></label></td><td><input size="10" type="text" name="time_end" id="time_end" value="'.(GETPOST('time_end') ? GETPOST('time_end') : $object->time_end).'"></td></tr>';

            // Inscription ouverte oui/non
            print '<tr><td><label for="registration_open">' . $langs->trans("RegistrationIsOpen") . '</label></td><td>';
            print $form->selectyesno('registration_open', (GETPOST('registration_open') ? GETPOST('registration_open') : $object->registration_open), 1);
            print '</td></tr>';

            // Price of event
            print '<tr><td><label for="price"><span class="fieldrequired">' . $langs->trans("Price") . '</span></label></td><td><input size="10" type="text" name="price" id="price" value="' . price(($_POST['price'] ? $_POST['price'] : $object->total_ht)) . '">';
            print '&nbsp;' . $langs->trans("Currency" . $conf->currency) . '</td></tr>';

            // Price base
            print '<tr><td width="15%">';
            print $langs->trans('PriceBase');
            print '</td>';
            print '<td>';
            print $form->selectPriceBaseType($object->price_base_type, "price_base_type");
            print '</td>';
            print '</tr>';

            // VAT
            print '<tr><td>' . $langs->trans("VATRate") . '</td><td>';
            print $form->load_tva("tva_tx", GETPOST("tva_tx"), $mysoc);
            print '</td></tr>';


            // DescriptionMail
            print '<tr><td>' . $langs->trans("DescriptionMail") . '</td>';
            print '<td>';
            $doleditor = new DolEditor('description', (GETPOST("description") ? GETPOST("description") : $object->description), '', 400, 'dolibarr_emailing', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 8, 160);
            $doleditor->Create();
            print '</td></tr>';

            // DescriptionWeb
            print '<tr><td>' . $langs->trans("DescriptionWeb") . '</td>';
            print '<td>';
            $doleditor = new DolEditor('description_web', (GETPOST("description_web") ? GETPOST("description_web") : $object->description_web), '', 400, 'dolibarr_emailing', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 8, 160);
            $doleditor->Create();
            print '</td></tr>';

            // Public note
            print '<tr><td valign="top">' . $langs->trans("NotePublic") . '</td>';
            print '<td>';
            print '<textarea name="note_public" cols="60" rows="' . ROWS_3 . '">' . (GETPOST('note_public') ? GETPOST('note_public') : $object->note_public) . "</textarea><br>";
            print "</td></tr>";

            // Private note
            if (!$user->societe_id) {
                print '<tr><td valign="top">' . $langs->trans("NotePrivate") . '</td>';
                print '<td>';
                print '<textarea name="note" cols="60" rows="' . ROWS_3 . '">' . (GETPOST('note') ? GETPOST('note') : $object->note) . "</textarea><br>";
                print "</td></tr>";
            }

            print '<tr><td align="center" colspan="2">';
            print '<input name="update" class="button" type="submit" value="' . $langs->trans("Modify") . '"> &nbsp; ';
            print '<input type="submit" class="button" name="cancel" Value="' . $langs->trans("Cancel") . '"></td></tr>';
            print '</table>';
            print '</form>';
        } else {

            /*
             * View of event day
             */

            /*
             * Confirmation de l'ouverture des inscriptions
             */
            if ($_GET['action'] == 'open_registration') {
                $ret = $form->form_confirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans("OpenRegistration"), $langs->trans("ConfirmOpenRegistration"), 'confirm_registration_open', '', 0, 1);
                if ($ret == 'html') {
                    print '<br>';
                }
            }
            /*
             * Confirmation de la fermeture des inscriptions
             */
            if ($_GET['action'] == 'close_registration') {
                $ret = $form->form_confirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans("CloseRegistration"), $langs->trans("ConfirmCloseRegistration"), 'confirm_registration_close');
                if ($ret == 'html') {
                    print '<br>';
                }
            }
            setEventMessage($langs->trans(GETPOST('message_alert')));

            print '<table class="border" width="100%">';

		
         ///////////////////:: recherche jour pecedent jour suivant /////////////////////////////////
		 $js = jour_suivant($db,$object->fk_event,dol_print_date($object->date_event, '%Y-%m-%d'));
		 $jp = jour_precedent($db,$object->fk_event,dol_print_date($object->date_event, '%Y-%m-%d'));
		 
         //////////////////////////////////////////////////////////////////			

		   ?><div style="vertical-align: middle">
					<div class="pagination paginationref">
						<ul class="right">
						<!--<li class="noborder litext">
						<a href="/dolibarr/societe/list.php?restore_lastsearch_values=1">Retour liste</a>
						</li>-->
						<?php 
						if($jp!='')
							echo '<li class="pagination"><a accesskey="p" href="'.DOL_URL_ROOT.'/custom/event/day/fiche.php?id='.$jp.'"><i class="fa fa-chevron-left"></i></a></li>';
						else
							echo '<li class="pagination"><span class="inactive"><i class="fa fa-chevron-left opacitymedium"></i></span></li>';
						if($js!='')
							echo '<li class="pagination"><a accesskey="p" href="'.DOL_URL_ROOT.'/custom/event/day/fiche.php?id='.$js.'"><i class="fa fa-chevron-right"></i></a></li>';
						else
							echo '<li class="pagination"><span class="inactive"><i class="fa fa-chevron-right opacitymedium"></i></span></li>';
						?>
						</ul></div>
				</div>
				
		  <?php
		 
		 
            // Label
            print '<tr><td valign="top" width="30%">';
            print $form->editfieldkey("LabelDay", 'label', $object->label, $object, $conf->global->MAIN_EDIT_ALSO_INLINE && $user->rights->event->write && $object->fk_statut != 9, 'string');
            print '</td><td>';
            print $form->editfieldval("LabelDay", 'label', $object->label, $object, $conf->global->MAIN_EDIT_ALSO_INLINE && $user->rights->event->write && $object->fk_statut != 9, 'string');
            print "</td></tr>";

            // Third party
            if ($object->fk_soc > 0) {
                print '<tr><td>' . $langs->trans("Company") . '</td><td>';
                print $object->thirdparty->getNomUrl(1);
                print '</td></tr>';
            }

            // Date
            $dayofweek = strftime("%w", $object->date_event);
            print '<tr><td>' . $langs->trans("EventDayDate") . '</td><td>' . $langs->trans("Day" . $dayofweek) . ' ' . dol_print_date($object->date_event, 'daytext') . '</td>';
            print '</tr>';

            // Time start
            print '<tr><td>' . $langs->trans("TimeStart") . '</td><td>' . $object->time_start . '</td></tr>';

            // Time end
            print '<tr><td>' . $langs->trans("TimeEnd") . '</td><td>' . $object->time_end . '</td></tr>';

            // Statut
            print '<tr><td>' . $langs->trans("Status") . '</td><td>' . $object->getLibStatut(4) . '</td></tr>';

            // Inscription ouverte oui/non
            print '<tr><td>' . $langs->trans("RegistrationIsOpen") . '</td>';
            if ($object->registration_open > 0) {
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
            print '</td></tr>';

            // Price HT
            print '<tr><td>';
            print $langs->trans('DayPriceHt');
            print '</td><td>';
            print price2num($object->total_ht, 'MT');
            print ' ' . $conf->currency . '</td></tr>';

            // Price TTC
            print '<tr><td>';
            print $langs->trans('DayPriceTtc');
            print '</td><td>';
            print price2num($object->total_ttc, 'MT');
            print ' ' . $conf->currency . '</td></tr>';

            // LINK PAGE CONFIRM
            print '<tr><td>'.$langs->trans('EventPageLink').'</td>';
            print '<td><a href="../registration/confirm_register.php?id='.$object->id.'&key=consult" target="_blank">ICI</a></td></tr>';

            // Stats
            print '<tr><td>'.$langs->trans("NumberRegistrationShort");
            print '</td><td class="valeur">';
            print img_picto($langs->trans('Draft'),'statut0') . ' ' . $object->getNbRegistration('0');
            print ' ' . img_picto($langs->trans('Waited'),'statut3') . ' ' . $object->getNbRegistration('1');
            print ' ' . img_picto($langs->trans('Queued'),'statut1') . ' ' . $object->getNbRegistration('8');
            print ' ' . img_picto($langs->trans('Confirmed'),'statut4') . ' ' . $object->getNbRegistration('4');
            print ' ' . img_picto($langs->trans('Cancelled'),'statut8') . ' ' . $object->getNbRegistration('5');
            print '</td></tr>';

            // DescriptionMail
            print '<tr><td>';
            print $langs->trans('DescriptionMail');
            print '</td><td>';
            print $object->description;
            print '</td></tr>';

            // DescriptionWeb
            print '<tr><td>';
            print $langs->trans('DescriptionWeb');
            print '</td><td>';
            print $object->description_web;
            print '</td></tr>';

            // Note Public
            print '<tr><td valign="top">';
            print $langs->trans("NotePublic");
            print '</td><td colspan="3">';
            print $form->editfieldval("NotePublic", 'note_public', $object->note_public, $object, $user->rights->event->write, 'textarea');
            print '</td>';
            print '</tr>';

            // Note Private
            if (!$user->societe_id) {
                print '<tr><td valign="top">';
                print $langs->trans("NotePrivate");
                print '</td><td colspan="3">';
                print $form->editfieldval("NotePrivate", 'note_private', $object->note, $object, $user->rights->event->write, 'textarea');
                print '</td>';
                print '</tr>';
            }

            print '</table>';

            if($conf->global->EVENT_HIDE_GROUP=='-1')
                {
                print '<table>
                <tr><td>';
                /*
                 * Statistics area
                 */
                $level = array();
                $level[0] = array('label' => 'sans', 'nb' => 0);
                $sql_level = "SELECT el.rowid, el.label  FROM " . MAIN_DB_PREFIX . "event_level as el LEFT JOIN " . MAIN_DB_PREFIX . "event_level_day as ld ON ld.fk_level=el.rowid ";
                $sql_level.=" WHERE ld.fk_eventday=$object->id";
                $sql_level.=" ORDER BY el.rang ASC";
                $resql = $db->query($sql_level);
                if ($resql) {
                    $num = $db->num_rows($resql);
                    if ($num) {
                        $i = 0;

                        while ($i < $num) {
                            $obj = $db->fetch_object($resql);
                            $level[$obj->rowid] = array('label' => $obj->label, 'nb' => 0);

                            $i++;
                        }
                    }
                }
                $total = 0;


                $sql = "SELECT r.fk_levelday";
                $sql.= " FROM " . MAIN_DB_PREFIX . "event_registration as r";
                $sql.= ' WHERE r.fk_eventday=' . $object->id . ' AND r.entity IN (' . getEntity('societe', 1) . ')';
                if ($socid) {
                    $sql.= " AND r.socid = " . $socid;
                }
                $result = $db->query($sql);
                if ($result) {
                    while ($objp = $db->fetch_object($result)) {
                        $found = 0;
                        if ($objp->fk_levelday > 0 && $level[$objp->fk_levelday]) {
                            $found = 1;
                            $level[$objp->fk_levelday]['nb'] ++;
                        } else {
                            $found = 1;
                            $level[0]["nb"] ++;
                        }


                        if ($found) {
                            $total++;
                        }
                    }
                } else {
                    dol_print_error($db);
                }

                print '<table class="noborder" width="100%">';
                print '<tr class="liste_titre"><th colspan="2">' . $langs->trans("Statistics") . '</th></tr>';
                if (!empty($conf->use_javascript_ajax)) {
                    print '<tr><td align="left">';
                    $dataseries = array();
                    foreach ($level as $key => $value) {
                        $dataseries[] = array('label' => $value['label'], 'data' => round($value['nb']));
                    }

                    $data = array('series' => $dataseries);
                    dol_print_graph('stats', 300, 180, $data, 1, 'pie', 0);
                    print '</td>';
                    print '</tr>';
                } else {
                    if (!empty($conf->societe->enabled) && empty($conf->global->SOCIETE_DISABLE_PROSPECTS_STATS)) {
                        $statstring = "<tr $bc[0]>";
                        $statstring.= '<td><a href="' . DOL_URL_ROOT . '/comm/prospect/list.php">' . $langs->trans("Prospects") . '</a></td><td align="right">' . round($third['prospect']) . '</td>';
                        $statstring.= "</tr>";
                    }
                    if (!empty($conf->societe->enabled) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS_STATS)) {
                        $statstring.= "<tr $bc[1]>";
                        $statstring.= '<td><a href="' . DOL_URL_ROOT . '/comm/list.php">' . $langs->trans("Customers") . '</a></td><td align="right">' . round($third['customer']) . '</td>';
                        $statstring.= "</tr>";
                    }
                    if (!empty($conf->fournisseur->enabled) && empty($conf->global->SOCIETE_DISABLE_SUPPLIERS_STATS)) {
                        $statstring2 = "<tr $bc[0]>";
                        $statstring2.= '<td><a href="' . DOL_URL_ROOT . '/fourn/liste.php">' . $langs->trans("Suppliers") . '</a></td><td align="right">' . round($third['supplier']) . '</td>';
                        $statstring2.= "</tr>";
                    }
                    print $statstring;
                    print $statstring2;
                }
                print '<tr class="liste_total"><td>' . $langs->trans("RegistartionForThisDay") . '</td><td align="right">';
                print $total;
                print '</td></tr>';
                print '</table>';
                print '</td></tr></table>';
            }
        }
        print '</div>';

        /*
         * Boutons actions
         */
        print '<div class="tabsAction">';
        if ($action != "edit") {
            // Delete
            if ($user->rights->event->write) {
                print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete_day">' . $langs->trans("Delete") . '</a>';
            }

            // Modify
            if ($object->fk_statut != 9 && $user->rights->event->write) {
                print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a>';
            }

            // Clone
            // var_dump($object->fk_event);
            if ($conf->global->EVENTDAY_ACTIVE_CLONE_FUNC =='1') {
                print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;event_id='.$object->fk_event.'&amp;action=CloneDay">' . $langs->trans("Clone") . '</a>';
            } else {
                print '<a class="butAction not-active" href="#">' . $langs->trans("Clone") . '</a>';
            }

            // CloseDay
            if ($object->fk_statut == 4 && $user->rights->event->write) {
                if (strtotime(date("Y-m-d")) > $object->date_event) {
                    print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=Clotured">' . $langs->trans("CloseDay") . '</a>';
                    }
                else print '<a class="butAction not-active" href="#">' . $langs->trans("CloseDay") . '</a>';
                }

            // Validate
            if ($object->fk_statut == 0 && $user->rights->event->write) {
                print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=validate">' . $langs->trans("Valid") . '</a>';
            }

            // Add a registration if validated
            if ($object->fk_statut == 4 && $user->rights->event->write) {
                if (strtotime(date("Y-m-d")) <= $object->date_event && $object->registration_open=='1')
                {
                    print '<a class="butAction" href="../registration/create.php?dayid=' . $object->id . '">' . $langs->trans("AddRegistration") . '</a>';
                    if($conf->global->EVENT_BLOCK_REGISTRATION_TAG=="1") print '<a class="butAction not-active" href="#">' . $langs->trans("AddRegistrationTag") . '</a>';
                    else print '<a class="butAction" href="../registration/create_tag.php?dayid=' . $object->id . '">' . $langs->trans("AddRegistrationTag") . '</a>';
                }
                else
                {
                    // if($object->registration_open)
                    print '<a class="butAction not-active" href="#">' . $langs->trans("AddRegistration") . '</a>';
                    print '<a class="butAction not-active" href="#">' . $langs->trans("AddRegistrationTag") . '</a>';
                    $msg = '<div class="warning">'.$langs->trans('DayAlreadyPast').'</div>';
                    dol_htmloutput_mesg($msg);
                }
            }
        }

        print "</div>";
    } else {
        dol_print_error($db, $object->error);
    }
} // view or edit

// End of page
llxFooter();
$db->close();
