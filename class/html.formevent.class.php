<?PHP
/* Copyright (C) 2005-2011 Laurent Destailleur <eldy@users.sourceforge.net>
* Copyright (C) 2012	-2015  JF FERRY <jfefe@aternatik.fr>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *       \file       event/lib/html.formregistration.class.php
 *       \ingroup    core
 *       \brief      Fichier de la classe permettant la generation des formulaires html du module event
 */
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT ."/core/class/html.formcompany.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/extrafields.class.php");
require_once("event.class.php");
require_once("day.class.php");
require_once("eventlevel.class.php");



/** 	\class FormEvent
* 		\brief Classe permettant la generation du formulaire d'une nouvelle inscription
* 		\remarks Utilisation: $formregister = new FormRegistration($db)
* 		\remarks $formregister->proprietes=1 ou chaine ou tableau de valeurs
* 		\remarks $formregister->show_form() affiche le formulaire
*/
class FormEvent
{
    var $db;

    var $fk_event;
    var $fk_eventday;
    var $fk_level;
    var $fk_soc;

	var $withcancel;

    var $substit=array();
    var $param=array();

    var $error;


    /**
    * Constructor
    *
    * @param DoliDB $DB Database handler
    */
    function __construct($db)
    {
        $this->db = $db;

        $this->action = 'add';
        $this->witheventday=0;
        $this->withusercreate=0;
        $this->withfromsocid=0;
        $this->withlevel=1;

		$this->withusercreate=1;


		$this->ref = 0;



        return 1;
    }


    /**
     * Affiche un champs select contenant la liste des évènements disponibles.
     *
     * @param   int 	$selectid		Valeur à preselectionner
     * @param   string	$htmlname		Name of select field
     * @param   string	$fullform		Display full form
     * @param   string	$sort			Name of Value to show/edit (not used in this function)
     * @param	 int	$showempty		Add an empty field
     * @param	 int	$forcecombo		Force to use combo box
     * @param	 array	$event			Event options
     * @return	string					HTML select field
     */
    function select_event($selectid='', $htmlname='event', $fullform="", $sort='intitule', $showempty=0, $forcecombo=0, $event=array())
    {
    	global $conf,$user,$langs;

    	$out='';

    	if ($sort == 'code') $order = 'e.ref';
    	else $order = 'e.date_start';

    	if($fullform)
    	{
    		$out.= '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form'.$htmlname.'">';
    		$out.= '<input type="hidden" name="action" value="set'.$htmlname.'">';
    		$out.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    	}

    	$sql = "SELECT e.rowid, e.label, e.date_start";
    	$sql.= " FROM ".MAIN_DB_PREFIX."event as e";
    	$sql.= " ORDER BY ".$order;

    	dol_syslog(get_class($this)."::select_event sql=".$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		print '<label for="'.$htmlname.'">'.$langs->trans('LabelSelectAnEvent').'</label>';

    		if ($conf->use_javascript_ajax && ! $forcecombo)
    		{
    			$out.= ajax_combobox($htmlname, $event);
    		}

    		$out.= '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'">';
    		if ($showempty) $out.= '<option value="-1"></option>';
    		$num = $this->db->num_rows($resql);
    		$i = 0;
    		if ($num)
    		{
    			while ($i < $num)
    			{
    				$obj = $this->db->fetch_object($resql);
    				$label=$obj->label;

    				if ($selectid > 0 && $selectid == $obj->rowid)
    				{
    					$out.= '<option value="'.$obj->rowid.'" selected="selected">'.dol_print_date($this->db->jdate($obj->date_start),'day').' - '.$label.'</option>';
    				}
    				else
    				{
    					$out.= '<option value="'.$obj->rowid.'">'.dol_print_date($this->db->jdate($obj->date_start),'day').' - '.$label.'</option>';
    				}
    				$i++;
    			}
    		}
    		$out.= '</select>';

    		if($fullform)
    		{
    			$out.= '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
    			$out.= '</form>';
    		}
    	}
    	else
    	{
    		dol_print_error($this->db);
    	}
    	$this->db->free($resql);
    	return $out;
    }

