<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020 demo
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    assistant/admin/about.php
 * \ingroup assistant
 * \brief   About page of module Assistant.
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once '../lib/assistant.lib.php';

// Translations
$langs->loadLangs(array("errors","admin","assistant@assistant"));

// Access control
if (! $user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Actions
 */

// Start changing value of set_ITIER_ENABLE_PUBLIC developer.baalla@gmail.com
if ($action == 'set_ITIER_ENABLE_PUBLIC') { 
	
	$ITIER_ENABLE_PUBLIC = GETPOST('ITIER_ENABLE_PUBLIC','alpha');
	$value = GETPOST('value','int');
	
	function initialColor($value, $kind, $db){
		
		$db->begin();
	
		// Show constants
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."const";
		$sql.= " WHERE name = '". $kind ."'";
		$result = $db->query($sql);
		$obj = $db->fetch_object($result);
		
		if ( empty($obj) ) {
			$sql = "INSERT INTO ".MAIN_DB_PREFIX."const";
			$sql.= " (name, value, type)";
			$sql.= " VALUES ('". $kind ."', '". $value ."', 'chaine')";
			$res = $db->query($sql);
			if (! $res > 0) $error++;
		} 
		else {
			$sql = "UPDATE ".MAIN_DB_PREFIX."const";
			$sql.= " SET value = '". $value ."'";
			$sql.= " WHERE rowid = ". $obj->rowid;
			$res = $db->query($sql);
			if (! $res > 0) $error++;
		}
		
		if ($res)
		{
			$db->commit();
			return $value;
		}
		else
		{
			$db->rollback();
			return 0;
		}
	}
	
	$conf->global->ITIER_ENABLE_PUBLIC = initialColor($value, 'ITIER_ENABLE_PUBLIC', $db);

}
// /.End changing value set_ITIER_ENABLE_PUBLIC developer.baalla@gmail.com



/*
 * View
 */

$form = new Form($db);

$page_name = "Formulaire d'auto-inscription publique";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_assistant@assistant');

// Configuration header
$head = assistantAdminPrepareHead();
dol_fiche_head($head, 'pre_inscription', '', 0, 'assistant@assistant');


// content start -------------------

print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

// dol_fiche_head($head, 'website', $langs->trans("Members"), -1, 'user');

if ($conf->use_javascript_ajax)
{
 print "\n".'<script type="text/javascript" language="javascript">';
 print 'jQuery(document).ready(function () {
			 function initemail()
			 {
				 if (jQuery("#MEMBER_NEWFORM_PAYONLINE").val()==\'-1\')
				 {
					 jQuery("#tremail").hide();
				 }
				 else
				 {
					 jQuery("#tremail").show();
				 }
			 }
			 function initfields()
			 {
				 if (jQuery("#ITIER_ENABLE_PUBLIC").val()==\'0\')
				 {
					 jQuery("#trforcetype, #tramount, #tredit, #trpayment, #tremail").hide();
				 }
				 if (jQuery("#ITIER_ENABLE_PUBLIC").val()==\'1\')
				 {
					 jQuery("#trforcetype, #tramount, #tredit, #trpayment").show();
					 if (jQuery("#MEMBER_NEWFORM_PAYONLINE").val()==\'-1\') jQuery("#tremail").hide();
					 else jQuery("#tremail").show();
				 }
			 }
			 initfields();
			 jQuery("#ITIER_ENABLE_PUBLIC").change(function() { initfields(); });
			 jQuery("#MEMBER_NEWFORM_PAYONLINE").change(function() { initemail(); });
		 })';
 print '</script>'."\n";
}


$enabledisablehtml = $langs->trans("Activer le formulaire d\'auto-inscription public du site");
if (empty($conf->global->ITIER_ENABLE_PUBLIC))
{
 // Button off, click to enable
 $enabledisablehtml .= '<a class="reposition valignmiddle" href="'.$_SERVER["PHP_SELF"].'?action=set_ITIER_ENABLE_PUBLIC&value=1'.$param.'">';
 $enabledisablehtml .= img_picto($langs->trans("Disabled"), 'switch_off');
 $enabledisablehtml .= '</a>';
}
else
{
 // Button on, click to disable
 $enabledisablehtml .= '<a class="reposition valignmiddle" href="'.$_SERVER["PHP_SELF"].'?action=set_ITIER_ENABLE_PUBLIC&value=0'.$param.'">';
 $enabledisablehtml .= img_picto($langs->trans("Activated"), 'switch_on');
 $enabledisablehtml .= '</a>';
}
print $enabledisablehtml;
print '<input type="hidden" id="ITIER_ENABLE_PUBLIC" name="ITIER_ENABLE_PUBLIC" value="'.(empty($conf->global->ITIER_ENABLE_PUBLIC) ? 0 : 1).'">';


print '</form>';


if (! empty($conf->global->ITIER_ENABLE_PUBLIC))
{
	print '<br>';
	//print $langs->trans('FollowingLinksArePublic').'<br>';
	print img_picto('', 'object_globe.png').' '.$langs->trans("Formulaire d'auto-inscription publique").':<br>';
	if ($conf->multicompany->enabled) {
		$entity_qr='?entity='.$conf->entity;
	} else {
		$entity_qr='';
	}

	// Define $urlwithroot
	$urlwithouturlroot=preg_replace('/'.preg_quote(DOL_URL_ROOT, '/').'$/i', '', trim($dolibarr_main_url_root));
	$urlwithroot=$urlwithouturlroot.DOL_URL_ROOT;		// This is to use external domain name found into config file
	//$urlwithroot=DOL_MAIN_URL_ROOT;					// This is to use same domain name than current

	print '<a target="_blank" href="'.$urlwithroot.'/custom/assistant/new.php'.$entity_qr.'">'.$urlwithroot.'/custom/assistant/new.php'.$entity_qr.'</a>';
}
// content end ---------------------


// Page end
dol_fiche_end();
llxFooter();
$db->close();
