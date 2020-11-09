<?php
/* Copyright (C) 2001-2002  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2001-2002  Jean-Louis Bergamo      <jlb@j1b.org>
 * Copyright (C) 2006-2013  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2012       Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2012       J. Fernando Lagrange    <fernando@demo-tic.org>
 * Copyright (C) 2018       Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2018       Alexandre Spangaro      <aspangaro@open-dsi.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 *	\file       htdocs/public/members/new.php
 *	\ingroup    member
 *	\brief      Example of form to add a new member
 *
 *  Note that you can add following constant to change behaviour of page
 *  MEMBER_NEWFORM_AMOUNT               Default amount for auto-subscribe form
 *  MEMBER_NEWFORM_EDITAMOUNT           0 or 1 = Amount can be edited
 *  MEMBER_NEWFORM_PAYONLINE            Suggest payment with paypal, paybox or stripe
 *  MEMBER_NEWFORM_DOLIBARRTURNOVER     Show field turnover (specific for dolibarr foundation)
 *  MEMBER_URL_REDIRECT_SUBSCRIPTION    Url to redirect once subscribe submitted
 *  MEMBER_NEWFORM_FORCETYPE            Force type of member
 *  MEMBER_NEWFORM_FORCEMORPHY          Force nature of member (mor/phy)
 *  MEMBER_NEWFORM_FORCECOUNTRYCODE     Force country
 */

if (! defined('NOLOGIN'))		define("NOLOGIN", 1);		// This means this output page does not require to be logged.
if (! defined('NOCSRFCHECK'))	define("NOCSRFCHECK", 1);	// We accept to go on this page from external web site.
if (! defined('NOIPCHECK'))		define('NOIPCHECK', '1');	// Do not check IP defined into conf $dolibarr_main_restrict_ip

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
// TODO This should be useless. Because entity must be retrieve from object ref and not from url.
$entity=(! empty($_GET['entity']) ? (int) $_GET['entity'] : (! empty($_POST['entity']) ? (int) $_POST['entity'] : 1));
if (is_numeric($entity)) define("DOLENTITY", $entity);

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';
if (! empty($conf->adherent->enabled)) require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';


$langs->loadLangs(array("companies","commercial","bills","banks","users"));

// Security check
if (empty($conf->global->ITIER_ENABLE_PUBLIC)) accessforbidden('', 0, 0, 1); 

if (! empty($conf->categorie->enabled)) $langs->load("categories");
if (! empty($conf->incoterm->enabled)) $langs->load("incoterm");
if (! empty($conf->notification->enabled)) $langs->load("mails");

$errmsg=''; $mesg=''; $error=0; $errors=array(); $num=0;

$action		= (GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view');
$cancel		= GETPOST('cancel', 'alpha');
$backtopage	= GETPOST('backtopage', 'alpha');
$confirm	= GETPOST('confirm', 'alpha');

$socid		= GETPOST('socid', 'int')?GETPOST('socid', 'int'):GETPOST('id', 'int');
if ($user->societe_id) $socid=$user->societe_id;
if (empty($socid) && $action == 'view') $action='create';

$object = new Societe($db);
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('thirdpartycard','globalcard'));

if ($socid > 0) $object->fetch($socid);

// Load object modCodeClient and modCodeFournisseur
$module = (!empty($conf->global->SOCIETE_CODECLIENT_ADDON) ? $conf->global->SOCIETE_CODECLIENT_ADDON : 'mod_codeclient_leopard');
if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
{
	$module = substr($module, 0, dol_strlen($module) - 4);
}
$dirsociete = array_merge(array('/core/modules/societe/'), $conf->modules_parts['societe']);
foreach ($dirsociete as $dirroot)
{
	$res = dol_include_once($dirroot.$module.'.php');
	if ($res) break;
}
$modCodeFournisseur = $modCodeClient = new $module($db);
// ---------------------------

// Load translation files
$langs->loadLangs(array("main","members","companies","install","other"));

// Security check
// if (empty($conf->adherent->enabled)) accessforbidden('', 0, 0, 1);

if (empty($conf->global->MEMBER_ENABLE_PUBLIC))
{
    print $langs->trans("Auto subscription form for public visitors has not been enabled");
    exit;
}

$user->loadDefaultValues();


