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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/event/models/mod_eventday_simple.php
 *	\ingroup    event
 *	\brief      File with class to manage the numbering module Simple for event references
 *	\version    $Id$
 */

dol_include_once("/event/core/models/modules_eventday.php");


/**
 * 	\class      mod_eventday_simple
 * 	\brief      Class to manage the numbering module Simple for event references
 */
class mod_eventday_simple extends ModeleNumRefEventday
{
	var $version='dolibarr';		// 'development', 'experimental', 'dolibarr'
	var $prefix='ED';
    var $error='';
	var $nom = "Simple";



    /**
     * 		\brief      Return description of numbering module
     *      \return     string      Text with description
     */
    function info()
    {
    	global $langs;
      	return $langs->trans("SimpleNumRefModelDesc");
    }


    /**
     * 		\brief      Return an example of numbering module values
     *      \return     string      Example
     */
    function getExample()
    {
        return $this->prefix."0510-0001";
    }



   /**
	*  \brief      Return next value
	*  \param      objsoc		Object third party
	*  \param      event		Object event
	*  \return     string		Value if OK, 0 if KO
	*/
    function getNextValue($objsoc=0,$event='')
    {
		global $db,$conf;

		// D'abord on recupere la valeur max (reponse immediate car champ indexe)
		$posindice=8;
		$sql = "SELECT MAX(SUBSTRING(ref,".$posindice.")) as max";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_day";
		$sql.= " WHERE ref like '".$this->prefix."%'";
		$sql.= " AND entity = ".$conf->entity;
		dol_syslog("mod_eventday_simple::getNextValue sql=".$sql);
		$resql=$db->query($sql);
		if ($resql)
		{
			$obj = $db->fetch_object($resql);
			if ($obj) $max = intval($obj->max);
			else $max=0;
		}
		else
		{
			dol_syslog("mod_eventday_simple::getNextValue sql=".$sql);
			return -1;
		}

		$date=empty($event->datec)?dol_now():$event->datec;

		$yymm = strftime("%y%m",$date);
		$num = sprintf("%04s",$max+1);

		dol_syslog("mod_eventday_simple::getNextValue return ".$this->prefix.$yymm."-".$num);
		return $this->prefix.$yymm."-".$num;
    }


    /**
     * 		\brief      Return next reference not yet used as a reference
     *      \param      objsoc      Object third party
     *      \param      event	Object event
     *      \return     string      Next not used reference
     */
    function event_get_num($objsoc=0,$event='')
    {
        return $this->getNextValue($objsoc,$event);
    }
}

?>