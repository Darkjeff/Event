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


require_once DOL_DOCUMENT_ROOT.'/core/lib/member.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

// Load translation files required by the page
$langs->loadLangs(array("admin","members"));

$extrafields = new ExtraFields($db);
$form = new Form($db);

// Translations
$langs->loadLangs(array("errors","admin","assistant@assistant"));


// List of supported format
$tmptype2label=ExtraFields::$type2label;
$type2label=array('');
foreach ($tmptype2label as $key => $val) $type2label[$key]=$langs->transnoentitiesnoconv($val);

$action=GETPOST('action', 'alpha');
$attrname=GETPOST('attrname', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$elementtype='societe'; //Must be the $table_element of the class that manage extrafield

if (!$user->admin) accessforbidden();


/*
 * Actions
 */

require DOL_DOCUMENT_ROOT.'/core/actions_extrafields.inc.php';


/*
 * View
 */

$textobject = $langs->transnoentitiesnoconv("Members");

$help_url='EN:Module_Foundations|FR:Module_Adh&eacute;rents|ES:M&oacute;dulo_Miembros';
$page_name = "Attributs supplémentaires (Tiers)";
llxHeader('', $langs->trans("MembersSetup"), $help_url);


// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_assistant@assistant');

// Configuration header
$head = assistantAdminPrepareHead();
dol_fiche_head($head, 'tiers_extrafields', '', 0, 'assistant@assistant');


require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_view.tpl.php';

dol_fiche_end();


// Buttons
if ($action != 'create' && $action != 'edit')
{
	print '<div class="tabsAction">';
	print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"]."?action=create\">".$langs->trans("NewAttribute").'</a></div>';
	print "</div>";
}


/* ************************************************************************** */
/*                                                                            */
/* Creation of an optional field											  */
/*                                                                            */
/* ************************************************************************** */

if ($action == 'create')
{
	print '<div name="topofform"></div><br>';
	print load_fiche_titre($langs->trans('NewAttribute'));

    require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_add.tpl.php';
}

/* ************************************************************************** */
/*                                                                            */
/* Edition of an optional field                                               */
/*                                                                            */
/* ************************************************************************** */
if ($action == 'edit' && ! empty($attrname))
{
	print '<div name="topofform"></div><br>';
	print load_fiche_titre($langs->trans("FieldEdition", $attrname));

    require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_edit.tpl.php';
}

// End of page
llxFooter();
$db->close();