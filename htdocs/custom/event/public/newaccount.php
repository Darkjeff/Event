<?php
/* Copyright (C) 2001-2002	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2001-2002	Jean-Louis Bergamo		<jlb@j1b.org>
 * Copyright (C) 2006-2013	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2012		Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2012		J. Fernando Lagrange    <fernando@demo-tic.org>
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
 *  MEMBER_NEWFORM_EDITAMOUNT           Amount can be edited
 *  MEMBER_NEWFORM_PAYONLINE            Suggest payment with paypal of paybox
 *  MEMBER_NEWFORM_DOLIBARRTURNOVER     Show field turnover (specific for dolibarr foundation)
 *  MEMBER_URL_REDIRECT_SUBSCRIPTION    Url to redirect once subscribe submitted
 *  MEMBER_NEWFORM_FORCETYPE            Force type of member
 *  MEMBER_NEWFORM_FORCEMORPHY          Force nature of member (mor/phy)
 *  MEMBER_NEWFORM_FORCECOUNTRYCODE     Force country
 */

/* Copyright (C) 2001-2007  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2003       Brian Fraval            <brian@fraval.org>
 * Copyright (C) 2004-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005       Eric Seigne             <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@capnetworks.com>
 * Copyright (C) 2008       Patrick Raguin          <patrick.raguin@auguria.net>
 * Copyright (C) 2010-2016  Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2011-2013  Alexandre Spangaro      <aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2015       Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2015       Marcos García           <marcosgdf@gmail.com>
 * Copyright (C) 2015       Raphaël Doursenaud      <rdoursenaud@gpcsolutions.fr>
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
 *  \file       htdocs/societe/soc.php
 *  \ingroup    societe
 *  \brief      Third party card page
 */

define('NOLOGIN','1');
 if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');  // Do not check anti CSRF attack test
 if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Do not check anti POST attack test
 //if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1'); // If there is no need to load and show top and left menu

require '../../../main.inc.php';
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
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/CMailFile.class.php");

if (! empty($conf->adherent->enabled)) require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';

$langs->load("companies");
$langs->load("commercial");
$langs->load("bills");
$langs->load("banks");
$langs->load("users");

if (! empty($conf->categorie->enabled)) $langs->load("categories");
if (! empty($conf->incoterm->enabled)) $langs->load("incoterm");
if (! empty($conf->notification->enabled)) $langs->load("mails");
//print '<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">';
print '<link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">';
print '<link rel="stylesheet" type="text/css" href="checkbox.css">';
//print '<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>';
print '<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>';
print '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>';
print '<script src="./js/bootstrap.min.js"></script>';
//print '<script src="checkboxscript.js"></script>';
print '<link rel="stylesheet" type="text/css" href="hover.css">';

// Load traductions files requiredby by page
$langs->load('event@event');
$langs->load('users');
//test

// Load traductions files requiredby by page
$langs->load("admin");
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");
$langs->load("cron");
//end

global $conf, $db, $user, $langs;

$user->fetch(1);
if (!$conf->global->EVENT_SWITCH_REGISTER){
	print 'conf : '.$conf->global->EVENT_PUBLIC_WEBSITE.'<br />';
	header("Location: http://".$conf->global->EVENT_PUBLIC_WEBSITE);

}
$action = GETPOST('action');
$error = 0;

// CODE TO CLEAN
// $id_url = GETPOST('id');
// $key_url = GETPOST('key');

// if (empty($id_url) || empty($key_url))
// 	$error++;

// if ($error == 0)
// {
// 	$regstat = new Registration($db);
// 	$regstat->fetch($id_url);
// 	$key = get_info_from_table('unique_key', $id_url);
// 	$statut = get_info_from_table('fk_statut', $id_url);
// 	$contact_id = get_info_from_table('fk_user_registered', $id_url);
// 	$contact = new Contact($db);
// 	$contact->fetch($contact_id);
// }

$cuser = new Contact($db);

//$user->fetch(NULL,$_SESSION["dol_login"]);

$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label('user');
$res=$user->fetch_optionals($user->id,$extralabels);
/*### Action ###*/
if ($action == 'participate') {
	}
elseif($conf->global->EVENT_PUBLIC_ACTIVE=='0') {
header("Location: http://".$conf->global->EVENT_PUBLIC_WEBSITE);
	}
else {
/*### View ###*/
print '
<html><head>
<title>'.MAIN_INFO_SOCIETE_NOM.' - '.$langs->trans('ListDayIncoming').'</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>';
print '<body>';

print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/custom/event/public/public.css">';
//fix CSS pour ce fichier, à séparer dans un fichier different
print '<style>.col{
	display: inline-block; min-width: 50%;text-align: left;
}</style>';
print '<meta name="viewport" content="width=device-width"/>';

if(file_exists("DOL_URL_ROOT.'/custom/event/public/logo_header.png")) print '<img src="'.DOL_URL_ROOT.'/custom/event/public/logo_header.png" align="center" alt="" class="logo_header">';

print '<div align=center style="width:100%;">';
print '<div class="container" name="fullpage">';

//Inclu le header de la page
print '<div class="header">';
print $conf->global->EVENT_PUBLIC_HEADER;
print '</div>';

