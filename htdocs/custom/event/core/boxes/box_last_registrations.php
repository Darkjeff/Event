<?php
/* Copyright (C) 2010 Regis Houssin  <regis@dolibarr.fr>
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
 * 		\file       event/core/boxes/box_registrations.php
 * 		\ingroup    contracts
 * 		\brief      Module de generation de l'affichage de la box registration
 */

include_once(DOL_DOCUMENT_ROOT."/core/boxes/modules_boxes.php");


class box_last_registrations extends ModeleBoxes {

    var $boxcode="lastregistrations";
    var $boximg="event_registration@event";
    var $boxlabel;
    var $depends = array("event");	// conf->event->enabled

    var $db;
    var $param;

    var $info_box_head = array();
    var $info_box_contents = array();


    /**
     *      \brief      Constructeur de la classe
     */
    function box_last_registrations()
    {
    	global $langs;
    	$langs->load("event@event");

    	$this->boxlabel=$langs->trans("BoxLastRegistrations");
    }

    /**
     *      \brief      Charge les donnees en memoire pour affichage ulterieur
     *      \param      $max        Nombre maximum d'enregistrements a charger
     */
    function loadBox($max=5)
    {
    	global $user, $langs, $db, $conf;

    	$this->max=$max;

    	dol_include_once("/event/class/registration.class.php");
    	$registration=new Registration($db);

    	$this->info_box_head = array('text' => $langs->trans("BoxTitleLastRegistrations",$max));

    	if ($user->rights->event->read && !$user->socid)
    	{
    		$sql = "SELECT s.nom,";
    		$sql.= " er.rowid, er.ref, er.datec, er.fk_event, er.fk_statut, er.fk_user_registered";
    		$sql.= ", e.label";
    		$sql.= ", ed.date_event";
    		$sql.= ", sp.lastname, sp.firstname, sp.fk_soc as socid";
    		$sql.= " FROM ".MAIN_DB_PREFIX."event_registration as er";
    		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."event as e ON er.fk_event=e.rowid";
    		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."event_day as ed ON er.fk_eventday=ed.rowid";
    		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON er.fk_soc=s.rowid";
    		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople as sp ON er.fk_user_registered=sp.rowid";
    		if($user->socid) $sql.= " AND s.rowid = ".$user->socid;
    		$sql.= " ORDER BY er.datec DESC, er.rowid DESC ";
    		$sql.= $db->plimit($max, 0);

    		$resql = $db->query($sql);
    		if ($resql)
    		{
    			$num = $db->num_rows($resql);
    			$now=gmmktime();

    			$i = 0;

    			while ($i < $num)
    			{
    				$objp = $db->fetch_object($resql);
    				$datec=$db->jdate($objp->datec);
    				$dateterm=$db->jdate($objp->fin_validite);
    				$dateclose=$db->jdate($objp->date_cloture);
    				$late = '';

    				$registration->statut=$objp->fk_statut;
    				$registration->id=$objp->rowid;
    				$registration->fk_statut=$objp->fk_statut;


    				$r=0;
    				$this->info_box_contents[$i][0] = array('td' => 'align="left" width="16"',
    				'logo' => $this->boximg,
    				'url' => dol_buildpath("/event/registration/fiche.php?id=".$objp->rowid,1));
    				$r++;

    				$this->info_box_contents[$i][$r] = array('td' => 'align="left"',
    				'text' => ($objp->ref?$objp->ref:$objp->rowid),	// Some event have no ref
    				'url' => dol_buildpath("/event/registration/fiche.php?id=".$objp->rowid,1));
    				$r++;

    				$this->info_box_contents[$i][$r] = array('td' => 'align="left"',
    				'text' => ($objp->label?dol_print_date($db->idate($objp->date_event),'day').' - '.$objp->label:$objp->fk_event),	// Some event have no ref
    				'url' => dol_buildpath("/event/fiche.php?id=".$objp->fk_event,1));
    				$r++;



    				$this->info_box_contents[$i][$r] = array('td' => 'align="left" width="16"',
    				'logo' => 'company',
    				'url' => DOL_URL_ROOT."/societe/card.php?socid=".$objp->socid);
    				$r++;

    				$this->info_box_contents[$i][$r] = array('td' => 'align="left"',
    				'text' => dol_trunc($objp->nom,40),
    				'url' => DOL_URL_ROOT."/comm/fiche.php?socid=".$objp->socid);
    				$r++;

    				$this->info_box_contents[$i][$r] = array('td' => 'align="left" width="16"',
    				'logo' => 'contact',
    				'url' => DOL_URL_ROOT."/contact/card.php?id=".$objp->fk_user_registered);
    				$r++;

    				$this->info_box_contents[$i][$r] = array('td' => 'align="left"',
    				'text' => dol_trunc(strtoupper($objp->lastname).' '.ucwords($objp->firstname),40),
    				'url' => DOL_URL_ROOT."/contact/card.php?id=".$objp->fk_user_registered);
    				$r++;

    				$this->info_box_contents[$i][$r] = array('td' => 'align="right"',
    				'text' => dol_print_date($datec,'dayhour'));
    				$r++;

    				$this->info_box_contents[$i][$r] = array('td' => 'align="right" nowrap="nowrap"',
    				'text' => $registration->getLibStatut(3),
    				'asis'=>1
    				);
    				$r++;

    				$i++;
    			}

    			if ($num==0) $this->info_box_contents[$i][0] = array('td' => 'align="center"','text'=>$langs->trans("NoRecordedContracts"));
    		}
    		else
    		{
    			dol_print_error($db);
    		}
    	}
    	else
    	{
    		$this->info_box_contents[0][0] = array('td' => 'align="left"',
    		'text' => $langs->trans("ReadPermissionNotAllowed"));
    	}
    }

    function showBox($head = null, $contents = null)
    {
        parent::showBox($this->info_box_head, $this->info_box_contents);
    }

}

?>
