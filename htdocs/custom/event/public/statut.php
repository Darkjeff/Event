<?php
//Commenté pour connaître l'utilisateur afin de savoir les cours auquel il est enregistré.
//define('NOLOGIN','1');
if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');  // Do not check anti CSRF attack test
if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Do not check anti POST attack test
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1'); // If there is no need to load and show top and left menu

$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

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


require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';


//TEST:
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/discount.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/invoice.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

// Load traductions files requiredby by page
$langs->load("admin");
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");
$langs->load("cron");
//end



global $conf;

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
$admin_user = new User($db);
$event = new Event($db);
$object = new Day($db);
$extrafields = new ExtraFields($db);
$noresult = 1;

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label('user');
$res=$user->fetch_optionals($user->id,$extralabels);
$admin_user->fetch(1);
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
<title>'.MAIN_INFO_SOCIETE_NOM.' - '.$langs->trans('ListDayIncoming').'</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>';
print '<body>';

print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/custom/event/public/public.css">';
print '<meta name="viewport" content="width=device-width"/>';

if(file_exists("DOL_URL_ROOT.'/custom/event/public/logo_header.png")) print '<img src="'.DOL_URL_ROOT.'/custom/event/public/logo_header.png" align="center" alt="" class="logo_header">';

print '<div align=center style="width:100%;">';
print '<div class="container" name="fullpage">';


print '<div class="header">';
print $conf->global->EVENT_PUBLIC_HEADER;
print '</div>';

print '<div class="navbar">';
print $conf->global->EVENT_PUBLIC_NAVBAR;
print '</div>';

// I.0 - Identification
print '<div class="" style="margin-top: 150px;">';
	?>
	<script>
	$(document).ready(function() {
		$('#displayPass').click(function(){
			if ($('#formpass').hasClass('active')){
				$('#formpass').hide(300);
				$('#formpass').removeClass('active');

			}
			else {
				$('#formpass').show(300);
				$('#formpass').addClass('active');
			}

		});

		$('input[name=new_password_confirm]').keyup(function () {
			var a;
			var b;

			a = $('input[name=new_password_confirm]').val();
			b = $('input[name=new_password]').val();
			var theinput = document.getElementById("new_password_confirm");
			if (a == b)
			{
				theinput.setCustomValidity("");
			}else{
				theinput.setCustomValidity("Les mots de passe ne correspondent pas.");
			}
		});
	});
	</script>
	<?php

  	//Menu boutons
	print '<div class="row">';
	print '<a href="'.DOL_URL_ROOT.'/custom/event/public/index.php" class="account"><div class="col" ><input type="button" value="'.$langs->trans('Retour').'" class="button"></div></a>';
	if ($conf->global->EVENT_SWITCH_BOUTIQUE && (!($user->admin)))
	print '<a href="'.DOL_URL_ROOT.'/custom/event/public/achat.php" class="account"><div class="col" ><input type="button" value="'.$langs->trans('Boutique').'" class="button"></div></a>';
	print '<a href="'.DOL_URL_ROOT.'/user/logout.php" class="account"><div class="col" ><input type="button" value="'.$langs->trans('Disconnect').'" class="button"></div></a>';
	print '</div>';
	//Menu Blanc
	if($_SESSION["dol_login"])
		{
		// IDENTIFIANT
		print '<b class="day_title">'.$langs->trans('Hello').', '.$user->firstname.' '.$user->lastname.'</b>';
		// NOMBRE HEURES RESTANTES
		$new_password = $_POST['new_password'];
		if (isset($_POST['new_password'])){
			$user->setPassword($admin_user, $new_password, 1, 0, 1);
			$user->send_password($admin_user, $new_password, 1);
			print '<b>Un email de confirmation vous a été envoyé, cliquez sur le lien présent dans celui-ci pour confirmer le changement de mot de passe</b>';
		}
	print '<div class="day log">';
	if (!($user->admin))
	print 'Votre quota restant : <b id="nb_unit">'.($user->array_options['options_event_counter']==''?$langs->trans('Empty'):$user->array_options['options_event_counter']).' '.$conf->global->EVENT_PUBLIC_UNIT_NAME.'</b><br />';
	print '<b><a href="#" class="account" id="displayPass">Changer de mot de passe.</a></b><br />';
	print '<form action="" method="post" id="formpass" name="changepass" style="display: none;">';
	print '<b class="labelmdp">Nouveau mot de passe : </b><input type="password" name="new_password" id="new_password" style="text-align: center;"  required><br />';
	print '<b class="labelmdp">Confirmez le mot de passe : </b><input type="password" name="new_password_confirm" id="new_password_confirm" style="text-align: center;" required><br />';
	print '<input type="submit" class="button" value="Envoyer" name="send_password" />';
	print '</form>';
	print '</div>';
}
else
	{


	print '<div class="row">';
	print '<a href="'.DOL_URL_ROOT.'/user/logout.php">';
	print '<div class="col" ><input type="button" value="'.$langs->trans('Disconnect').'" class="button" onclick="window.open(\''.DOL_URL_ROOT.'/user/logout.php\', "_self")"></div>';
	print '</a>';

	if ($user->admin){
		print '<a href="'.DOL_URL_ROOT.'/custom/event/admin/admin_event.php'.'" target="_blank">';
		print '<div class="col" ><input type="button" value="'.$langs->trans('Admin').'" class="button" onclick="window.open(\''.DOL_URL_ROOT.'/custom/event/admin/admin_event.php'.'\', _blank")"></div>';
		print '</a>';
	}
	print '</div>';
}}
$eventdays = $object->fetch_all('ASC', 'date_event'); //$sortorder,$sortfield, $limit, $offset,$arch,$filter



