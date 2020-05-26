<?php
/* Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This file is an example to follow to add your own email selector inside
 * the Dolibarr email tool.
 * Follow instructions given in README file to know what to change to build
 * your own emailing list selector.
 * Code that need to be changed in this file are marked by "CHANGE THIS" tag.
 */

/**
 *    	\file       event/core/modules/mailings/registrationbyday.modules.php
 *		\ingroup    mailing
 *		\brief      file to provide a list of recipients for mailing module
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/mailings/modules_mailings.php';


// CHANGE THIS: Class name must be called mailing_xxx with xxx=name of your selector

/**
	    \class      mailing_example
		\brief      Class to manage a list of personalised recipients for mailing feature
*/
class mailing_registrationbyday extends MailingTargets
{
    // CHANGE THIS: Put here a name not already used
    var $name='registrationbyday';
    // CHANGE THIS: Put here a description of your selector module.
    // This label is used if no translation is found for key MailingModuleDescXXX where XXX=name is found
    var $desc='Liste des contacts inscrits à une journée';
	// CHANGE THIS: Set to 1 if selector is available for admin users only
    var $require_admin=1;

    var $require_module=array('event');
    var $picto='event@event';
    var $db;


    // CHANGE THIS: Constructor name must be called mailing_xxx with xxx=name of your selector
	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
    function __construct($db)
    {
        $this->db=$db;
    }


    /**
     *  This is the main function that returns the array of emails
     *
     *  @param	int		$mailing_id    	Id of mailing. No need to use it.
     *  @param  array	$filtersarray   If you used the formFilter function. Empty otherwise.
     *  @return int           			<0 if error, number of emails added if ok
     */
    function add_to_target($mailing_id,$filtersarray=array())
    {
        $target = array();

        global $conf, $langs;
        
        $cibles = array();
        
        // La requete doit retourner: id, email, fk_contact, name, firstname, other
        $sql  = "SELECT c.rowid as id, c.email as email, c.rowid as fk_contact,";
        $sql.= " c.lastname as name, c.firstname as firstname, c.civilite,";
        $sql.= " r.rowid as registration_id,";
        $sql.= " ed.date_event,";
        $sql.= " e.label as event_label";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c,";
        $sql .= " ".MAIN_DB_PREFIX."event_registration as r";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."event as e ON e.rowid=r.fk_event";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."event_day as ed ON ed.rowid=r.fk_eventday";
        $sql .= " WHERE c.entity IN (".getEntity('societe', 1).")";
        $sql .= " AND c.rowid=r.fk_user_registered";
        $sql .= " AND c.email != ''"; // Note that null != '' is false
        $sql .= " AND c.no_email = 0";
        
        foreach($filtersarray as $key)
        {
        	$sql.= " AND r.fk_eventday='$key'";
        	
        }
        $sql.= " ORDER BY c.email";
        
        // Stocke destinataires dans cibles
        $result=$this->db->query($sql);
        if ($result)
        {
        	$num = $this->db->num_rows($result);
        	$i = 0;
        	$j = 0;
        
        	dol_syslog(get_class($this)."::add_to_target mailing ".$num." targets found");
        
        	$old = '';
        	while ($i < $num)
        	{
        		$obj = $this->db->fetch_object($result);
        		if ($old <> $obj->email)
        		{
        			$cibles[$j] = array(
	        			'email' => $obj->email,
	        			'fk_contact' => $obj->fk_contact,
	        			'name' => strtoupper($obj->name),
	        			'firstname' => ucfirst($obj->firstname),
	        			'other' =>
		        			$langs->transnoentities("EventLabel").'='.$obj->event_label.';'.
		        			$langs->transnoentities("UserTitle").'='.( $obj->civilite ? $langs->transnoentities("Civility".$obj->civilite) : '' ).';'.
		        			$langs->transnoentities("EventDate").'='.dol_print_date($obj->date_event,'daytext'),
	        			'source_url' => $this->url($obj->registration_id),
	        			'source_id' => $obj->registration_id,
	        			'source_type' => 'event_registration'
        			);
        			$old = $obj->email;
        			$j++;
        		}
        
        		$i++;
        	}
        }
        else
        {
        	dol_syslog($this->db->error());
        	$this->error=$this->db->error();
        	return -1;
        }
        return parent::add_to_target($mailing_id, $cibles);
        
        
		// ----- Your code end here -----

      
    }


    /**
	 *	On the main mailing area, there is a box with statistics.
	 *	If you want to add a line in this report you must provide an
	 *	array of SQL request that returns two field:
	 *	One called "label", One called "nb".
	 *
	 *	@return		array		Array with SQL requests
	 */
	function getSqlArrayForStats()
	{
		return array();
	}


    /**
     *	Return here number of distinct emails returned by your selector.
     *	For example if this selector is used to extract 500 different
     *	emails from a text file, this function must return 500.
     *
     *	@return		int
     */
    function getNbOfRecipients()
    {
	    $sql  = "SELECT count(distinct(c.email)) as nb";
		$sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c,";
		$sql .= " ".MAIN_DB_PREFIX."event_registration as r";
		$sql .= " WHERE c.rowid=r.fk_user_registered";
		$sql .= " AND c.entity IN (".getEntity('societe', 1).")";
		$sql .= " AND c.email != ''"; // Note that null != '' is false
		$sql .= " AND c.no_email = 0";
        
		// La requete doit retourner un champ "nb" pour etre comprise
		// par parent::getNbOfRecipients
		return parent::getNbOfRecipients($sql);
    }

    /**
     *  This is to add a form filter to provide variant of selector
     *	If used, the HTML select must be called "filter"
     *
     *  @return     string      A html select zone
     */
    function formFilter()
    {
	    global $langs;
		$langs->load("companies");
		$langs->load("commercial");
		$langs->load("event@event");

		require_once dol_buildpath('/event/class/event.class.php');
		require_once dol_buildpath('/event/class/day.class.php');
		
		$eventday=new Day($this->db);

		$listodfays = $eventday->fetch_all();
		$s='';
		$s.='<select name="filter" class="flat">';
 
		$num = count($listodfays);
		if ($num) $s.='<option value="all">&nbsp;</option>';
	
		foreach ($eventday->line as $event)
		{
			$s.='<option value="'.$event->id.'">'.dol_print_date($event->date_event,'day').' '.$event->label.'</option>';
			
		}
		$s.='</select>';
		return $s;
    }


    /**
     *  Can include an URL link on each record provided by selector
     *	shown on target page.
     *
     *  @param	int		$id		ID
     *  @return string      	Url link
     */
    function url($id)
    {
        return '<a href="'.dol_buildpath('/event/registration/fiche.php',1).'?id='.$id.'">'.img_object('',"event_registration@event").'</a>';
    }

}

?>