//Inclu la navbar de la page
print '<div class="navbar">';
//print $conf->global->EVENT_PUBLIC_NAVBAR;
print '</div>';
}

//$user->fetch(NULL,$_SESSION["dol_login"]);

$mesg=''; $error=0; $errors=array();

$action		= (GETPOST('action') ? GETPOST('action') : 'create');
$cancel     = GETPOST('cancel');
$backtopage = GETPOST('backtopage','alpha');
$confirm	= GETPOST('confirm');
$socid		= GETPOST('socid','int');


if ($user->societe_id) $socid=$user->societe_id;
if (empty($socid) && $action == 'view') $action='create';

$object = new Societe($db);
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('thirdpartycard','globalcard'));


// Get object canvas (By default, this is not defined, so standard usage of dolibarr)
$object->getCanvas($socid);
$canvas = $object->canvas?$object->canvas:GETPOST("canvas");
$objcanvas=null;
if (! empty($canvas))
{
    require_once DOL_DOCUMENT_ROOT.'/core/class/canvas.class.php';
    $objcanvas = new Canvas($db, $action);
    $objcanvas->getCanvas('thirdparty', 'card', $canvas);
}

// Security check
//$result = restrictedArea($user, 'societe', $socid, '&societe', '', 'fk_soc', 'rowid', $objcanvas);



/*
 * Actions
 */