//LISTE FACTURES
print '<div class="day">';
print '<div class="day_title">Liste des factures</div></div><div class="day" style="max-width: 700px; border-radius: 3px;">';
//Il nous faut une facture :
$facturestatic=new Facture($db);


//Requete :
$sql = 'SELECT f.rowid as facid, f.facnumber, f.type, f.amount';
$sql.= ', f.total as total_ht';
$sql.= ', f.tva as total_tva';
$sql.= ', f.total_ttc';
$sql.= ', f.datef as df, f.datec as dc, f.paye as paye, f.fk_statut as statut';
$sql.= ', s.nom, s.rowid as socid';
$sql.= ', SUM(pf.amount) as am';
$sql.= " FROM ".MAIN_DB_PREFIX."societe as s,".MAIN_DB_PREFIX."facture as f";
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'paiement_facture as pf ON f.rowid=pf.fk_facture';
$sql.= " WHERE f.fk_soc = s.rowid AND s.rowid = ".$user->socid;
$sql.= " AND f.entity = ".$conf->entity;
$sql.= ' GROUP BY f.rowid, f.facnumber, f.type, f.amount, f.total, f.tva, f.total_ttc,';
$sql.= ' f.datef, f.datec, f.paye, f.fk_statut,';
$sql.= ' s.nom, s.rowid';
$sql.= " ORDER BY f.datef DESC, f.datec DESC";

$resql=$db->query($sql);

if ($resql)
{
	$var=true;
	$num = $db->num_rows($resql);
	$i = 0;

//On set la facture :
//Initialisation de la facture :
while ($i < $num)
{
				$objp = $db->fetch_object($resql);
				$facturestatic->id = $objp->facid;
				$facturestatic->ref = $objp->facnumber;
				$facturestatic->type = $objp->type;
				$facturestatic->total_ht = $objp->total_ht;
				$facturestatic->total_tva = $objp->total_tva;
				$facturestatic->total_ttc = $objp->total_ttc;
				$filename=dol_sanitizeFileName($facturestatic->ref).'.pdf';
				$filedir=$conf->facture->dir_output . '/' . dol_sanitizeFileName($facturestatic->ref);
				$file = $filedir . '/' . $filename;
				$mime = dol_mimetype($file);
				print $objp->df.' : <a targer="_blank" href="'.DOL_URL_ROOT.'/document.php?modulepart=facture&file='.basename($filedir).'%2F'.$filename.'">'.$objp->nom.' <img src="'.DOL_URL_ROOT.'/theme/common/mime/pdf.png" /></a><br />';
				$i++;
}
}
else{
	print 'Pas de facture.';
}
//Affichage de la facture;

print '</div>';

