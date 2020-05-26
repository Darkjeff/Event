<?php
/* Copyright (C) 2010-2012  Regis Houssin     <regis@dolibarr.fr>
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
 */

/**
 *      \file       /event/core/triggers/interface_30_modFacture_EventBill.class.php
 *      \ingroup    bill
 *      \brief      Trigger file for create a project with a bill
 */


/**
 *      \class      InterfaceEventBill
 *      \brief      Classe des fonctions triggers des actions personalisees du workflow
 */

class InterfaceEventBill
{
    var $db;

    /**
     *   \brief      Constructeur.
     *   \param      DB      Handler d'acces base
     */
    function __construct($db)
    {
        $this->db = $db ;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "propale";
        $this->description = "Triggers of this module allows to create a project with a signed bill";
        $this->version = '1.1';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'business@business';
    }


    /**
     *   \brief      Renvoi nom du lot de triggers
     *   \return     string      Nom du lot de triggers
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   \brief      Renvoi descriptif du lot de triggers
     *   \return     string      Descriptif du lot de triggers
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   \brief      Renvoi version du lot de triggers
     *   \return     string      Version du lot de triggers
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') return $langs->trans("Development");
        elseif ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

    /**
     *      \brief      Fonction appelee lors du declenchement d'un evenement Dolibarr.
     *                  D'autres fonctions run_trigger peuvent etre presentes dans core/triggers
     *      \param      action      Code de l'evenement
     *      \param      object      Objet concerne
     *      \param      user        Objet user
     *      \param      lang        Objet lang
     *      \param      conf        Objet conf
     *      \return     int         <0 if fatal error, 0 si nothing done, >0 if ok
     */
	function run_trigger($action,$object,$user,$langs,$conf)
    {
        // Mettre ici le code a executer en reaction de l'action
        // Les donnees de l'action sont stockees dans $object


        if ($action == 'BILL_PAYED')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	return $this->_BillPayed($action,$object,$user,$langs,$conf);
        }

		return 0;
    }



	/**
	 * 		\brief      Set status valid
	 */
	function _BillPayed($action,$object,$user,$langs,$conf)
	{
		dol_include_once("/event/class/registration.class.php");

		$registration = new Registration($this->db);
		$registration->fetchObjectLinked('','',$object->id,'facture');

		foreach($registration->linkedObjects as $objecttype => $objects)
		{
			if ($objecttype == 'event_registration') {
				dol_syslog("_BillPayed for registration: ".$objects[0]->id, LOG_DEBUG);
				$res = $registration->fetch($objects[0]->id);
				if($res > 0) 
				{
					$ret = $registration->setConfirmed($user);
					if ($ret < 0) return -1;
				}
				else return -1;
			}
		}

		return 1;
	}

}
?>
