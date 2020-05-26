<?php
/**
 * Copyright (C) 2015    Jean-François Ferry <jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file event/class/eventlevel_cal.class.php
 * \ingroup event
 * \brief Manage location object
 */
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");

/**
 * Session calendar class
 */
class Eventlevel_cal {
	var $db;
	var $error;
	var $errors = array ();
	var $element = 'event';
	var $table_element = 'event_level_day_cal';
	var $id;
	var $fk_event_day;
	var $fk_level;

	var $date_session;
	var $heured;
	var $heuref;
	var $duree;
	var $fk_actioncomm;
	var $lines = array ();

	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	function __construct($DB) {

		$this->db = $DB;
		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param User $user that create
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;

		// Clean parameters

		// Check parameters
		// Put here code to add control on parameters value

		if ($conf->global->EVENT_DOL_AGENDA) {
			$result = $this->createAction ( $user );
			if ($result <= 0) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			} else {
				$this->fk_actioncomm = $result;
			}
		}

		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "event_level_day_cal(";
		$sql .= "fk_level, fk_event_day, date_session, heured, heuref, fk_actioncomm, fk_user_author, fk_user_mod, datec, tms";
		$sql .= ") VALUES (";
		$sql .= "'" . $this->fk_level . "', ";
		$sql .= "'" . $this->fk_event_day . "', ";
		$sql .= "'" . $this->db->idate ( $this->date_session ) . "', ";
		$sql .= "'" . $this->db->idate ( $this->heured ) . "', ";
		$sql .= "'" . $this->db->idate ( $this->heuref ) . "', ";
		$sql .= " " . (! isset ( $this->fk_actioncomm ) ? 'NULL' : "'" . $this->db->escape ( $this->fk_actioncomm ) . "'") . ",";
		$sql .= ' ' . $user->id . ', ';
		$sql .= ' ' . $user->id . ', ';
		$sql .= "'" . $this->db->idate ( dol_now () ) . "', ";
		$sql .= "'" . date('Y-m-d H:i:s') . "'";
		$sql .= ")";


		$this->db->begin ();

