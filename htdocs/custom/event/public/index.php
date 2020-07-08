<?php
//Commenté pour connaître l'utilisateur afin de savoir les cours auquel il est enregistré.
define('NOLOGIN','1');
if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');  // Do not check anti CSRF attack test
if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Do not check anti POST attack test
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1'); // If there is no need to load and show top and left menu

$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

global $conf, $db, $user, $langs;

if($conf->global->EVENT_PUBLIC_ACTIVE=='0') {
header("Location: http://".$conf->global->EVENT_PUBLIC_WEBSITE);
}

if (! $res) die("Include of main fails");

require_once("../class/event.class.php");

require_once("../class/day.class.php");

require_once("../class/registration.class.php");

require_once("../class/eventlevel.class.php");

require_once("../class/eventlevel_cal.class.php");
require_once("../lib/event.lib.php");
require_once("../core/modules/event/modules_event.php");
require_once("../core/modules/registration/modules_registration.php");
require_once("../lib/html.formregistration.class.php");
require_once("../registration/confirm_register.function.php");//CDN Bootstrap
//print '<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">';
print '<link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">';
print '<link rel="stylesheet" type="text/css" href="checkbox.css">';

//print '<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>';
print '<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>';
print '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>';
print '<script src="./js/bootstrap.min.js"></script>';
print '<script src="checkboxscript.js"></script>';
print '<link rel="stylesheet" type="text/css" href="hover.css">';

// Load traductions files requiredby by page
$langs->load('event@event');
$langs->load('users');


//test
if (! $res) die("Include of main fails");
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");

// Load traductions files requiredby by page
$langs->load("admin");
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");
$langs->load("cron");
//end


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

$regstat = new Registration($db);
$cuser = new Contact($db);
$event = new Event($db);
$object = new Day($db);

$user->fetch(NULL,$_SESSION["dol_login"]);

$extrafields = new ExtraFields($db);



// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label('user');
$res=$user->fetch_optionals($user->id,$extralabels);



print '<form onsubmit="return confirm(\''.$langs->trans('ConfirmSubmit').'\');" id="formevent" name="formevent" method="POST" enctype="multipart/form-data" action="../registration/create.php">';

/*### Action ###*/
if ($action == 'participate') {
	}
elseif($conf->global->EVENT_PUBLIC_ACTIVE=='0') {
header("Location: http://".$conf->global->EVENT_PUBLIC_WEBSITE);
	}
