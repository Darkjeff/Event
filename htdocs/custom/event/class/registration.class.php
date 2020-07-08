<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *  \file       event/class/registration.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2012-07-06 00:18
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");

/**
 *	Put here description of your class
 */
class Registration  extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='event_registration';			//!< Id that identify managed objects
	var $table_element='event_registration';	//!< Name of table without prefix where object is stored

	var $id;

	var $client;		// Objet societe client (a charger par fetch_thirdparty)
	var $entity;
	var $ref;
	var $fk_soc;
	var $fk_event;
	var $fk_eventday;
	var $fk_levelday;
	var $datec='';
	var $tms='';
	var $date_valid='';
	var $total_ht;
	var $total_tva;
	var $total_ttc;
	var $tva_tx;
	var $paye; // 1 if paid COMPLETELY
	var $accountancy_code;
	var $fk_user_create;
	var $fk_user_valid;
	var $fk_user_registered;
	//! 0=draft,
    //! 1=validated (need to be paid),
    //! #2=classified paid partially (close_code='discount_vat','badcustomer') or completely (close_code=null),
    //! #3=classified
	//! 4=confirmed (paid and put in a session level)
	//! 5=cancelled
	//! 6=Closed
	//! 8=Waiting (not paid)
	var $fk_statut;
	var $civilite;
	var $firstname;
	var $naiss='';
	var $lastname;
	var $address;
	var $zip;
	var $town;
	var $state_id;
	var $country_id;
	var $phone;
	var $phone_perso;
	var $phone_mobile;
	var $note_private;
	var $note_public;

	var $eventday;			// object of eventday


	/**
	 *  Constructor
	 *
	 *  @param	DoliDb		$db      Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
		$this->statuts_short=array(0=>'Draft',1=>'Waited', 4=>'Confirmed', 5=>'Cancelled',6=>'Closed', 8=>'Queued');
		$this->statuts=array(0=>'Draft',1=>'Waited', 4=>'Confirmed',5=>'Cancelled',6=>'Closed',8=>'Queued');
		return 1;
	}


	/**
	 *  Create object into database
	 *
	 *  @param	User	$user        User that create
	 *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
	 *  @return int      		   	 <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->fk_soc)) $this->fk_soc=trim($this->fk_soc);
		if (isset($this->fk_event)) $this->fk_event=trim($this->fk_event);
		if (isset($this->fk_eventday)) $this->fk_eventday=trim($this->fk_eventday);
		if ($this->fk_levelday == '')
		    $this->fk_levelday = 0;
		if (isset($this->fk_levelday)) $this->fk_levelday=trim($this->fk_levelday);
		if (isset($this->total_ht)) $this->total_ht=trim($this->total_ht);
		if (isset($this->total_tva)) $this->total_tva=trim($this->total_tva);
		if (isset($this->total_ttc)) $this->total_ttc=trim($this->total_ttc);
		if (isset($this->tva_tx)) $this->tva_tx=trim($this->tva_tx);
		if (isset($this->paye)) $this->tva_tx=trim($this->paye);
		if (isset($this->accountancy_code)) $this->accountancy_code=trim($this->accountancy_code);
		if (isset($this->fk_user_create)) $this->fk_user_create=trim($this->fk_user_create);
		if (isset($this->fk_user_valid)) $this->fk_user_valid=trim($this->fk_user_valid);
		if (isset($this->fk_user_registered)) $this->fk_user_registered=trim($this->fk_user_registered);
		if (isset($this->fk_statut)) $this->fk_statut=trim($this->fk_statut);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);



		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."event_registration(";

		$sql.= "entity,";
		$sql.= "ref,";
		$sql.= "fk_soc,";
		$sql.= "fk_event,";
		$sql.= "fk_eventday,";
		$sql.= "fk_levelday,";
		$sql.= "datec,";
		$sql.= "date_valid,";
		$sql.= "total_ht,";
		$sql.= "total_tva,";
		$sql.= "total_ttc,";
		$sql.= "tva_tx,";
		$sql.= "paye,";
		$sql.= "accountancy_code,";
		$sql.= "fk_user_create,";
		$sql.= "fk_user_valid,";
		$sql.= "fk_user_registered,";
		$sql.= "fk_statut,";
		$sql.= "note_private,";
		$sql.= "note_public,";
		$sql.= "unique_key";


		$sql.= ") VALUES (";

		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").",";
		$sql.= " ".(! isset($this->fk_soc)?'NULL':"'".$this->fk_soc."'").",";
		$sql.= " ".(! isset($this->fk_event)?'NULL':"'".$this->fk_event."'").",";
		$sql.= " ".(! isset($this->fk_eventday)?'NULL':"'".$this->fk_eventday."'").",";
		$sql.= " ".(! isset($this->fk_levelday)?'NULL':"'".$this->fk_levelday."'").",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':"'".$this->db->idate($this->datec)."'").",";
		$sql.= " ".(! isset($this->date_valid) || dol_strlen($this->date_valid)==0?'NULL':"'".$this->db->idate($this->date_valid)."'").",";
		$sql.= " ".(! isset($this->total_ht)?'NULL':"'".$this->total_ht."'").",";
		$sql.= " ".(! isset($this->total_tva)?'NULL':"'".$this->total_tva."'").",";
		$sql.= " ".(! isset($this->total_ttc)?'NULL':"'".$this->total_ttc."'").",";
		$sql.= " ".(! isset($this->tva_tx)?'NULL':"'".$this->tva_tx."'").",";
		$sql.= " ".(! isset($this->paye)?'NULL':"'".$this->paye."'").",";
		$sql.= " ".(! isset($this->accountancy_code)?'NULL':"'".$this->db->escape($this->accountancy_code)."'").",";
		$sql.= " ".$user->id.",";
		$sql.= " ".(! isset($this->fk_user_valid)?'0':"'".$this->fk_user_valid."'").",";
		$sql.= " ".(! isset($this->fk_user_registered)?'0':"'".$this->fk_user_registered."'").",";
		$sql.= " ".(! isset($this->fk_statut)?'0':"'".$this->fk_statut."'").",";
		$sql.= " ".(! isset($this->note_private)?'NULL':"'".$this->db->escape($this->note_private)."'").",";
		$sql.= " ".(! isset($this->note_public)?'NULL':"'".$this->db->escape($this->note_public)."'").",";
		$sql.= " '".md5(microtime(TRUE) * 100000)."'";


		$sql.= ")";
		$this->db->begin();

		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);



		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{

			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."event_registration");

			// Actions on extra fields (by external module)
			include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
			$hookmanager=new HookManager($this->db);
			$hookmanager->initHooks(array('registrationdao'));
			$parameters=array('id'=>$this->id);
			$action='';
			$reshook=$hookmanager->executeHooks('insertExtraFields',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
			if (empty($reshook))
			{
				$result=$this->insertExtraFields();
				if ($result < 0)
				{
					$error++;
				}
			}
			else if ($reshook < 0) $error++;

			if (! $notrigger)
			{

				// Call triggers
				include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				$interface=new Interfaces($this->db);
				$result=$interface->run_triggers('EVENT_REGISTRATION_CREATE',$this,$user,$langs,$conf);
				if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// End call triggers
			}
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return $this->id;
		}
	}

	/*
	**
	**	Fonction qui renvoie un lien cliquable pour télecharger le fichier ics
	**
	*/
	function ics_link(){
		include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/event.class.php");
		global $conf,$langs;
		$event = new Event($this->db);
		$event->fetch($this->fk_event);
		$link_url = DOL_URL_ROOT."/custom/event/ics/event_".$this->ref.".ics";
		$link = '<a href="'.$link_url.'">'.$this->ref.'</a>';
		return ($link);
	}


	/*
	**
	**	Fonction qui créée un fichier ics et renvoie l'adresse du fichier.
	**
	*/

	function ics_create($debug = 0)
	{
		//Dépendances
		include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/eventlevel_cal.class.php");
		include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/eventlevel.class.php");
		include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/event.class.php");
		include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/day.class.php");

	   	global $conf,$langs;

		/* création du fichier ics */
		$event = new Event($this->db);
		$eventday = new Day($this->db);
		$level = new Eventlevel($this->db);
		$calendrier = new Eventlevel_cal($this->db);
		$contact_registered = new Contact($this->db);

		/*Initialisation*/
		$event->fetch($this->fk_event);
		$level->fetch($this->fk_levelday);
		$eventday->fetch($this->fk_eventday);
		$tab=$eventday->LoadLevelForDay($eventday->id);
		$contact_registered->fetch($this->fk_user_registered);

		$calendrier->fetch_all($this->fk_levelday, $this->fk_eventday);

		if ($debug){
			print '<b style="color: red;">EVENT : </b><br />';
		var_dump($event);
			print '<br /><b style="color: blue;">DAY : </b><br />';
		var_dump($eventday);
			print '<br /><b style="color: green;">LEVEL : </b><br />';
		var_dump($level);
			print '<br /><b style="color: purple;">OBJECT : </b><br />';
		var_dump($this);
			print '<br /><b style="color: yellow;">CALENDRIER : </b><br />';
		var_dump($calendrier);
			print '<br /><b style="color: orange;">TAB: </b><br />';
		var_dump($tab);
			print '<br />TIME IS : '.$nb;
		print '<br /><b style="color : red;>USER</b><br />"';
		var_dump($contact_registered);
			die('toto');
			}
		// Contenu
		$ics = "BEGIN:VCALENDAR\n";
		$ics.= "VERSION:2.0\n";
		$ics.= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";
		$ics.= "BEGIN:VEVENT\n";

                $time_start = new Datetime($eventday->time_start);
		$time_end = new Datetime($eventday->time_end);
		$time_stamp_start = $eventday->date_event + $time_start->getTimestamp() - strtotime('today midnight');
		$time_stamp_end = $eventday->date_event + $time_end->getTimestamp() - strtotime('today midnight');

		/*Si l'utilisateur est insrit à un groupe, on prend les horaires de celui-ci, sinon, on prend la date de l'évènement.*/
		$heured = (isset($calendrier->lines[0]->heured)) ? $calendrier->lines[0]->heured : $time_stamp_start;
		$heuref = (isset($calendrier->lines[0]->heuref)) ? $calendrier->lines[0]->heuref : $time_stamp_end;

		/*Affichage */
		if ($debug)	{
			print "<br />Date de début - DTSTART:".date("Ymd", $heured)."T".str_replace(":", "", date('His', $heured))."\n";
			print "<br />Date de fin - DTEND:".date("Ymd", $heuref)."T".str_replace(":", "", date('His', $heuref))."\n";
		}
		/*On set au format ANNEEMOISJOURTHEUREMINUTESECONDE*/
		$ics.= "DTSTART:".date("Ymd", $heured)."T".str_replace(":", "", date('His', $heured))."\n";
		$ics.= "DTEND:".date("Ymd", $heuref)."T".str_replace(":", "", date('His', $heuref))."\n";
		/*Titre de la journée*/
		$ics.= "SUMMARY:".$eventday->label."\n";
		/*Si la description de la journée existe, on l'utilise, sinon, on prend celle de l'Évent.*/
		if (isset($eventday->description_web))
			$description = $eventday->description_web;
		else if (isset($eventday->description))
			$description = $eventday->description;
		else
			$description = $event->description;
		
		/*On rend les balises HTML acceptable pour le format.*/
		$description = strip_tags(html_entity_decode($description));
		$description = str_replace("\n"," ",$description);
		$description = str_replace("\r","",$description);
		$description = str_replace("\t","",$description);

		$substit['__REGREF__']		= $this->ref;
		$substit['__EVENEMENT__']	= $event->label;
		$substit['__JOURNEE__']		= $eventday->label;
		$substit['__DATEJOURNEE__']	= dol_print_date($eventday->date_event, 'day');
		$substit['__PARTICIPANT__']	= dolGetFirstLastname($contact_registered->firstname, $contact_registered->lastname);
		$substit['__TIMESTART__']	= $eventday->time_start;
		$substit['__TIMEEND__']	= $eventday->time_end;

		$description= make_substitutions($description, $substit);

		$ics.= "DESCRIPTION:".$description."\n";

		/*Fin de l'évènement.*/
		$ics.= "END:VEVENT\n";
		$ics.= "END:VCALENDAR\n";

		/*On créée le dossier si il n'existe pas*/
		if (!file_exists($conf->event->dir_output . "/".dol_sanitizeFileName($this->ref)))
			mkdir($conf->event->dir_output . "/".dol_sanitizeFileName($this->ref));

		/*On ouvre et on écrit dans le fichier*/
		$filedir=$conf->event->dir_output . "/".dol_sanitizeFileName($this->ref);
		$file = $filedir.'/'.'cal_'.dol_sanitizeFileName($this->ref).".ics";
		$res_file = fopen($file, "w");
		if (!fwrite($res_file, $ics)) print '<br />ICS -> Pb to create : '.$file;
		fclose($res_file);

		/* end création du fichier ics */
		if ($debug) print '<br />FILE : '.$file.'<br />';
		/*On renvoie le nom du fichier créée*/
		return ($file);
	}


	/**
	 *  Load object in memory from database
	 *
	 *  @param	int		$id    Id object
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function fetch($id, $ref='')
	{
		global $langs;

		// Check parameters
		if (! $id && ! $ref )
		{
			$this->error='ErrorWrongParameters';
			dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}

		$sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.fk_soc,";
		$sql.= " t.fk_event,";
		$sql.= " t.fk_eventday,";
		$sql.= " t.fk_levelday,";
		$sql.= " t.datec,";
		$sql.= " t.tms,";
		$sql.= " t.date_valid,";
		$sql.= " d.total_ht,";
		$sql.= " d.total_tva,";
		$sql.= " d.total_ttc,";
		$sql.= " d.tva_tx,";
		$sql.= " t.paye,";
		$sql.= " t.accountancy_code,";
		$sql.= " t.fk_user_create,";
		$sql.= " t.fk_user_valid,";
		$sql.= " t.fk_user_registered,";
		$sql.= " t.fk_statut,";
		$sql.= " t.note_private,";
		$sql.= " t.note_public";

		$sql.=", l.label as level_label";
		$sql.=", d.date_event";


		$sql.= " FROM ".MAIN_DB_PREFIX."event_registration as t";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."event_day as d ON d.rowid = t.fk_eventday";
		$sql.=' LEFT JOIN '.MAIN_DB_PREFIX.'event_level AS l ON l.rowid=t.fk_levelday';
		if($id)
			$sql.= " WHERE t.rowid = ".$id;
		else
			$sql.= " WHERE t.ref = '".$ref."'";

		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id    = $obj->rowid;

				$this->entity = $obj->entity;
				$this->ref = $obj->ref;
				$this->fk_soc = $this->socid = $obj->fk_soc;
				$this->fk_event = $obj->fk_event;
				$this->fk_eventday = $obj->fk_eventday;
				$this->fk_levelday = $obj->fk_levelday;
				$this->date_event = $obj->date_event;
				$this->level_label = $obj->level_label;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->date_valid = $this->db->jdate($obj->date_valid);
				$this->total_ht = $obj->total_ht;
				$this->total_tva = $obj->total_tva;
				$this->total_ttc = $obj->total_ttc;
				$this->tva_tx = $obj->tva_tx;
				$this->paye = $obj->paye;
				$this->accountancy_code = $obj->accountancy_code;
				$this->fk_user_create = $obj->fk_user_create;
				$this->fk_user_valid = $obj->fk_user_valid;
				$this->fk_user_registered = $obj->fk_user_registered;
				$this->fk_statut = $obj->fk_statut;
				$this->note_private = $obj->note_private;
				$this->note_public = $obj->note_public;

				$this->fetch_thirdparty();
				//$this->fetch_lines();

			}
			$this->db->free($resql);

			return 1;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	}


	/**
	 *  Search registration by ref or ID
	 *
	 *  @param	string		$query        query string
 	 *  @param  int		$event
	 *  @param  int		$day
	 *  @return mixed       <0 if KO, > 0 if OK
	 */
	function search_by_ref_or_id($query,$event='',$eventday='')
	{
		global $langs;

		$sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.fk_soc,";
		$sql.= " t.fk_event,";
		$sql.= " t.fk_eventday,";
		$sql.= " t.fk_levelday,";
		$sql.= " t.datec,";
		$sql.= " t.date_valid,";
		$sql.= " t.paye,";
		$sql.= " t.accountancy_code,";
		$sql.= " t.fk_user_create,";
		$sql.= " t.fk_user_valid,";
		$sql.= " t.fk_user_registered,";
		$sql.= " t.fk_statut,";
		$sql.= " t.note_private,";
		$sql.= " t.note_public,";
		$sql.= " e.label as event_label,";
		$sql.= " el.label as eventday_label_level,";
		$sql.= " ed.date_event as event_date";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_registration as t";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."event as e ON t.fk_event = e.rowid";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."event_day as ed ON t.fk_eventday = ed.rowid";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople as sp ON sp.rowid = t.fk_user_registered";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."event_level AS el ON el.rowid=t.fk_levelday";

		$sql.= " WHERE t.entity IN (".getEntity($this->element, 1).")";
		if($event!='') {
			$sql.= ' AND t.fk_event ='.$event;
			$sql.= ' AND CONCAT(sp.firstname, \' \', sp.lastname) LIKE \'%'.$query.'%\'';
		}
		elseif($eventday!='') {
			$sql.= ' AND t.fk_eventday ='.$eventday;
			$sql.= ' AND CONCAT(sp.firstname, \' \', sp.lastname) LIKE \'%'.$query.'%\'';
		}
		else {
			$sql.= ' AND e.label LIKE \'%'.$query.'%\'';
			$sql.= ' OR ed.label LIKE \'%'.$query.'%\'';
			$sql.= ' OR CONCAT(sp.firstname, \' \', sp.lastname) LIKE \'%'.$query.'%\'';
		}
		$sql.= " GROUP BY (t.rowid)";

		dol_syslog(get_class($this)."::search_by_ref_or_id sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{

			$this->line = array();
			$num = $this->db->num_rows($resql);

			$i = 0;
			while( $i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$this->line[$i] = new stdClass();
				$this->line[$i]->id                 = $obj->rowid;
				$this->line[$i]->entity             = $obj->entity;
				$this->line[$i]->ref                = $obj->ref;
				$this->line[$i]->fk_event           = $obj->fk_event;
				$this->line[$i]->paye 				= $obj->paye;
				$this->line[$i]->fk_soc 			= $obj->fk_soc;
				$this->line[$i]->fk_event 			= $obj->fk_event;
				$this->line[$i]->date_event 		= $obj->date_event;
				$this->line[$i]->fk_eventday 		= $obj->fk_eventday;
				$this->line[$i]->fk_levelday 		= $obj->fk_levelday;
				$this->line[$i]->datec 				= $obj->datec;
				$this->line[$i]->date_valid 		= $obj->date_valid;
				$this->line[$i]->fk_user_create 	= $obj->fk_user_create;
				$this->line[$i]->fk_statut 			= $obj->fk_statut;
				$this->line[$i]->fk_user_registered = $obj->fk_user_registered;
				$this->line[$i]->fk_user_valid 		= $obj->fk_user_valid;
				$this->line[$i]->note_public 		= $obj->note_public;
				$this->line[$i]->registration_open 	= $obj->registration_open;
				$this->line[$i]->paye               = $obj->paye;
				$this->line[$i]->event_label 	    = $obj->event_label;
				$this->line[$i]->eventday_label_level   = $obj->eventday_label_level;
				$this->line[$i]->event_date 	    = $this->db->jdate($obj->event_date);
				$i++;
			}
			$this->db->free($resql);
			return $num;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 *  Update object into database
	 *
	 *  @param	User	$user        User that modify
	 *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return int     		   	 <0 if KO, >0 if OK
	 */
	function update($user=0, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->fk_soc)) $this->fk_soc=trim($this->fk_soc);
		if (isset($this->fk_event)) $this->fk_event=trim($this->fk_event);
		if (isset($this->fk_eventday)) $this->fk_eventday=trim($this->fk_eventday);
		if (isset($this->fk_levelday)) $this->fk_levelday=trim($this->fk_levelday);
		if (isset($this->total_ht)) $this->total_ht=trim($this->total_ht);
		if (isset($this->total_tva)) $this->total_tva=trim($this->total_tva);
		if (isset($this->total_ttc)) $this->total_ttc=trim($this->total_ttc);
		if (isset($this->tva_tx)) $this->tva_tx=trim($this->tva_tx);
		if (isset($this->paye)) $this->tva_tx=trim($this->paye);
		if (isset($this->accountancy_code)) $this->accountancy_code=trim($this->accountancy_code);
		if (isset($this->fk_user_create)) $this->fk_user_create=trim($this->fk_user_create);
		if (isset($this->fk_user_valid)) $this->fk_user_valid=trim($this->fk_user_valid);
		if (isset($this->fk_user_registered)) $this->fk_user_registered=trim($this->fk_user_registered);
		if (isset($this->fk_statut)) $this->fk_statut=trim($this->fk_statut);
		if (isset($this->civilite)) $this->civilite=trim($this->civilite);
		if (isset($this->firstname)) $this->firstname=trim($this->firstname);
		if (isset($this->lastname)) $this->lastname=trim($this->lastname);
		if (isset($this->address)) $this->address=trim($this->address);
		if (isset($this->zip)) $this->zip=trim($this->zip);
		if (isset($this->town)) $this->town=trim($this->town);
		if (isset($this->state_id)) $this->state_id=trim($this->state_id);
		if (isset($this->country_id)) $this->country_id=trim($this->country_id);
		if (isset($this->phone)) $this->phone=trim($this->phone);
		if (isset($this->phone_perso)) $this->phone_perso=trim($this->phone_perso);
		if (isset($this->phone_mobile)) $this->phone_mobile=trim($this->phone_mobile);
		if (isset($this->email_registration)) $this->email_registration=trim($this->email_registration);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);



		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event_registration SET";

		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " ref=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " fk_soc='".(isset($this->fk_soc)?$this->db->escape($this->fk_soc):"null")."',";
		$sql.= " fk_event='".(isset($this->fk_event)?$this->db->escape($this->fk_event):"null")."',";
		$sql.= " fk_eventday='".(isset($this->fk_eventday)?$this->db->escape($this->fk_eventday):"null")."',";
		$sql.= " fk_levelday='".(isset($this->fk_levelday)?$this->db->escape($this->fk_levelday):"null")."',";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql.= " date_valid=".(dol_strlen($this->date_valid)!=0 ? "'".$this->db->idate($this->date_valid)."'" : 'null').",";
		$sql.= " total_ht=".(isset($this->total_ht)?$this->total_ht:"null").",";
		$sql.= " total_tva=".(isset($this->total_tva)?$this->total_tva:"null").",";
		$sql.= " total_ttc=".(isset($this->total_ttc)?$this->total_ttc:"null").",";
		$sql.= " tva_tx=".(isset($this->tva_tx)?$this->tva_tx:"null").",";
		$sql.= " paye=".(isset($this->paye)?$this->paye:"null").",";
		$sql.= " accountancy_code=".(isset($this->accountancy_code)?"'".$this->db->escape($this->accountancy_code)."'":"null").",";
		$sql.= " fk_user_create=".(isset($this->fk_user_create)?$this->fk_user_create:"null").",";
		$sql.= " fk_user_valid=".(isset($this->fk_user_valid)?$this->fk_user_valid:"null").",";
		$sql.= " fk_user_registered=".(isset($this->fk_user_registered)?$this->fk_user_registered:"null").",";
		$sql.= " fk_statut=".(isset($this->fk_statut)?$this->fk_statut:"null").",";
		$sql.= " civilite=".(isset($this->civilite)?"'".$this->db->escape($this->civilite)."'":"null").",";
		$sql.= " firstname=".(isset($this->firstname)?"'".$this->db->escape($this->firstname)."'":"null").",";
		$sql.= " naiss=".(dol_strlen($this->naiss)!=0 ? "'".$this->db->idate($this->naiss)."'" : 'null').",";
		$sql.= " lastname=".(isset($this->lastname)?"'".$this->db->escape($this->lastname)."'":"null").",";
		$sql.= " address=".(isset($this->address)?"'".$this->db->escape($this->address)."'":"null").",";
		$sql.= " zip=".(isset($this->zip)?"'".$this->db->escape($this->zip)."'":"null").",";
		$sql.= " town=".(isset($this->town)?"'".$this->db->escape($this->town)."'":"null").",";
		$sql.= " state_id=".(isset($this->state_id)?$this->state_id:"null").",";
		$sql.= " country_id=".(isset($this->country_id)?$this->country_id:"null").",";
		$sql.= " phone=".(isset($this->phone)?"'".$this->db->escape($this->phone)."'":"null").",";
		$sql.= " phone_perso=".(isset($this->phone_perso)?"'".$this->db->escape($this->phone_perso)."'":"null").",";
		$sql.= " phone_mobile=".(isset($this->phone_mobile)?"'".$this->db->escape($this->phone_mobile)."'":"null").",";
		$sql.= " email_registration=".(isset($this->email_registration)?"'".$this->db->escape($this->email_registration)."'":"null").",";
		$sql.= " note_private=".(isset($this->note_private)?"'".$this->db->escape($this->note_private)."'":"null").",";
		$sql.= " note_public=".(isset($this->note_public)?"'".$this->db->escape($this->note_public)."'":"null")."";


		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}


	/**
	 *  Delete object in database
	 *
	 *	@param  User	$user        User that delete
	 *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."event_registration";
			$sql.= " WHERE rowid=".$this->id;

			dol_syslog(get_class($this)."::delete sql=".$sql);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error++; $this->errors[]="Error ".$this->db->lasterror();
			}

			if (! $error)
			{
				$sql = "DELETE FROM ".MAIN_DB_PREFIX."actioncomm";
				$sql.= " WHERE elementtype='event_registration' AND fk_element=".$this->id;

				dol_syslog(get_class($this)."::delete sql=".$sql);
				$resql = $this->db->query($sql);
				if (! $resql) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}
			}
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}


	/**
	 *	Return status label of object
	 *
	 *	@param		int		$mode	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto
	 *	@return		string			Label
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->fk_statut,$mode);
	}

	/**
	 *	Return status label of object
	 *
	 *	@param		int		$fk_statut		Statut id
	 *	@param      int		$mode		0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto
	 *	@return		string				Label
	 */
	function LibStatut($statut,$mode=0)
	{
		global $langs;

		if ($mode == 0)
		{
			return $langs->trans($this->statuts[$statut]);
		}
		if ($mode == 1)
		{
			return $langs->trans($this->statuts_short[$statut]);
		}
		if ($mode == 2)
		{
			if ($statut==0) return img_picto($langs->trans($this->statuts_short[$statut]),'statut0').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==1) return img_picto($langs->trans($this->statuts_short[$statut]),'statut3').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==4) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut8').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==6) return img_picto($langs->trans($this->statuts_short[$statut]),'statut6').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==8) return img_picto($langs->trans($this->statuts_short[$statut]),'statut1').' '.$langs->trans($this->statuts_short[$statut]);
		}
		if ($mode == 3)
		{
			if ($statut==0) return img_picto($langs->trans($this->statuts_short[$statut]),'statut0');
			if ($statut==1) return img_picto($langs->trans($this->statuts_short[$statut]),'statut3');
			if ($statut==4) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4');
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut8');
			if ($statut==6) return img_picto($langs->trans($this->statuts_short[$statut]),'statut6');
			if ($statut==8) return img_picto($langs->trans($this->statuts_short[$statut]),'statut1');
		}
		if ($mode == 4)
		{

			if ($statut==0) return img_picto($langs->trans($this->statuts_short[$statut]),'statut0').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==1) return img_picto($langs->trans($this->statuts_short[$statut]),'statut3').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==4) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut8').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==6) return img_picto($langs->trans($this->statuts_short[$statut]),'statut6').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==8) return img_picto($langs->trans($this->statuts_short[$statut]),'statut1').' '.$langs->trans($this->statuts_short[$statut]);

		}
		if ($mode == 5)
		{
			if ($statut==0) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut0');
			if ($statut==1) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut3');
			if ($statut==4) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut4');
			if ($statut==5) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut8');
			if ($statut==6) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut6');
			if ($statut==8) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut1');
		}
	}

	/**
	 *	Renvoie nom clicable (avec eventuellement le picto)
	 *
	 *	@param		int		$withpicto		0=Pas de picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 *	@param		string	$option			Sur quoi pointe le lien
	 *	@return		string					Chaine avec URL
	 */
	function getNomUrl($withpicto=0,$option='')
	{
		global $langs;

		$result='';

		$lien = '<a href="'.dol_buildpath('/event/registration/fiche.php', 1).'?id='.$this->id.'">';
		$lienfin='</a>';

		$picto='event_registration@event';
		if (! $this->public) $picto='event_registration@event';

		$label=$langs->trans("ShowRegistration").': '.$this->ref.($this->label?' - '.$this->label:'');

		if ($withpicto) $result.=($lien.img_object($label,$picto).$lienfin);
		if ($withpicto && $withpicto != 2) $result.=' ';
		if ($withpicto != 2) $result.=$lien.$this->ref.$lienfin;
		return $result;
	}

	/**
	 *  Update private note
	 *
	 *  @param      string		$note	New value for note
	 *  @return     int      		   	<0 if KO, >0 if OK
	 */
	function update_note($note, $suffix ='')
	{
		if (! $this->table_element)
		{
			dol_syslog(get_class($this)."::update_note was called on objet with property table_element not defined", LOG_ERR);
			return -1;
		}

		$sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
		$sql.= " SET note_private = '".$this->db->escape($note)."'";
		$sql.= " WHERE rowid =". $this->id;

		dol_syslog(get_class($this)."::update_note sql=".$sql, LOG_DEBUG);
		if ($this->db->query($sql))
		{
			$this->note = $note;            // deprecated
			$this->note_private = $note;
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			dol_syslog(get_class($this)."::update_note error=".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 * Set registration on validated statut
	 *
	 */
	function setValid($user,$notrigger=false){
		global $user,$langs,$conf;

		$error=0;
		$now = dol_now();

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event_registration SET";
		$sql.= " date_valid="."'".$this->db->idate($now)."'".",";
		$sql.= " fk_user_valid='".$user->id."',";
		$sql.= " fk_statut=1,";
		$sql.= " ref = '(REGI".$this->id.")'";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::setValid sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{
			$this->ref='(REGI'.$this->id.')';
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.

				// Call triggers
				include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				$interface=new Interfaces($this->db);
				$result=$interface->run_triggers('EVENT_REGISTRATION_VALID',$this,$user,$langs,$conf);
				if ($result < 0) {
					$error++; $this->errors=$interface->errors;
				}
				// End call triggers
			}
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::setValid ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Set registration on confirmed statut
	 *
	 */
	function setConfirmed($notrigger=0){
		global $soc,$user,$langs,$conf;

		$error=0;
		$now = dol_now();

		$defaultref='';
		if(!empty($this->ref))
		{
			$defaultref = $this->ref;
		}
		if (preg_match('/^[\(]?REG/i', $this->ref))
		{
			$create_ref = true;
		}

		if (empty($defaultref)) {
			$defaultref='';
			$create_ref = true;
		}

		if($create_ref)
		{
			$obj = empty($conf->global->EVENT_REGISTRATION_ADDON)?'mod_event_registration_simple':$conf->global->EVENT_REGISTRATION_ADDON;
			if (! empty($conf->global->EVENT_REGISTRATION_ADDON) && is_readable(dol_buildpath("/event/core/models/num/".$conf->global->EVENT_REGISTRATION_ADDON.".php")))
			{
				dol_include_once("/event/core/models/num/".$conf->global->EVENT_REGISTRATION_ADDON.".php");
				$modEvent = new $obj;
				$defaultref = $modEvent->getNextValue($soc,$this);
			}
		}

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event_registration SET";
		$sql.= " date_valid="."'".$this->db->idate($now)."'".",";
		$sql.= " fk_user_valid=".$user->id.",";
		$sql.= " ref='".$defaultref."',";
		$sql.= " fk_statut=4";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::setConfirmed sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{
			// Generate new PDF
			$this->ref = $defaultref;
			$result=event_pdf_create($this->db, $this, '', $langs);

			if ($result <= 0)
			{
				$error++; $this->errors[]="Error ".$this->db->lasterror();
			}

			// Generate new ICS
			$this->ics_create(0);

			if (! $notrigger)
			{
				// Call triggers
				include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				$interface=new Interfaces($this->db);
				$result=$interface->run_triggers('EVENT_REGISTRATION_CONFIRM',$this,$user,$langs,$conf);
				if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// End call triggers
			}
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::setConfirmed ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Set registration on waiting list
	 *
	 */
	function setWaiting(){
		global $soc,$user,$langs,$conf;

		$error=0;
		$now = dol_now();

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event_registration SET";
		$sql.= " date_valid="."'".$this->db->idate($now)."'".",";
		$sql.= " fk_user_valid=".$user->id.",";
		$sql.= " fk_statut=8";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{
			if (! $notrigger)
			{
				// Call triggers
				include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				$interface=new Interfaces($this->db);
				$result=$interface->run_triggers('EVENT_REGISTRATION_WAITING',$this,$user,$langs,$conf);
				if ($result < 0) {
					$error++; $this->errors=$interface->errors;
				}
				// End call triggers
			}
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Cancel registration
	 *
	 */
	function setCancelled($notrigger=0){
		global $soc, $user, $langs, $conf;

		$error=0;
		$now = dol_now();

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event_registration SET";
		$sql.= " date_valid="."'".$this->db->idate($now)."'".",";
		$sql.= " fk_user_valid=".$user->id.",";
		$sql.= " fk_statut=5";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{
			if (! $notrigger)
			{
				// Call triggers
				include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				$interface=new Interfaces($this->db);
				$result=$interface->run_triggers('EVENT_REGISTRATION_CANCELLED',$this,$user,$langs,$conf);
				if ($result < 0) {
					$error++; $this->errors=$interface->errors;
				}
				// End call triggers
			}
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Load event informations
	 * @return int
	 */
	function LoadEventInfo()
	{
		$staticevent = new Event($this->db);
		$ret = $staticevent->fetch($this->fk_event);

		if ($ret > 0)
		{
			$this->event=$staticevent;
			return 1;
		}
		else return -1;

		return 0;
	}

	/**
	 * Load event day informations
	 * @return int
	 */
	function LoadEventDayInfo()
	{
		$staticday = new Day($this->db);
		$ret = $staticday->fetch($this->fk_eventday);

		if ($ret > 0)
		{
			$this->eventday=$staticday;
			return 1;
		}
		else return -1;

		return 0;

	}

	/**
	 *  Tag la facture comme paye completement  + appel trigger EVENT_REGISTRATION_PAID
	 *
	 *  @param	User	$user      	Objet utilisateur qui modifie
	 *  @return int         		<0 if KO, >0 if OK
	 */
	function set_paid($user)
	{
		global $conf,$langs;
		$error=0;

		if ($this->paye != 1)
		{
			$this->db->begin();

			dol_syslog(get_class($this)."::set_paid rowid=".$this->id, LOG_DEBUG);
			$sql = 'UPDATE '.MAIN_DB_PREFIX.'event_registration SET';
			$sql.= ' paye=1';
			$sql.= ' WHERE rowid = '.$this->id;

			$resql = $this->db->query($sql);
			if ($resql)
			{
				// Appel des triggers
				include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				$interface=new Interfaces($this->db);
				$result=$interface->run_triggers('EVENT_REGISTRATION_PAID',$this,$user,$langs,$conf);
				if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// Fin appel triggers
			}
			else
			{
				$error++;
				$this->error=$this->db->error();
				dol_print_error($this->db);
			}

			if (! $error)
			{
				$this->db->commit();
				return 1;
			}
			else
			{
				$this->db->rollback();
				return -1;
			}
		}
		else
		{
			return 0;
		}
	}


	/**
	 *  Tag l'inscription comme non payee completement + appel trigger EVENT_REGISTRATION_UNPAYED
	 *
	 *  @param	User	$user       Object user that change status
	 *  @return int         		<0 if KO, >0 if OK
	 */
	function set_unpaid($user)
	{
		global $conf,$langs;
		$error=0;

		$this->db->begin();

		$sql = 'UPDATE '.MAIN_DB_PREFIX.'event_registration';
		$sql.= ' SET paye=0, fk_statut=1';
		$sql.= ' WHERE rowid = '.$this->id;

		dol_syslog(get_class($this)."::set_unpaid sql=".$sql);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			// Appel des triggers
			include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
			$interface=new Interfaces($this->db);
			$result=$interface->run_triggers('EVENT_REGISTRATION_UNPAYED',$this,$user,$langs,$conf);
			if ($result < 0) { $error++; $this->errors=$interface->errors; }
			// Fin appel triggers
		}
		else
		{
			$error++;
			$this->error=$this->db->error();
			dol_print_error($this->db);
		}

		if (! $error)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *  Met à jour le groupe d'une inscription
	 *
	 *  @param	User	$user       Object user that change status
	 *  @return int         		<0 if KO, >0 if OK
	 */
	function set_level($newlevel)
	{
		global $conf,$langs;
		$error=0;

		$this->db->begin();

		$sql = 'UPDATE '.MAIN_DB_PREFIX.'event_registration';
		$sql.= ' SET fk_levelday='.$newlevel;
		$sql.= ' WHERE rowid = '.$this->id;

		dol_syslog(get_class($this)."::set_level sql=".$sql);
		$resql = $this->db->query($sql);
		if (!$resql)
		{
			$error++;
			$this->error=$this->db->error();
			dol_print_error($this->db);
		}

		if (! $error)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *  Met à jour les infos societe & contact
	 *
	 *  @param	socpeople_id 	$socpeople_id	contact id to registered
	 *  @param	socid			$socid       	socid for registration
	 *  @return int         		<0 if KO, >0 if OK
	 */
	function set_contact($socpeople_id,$socid='')
	{
		global $conf,$langs;
		$error=0;

		$this->db->begin();

		$sql = 'UPDATE '.MAIN_DB_PREFIX.'event_registration SET';
		if($socpeople_id > 0)
			$sql.= ' fk_user_registered='.$socpeople_id;
		if($socpeople_id > 0 && $socid > 0)
			$sql.= ',';
		if($socid > 0)
			$sql.= ' fk_soc='.$socid;
		$sql.= ' WHERE rowid = '.$this->id;

		dol_syslog(get_class($this)."::set_level sql=".$sql);
		$resql = $this->db->query($sql);
		if (!$resql)
		{
			$error++;
			$this->error=$this->db->error();
			dol_print_error($this->db);
		}

		if (! $error)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 * Send a registration by email
	 *
	 * @param unknown $sendto          Email
	 * @param number $sendtoid         Id contact
	 * @param unknown $sujet           Subject
	 * @param unknown $message         Message
	 * @param number $joinpdf         Join PDF
	 * @param string $actiontypecode   Action code for agenda
	 * @param number $joinics         Join ICS
	 * @return > 0 if ok
	 */
	function SendByEmail($eventdayref,$sendto,$sendtoid=0,$sujet,$message,$joinpdf='',$actiontypecode='AC_REMAIL',$joinics='')
	{
		global $conf,$langs,$user;
		$now=dol_now();
//		if (isset($_POST['message'])) 
//			$_POST['message'] = str_replace('"','\'',$_POST['message']);
//		if (isset($_POST['message']) && !$message)
//			$message = $_POST['message'];

		dol_syslog($message, LOG_DEBUG);
		require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
		require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');
		include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/event.class.php");
		$contactforaction=new Contact($this->db);
		$societeforaction=new Societe($this->db);
		if ($this->fk_soc > 0)    $societeforaction->fetch($this->fk_soc);

		// Destinataire : email du participant
		if (dol_strlen($sendto))
		{
			$langs->load("commercial");
			$from = $conf->global->MAIN_INFO_SOCIETE_NOM.'<'.$conf->global->MAIN_MAIL_EMAIL_FROM.'>';
			$replyto = $from;

			// Init to avoid errors
			$filepath = array();
			$filename = array();
			$mimetype = array();

			include_once(DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php');

			if($joinpdf)
			{
				$model = 'registration';
				$ref = dol_sanitizeFileName($this->ref);
				$fileparams = dol_most_recent_file($conf->event->dir_output . '/' . $ref,'','',1);
				$file=$fileparams['fullname'];//obsolète;
				$filedir=$conf->event->dir_output."/".dol_sanitizeFileName($ref);
		  	  	$file = $filedir.'/'.dol_sanitizeFileName($ref).".pdf";
				// Build document if it not exists
				if (! $file || ! is_readable($file))
				{
					// Define output language
					$outputlangs = $langs;
					$newlang='';
					if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id'])) $newlang=$_REQUEST['lang_id'];
					if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang=$this->client->default_lang;
					if (! empty($newlang))
					{
						$outputlangs = new Translate("",$conf);
						$outputlangs->setDefaultLang($newlang);
					}
					dol_include_once("/event/core/modules/registration/modules_registration.php");
					$result=event_pdf_create($this->db, $this, $model?$model:$this->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref, $hookmanager);
					if ($result <= 0)
					{
						dol_print_error($this->db,$result);
						exit;
					}
					$fileparams = dol_most_recent_file($conf->event->dir_output . '/' . $ref,'',1);
					$file=$fileparams['fullname'];//obsolète
					$filedir=$conf->event->dir_output."/".dol_sanitizeFileName($ref);
			  	  	$file = $filedir.'/'.dol_sanitizeFileName($ref).".pdf";
				}
				$filepath = array($file, $filenametmp);
				$filename = array(basename($file), basename($filenametmp));
				$mimetype = array(dol_mimetype($file));
			}
			if ($joinics=='1'){
				$lev = ($joinpdf)?1:0;
				$file=$conf->event->dir_output . "/" . dol_sanitizeFileName($this->ref).'/'.'cal_'.dol_sanitizeFileName($this->ref).'.ics';
				$filepath[$lev] = $file;
				$mimetype[$lev] = dol_mimetype($file);
				$filename[$lev] = 'cal_'.dol_sanitizeFileName($this->ref).'.ics';
			}

			// Envoi du mail
			require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');

			dol_syslog("MAIL : "."$sujet,$sendto,$from,$message,$filepath,$mimetype,$filename,$sendtocc,'',$deliveryreceipt,-1",LOG_DEBUG);

			include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/eventlevel_cal.class.php");
			include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/eventlevel.class.php");
			include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/event.class.php");
			include_once(DOL_DOCUMENT_ROOT . "/custom/event/class/day.class.php");

			$event = new Event($this->db);
			$eventday = new Day($this->db);
			$level = new Eventlevel($this->db);
			$calendrier = new Eventlevel_cal($this->db);
			$contact_registered = new Contact($this->db);

			$contact_registered->fetch($this->fk_user_registered);
			$event->fetch($this->fk_event);
			$level->fetch($this->fk_levelday);
			$eventday->fetch($this->fk_eventday);
			$tab=$eventday->LoadLevelForDay($eventday->id);
			$calendrier->fetch_all($this->fk_levelday, $this->fk_eventday);
			
			if (file_exists(DOL_DOCUMENT_ROOT . "/custom/event/css/custom.css"))  {
				$urlcss=DOL_DOCUMENT_ROOT . "/custom/event/css/custom.css";
				dol_syslog(get_class($this).' :: SendByEmail - CSS FILE EXIST -> '.$urlcss,LOG_DEBUG);
				}
			else {
				$urlcss='';
				dol_syslog(get_class($this).' :: SendByEmail - CSS FILE NOT EXIST -> '.$urlcss,LOG_DEBUG);
			}

			$mailfile = new CMailFile($sujet,$sendto,$from,$message,$filepath,$mimetype,$filename,$sendtocc,'',$deliveryreceipt,'1','','','','',$urlcss);
			
			if ($mailfile->error)
			{
				$error++;
				$this->error=$mailfile->error;
			}
			else
			{

				if ($mailfile->sendfile())
				{

					$error=0;
					// Initialisation donnees
					$actionmsg = $langs->transnoentities('MailSentBy').' '.$from.' '.$langs->transnoentities('To').' '.$sendto.".\n";
					if ($message)
					{
						$actionmsg.=$langs->transnoentities('MailTopic').": ".$sujet."\n";
						$actionmsg.=$langs->transnoentities('TextUsedInTheMessageBody').":\n";
						$actionmsg.=$message;
					}
					$actionmsg2=$langs->transnoentities('MsgAction'.$actiontypecode);

					$this->sendtoid		    = $sendtoid;
					$this->actiontypecode	= $actiontypecode;
					$this->actionmsg 		= $actionmsg;
					$this->actionmsg2		= $actionmsg2;
					$this->fk_element		= $this->id;
					$this->elementtype	    = $this->element;

					// // Appel des triggers
					// include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
					// $interface=new Interfaces($this->db);

					// $trigger_name = ( $actiontypecode=='AC_REMIND' ? 'FICHEREGISTRATION_REMINDBYMAIL' : 'FICHEREGISTRATION_SENTBYMAIL');

					// $result=$interface->run_triggers($trigger_name,$this,$user,$langs,$conf);
					// if ($result < 0) {
					// 	$error++; $this->errors=$interface->errors;
					// }
					// // Fin appel triggers

					if(!$error)
						return 1;
				}
				else
				{
					$langs->load("other");
					if ($mailfile->error)
					{
						$this->error=$langs->trans('ErrorFailedToSendMail',$from,$sendto);
						$error++;
					}
					else
					{
						$this->error = 'No mail sent. Feature is disabled by option MAIN_DISABLE_ALL_MAILS';
						$error++;
					}
				}
			}
		}
		else
		{
			$langs->load("other");
			$this->error=$langs->trans('ErrorMailRecipientIsEmpty');
			dol_syslog('Recipient email is empty');
		}
		return 1;
	}

	/*
	 * Send a sms with confirmation for registration
	*/
	function SendBySms($sendto,$sendtoid='',$message) {
		global $conf, $langs, $user;

		$error=0;

		$smsfrom=$conf->global->EVENT_SMS_NUMBER_FROM;

		// Make substitutions into message
		$substitutionarrayfortest=array();
		complete_substitutions_array($substitutionarrayfortest,$langs);
		$body=make_substitutions($message,$substitutionarrayfortest);

		require_once(DOL_DOCUMENT_ROOT."/core/class/CSMSFile.class.php");

		if ((empty($sendto) || ! str_replace('+','',$sendto)) && ! empty($receiver) && $receiver != '-1')
		{
			$company_static=new Societe($this->db);
			$sendto=$company_static->contact_get_property($receiver,'mobile');
		}

		$smsfile = new CSMSFile($sendto, $smsfrom, $message, $deliveryreceipt, $deferred, $priority, $class);  // This define OvhSms->login, pass, session and account
		$result=$smsfile->sendfile(); // This send SMS

		if ($result > 0)
		{
			dol_syslog($langs->trans("SmsSuccessfulySent",$smsfrom,$sendto));

			$error=0;
			// Initialisation donnees
			$actiontypecode='AC_RESMS';
			$actionmsg = $langs->transnoentities('SmsSentTo').' '.$sendto.".\n";
			if ($message)
			{
				$actionmsg.=$langs->transnoentities('SmsTextUsedInTheMessageBody').":\n";
				$actionmsg.=$message;
			}
			$actionmsg2=$langs->transnoentities('MsgAction'.$actiontypecode);

			$this->sendtoid		    = $sendtoid;
			$this->actiontypecode	= $actiontypecode;
			$this->actionmsg 		= $actionmsg;
			$this->actionmsg2		= $actionmsg2;
			$this->fk_element		= $this->id;
			$this->elementtype	    = $this->element;

			// Appel des triggers
			include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
			$interface=new Interfaces($this->db);
			$result=$interface->run_triggers('FICHEREGISTRATION_SENTBYSMS',$this,$user,$langs,$conf);
			if ($result < 0) {
				$error++; $this->errors=$interface->errors;
			}
			// Fin appel triggers

			if(!$error)
				return 1;
		}
		else
		{
			dol_syslog($langs->trans("ResultKo").' '.$smsfile->error);
			return -1;
		}

		return 0;

	}

	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Registration($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *	Load all detailed lines into this->lines
	 *
	 *	@return     int         1 if OK, < 0 if KO
	 */
	function fetch_lines()
	{
		global $langs;
		$this->lines=array();

		$sql = 'SELECT d.rowid, d.label,';
		$sql.= ' d.date_event as date_start, d.date_event as date_end, d.total_ht, d.total_tva, d.total_ttc, d.tva_tx';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'event_day as d';
		$sql.= ' WHERE d.rowid = '.$this->fk_eventday;

		dol_syslog(get_class($this).'::fetch_lines sql='.$sql, LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num)
			{
				$objp = $this->db->fetch_object($result);
				$line = new RegistrationLigne($this->db);

				$line->rowid	        = $objp->rowid;
				$line->desc             = $langs->trans('Registration').' '.$objp->label. ' - '.dol_print_date($objp->date_start,'daytext');				// Description line
				$line->product_type     = 1;		// Type of line
				$line->product_ref      = $objp->product_ref;		// Ref product
				$line->libelle          = $objp->product_label;
				$line->product_label	= $objp->product_label;		// Label product
				$line->product_desc     = $objp->product_desc;		// Description product
				$line->fk_product_type  = $objp->fk_product_type;	// Type of product
				$line->qty              = 1;
				$line->subprice         = $objp->total_ht;
				$line->tva_tx           = $objp->tva_tx;
				$line->localtax1_tx     = $objp->localtax1_tx;
				$line->localtax2_tx     = $objp->localtax2_tx;
				$line->remise_percent   = $objp->remise_percent;
				$line->fk_remise_except = $objp->fk_remise_except;
				$line->fk_product       = $objp->fk_renew;
				$line->info_bits        = $objp->info_bits;
				$line->total_ht         = $objp->total_ht;
				$line->total_tva        = $objp->total_tva;
				$line->total_localtax1  = $objp->total_localtax1;
				$line->total_localtax2  = $objp->total_localtax2;
				$line->total_ttc        = $objp->total_ttc;
				$line->export_compta    = $objp->fk_export_compta;
				$line->code_ventilation = $objp->fk_code_ventilation;
				$line->rang				= $objp->rang;
				$line->special_code		= $objp->special_code;
				$line->fk_parent_line	= $objp->fk_parent_line;



				$this->lines[$i] = $line;

				$i++;
			}
			$this->db->free($result);
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			dol_syslog(get_class($this).'::fetch_lines '.$this->error,LOG_ERR);
			return -3;
		}
	}

	/**
	 *	Load all registration for thirdparty into this->lines
	 *
	 *	@return     int         1 if OK, < 0 if KO
	 */
	function fetchRegistrationForThirdparty($eventid, $socid)
	{
		global $langs;
		$this->lines=array();

		$sql = 'SELECT re.rowid';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'event_registration as re';
		$sql.= ' WHERE re.fk_soc = '.$socid.' AND re.fk_event='.$eventid;

		dol_syslog(get_class($this).'::fetch_for_thirdparty sql='.$sql, LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num)
			{
				$objp = $this->db->fetch_object($result);
				$registrationstatic = new Registration($this->db);
				$registrationstatic->fetch($objp->rowid);
				$this->lines[$i] = $registrationstatic;

				$i++;
			}
			$this->db->free($result);
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			dol_syslog(get_class($this).'::fetch_for_thirdparty '.$this->error,LOG_ERR);
			return -3;
		}
	}

	/**
	 *	Get an array of orders ID related to registration
	 *
	 $ @param int $id ID registration
	 *	@return     int         1 if OK, < 0 if KO
	 */
	function getOrderForRegistration($id)
	{
		//global $langs;
		$this->lines=array();

		$sql = 'SELECT c.rowid, c.ref';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'commande as c';
		$sql.= ', '.MAIN_DB_PREFIX.'commandedet as order_lines';
		$sql.= ', '.MAIN_DB_PREFIX.'commandedet_extrafields as order_lines_extra';
		$sql.= ' WHERE order_lines_extra.fk_registration = '.$id;
		$sql.= ' AND order_lines.rowid = order_lines_extra.fk_object';
		$sql.= ' AND c.rowid = order_lines.fk_commande';
		$sql.= ' GROUP BY c.rowid';

		dol_syslog(get_class($this).'::getOrderForRegistration sql='.$sql, LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num)
			{
				$objp = $this->db->fetch_object($result);
				$array_orders[$objp->rowid] = $objp->ref;

				$i++;
			}
			$this->db->free($result);
			return $array_orders;
		}
		else
		{
			print $this->error;
			$this->error=$this->db->error();
			dol_syslog(get_class($this).'::getOrderForRegistration '.$this->error,LOG_ERR);
			return -1;
		}
	}

    /**
     * Send registration reminders
     *
     * This method would be launched by CRON.
     * With a loop on each next event, look for non validated
     * registrations and send reminder by email following these rules
     * - 10 days remaining : first email
     * - 3 days : last message before release registration
     * - 0 days : registration will be put away from list (state canceled)
     */
    function cronDoReminder() {
        global $langs, $conf, $user;
        if(!class_exists('Event')) {
            dol_include_once('/event/class/event.class.php');
        }
        $event = new Event($this->db);

        $sortfield = 't.date_start';
        $sortorder = 'ASC';
        $limit = 0;
        $page = 0;
        $offset = $limit * $page;
        $arch='';
        $year = date("Y");
        $filter = array('_onlynext' => ($year > 0 ? $year : date('Y')));

        $res_events = $event->fetch_all($sortorder, $sortfield, $limit, $offset, $arch, $filter);
        if ($res_events > 0) {
            /*
             * Loop on each event
             */
            $nb_reminders=$nb_cancelled=0;
            $message_admin = '';
            foreach ($event->line as $line_event) {
                $registration = new Registration($this->db);
                $message_admin.= '<h2>Relances inscriptions pour '.$line_event->label.' - '.dol_print_date($line_event->date_start,'daytext') . '</h2>';

                $array_relances = $registration->getRemindersForEvent($line_event->id);
                if (is_array($array_relances) && count($array_relances) > 0) {
                    foreach ($array_relances as $registration_to_remind) {
                        if( $registration_to_remind->nbjours < 11) {
                            switch ($registration_to_remind->nbjours) {
                                // Relance à 10 jours
                                case 10:
                                    $registration->sendReminder($registration_to_remind, $message_admin);
                                    $nb_reminders++;
                                    break;

                                // Relance à 3 jours
                                case 3:
                                    $registration->sendReminder($registration_to_remind, $message_admin);
                                    $nb_reminders++;
                                    break;

                                // Mise en liste d'attente
                                case 0:
                                    $registration->sendReminder($registration_to_remind, $message_admin);
                                    $nb_reminders++;
                                    // Cancel registration
                                    $registration_to_remind->setCancelled();
                                    break;

                                default:
                                    break;
                            }
                        } elseif($registration_to_remind->nbjours >  10) {
                            $registration->sendReminder($registration_to_remind, $message_admin);
                            $nb_cancelled++;
                            // Cancel registration
                            $registration_to_remind->setCancelled();
                        } else {
                            $message_admin.= $langs->transnoentities('NoRegistrationToExpire') . '<br />';
                        }
                    }
                } else {
                    $message_admin.= $langs->transnoentities('NoRegistrationToExpire') . '<br />';
                }
            }
            // End Loop on events
            $message_admin.=' <br /><br />'.$langs->transnoentities('RemindersSuccessfullySent',$nb_reminders).'<br />';
            $message_admin.=$langs->transnoentities('RegistrationAutoReminderTotalCancelled',$nb_cancelled);

            // Envoi récap à l'admin
            $sujet_admin = $langs->transnoentities('RegistrationAutoReminderSummarySent');

            $from = $conf->global->MAIN_INFO_SOCIETE_NOM.'<'.$conf->global->MAIN_MAIL_EMAIL_FROM.'>';
			$replyto = $from;
            $sendtoadmin = $conf->global->MAIN_MAIL_EMAIL_FROM;
            $deliveryreceipt='';
            $sendtocc='systeme@aternatik.fr';

			// Init to avoid errors
			$filepath = array();
			$filename = array();
			$mimetype = array();

            // Envoi du mail
			require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
			$mailfile = new CMailFile($sujet_admin,$sendtoadmin,$from,$message_admin,$filepath,$mimetype,$filename,$sendtocc,'',$deliveryreceipt,-1);
			if ($mailfile->error)
			{
				$error++;
				$msg_error=$mailfile->error;
			}
			else
			{
				if ($mailfile->sendfile())
				{
					$error=0;
                }
            }
        }
    }

    /**
     * Get registrations reminders
     *
     * @global $conf
     * @return array    List of registration
     */
    function getRemindersForEvent($eventid)
    {
        global $conf;

        $array_registrations = array();

        $sql_reg = "SELECT r.rowid, r.fk_soc, r.fk_statut, r.fk_eventday, r.ref, r.datec, r.date_valid, r. fk_user_registered, ed.fk_event";
    	$sql_reg.= ", ed.date_event, DATEDIFF( NOW(),DATE_ADD(r.datec, INTERVAL ".$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE." DAY)  ) as nbjours";
    	$sql_reg.=", DATE_ADD(r.datec, INTERVAL ".$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE." DAY) as date_limit";
    	$sql_reg.=", l.label";
    	$sql_reg.=' FROM '.MAIN_DB_PREFIX.'event_registration AS r';
    	$sql_reg.=' LEFT JOIN '.MAIN_DB_PREFIX.'event_day AS ed ON ed.rowid=r.fk_eventday';
    	$sql_reg.=' LEFT JOIN '.MAIN_DB_PREFIX.'event_level AS l ON l.rowid=r.fk_levelday';
    	$sql_reg.=' WHERE r.fk_statut IN (0,1)'; // validated only
    	$sql_reg.=' AND r.fk_event='.$eventid;

    	// Only registration expire in X days
    	$sql_reg.=' AND	DATE_ADD(r.datec, INTERVAL '.$conf->global->EVENT_REGISTRATION_LIMIT_EXPIRE.' DAY) < NOW()';
    	$sql_reg.=' ORDER BY r.fk_statut DESC, r.datec ASC;';

    	dol_syslog(get_class($this).'::getRemindersForEvent sql='.$sql_reg, LOG_DEBUG);
        $result = $this->db->query($sql_reg);

        if ($result)
        {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num)
            {
                $objp = $this->db->fetch_object($result);

                $reg_stat = new Registration($this->db);
                $reg_stat->fk_soc = $objp->fk_soc;
                $reg_stat->fk_statut = $objp->fk_statut;
                $reg_stat->fk_eventday = $objp->fk_eventday;
                $reg_stat->ref = $objp->ref;
                $reg_stat->datec = $this->db->jdate($objp->datec);
                $reg_stat->date_valid = $this->db->jdate($objp->date_valid);
                $reg_stat->fk_user_registered = $objp->fk_user_registered;
                $reg_stat->fk_event = $objp->fk_event;
                $reg_stat->date_event = $this->db->jdate($objp->date_event);
                $reg_stat->nbjours = $objp->nbjours;

                $array_registrations[] = $reg_stat;

                $i++;
            }
            $this->db->free($result);
            return $array_registrations;
        }
        else
        {
            print $this->error;
            $this->error=$this->db->error();
            dol_syslog(get_class($this).'::getRemindersForEvent '.$this->error,LOG_ERR);
            return -1;
        }
    }

    /**
     * Send reminder by email
     * @global type $langs
     * @param type $registration
     * @param type $message_admin
     */
    function sendReminder($registration, &$message_admin) {

        global $langs;

        $eventstat = new Event($this->db);
        if(!class_exists('Contact')) {
            require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
        }
        $contact = new Contact($this->db);
        if(!class_exists('Eventlevel')) {
            dol_include_once('/event/class/eventlevel.class.php');
        }
        $level = new Eventlevel($this->db);
        if(!class_exists('Day')) {
            dol_include_once('/event/class/day.class.php');
        }
        $eventday = new Day($this->db);

        // Infos participants
        $ret = $contact->fetch($registration->fk_user_registered);
        if ($ret > 0) {
            $eventstat->fetch($registration->fk_event);
            $eventday->fetch($registration->fk_eventday);

            if($registration->fk_levelday > 0) {
                $level->fetch($registration->fk_levelday);
            } else {
                $level->label = 'n/a';
            }

            $substit['__REGREF__'] = $registration->ref;
            $substit['__EVENEMENT__'] = $event->label;
            $substit['__JOURNEE__'] = $eventday->label;
            $substit['__DATEJOURNEE__'] = dol_print_date($eventday->date_event, 'day');
            $substit['__PARTICIPANT__'] = dolGetFirstLastname($contact->firstname, $contact->lastname);
            $substit['__LEVEL__'] = $level->label;
			$substit['__TIMESTART__'] = $eventday->time_start;
			$substit['__TIMEEND__'] = $eventday->time_end;

            $sujet = $langs->transnoentities('RegistrationAutoReminderSubject', $registration->nbjours);
            $message = $langs->transnoentities('RegistrationAutoReminderTextIntro');

            if($registration->nbjours == 10) {
                // Relance à 10 jours
                $message.= $langs->transnoentities('RegistrationAutoReminderTextDaysLeft', $registration->nbjours);
                $message.= $langs->transnoentities('RegistrationAutoReminderTextPayment');
                $message.= $langs->transnoentities('RegistrationAutoReminderTextFirstMail');

            } elseif($registration->nbjours == 3) {
                // Relance à 3 jours
                $message.= $langs->transnoentities('RegistrationAutoReminderTextDaysLeft', $registration->nbjours);
                $message.= $langs->transnoentities('RegistrationAutoReminderTextPayment');
                $message.= $langs->transnoentities('RegistrationAutoReminderTextLastMail');

            } elseif($registration->nbjours === 0) {
                // Annulation inscription
                $message.= $langs->transnoentities('RegistrationAutoReminderTextRegistrationCanceled');
            } elseif($registration->nbjours > 10) {
                $message.= $langs->transnoentities('RegistrationAutoReminderTextRegistrationCanceled');
            }

            $sujet = make_substitutions($sujet, $substit);
            $message = make_substitutions($message, $substit);
            $message = str_replace('\n', "\n", $message);

            if (isValidEmail($contact->email)) {
                $message_admin.= 'Relance à ' . dolGetFirstLastname( $contact->firstname,$contact->lastname ) . ' / ref ' . $registration->ref . ' / journée '.dol_print_date($registration->date_event,'daytext') .' / '. $registration->nbjours . 'j avant expiration <br />';
                  if ( $registration->SendByEmail($contact->email,$contact->id,$sujet,$message,1,'AC_REMIND') )
                  {
                    $nb_sent++;
                  }
            }
        } else {
            $message_admin.='No contact defined on registration';
        }
    }


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;

		$this->entity='';
		$this->ref='';
		$this->fk_soc='';
		$this->fk_event='';
		$this->fk_eventday='';
		$this->fk_levelday='';
		$this->datec='';
		$this->tms='';
		$this->date_valid='';
		$this->total_ht='';
		$this->total_tva='';
		$this->total_ttc='';
		$this->tva_tx='';
		$this->accountancy_code='';
		$this->fk_user_create='';
		$this->fk_user_valid='';
		$this->fk_user_registered='';
		$this->fk_statut='';
		$this->civilite='';
		$this->firstname='';
		$this->naiss='';
		$this->lastname='';
		$this->address='';
		$this->zip='';
		$this->town='';
		$this->state_id='';
		$this->country_id='';
		$this->phone='';
		$this->phone_perso='';
		$this->phone_mobile='';
		$this->note_private='';
		$this->note_public='';


	}

}



/**
 *	\class      	RegistrationLigne
 *	\brief      	Classe permettant la gestion des lignes de factures
 *					Gere des lignes de la table llx_facturedet
 */
class RegistrationLigne
{
	var $db;
	var $error;

	var $oldline;

	//! From llx_facturedet
	var $rowid;
	//! Id facture
	var $fk_facture;
	//! Id parent line
	var $fk_parent_line;
	//! Description ligne
	var $desc;
	var $fk_product;		// Id of predefined product
	var $product_type = 0;	// Type 0 = product, 1 = Service

	var $qty;				// Quantity (example 2)
	var $tva_tx;			// Taux tva produit/service (example 19.6)
	var $localtax1_tx;		// Local tax 1
	var $localtax2_tx;		// Local tax 2
	var $subprice;      	// P.U. HT (example 100)
	var $remise_percent;	// % de la remise ligne (example 20%)
	var $fk_remise_except;	// Link to line into llx_remise_except
	var $rang = 0;

	var $info_bits = 0;		// Liste d'options cumulables:
	// Bit 0:	0 si TVA normal - 1 si TVA NPR
	// Bit 1:	0 si ligne normal - 1 si bit discount (link to line into llx_remise_except)

	var $special_code;	// Liste d'options non cumulabels:
	// 1: frais de port
	// 2: ecotaxe
	// 3: ??

	var $origin;
	var $origin_id;

	//! Total HT  de la ligne toute quantite et incluant la remise ligne
	var $total_ht;
	//! Total TVA  de la ligne toute quantite et incluant la remise ligne
	var $total_tva;
	var $total_localtax1; //Total Local tax 1 de la ligne
	var $total_localtax2; //Total Local tax 2 de la ligne
	//! Total TTC de la ligne toute quantite et incluant la remise ligne
	var $total_ttc;

	var $fk_code_ventilation = 0;
	var $fk_export_compta = 0;

	var $date_start;
	var $date_end;

	// Ne plus utiliser
	//var $price;         	// P.U. HT apres remise % de ligne (exemple 80)
	//var $remise;			// Montant calcule de la remise % sur PU HT (exemple 20)

	// From llx_product
	var $ref;				// Product ref (deprecated)
	var $product_ref;       // Product ref
	var $libelle;      		// Product label (deprecated)
	var $product_label;     // Product label
	var $product_desc;  	// Description produit

	var $skip_update_total; // Skip update price total for special lines


	/**
	 *  Constructor
	 *
	 *  @param	DoliDB		$db		Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
	}


}
