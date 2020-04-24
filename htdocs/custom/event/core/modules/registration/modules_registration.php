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
 *	\file       htdocs/core/modules/event/modules_registration.php
 *      \ingroup    event
 *      \brief      File that contain parent class for event numbering models
 *      \version    $Id$
 */
require_once(DOL_DOCUMENT_ROOT."/core/class/commondocgenerator.class.php");

/**
 * \class      ModelePDFRegistration
 * \brief      Parent class for registration models
 */
abstract class ModeleRegistration extends CommonDocGenerator
{
	var $error='';


	/**
	 *      \brief      Return list of active generation modules
	 *       \param      $db      Database handler
	 */
	static function liste_modeles($db)
	{
		global $conf;

		$type='registration';
		$liste=array();

		include_once(DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php');
		$liste=getListOfModels($db,$type,'');

		return $liste;
	}
}


/**
 *    Create object on disk
 *    @param       db         objet base de donnee
 *    @param       object        object registration
 *    @param       model         force le modele a utiliser ('' to not force)
 *    @param      outputlangs    objet lang a utiliser pour traduction
 *      @return     int          0 si KO, 1 si OK
 */
function registration_pdf_create($db, $object, $model,$outputlangs)
{
	global $conf,$langs;
	$langs->load("event@event");

	$dir = dol_buildpath('/event/core/modules/event/pdf/');

	// Positionne modele sur le nom du modele de projet a utiliser
	if (! dol_strlen($model))
	{
		if (! empty($conf->global->EVENT_REGISTRATION_ADDON_PDF))
		{
			$model = $conf->global->EVENT_REGISTRATION_ADDON_PDF;
		}
		else
		{
			$model='registration';
		}
	}

	// Charge le modele
	$file = "pdf_".$model.".modules.php";

	if (file_exists($dir.$file))
	{
		$classname = "pdf_".$model;
		require_once($dir.$file);

		$obj = new $classname($db);

		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		$sav_charset_output=$outputlangs->charset_output;
		if ($obj->write_file($object,$outputlangs) > 0)
		{
			// on supprime l'image correspondant au preview
			registration_delete_preview($db, $object->id);

			$outputlangs->charset_output=$sav_charset_output;
			return 1;
		}
		else
		{
			$outputlangs->charset_output=$sav_charset_output;
			dol_syslog("Erreur dans registration_pdf_create");
			dol_print_error($db,$obj->error);
			return 0;
		}
	}
	else
	{
		print $langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists",$dir.$file);
		return 0;
	}
}


/**
 * Enter description here...
 *
 * @param   $db
 * @param   $objectid
 * @return  int
 */
function registration_delete_preview($db, $objectid)
{
	global $langs,$conf;
	require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");

	$registration = new Registration($db);
	$registration->fetch($objectid);
	$client = new Societe($db);
	$client->fetch($registration->socid);

	if ($conf->event->dir_output.'/registration')
	{
		$registrationRef = dol_sanitizeFileName($registration->id);
		$dir = $conf->dir_output->dir_output . "/" . $registrationRef ;
		$file = $dir . "/" . $registrationRef . ".pdf.png";

		if ( file_exists( $file ) && is_writable( $file ) )
		{
			if ( ! dol_delete_file($file) )
			{
				$this->error=$langs->trans("ErrorFailedToOpenFile",$file);
				return 0;
			}
		}
	}

	return 1;
}



/**
 *  \class      ModeleNumRefRegistration
 *  \brief      Classe mere des modeles de numerotation des references de projets
 */
class ModeleNumRefRegistration
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
