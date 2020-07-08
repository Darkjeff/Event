<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012-2015 JF FERRY			<jfefe@aternatik.fr>
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
 *  \file       event/class/day.class.php
 *  \ingroup    event
 *  \brief      CRUD class file (Create/Read/Update/Delete) to manage day of events
 *				Initialy built by build_class_from_table on 2012-07-02 00:14
 */

require_once 'event.class.php';

/**
 *	Put here description of your class
 */
class Day extends Event
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='event_day';			//!< Id that identify managed objects
	var $table_element='event_day';	//!< Name of table without prefix where object is stored
    var $id;
	var $entity;
	var $ref;
	var $fk_soc;
	var $fk_event;
	var $datec='';
	var $tms='';
	var $date_event='';
	var $label;
	var $description;
	var $description_web;
	var $price_base_type;
	var $total_ht;
	var $total_tva;
	var $total_ttc;
	var $tva_tx;
	var $accountancy_code;
	var $fk_user_create;
	var $fk_statut;
	var $note;
	var $note_public;
	var $registration_open;
	var $time_start;
	var $time_end;
	var $relance_waiting_auto;
	var $relance_confirmed_auto;

    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;

        $this->statuts_short=array(0=>'Draft',4=>'Validated',5=>'Canceled',6=>'terminated',8=>'full',9=>'Closed');
        $this->statuts=array(0=>'Draft',4=>'Validated',5=>'Canceled',6=>'terminated',8=>'full',9=>'Closed');
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
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->description_web)) $this->description_web=trim($this->description_web);
		if (isset($this->price_base_type)) $this->price_base_type=trim($this->price_base_type);
		if (isset($this->total_ht)) $this->total_ht=trim($this->total_ht);
		if (isset($this->total_tva)) $this->total_tva=trim($this->total_tva);
		if (isset($this->total_ttc)) $this->total_ttc=trim($this->total_ttc);
		if (isset($this->tva_tx)) $this->tva_tx=trim($this->tva_tx);
		if (isset($this->time_start)) $this->time_start=trim($this->time_start);
		if (isset($this->time_end)) $this->time_end=trim($this->time_end);
		if (isset($this->total_ht)) $this->total_ht=trim($this->total_ht);
		if (isset($this->fk_user_create)) $this->fk_user_create=trim($this->fk_user_create);
		if (isset($this->note)) $this->note=trim($this->note);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->registration_open)) $this->registration_open=trim($this->registration_open);
		// if (isset($this->relance_waiting_auto)) $this->relance_waiting_auto=trim($this->relance_waiting_auto);
		// if (isset($this->relance_confirmed_auto)) $this->relance_confirmed_auto=trim($this->relance_confirmed_auto);

		$this->fk_statut=$conf->global->DAY_ACTIVE_BY_DEFAULT;

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."event_day(";

		$sql.= "entity,";
		$sql.= "ref,";
		$sql.= "fk_soc,";
		$sql.= "fk_event,";
		$sql.= "datec,";
		$sql.= "date_event,";
		$sql.= "label,";
		$sql.= "description,";
		$sql.= "price_base_type,";
		$sql.= "total_ht,";
		$sql.= "total_tva,";
		$sql.= "total_ttc,";
		$sql.= "tva_tx,";
		$sql.= "accountancy_code,";
		$sql.= "fk_user_create,";
		$sql.= "fk_statut,";
		$sql.= "note,";
		$sql.= "note_public,";
		$sql.= "registration_open,";
		$sql.= "time_start,";
		$sql.= "time_end,";
		$sql.= "description_web";
		// $sql.= "relance_waiting_auto";
		// $sql.= "relance_confirmed_auto";


        $sql.= ") VALUES (";

		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").",";
		$sql.= " ".(! isset($this->fk_soc)?'NULL':"'".$this->fk_soc."'").",";
		$sql.= " ".(! isset($this->fk_event)?'NULL':"'".$this->fk_event."'").",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':"'".$this->db->idate($this->datec)."'").",";
		$sql.= " ".(! isset($this->date_event) || dol_strlen($this->date_event)==0?'NULL':"'".$this->db->idate($this->date_event)."'").",";
		$sql.= " ".(! isset($this->label)?'NULL':"'".$this->db->escape($this->label)."'").",";
		$sql.= " ".(! isset($this->description)?'NULL':"'".$this->db->escape($this->description)."'").",";
		$sql.= " ".(! isset($this->price_base_type)?'NULL':"'".$this->price_base_type."'").",";
		$sql.= " ".(! isset($this->total_ht)?'NULL':"'".$this->total_ht."'").",";
		$sql.= " ".(! isset($this->total_tva)?'NULL':"'".$this->total_tva."'").",";
		$sql.= " ".(! isset($this->total_ttc)?'NULL':"'".$this->total_ttc."'").",";
		$sql.= " ".(! isset($this->tva_tx)?'NULL':"'".$this->tva_tx."'").",";
		$sql.= " ".(! isset($this->accountancy_code)?'NULL':"'".$this->db->escape($this->accountancy_code)."'").",";
		$sql.= " ".(! isset($this->fk_user_create)?$user->id:"'".$this->fk_user_create."'").",";
		$sql.= " ".(! isset($this->fk_statut)?0:"'".$this->fk_statut."'").",";
		$sql.= " ".(! isset($this->note)?'NULL':"'".$this->db->escape($this->note)."'").",";
		$sql.= " ".(! isset($this->note_public)?'NULL':"'".$this->db->escape($this->note_public)."'").",";
		$sql.= " ".(! isset($this->registration_open)?'0':"'1'").",";
		$sql.= " ".(! isset($this->time_start)?"'09:00:00'":"'".$this->db->escape($this->time_start)."'").",";
		$sql.= " ".(! isset($this->time_end)?"'17:00:00'":"'".$this->db->escape($this->time_end)."'").",";
		$sql.= " ".(! isset($this->description_web)?'NULL':"'".$this->db->escape($this->description_web)."'")."";
		// $sql.= " ".(! isset($this->relance_waiting_auto)?'NULL':"'".$this->db->escape($this->relance_waiting_auto)."'").",";
		// $sql.= " ".(! isset($this->relance_confirmed_auto)?'NULL':"'".$this->db->escape($this->relance_confirmed_auto)."'")."";

		$sql.= ")";
		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."event_day");
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


    /**
     *  Load object in memory from database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id='',$ref='')
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.fk_soc,";
		$sql.= " t.fk_event,";
		$sql.= " t.datec,";
		$sql.= " t.tms,";
		$sql.= " t.date_event,";
		$sql.= " t.label,";
		$sql.= " t.description,";
		$sql.= " t.price_base_type,";
		$sql.= " t.total_ht,";
		$sql.= " t.total_tva,";
		$sql.= " t.total_ttc,";
		$sql.= " t.tva_tx,";
		$sql.= " t.accountancy_code,";
		$sql.= " t.fk_user_create,";
		$sql.= " t.fk_statut,";
		$sql.= " t.note,";
		$sql.= " t.note_public,";
		$sql.= " t.registration_open,";
		$sql.= " t.time_start,";
		$sql.= " t.time_end,";
		$sql.= " t.description_web,";
		$sql.= " t.relance_waiting_auto,";
		$sql.= " t.relance_confirmed_auto";


        $sql.= " FROM ".MAIN_DB_PREFIX."event_day as t";
        //$sql.= " WHERE entity IN (".getEntity($this->element, 1).")";
        if ($id > 0) $sql.= " WHERE t.rowid = '".$id."'";
        else
        {
        	$sql.= " AND ref = '".$this->db->escape($ref)."'";
        }

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    				= $obj->rowid;

				$this->entity 				= $obj->entity;
				$this->ref 					= $obj->ref;
				$this->fk_soc 				= $obj->fk_soc;
				$this->socid 				= $this->fk_soc;
				$this->fk_event				= $obj->fk_event;
				$this->datec 				= $this->db->jdate($obj->datec);
				$this->tms 					= $this->db->jdate($obj->tms);
				$this->date_event 			= $this->db->jdate($obj->date_event);
				$this->label 				= $obj->label;
				$this->description 			= $obj->description;
				$this->description_web 			= $obj->description_web;
				$this->price_base_type 			= $obj->price_base_type;
				$this->total_ht 			= $obj->total_ht;
				$this->total_tva 			= $obj->total_tva;
				$this->total_ttc 			= $obj->total_ttc;
				$this->tva_tx 				= $obj->tva_tx;
				$this->accountancy_code 	= $obj->accountancy_code;
				$this->fk_user_create 		= $obj->fk_user_create;
				$this->fk_statut 			= $obj->fk_statut;
				$this->note 				= $obj->note;
				$this->note_public 			= $obj->note_public;
				$this->registration_open 	= $obj->registration_open;
				$this->time_start			= $obj->time_start;
				$this->time_end 			= $obj->time_end;
				$this->relance_waiting_auto = $obj->relance_waiting_auto;
				$this->relance_confirmed_auto = $obj->relance_confirmed_auto;

				// Load $this->thirdparty object
				$this->fetch_thirdparty();

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
     *  Load all objects in memory from database
     *
     *  @param	string		$sortorder    sort order
     *  @param	string		$sortfield    sort field
     *  @param	int			$limit		  limit page if "all" load all entries
     *  @param	int			$offset    	  page
     *  @param	int			$arch    	  display archive or not
     *  @param	array		$filter    	  filter output
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch_all($sortorder="DESC", $sortfield="t.date_event", $limit=0, $offset=1, $arch=0,$filter='')
    {
    	global $langs;

    	$sql = "SELECT";
    	$sql.= " t.rowid,";
		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.fk_soc,";
		$sql.= " t.fk_event,";
		$sql.= " t.datec,";
		$sql.= " t.tms,";
		$sql.= " t.date_event,";
		$sql.= " t.label,";
		$sql.= " t.description,";
		$sql.= " t.price_base_type,";
		$sql.= " t.total_ht,";
		$sql.= " t.total_tva,";
		$sql.= " t.total_ttc,";
		$sql.= " t.tva_tx,";
		$sql.= " t.accountancy_code,";
		$sql.= " t.fk_user_create,";
		$sql.= " t.fk_statut,";
		$sql.= " t.note,";
		$sql.= " t.note_public,";
		$sql.= " t.registration_open,";
		$sql.= " t.time_start,";
		$sql.= " t.time_end,";
		$sql.= " t.description_web";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_day as t";
    	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."event as ev ON t.fk_event = ev.rowid";

    	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON t.fk_soc = s.rowid";
    	$sql.= " WHERE t.entity IN (".getEntity($this->element, 1).")";

    	// Statut with $arch parameter
    	$sql.= " AND t.fk_statut IN (0,4,5";
    	if ($arch)
    		$sql.= ",9";
    	$sql.= ")";

    	//Manage filter
    	if (!empty($filter)){
    		foreach($filter as $key => $value) {
    				$sql.= ' AND '.$key.' LIKE \'%'.$value.'%\'';
    		}
    	}


    	$sql.= " GROUP BY (t.rowid)";
    	$sql.= " ORDER BY ".$sortfield." ".$sortorder;
    	if($limit > 0)
    		$sql.= " ".$this->db->plimit( $limit + 1 ,$offset);

    	dol_syslog(get_class($this)."::fetch_all sql=".$sql, LOG_DEBUG);

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
    			$this->line[$i]->id = $obj->rowid;
    			$this->line[$i]->entity = $obj->entity;
    			$this->line[$i]->ref = $obj->ref;
    			$this->line[$i]->fk_event = $obj->fk_event;
    			$this->line[$i]->fk_soc 			= $obj->fk_soc;
    			$this->line[$i]->datec 				= $obj->datec;
    			$this->line[$i]->date_event 		= $obj->date_event;
    			$this->line[$i]->label 				= stripslashes($obj->label);
    			$this->line[$i]->description 		= stripslashes($obj->description);
    			$this->line[$i]->price_base_type 	= $obj->price_base_type;
    			$this->line[$i]->total_ht 			= $obj->total_ht;
    			$this->line[$i]->total_tva 			= $obj->total_tva;
    			$this->line[$i]->total_ttc 			= $obj->total_ttc;
    			$this->line[$i]->tva_tx 			= $obj->tva_tx;
    			$this->line[$i]->accountancy_code 	= $obj->accountancy_code;
    			$this->line[$i]->fk_user_create 	= $obj->fk_user_create;
    			$this->line[$i]->fk_statut 			= $obj->fk_statut;
    			$this->line[$i]->note 				= $obj->note;
    			$this->line[$i]->note_public 		= $obj->note_public;
    			$this->line[$i]->registration_open 	= $obj->registration_open;
    			$this->line[$i]->time_start 		= $obj->time_start;
    			$this->line[$i]->time_end 	 		= $obj->time_end;
    			$this->line[$i]->description_web 		= stripslashes($obj->description_web);

    			$i++;
    		}
    		$this->db->free($resql);
    		return $num;
    	}
    	else
    	{
    		$this->error="Error ".$this->db->lasterror();
    		dol_syslog(get_class($this)."::fetch_all ".$this->error, LOG_ERR);
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

		if (isset($this->entity)) 			$this->entity=trim($this->entity);
		if (isset($this->ref)) 				$this->ref=trim($this->ref);
		if (isset($this->fk_soc)) 			$this->fk_soc=trim($this->fk_soc);
		if (isset($this->fk_event)) 		$this->fk_event=trim($this->fk_event);
		if (isset($this->label)) 			$this->label=trim($this->label);
		if (isset($this->description))		$this->description=trim($this->description);
		if (isset($this->price_base_type)) 	$this->price_base_type=trim($this->price_base_type);
		if (isset($this->total_ht)) 		$this->total_ht=trim($this->total_ht);
		if (isset($this->total_tva)) 		$this->total_tva=trim($this->total_tva);
		if (isset($this->total_ttc)) 		$this->total_ttc=trim($this->total_ttc);
		if (isset($this->tva_tx)) 			$this->tva_tx=trim($this->tva_tx);
		if (isset($this->accountancy_code)) $this->accountancy_code=trim($this->accountancy_code);
		if (isset($this->fk_user_create)) 	$this->fk_user_create=trim($this->fk_user_create);
		if (isset($this->fk_statut)) 		$this->fk_statut=trim($this->fk_statut);
		if (isset($this->note)) 			$this->note=trim($this->note);
		if (isset($this->note_public)) 		$this->note_public=trim($this->note_public);
		if (isset($this->registration_open)) $this->registration_open=trim($this->registration_open);
		if (isset($this->time_start)) $this->time_start=trim($this->time_start);
		if (isset($this->time_end)) $this->time_end=trim($this->time_end);
		if (isset($this->description_web))		$this->description_web=trim($this->description_web);
		if (isset($this->relance_waiting_auto))		$this->relance_waiting_auto=trim($this->relance_waiting_auto);
		if (isset($this->relance_confirmed_auto))		$this->relance_confirmed_auto=trim($this->relance_confirmed_auto);

		// Check parameters
		// Put here code to add control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."event_day SET";

		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " ref=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " fk_soc=".(isset($this->fk_soc)?$this->fk_soc:"null").",";
		$sql.= " fk_event=".(isset($this->fk_event)?$this->fk_event:"null").",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql.= " date_event=".(dol_strlen($this->date_event)!=0 ? "'".$this->db->idate($this->date_event)."'" : 'null').",";
		$sql.= " label=".(isset($this->label)?"'".$this->db->escape($this->label)."'":"null").",";
		$sql.= " description=".(isset($this->description)?"'".$this->db->escape($this->description)."'":"null").",";
		$sql.= " price_base_type=".(isset($this->price_base_type)?"'".$this->price_base_type."'":"null").",";
		$sql.= " total_ht=".(isset($this->total_ht)?"'".$this->total_ht."'":"null").",";
		$sql.= " total_tva=".(isset($this->total_tva)?"'".$this->total_tva."'":"null").",";
		$sql.= " total_ttc=".(isset($this->total_ttc)?"'".$this->total_ttc."'":"null").",";
		$sql.= " tva_tx=".(isset($this->tva_tx)?"'".$this->tva_tx."'":"null").",";
		$sql.= " accountancy_code=".(isset($this->accountancy_code)?"'".$this->db->escape($this->accountancy_code)."'":"null").",";
		$sql.= " fk_user_create=".(isset($this->fk_user_create)?$this->fk_user_create:"null").",";
		$sql.= " fk_statut=".(isset($this->fk_statut)?$this->fk_statut:"null").",";
		$sql.= " note=".(isset($this->note)?"'".$this->db->escape($this->note)."'":"null").",";
		$sql.= " note_public=".(isset($this->note_public)?"'".$this->db->escape($this->note_public)."'":"null").",";
		$sql.= " time_start=".(isset($this->time_start)?"'".$this->time_start."'":"'09:00:00'").",";
		$sql.= " time_end=".(isset($this->time_end)?"'".$this->time_end."'":"'17:00:00'").",";
		$sql.= " description_web=".(isset($this->description_web)?"'".$this->db->escape($this->description_web)."'":"null").",";
		$sql.= " relance_waiting_auto=".(isset($this->relance_waiting_auto)?"'".$this->db->escape($this->relance_waiting_auto)."'":"null").",";
		$sql.= " relance_confirmed_auto=".(isset($this->relance_confirmed_auto)?"'".$this->db->escape($this->relance_confirmed_auto)."'":"null")."";

        $sql.= " WHERE rowid=".$this->id;
		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."event_day";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
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
			if ($statut==4) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut5').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==6) return img_picto($langs->trans($this->statuts_short[$statut]),'statut6').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==8) return img_picto($langs->trans($this->statuts_short[$statut]),'statut8').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==9) return img_picto($langs->trans($this->statuts_short[$statut]),'statut9').' '.$langs->trans($this->statuts_short[$statut]);
		}
		if ($mode == 3)
		{
			if ($statut==0) return img_picto($langs->trans($this->statuts_short[$statut]),'statut0');
			if ($statut==4) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4');
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut5');
			if ($statut==6) return img_picto($langs->trans($this->statuts_short[$statut]),'statut6');
			if ($statut==8) return img_picto($langs->trans($this->statuts_short[$statut]),'statut8');
			if ($statut==9) return img_picto($langs->trans($this->statuts_short[$statut]),'statut9');
		}
		if ($mode == 4)
		{

			if ($statut==0) return img_picto($langs->trans($this->statuts_short[$statut]),'statut0').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==4) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut5').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==6) return img_picto($langs->trans($this->statuts_short[$statut]),'statut6').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==8) return img_picto($langs->trans($this->statuts_short[$statut]),'statut8').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==9) return img_picto($langs->trans($this->statuts_short[$statut]),'statut9').' '.$langs->trans($this->statuts_short[$statut]);

		}
		if ($mode == 5)
		{
			if ($statut==0) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut0');
			if ($statut==4) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut4');
			if ($statut==5) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut5');
			if ($statut==6) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut6');
			if ($statut==8) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut8');
			if ($statut==9) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut9');
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

		$lien = '<a href="'.dol_buildpath('/event/day/fiche.php', 1).'?id='.$this->id.'">';
		$lienfin='</a>';

		$picto='day@event';
		if (! $this->public) $picto='day@event';

		$label=$langs->trans("ShowEvent").': '.$this->ref.($this->label ?' - '.$this->label:'');

		if ($withpicto) $result.=($lien.img_object($label,$picto).$lienfin);
		if ($withpicto && $withpicto != 2) $result.=' ';
		if ($withpicto != 2) $result.=$lien.$this->label.$lienfin;
		return $result;
	}


	/**
	 *	Renvoie nombre d'inscription en fonction du statut de l'inscription
	 *
	 *	@param		int		$withpicto		0=Pas de picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 *	@param		string	$option			Sur quoi pointe le lien
	 *	@return		string					Chaine avec URL
	 */
	function getNbRegistration($fk_statut='')
	{
		global $conf;
		$sql = "SELECT count(rowid) as nb";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_registration as r";
		$sql.= " WHERE r.fk_eventday=".$this->id;
		if(!empty($fk_statut) || $fk_statut == '0')
		{
			$sql.= " AND r.fk_statut=$fk_statut";
		}
		$sql.= " AND entity = ".$conf->entity;
		dol_syslog("eventday::getNbRegistration sql=".$sql);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$obj = $this->db->fetch_object($resql);
			if ($obj) $max = intval($obj->nb);
			else $max=0;
		}
		else
		{
			dol_syslog("eventday::getNbRegistration sql=".$sql);
			return -1;
		}
		return $max;
	}


	/**
	 * Mark registration as open
	 *
	 */
	function setRegistrationOpen($state){
		global $soc,$user,$langs,$conf;

		$error=0;
		$now = dol_now();

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event_day SET";
		$sql.= " registration_open=$state";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::OpenRegistration sql=".$sql, LOG_DEBUG);
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
	 * Activate registration as open
	 *
	 */
	function setReminederOpen($mode,$state){
		global $soc,$user,$langs,$conf;

		$error=0;
		$now = dol_now();

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event_day SET";
		($mode=='relance_waiting_auto')?$sql.= " relance_waiting_auto=$state":$sql.= " relance_confirmed_auto=$state";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::setReminederOpen sql=".$sql, LOG_DEBUG);
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
	 * Load array of level for a day
	 * @param		int		$dayid		Id de la journée dont on veut les niveaux
	 * @return		array	infos about levels
	 */
	function LoadLevelForDay($dayid,$mode = null) {
		global $conf;

		$sql = "SELECT l.rowid as levelid, ld.rowid, l.label, l.description,ld.place, ld.full, l.rang FROM ".MAIN_DB_PREFIX."event_level as l,".MAIN_DB_PREFIX."event_level_day as ld WHERE fk_eventday = '".$dayid."' AND l.rowid=ld.fk_level;";

		dol_syslog(get_class($this)."::LoadLevelForDay sql=".$sql, LOG_DEBUG);

		$resql=$this->db->query($sql);
		if ($resql) {
			$level_array = array();

			$num = $this->db->num_rows($resql);
			if ($num) {
				$i=0;
				while ($i<$num) {
					$obj = $this->db->fetch_object($resql);

					include_once('eventlevel.class.php');
					$levelstat = new Eventlevel($this->db);
					$res = $levelstat->fetch($obj->levelid);
					if ($res)
					{
						$level_array[$i]['id'] = $levelstat->id;
						$level_array[$i]['label'] = $obj->label;
						$level_array[$i]['description'] = $obj->description;
						$level_array[$i]['place_dispo'] = $obj->place;
						$level_array[$i]['full'] = $obj->full;
						$level_array[$i]['rang'] = $obj->rang;

						$level_list[] = $levelstat->id;

						$nb_inscrits = $levelstat->countRegistrationForLevel($dayid,$levelstat->id,4) + $levelstat->countRegistrationForLevel($dayid,$levelstat->id,1);
						$nb_inscrits+=$levelstat->countRegistrationForLevel($dayid,$levelstat->id,0);
						$level_array[$i]['registered'] = $nb_inscrits;

						$place_left = $obj->place - $nb_inscrits;
						$level_array[$i]['place_left'] = $place_left;

						// si le nb de place - limite est inférieur ou = au nb d'inscrits ET nb d'inscrit inférieur au nombre de place : on met en vert
						if(($obj->place - $conf->global->EVENT_LIMIT_LEVEL_PLACE) <= $nb_inscrits && $nb_inscrits < $obj->place) {
							$level_array[$i]['statut_level'] = 'orange';
						}
						// limite non atteinte
						elseif (($obj->place - $conf->global->EVENT_LIMIT_LEVEL_PLACE) > $nb_inscrits ) {
							$level_array[$i]['statut_level'] = 'green';
						}
						else {
							$level_array[$i]['statut_level'] = 'red';

						}
					}
					$i++;
				}
			}
		}
		(!isset($mode)?$return=$level_array:$return=$level_list);
		return $return;
	}

	/**
	 *      Load properties id_previous and id_next
	 *
	 *      @param	string	$filter		Optional filter
	 *	 	@param  int		$fieldid   	Name of field to use for the select MAX and MIN
	 *      @return int         		<0 if KO, >0 if OK
	 */
	function load_previous_next_ref($filter,$fieldid, $nodbprefix = 0)
	{
		global $conf, $user;

		if (! $this->table_element)
		{
			dol_print_error('',get_class($this)."::load_previous_next_ref was called on objet with property table_element not defined", LOG_ERR);
			return -1;
		}

		// this->ismultientitymanaged contains
		// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
		$alias = 's';
		if ($this->element == 'societe') $alias = 'te';

		$sql = "SELECT te.".$fieldid;
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." as te";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && empty($user->rights->societe->client->voir))) $sql.= ", ".MAIN_DB_PREFIX."societe as s";	// If we need to link to societe to limit select to entity
		if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe_commerciaux as sc ON ".$alias.".rowid = sc.fk_soc";
		$sql.= " WHERE te.date_event < '".$this->db->escape($this->db->idate($this->date_event))."'";
		if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir) $sql.= " AND sc.fk_user = " .$user->id;
		if (! empty($filter)) $sql.=" AND ".$filter;
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)) $sql.= ' AND te.fk_soc = s.rowid';			// If we need to link to societe to limit select to entity
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql.= ' AND te.entity IN ('.getEntity($this->element, 1).')';
		$sql.=" ORDER BY date_event DESC LIMIT 0,1";
		$result = $this->db->query($sql);
		if (! $result)
		{
			$this->error=$this->db->error();
			return -1;
		}
		$row = $this->db->fetch_row($result);
		$this->ref_previous = $row[0];


		$sql = "SELECT te.".$fieldid;
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." as te";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)) $sql.= ", ".MAIN_DB_PREFIX."societe as s";	// If we need to link to societe to limit select to entity
		if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe_commerciaux as sc ON ".$alias.".rowid = sc.fk_soc";
		$sql.= " WHERE te.date_event > '".$this->db->escape($this->db->idate($this->date_event))."'";
		if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir) $sql.= " AND sc.fk_user = " .$user->id;
		if (! empty($filter)) $sql.=" AND ".$filter;
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)) $sql.= ' AND te.fk_soc = s.rowid';			// If we need to link to societe to limit select to entity
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql.= ' AND te.entity IN ('.getEntity($this->element, 1).')';
		// Rem: Bug in some mysql version: SELECT MIN(rowid) FROM llx_socpeople WHERE rowid > 1 when one row in database with rowid=1, returns 1 instead of null
		$sql.=" ORDER BY date_event ASC LIMIT 0,1";
		$result = $this->db->query($sql);
		if (! $result)
		{
			$this->error=$this->db->error();
			return -2;
		}
		$row = $this->db->fetch_row($result);
		$this->ref_next = $row[0];

		return 1;
	}

	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid,$new_event='')
	{
		global $user,$langs;

		$error=0;

		$object=new Day($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;

		// Clear fields
		dol_include_once("/event/core/models/num/mod_event_simple.php");
		$modEvent = new mod_event_simple($this->db);
		$object->ref = $modEvent->getNextValue($soc,$object);
		$object->label = 'CLONEE '.$object->label;
		$object->fk_event = $new_event;

		// Create clone
		$result=$object->create($user);
		dol_syslog(get_class($this).'::createFromClone fromid='.$fromid, LOG_DEBUG);
		dol_syslog(get_class($this).'::createFromClone new='.$object->id, LOG_DEBUG);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
			dol_syslog(get_class($this).'::createFromClone OK fromid='.$fromid, LOG_DEBUG);
		}
		else
		{
			$this->db->rollback();
			dol_syslog(get_class($this).'::createFromClone KO fromid='.$fromid, LOG_DEBUG);
			return -1;
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
		$this->datec='';
		$this->tms='';
		$this->date_event='';
		$this->label='';
		$this->description='';
		$this->price_base_type='';
		$this->total_ht='';
		$this->total_tva='';
		$this->total_ttc='';
		$this->tva_tx='';
		$this->accountancy_code='';
		$this->fk_user_create='';
		$this->fk_statut='';
		$this->note='';
		$this->note_public='';
		$this->registration_open='';
		$this->description_web='';
		$this->relance_waiting_auto='';
		$this->relance_confirmed_auto='';
	}

	/**
	 * Mark Day as clotured
	 *
	 */
	function setClotured($id=0){

	// Update request
	$sql = "UPDATE ".MAIN_DB_PREFIX."event_day SET";
	$sql.= " fk_statut=9";
	if(!isset($id)) $sql.= " WHERE rowid=".$this->id;
	else $sql.= " WHERE rowid=".$id;

	$this->db->begin();
	dol_syslog(get_class($this)."::START setCloturedDay sql=".$sql, LOG_DEBUG);

	$resql = $this->db->query($sql);
	if (! $resql) {
		dol_syslog(get_class($this)."::ERROR setCloturedDay sql=".$sql, LOG_DEBUG);
		$error++; $this->errors[]="Error ".$this->db->lasterror();
		}
	else {
		dol_syslog(get_class($this)."::setClotured sql=".$sql, LOG_DEBUG);
		}
	
	// Commit or rollback
	if ($error) {
		foreach($this->errors as $errmsg) {
			dol_syslog(get_class($this)."::setCloturedDay ".$errmsg, LOG_ERR);
			$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
		$this->db->rollback();
		return -1*$error;
		}
	else {
		$this->db->commit();
		return 1;
		}
	}

}
?>
