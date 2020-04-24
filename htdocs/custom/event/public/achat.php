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
require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';


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

if (!($conf->global->EVENT_SWITCH_BOUTIQUE))
	Header("Location: index.php");


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
<title>'.$conf->global->MAIN_INFO_SOCIETE_NOM.' - '.$langs->trans('ListDayIncoming').'</title>
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

if ($conf->global->EVENT_SWITCH_NAVBAR){
print '<div class="navbar">';
print $conf->global->EVENT_PUBLIC_NAVBAR;
print '</div>';
}
// I.0 - Identification
print '<div class="" style="margin-top: ';
if ($conf->global->EVENT_SWITCH_NAVBAR)print '100px;">';
else print '0px;">';

if($_SESSION["dol_login"])
	{
	// IDENTIFIANT
	print '<b class="day_title">'.$langs->trans('Hello').', '.$user->firstname.' '.$user->lastname.'</b>';
	// NOMBRE HEURES RESTANTES
	$new_password = $_POST['new_password'];
	if (isset($_POST['new_password'])){
		$user->setPassword($admin_user, $new_password, 1, 0, 1);
		$user->send_password($admin_user, $new_password, 1);
		print $conf->global->EVENT_MAIL_SUBJECT;
	//	print '<b>Un email de confirmation vous a été envoyé, cliquez sur le lien présent dans celui-ci pour confirmer le changement de mot de passe</b>';
	}
		
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
	
	
	print '<div class="day log">';
	print 'Votre quota restant : <b id="nb_unit">'.($user->array_options['options_event_counter']==''?$langs->trans('Empty'):$user->array_options['options_event_counter']).' '.$conf->global->EVENT_PUBLIC_UNIT_NAME.'</b><br />';
	print '</div>';

//CONTENU DE LA PAGE
//REQUETE POUR OBTENIR LES ID DES PRODUITS
	$sql= "select p.fk_categorie, p.fk_product, c.rowid, c.label from llx_categorie_product AS p LEFT JOIN llx_categorie AS c ON p.fk_categorie = c.rowid WHERE c.label = 'Event'";
	$resql=$db->query($sql);

	
	
	if ($resql){
		$var=true;
		$num = $db->num_rows($resql);
		$i = 0;}
//Initialisation de la facture :
	$buy = $_POST['buy'];
	$id_product = $_POST['id_product'];
	$qty = $_POST['qty'];
	

	if ($buy == 'buy' && $id_product && $qty > 0){ //
	//Selection du client
	
	
		$soc = new Societe($db);
		if (!$user->societe_id) $user->societe_id = 1;
		$soc->fetch($user->societe_id);


		
	//Sélection du produit
		$product = new Product($db);
		$product->fetch($id_product);
	//Création de la facture
		$facture = new Facture($db);
		$facture->ref_client = $user->societe_id;
		$facture->socid = $user->societe_id;
		$nb_unit = $product->array_options['options_nbunitbuy'];

	//Set facture
		$facture->date						= time();
		$facture->socid						= $user->societe_id;
		$facture->type						= Facture::TYPE_STANDARD;
		$facture->number					= "provisoire";
		$facture->day_date					= time();
		$facture->date_pointoftax			= 0;
		$facture->note_public				= '';
		$facture->note_private				= '';
		$facture->ref_client				= $user->societe_id;
		$facture->ref_int					= $_POST['ref_int'];
		$facture->modelpdf					= $conf->global->FACTURE_ADDON_PDF;
		$facture->fk_project				= '';
		$facture->cond_reglement_id			= 1;//avec paypal faut changer ça
		$facture->mode_reglement_id			= 6;//avec paypal faut changer ça
		$facture->fk_account       			= $user->id;
		$facture->amount					= 0;
		$facture->remise_absolue			= 0;
		$facture->remise_percent			= 0;
		$facture->fk_incoterms 				= 0;
		$facture->location_incoterms		= 0;
		$facture->multicurrency_code		= 0;
		$facture->multicurrency_tx 			= 0;

		$usertmp = clone $user;
        
		$id = $facture->create($admin_user);
		
		
			//$id = $facture->create($user);
			
			$result = $facture->addline($product->description,$product->price,$qty,$product->tva_tx,$product->localtax1_tx,
										$product->localtax2_tx,$id_product,0,0,0,0,'','',0,$product->price,1,-1,0,'',0,0,0,0,$product->label, '', '', '', 1);
		$facture->fetch_lines();
		$facture->getLinesArray();

		$facture->fetch($id);
		$facture->ref = $facture->getNextNumRef($soc);
		$facture->generateDocument("crabe", $langs);
		$facture->validate($admin_user);
		$facture->update($admin_user);
		$filename=dol_sanitizeFileName($facture->ref).'.pdf';
		$filedir=$conf->facture->dir_output . '/' . dol_sanitizeFileName($facture->ref);
		$file = $filedir . '/' . $filename;
		$mime = dol_mimetype($file);
		
		
		$user = clone $usertmp;
					

		print '<div class="day"><div class="day_title">ACHAT CONFIRMÉ : '.$qty.' * '.$product->label.'</div>'; //J'ai pas d'idée de message.
		print '<div class="row">';
		print '<div class="col"><a href="'.DOL_URL_ROOT.'/public/payment/newpayment.php?source=invoice&ref='.$facture->ref.'&entity=1"><div class="button">Valider la commande</div></a></div>';
		print '<div class="col"><a href="'.DOL_URL_ROOT.'/custom/event/public/index.php" class="account"><input type="button" value="'.$langs->trans('Retour').'" class="button"></a></div>';

		print '</div></div></div>'; //day button row
		$sujet = 'Facture du [DATE] pour [USER]';
		$str = '<a href="'.DOL_MAIN_URL_ROOT.'/document.php?modulepart=facture&file='.basename($filedir).'%2F'.$filename.'">'.'Télécharger La facture</a>';
		$str_fac = '<a href="'.DOL_MAIN_URL_ROOT.'/public/paypal/newpayment.php?source=invoice&ref='.$facture->ref.'&entity=1">Valider la commande</a>';
		$today = new Datetime("now");
		$str_date = dol_print_date($today->getTimestamp(), "%A %d %B %Y");
		$solde = $user->array_options['options_event_counter'];
		$nb_unit_buy = $product->array_options['options_nbunitbuy'];
		$newsolde += $nb_unit_buy * $qty;

		//Liste des macros
		$mailcontent = str_replace('__VALIDER_FACTURE__'	,$str						,$conf->global->EVENT_MAIL_CONTENT);
		$mailcontent = str_replace('__AFFICHER_FACTURE__'	,$str_fac					,$mailcontent);
		$mailcontent = str_replace('__PRENOM__'				,$user->firstname			,$mailcontent);
		$mailcontent = str_replace('__NOM__'				,$user->lastname			,$mailcontent);
		$mailcontent = str_replace('__QUANTITE__'				,$qty						,$mailcontent);
		$mailcontent = str_replace('__NOM_PRODUIT__'				,$product->label			,$mailcontent);
		$mailcontent = str_replace('__NOUVEAU_SOLDE__'		,$newsolde					,$mailcontent);
		$mailcontent = str_replace('__SOLDE__'				,$solde						,$mailcontent);
		$mailcontent = str_replace('__DATE__'				,$str_date					,$mailcontent);
		$mailcontent = str_replace('__PRIX_UNITE__'			,$product->price_ttc		,$mailcontent);
		$mailcontent = str_replace('__PRIX_TTC__'			,$product->price_ttc * $qty	,$mailcontent);


		$mailfile = new CMailFile( 	  	$subject = isset($conf->global->EVENT_MAIL_SUBJECT)?$conf->global->EVENT_MAIL_SUBJECT:'Facture pour '.$product->label,
  										$to = $user->email,
  										$from = $admin_user->email,
  										$msg = isset($conf->global->EVENT_MAIL_CONTENT)?$mailcontent:'Vous avez une facture.',
  										$filename_list = array(),
  										$mimetype_list = array(),
  										$mimefilename_list = array(),
  										$addr_cc = "",
  										$addr_bcc = "",
  										$deliveryreceipt = 0,
  										$msgishtml = 1,
  										$errors_to = '',
  										$css = '',
  										$trackid = '',
  										$moreinheader = ''
								);
		$ret = $mailfile->sendfile();
		$parameters=array();
			if ($ret == false) {
				print '<b>Erreur dans l\'envoi du mail.</b>';
			}
		
	}
	if (!($buy == 'buy')) { // LA boutique s'affiche si l'achat n'est pas confirmé
	print '<div class="day">';
	print '<div class="day_title">BOUTIQUE</div>';
	print $mailcontent;
	print '<div class="day_description">';
	print isset($conf->global->EVENT_PUBLIC_DESCRIPTION_BOUTIQUE)?$conf->global->EVENT_PUBLIC_DESCRIPTION_BOUTIQUE:'';
	print '</div>';

	while ($i < $num)
	{
					$result = $db->fetch_object($resql);
					$product = new Product($db);
					$product->fetch($result->fk_product);


					print '<b>'.$product->label.'</b>'.' : '.floor($product->price_ttc).' € '.$langs->trans('TTC').' <br /> ';
					print '<form action="" method="post" name="buy-'.$result->fk_product.'">';
					print '<input type="hidden" name="buy" id="buy" value="buy">';
					print '<input type="hidden" name="id_product" id="id_product" value='.$result->fk_product.'>';
					print '<input type="number" style="width: 50px; transform: scale(1.4); margin-right: 5px;" name="qty" id="qty" value="1" min="1" max="99" size="2">';
					print '<input type="submit" class="button" value="acheter" val="acheter"  id="product-'.$result->fk_product.'" name="product"';
					if ($conf->global->EVENT_BOUTIQUE_CGU || $conf->globa->EVENT_BOUTIQUE_CGV) print 'disabled';
					print '>';
					print '</form>';
					$i++;
	}
	?>
	<script>
	$(document).ready(function() {



		$(':checkbox').change(function() {
			var ok = 1;

			$('input[type=checkbox]').each(function () {
				if (this.checked){
				}
				else{
					ok = 0;
				}

			});
			if (ok == 0){
			$('input[name=product]').prop('disabled', true);
		}
			else {
				$('input[name=product]').prop('disabled', false);
			}

		});

});
	</script>
	<?php

	if ($conf->global->EVENT_BOUTIQUE_CGV){
		print '<div class="reglement">';
		print '<input type="checkbox" id="cgv" >';
		print $langs->trans('EVENT_AGREE').'<a href="'.$conf->global->EVENT_BOUTIQUE_CGV.'" target="_blank">'.$langs->trans('EVENT_CGV_LINK').'</a>';
		print '<br />'; //Next Règlement
		}
	if ($conf->global->EVENT_BOUTIQUE_CGU){
		print '<div class="reglement">';
		print '<input type="checkbox" id="reglement">';
		print $langs->trans('EVENT_AGREE').' <a href="'.$conf->global->EVENT_BOUTIQUE_CGU.'" target="_blank">'.$langs->trans('EVENT_CGU_LINK').'</a>';
		print '</div>'; // Règlement
		}
	print '</div>'; // DAY


	print '<div class="row">';
	print '<a href="'.DOL_URL_ROOT.'/custom/event/public/index.php" class="account"><div class="col" ><input type="button" value="'.$langs->trans('Retour').'" class="button"></div></a>';
	if (!($user->admin))
	print '<a href="'.DOL_URL_ROOT.'/custom/event/public/achat.php" class="account"><div class="col" ><input type="button" value="'.$langs->trans('Boutique').'" class="button"></div></a>';
	print '<a href="'.DOL_URL_ROOT.'/user/logout.php" class="account"><div class="col" ><input type="button" value="'.$langs->trans('Disconnect').'" class="button"></div></a>';
	print '</div>';
} //boutique
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


//LISTE COURS INSCRIT


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
			'__REGREF__' 				=> $regstat->ref,
			'__EVENEMENT__'				=> $event->label,
			'__JOURNEE__'				=> $eventday->label,
			'__DATEJOURNEE__'			=> dol_print_date($eventday->date_event, 'day'),
			'__PARTICIPANT__'			=> dolGetFirstLastname($user->firstname, $user->lastname),
			'__LIEN_VALIDATION__'		=> $url2,
			'__TIMESTART__'				=> $eventday->time_start,
			'__TIMEEND__'				=> $eventday->time_end,
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