//LISTE COURS INSCRIT
if(count($object->line)>0)
{
print '<div class="day">';
print '<div class="day_title">HISTORIQUE</div></div>';
print '<div class="day" style="max-width: 700px;border-radius: 3px">';
foreach($object->line as $eventday) {
    //var_dump($eventday);
	$eventlevel = new Eventlevel($db);
	$eventlevel_cal = new Eventlevel_cal($db);
	$tab=$object->LoadLevelForDay($eventday->id);
	$today = new Datetime("now");
	$date = date_create($eventday->date_event.' '.$eventday->time_start);
	$interval = ($date->getTimestamp() - $today->getTimestamp()) / 3600;
	//var_dump($interval);
	//if ($interval > 0)
	//	continue;
    $tab=$object->LoadLevelForDay($eventday->id);
 	if(count($tab)>0) {
    foreach($tab as $levelForDay => $elem) {
		$sql = "SELECT r.fk_user_registered, p.rowid FROM ".MAIN_DB_PREFIX."event_registration as r LEFT JOIN ".MAIN_DB_PREFIX."socpeople as p ON r.fk_user_registered = p.rowid WHERE r.fk_eventday = ".$eventday->id."
		AND (r.fk_user_registered = ".$user->id." OR r.fk_user_registered = ".$user->contact_id.") AND r.fk_levelday = ".$elem['id'];
		//echo 'SQL  :'.$sql."<br/>";
		$resql = $db->query($sql);
    	if ($resql)
      		$res = $resql->fetch_assoc();
		if (!empty($res['rowid'])) {
		       
		    	$lev = 'level-'.$eventday->id.'-'.$elem['id'];

				$calendrier = new Eventlevel_cal($db);
				$calendrier->fetch_all($elem['id'], $eventday->id);
				$blocNumber = count ( $calendrier->lines);
               // echo "BLOC NUMBER :".$blocNumber;
				
				if ($blocNumber>0)
				{
					for($i = 0; $i < $blocNumber; $i ++) {
						//A partir d'ici on a toutes les informations necessaires pour afficher les infos sur un event.
						$noresult = 0;
						print '<b>'.$eventday->label.' : <b>';
						print '<b>Vous étiez inscrit(e) à la journée du </b>';
					print '<span class="level_cours" style="margin: 0;">'.dol_print_date($calendrier->lines[$i]->date_session, 'day').'</span>';
					$duree = ($calendrier->lines[$i]->heuref - $calendrier->lines[$i]->heured) / 60;
					if 		($calendrier->lines [$i]->heured)
						print ', de <span class="level_cours" style="margin: 0;">'.dol_print_date ($calendrier->lines [$i]->heured, 'hour' ).'</span>';
					if 		($calendrier->lines [$i]->heuref)
						print ' à <span class="level_cours" style="margin: 0;">'.dol_print_date ( $calendrier->lines [$i]->heuref, 'hour' ).'</span>';
					if 		($eventday->description_web)
						print '<span class="day_description" style="margin: 0;">'.get_reg($eventday->description_web, $event, $regstat, $eventday, $url2, $user).'</span>';
					else
						print '<br />';
					}
				}
				else 
				{
					print '<b>'.$eventday->label.' : <b>';
					print '<b>Vous étiez inscrit(e) à la journée du </b>';
					print '<span class="level_cours" style="margin: 0;">'.dol_print_date($eventday->date_event, 'day').'</span>';
					/*$duree = ($calendrier->lines[$i]->heuref - $calendrier->lines[$i]->heured) / 60;
					if 		($calendrier->lines [$i]->heured)
						print ', de <span class="level_cours" style="margin: 0;">'.dol_print_date ($calendrier->lines [$i]->heured, 'hour' ).'</span>';
					if 		($calendrier->lines [$i]->heuref)
						print ' à <span class="level_cours" style="margin: 0;">'.dol_print_date ( $calendrier->lines [$i]->heuref, 'hour' ).'</span>';*/
					if 		($eventday->description_web)
						print '<span class="day_description" style="margin: 0;">'.get_reg($eventday->description_web, $event, $regstat, $eventday, $url2, $user).'</span>';
					else
						print '<br />';
				}
		}
		}
	}
}
		if (!$noresult)
		print '</div>';//day
		} //Eventday
if ($noresult){print '<div class="day"><b>Vous n\'avez participé à aucun cours.</b></div>';}

print '</div>';
print '</div>';
print '</div>';

if(file_exists("DOL_URL_ROOT.'/custom/event/public/logo_footer.png")) print '<img src="'.DOL_URL_ROOT.'/custom/event/public/logo_footer.png" alt="" align="center" class="logo_footer">';

print '</div>';
print '</div>';//container
print '</form>';
print '<div class="footer container">';
print $conf->global->EVENT_PUBLIC_FOOTER;
print '</div>';
print '</body></html>';



//Remplace les tags __INFO__ par leur valeur
function get_reg($str, $event, $regstat, $eventday, $url2, $user){
$substit = array(
			'__REGREF__' 					=> $regstat->ref,
			'__EVENEMENT__'				=> $event->label,
			'__JOURNEE__'					=> $eventday->label,
			'__DATEJOURNEE__'			=> dol_print_date($eventday->date_event, 'day'),
			'__PARTICIPANT__'			=> dolGetFirstLastname($user->firstname, $user->lastname),
			'__LIEN_VALIDATION__'	=> $url2,
			'__TIMESTART__'				=> $eventday->time_start,
			'__TIMEEND__'					=> $eventday->time_end,
		);

foreach ($substit as $key => $value){
	$str = str_replace($key, $value, $str);
}
return ($str);
}

function htmlpath($realpath) {
   $i = substr_count($_ENV["SCRIPT_URL"],'/')."<br>";
   $baserealpath=realpath(str_repeat('../',$i-1));
   $htmlpath=str_replace($baserealpath,'',$realpath);
   return $htmlpath;
}
