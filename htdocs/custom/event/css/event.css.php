<?php
/* Copyright (C) 2010-2012 Regis Houssin  <regis@dolibarr.fr>
 * Copyright (C) 2012      JF FERRY       <jfefe@aternatik.fr>
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
 */

/**
 *		\file       htdocs/event/css/event.css.php
 *		\brief      Fichier de style CSS complementaire du module Event
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled cause need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled to increase speed. Language code is found on url.
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled cause need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK',1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL',1);
if (! defined('NOLOGIN'))         define('NOLOGIN',1);
if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML',1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX','1');


$res=@include("../../main.inc.php");					// For root directory
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");

// Define css type
header('Content-type: text/css');
// Important: Following code is to avoid page request by browser and PHP CPU at
// each Dolibarr page access.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');


if (! empty($_GET["lang"])) $langs->setDefaultLang($_GET["lang"]);	// If language was forced on URL by the main.inc.php
$langs->load("main",0,1);
$right=($langs->direction=='rtl'?'left':'right');
$left=($langs->direction=='rtl'?'right':'left');
?>

#registration_list tr:hover td {border-bottom: 3px solid #000000;}



div.mainmenu.event {
	background-image: url(<?php echo dol_buildpath('/event/img/event.png',1) ?>);
}

.editkey_select.event {
	background: url(<?php echo dol_buildpath('/event/img/object_event.png',1) ?>) right center no-repeat;
	cursor: pointer;
}

.icon.icon--event {
    background: url(<?php echo dol_buildpath('custom/event/img/event.png', 1) ?> center no-repeat;
}

/*
	Progress bar
*/
.progress-bar {
	background-color: #FFFFFF;
	height: 15px;
	padding: 1px;
	width: 100%;
	margin: 3px 0;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	-moz-box-shadow: 0 1px 5px #CCCCCC inset, 0 1px 0 #CCCCCC;
	-webkit-box-shadow: 0 1px 5px #CCCCCC inset, 0 1px 0 #CCCCCC;
	box-shadow: 0 1px 5px #CCCCCC inset, 0 1px 0 #444;
}

.progress-bar span {
	display: inline-block;
	font-weight: bold;
	height: 100%;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	-moz-box-shadow: 0 1px 0 rgba(255, 255, 255, .2) inset;
	-webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, .2) inset;
	box-shadow: 0 1px 0 rgba(255, 255, 255, .2) inset;
        -webkit-transition: width .4s ease-in-out;
        -moz-transition: width .4s ease-in-out;
        -ms-transition: width .4s ease-in-out;
        -o-transition: width .4s ease-in-out;
        transition: width .4s ease-in-out;
}

.blue span {
	background-color: #34c2e3;
}

.orange span {
	  background-color: #fecf23;
	  background-image: -webkit-gradient(linear, left top, left bottom, from(#fecf23), to(#fd9215));
	  background-image: -webkit-linear-gradient(top, #fecf23, #fd9215);
	  background-image: -moz-linear-gradient(top, #fecf23, #fd9215);
	  background-image: -ms-linear-gradient(top, #fecf23, #fd9215);
	  background-image: -o-linear-gradient(top, #fecf23, #fd9215);
	  background-image: linear-gradient(top, #fecf23, #fd9215);
}

.green span {
	  background-color: #a5df41;
	  background-image: -webkit-gradient(linear, left top, left bottom, from(#a5df41), to(#4ca916));
	  background-image: -webkit-linear-gradient(top, #a5df41, #4ca916);
	  background-image: -moz-linear-gradient(top, #a5df41, #4ca916);
	  background-image: -ms-linear-gradient(top, #a5df41, #4ca916);
	  background-image: -o-linear-gradient(top, #a5df41, #4ca916);
	  background-image: linear-gradient(top, #a5df41, #4ca916);
}

.red span {
	  background-color: #f0a3a3;
	  background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f0a3a3),color-stop(1, #f42323));
	  background-image: -webkit-linear-gradient(top, #f0a3a3, #f42323);
      background-image: -moz-linear-gradient(top, #f0a3a3, #f42323);
      background-image: -ms-linear-gradient(top, #f0a3a3, #f42323);
      background-image: -o-linear-gradient(top, #f0a3a3, #f42323);
}

.not-active {
   pointer-events: none;
   cursor: default;
   color: #d9d9d9 !important;
   border: 1px solid #d9d9d9 !important;
}

.img_mail {
	width: 70%;
}

.img_mail img{
	transition: all 0.7s ease-out;
	width: 50%;
	opacity: 0.2;
}

.img_mail:hover img{
	transition: all 0.7s ease-out;
	width: 100%;
	opacity: 1;
}
