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
 *      \file       /business/inc/triggers/interface_30_modBusiness_BusinessWorkflow.class.php
 *      \ingroup    business
 *      \brief      Trigger file for workflow of business module
 */


/**
 *      \class      InterfaceBusinessWorkflow
 *      \brief      Classe des fonctions triggers des actions personalisees du workflow
 */

class InterfaceEventWorkflow
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
        $this->family = "business";
        $this->description = "Triggers of this module allows to manage workflow of event";
        $this->version = '1.1';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'event@event';
        $this->disabled_if_workflow = false;
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

    	// Valid registration
        if ($action == 'EVENT_REGISTRATION_VALID')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
            dol_include_once("/event/class/registration.class.php");

	    	$registration = new Registration($this->db);
	    	$registration->fetch($object->id);

	    	$langs->load("other");
	    	$langs->load("bills");
	    	$langs->load("agenda");

	    	$object->actiontypecode='EVE_RV';
	    	if (empty($object->actionmsg2)) $object->actionmsg2=$langs->transnoentities("EventRegistrationValidated",$object->id);
	    	$object->actionmsg=$langs->transnoentities("EventRegistrationValidated",$object->id);
	    	$object->actionmsg.="\n".$langs->transnoentities("Author").': '.$user->login;

	    	$object->sendtoid=0;
	    	$ok=1;
        }

        if ($action == 'EVENT_REGISTRATION_PAID')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	dol_include_once("/event/class/registration.class.php");

        	$registration = new Registration($this->db);
        	$registration->fetch($object->id);

        	$langs->load("other");
        	$langs->load("bills");
        	$langs->load("agenda");

        	$object->actiontypecode='EVE_RPAID';
        	if (empty($object->actionmsg2)) $object->actionmsg2=$langs->transnoentities("EventRegistrationPaid",$object->id);
        	$object->actionmsg=$langs->transnoentities("EventRegistrationPaid",$object->id);
        	$object->actionmsg.="\n".$langs->transnoentities("Author").': '.$user->login;

        	$object->sendtoid=0;
        	$ok=1;
        }

        // Confirm registration
        if ($action == 'EVENT_REGISTRATION_CONFIRM')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	dol_include_once("/event/class/registration.class.php");

        	$registration = new Registration($this->db);
        	$registration->fetch($object->id);

        	$langs->load("other");
        	$langs->load("bills");
        	$langs->load("agenda");

        	$object->actiontypecode='EVE_RC';
        	if (empty($object->actionmsg2)) $object->actionmsg2=$langs->transnoentities("EventRegistrationConfirmed",$object->id);
        	$object->actionmsg=$langs->transnoentities("EventRegistrationConfirmed",$object->id);
        	$object->actionmsg.="\n".$langs->transnoentities("Author").': '.$user->login;

        	$object->sendtoid=0;
        	$ok=1;
        }

        // Set registration in waiting list
        if ($action == 'EVENT_REGISTRATION_WAITING')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	dol_include_once("/event/class/registration.class.php");

        	$registration = new Registration($this->db);
        	$registration->fetch($object->id);

        	$langs->load("other");
        	$langs->load("bills");
        	$langs->load("agenda");

        	$object->actiontypecode='EVE_RQ';
        	if (empty($object->actionmsg2)) $object->actionmsg2=$langs->transnoentities("EventRegistrationSetInWaiting",$object->id);
        	$object->actionmsg=$langs->transnoentities("EventRegistrationSetInWaiting",$object->id);
        	$object->actionmsg.="\n".$langs->transnoentities("Author").': '.$user->login;

        	$object->sendtoid=0;
        	$ok=1;
        }

        // Set registration in waiting list
        if ($action == 'FICHEREGISTRATION_SENTBYMAIL')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	dol_include_once("/event/class/registration.class.php");

        	$registration = new Registration($this->db);
        	$registration->fetch($object->id);

        	$langs->load("other");
        	$langs->load("bills");
        	$langs->load("agenda");

        	$object->actiontypecode='EVE_REMAIL';
        	if (empty($object->actionmsg2)) $object->actionmsg2=$langs->transnoentities("RegistrationSentByMail",$object->ref);
        	if (empty($object->actionmsg))
        	{
        		$object->actionmsg=$langs->transnoentities("RegistrationSentByMail",$object->ref);
	        	$object->actionmsg.="\n".$langs->transnoentities("Author").': '.$user->login;
        	}

        	$object->sendtoid=$object->fk_user_registered;
        	$ok=1;
        }

        // Set registration in waiting list
        if ($action == 'FICHEREGISTRATION_REMINDBYMAIL')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
            dol_include_once("/event/class/registration.class.php");

            $registration = new Registration($this->db);
            $registration->fetch($object->id);

            $langs->load("other");
            $langs->load("bills");
            $langs->load("agenda");

            $object->actiontypecode='EVE_REMIND';
            if (empty($object->actionmsg2)) $object->actionmsg2=$langs->transnoentities("RegistrationSentByMail",$object->ref);
            if (empty($object->actionmsg))
            {
                $object->actionmsg=$langs->transnoentities("RegistrationSentByMail",$object->ref);
                $object->actionmsg.="\n".$langs->transnoentities("Author").': '.$user->login;
            }

            $object->sendtoid=$object->fk_user_registered;
            $ok=1;
        }

        if ($action == 'FICHEREGISTRATION_SENTBYSMS')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	dol_include_once("/event/class/registration.class.php");

        	$registration = new Registration($this->db);
        	$registration->fetch($object->id);

        	$langs->load("other");
        	$langs->load("bills");
        	$langs->load("agenda");

        	$object->actiontypecode='EVE_RESMS';
        	if (empty($object->actionmsg2)) $object->actionmsg2=$langs->transnoentities("RegistrationSentBySms",$object->ref);
        	if (empty($object->actionmsg))
        	{
        		$object->actionmsg=$langs->transnoentities("RegistrationSentBySms",$object->ref);
        		$object->actionmsg.="\n".$langs->transnoentities("Author").': '.$user->login;
        	}

        	$object->sendtoid=0;
        	$ok=1;
        }

        // Add entry in event table
        if ($ok)
        {
        	$now=dol_now();

        	require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
        	require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');
        	$contactforaction=new Contact($this->db);
        	$societeforaction=new Societe($this->db);
        	if ($object->sendtoid > 0) $contactforaction->fetch($object->sendtoid);
        	if ($object->socid > 0)    $societeforaction->fetch($object->socid);

        	// Insertion action
        	require_once(DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php');
        	$actioncomm = new ActionComm($this->db);
        	$actioncomm->type_code   = $object->actiontypecode;
        	$actioncomm->label       = $object->actionmsg2;
        	$actioncomm->note        = $object->actionmsg;
        	$actioncomm->datep       = $now;
        	$actioncomm->datef       = $now;
        	$actioncomm->durationp   = 0;
        	$actioncomm->punctual    = 1;
        	$actioncomm->percentage  = -1;   // Not applicable
        	$actioncomm->contact     = $contactforaction;
        	$actioncomm->societe     = $societeforaction;
        	$actioncomm->author      = $user;   // User saving action
        	$actioncomm->usertodo  	 = $object->sendtoid;	// User affected to action
        	$actioncomm->userdone    = $user;	// User doing action
            $actioncomm->userownerid = $user->id;
        	$actioncomm->fk_element  = $object->id;
        	$actioncomm->elementtype = $object->element;

        	$ret=$actioncomm->add($user);       // User qui saisit l'action
        	if ($ret > 0)
        	{
        		return 1;
        	}
        	else
        	{
        		$error ="Failed to insert : ".$actioncomm->error." ";
        		$this->error=$error;
        		dol_syslog("".__FILE__.": ".$this->error, LOG_ERR);
        		return -1;
        	}
        }

		return 0;
    }
}
?>