    /*
     Affiche une liste de sélection des journée d'un évènement
    */
    function select_eventdays($selected='',$htmlname='fk_levelday',$fullform="",$showempty=0, $moreclass='', $forcecombo=0, $event=array()) {
    	global $langs;

    	$sql_event = "SELECT ed.rowid, ed.ref, ed.date_event, e.label  FROM ".MAIN_DB_PREFIX."event_day as ed LEFT JOIN ".MAIN_DB_PREFIX."event as e ON e.rowid=ed.fk_event ";
    	$resql=$this->db->query($sql_event);

    	if ($resql) {
    		$num = $this->db->num_rows($resql);
    		if ($num) {

    			$i = 0;
    			$out='';
    			if($fullform)
    			{
	    			print_fiche_titre($langs->trans('ChooseADay'));
    				$out.= '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form'.$htmlname.'">';
    				$out.= '<input type="hidden" name="id" value="'.$dayid.'">';
    				if ($this->fk_registration) $out.= '<input type="hidden" name="fk_registration" value="'.$this->fk_registration.'">';
    				$out.= '<input type="hidden" name="action" value="set'.$htmlname.'">';
    				$out.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	    			print '<label for="'.$htmlname.'">'.$langs->trans('LabelSelectAnEventDay').'</label>';
    			}

    			if ($conf->use_javascript_ajax && ! $forcecombo)
    			{
    				$out.= ajax_combobox($htmlname, $event);
    			}

    			if ($htmlname != 'none' || $options_only) $out.= '<select class="flat'.($moreclass?' '.$moreclass:'').'" id="'.$htmlname.'" name="'.$htmlname.'">';
    			if ($showempty) $out.= '<option value="-1"></option>';
    			while ($i < $num)
    			{
    				$obj = $this->db->fetch_object($resql);

    				$out.= '<option value="'.$obj->rowid.'" '.($obj->rowid==$selected?'selected="selected"':'').'>'.dol_print_date($this->db->jdate($obj->date_event),'day').' - '.$obj->ref.' - '.$obj->label.'</option>';

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
    	else
    	{
    		$this->error=$this->db->error;
    		$error++;
    	}
    	// End
    	if (! $error)
    	{
    		return $out;
    	}
    	else
    	{

    		return -1;
    	}
    }


    function show_select_event($full_form=0)
    {
    	if($full_form)
    	{
    		print "\n<!-- Begin select LEVELFORDAY -->\n";


    		print "<form method=\"POST\" name=\"select_event\" enctype=\"multipart/form-data\" action=\"".$this->param["returnurl"]."\">\n";
    		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    		print '<input type="hidden" name="action" value="'.$this->action.'">';
    		foreach ($this->param as $key=>$value)
    		{
    			print '<input type="hidden" name="$key" value="'.$value.'"'. ($value == $this->fk_event?'selected="selected"':'').' />';
    		}
    	}

    }

    /**
	 * Affiche un champs select contenant la liste des 1/4 d"heures de 7:00 à 21h00.
	 *
	 * @param string $selectval valeur a selectionner par defaut
	 * @param string $htmlname nom du control HTML
	 * @return string The HTML control
	 */
	function select_time($selectval = '', $htmlname = 'period') {

		$time = 7;
		$heuref = 23;
		$min = 0;
		$options = '<option value=""></option>' . "\n";
		while ( $time < $heuref ) {
			if ($min == 60) {
				$min = 0;
				$time ++;
			}
			$ftime = sprintf ( "%02d", $time ) . ':' . sprintf ( "%02d", $min );
			if ($selectval == $ftime)
				$selected = ' selected="selected"';
			else
				$selected = '';
			$options .= '<option value="' . $ftime . '"' . $selected . '>' . $ftime . '</option>' . "\n";
			$min += 15;
		}
		return '<select class="flat" name="' . $htmlname . '">' . "\n" . $options . "\n" . '</select>' . "\n";
	}


}

?>