$parameters=array('id'=>$socid, 'objcanvas'=>$objcanvas);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
    if ($cancel)
    {
        $action='';
        if (! empty($backtopage))
        {
            header("Location: ".$backtopage);
            exit;
        }
    }

    if (GETPOST('getcustomercode'))
    {
        // We defined value code_client
        $_POST["code_client"]="Acompleter";
    }

    if (GETPOST('getsuppliercode'))
    {
        // We defined value code_fournisseur
        $_POST["code_fournisseur"]="Acompleter";
    }

    if($action=='set_localtax1')
    {
    	//obtidre selected del combobox
    	$value=GETPOST('lt1');
    	$object->fetch($socid);
    	$res=$object->setValueFrom('localtax1_value', $value);
    }
    if($action=='set_localtax2')
    {
    	//obtidre selected del combobox
    	$value=GETPOST('lt2');
    	$object->fetch($socid);
    	$res=$object->setValueFrom('localtax2_value', $value);
    }

    // Add new or update third party
    if ((! GETPOST('getcustomercode') && ! GETPOST('getsuppliercode'))
    && ($action == 'add' || $action == 'update'))
    {
        require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

        if (! GETPOST('name'))
        {
            setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ThirdPartyName")), null, 'errors');
            $error++;
            $action='create';
        }
        if (GETPOST('client') < 0)
        {
            setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ProspectCustomer")), null, 'errors');
            $error++;
            $action='create';
        }
        if (GETPOST('fournisseur') < 0)
        {
            setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Supplier")), null, 'errors');
            $error++;
            $action='create';
        }

	 	$object->canvas=$canvas;

        if (GETPOST("private") == 1)
        {
            $object->particulier       = GETPOST("private");

            $object->name              = dolGetFirstLastname(GETPOST('firstname','alpha'),GETPOST('name','alpha'));
            $object->civility_id       = GETPOST('civility_id');	// Note: civility id is a code, not an int
            // Add non official properties
            $object->name_bis          = GETPOST('name','alpha');
            $object->firstname         = GETPOST('firstname','alpha');
        }
        else
        {
            $object->name              = GETPOST('name', 'alpha');
	        $object->name_alias   = GETPOST('name_alias');
        }

        $object->address               = GETPOST('address');
        $object->zip                   = GETPOST('zipcode', 'alpha');
        $object->town                  = GETPOST('town', 'alpha');
        $object->country_id            = GETPOST('country_id', 'int');
        $object->state_id              = GETPOST('state_id', 'int');
        $object->skype                 = GETPOST('skype', 'alpha');
        $object->phone                 = GETPOST('phone', 'alpha');
        $object->fax                   = GETPOST('fax','alpha');
        $object->email                 = GETPOST('email', 'custom', 0, FILTER_SANITIZE_EMAIL);
		

		
		$sql = "SELECT fk_soc, rowid FROM llx_socpeople WHERE lastname='".$object->name_bis."' and firstname='".$object->firstname."'";
		$resql = $db->query($sql);
		if ($resql)
		   $res = $resql->fetch_assoc();

		 if(isset($res) && $res['rowid']!='') 
		 {
			$error++;
			print '<h3>Création du compte impossible. Un compte avec ces nom et prénom existe déjà.</h3><br/><br/>';
			print "<a href='.'>Retour</a";
		 }
		else
		{
			$sql = "SELECT fk_soc, rowid FROM llx_socpeople WHERE email='".$object->email."'";
			$resql = $db->query($sql);
			if ($resql)
			   $res = $resql->fetch_assoc();

		   
			 //print_r($res);
			 if($res  && $res['rowid']!='')
			 {
				$error++;
				print '<h3>Création du compte impossible. Un compte avec cette adresse mail existe déjà.</h3><br/><br/>';
				print "<a href='.'>Retour</a>";
			 }
		}
		 
		 		
        $object->url                   = GETPOST('url', 'custom', 0, FILTER_SANITIZE_URL);
        $object->idprof1               = GETPOST('idprof1', 'alpha');
        $object->idprof2               = GETPOST('idprof2', 'alpha');
        $object->idprof3               = GETPOST('idprof3', 'alpha');
        $object->idprof4               = GETPOST('idprof4', 'alpha');
        $object->idprof5               = GETPOST('idprof5', 'alpha');
        $object->idprof6               = GETPOST('idprof6', 'alpha');
        $object->prefix_comm           = GETPOST('prefix_comm', 'alpha');
        $object->code_client           = GETPOST('code_client', 'alpha');
        $object->code_fournisseur      = GETPOST('code_fournisseur', 'alpha');
        $object->capital               = GETPOST('capital', 'alpha');
        $object->barcode               = GETPOST('barcode', 'alpha');

        $object->tva_intra             = GETPOST('tva_intra', 'alpha');
        $object->tva_assuj             = GETPOST('assujtva_value', 'alpha');
        $object->status                = GETPOST('status', 'alpha');

        // Local Taxes
        $object->localtax1_assuj       = GETPOST('localtax1assuj_value', 'alpha');
        $object->localtax2_assuj       = GETPOST('localtax2assuj_value', 'alpha');

        $object->localtax1_value	   = GETPOST('lt1', 'alpha');
        $object->localtax2_value	   = GETPOST('lt2', 'alpha');

        $object->forme_juridique_code  = GETPOST('forme_juridique_code', 'int');
        $object->effectif_id           = GETPOST('effectif_id', 'int');
        $object->typent_id             = GETPOST('typent_id');

        $object->client                = GETPOST('client', 'int');
        $object->fournisseur           = GETPOST('fournisseur', 'int');

        $object->commercial_id         = GETPOST('commercial_id', 'int');
        $object->default_lang          = GETPOST('default_lang');

        // Webservices url/key
        $object->webservices_url       = GETPOST('webservices_url', 'custom', 0, FILTER_SANITIZE_URL);
        $object->webservices_key       = GETPOST('webservices_key', 'san_alpha');

		// Incoterms
		if (!empty($conf->incoterm->enabled))
		{
			$object->fk_incoterms 		   = GETPOST('incoterm_id', 'int');
			$object->location_incoterms    = GETPOST('location_incoterms', 'alpha');
		}

		// Multicurrency
		if (!empty($conf->multicurrency->enabled))
		{
			$object->multicurrency_code = GETPOST('multicurrency_code', 'alpha');
		}

        // Fill array 'array_options' with data from add form
        $ret = $extrafields->setOptionalsFromPost($extralabels,$object);
		if ($ret < 0)
		{
			 $error++;
			 $action = ($action=='add'?'create':'edit');
		}

        if (GETPOST('deletephoto')) $object->logo = '';
        else if (! empty($_FILES['photo']['name'])) $object->logo = dol_sanitizeFileName($_FILES['photo']['name']);

        // Check parameters
        if (! GETPOST("cancel"))
        {
            if (! empty($object->email) && ! isValidEMail($object->email))
            {
                $langs->load("errors");
                $error++; $errors[] = $langs->trans("ErrorBadEMail",$object->email);
                $action = ($action=='add'?'create':'edit');
            }
            if (! empty($object->url) && ! isValidUrl($object->url))
            {
                $langs->load("errors");
                $error++; $errors[] = $langs->trans("ErrorBadUrl",$object->url);
                $action = ($action=='add'?'create':'edit');
            }
            if ($object->fournisseur && ! $conf->fournisseur->enabled)
            {
                $langs->load("errors");
                $error++; $errors[] = $langs->trans("ErrorSupplierModuleNotEnabled");
                $action = ($action=='add'?'create':'edit');
            }
            if (! empty($object->webservices_url)) {
                //Check if has transport, without any the soap client will give error
                if (strpos($object->webservices_url, "http") === false)
                {
                    $object->webservices_url = "http://".$object->webservices_url;
                }
                if (! isValidUrl($object->webservices_url)) {
                    $langs->load("errors");
                    $error++; $errors[] = $langs->trans("ErrorBadUrl",$object->webservices_url);
                    $action = ($action=='add'?'create':'edit');
                }
            }
            // We set country_id, country_code and country for the selected country
            $object->country_id=GETPOST('country_id')!=''?GETPOST('country_id'):$mysoc->country_id;
            if ($object->country_id)
            {
            	$tmparray=getCountry($object->country_id,'all');
            	$object->country_code=$tmparray['code'];
            	$object->country=$tmparray['label'];
            }

            // Check for duplicate or mandatory prof id
            // Only for companies
	        if (!($object->particulier || $private))
        	{
	        	for ($i = 1; $i <= 6; $i++)
	        	{
	        	    $slabel="idprof".$i;
	    			$_POST[$slabel]=trim($_POST[$slabel]);
	        	    $vallabel=$_POST[$slabel];
	        		if ($vallabel && $object->id_prof_verifiable($i))
					{
						if($object->id_prof_exists($i,$vallabel,$object->id))
						{
							$langs->load("errors");
	                		$error++; $errors[] = $langs->transcountry('ProfId'.$i, $object->country_code)." ".$langs->trans("ErrorProdIdAlreadyExist", $vallabel);
	                		$action = (($action=='add'||$action=='create')?'create':'edit');
						}
					}

            		// Check for mandatory prof id (but only if country is than than ours)
					if ($mysoc->country_id > 0 && $object->country_id == $mysoc->country_id)
            		{
    					$idprof_mandatory ='SOCIETE_IDPROF'.($i).'_MANDATORY';
    					if (! $vallabel && ! empty($conf->global->$idprof_mandatory))
    					{
    						$langs->load("errors");
    						$error++;
    						$errors[] = $langs->trans("ErrorProdIdIsMandatory", $langs->transcountry('ProfId'.$i, $object->country_code));
    						$action = (($action=='add'||$action=='create')?'create':'edit');
    					}
            		}
	        	}
        	}
        }

        if (! $error)
        {
            if ($action == 'add')
            {
                $db->begin();

                //if (empty($object->client))      $object->code_client='';
                //if (empty($object->fournisseur)) $object->code_fournisseur='';

                // Set to -1 ti have auto-generated code from dolibarr
                $object->code_client = -1;
                $object->code_fournisseur = -1;

                $result = $object->create($user);
                dol_syslog("Creation result : ".$result,LOG_DEBUG);
				if ($result >= 0)
                {
                    if ($object->particulier)
                    {
                        dol_syslog("This thirdparty is a personal people",LOG_DEBUG);
                        $result=$object->create_individual($user);
                        if (! $result >= 0)
                        {
                            $error=$object->error; $errors=$object->errors;
                        }
                    }

					// Customer categories association
					$custcats = GETPOST( 'custcats', 'array' );
					$object->setCategories($custcats, 'customer');

					// Supplier categories association
					$suppcats = GETPOST('suppcats', 'array');
					$object->setCategories($suppcats, 'supplier');

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

					if($result == -3) {
						$duplicate_code_error = true;
						$object->code_fournisseur = null;
						$object->code_client = null;
					}

                    $error=$object->error; $errors=$object->errors;
                }

                if ($result >= 0)
                {
                    $db->commit();

                	if (! empty($backtopage))
                	{
               		    header("Location: ".$backtopage);
                    	exit;
                	}
                	else
                	{
                    	$url=$_SERVER["PHP_SELF"]."?socid=".$object->id;
                    	if (($object->client == 1 || $object->client == 3) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) $url=$_SERVER["PHP_SELF"]."?action=create_user&socid=".$object->id;
                    	else if ($object->fournisseur == 1) $url=DOL_URL_ROOT."/fourn/card.php?socid=".$object->id;
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
        }
    }



    // Actions to send emails
    $id=$socid;
    $actiontypecode='AC_OTH_AUTO';
    $trigger_name='COMPANY_SENTBYMAIL';
    $paramname='socid';
    $mode='emailfromthirdparty';
    include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';

    // Actions to build doc
    $id = $socid;
    $upload_dir = $conf->societe->dir_output;
    $permissioncreate=true;
    include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';
}



/*
 *  View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formadmin = new FormAdmin($db);
$formcompany = new FormCompany($db);

if ($socid > 0 && empty($object->id))
{
    $result=$object->fetch($socid);
	if ($result <= 0) dol_print_error('',$object->error);
}

$title=$langs->trans("ThirdParty");
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/thirdpartynameonly/',$conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->name." - ".$langs->trans('Card');
$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';


$countrynotdefined=$langs->trans("ErrorSetACountryFirst").' ('.$langs->trans("SeeAbove").')';

if (is_object($objcanvas) && $objcanvas->displayCanvasExists($action))
{
    // -----------------------------------------
    // When used with CANVAS
    // -----------------------------------------
   	$objcanvas->assign_values($action, $object->id, $object->ref);	// Set value for templates
    $objcanvas->display_canvas($action);							// Show template
}
else
{
    // -----------------------------------------
    // When used in standard mode
    // -----------------------------------------

    if ($action == 'create')
    {
        /*
         *  Creation
         */
		 print '<br /><br /><br /><div class="day">';
		 print '<div class="day_title">'.$langs->trans('Inscription').'</div>';
		 print '<div class="day_description">'.$conf->global->EVENT_PUBLIC_DESCRIPTION_1.'</div>';
		$private=GETPOST("private","int");
		$private=1;
		if (! empty($conf->global->MAIN_THIRDPARTY_CREATION_INDIVIDUAL) && ! isset($_GET['private']) && ! isset($_POST['private'])) $private=1;
    	if (empty($private)) $private=0;

        // Load object modCodeTiers
        $module=(! empty($conf->global->SOCIETE_CODECLIENT_ADDON)?$conf->global->SOCIETE_CODECLIENT_ADDON:'mod_codeclient_leopard');
        if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
        {
            $module = substr($module, 0, dol_strlen($module)-4);
        }
        $dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
        foreach ($dirsociete as $dirroot)
        {
            $res=dol_include_once($dirroot.$module.'.php');
            if ($res) break;
        }
        $modCodeClient = new $module;
        // Load object modCodeFournisseur
        $module=(! empty($conf->global->SOCIETE_CODECLIENT_ADDON)?$conf->global->SOCIETE_CODECLIENT_ADDON:'mod_codeclient_leopard');
        if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
        {
            $module = substr($module, 0, dol_strlen($module)-4);
        }
        $dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
        foreach ($dirsociete as $dirroot)
        {
            $res=dol_include_once($dirroot.$module.'.php');
            if ($res) break;
        }
        $modCodeFournisseur = new $module;

        // Define if customer/prospect or supplier status is set or not
        if (GETPOST("type")!='f')
        {
            $object->client=-1;
            if (! empty($conf->global->THIRDPARTY_CUSTOMERPROSPECT_BY_DEFAULT))  { $object->client=3; }
        }
        if (GETPOST("type")=='c')  { $object->client=3; }   // Prospect / Customer
        if (GETPOST("type")=='p')  { $object->client=2; }
        if (! empty($conf->fournisseur->enabled) && (GETPOST("type")=='f' || (GETPOST("type")=='' && ! empty($conf->global->THIRDPARTY_SUPPLIER_BY_DEFAULT))))  { $object->fournisseur=1; }

        $object->name				= GETPOST('name', 'alpha');
        $object->firstname			= GETPOST('firstname', 'alpha');
        $object->particulier		= $private;
        $object->prefix_comm		= GETPOST('prefix_comm');
        $object->client				= GETPOST('client')?GETPOST('client'):$object->client;

        if(empty($duplicate_code_error)) {
	        $object->code_client		= GETPOST('code_client', 'alpha');
	        $object->fournisseur		= GETPOST('fournisseur')?GETPOST('fournisseur'):$object->fournisseur;
        }
		else {
			setEventMessages($langs->trans('NewCustomerSupplierCodeProposed'),'', 'warnings');
		}

        $object->code_fournisseur	= GETPOST('code_fournisseur', 'alpha');
        $object->address			= GETPOST('address', 'alpha');
        $object->zip				= GETPOST('zipcode', 'alpha');
        $object->town				= GETPOST('town', 'alpha');
        $object->state_id			= GETPOST('state_id', 'int');
        $object->skype				= GETPOST('skype', 'alpha');
        $object->phone				= GETPOST('phone', 'alpha');
        $object->fax				= GETPOST('fax', 'alpha');
        $object->email				= GETPOST('email', 'custom', 0, FILTER_SANITIZE_EMAIL);
        $object->url				= GETPOST('url', 'custom', 0, FILTER_SANITIZE_URL);
        $object->capital			= GETPOST('capital', 'alpha');
        $object->barcode			= GETPOST('barcode', 'alpha');
        $object->idprof1			= GETPOST('idprof1', 'alpha');
        $object->idprof2			= GETPOST('idprof2', 'alpha');
        $object->idprof3			= GETPOST('idprof3', 'alpha');
        $object->idprof4			= GETPOST('idprof4', 'alpha');
        $object->idprof5			= GETPOST('idprof5', 'alpha');
        $object->idprof6			= GETPOST('idprof6', 'alpha');
        $object->typent_id			= GETPOST('typent_id', 'int');
        $object->effectif_id		= GETPOST('effectif_id', 'int');
        $object->civility_id		= GETPOST('civility_id', 'int');

        $object->tva_assuj			= GETPOST('assujtva_value', 'int');
        $object->status				= GETPOST('status', 'int');

        //Local Taxes
        $object->localtax1_assuj	= GETPOST('localtax1assuj_value', 'int');
        $object->localtax2_assuj	= GETPOST('localtax2assuj_value', 'int');

        $object->localtax1_value	=GETPOST('lt1', 'int');
        $object->localtax2_value	=GETPOST('lt2', 'int');

        $object->tva_intra			= GETPOST('tva_intra', 'alpha');

        $object->commercial_id		= GETPOST('commercial_id', 'int');
        $object->default_lang		= GETPOST('default_lang');

        $object->logo = (isset($_FILES['photo'])?dol_sanitizeFileName($_FILES['photo']['name']):'');

        // Gestion du logo de la société
        $dir     = $conf->societe->multidir_output[$conf->entity]."/".$object->id."/logos";
        $file_OK = (isset($_FILES['photo'])?is_uploaded_file($_FILES['photo']['tmp_name']):false);
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

        // We set country_id, country_code and country for the selected country
        $object->country_id=GETPOST('country_id')?GETPOST('country_id'):$mysoc->country_id;
        if ($object->country_id)
        {
            $tmparray=getCountry($object->country_id,'all');
            $object->country_code=$tmparray['code'];
            $object->country=$tmparray['label'];
        }
        $object->forme_juridique_code=GETPOST('forme_juridique_code');
        /* Show create form */

        $linkback="";

            print "\n".'<script type="text/javascript">';
            print '$(document).ready(function () {
						id_te_private=8;
                        id_ef15=1;
                        is_private='.$private.';
						if (is_private) {
							$(".individualline").show();
						} else {
							$(".individualline").hide();
						}
                        $("#radiocompany").click(function() {
                        	$(".individualline").hide();
                        	$("#typent_id").val(0);
							$("#name_alias").show();
                        	$("#effectif_id").val(0);
                        	$("#TypeName").html(document.formsoc.ThirdPartyName.value);
                        	document.formsoc.private.value=0;
                        });
                        $("#radioprivate").click(function() {
                        	$(".individualline").show();
                        	$("#typent_id").val(id_te_private);
							$("#name_alias").hide();
                        	$("#effectif_id").val(id_ef15);
                        	$("#TypeName").html(document.formsoc.LastName.value);
                        	document.formsoc.private.value=1;
                        });
                        $("#selectcountry_id").change(function() {
                        	document.formsoc.action.value="create";
                        	document.formsoc.submit();
                        });
                     });';
            print '</script>'."\n";

            print '<div id="selectthirdpartytype" style="display: none;">';
            print '<div class="hideonsmartphone float" style="display: none;">';
			            print '</div>';
	        print '<label for="radiocompany">';
            print '<input type="radio" id="radiocompany" class="flat" name="private"  value="0"'.($private?'':' checked').'>';
	        print '&nbsp;';
            print $langs->trans("Company/Fundation");
	        print '</label>';
            print ' &nbsp; &nbsp; ';
	        print '<label for="radioprivate">';
            $text ='<input type="radio" id="radioprivate" class="flat" name="private" value="1"'.($private?' checked':'').'>';
	        $text.='&nbsp;';
	        $text.= $langs->trans("Individual");
	        $htmltext=$langs->trans("ToCreateContactWithSameName");
	       print $form->textwithpicto($text, $htmltext, 1, 'help', '', 0, 3);
            print '</label>';
            print '</div>';
            print "<br>\n";


        dol_htmloutput_mesg(is_numeric($error)?'':$error, $errors, 'error');


			// Cree l'objet formulaire mail
			include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
			$formmail = new FormMail($db);
			$formmail->fromname = (isset($_POST['fromname'])?$_POST['fromname']:$conf->global->MAIN_MAIL_EMAIL_FROM);
			$formmail->frommail = (isset($_POST['frommail'])?$_POST['frommail']:$conf->global->MAIN_MAIL_EMAIL_FROM);
			$formmail->trackid=(($action == 'testhtml')?"testhtml":"test");
			$formmail->withfromreadonly=0;
			$formmail->withsubstit=0;
			$formmail->withfrom=1;
			$formmail->witherrorsto=1;
			$formmail->withto=(! empty($_POST['sendto'])?$_POST['sendto']:($user->email?$user->email:1));
			$formmail->withtocc=(! empty($_POST['sendtocc'])?$_POST['sendtocc']:1);       // ! empty to keep field if empty
			$formmail->withtoccc=(! empty($_POST['sendtoccc'])?$_POST['sendtoccc']:1);    // ! empty to keep field if empty
			$formmail->withtopic=(isset($_POST['subject'])?$_POST['subject']:$langs->trans("Test"));
			$formmail->withtopicreadonly=0;
			$formmail->withfile=2;
			$formmail->withbody=(isset($_POST['message'])?$_POST['message']:($action == 'testhtml'?$langs->transnoentities("PredefinedMailTestHtml"):$langs->transnoentities("PredefinedMailTest")));
			$formmail->withbodyreadonly=0;
			$formmail->withcancel=1;
			$formmail->withdeliveryreceipt=1;
			$formmail->withfckeditor=($action == 'testhtml'?1:0);
			$formmail->ckeditortoolbar='dolibarr_mailings';
			// Tableau des substitutions
			$formmail->substit=$substitutionarrayfortest;
			// Tableau des parametres complementaires du post
			$formmail->param["action"]="send";
			$formmail->param["models"]="body";
			$formmail->param["mailid"]=0;
			$formmail->param["returnurl"]=$_SERVER["PHP_SELF"];

			print '<br>';


        print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'" method="post" name="formsoc">';

        print '<input type="hidden" name="action" value="add">';
        print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        print '<input type="hidden" name="private" value='.$object->particulier.'>';
        print '<input type="hidden" name="type" value='.GETPOST("type").'>';
        print '<input type="hidden" name="LastName" value="'.$langs->trans('LastName').'">';
        print '<input type="hidden" name="ThirdPartyName" value="'.$langs->trans('ThirdPartyName').'">';
        if ($modCodeClient->code_auto || $modCodeFournisseur->code_auto) print '<input type="hidden" name="code_auto" value="1">';

        dol_fiche_head(null, 'card', '', 0, '');

        print '<table class="border" width="100%">';

        // Name, firstname

		print '<div class="row"><div class="col account event_label"><b>'.$langs->trans('Civilité').'</b></div>';
		$str = '<div class="col account">'.$formcompany->select_civility($object->civility_id).'</div>';
		$str = str_replace('name="civility_id"', 'name="civility_id required "', $str);
		print $str;
		print '</div>';
		print '<div class="row">';
        if ($object->particulier || $private)
        {
			print '<div class="col account event_label">';
	        print '<span id="TypeName" class="fieldrequired field"><b>'.$langs->trans('LastName','name')."*".'</b></span>';
			print '</div>';

        }
        else
		{
			print '<span span id="TypeName" class="field" required>'.fieldLabel('ThirdPartyName','name')."*".'</span>';
        }
		print '<div class="col account">';
	    print '<input type="text" maxlength="30" size="30" name="name" id="name" value="'.$object->name.'" autofocus="autofocus" required>';
		print '</div>';

		print '</div>';
        // If javascript on, we show option individual
        if ($conf->use_javascript_ajax)
        {
            print '<div class="row"><div class="col account event_label">'.fieldLabel('FirstName','firstname')."*".'</div>';
	        print '<div class="col account"><input type="text" size="30" maxlength="30" name="firstname" id="firstname" value="'.$object->firstname.'" required></div>';
            print '</div>';

        }

		// Email web
		print '<div class="row">';
		print '<div class="col account event_label">';
		print ''.fieldLabel('EMail','email')."*".'';
		print '</div><div class="col account">';
		print '<input type="email" name="email" id="email" size="30" maxlength="128" value="'.$object->email.'" required>';
		print '</div>';
		print '</div>';

        // Alias names (commercial, trademark or alias names)

        // Prospect/Customer
        print '<tr style="display: none;"><td class="titlefieldcreate">'.fieldLabel('ProspectCustomer','customerprospect',1).'';
	    print '<td class="maxwidthonsmartphone">';
	    $selected=0;
        print '<select class="flat" name="client" id="customerprospect" style="display: none;">';
        if (GETPOST("type") == '') print '<option value="-1"></option>';
        if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) print '<option value="2"'.($selected==2?' selected':'').'>'.$langs->trans('Prospect').'</option>';
        if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print '<option value="3"'.($selected==3?' selected':'').'>'.$langs->trans('ProspectCustomer').'</option>';
        if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print '<option value="1"'.' selected="selected" '.'>'.$langs->trans('Customer').'</option>';
        print '<option value="0">'.$langs->trans('NorProspectNorCustomer').'</option>';
        print '</select>';

        print '<table class="nobordernopadding">';
		$tmpcode=$object->code_client;
        print '';
        $s=$modCodeClient->getToolTip($langs,$object,0);
        print $form->textwithpicto('',$s,1);

        print '</tr></table>';
        print '</tr>';


        // Status
        print '<tr style="display: none;"><td style="display: none;">'.fieldLabel('Status','status').'';
        print $form->selectarray('status', array('0'=>$langs->trans('ActivityCeased'),'1'=>$langs->trans('InActivity')),1);
		print '</td>';
        print '</tr>';

        // Address
		print '<div class="row"><div class="col account event_label">';
        print ''.fieldLabel('Address','address').'</div>';
	    print '<div class="col account"><input type="text" name="address" id="address" size="50" class="quatrevingtpercent" rows="'._ROWS_2.'" wrap="soft" required></div>';
		print '</div>';

		// Address
		print '<div class="row"><div class="col account event_label">';
        print ''.fieldLabel('Phone','Phone').'</div>';
	    print '<div class="col account"><input type="text" name="phone" id="phone" size="10" class="quatrevingtpercent" wrap="soft" required></div>';
		print '</div>';

        // Zip / Town
		print '<div class="row"><div class="col account event_label">';
        print ''.fieldLabel('Zip','zipcode').'</div>';
		print '<div class="col account">';
        $str = $formcompany->select_ziptown($object->zip,'zipcode',array('town','selectcountry_id','state_id'),6);
		$str = str_replace('name="zipcode"', 'name="zipcode" required max="99999" min="10"', $str);
		$str = str_replace('type="text"', 'type="number"', $str);
		print $str;
		print '</div><div class="col account event_label">';
        print ''.fieldLabel('Town','town').'';
		print '</div><div class="col account">';
        $str = $formcompany->select_ziptown($object->town,'town',array('zipcode','selectcountry_id','state_id'));
		$str = str_replace('name="town"', 'name="town" size="30" required ', $str);
		print $str;
		print '</div>';
        print '</div>';

		print '<div style="display: none;">';
        // Country
        print '<tr style="display: none;"><td width="25%" style="display: none;">'.fieldLabel('Country','selectcountry_id').'<td colspan="3" class="maxwidthonsmartphone">';
        print $form->select_country((GETPOST('country_id')!=''?GETPOST('country_id'):$object->country_id));
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
        print '</tr>';

        /*// State
        if (empty($conf->global->SOCIETE_DISABLE_STATE))
        {
			print '<div class="row">';
			print '<div class="col account">';
            print ''.fieldLabel('State','state_id');
			print '</div><div class="col account">';
            if ($object->country_id) print $formcompany->select_state($object->state_id,$object->country_code);
            else print $countrynotdefined;
			print '</div>';
			print '</div>';
        }*/
		print '</div>';



        // Type - Size
		print '<span style="display: none !important;">';
        print fieldLabel('ThirdPartyType','typent_id').''."\n";

        $sortparam=(empty($conf->global->SOCIETE_SORT_ON_TYPEENT)?'ASC':$conf->global->SOCIETE_SORT_ON_TYPEENT); // NONE means we keep sort of original array, so we sort on position. ASC, means next function will sort on label.
        print $form->selectarray("typent_id", $formcompany->typent_array(0), 8, 0, 0, 0, '', 0, 0, 0, $sortparam);
        if ($user->admin) print ' '.info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);

        print '';
        print ''.fieldLabel('Staff','effectif_id').'<td class="maxwidthonsmartphone">';
		print '<span style="display: none;">';
        print $form->selectarray("effectif_id", $formcompany->effectif_array(0), $object->effectif_id);
		print '</span>';
        if ($user->admin) print ' '.info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
				print '</span>';
		// Add a variable param to force not using cache (jmobile)




        print '</table>'."\n";

        dol_fiche_end();

        print '<div class="center">';
        print '<input type="submit" class="button" name="create" value="'."Continuer".'">';
        if ($backtopage)
        {
            print ' &nbsp; ';
            print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
        }

        print '</div>'."\n";
        print '</form>'."\n";
		print '</div>';
    }
		if ($action == 'create_user')
        {
	
	
			print '<div class="day"><div class="day_title">Login</div>';
			print '<div class="day_description">'.$conf->global->EVENT_PUBLIC_DESCRIPTION_2.'</div>';
			$login = GETPOST('login');
			$motdepasse = GETPOST('motdepasse');
			$name = GETPOST('name');
			$id = GETPOST('socid');
			$object = new Societe($db);
			$contact = new Contact($db);
			$object->fetch($id);
			$sql = "SELECT fk_soc, rowid FROM llx_socpeople WHERE fk_soc=".$id;
		    $resql = $db->query($sql);
			 if ($resql)
			   $res = $resql->fetch_assoc();
			$contact->fetch($res['rowid']);
            // Full firstname and lastname separated with a dot : firstname.lastname
            include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

            $generated_password='';
            if (! $ldap_sid) // TODO ldap_sid ?
            {
                require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
                $generated_password=getRandomPassword(false);
            }
            $password=$generated_password;
			$password = "";
            // Create a form array
            $formquestion=array(
            array('label' => $langs->trans("LoginToCreate"), 'type' => 'text', 'name' => 'login', 'value' => $contact->firstname.'.'.$contact->lastname, 'required' => 'required'),
            array('label' => $langs->trans("Password"), 'type' => 'password', 'name' => 'password', 'value' => $password, 'required' => 'required'),
            //array('label' => $form->textwithpicto($langs->trans("Type"),$langs->trans("InternalExternalDesc")), 'type' => 'select', 'name' => 'intern', 'default' => 1, 'values' => array(0=>$langs->trans('Internal'),1=>$langs->trans('External')))
            );
        //    $text=$langs->trans("ConfirmCreateContact").'<br>';
			$str = $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$id."&mail=".$contact->email,$langs->trans("CreateDolibarrLogin"),$text,"confirm_create_user",$formquestion,'yes');
			$str = str_replace('name="login"','name="login" required minlength="5" size="25" disabled value="'.strtolower($contact->firstname).'.'.strtolower($contact->lastname).'" maxlength="100"',$str);
			$str = str_replace('name="password"','name="password" required minlength="5" size="25" maxlength="100"',$str);
			print "<style>input{margin-top: 4px;margin-bottom: 4px;}</style>";
            print $str;
			print '</div>';
        }
		$confirm = GETPOST('confirm');
	    if ($confirm == 'yes') $action = 'confirm_create_user';
		if ($action == 'confirm_create_user' && $confirm == 'yes')
	    {
			$mail = GETPOST("mail");
			print '<div class="day">';
			print '<div class="day_title">Vous pouvez désormais vous connecter à l\'adresse <br />'.$mail;
			print '<div class="day_description">'.$conf->global->EVENT_PUBLIC_DESCRIPTION_3.'</div>';
			print '<a href="'.DOL_URL_ROOT.'/index.php'.'">';
			print '<br /><div class="col account"><input type="button" value="'.$langs->trans('Connect').'" class="button submit_login" onclick="window.open(\''.DOL_URL_ROOT.'index.php'.'\', "_self")"></div>';
			print '</a>';
			print '</div>';
			$id = GETPOST("id");
	        // Recuperation contact actuel
			$object = new Societe($db);
			$contact = new Contact($db);
	        $result = $object->fetch($id);
	        if ($result > 0)
	        {
	            $db->begin();

				//On essaye d'obtenir le contact
				$sql = "SELECT fk_soc, rowid FROM llx_socpeople WHERE fk_soc=".$id;
			   $resql = $db->query($sql);
				 if ($resql)
				   $res = $resql->fetch_assoc();
				$contact->fetch($res['rowid']);
	            // Creation user
	            $nuser = new User($db);

				$nuser->socid = $object->id;

				//$userLogin = GETPOST("login");

                // FIX 30/10/2018 David : Impossible d'obtenir le GETPOST d'un champ de formulaire disabled (voir ligne 1091).
                // On va générer le login à partir du contact sous le format prenom.nom
                $userLogin = strtolower($contact->firstname).'.'.strtolower($contact->lastname);
	            $result=$nuser->create_from_contact($contact,$userLogin);	// Do not use GETPOST(alpha)

	            if ($result > 0)
	            {
	                $result2=$nuser->setPassword($user,GETPOST("password"),0,0,1);	// Do not use GETPOST(alpha)
	                if ($result2)
	             	{
	                    $db->commit();
	                }
	                else
	                {
	                    $error=$nuser->error; $errors=$nuser->errors;
	                    $db->rollback();
	                }
	            }
	            else
	            {
	                $error=$nuser->error; $errors=$nuser->errors;
	                $db->rollback();
	            }
	        }
	        else
	        {
	            $error=$object->error; $errors=$object->errors;
	        }
	    }
}


// End of page
$db->close();
