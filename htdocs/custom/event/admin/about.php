<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012		JF FERRY			<jfefe@aternatik.fr>
 * Copyright (C) 2017		Eric GROULT			<eric@code42.fr>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file		admin/about.php
 * \ingroup		event
 * \brief		About Page < event Configurator >
 */

// Dolibarr environment
$res = @include '../../main.inc.php'; // From htdocs directory
if (! $res) {
	$res = @include '../../../main.inc.php'; // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once '../lib/event.lib.php';

dol_include_once('/event/lib/php-markdown/markdown.php');

// Langs
$langs->load("event@event");

// Access control
if (! $user->admin)
  accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
$page_name = "EventAboutTitle";
llxHeader('', $langs->trans($page_name));

// Subheader
print_fiche_titre($langs->trans($page_name));

// Configuration header
$head = event_admin_prepare_head();
dol_fiche_head($head, 'about', $langs->trans('Module1680Name'), 0, 'event@event');

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre"><td colspan="2">' . $langs->trans("Authors") . '</td>';
print '</tr><tr>';

print '<td><b>Laurent LEBLANC</b>&nbsp;-&nbsp;Architecte IT / Ingénieur Développement';

print '</tr></table>';

print '<br>';

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre"><td colspan="2">' . $langs->trans("OldAuthors") . '</td>';
print '</tr>';

// Eric GROULT
print '<td><b>Eric GROULT</b>&nbsp;-&nbsp;Développeur';
print '</td></tr>';


// JF FERRY
print '<td><b>JF FERRY</b>&nbsp;-&nbsp;Développeur';
print '<br>&nbsp;';
print '</td></tr>';

print '</table>';

print '<br>';

print '<h2>Licence</h2>';
print $langs->trans("");
print '<h2>Bugs / comments</h2>';
print $langs->trans("EventAboutMessage");

$buffer = file_get_contents(dol_buildpath('./custom/event/CHANGELOG.md', 0));
echo Markdown($buffer);

llxFooter();

$db->close();