/**
 * Show header for new member
 *
 * @param 	string		$title				Title
 * @param 	string		$head				Head array
 * @param 	int    		$disablejs			More content into html header
 * @param 	int    		$disablehead		More content into html header
 * @param 	array  		$arrayofjs			Array of complementary js files
 * @param 	array  		$arrayofcss			Array of complementary css files
 * @return	void
 */
function llxHeaderVierge($title, $head = "", $disablejs = 0, $disablehead = 0, $arrayofjs = '', $arrayofcss = '')
{
    global $user, $conf, $langs, $mysoc;

    top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss); // Show html headers
    print '<body id="mainbody" class="publicnewmemberform" style="margin-top: 10px;">';

    // Print logo
    $urllogo=DOL_URL_ROOT.'/theme/login_logo.png';

    if (! empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
    {
        $urllogo=DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=mycompany&amp;file='.urlencode('logos/thumbs/'.$mysoc->logo_small);
    }
    elseif (! empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
    {
        $urllogo=DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=mycompany&amp;file='.urlencode('logos/'.$mysoc->logo);
        $width=128;
    }
    elseif (is_readable(DOL_DOCUMENT_ROOT.'/theme/dolibarr_logo.png'))
    {
        $urllogo=DOL_URL_ROOT.'/theme/dolibarr_logo.png';
    }
    print '<div class="center">';
    print '<img alt="Logo" id="logosubscribe" title="" src="'.$urllogo.'" />';
    print '</div><br>';

    print '<div class="divmainbodylarge">';
}

/**
 * Show footer for new member
 *
 * @return	void
 */
function llxFooterVierge()
{
    print '</div>';

    printCommonFooter('public');

    print "</body>\n";
    print "</html>\n";
}


/*
 * Actions
 */

// Action called when page is submitted
// Add new or update third party
if ((! GETPOST('getcustomercode') && ! GETPOST('getsuppliercode'))
&& ($action == 'add' || $action == 'update'))
{
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

	if (! GETPOST('name'))
	{
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ThirdPartyName")), null, 'errors');
		$error++;
	}
	if (GETPOST('client') < 0)
	{
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ProspectCustomer")), null, 'errors');
		$error++;
	}
	if (GETPOST('fournisseur') < 0)
	{
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Supplier")), null, 'errors');
		$error++;
	}

	if (! $error)
	{
		if ($action == 'update')
		{
			$ret=$object->fetch($socid);
			$object->oldcopy = clone $object;
		}
		else $object->canvas=$canvas;

		if (GETPOST("private", 'int') == 1)	// Ask to create a contact
		{
			$object->particulier		= GETPOST("private");

			$object->name				= dolGetFirstLastname(GETPOST('firstname', 'alpha'), GETPOST('name', 'alpha'));
			$object->civility_id		= GETPOST('civility_id');	// Note: civility id is a code, not an int
			// Add non official properties
			$object->name_bis			= GETPOST('name', 'alpha');
			$object->firstname			= GETPOST('firstname', 'alpha');
		}
		else
		{
			$object->name				= GETPOST('name', 'alpha');
		}
		$object->entity					= (GETPOSTISSET('entity')?GETPOST('entity', 'int'):$conf->entity);
		$object->name_alias				= GETPOST('name_alias');
		$object->address				= GETPOST('address');
		$object->zip					= GETPOST('zipcode', 'alpha');
		$object->town					= GETPOST('town', 'alpha');
		$object->country_id				= GETPOST('country_id', 'int');
		$object->state_id				= GETPOST('state_id', 'int');
		$object->skype					= GETPOST('skype', 'alpha');
		$object->twitter				= GETPOST('twitter', 'alpha');
		$object->facebook				= GETPOST('facebook', 'alpha');
		$object->linkedin				= GETPOST('linkedin', 'alpha');
		$object->phone					= GETPOST('phone', 'alpha');
		$object->fax					= GETPOST('fax', 'alpha');
		$object->email					= trim(GETPOST('email', 'custom', 0, FILTER_SANITIZE_EMAIL));
		$object->url					= trim(GETPOST('url', 'custom', 0, FILTER_SANITIZE_URL));
		$object->idprof1				= trim(GETPOST('idprof1', 'alpha'));
		$object->idprof2				= trim(GETPOST('idprof2', 'alpha'));
		$object->idprof3				= trim(GETPOST('idprof3', 'alpha'));
		$object->idprof4				= trim(GETPOST('idprof4', 'alpha'));
		$object->idprof5				= trim(GETPOST('idprof5', 'alpha'));
		$object->idprof6				= trim(GETPOST('idprof6', 'alpha'));
		$object->prefix_comm			= GETPOST('prefix_comm', 'alpha');
		$object->code_client			= GETPOSTISSET('customer_code')?GETPOST('customer_code', 'alpha'):GETPOST('code_client', 'alpha');
		$object->code_fournisseur		= GETPOSTISSET('supplier_code')?GETPOST('supplier_code', 'alpha'):GETPOST('code_fournisseur', 'alpha');
		$object->capital				= GETPOST('capital', 'alpha');
		$object->barcode				= GETPOST('barcode', 'alpha');

		$object->tva_intra				= GETPOST('tva_intra', 'alpha');
		$object->tva_assuj				= GETPOST('assujtva_value', 'alpha');
		$object->status					= GETPOST('status', 'alpha');

		// Local Taxes
		$object->localtax1_assuj		= GETPOST('localtax1assuj_value', 'alpha');
		$object->localtax2_assuj		= GETPOST('localtax2assuj_value', 'alpha');

		$object->localtax1_value		= GETPOST('lt1', 'alpha');
		$object->localtax2_value		= GETPOST('lt2', 'alpha');

		$object->forme_juridique_code	= GETPOST('forme_juridique_code', 'int');
		$object->effectif_id			= GETPOST('effectif_id', 'int');
		$object->typent_id				= GETPOST('typent_id', 'int');

		$object->typent_code			= dol_getIdFromCode($db, $object->typent_id, 'c_typent', 'id', 'code');	// Force typent_code too so check in verify() will be done on new type

		$object->client					= GETPOST('client', 'int');
		$object->fournisseur			= GETPOST('fournisseur', 'int');

		$object->commercial_id			= GETPOST('commercial_id', 'int');
		$object->default_lang			= GETPOST('default_lang');

		// Webservices url/key
		$object->webservices_url		= GETPOST('webservices_url', 'custom', 0, FILTER_SANITIZE_URL);
		$object->webservices_key		= GETPOST('webservices_key', 'san_alpha');

		// Commentaires
		$object->note_private			= GETPOST('note_private');

		// Incoterms
		if (!empty($conf->incoterm->enabled))
		{
			$object->fk_incoterms		= GETPOST('incoterm_id', 'int');
			$object->location_incoterms	= GETPOST('location_incoterms', 'alpha');
		}

		// Multicurrency
		if (!empty($conf->multicurrency->enabled))
		{
			$object->multicurrency_code = GETPOST('multicurrency_code', 'alpha');
		}

		// Fill array 'array_options' with data from add form
		$ret = $extrafields->setOptionalsFromPost($extralabels, $object);
		if ($ret < 0)
		{
			 $error++;
		}

		if (GETPOST('deletephoto')) $object->logo = '';
		elseif (! empty($_FILES['photo']['name'])) $object->logo = dol_sanitizeFileName($_FILES['photo']['name']);

		// Check parameters
		if (! GETPOST('cancel', 'alpha'))
		{
			if (! empty($object->email) && ! isValidEMail($object->email))
			{
				$langs->load("errors");
				$error++;
				setEventMessages('', $langs->trans("ErrorBadEMail", $object->email), 'errors');
			}
			if (! empty($object->url) && ! isValidUrl($object->url))
			{
				$langs->load("errors");
				setEventMessages('', $langs->trans("ErrorBadUrl", $object->url), 'errors');
			}
			if (! empty($object->webservices_url)) {
				//Check if has transport, without any the soap client will give error
				if (strpos($object->webservices_url, "http") === false)
				{
					$object->webservices_url = "http://".$object->webservices_url;
				}
				if (! isValidUrl($object->webservices_url)) {
					$langs->load("errors");
					$error++; $errors[] = $langs->trans("ErrorBadUrl", $object->webservices_url);
				}
			}

			// We set country_id, country_code and country for the selected country
			$object->country_id=GETPOST('country_id')!=''?GETPOST('country_id'):$mysoc->country_id;
			if ($object->country_id)
			{
				$tmparray=getCountry($object->country_id, 'all');
				$object->country_code=$tmparray['code'];
				$object->country=$tmparray['label'];
			}
		}
	}

	if (! $error)
	{
		if ($action == 'add')
		{
			$error = 0;

			$db->begin();

			if (empty($object->client))      $object->code_client='';
			if (empty($object->fournisseur)) $object->code_fournisseur='';

			$result = $object->create($user);

			if ($result >= 0)
			{
				if ($object->particulier)
				{
					dol_syslog("We ask to create a contact/address too", LOG_DEBUG);
					$result=$object->create_individual($user);
					if ($result < 0)
					{
						setEventMessages($object->error, $object->errors, 'errors');
						$error++;
					}
				}

				// Links with users
				$salesreps = GETPOST('commercial', 'array');
				$result = $object->setSalesRep($salesreps);
				if ($result < 0)
				{
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}

				// Customer categories association
				$custcats = GETPOST('custcats', 'array');
				$result = $object->setCategories($custcats, 'customer');
				if ($result < 0)
				{
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}

				// Supplier categories association
				$suppcats = GETPOST('suppcats', 'array');
				$result = $object->setCategories($suppcats, 'supplier');
				if ($result < 0)
				{
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}

				// Logo/Photo save
				$dir     = $conf->societe->multidir_output[$conf->entity]."/".$object->id."/logos/";
				$file_OK = is_uploaded_file($_FILES['photo']['tmp_name']);
				if ($file_OK)
				{
					if (image_format_supported($_FILES['photo']['name']))
					{
						dol_mkdir($dir);

						if (@is_dir($dir))
						{
							$newfile=$dir.'/'.dol_sanitizeFileName($_FILES['photo']['name']);
							$result = dol_move_uploaded_file($_FILES['photo']['tmp_name'], $newfile, 1);

							if (! $result > 0)
							{
								$errors[] = "ErrorFailedToSaveFile";
							}
							else
							{
								// Create thumbs
								$object->addThumbs($newfile);
							}
						}
					}
				}
				else
				{
					switch($_FILES['photo']['error'])
					{
						case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
						case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
						  $errors[] = "ErrorFileSizeTooLarge";
						  break;
						case 3: //uploaded file was only partially uploaded
						  $errors[] = "ErrorFilePartiallyUploaded";
						  break;
					}
				}
				// Gestion du logo de la société
			}
			else
			{
				if ($db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') // TODO Sometime errors on duplicate on profid and not on code, so we must manage this case
				{
					$duplicate_code_error = true;
					$object->code_fournisseur = null;
					$object->code_client = null;
				}

				setEventMessages($object->error, $object->errors, 'errors');
				$error++;
			}

			if ($result >= 0 && ! $error)
			{
				$db->commit();

				if (! empty($backtopage))
				{
					if (preg_match('/\?/', $backtopage)) $backtopage.='&socid='.$object->id;
					header("Location: ".$backtopage);
					exit;
				}
				else
				{
					$url=$_SERVER["PHP_SELF"]."?socid=".$object->id;
					if (($object->client == 1 || $object->client == 3) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) $url=DOL_URL_ROOT."/comm/card.php?socid=".$object->id;
					elseif ($object->fournisseur == 1) $url=DOL_URL_ROOT."/fourn/card.php?socid=".$object->id;

					header("Location: ".$url);
					exit;
				}
			}
			else
			{
				$db->rollback();
				$action='create';
			}
		}

		if ($action == 'update')
		{
			$error = 0;

			if (GETPOST('cancel', 'alpha'))
			{
				if (! empty($backtopage))
				{
					header("Location: ".$backtopage);
					exit;
				}
				else
				{
					header("Location: ".$_SERVER["PHP_SELF"]."?socid=".$socid);
					exit;
				}
			}

			// To not set code if third party is not concerned. But if it had values, we keep them.
			if (empty($object->client) && empty($object->oldcopy->code_client))          $object->code_client='';
			if (empty($object->fournisseur)&& empty($object->oldcopy->code_fournisseur)) $object->code_fournisseur='';
			//var_dump($object);exit;

			$result = $object->update($socid, $user, 1, $object->oldcopy->codeclient_modifiable(), $object->oldcopy->codefournisseur_modifiable(), 'update', 0);
			if ($result <=  0)
			{
				setEventMessages($object->error, $object->errors, 'errors');
				$error++;
			}

			// Links with users
			$salesreps = GETPOST('commercial', 'array');
			$result = $object->setSalesRep($salesreps);
			if ($result < 0)
			{
				$error++;
				setEventMessages($object->error, $object->errors, 'errors');
			}

			// Prevent thirdparty's emptying if a user hasn't rights $user->rights->categorie->lire (in such a case, post of 'custcats' is not defined)
			if (! $error && !empty($user->rights->categorie->lire))
			{
				// Customer categories association
				$categories = GETPOST('custcats', 'array');
				$result = $object->setCategories($categories, 'customer');
				if ($result < 0)
				{
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}

				// Supplier categories association
				$categories = GETPOST('suppcats', 'array');
				$result = $object->setCategories($categories, 'supplier');
				if ($result < 0)
				{
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}
			}

			// Logo/Photo save
			$dir     = $conf->societe->multidir_output[$object->entity]."/".$object->id."/logos";
			$file_OK = is_uploaded_file($_FILES['photo']['tmp_name']);
			if (GETPOST('deletephoto') && $object->logo)
			{
				$fileimg=$dir.'/'.$object->logo;
				$dirthumbs=$dir.'/thumbs';
				dol_delete_file($fileimg);
				dol_delete_dir_recursive($dirthumbs);
			}
			if ($file_OK)
			{
				if (image_format_supported($_FILES['photo']['name']) > 0)
				{
					dol_mkdir($dir);

					if (@is_dir($dir))
					{
						$newfile=$dir.'/'.dol_sanitizeFileName($_FILES['photo']['name']);
						$result = dol_move_uploaded_file($_FILES['photo']['tmp_name'], $newfile, 1);

						if (! $result > 0)
						{
							$errors[] = "ErrorFailedToSaveFile";
						}
						else
						{
							// Create thumbs
							$object->addThumbs($newfile);

							// Index file in database
							if (! empty($conf->global->THIRDPARTY_LOGO_ALLOW_EXTERNAL_DOWNLOAD))
							{
								require_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
								// the dir dirname($newfile) is directory of logo, so we should have only one file at once into index, so we delete indexes for the dir
								deleteFilesIntoDatabaseIndex(dirname($newfile), '', '');
								// now we index the uploaded logo file
								addFileIntoDatabaseIndex(dirname($newfile), basename($newfile), '', 'uploaded', 1);
							}
						}
					}
				}
				else
				{
					$errors[] = "ErrorBadImageFormat";
				}
			}
			else
			{
				switch($_FILES['photo']['error'])
				{
					case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
					case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
					  $errors[] = "ErrorFileSizeTooLarge";
					  break;
					case 3: //uploaded file was only partially uploaded
					  $errors[] = "ErrorFilePartiallyUploaded";
					  break;
				}
			}
			// Gestion du logo de la société


			// Update linked member
			if (! $error && $object->fk_soc > 0)
			{

				$sql = "UPDATE ".MAIN_DB_PREFIX."adherent";
				$sql.= " SET fk_soc = NULL WHERE fk_soc = " . $id;
				if (! $object->db->query($sql))
				{
					$error++;
					$object->error .= $object->db->lasterror();
					setEventMessages($object->error, $object->errors, 'errors');
				}
			}

			if (! $error && ! count($errors))
			{
				if (! empty($backtopage))
				{
					header("Location: ".$backtopage);
					exit;
				}
				else
				{
					header("Location: ".$_SERVER["PHP_SELF"]."?socid=".$socid);
					exit;
				}
			}
			else
			{
				$object->id = $socid;
				$action= "edit";
			}
		}
	}
	else
	{
		$action = ($action=='add'?'create':'edit');
	}
}

// Action called after a submitted was send and member created successfully
// If MEMBER_URL_REDIRECT_SUBSCRIPTION is set to url we never go here because a redirect was done to this url.
// backtopage parameter with an url was set on member submit page, we never go here because a redirect was done to this url.
if ($action == 'added')
{
    llxHeaderVierge($langs->trans("NewMemberForm"));

    // Si on a pas ete redirige
    print '<br>';
    print '<div class="center">';
    print $langs->trans("NewMemberbyWeb");
    print '</div>';

    llxFooterVierge();
    exit;
}



/*
 * View
 */

$form = new Form($db);
$formcompany = new FormCompany($db);
$adht = new AdherentType($db);
$extrafields->fetch_name_optionals_label('societe');    // fetch optionals attributes and labels


llxHeaderVierge("Nouveau tier");


print load_fiche_titre($langs->trans("Nouveau tier"), '', '', 0, 0, 'center');


print '<div align="center">';
print '<div id="divsubscribe">';

print '<div class="center subscriptionformhelptext justify">';
/* if (! empty($conf->global->MEMBER_NEWFORM_TEXT)) print $langs->trans($conf->global->MEMBER_NEWFORM_TEXT)."<br>\n";
else print "Ce formulaire permet de vous inscrire comme nouveau tier de l'association. Pour un renouvellement (si vous êtes déjà tier), contactez plutôt l'association par email .<br>\n"; */
print '</div>';

dol_htmloutput_errors($errmsg);

// Print form 
print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'" method="post" name="formsoc" autocomplete="off">'."\n";
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" / >';
print '<input type="hidden" name="entity" value="'.$entity.'" />';
print '<input type="hidden" name="action" value="add" />';
print '<input type="hidden" name="private" value="1" />';
print '<input type="hidden" name="status" value="1">';

print '<br>';

print '<br><span class="opacitymedium">'.$langs->trans("FieldsWithAreMandatory", '*').'</span><br>';
//print $langs->trans("FieldsWithIsForPublic",'**').'<br>';

dol_fiche_head('');

// Code client
if ((!$object->code_client || $object->code_client == -1) && $modCodeClient->code_auto)
{
	$tmpcode=$object->code_client;
	if (empty($tmpcode) && ! empty($object->oldcopy->code_client)) $tmpcode=$object->oldcopy->code_client; // When there is an error to update a thirdparty, the number for supplier and customer code is kept to old value.
	if (empty($tmpcode) && ! empty($modCodeClient->code_auto)) $tmpcode=$modCodeClient->getNextValue($object, 0);
	print '<input type="hidden" name="customer_code" id="customer_code" size="16" value="'.dol_escape_htmltag($tmpcode).'" maxlength="15">';
}
elseif ($object->codeclient_modifiable())
{
	print '<input type="hidden" name="customer_code" id="customer_code" size="16" value="'.dol_escape_htmltag($object->code_client).'" maxlength="15">';
}
else
{
	print $object->code_client;
	print '<input type="hidden" name="customer_code" value="'.dol_escape_htmltag($object->code_client).'">';
}
// $s=$modCodeClient->getToolTip($langs, $object, 0);
// print $form->textwithpicto('', $s, 1);

// Supplier
if ((!$object->code_fournisseur || $object->code_fournisseur == -1) && $modCodeFournisseur->code_auto)
{
	$tmpcode=$object->code_fournisseur;
	if (empty($tmpcode) && ! empty($object->oldcopy->code_fournisseur)) $tmpcode=$object->oldcopy->code_fournisseur; // When there is an error to update a thirdparty, the number for supplier and customer code is kept to old value.
	if (empty($tmpcode) && ! empty($modCodeFournisseur->code_auto)) $tmpcode=$modCodeFournisseur->getNextValue($object, 1);
	print '<input type="hidden" name="supplier_code" id="supplier_code" size="16" value="'.dol_escape_htmltag($tmpcode).'" maxlength="15">';
}
elseif ($object->codefournisseur_modifiable())
{
	print '<input type="hidden" name="supplier_code" id="supplier_code" size="16" value="'.$object->code_fournisseur.'" maxlength="15">';
}
else
{
	print $object->code_fournisseur;
	print '<input type="hidden" name="supplier_code" value="'.$object->code_fournisseur.'">';
}
// $s=$modCodeFournisseur->getToolTip($langs, $object, 1);
// print $form->textwithpicto('', $s, 1);


print '<script type="text/javascript">
jQuery(document).ready(function () {
    jQuery(document).ready(function () {
        function initmorphy()
        {
                if (jQuery("#morphy").val()==\'phy\') {
                    jQuery("#trcompany").hide();
                }
                if (jQuery("#morphy").val()==\'mor\') {
                    jQuery("#trcompany").show();
                }
        };
        initmorphy();
        jQuery("#morphy").click(function() {
            initmorphy();
        });
        jQuery("#selectcountry_id").change(function() {
           document.newmember.action.value="create";
           document.newmember.submit();
        });
    });
});
</script>';


print '<table class="border" summary="form to subscribe" id="tablesubscribe">'."\n";

// Type
$defaulttype='';
$isempty=1;
print '<tr><td class="titlefield">'.$langs->trans("Type").' </td><td>';
print $form->selectarray("typent_id", $formcompany->typent_array(0), GETPOST('typent_id')?GETPOST('typent_id'):$defaulttype);
print '</td></tr>'."\n";
// Name
print '<tr><td>'.$langs->trans("Nom").' <FONT COLOR="red">*</FONT></td><td><input type="text" class="minwidth150" maxlength="128" name="name" id="name" value="'.dol_escape_htmltag(GETPOST('name')).'" required></td></tr>'."\n";

// prenom
print '<tr><td>'.$langs->trans("Prénom").' </td><td><input type="text" class="minwidth150" maxlength="128" name="firstname" id="firstname" value="'.dol_escape_htmltag(GETPOST('firstname')).'"></td></tr>'."\n";

// Phone / Fax
print '<tr><td>'.$langs->trans("Phone").'</td>';
print '<td><input type="text" name="phone" id="phone" maxlength="15" class="minwidth150" value="'.dol_escape_htmltag(GETPOST('phone')).'"></td></tr>';
/* print '<tr><td>'.$langs->trans("Phone").'</td>';
print '<td><input type="text" name="phone_mobile" id="phone_mobile" maxlength="15" class="minwidth150" value="'.dol_escape_htmltag(GETPOST('phone_mobile')).'"></td></tr>'; */

// Civility
print '<tr><td><label for="civility_id">'.$langs->trans("UserTitle").'</label></td><td colspan="3">';
print $formcompany->select_civility(GETPOSTISSET("civility_id")?GETPOST("civility_id", 'alpha'):$object->civility_id, 'civility_id');
print '</td></tr>';

// Address
print '<tr><td>'.$langs->trans("Address").'</td><td>'."\n";
print '<textarea name="address" id="address" wrap="soft" class="quatrevingtpercent" rows="'.ROWS_3.'">'.dol_escape_htmltag(GETPOST('address', 'none'), 0, 1).'</textarea></td></tr>'."\n";

// Zip / Town
print '<tr><td>'.$langs->trans('Zip').' / '.$langs->trans('Town').'</td><td>';
print $formcompany->select_ziptown(GETPOST('zipcode'), 'zipcode', array('town','selectcountry_id','state_id'), 6, 1);
print ' / ';
print $formcompany->select_ziptown(GETPOST('town'), 'town', array('zipcode','selectcountry_id','state_id'), 0, 1);
print '</td></tr>';

// Country
print '<tr><td>'.$langs->trans('Country').'</td><td>';
$country_id= !empty(GETPOST('country_id'))? GETPOST('country_id'): 1;
if (! $country_id && ! empty($conf->global->MEMBER_NEWFORM_FORCECOUNTRYCODE)) $country_id=getCountry($conf->global->MEMBER_NEWFORM_FORCECOUNTRYCODE, 2, $db, $langs);
if (! $country_id && ! empty($conf->geoipmaxmind->enabled))
{
    $country_code=dol_user_country();
    //print $country_code;
    if ($country_code)
    {
        $new_country_id=getCountry($country_code, 3, $db, $langs);
        //print 'xxx'.$country_code.' - '.$new_country_id;
        if ($new_country_id) $country_id=$new_country_id;
    }
}
$country_code=getCountry($country_id, 2, $db, $langs);
print $form->select_country($country_id, 'country_id');
print '</td></tr>';

// State
if (empty($conf->global->SOCIETE_DISABLE_STATE))
{
    print '<tr><td>'.$langs->trans('State').'</td><td>';
    if ($country_code) print $formcompany->select_state(GETPOST("state_id"), $country_code);
    else print '';
    print '</td></tr>';
}

// EMail
print '<tr><td>'.$langs->trans("Email").' <FONT COLOR="red">*</FONT></td><td><input type="email" name="email" maxlength="255" class="minwidth150" value="'.dol_escape_htmltag(GETPOST('email')).'" required></td></tr>'."\n";


// Other attributes
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

// Comments
print '<tr>';
print '<td class="tdtop">'.$langs->trans("Comments").'</td>';
print '<td class="tdtop"><textarea name="note_private" id="note_private" wrap="soft" class="quatrevingtpercent" rows="'.ROWS_3.'">'.dol_escape_htmltag(GETPOST('note_private', 'none'), 0, 1).'</textarea></td>';
print '</tr>'."\n";

print "</table>\n";

dol_fiche_end();

// Save
print '<div class="center">';
print '<input type="submit" value="'.$langs->trans("Save").'" id="submitsave" class="button">';
if (! empty($backtopage))
{
    print ' &nbsp; &nbsp; <input type="submit" value="'.$langs->trans("Cancel").'" id="submitcancel" class="button">';
}
print '</div>';


print "</form>\n";
print "<br>";
print '</div></div>';


// End of page
llxFooter();
$db->close();
