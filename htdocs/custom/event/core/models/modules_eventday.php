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
 *		\file       htdocs/core/modules/event/modules_event.php
 *      \ingroup    event
 *      \brief      File that contain parent class for event numbering models
 *      \version    $Id$
 */


/**
 *  \class      ModeleNumRefEventday
 *  \brief      Classe mere des modeles de numerotation des references de projets
 */
class ModeleNumRefEventday
{
	var $error='';

	/**
	 *  \brief     	Return if a module can be used or not
	 *  \return		boolean     true if module can be used
	 */
	function isEnabled()
	{
		return true;
	}

	/**
	 *  \brief      Renvoi la description par defaut du modele de numerotation
	 *  \return     string      Texte descripif
	 */
	function info()
	{
		global $langs;
		$langs->load("event@event");
		return $langs->trans("NoDescription");
	}

	/**
	 *  \brief      Renvoi un exemple de numerotation
	 *  \return     string      Example
	 */
	function getExample()
	{
		global $langs;
		$langs->load("event@event");
		return $langs->trans("NoExample");
	}

	/**
	 *  \brief      Test si les numeros deja en vigueur dans la base ne provoquent pas de
	 *                  de conflits qui empechera cette numerotation de fonctionner.
	 *  \return     boolean     false si conflit, true si ok
	 */
	function canBeActivated()
	{
		return true;
	}

	/**
	 *  \brief      Renvoi prochaine valeur attribuee
	 *  \return     string      Valeur
	 */
	function getNextValue()
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 *  \brief      Renvoi version du module numerotation
	 *  \return     string      Valeur
	 */
	function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') return $langs->trans("VersionDevelopment");
		if ($this->version == 'experimental') return $langs->trans("VersionExperimental");
		if ($this->version == 'dolibarr') return DOL_VERSION;
		return $langs->trans("NotAvailable");
	}
}

?>
