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
 *	\file       htdocs/business/models/mod_project_universal.php
 *	\ingroup    business
 *	\brief      File with class to manage the numbering module Universal for business references
 *	\version    $Id$
 */

dol_include_once("/business/core/models/modules_business.php");


/**
 * 	\class      mod_business_universal
 * 	\brief      Classe du modele de numerotation de reference de business Universal
 */
class mod_business_universal extends ModeleNumRefBusiness
{
	var $version='dolibarr';		// 'development', 'experimental', 'dolibarr'
	var $error = '';
	var $nom = 'Universal';


    /**
     *  \brief      Renvoi la description du modele de numerotation
     *  \return     string      Texte descripif
     */
	function info()
    {
    	global $conf,$langs;

		$langs->load("business@business");
		$langs->load("admin");

		$form = new Form($db);

		$texte = $langs->trans('GenericNumRefModelDesc')."<br>\n";
		$texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		$texte.= '<input type="hidden" name="action" value="updateMask">';
		$texte.= '<input type="hidden" name="maskconstbusiness" value="BUSINESS_UNIVERSAL_MASK">';
		$texte.= '<table class="nobordernopadding" width="100%">';

		$tooltip=$langs->trans("GenericMaskCodes",$langs->transnoentities("Business"));
		$tooltip.=$langs->trans("GenericMaskCodes2");
		$tooltip.=$langs->trans("GenericMaskCodes3");
		$tooltip.=$langs->trans("GenericMaskCodes4a",$langs->transnoentities("Business"),$langs->transnoentities("Business"));
		$tooltip.=$langs->trans("GenericMaskCodes5");

		// Parametrage du prefix
		$texte.= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte.= '<td align="right">'.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskbusiness" value="'.$conf->global->BUSINESS_UNIVERSAL_MASK.'">',$tooltip,1,1).'</td>';

		$texte.= '<td align="left" rowspan="2">&nbsp; <input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';

		$texte.= '</tr>';

		$texte.= '</table>';
		$texte.= '</form>';

		return $texte;
    }

    /**
     *  \brief      Renvoi un exemple de numerotation
     *  \return     string      Example
     */
    function getExample()
    {
    	global $conf,$langs,$mysoc;

    	$old_code_client=$mysoc->code_client;
    	$mysoc->code_client='CCCCCCCCCC';
    	$numExample = $this->getNextValue($mysoc,'');
		$mysoc->code_client=$old_code_client;

		if (! $numExample)
		{
			$numExample = $langs->trans('NotConfigured');
		}
		return $numExample;
    }

   /**
	*  \brief      Return next value
	*  \param      objsoc		Object third party
	*  \param      project		Object project
	*  \return     string		Value if OK, 0 if KO
	*/
    function getNextValue($objsoc=0,$business='')
    {
		global $db,$conf;

		require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions2.lib.php");

		// On defini critere recherche compteur
		$mask=$conf->global->BUSINESS_UNIVERSAL_MASK;

		if (! $mask)
		{
			$this->error='NotConfigured';
			return 0;
		}

		$date=empty($business->date_c)?dol_now():$business->date_c;
		$numFinal=get_next_value($db,$mask,'business','ref','',$objsoc->code_client,$date);

		return  $numFinal;
	}


    /**     \brief      Return next reference not yet used as a reference
     *      \param      objsoc      Object third party
     *      \param      project		Object project
     *      \return     string      Next not used reference
     */
    function business_get_num($objsoc=0,$business='')
    {
        return $this->getNextValue($objsoc,$business);
    }
}

?>