else {

$eventdays = $object->fetch_all('ASC', 'date_event'); //$sortorder,$sortfield, $limit, $offset,$arch,$filter
if($eventdays < 0) dol_print_error($db,$object->error);

/*### View ###*/
print '
<html><head>
<title>'.$conf->global->MAIN_INFO_SOCIETE_NOM.' - '.$langs->trans('ListDayIncoming').'</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>';
print '<body>';

print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/custom/event/public/public.css">';
print '<meta name="viewport" content="width=device-width"/>';

if(file_exists("DOL_URL_ROOT.'/custom/event/public/logo_header.png")) print '<img src="'.DOL_URL_ROOT.'/custom/event/public/logo_header.png" align="center" alt="" class="logo_header">';

print '<div align=center style="width:100%;">';
print '<div class="container" name="fullpage">';

//Inclu le header de la page
print '<div class="header">';
print $conf->global->EVENT_PUBLIC_HEADER;
print '</div>';

//Inclu la navbar de la page
if ($conf->global->EVENT_SWITCH_NAVBAR){
print '<div class="navbar">';
print $conf->global->EVENT_PUBLIC_NAVBAR;
print '</div>';
}
//Inclu un article, une brève présentation, etc
print '<div class="field-item even article" style="margin-top: ';
if ($conf->global->EVENT_SWITCH_NAVBAR)print '100px;">';
else print '0px;">';
print $conf->global->EVENT_PUBLIC_CONTENT;
print '</div>';

//Class globale de page & Event. Préferer les class Bootstrap & les code en BDD
print '<div class="">';

// I.0 - Identification
if($_SESSION["dol_login"])
	{
	// IDENTIFIANT On affiche les informations Utilisateur
	print '<b class="day_title">'.$langs->trans('Hello').', '.$user->firstname.' '.$user->lastname.'</b>';
	print '<div class="day log">';	// NOMBRE HEURES RESTANTES
	if (!($user->admin)){
	print $langs->trans('QuotaRestant').' <b id="nb_unit" class="quota_unit">'.($user->array_options['options_event_counter']==''?'0':$user->array_options['options_event_counter']).'</b> '.'<b>'.$conf->global->EVENT_PUBLIC_UNIT_NAME.'</b>';
	print '<div class="panier"><span class="glyphicon glyphicon-shopping-cart"></span> '.$langs->trans('Panier').' <b><span class="totalvalue level_cours" class="level_cours" style="margin : 0;">0</span> '.$conf->global->EVENT_PUBLIC_UNIT_NAME.'</b></div>';
}
	print '<div><span class="glyphicons glyphicons-vcard"></span><a href="'.DOL_URL_ROOT.'/custom/event/public/statut.php" class="account">Accéder à mon compte</a></div>';
	//DEBUG
	if(MAIN_FEATURES_LEVEL=='3') print '<br />'.$user->showOptionals($extrafields);
	}
else
	{
	print '<div class="row">';
	print '<a href="'.DOL_URL_ROOT.'/index.php'.'">';
	print '<div class="col" ><input type="button" value="'.$langs->trans('Connect').'" class="button" onclick="window.open(\''.DOL_URL_ROOT.'index.php'.'\', "_self")"></div>';
	print '</a>';

	if ($conf->global->EVENT_SWITCH_REGISTER){
	print '<a href="'.DOL_URL_ROOT.'/custom/event/public/newaccount.php'.'">';
	print '<div class="col"><input type="button" value="'.$langs->trans('Create').'" class="button"></div>';
	print '</div>';
	print '</a>';
}
	}
print '</div>';

if($_SESSION["dol_login"])
{
	print '<div class="day send_error" id="send_error" style="display: none;">';
	print '<span class="date_important" style="color: white;">'.$langs->trans('WarningEvent').'</span><br />';
	print $langs->trans('pleaseCheck');
	print '</div>';

	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="addd">';
	print '<input type="hidden" name="fk_user_create" value="'.$user->contactid.'">'; // User who registred
	print '<input type="hidden" name="fk_user_registered" value="'.$user->contactid.'">'; // User who will be registred
	print '<input type="hidden" name="registration_valid_after_create" value="2">';
	print '<input type="hidden" name="redirect_to" value="../public/index.php">';

	print '<div class="row">';


	if ($user->admin){
		print '<a href="'.DOL_URL_ROOT.'/custom/event/admin/admin_event.php'.'" target="_blank">';
		print '<div class="col" ><input type="button" value="'.$langs->trans('Admin').'" class="button" onclick="window.open(\''.DOL_URL_ROOT.'/custom/event/admin/admin_event.php'.'\', _blank")"></div>';
		print '</a>';
	}
	if ($conf->global->EVENT_SWITCH_BOUTIQUE && (!($user->admin)))
	print '<a href="'.DOL_URL_ROOT.'/custom/event/public/achat.php" class="account"><div class="col" ><input type="button" value="'.$langs->trans('Boutique').'" class="button"></div></a>';
	print '<div class="col send_event s_ev"><input type="submit" id="send_event_top" value="'.$langs->trans('Reserve').'" class="button"></div>';
	print '<a href="'.DOL_URL_ROOT.'/user/logout.php">';
	print '<div class="col" ><input type="button" value="'.$langs->trans('Disconnect').'" class="button" onclick="window.open(\''.DOL_URL_ROOT.'/user/logout.php\', "_self")"></div>';
	print '</a>';
	print '</div>';
}

/* Ébauche de code en test pour l'envoi du formulaire
//On essaye d'initialiser la db
$nb_create=0;
$db->begin();
$registration_to_create = array();
$nb=0;
*/

	if(count($object->line)>0)
	{
	// Tableau des journées
					$n = -1;
					$today = new Datetime("now");

	foreach($object->line as $eventday) {

        $date = date_create($eventday->date_event.' '.$eventday->time_start);

        $societe = new Societe($db);
		$societe->fetch($eventday->fk_soc);
		$var = !$var;

		$event->fetch($eventday->fk_event);
	    $interval_day = $date->getTimestamp() - $today->getTimestamp();

	    //$inter = date_diff($date, $today);
        $inter = date_diff($today, $date);

    //var_dump($inter->format("%r%a"));
    //var_dump($interval_day);
	
	if ($inter->format("%r%a") < 0)
        continue; //On skip la boucle si l'article est dans le passé.

	
//Pour chaque journée on inscrit dans le groupe choisi

		// I. - On liste les journées
			// Si page publique ON, on affiche
			// I.1 - On affiche le nom et la description
		    // I.2 - On la date l'heure d'arrivé
			// I.3 - On affiche un lien publique pour plus d'informations - PLUS TARD

			// I.5 - On test la présence de Groupe Définis
				// I.5.1 - On affiche le Nombre de place possible
				// I.5.2 - On affiche le Nom de place restante
				// I.5.3 - On affiche si le groupe est fermé
				// - On Affiche les plages horaire de la journée

				// I.1 - Name of the day
				print '<div class="day">';
				//init substitution array

				print '<span class="day_title">'.$eventday->label.'</span>';
				// I.1 - Description of the day
				print '<span class="day_cours">'.$langs->trans('Eventt').'</span>';
				print '<div class="day_date">'.dol_print_date($eventday->date_event,'%d').'<br />'
				.'<span class="day_month">'.strtoupper(str_replace('.','', dol_print_date($eventday->date_event, '%b'))).'</span>'.'<br />'
				.dol_print_date($eventday->date_event, '%Y')
				.'</div>';
				print '&nbsp;'.$langs->trans("EventPeriodTimeB").' : '.'<span class="date_important">'.$eventday->time_start.'</span>';
				print ' - '.$langs->trans("EventPeriodTimeE").' : '.'<span class="date_important">'.$eventday->time_end.'</span>';
				//Remplace les tags __INFO__ par leur valeur
				print '<span class="day_description">'.get_reg($eventday->description_web, $event, $regstat, $eventday, $url2, $user).'</span>';
				// Date

				// I.5 - Groupe
				$eventlevel = new Eventlevel($db);
				$eventlevel_cal = new Eventlevel_cal($db);
				$tab=$object->LoadLevelForDay($eventday->id);
				if(MAIN_FEATURES_LEVEL =='3') print '<br />LoadLevelForDay : '.print_r($tab);

				if(count($tab)>0) {
					foreach($tab as $levelForDay => $elem) {
						$lev = 'level-'.$eventday->id.'-'.$elem['id'];
						$leve = $elem['id'];

						$calendrier = new Eventlevel_cal($db);
						$calendrier->fetch_all($elem['id'], $eventday->id);
						$blocNumber = count ( $calendrier->lines);

						$ishour = 0;
						for($i = 0; $i < $blocNumber; $i ++) {
								$duree = ($calendrier->lines[$i]->heuref - $calendrier->lines[$i]->heured) / 60;
								if ($calendrier->lines [$i]->heured) print '<div class="hvr-fade group"><div class="level_cours">'.dol_print_date ($calendrier->lines [$i]->heured, 'hour' ).''; $ishour = 1;
								if ($calendrier->lines [$i]->heuref) print ' - '.dol_print_date ( $calendrier->lines [$i]->heuref, 'hour' ).'</div>';
						}

	            //Requête pour savoir si les cases sont cochées par défaut.
	            $sql = "SELECT r.fk_user_registered, p.rowid FROM ".MAIN_DB_PREFIX."event_registration as r LEFT JOIN ".MAIN_DB_PREFIX."socpeople as p ON r.fk_user_registered = p.rowid WHERE r.fk_eventday = ".$eventday->id."
	            AND (r.fk_user_registered = ".$user->id." OR r.fk_user_registered = ".$user->contact_id.") AND r.fk_levelday = ".$elem['id'];
	            $resql = $db->query($sql);
	              if ($resql)
	                $res = $resql->fetch_assoc();
							$limit = $conf->global->EVENT_PUBLIC_REGISTRATION_LIMIT_DATE;
							$limit_unregister = $conf->global->EVENT_PUBLIC_UNREGISTRATION_LIMIT_DATE;
							if (!$limit) $limit = 0;
							if (!$limit_unregister) $limit_unregister = 0;
							$date = date_create($eventday->date_event.' '.$eventday->time_start);
							$interval = ($date->getTimestamp() - $today->getTimestamp()) / 3600;
							if (($elem['place_left'] == 0 && empty($res['rowid']))			|| 	/* Plus de place_dispo	*/
							 	$interval < $limit	 										|| 		/* Trop tard pour s'inscrire */
								($interval < $limit_unregister && !empty($res['rowid']))	||
								($user->admin)){
							$lock = true;
							if ($duree > $user->array_options['options_event_counter']) $error_checkbox = $langs->trans('errNoUnit');
							if ($interval < $limit) $error_checkbox = $langs->trans('errTooLateSub');
							if ($interval < $limit_unregister && !empty($res['rowid'])) $error_checkbox = $langs->trans('errTooLateUnSub');
							if ($elem['place_left'] == 0) $error_checkbox = $langs->trans('errNoSpot');
							if ($user->admin)				$error_checkbox = $langs->trans('errAdmin');
							}
							else {
								$lock = false;
							}

							$n++;
								if($elem['full']=='1') {
								print '<il>'.$elem['description'].'</il>';
								print '<br /><b>'.$langs->trans('GrpFull').'</b>';
								}
							elseif($elem['place_dispo'] != '0') {
											if (!$lock) {
								if($_SESSION["dol_login"])print '<label class="label-cours" style="margin-left: 6px;" for="'.$lev.'">';
								if($_SESSION["dol_login"])print '<label  for="'.$lev.'">';}
								print '<span class="content_cours">';
								print '<il>'.$elem['description'].'</il>';
								if (!$conf->global->EVENT_PLACE_AVAILABLE){
								print ' - '.$langs->trans('PlaceAvailable').' : ';
								print '<b>'.$elem['place_left'].'</b>&nbsp;/&nbsp;<b>'.$elem['place_dispo'].'</b>';}
								else{
									$nbInscrit = $elem['place_dispo'] - $elem['place_left'];
									print ' - '.$langs->trans('nombreInscrit').' : ';
									print '<b>'.$nbInscrit.'</b>&nbsp;/&nbsp;<b>'.$elem['place_dispo'].'</b>';
								}
								print '</span>';
								if($_SESSION["dol_login"] && (!$lock))print '</label>';
									if($_SESSION["dol_login"]) {
								if (!empty($res['rowid'])) print '<span class="desinscrire"> Annuler ';
								print '<div class="btn-group " data-toggle="buttons"';
								print '>';
								print '<label class="btn btn-default btn-cours';
	            			//	if (!empty($res['rowid'])) print ' active"';
								//else print '"';
								print '"';
								if ($lock) print ' disabled ';
	              				print '>';
								if (!$lock){
											if (!empty($res['rowid']))	print '<span class="glyphicon glyphicon-log-out"></span>';
								 								else	print '<span class="glyphicon glyphicon-ok"></span>';
							 				}
								else print '<span class="glyphicon glyphicon-ok"></span>';

								//Si il y a de la place, on affiche la checkbox
								print '&nbsp;<input type="checkbox"';
								//print ' class="checkbox_day"';
								print ' name="fk_level['.$eventday->id.'][]"';
								if (!empty($res['rowid'])) $leve = '-'.$leve;
								print ' value="'.$leve.'"';
								print ' data-price="'.$duree.'" ';
								print ' id="'.$lev.'"';
								print ' autocomplete="off"';
								if (!empty($res['rowid'])){
                                    print ' class="checkbox_day payed" ';
                                }else{
                                    print ' class="checkbox_day" ';
                                }
								if ($lock) print ' disabled ';
								// print 'autocomplete="off" id="'.$lev.'"';
								print 'value="'.$duree.'"';
	              			//	if (!empty($res['rowid'])) print ' checked';

	          					print '>';
								if($_SESSION["dol_login"] && !$lock)print '</label>';
								print '</div>';
							if($_SESSION["dol_login"] && !$lock)print '</label>';
								if ($lock) print ' <b><i>'.$error_checkbox.'</i></b>';
							}
							if (!empty($res['rowid'])) print '</span>';
						if (!$lock)
						print '<span><i> '.$duree.' '.$conf->global->EVENT_PUBLIC_UNIT_NAME.' unités</i></span>';
						}
						else {
							print '<b>'.$langs->trans('PlaceNoLimit').'</b>';
							}
							if ($ishour)
							print '</div>';
						print '<br />';
						}
					}
				else {
					if($conf->global->EVENT_LEVEL_DEFAULT_LEVEL_DISPO == '0')
					print '<br />'.$langs->trans('PlaceNoLimit');
				}
			print "</div>";
			}
		}
		else {
			print $langs->trans('NoEventIncoming');
		}
    print '<div class="day send_error" id="send_error" style="display: none;">';
    print '<span class="date_important" style="color: #f4f4f4;">'.$langs->trans('WarningEvent').'</span><br />';
    print $langs->trans('pleaseCheck');
    print '</div>';
print '<div class="row">';
if($_SESSION["dol_login"])
{
	// NOMBRE HEURES RESTANTES
	if ($user->admin) {
		print '<a href="'.DOL_URL_ROOT.'/custom/event/admin/admin_event.php'.'" target="_blank">';
		print '<div class="col" ><input type=on" value="'.$langs->trans('Admin').'" class="button" onclick="window.open(\''.DOL_URL_ROOT.'/custom/event/admin/admin_event.php'.'\', "_blank")"></div>';
		print '</a>';
	}
	if ($conf->global->EVENT_SWITCH_BOUTIQUE && (!($user->admin)))
	print '<a href="'.DOL_URL_ROOT.'/custom/event/public/achat.php" class="account"><div class="col" ><input type="button" value="'.$langs->trans('Boutique').'" class="button"></div></a>';
	print '<div class="col send_event s_ev"><input id="send_event_bot"  type="submit" value="'.$langs->trans('Reserve').'" class="button"></div>';
	print '	<a href="'.DOL_URL_ROOT.'/user/logout.php"><div class="col" ><input type="button" value="'.$langs->trans('Disconnect').'" class="button" onclick="window.open(\''.DOL_URL_ROOT.'/user/logout.php\', "_self")"></div></a>';

}
print '</div>';
print '<div class="footer">';
print $conf->global->EVENT_PUBLIC_FOOTER;
/*
print '</div>';
print '<a href="http://'.$conf->global->EVENT_PUBLIC_WEBSITE.'" target="_blank">'.$conf->global->EVENT_PUBLIC_WEBSITE.'</a>';
print '</div>';
*/

if(file_exists("DOL_URL_ROOT.'/custom/event/public/logo_footer.png")) print '<img src="'.DOL_URL_ROOT.'/custom/event/public/logo_footer.png" alt="" align="center" class="logo_footer">';

print '</div>';
print '</div>';
print '</form>';
print '</body></html>';
}

//Remplace les tags __INFO__ par leur valeur
function get_reg($str, $event, $regstat, $eventday, $url2, $user){
$substit = array(
			'__REGREF__' 					=> $regstat->ref,
			'__EVENEMENT__'					=> $event->label,
			'__JOURNEE__'					=> $eventday->label,
			'__DATEJOURNEE__'				=> dol_print_date($eventday->date_event, 'day'),
			'__PARTICIPANT__'				=> dolGetFirstLastname($user->firstname, $user->lastname),
			'__LIEN_VALIDATION__'			=> $url2,
			'__TIMESTART__'					=> $eventday->time_start,
			'__TIMEEND__'					=> $eventday->time_end,
		);

foreach ($substit as $key => $value){
	$str = str_replace($key, $value, $str);
}
return ($str);
}
