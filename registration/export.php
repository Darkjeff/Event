<?php
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=include("../../../main.inc.php"); // for curstom directory

if (! $res) die("Include of main fails");
require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/eventlevel.class.php");
require_once("../class/registration.class.php");

require_once("../lib/event.lib.php");
require_once("../core/modules/event/modules_event.php");
require_once("../core/modules/registration/modules_registration.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");


// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("event@event");

// Get parameters
$dayid		= GETPOST('dayid','int');
$action		= GETPOST('action','alpha');
$value		= GETPOST('statut');
$statuts_short=array(0=>'Draft',1=>'Waited', 4=>'Confirmed', 5=>'Cancelled',6=>'Closed', 8=>'Queued');

$patterns = array();
	$patterns[0] = '/&eacute;/';
	$patterns[1] = '/&egrave;/';
	$patterns[2] = '/&ecirc;/';
	$patterns[3] = '/&ccedil;/';

$replacements = array();
	$replacements[0] = 'é';
	$replacements[1] = 'è';
	$replacements[2] = 'ê';
	$replacements[3] = 'ç';


function forcerTelechargement($nom,$situation)
  {
 	header('Content-Encoding: UTF-8');
	header('Content-type: text/csv; charset=UTF-8');
 	header('Content-disposition: attachment; filename="'.$nom.'"');
    header('Pragma: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    readfile($situation);
    unlink($situation);
    exit();
  }

$sql_reg = "SELECT r.fk_statut, s.firstname, s.lastname, so.nom, s.poste, s.email";
$sql_reg.=" FROM ".MAIN_DB_PREFIX."event_registration AS r";
$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."socpeople AS s ON s.rowid=r.fk_user_registered";
$sql_reg.=" LEFT JOIN ".MAIN_DB_PREFIX."societe AS so ON so.rowid=s.fk_soc";
$sql_reg.=" WHERE r.fk_eventday = '".$dayid."'";
if ($value != "-1") $sql_reg.=" AND r.fk_statut = '".$value."'";
$sql_reg.="  AND (r.fk_levelday IS NULL OR r.fk_levelday=0)";
$sql_reg.=" ORDER BY r.fk_statut DESC, s.lastname, r.datec ASC;";

$fin = utf8_decode(("Prénom,Nom,Societe,Poste,Statut\r\n"));
$resql_reg=$db->query($sql_reg);
if ($resql_reg) {
	$num2 = $db->num_rows($sql_reg);
	$i = 0;
	while ($i < $num2)
	{
		$res = $resql_reg->fetch_assoc();
		$fin.= utf8_decode($res['firstname']).",";
		$fin.= utf8_decode($res['lastname']).",";
		$fin.= utf8_decode($res['nom']).",";
		$fin.= utf8_decode($res['email']).",";
		$fin.= utf8_decode($res['poste']).",";
		$fin.= utf8_decode(htmlspecialchars_decode(html_entity_decode($langs->trans($statuts_short[$res['fk_statut']]))))."\r\n";
		$i++;
	}
}

$file = fopen("export-".$langs->trans($statuts_short[$res['fk_statut']]).".csv", "w");
fwrite($file, $fin);
fclose($file);
$nom=preg_replace($patterns, $replacements,'export-'.$langs->trans($statuts_short[$res['fk_statut']]).'.csv');
forcerTelechargement($nom,'export-'.$langs->trans($statuts_short[$res['fk_statut']]).'.csv');
?>
