<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012 		JF FERRY			<jfefe@aternatik.fr>
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
 *  \file       dev/skeletons/event.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2012-07-02 00:12
 */

// Put here all includes required by your class file
//require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Event extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='event';			//!< Id that identify managed objects
	var $table_element='event';		//!< Name of table without prefix where object is stored
	//var $table_element_line='business_phase';
	//var $fk_element='fk_business';

	var $id;

	var $entity;
	var $ref;
	var $socid;
	var $datec='';
	var $tms='';
	var $date_start='';
	var $date_end='';
	var $label;
	var $description;
	var $price_day;
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
	var $registration_byday;

	var $statuts_short;
	var $statuts;


    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;

        $this->statuts_short=array(0=>'Draft',5=>'Validated',9=>'Closed');
        $this->statuts=array(0=>'Draft',5=>'Validated',9=>'Closed');
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

		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->socid)) $this->socid=trim($this->socid);
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->price_day)) $this->price_day=trim($this->price_day);
		if (isset($this->total_ht)) $this->total_ht=trim($this->total_ht);
		if (isset($this->total_tva)) $this->total_tva=trim($this->total_tva);
		if (isset($this->total_ttc)) $this->total_ttc=trim($this->total_ttc);
		if (isset($this->tva_tx)) $this->tva_tx=trim($this->tva_tx);
		if (isset($this->accountancy_code)) $this->accountancy_code=trim($this->accountancy_code);
		if (isset($this->note)) $this->note=trim($this->note);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->registration_open)) $this->registration_open=trim($this->registration_open);
		if (isset($this->registration_byday)) $this->registration_byday=trim($this->registration_byday);
		$this->fk_statut=$conf->global->EVENT_ACTIVE_BY_DEFAULT;

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."event(";

		$sql.= "entity,";
		$sql.= "ref,";
		$sql.= "fk_soc,";
		$sql.= "datec,";
		$sql.= "date_start,";
		$sql.= "date_end,";
		$sql.= "label,";
		$sql.= "description,";
		$sql.= "price_day,";
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
		$sql.= "registration_byday";


        $sql.= ") VALUES (";

		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").",";
		$sql.= " ".(! isset($this->socid)?'NULL':"'".$this->socid."'").",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':"'".$this->db->idate($this->datec)."'").",";
		$sql.= " ".(! isset($this->date_start) || dol_strlen($this->date_start)==0?'NULL':"'".$this->db->idate($this->date_start)."'").",";
		$sql.= " ".(! isset($this->date_end) || dol_strlen($this->date_end)==0?'NULL':"'".$this->db->idate($this->date_end)."'").",";
		$sql.= " ".(! isset($this->label)?'NULL':"'".$this->db->escape($this->label)."'").",";
		$sql.= " ".(! isset($this->description)?'NULL':"'".$this->db->escape($this->description)."'").",";
		$sql.= " ".(! isset($this->price_day)?'NULL':"'".str_replace(",", ".", $this->price_day."'")).",";
		$sql.= " ".(! isset($this->total_ht)?'NULL':"'".str_replace(",", ".", $this->total_ht."'")).",";
		$sql.= " ".(! isset($this->total_tva)?'NULL':"'".str_replace(",", ".", $this->total_tva."'")).",";
		$sql.= " ".(! isset($this->total_ttc)?'NULL':"'".str_replace(",", ".", $this->total_ttc."'")).",";
		$sql.= " ".(! isset($this->tva_tx)?'NULL':"'".$this->tva_tx."'").",";
		$sql.= " ".(! isset($this->accountancy_code)?'NULL':"'".$this->db->escape($this->accountancy_code)."'").",";
		$sql.= " ".$user->id.",";
		$sql.= " ".(! isset($this->fk_statut)?'0':"'".$this->fk_statut."'").",";
		$sql.= " ".(! isset($this->note)?'NULL':"'".$this->db->escape($this->note)."'").",";
		$sql.= " ".(! isset($this->note_public)?'NULL':"'".$this->db->escape($this->note_public)."'").",";
		$sql.= " ".(! isset($this->registration_open)?'NULL':"'".$this->registration_open."'").",";
		$sql.= " ".(! isset($this->registration_byday)?'NULL':"'".$this->registration_byday."'")."";


		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."event");

			if (! $notrigger)
			{

	            // Call triggers
	            include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            $interface=new Interfaces($this->db);
	            $result=$interface->run_triggers('EVENT_CREATE',$this,$user,$langs,$conf);
	            if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            // End call triggers



	            /*
	             *  Create event_day on the event
	             */
	            if($conf->global->DISABLE_CREATE_1ST_BAY_BY_DEFAULT=="0") {
		            include_once(DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");
		            if($this->date_end > 0)
		            {
		            	// Nb days between date_start & date_end
		            	$nb_days = num_between_day($this->date_start,$this->date_end,1);

		            }
		            else
		            {
		            	$nb_days = 1;
		            }
		            require_once('day.class.php');
		            $eventday = new Day($this->db);
		            for($i=0; $i < $nb_days; $i++) {
		            	$secondes = $i * 86400;
		            	$Date_journee = date("Y-m-d", $this->date_start+$secondes);

		            	$eventday->fk_soc=$this->socid;
		            	$eventday->datec=dol_now();
		            	$eventday->fk_event=$this->id;
		            	$eventday->label=$conf->global->PREFIX_NAME_EVENTDAY." ".$this->label;
		            	$eventday->total_ht  = price2num($this->price_day,'MU');
						$eventday->total_ttc = price2num($this->price_day) * (1 + ($this->tva_tx / 100));
						$eventday->total_ttc = price2num($eventday->total_ttc,'MU');
		            	$eventday->price_base_type="HT";
		            	$eventday->tva_tx=$this->tva_tx;
		            	$eventday->fk_user_create=$user->id;
		            	$eventday->fk_statut=0;
		            	$eventday->date_event=$this->date_start+$secondes;
		            	$eventday->registration_open=($this->registration_open == 'yes'?1:0);

		            	if(empty($eventday->ref))
		            	{
		            		$defaultref='';
		            		$obj = empty($conf->global->EVENTDAY_ADDON)?'mod_eventday_simple':$conf->global->EVENTDAY_ADDON;
		            		if ( ! empty($conf->global->EVENTDAY_ADDON) && is_readable(dol_buildpath("/event/core/models/num/".$conf->global->EVENTDAY_ADDON.".php")))
		            		{
		            			dol_include_once("/event/core/models/num/".$conf->global->EVENTDAY_ADDON.".php");
		            			$modEvent = new $obj;
		            			$defaultref = $modEvent->getNextValue($soc,$eventday);
		            		}

		            		if (empty($defaultref)) $defaultref=$eventday->ref;
		            	}
		            	else
		            		$defaultref = (GETPOST('ref') ? $_POST['ref']:$eventday->ref);
		            	$eventday->ref=$defaultref;

		            	$resc = $eventday->create($user);
		            	if($resc > 0) {

		            		$eventday->ref=''; // RAZ pour la journée suivante
		            		dol_syslog('Journée du '.$Date_journee.' ajoutée');
		            		$msg[] = 'Journée ajoutée !';
		            	}
		            	else
		            	{
		            		$error++;
		            	}
		            }

	            }
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


    /**
     *  Load object in memory from database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id='',$ref='')
    {
    	global $langs, $conf;

    	dol_syslog(get_class($this)."::fetch id=".$id." ref=".$ref." ref_ext=".$ref_ext);

    	// Check parameters
    	if (! $id && ! $ref)
    	{
    		$this->error=$langs->trans('ErrorWrongParameters');
    		dol_print_error(get_class($this)."::fetch ".$this->error, LOG_ERR);
    		return -1;
    	}

        $sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.fk_soc,";
		$sql.= " t.datec,";
		$sql.= " t.tms,";
		$sql.= " t.date_start,";
		$sql.= " t.date_end,";
		$sql.= " t.label,";
		$sql.= " t.description,";
		$sql.= " t.price_day,";
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
		$sql.= " t.registration_byday";
        $sql.= " FROM ".MAIN_DB_PREFIX."event as t";
        if ($id) $sql.= " WHERE t.rowid = '".$id."'";
        else
        {
        	$sql.= " WHERE entity IN (".getEntity($this->element, 1).")";
        	$sql.= " AND ref = '".$this->db->escape($ref)."'";
        }

        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->entity = $obj->entity;
				$this->ref = $obj->ref;
				$this->socid = $obj->fk_soc;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->date_start = $this->db->jdate($obj->date_start);
				$this->date_end = $this->db->jdate($obj->date_end);
				$this->label = $obj->label;
				$this->description = $obj->description;
				$this->price_day = $obj->price_day;
				$this->total_ht = $obj->total_ht;
				$this->total_tva = $obj->total_tva;
				$this->total_ttc = $obj->total_ttc;
				$this->tva_tx = $obj->tva_tx;
				$this->paye = $obj->paye;
				$this->accountancy_code = $obj->accountancy_code;
				$this->fk_user_create = $obj->fk_user_create;
				$this->fk_statut = $obj->fk_statut?$obj->fk_statut:0;
				$this->note = $obj->note;
				$this->note_public = $obj->note_public;
				$this->registration_open = $obj->registration_open;
				$this->registration_byday = $obj->registration_byday;


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
		if (isset($this->socid)) $this->socid=trim($this->socid);
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->price_day)) $this->price_day=trim($this->price_day);
		if (isset($this->total_ht)) $this->total_ht=trim($this->total_ht);
		if (isset($this->total_tva)) $this->total_tva=trim($this->total_tva);
		if (isset($this->total_ttc)) $this->total_ttc=trim($this->total_ttc);
		if (isset($this->tva_tx)) $this->tva_tx=trim($this->tva_tx);
		if (isset($this->accountancy_code)) $this->accountancy_code=trim($this->accountancy_code);
		if (isset($this->fk_user_create)) $this->fk_user_create=trim($this->fk_user_create);
		if (isset($this->fk_statut)) $this->fk_statut=trim($this->fk_statut);
		if (isset($this->note)) $this->note=trim($this->note);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->registration_open)) $this->registration_open=trim($this->registration_open);
		if (isset($this->registration_byday)) $this->registration_byday=trim($this->registration_byday);



		// Check parameters
		// Put here code to add control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."event SET";

		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " ref=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " fk_soc=".(isset($this->socid)?$this->socid:"null").",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql.= " date_start=".(dol_strlen($this->date_start)!=0 ? "'".$this->db->idate($this->date_start)."'" : 'null').",";
		$sql.= " date_end=".(dol_strlen($this->date_end)!=0 ? "'".$this->db->idate($this->date_end)."'" : 'null').",";
		$sql.= " label=".(isset($this->label)?"'".$this->db->escape($this->label)."'":"null").",";
		$sql.= " description=".(isset($this->description)?"'".$this->db->escape($this->description)."'":"null").",";
		$sql.= " price_day=".(isset($this->price_day)?"'".str_replace(",", ".", $this->db->escape($this->price_day))."'":"null").",";
		$sql.= " total_ht=".(isset($this->total_ht)?"'".str_replace(",", ".", $this->db->escape($this->total_ht))."'":"null").",";
		$sql.= " total_tva=".(isset($this->total_tva)?"'".str_replace(",", ".", $this->db->escape($this->total_tva))."'":"null").",";
		$sql.= " total_ttc=".(isset($this->total_ttc)?"'".str_replace(",", ".", $this->db->escape($this->total_ttc))."'":"null").",";
		$sql.= " tva_tx=".(isset($this->tva_tx)?"'".$this->db->escape($this->tva_tx)."'":"null").",";
		$sql.= " accountancy_code=".(isset($this->accountancy_code)?"'".$this->db->escape($this->accountancy_code)."'":"null").",";
		$sql.= " fk_user_create=".(isset($this->fk_user_create)?$this->fk_user_create:"null").",";
		$sql.= " fk_statut=".(isset($this->fk_statut)?$this->fk_statut:"null").",";
		$sql.= " note=".(isset($this->note)?"'".$this->db->escape($this->note)."'":"null").",";
		$sql.= " note_public=".(isset($this->note_public)?"'".$this->db->escape($this->note_public)."'":"null").",";
		$sql.= " registration_open=".(isset($this->registration_open)?$this->registration_open:"null").",";
		$sql.= " registration_byday=".(isset($this->registration_byday)?$this->registration_byday:"null")."";


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
     *    \brief	Load all objects in memory from database
     *    \param	id	id object
     *  @param	string		$sortorder    sort order
     *  @param	string		$sortfield    sort field
     *  @param	int			$limit		  limit page if "0" load all entries
     *  @param	int			$offset    	  page
     *  @param	int			$arch    	  display closed event or not (by default)
     *  @param	array		$filter    	  filter output
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch_all($sortorder, $sortfield, $limit, $offset, $arch=0,$filter)
    {
    	global $langs, $conf;

    	$sql = "SELECT";
    	$sql.= " t.rowid,";
    	$sql.= " t.entity,";
    	$sql.= " t.ref,";
    	$sql.= " t.fk_soc,";
    	$sql.= " t.datec,";
    	$sql.= " t.tms,";
    	$sql.= " t.date_start,";
    	$sql.= " t.date_end,";
    	$sql.= " t.label,";
    	$sql.= " t.description,";
    	$sql.= " t.price_day,";
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
    	$sql.= " t.registration_byday,";
    	$sql.= " s.rowid as socid, s.nom as socname ";
    	$sql.= " FROM ".MAIN_DB_PREFIX."event as t";
    	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON t.fk_soc = s.rowid";

    	$sql.= " WHERE t.entity IN (".getEntity($this->element, 1).")";

    	// Statut with $arch parameter
    	$sql.= " AND t.fk_statut IN (0,5";
    	if ($arch)
    		$sql.= ",9";
    	$sql.= ")";
    	//Manage filter
    	if (!empty($filter)){
    		foreach($filter as $key => $value) {
    			if (strpos($key,'__date')) {
    				$sql.= ' AND '.$key.' = \''.$this->db->idate($value).'\'';
    			}
    			elseif ($key=='_onlynext')
    			{
    				$sql.= ' AND t.date_start > NOW()';
    			}
    			else {
                    $sql.= ' AND '.$key.' LIKE \'%'.$value.'%\'';
    			}
    		}
    	}
    	$sql.= " GROUP BY t.rowid";
    	$sql.= " ORDER BY ".$sortfield." ".$sortorder;
    	$nbtotalofrecords = 0;
		if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
		{
			$result = $this->db->query($sql);
			$nbtotalofrecords = $this->db->num_rows($result);
		}
    	if($limit > 0)
    		$sql.= " ".$this->db->plimit( $limit + 1 ,$offset);

    	dol_syslog(get_class($this)."::fetchall sql=".$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		$this->line = array();
    		$num = $this->db->num_rows($resql);

    		$i = 0;
    		while( $i < min($num, $conf->liste_limit))
    		{
    			$obj = $this->db->fetch_object($resql);
    			$this->line[$i] = new stdClass();
    			$this->line[$i]->id = $obj->rowid;
    			$this->line[$i]->ref = $obj->ref;
    			$this->line[$i]->entity = $obj->entity;
    			$this->line[$i]->socid = $obj->fk_soc;
                $this->line[$i]->fk_soc = $obj->fk_soc;
    			$this->line[$i]->datec = $this->db->jdate($obj->datec);
    			$this->line[$i]->date_start = $this->db->jdate($obj->date_start);
    			$this->line[$i]->date_end = $this->db->jdate($obj->date_end);
    			$this->line[$i]->label = stripslashes($obj->label);
    			$this->line[$i]->description = stripslashes($obj->description);
    			$this->line[$i]->price_day = stripslashes($obj->price_day);
    			$this->line[$i]->total_ht = stripslashes($obj->total_ht);
    			$this->line[$i]->total_tva = stripslashes($obj->total_tva);
    			$this->line[$i]->total_ttc = stripslashes($obj->total_ttc);
    			$this->line[$i]->tva_tx = stripslashes($obj->tva_tx);
    			$this->line[$i]->paye = $obj->paye;
    			$this->line[$i]->accountancy_code = $obj->accountancy_code;
    			$this->line[$i]->fk_user_create = $obj->fk_user_create;
    			$this->line[$i]->fk_statut = $obj->fk_statut?$obj->fk_statut:0;
    			$this->line[$i]->note = stripslashes($obj->note);
    			$this->line[$i]->note_public = stripslashes($obj->note_public);
    			$this->line[$i]->registration_open = $obj->registration_open;
    			$this->line[$i]->registration_byday = $obj->registration_byday;



    			$i++;
    		}
    		$this->db->free($resql);
    		return array($num, $nbtotalofrecords);
    	}
    	else
    	{
    		$this->error="Error ".$this->db->lasterror();
    		dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
    		return -1;
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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."event";
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

	function ics_create()
	{
	   global $conf,$langs;
	   $langs->load("event@event");

		/* création du fichier ics */

		// Contenu
		$ics = "BEGIN:VCALENDAR\n";
		$ics.= "VERSION:2.0\n";
		$ics.= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";
		$ics.= "BEGIN:VEVENT\n";
		$ics.= "DTSTART:".date("Ymd", $this->date_event)."T".str_replace(":", "", $this->time_start)."\n";
		$ics.= "DTEND:".date("Ymd", $this->date_event)."T".str_replace(":", "", $this->time_end)."\n";
		$ics.= "SUMMARY:".$this->label."\n";
		$chaine = strip_tags(html_entity_decode($this->description));
		$chaine = str_replace("\n"," ",$chaine);
		$chaine = str_replace("\r","",$chaine);
		$chaine = str_replace("\t","",$chaine);
	   $ics.= "DESCRIPTION:".$chaine."\n";
	   $ics.= "END:VEVENT\n";
	   $ics.= "END:VCALENDAR\n";
	   //  Fichier
	  if (!file_exists("./../ics/"))
		  mkdir("./../ics");
	  $file = DOL_DOCUMENT_ROOT."/custom/event/ics/event_".$this->ref.".ics";
		$res_file = fopen($file, "w");
		if(!fwrite($res_file, $ics)) print '<br />ICS -> Pb to create : '.$file;
		// else print '<br />ICS -> Create : '.$file;
		fclose($res_file);
		/* end création du fichier ics */
		return ($file);
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
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==9) return img_picto($langs->trans($this->statuts_short[$statut]),'statut9').' '.$langs->trans($this->statuts_short[$statut]);
		}
		if ($mode == 3)
		{
			if ($statut==0) return img_picto($langs->trans($this->statuts_short[$statut]),'statut0');
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4');
			if ($statut==9) return img_picto($langs->trans($this->statuts_short[$statut]),'statut9');
		}
		if ($mode == 4)
		{

			if ($statut==0) return img_picto($langs->trans($this->statuts_short[$statut]),'statut0').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==5) return img_picto($langs->trans($this->statuts_short[$statut]),'statut4').' '.$langs->trans($this->statuts_short[$statut]);
			if ($statut==9) return img_picto($langs->trans($this->statuts_short[$statut]),'statut9').' '.$langs->trans($this->statuts_short[$statut]);

		}
		if ($mode == 5)
		{
			if ($statut==0) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut0');
			if ($statut==5) return $langs->trans($this->statuts_short[$statut]).' '.img_picto($langs->trans($this->statuts_short[$statut]),'statut4');
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

		$lien = '<a href="'.dol_buildpath('/event/fiche.php', 1).'?id='.$this->id.'">';
		$lienfin='</a>';

		$picto='event@event';
		if (! $this->public) $picto='event@event';

		$label=$langs->trans("ShowEvent").': '.$this->ref.($this->label?' - '.$this->label:'');

		if ($withpicto) $result.=($lien.img_object($label,$picto).$lienfin);
		if ($withpicto && $withpicto != 2) $result.=' ';
		if ($withpicto != 2) $result.=$lien.$this->label.$lienfin;
		return $result;
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
		$sql = "UPDATE ".MAIN_DB_PREFIX."event SET";
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
	 * Mark registration as open
	 *
	 */
	function setRegistrationByDay($state){
		global $soc,$user,$langs,$conf;

		$error=0;
		$now = dol_now();

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event SET";
		$sql.= " registration_byday=$state";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::setRegistrationByDay sql=".$sql, LOG_DEBUG);
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
	 * Mark event as clotured
	 *
	 */
	function setClotured($user){
		global $soc,$user,$langs,$conf;

		$error=0;
		$now = dol_now();

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."event SET";
		$sql.= " fk_statut=9";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::setClotured sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}
		else
		{
			// Update all day
			$sql = "UPDATE ".MAIN_DB_PREFIX."event_day SET";
			$sql.= " fk_statut=9";
			$sql.= " WHERE fk_event=".$this->id;

			dol_syslog(get_class($this)."::setClotured sql=".$sql, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error++; $this->errors[]="Error ".$this->db->lasterror();
			}
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::setClotured ".$errmsg, LOG_ERR);
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
	 * Retourne les journées de l'évènement
	 *
	 * @return	void
	 */
	function get_days($id=null,$mode=null)
	{
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."event_day";
		$sql.= " WHERE fk_event = ".($id!=null?$id:$this->id);

		$res  = $this->db->query($sql);

		if ($res)
		{
			$eventdays = array ();
			while ($rec = $this->db->fetch_array($res))
			{
				$eventday = new Day($this->db);
				$eventdays[] = $rec['rowid'];
			}
			return $eventdays;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	/**
	 * Retourne le nombre journées de l'évènement
	 *
	 * @return	void
	 */
	function get_nb_days()
	{
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."event_day";
		$sql.= " WHERE fk_event = ".$this->id;

		$res  = $this->db->query($sql);

		if ($res)
		{
			$num = $this->db->num_rows($res);
			$this->nb_inscrits = $num;
			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	/*
	 * Compte le nombre d'inscrit pour un évènement
	*/
	function countRegistrationForEvent($fk_statut='4') {

		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."event_registration WHERE fk_event='".$this->id."' AND fk_statut='".$fk_statut."' ";

		dol_syslog(get_class($this)."::countRegistrationForEvent sql=".$sql, LOG_DEBUG);

		if(!empty($fk_statut) || $fk_statut == '0')
		{
			$sql.= " AND fk_statut=$fk_statut";
		}
		$resql=$this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$this->nb_inscrits = $num;
			return $num;
		}
		else return 0;
	}

	/**
	 *      Load properties id_previous and id_next
	 *
	 *      @param	string	$filter		Optional filter
	 *	 	@param  int		$fieldid   	Name of field to use for the select MAX and MIN
	 *      @return int         		<0 if KO, >0 if OK
	 */
	function load_previous_next_ref($filter,$fieldid,$nodbprefix = 0)
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
		$sql.= " WHERE te.date_start < '".$this->db->escape($this->db->idate($this->date_start))."'";
		if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir) $sql.= " AND sc.fk_user = " .$user->id;
		if (! empty($filter)) $sql.=" AND ".$filter;
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)) $sql.= ' AND te.fk_soc = s.rowid';			// If we need to link to societe to limit select to entity
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql.= ' AND te.entity IN ('.getEntity($this->element, 1).')';
		$sql.=" ORDER BY date_start DESC LIMIT 0,1";
		//print $sql."<br>";
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
		$sql.= " WHERE te.date_start > '".$this->db->escape($this->db->idate($this->date_start))."'";
		if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir) $sql.= " AND sc.fk_user = " .$user->id;
		if (! empty($filter)) $sql.=" AND ".$filter;
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)) $sql.= ' AND te.fk_soc = s.rowid';			// If we need to link to societe to limit select to entity
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql.= ' AND te.entity IN ('.getEntity($this->element, 1).')';
		// Rem: Bug in some mysql version: SELECT MIN(rowid) FROM llx_socpeople WHERE rowid > 1 when one row in database with rowid=1, returns 1 instead of null
		$sql.=" ORDER BY date_start ASC LIMIT 0,1";
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
	 *	Load all thirdparty for an event
	 *
	 *	@return     array         array with id => 'thirdparty name'
	 */
	function getThirdpartiesForEvent()
	{
		global $langs;
		$ret=array();

		$sql = 'SELECT s.nom, re.rowid, re.fk_soc';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'event_registration as re';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as s ON s.rowid=re.fk_soc';
		$sql.= ' WHERE re.fk_event='.$this->id;
		$sql.=" GROUP BY re.fk_soc";
		$sql.=" ORDER BY s.nom ASC";

		dol_syslog(get_class($this).'::getThirdpartiesForEvent sql='.$sql, LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num)
			{
				$objp = $this->db->fetch_object($result);

				$ret[$objp->fk_soc] = $objp->nom ;

				$i++;
			}
			$this->db->free($result);
			return $ret;
		}
		else
		{
			$this->error=$this->db->error();
			dol_syslog(get_class($this).'::getThirdpartiesForEvent '.$this->error,LOG_ERR);
			return -1;
		}
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

		$object=new Event($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;

		// Clear fields
		dol_include_once("/event/core/models/num/mod_event_simple.php");
		$modEvent = new mod_event_simple($this->db);
		$object->ref = $modEvent->getNextValue($soc,$object);
		$object->label = 'CLONE '.$object->label;

		// Create clone
		$result=$object->create($user, 1);
		dol_syslog(get_class($this).'::createFromClone from='.$fromid, LOG_DEBUG);
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
		}
		else
		{
			$this->db->rollback();
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
		$this->datec='';
		$this->tms='';
		$this->date_start='';
		$this->date_end='';
		$this->label='';
		$this->description='';
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
		$this->registration_byday='';


	}


}
?>