		dol_syslog ( get_class ( $this ) . "::create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		if (! $error) {
			$this->id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "event_level_day_cal" );
		}

		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::create " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return $this->id;
		}
	}


	public static function nbunit(){
		$nb = 0;

		if ($this->heuref && $this->heured)
		$nb = ($this->heuref - $this->heured) / 60;
		else {
			$nb = 0;
		}
		dol_syslog ( get_class ( $this ) . "::nbunit " . '', LOG_DEBUG );
		return ($nb);
	}
	/**
	 * Load object in memory from database
	 *
	 * @param int $actionid object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch($id) {

		global $langs;

		$sql = "SELECT";
		$sql .= " s.rowid, s.date_session, s.fk_level, s.heured, s.heuref, s.fk_actioncomm, s.fk_event_day ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "event_level_day_cal as s";
		$sql .= " WHERE s.rowid = " . $id;

		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				$this->id = $obj->rowid;
				$this->fk_level = $obj->fk_level;
				$this->fk_event_day = $obj->fk_event_day;
				$this->date_session = $this->db->jdate ( $obj->date_session );
				$this->heured = $this->db->jdate ( $obj->heured );
				$this->heuref = $this->db->jdate ( $obj->heuref );
				$this->duree = ($this->heuref - $this->heured) / 60;
				$this->fk_actioncomm = $obj->fk_actioncomm;
			}
			$this->db->free ( $resql );

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $actionid object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_by_action($actionid) {

		global $langs;

		$sql = "SELECT";
		$sql .= " s.rowid, s.date_session, s.fk_level, s.heured, s.heuref, s.fk_actioncomm, s.fk_event_day ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "event_level_day_cal as s";
		$sql .= " WHERE s.fk_actioncomm = " . $actionid;

		dol_syslog ( get_class ( $this ) . "::fetch_by_action sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				$this->id = $obj->rowid;
				$this->date_session = $this->db->jdate ( $obj->date_session );
				$this->heured = $this->db->jdate ( $obj->heured );
				$this->heuref = $this->db->jdate ( $obj->heuref );
				$this->duree = ($this->heuref - $this->heured) / 60;
				$this->fk_event_day = $obj->fk_event_day;
				$this->fk_actioncomm = $obj->fk_actioncomm;
				$this->fk_level = $obj->fk_level;
			}
			$this->db->free ( $resql );

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_by_action " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $fk_level     ID of Level
	 * @param int $fkevent_dayy	IF of event day
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_all($fk_level, $fk_event_day) {

		global $langs;

		$sql = "SELECT";
		$sql .= " s.rowid, s.date_session, s.heured, s.fk_actioncomm, s.heuref, s.fk_level, s.fk_event_day";
		$sql .= " FROM " . MAIN_DB_PREFIX . "event_level_day_cal as s";
		$sql .= " WHERE s.fk_level = " . $fk_level;
		$sql .= " AND s.fk_event_day = " . $fk_event_day;
		$sql .= " ORDER BY s.date_session ASC, s.heured ASC";

		dol_syslog ( get_class ( $this ) . "::fetch_all sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->lines = array ();
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			for($i = 0; $i < $num; $i ++) {
				$line = new Eventlevel_cal_line ();

				$obj = $this->db->fetch_object ( $resql );

				$line->id = $obj->rowid;
				$line->date_session = $this->db->jdate ( $obj->date_session );
				$line->heured = $this->db->jdate ( $obj->heured );
				$line->heuref = $this->db->jdate ( $obj->heuref );
				$this->duree = ($this->heuref - $this->heured) / 60;
				$line->fk_level = $obj->fk_level;
				$line->fk_event_day = $obj->fk_event_day;
                $line->fk_actioncomm = $obj->fk_actioncomm;

				$this->lines [$i] = $line;
			}
			$this->db->free ( $resql );
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_all " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Give information on the object
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function info($id) {

		global $langs;

		$sql = "SELECT";
		$sql .= " s.rowid, s.datec, s.tms, s.fk_user_author, s.fk_user_mod";
		$sql .= " FROM " . MAIN_DB_PREFIX . "event_level_day_cal as s";
		$sql .= " WHERE s.rowid = " . $id;

		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				$this->id = $obj->rowid;
				$this->date_creation = $this->db->jdate ( $obj->datec );
				$this->tms = $obj->tms;
				$this->user_creation = $obj->fk_user_author;
				$this->user_modification = $obj->fk_user_mod;
			}
			$this->db->free ( $resql );

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function update($user, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;

		// Clean parameters

		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "event_level_day_cal SET";
		$sql .= " fk_level=" . $this->fk_level . ", ";
		$sql .= " fk_event_day=" . $this->fk_event_day . ", ";
		$sql .= " date_session='" . $this->db->idate ( $this->date_session ) . "',";
		$sql .= " heured='" . $this->db->idate ( $this->heured ) . "',";
		$sql .= " heuref='" . $this->db->idate ( $this->heuref ) . "',";
		$sql .= " fk_user_mod=" . $user->id . " ";
		$sql .= " WHERE rowid = " . $this->id;

		$this->db->begin ();

		dol_syslog ( get_class ( $this ) . "::update sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		if (! $error) {
			if (! $notrigger) {
				// Update Action is needed
				if (! empty ( $this->fk_actioncomm ) && $conf->global->EVENT_DOL_AGENDA) {
					$result = $this->updateAction ( $user );
					if ($result == - 1) {
						$error ++;
						$this->errors [] = "Error " . $this->db->lasterror ();
					}
				}
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::update " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param int $id to delete
	 * @return int <0 if KO, >0 if OK
	 */
	function remove($id) {

		$result = $this->fetch ( $id );
		if (! empty ( $this->fk_actioncomm )) {
			dol_include_once ( '/comm/action/class/actioncomm.class.php' );

			$action = new ActionComm ( $this->db );
			$action->id = $this->fk_actioncomm;
			$action->delete ();
		}

		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "event_level_day_cal";
		$sql .= " WHERE rowid = " . $id;

		dol_syslog ( get_class ( $this ) . "::remove sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );

		if ($resql) {
			return 1;
		} else {
			$this->error = $this->db->lasterror ();
			return - 1;
		}
	}

	/**
	 * Create Action in Dolibarr Agenda
	 *
	 * @param int			fk_session_place Location of session
	 * @param User $user that modify
	 */
	function createAction($user) {

		global $conf, $langs;

		$error = 0;

		dol_include_once ( '/comm/action/class/actioncomm.class.php' );
		require_once 'eventlevel.class.php';

		$action = new ActionComm ( $this->db );
		$event = new Day ( $this->db );
		$level = new Eventlevel ( $this->db);

		$result = $event->fetch ( $this->fk_event_day );
		if ($result < 0) {
			$error ++;
		}

		$result = $level->fetch ( $this->fk_level );
		if ($result < 0) {
			$error ++;
		}

		$action->label = $event->label . ' (' . $level->label . ')';
		$action->datep = $this->heured;
		$action->datef = $this->heuref;
		$action->author = $user; // User saving action
		$action->fk_element = $event->id;
		$action->elementtype = $event->element;
		$action->type_code = 'EVE_SESS';
		if (! empty ( $event->fk_soc )) {
			$action->societe->id = $event->fk_soc;
		}
		if ($error == 0) {
			$result = $action->add ( $user );
			var_dump($action->error);
			if ($result < 0) {
				$error ++;
				dol_syslog ( get_class ( $this ) . "::createAction " . $action->error, LOG_ERR );
				return - 1;
			} else {
				return $result;
			}
		} else {
			dol_syslog ( get_class ( $this ) . "::createAction " . $action->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * update Action in Dolibarr Agenda
	 *
	 * @param User $user that modify
	 */
	function updateAction($user) {

		global $conf, $langs;

		$error = 0;

		dol_include_once ( '/comm/action/class/actioncomm.class.php' );
		require_once 'eventlevel.class.php';


		$action = new ActionComm ( $this->db );
		$event = new Day ( $this->db );
		$level = new Eventlevel ( $this->db);

		$result = $event->fetch ( $this->fk_event_day );
		if ($result < 0) {
			$error ++;
		}

		$result = $action->fetch ( $this->fk_actioncomm );
		if ($result < 0) {
			$error ++;
		}

		$result = $level->fetch ( $this->fk_level );
		if ($result < 0) {
			$error ++;
		}

		if ($error == 0) {

			if ($action->id == $this->fk_actioncomm) {

				$action->label = $event->label . '(' . $level->label . ')';
				$action->location = $event->placecode;
				$action->datep = $this->heured;
				$action->datef = $this->heuref;
				$action->type_code = 'EVE_SESS';

				$result = $action->update ( $user );
			} else {
				$result = $this->createAction ( $user );
			}

			if ($result < 0) {
				$error ++;

				dol_syslog ( get_class ( $this ) . "::updateAction " . $action->error, LOG_ERR );
				return - 1;
			} else {
				return 1;
			}
		} else {
			dol_syslog ( get_class ( $this ) . "::updateAction " . $action->error, LOG_ERR );
			return - 1;
		}
	}

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

		$object=new Eventlevel_cal($this->db);

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

}

class Eventlevel_cal_line {
	var $id;
	var $date_session;
	var $heured;
	var $heuref;
	var $duree;
	var $fk_level;
    var $fk_actioncomm;

	function __construct() {

	}
}
