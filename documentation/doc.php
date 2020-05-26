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

// Configuration header

print '<table class="noborder" width="100%">';



print '</table>';

print '<br>';

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre"><td colspan="2">' . "Gestion page publique". '</td>';
print '</tr>';

// JF FERRY

print '</table>';

print '<br>';

$buffer = file_get_contents(dol_buildpath('./custom/event/documentation/Doc.md', 0));
echo Markdown($buffer);

llxFooter();

$db->close();
