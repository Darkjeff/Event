<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012-2015 JF FERRY     		<jfefe@aternatik.fr>
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
 *  \file       event/class/eventlevel.class.php
 *  \ingroup    event
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
//require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Eventlevel //extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='eventlevel';			//!< Id that identify managed objects
	var $table_element='event_level';	//!< Name of table without prefix where object is stored
    var $id;
	var $entity;
	var $datec='';
	var $tms='';
	var $label;
	var $description;
	var $fk_user_create;
	var $rang;
	var $statut;
	var $nb_inscrits;

    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
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
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->fk_user_create)) $this->fk_user_create=trim($this->fk_user_create);
		if (isset($this->rang)) $this->rang=trim($this->rang);
		if (isset($this->statut)) $this->statut=trim($this->statut);



		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."event_level(";

		$sql.= "entity,";
		$sql.= "datec,";
		$sql.= "label,";
		$sql.= "description,";
		$sql.= "fk_user_create,";
		$sql.= "rang,";
		$sql.= "statut";


        $sql.= ") VALUES (";

		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':"'".$this->db->idate($this->datec)."'").",";
		$sql.= " ".(! isset($this->label)?'NULL':"'".$this->db->escape($this->label)."'").",";
		$sql.= " ".(! isset($this->description)?'NULL':"'".$this->db->escape($this->description)."'").",";
		$sql.= " ".(! isset($this->fk_user_create)?$user->id:"'".$this->fk_user_create."'").",";
		$sql.= " ".(! isset($this->rang)?'0':"'".$this->rang."'").",";
		$sql.= " ".(! isset($this->statut)?'0':"'".$this->statut."'")."";


		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."event_level");
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
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.entity,";
		$sql.= " t.datec,";
		$sql.= " t.tms,";
		$sql.= " t.label,";
		$sql.= " t.description,";
		$sql.= " t.fk_user_create,";
		$sql.= " t.rang,";
		$sql.= " t.statut";


        $sql.= " FROM ".MAIN_DB_PREFIX."event_level as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->entity = $obj->entity;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->label = $obj->label;
				$this->description = $obj->description;
				$this->fk_user_create = $obj->fk_user_create;
				$this->rang = $obj->rang;
				$this->statut = $obj->statut;


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
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->fk_user_create)) $this->fk_user_create=trim($this->fk_user_create);
		if (isset($this->rang)) $this->rang=trim($this->rang);
		if (isset($this->statut)) $this->statut=trim($this->statut);



		// Check parameters
		// Put here code to add control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."event_level SET";

		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " label=".(isset($this->label)?"'".$this->db->escape($this->label)."'":"null").",";
		$sql.= " description=".(isset($this->description)?"'".$this->db->escape($this->description)."'":"null").",";
		$sql.= " rang=".(isset($this->rang)?$this->rang:"null").",";
		$sql.= " statut=".(isset($this->statut)?$this->statut:"null")."";


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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."event_level";
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


	/*
	 Efface les groupes pour la journée
	*/

	function raz_level($dayid) {
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."event_level_day WHERE fk_eventday=".$dayid;
		dol_syslog(get_class($this)."::raz_level sql=".$sql);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}
		if ($error)
			return -1;
		else
			return 1;
	}

	/*
	 Ajout d'un groupe pour une journée
	*/
	function DefLevelForDay($level,$dayid) {
		global $conf;
		
		//récupération des données
		$sql = "SELECT ".MAIN_DB_PREFIX."event_day.fk_event, ".MAIN_DB_PREFIX."event_day.fk_user_create FROM ".MAIN_DB_PREFIX."event_day WHERE ".MAIN_DB_PREFIX."event_day.rowid = ".$dayid;
		$resql = $this->db->query($sql);
		$tmp = $resql->fetch_assoc();
		dol_syslog(get_class($this).'::DefLevelForDay level='.$level, LOG_DEBUG);
		dol_syslog(get_class($this).'::DefLevelForDay dayid='.$dayid, LOG_DEBUG);

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."event_level_day (fk_level,fk_eventday,place,fk_event,fk_user_create) VALUES ('".$level."','".$dayid."','".$conf->global->EVENT_LEVEL_DEFAULT_LEVEL_DISPO."','".$tmp['fk_event']."','".$tmp['fk_user_create']."')";

		$resql=$this->db->query($sql);
		if ($resql)
		{
			return true;
		}
		else {
			$this->error=$this->db->error().$sql;
			return false;
		}
	}

	/**
	 *
	 *  @param	int		$id    Id object
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function GetLevelForDay($eventid,$dayid)
	{
		global $langs;
		$sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.place,";
		$sql.= " t.full";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_level_day as t";
		$sql.= " WHERE t.fk_event = ".$eventid;
		$sql.= " AND t.fk_eventday = ".$dayid;

		dol_syslog(get_class($this)."::GetLevelForDay fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num=$this->db->num_rows($resql);
			$i=0;
			if($num<0) {
				$obj = $this->db->fetch_object($resql);
			    print '<br />N' .$obj->rowid;
			    $ret = array();
			    array_push($ret,$obj->rowid);
				$i++;
			}
			// if ($this->db->num_rows($resql))
			// {
			// 	$obj = $this->db->fetch_object($resql);
			//     print '<br />N' .$obj->rowid;
			//     $ret = array();
			//     array_push($ret,$obj->rowid);
			// }
			$this->db->free($resql);

			return $ret;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::GetInfosLevelForDay ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 *  Load object in memory from database
	 *
	 *  @param	int		$id    Id object
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function GetInfosLevelForDay($dayid,$levelid='')
	{
		global $langs;
		$sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.place,";
		$sql.= " t.full";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_level_day as t";
		$sql.= " WHERE t.fk_levent = ".($levelid==''?$this->id:$levelid);
		$sql.= " AND t.fk_eventday = ".$dayid;

		dol_syslog(get_class($this)."::GetInfosLevelForDay fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);
				$this->levelday_id    = $obj->rowid;
				$this->place = $obj->place;
				$this->full = $obj->full;
			}
			$this->db->free($resql);

			return 1;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::GetInfosLevelForDay ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/*
	 * Compte le nombre d'inscrit pour une journée et un groupe donné
	 */
	function countRegistrationForLevel($dayid,$fk_level='0',$fk_statut='') {
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."event_registration WHERE fk_eventday='".$dayid."' AND fk_levelday='".$fk_level."' ";
		
		dol_syslog(get_class($this)."::countRegistrationForLevel sql=".$sql, LOG_DEBUG);
		
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


	function setLevel ($dayid) {
		$sql = "UPDATE ".MAIN_DB_PREFIX."event_level_day SET place = '".$this->place."', full='".$this->full."' WHERE rowid='".$this->id."' AND fk_eventday = '".$dayid."'";
		dol_syslog(get_class($this)."::setLevel sql=".$sql);
		$resql = $this->db->query($sql);
		if ($resql) return 1;
		else { return -1;
		}
	}

	/*
	 Affiche une liste de sélection des groupes pour une journée donnée
	*/
	function print_select_level($dayid,$selected='',$htmlname='fk_level',$fullform="",$showempty=0,$moreclass='') {
		global $langs;
		$sql_level = "SELECT el.rowid, el.label  FROM ".MAIN_DB_PREFIX."event_level as el LEFT JOIN ".MAIN_DB_PREFIX."event_level_day as ld ON ld.fk_level=el.rowid ";
		$sql_level.=" WHERE ld.fk_eventday=$dayid";
		$sql_level.=" ORDER BY el.rang ASC";
		$resql=$this->db->query($sql_level);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num) {
				$i = 0;
				$out='';
				if($fullform) 
				{
					$out.= '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form'.$htmlname.'">';
					$out.= '<input type="hidden" name="id" value="'.$dayid.'">';
					if ($this->fk_registration) $out.= '<input type="hidden" name="fk_registration" value="'.$this->fk_registration.'">';
					$out.= '<input type="hidden" name="action" value="set'.$htmlname.'">';
					$out.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
				}
				if ($htmlname != 'none' || $options_only) $out.= '<select class="flat'.($moreclass?' '.$moreclass:'').'" id="'.$htmlname.'" name="'.$htmlname.'">';

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					
					$out.= '<option value="'.$obj->rowid.'" '.($obj->rowid==$selected?'selected="selected"':'').'>'.$obj->label.'</option>';
				
					$i++;
				}
		
				
				$out.= '</select>';
				if($fullform)
				{
					$out.= '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
					$out.= '</form>';
				}
			}
		}
		return $out;
	}
	/*

	*/
	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid,$new_day)
	{
		global $user,$langs;

		$error=0;

		$object=new Eventlevel($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		// $object->statut=0;

		// Clear fields
		dol_include_once("/event/core/models/num/mod_event_simple.php");
		$modEvent = new mod_event_simple($this->db);
		$object->ref = $modEvent->getNextValue($soc,$object);
		$object->fk_eventday = $new_day;

		// Create clone
		$result=$object->create($user);
		dol_syslog(get_class($this).'::createFromClone fromid='.$fromid, LOG_DEBUG);
		dol_syslog(get_class($this).'::createFromClone new='.$object->id, LOG_DEBUG);
		dol_syslog(get_class($this).'::createFromClone new_ref='.$object->ref, LOG_DEBUG);

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
		$this->datec='';
		$this->tms='';
		$this->label='';
		$this->description='';
		$this->fk_user_create='';
		$this->rang='';
		$this->statut='';


	}

}
?>
