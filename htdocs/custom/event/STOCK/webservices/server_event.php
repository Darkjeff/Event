<?php
/* Copyright (C) 2006-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *       \file       htdocs/webservices/server_event.php
 *       \brief      File that is entry point to call Dolibarr WebServices
 *       \version    $Id: server_event.php,v 1.7 2010/12/19 11:49:37 eldy Exp $
 */

// This is to make Dolibarr working with Plesk
set_include_path($_SERVER['DOCUMENT_ROOT'].'/htdocs');

$res = require_once("../../master.inc.php");
if (! $res)	$res = require_once("../../../master.inc.php");
if (! $res) die("Include of main fails");

require_once(NUSOAP_PATH.'/nusoap.php');		// Include SOAP

require_once(DOL_DOCUMENT_ROOT."/core/lib/ws.lib.php");
dol_include_once("/event/class/event.class.php");
dol_include_once("/event/class/day.class.php");
dol_include_once("/event/class/registration.class.php");
dol_include_once("/event/class/eventlevel.class.php");
dol_include_once("/event/class/eventoptions.class.php");

require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';


dol_syslog("Call Event webservices interfaces");

// Enable and test if module web services is enabled
if (empty($conf->global->MAIN_MODULE_WEBSERVICES))
{
    $langs->load("admin");
    dol_syslog("Call Dolibarr webservices interfaces with module webservices disabled");
    print $langs->trans("WarningModuleNotActive",'WebServices').'.<br><br>';
    print $langs->trans("ToActivateModule");
    exit;
}

// Create the soap Object
$server = new nusoap_server();
$server->soap_defencoding='UTF-8';
$server->decode_utf8=false;
$ns='http://www.dolibarr.org/ns/';
$server->configureWSDL('WebServicesDolibarrEvent',$ns);
$server->wsdl->schemaTargetNamespace=$ns;


// Define WSDL Authentication object
$server->wsdl->addComplexType(
    'authentication',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'dolibarrkey' => array('name'=>'dolibarrkey','type'=>'xsd:string'),
    	'sourceapplication' => array('name'=>'sourceapplication','type'=>'xsd:string'),
    	'login' => array('name'=>'login','type'=>'xsd:string'),
    	'password' => array('name'=>'password','type'=>'xsd:string'),
        'entity' => array('name'=>'entity','type'=>'xsd:string'),
    )
);

// Define WSDL Return object
$server->wsdl->addComplexType(
    'result',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'result_code' => array('name'=>'result_code','type'=>'xsd:string'),
        'result_label' => array('name'=>'result_label','type'=>'xsd:string'),
    )
);

// Define other specific objects
$server->wsdl->addComplexType(
    'event',
    'complexType',
    'struct',
    'all',
    '',
    array(

		'id' => array('name'=>'id','type'=>'xsd:string'),
		'entity' => array('name'=>'entity','type'=>'xsd:string'),
		'ref' => array('name'=>'ref','type'=>'xsd:string'),
		'socid' => array('name'=>'socid','type'=>'xsd:string'),
		'datec' => array('name'=>'datec','type'=>'xsd:string'),
		'tms' => array('name'=>'tms','type'=>'xsd:string'),
		'date_start' => array('name'=>'date_start','type'=>'xsd:string'),
		'date_end' => array('name'=>'date_end','type'=>'xsd:string'),
		'label' => array('name'=>'label','type'=>'xsd:string'),
		'description' => array('name'=>'description','type'=>'xsd:string'),
		'price_day' => array('name'=>'price_day','type'=>'xsd:string'),
		'total_ht' => array('name'=>'total_ht','type'=>'xsd:string'),
		'total_tva' => array('name'=>'total_tva','type'=>'xsd:string'),
		'total_ttc' => array('name'=>'total_ttc','type'=>'xsd:string'),
		'tva_tx' => array('name'=>'tva_tx','type'=>'xsd:string'),
		'accountancy_code' => array('name'=>'accountancy_code','type'=>'xsd:string'),
		'fk_user_create' => array('name'=>'fk_user_create','type'=>'xsd:string'),
		'fk_statut' => array('name'=>'fk_statut','type'=>'xsd:string'),
		'note' => array('name'=>'note','type'=>'xsd:string'),
		'note_public' => array('name'=>'note_public','type'=>'xsd:string'),
		'registration_open' => array('name'=>'registration_open','type'=>'xsd:string'),
		'registration_byday' => array('name'=>'registration_byday','type'=>'xsd:string'),
		'statuts_short' => array('name'=>'statuts_short','type'=>'xsd:string'),
		'statuts' => array('name'=>'statuts','type'=>'xsd:string'),
		'canvas' => array('name'=>'canvas','type'=>'xsd:string'),
		'eventdays' => array('name'=>'eventdays','type'=>'tns:EventdayArray')
    )
);

// Define other specific objects
$server->wsdl->addComplexType(
	'eventday',
	'complexType',
	'struct',
	'all',
	'',
	array(

	'id' => array('name'=>'id','type'=>'xsd:string'),
	'entity' => array('name'=>'entity','type'=>'xsd:string'),
	'ref' => array('name'=>'ref','type'=>'xsd:string'),
	'socid' => array('name'=>'socid','type'=>'xsd:string'),

	'date_event' => array('name'=>'date_event','type'=>'xsd:string'),
	'label' => array('name'=>'label','type'=>'xsd:string'),
	'description' => array('name'=>'description','type'=>'xsd:string'),

	'fk_statut' => array('name'=>'fk_statut','type'=>'xsd:string'),

	'note_public' => array('name'=>'note_public','type'=>'xsd:string'),
	'registration_open' => array('name'=>'registration_open','type'=>'xsd:string'),
	'eventday_levels' => array('name'=>'eventday_levels','type'=>'tns:EventdaylevelArray')
)
);

$registration_fields = array(

	'id' => array('name'=>'id','type'=>'xsd:string'),
	'entity' => array('name'=>'entity','type'=>'xsd:string'),
	'ref' => array('name'=>'ref','type'=>'xsd:string'),
	'fk_soc' => array('name'=>'fk_soc','type'=>'xsd:string'),

	'fk_event' => array('name'=>'fk_event','type'=>'xsd:string'),
	'fk_eventday' => array('name'=>'fk_eventday','type'=>'xsd:string'),
	'fk_levelday' => array('name'=>'fk_levelday','type'=>'xsd:string'),

	'datec' => array('name'=>'datec','type'=>'xsd:string'),
	'date_valid' => array('name'=>'date_valid','type'=>'xsd:string'),

	'paye' => array('name'=>'paye','type'=>'xsd:string'),

	'fk_user_create' => array('name'=>'fk_user_create','type'=>'xsd:string'),
	'fk_user_valid' => array('name'=>'fk_user_valid','type'=>'xsd:string'),
	'fk_user_registered' => array('name'=>'fk_user_registered','type'=>'xsd:string'),
		
	'lastname' => array('name'=>'lastname','type'=>'xsd:string'),
	'firstname' => array('name'=>'firstname','type'=>'xsd:string'),

	'fk_statut' => array('name'=>'fk_statut','type'=>'xsd:string'),
	'statuts_short' => array('name'=>'statuts_short','type'=>'xsd:string'),
	'note_private' => array('name'=>'note_private','type'=>'xsd:string'),
	'note_public' => array('name'=>'note_public','type'=>'xsd:string'),

	'event_label' => array('name'=>'event_label','type'=>'xsd:string'),
	'date_event' => array('name'=>'date_event','type'=>'xsd:string'),
	'level_label' => array('name'=>'level_label','type'=>'xsd:string')
);
//Retreive all extrafield for thirdsparty
// fetch optionals attributes and labels
$extrafields=new ExtraFields($db);
$extralabels=$extrafields->fetch_name_optionals_label('event_registration',true);
if (count($extralabels)>0)
{
	$extrafield_array = array();
	foreach($extrafields->attribute_label as $key=>$label)
	{
		$type =$extrafields->attribute_type[$key];
		if ($type=='date' || $type=='datetime') {$type='xsd:dateTime';}
		else {$type='xsd:string';}

		$extrafield_array['options_'.$key]=array('name'=>'options_'.$key,'type'=>$type);
	}
	$registration_fields=array_merge($registration_fields,$extrafield_array);
}

// Registration
$server->wsdl->addComplexType(
	'registration',
	'complexType',
	'struct',
	'all',
	'',
	$registration_fields
);

// Define other specific objects
$server->wsdl->addComplexType(
	'filterregistration',
	'complexType',
	'struct',
	'all',
	'',
	array(
	'fk_soc' => array('name'=>'fk_soc','type'=>'xsd:string'),
	'show_user_registration' => array('name'=>'show_user_registration','type'=>'xsd:string'),
	'show_user_registered' => array('name'=>'show_user_registered','type'=>'xsd:string'),
	)
);

// Define other specific objects
$server->wsdl->addComplexType(
	'eventday_level',
	'complexType',
	'struct',
	'all',
	'',
	array(

	'id' => array('name'=>'id','type'=>'xsd:string'),
	'label' => array('name'=>'label','type'=>'xsd:string'),
	'place_dispo' => array('name'=>'place_dispo','type'=>'xsd:string'),
	'full' => array('name'=>'full','type'=>'xsd:string'),
	'place_left' => array('name'=>'place_left','type'=>'xsd:string'),
	'statut_level' => array('name'=>'statut_level','type'=>'xsd:string'),
	'registered' => array('name'=>'registered','type'=>'xsd:string')
	)
);

/*
 * Les journées filles, sous tableau d'un évènement
*/
$server->wsdl->addComplexType(
	'EventdayArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
	array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:eventday[]')
	),
	'tns:eventday'
);

$server->wsdl->addComplexType(
	'EventsArray2',
	'complexType',
	'array',
	'sequence',
	'',
	array(
	'event' => array(
	'name' => 'event',
	'type' => 'tns:event',
	'minOccurs' => '0',
	'maxOccurs' => 'unbounded'
	)
	)
);

$server->wsdl->addComplexType(
	'RegistrationArray2',
	'complexType',
	'array',
	'sequence',
	'',
	array(
	'registration' => array(
	'name' => 'registration',
	'type' => 'tns:registration',
	'minOccurs' => '0',
	'maxOccurs' => 'unbounded'
	)
	)
);

/*
 * Tableau du paramétrage des groupe pour une journée
*/
$server->wsdl->addComplexType(
	'EventdaylevelArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
	array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:eventday_level[]')
	),
	'tns:eventday_level'
);

// Define WSDL Return object
$server->wsdl->addComplexType(
	'registration_pdf',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'filename' => array('name'=>'filename','type'=>'xsd:string'),
		'mimetype' => array('name'=>'mimetype','type'=>'xsd:string'),
		'content' => array('name'=>'content','type'=>'xsd:string'),
		'length' => array('name'=>'length','type'=>'xsd:string')
	)
);


// 5 styles: RPC/encoded, RPC/literal, Document/encoded (not WS-I compliant), Document/literal, Document/literal wrapped
// Style merely dictates how to translate a WSDL binding to a SOAP message. Nothing more. You can use either style with any programming model.
// http://www.ibm.com/developerworks/webservices/library/ws-whichwsdl/
$styledoc='rpc';       // rpc/document (document is an extend into SOAP 1.0 to support unstructured messages)
$styleuse='encoded';   // encoded/literal/literal wrapped
// Better choice is document/literal wrapped but literal wrapped not supported by nusoap.


// Register WSDL
$server->register(
    'getEventInfos',
    // Entry values
    array('authentication'=>'tns:authentication','id'=>'xsd:string','ref'=>'xsd:string','ref_ext'=>'xsd:string'),
    // Exit values
    array('result'=>'tns:result','event'=>'tns:event'),
    $ns,
    $ns.'#getEventInfos',
    $styledoc,
    $styleuse,
    'WS to get event'
);

$server->register(
	'getCompleteListOfEvents',
	// Entry values
	array('authentication'=>'tns:authentication'),
	// Exit values
	array('result'=>'tns:result','events'=>'tns:EventsArray2'),
	$ns,
	$ns.'#getCompleteListOfEvents',
	$styledoc,
	$styleuse,
	'WS to get all events'
);

$server->register(
    'createRegistration',
    // Entry values
    array('authentication'=>'tns:authentication','registration'=>'tns:registration'),
    // Exit values
    array('result'=>'tns:result','id'=>'xsd:string','ref'=>'xsd:string'),
    $ns,
    $ns.'#createRegistration',
    $styledoc,
    $styleuse,
    'WS to create a registration'
);

$server->register(
	'getRegistration',
	// Entry values
	array('authentication'=>'tns:authentication','id'=>'xsd:string','ref'=>'xsd:string','ref_ext'=>'xsd:string'),
	// Exit values
	array('result'=>'tns:result','registration'=>'tns:registration'),
	$ns,
	$ns.'#getRegistration',
	$styledoc,
	$styleuse,
	'WS to get registration'
);

$server->register(
	'getListOfRegistration',
	// Entry values
	array('authentication'=>'tns:authentication','filterregistration'=>'tns:filterregistration'),
	// Exit values
	array('result'=>'tns:result','registrations'=>'tns:RegistrationArray2'),
	$ns,
	$ns.'#getListOfRegistration',
	$styledoc,
	$styleuse,
	'WS to get list of all registration'
);

// Register WSDL
$server->register(
	'getRegistrationPdf',
	// Entry values
	array('authentication'=>'tns:authentication','ref'=>'xsd:string'),
	// Exit values
	array('result'=>'tns:result','registration_pdf'=>'tns:registration_pdf'),
	$ns,
	$ns.'#getRegistrationPdf',
	$styledoc,
	$styleuse,
	'WS to get registration pdf'
);

// Register WSDL
$server->register(
		'getOptionsForDay',
		// Entry values
		array('authentication'=>'tns:authentication','id'=>'xsd:string'),
		// Exit values
		array('result'=>'tns:result','dayoptions'=>'tns:DayOptionsArray'),
		$ns,
		$ns.'#getOptionsForDay',
		$styledoc,
		$styleuse,
		'WS to get list of all products or services for a category'
);


$server->wsdl->addComplexType(
		'dayoption',
		'complexType',
		'struct',
		'all',
		'',
		array(
				'id' => array('name'=>'id','type'=>'xsd:string'),
				'option_id' => array('name'=>'option_id','type'=>'xsd:string'),
				'ref' => array('name'=>'ref','type'=>'xsd:string'),
				'ref_ext' => array('name'=>'ref_ext','type'=>'xsd:string'),
				'type' => array('name'=>'type','type'=>'xsd:string'),
				'label' => array('name'=>'label','type'=>'xsd:string'),
				'description' => array('name'=>'description','type'=>'xsd:string'),
				'date_creation' => array('name'=>'date_creation','type'=>'xsd:dateTime'),
				'date_modification' => array('name'=>'date_modification','type'=>'xsd:dateTime'),
				'note' => array('name'=>'note','type'=>'xsd:string'),
				'status_tobuy' => array('name'=>'status_tobuy','type'=>'xsd:string'),
				'status_tosell' => array('name'=>'status_tosell','type'=>'xsd:string'),
				'barcode' => array('name'=>'barcode','type'=>'xsd:string'),
				'barcode_type' => array('name'=>'barcode_type','type'=>'xsd:string'),
				'country_id' => array('name'=>'country_id','type'=>'xsd:string'),
				'country_code' => array('name'=>'country_code','type'=>'xsd:string'),
				'customcode' => array('name'=>'customcode','type'=>'xsd:string'),

				'price_net' => array('name'=>'price_net','type'=>'xsd:string'),
				'price' => array('name'=>'price','type'=>'xsd:string'),
				'price_min_net' => array('name'=>'price_min_net','type'=>'xsd:string'),
				'price_min' => array('name'=>'price_min','type'=>'xsd:string'),

				'price_base_type' => array('name'=>'price_base_type','type'=>'xsd:string'),

				'price_increase' => array('name'=>'price_increase','type'=>'xsd:string'),

				'vat_rate' => array('name'=>'vat_rate','type'=>'xsd:string'),
				'vat_npr' => array('name'=>'vat_npr','type'=>'xsd:string'),
				'localtax1_tx' => array('name'=>'localtax1_tx','type'=>'xsd:string'),
				'localtax2_tx' => array('name'=>'localtax2_tx','type'=>'xsd:string'),

				'stock_alert' => array('name'=>'stock_alert','type'=>'xsd:string'),
				'stock_real' => array('name'=>'stock_real','type'=>'xsd:string'),
				'stock_pmp' => array('name'=>'stock_pmp','type'=>'xsd:string'),
				'canvas' => array('name'=>'canvas','type'=>'xsd:string'),
				'import_key' => array('name'=>'import_key','type'=>'xsd:string'),

				'dir' => array('name'=>'dir','type'=>'xsd:string'),
				'images' => array('name'=>'images','type'=>'tns:ImagesArray'),
		)
);


$server->wsdl->addComplexType(
		'DayOptionsArray',
		'complexType',
		'array',
		'sequence',
		'',
		array(
				'product' => array(
						'name' => 'dayoption',
						'type' => 'tns:dayoption',
						'minOccurs' => '0',
						'maxOccurs' => 'unbounded'
				)
		)
);


/*
 * Image of product
*/
$server->wsdl->addComplexType(
		'ImagesArray',
		'complexType',
		'array',
		'sequence',
		'',
		array(
				'image' => array(
						'name' => 'image',
						'type' => 'tns:image',
						'minOccurs' => '0',
						'maxOccurs' => 'unbounded'
				)
		)
);

/*
 * An image
*/
$server->wsdl->addComplexType(
		'image',
		'complexType',
		'struct',
		'all',
		'',
		array(
				'photo' => array('name'=>'photo','type'=>'xsd:string'),
				'photo_vignette' => array('name'=>'photo_vignette','type'=>'xsd:string'),
				'imgWidth' => array('name'=>'imgWidth','type'=>'xsd:string'),
				'imgHeight' => array('name'=>'imgHeight','type'=>'xsd:string')
		)
);





/**
 * Get Event
 *
 * @param	array		$authentication		Array of authentication information
 * @param	int			$id					Id of object
 * @param	string		$ref				Ref of object
 * @param	ref_ext		$ref_ext			Ref external of object
 * @return	mixed
 */
function getEventInfos($authentication,$id,$ref='',$ref_ext='')
{
    global $db,$conf,$langs;

    dol_syslog("Function: getEvent login=".$authentication['login']." id=".$id." ref=".$ref." ref_ext=".$ref_ext);

    if ($authentication['entity']) $conf->entity=$authentication['entity'];

    // Init and check authentication
    $objectresp=array();
    $errorcode='';$errorlabel='';
    $error=0;
    $fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
    // Check parameters
    if (! $error && (($id && $ref) || ($id && $ref_ext) || ($ref && $ref_ext)))
    {
        $error++;
        $errorcode='BAD_PARAMETERS'; $errorlabel="Parameter id, ref and ref_ext can't be both provided. You must choose one or other but not both.";
    }

    if (! $error)
    {
        $fuser->getrights();

        if ($fuser->rights->event->read)
        {
            $event=new Event($db);
            $result=$event->fetch($id,$ref,$ref_ext);
            if ($result > 0)
            {

            	$eventret = array(

						'id' => $event->id,
						'entity' => $event->entity,
						'ref' => $event->ref,
						'socid' => $event->socid,
						'datec' => $event->datec,
						'tms' => $event->tms,
						'date_start' => $event->date_start,
						'date_end' => $event->date_end,
						'label' => $event->label,
						'description' => $event->description,
						'price_day' => $event->price_day,
						'total_ht' => $event->total_ht,
						'total_tva' => $event->total_tva,
						'total_ttc' => $event->total_ttc,
						'tva_tx' => $event->tva_tx,
						'accountancy_code' => $event->accountancy_code,
						'fk_user_create' => $event->fk_user_create,
						'fk_statut' => $event->fk_statut,
						'note' => $event->note,
						'note_public' => $event->note_public,
						'registration_open' => $event->registration_open,
						'registration_byday' => $event->registration_byday,
						'statuts_short' => $event->statuts_short,
						'statuts' => $event->statuts,
						'canvas' => $event->canvas
                    );

	            	$eventdays = $event->get_days();

	            	if (sizeof ($eventdays) > 0)
	            	{
	            		$i=0;
	            		foreach($eventdays as $eventday)
	            		{

	            			$eventdaystat = new Day($db);

	            			$eventret['eventdays'][$i] = array(
		            			'id'=>$eventday->id,
		            			'entity'=>$eventday->entity,
		            			'ref'=>$eventday->ref,
		            			'socid'=>$eventday->socid,
		            			'date_event'=>$eventday->date_event,
		            			'label'=>$eventday->label,
		            			'description'=>$eventday->description,
		            			'fk_statut' => $eventday->fk_statut,

		            			'note_public'=>$eventday->note_public,
		            			'registration_open'=>$eventday->registration_open
	            			);

	            			$levels = $eventdaystat->LoadLevelForDay($eventday->id);

	            			foreach($levels as $level) {

	            				$eventret['eventdays'][$i]['eventday_levels'][] = array(
	            					'id' => $level['id'],
	            					'label' => $level['label'],
	            					'place_dispo' => $level['place_dispo'],
	            					'full' => $level['full'],
	            					'place_left' => $level['place_left'],
	            					'statut_level' => $level['statut_level']
	            				);

	            			}
	            			$i++;
	            		}
	            	}


                // Create
                $objectresp = array(
			    	'result'=>array('result_code'=>'OK', 'result_label'=>''),
			        'event'=> $eventret
                );
            }
            else
            {
                $error++;
                $errorcode='NOT_FOUND'; $errorlabel='Object not found for id='.$id.' nor ref='.$ref.' nor ref_ext='.$ref_ext;
            }
        }
        else
        {
            $error++;
            $errorcode='PERMISSION_DENIED'; $errorlabel='User does not have permission for this request';
        }
    }

    if ($error)
    {
        $objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
    }

    return $objectresp;
}

/**
 * Get list of events
 *
 * @param	array		$authentication		Array of authentication information
 * @return	array							Array result
 */
function getCompleteListOfEvents($authentication)
{
	global $db,$conf,$langs;

	dol_syslog("Function: getCompleteListOfEvents login=".$authentication['login']." idthirdparty=".$idthirdparty);

	if ($authentication['entity']) $conf->entity=$authentication['entity'];

	// Init and check authentication
	$objectresp=array();
	$errorcode='';$errorlabel='';
	$error=0;
	$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);

	if (! $error)
	{
		$linesinvoice=array();

		$sql.='SELECT e.rowid as eventid, ref';
		$sql.=' FROM '.MAIN_DB_PREFIX.'event as e';
		$sql.=" WHERE e.entity = ".$conf->entity;
		$sql.=" AND DATE (e.date_start) > NOW()";
		$sql.=" OR DATE (e.date_end) > NOW()";

		$resql=$db->query($sql);
		if ($resql)
		{
			$num=$db->num_rows($resql);
			$i=0;
			while ($i < $num)
			{
				// En attendant remplissage par boucle
				$obj=$db->fetch_object($resql);

				$event=new Event($db);
				$event->fetch($obj->eventid);


				$eventret = array(
					'id' => $event->id,
					'entity' => $event->entity,
					'ref' => $event->ref,
					'socid' => $event->socid,

					'date_start' => $event->date_start,
					'date_end' => $event->date_end,
					'label' => $event->label,
					'description' => $event->description,



					'fk_statut' => $event->fk_statut,

					'note_public' => $event->note_public,
					'registration_open' => $event->registration_open,
					'registration_byday' => $event->registration_byday,

				);

				$eventdays = $event->get_days();
				if (sizeof ($eventdays) > 0)
				{

					$eventdaystat = new Day($db);
					$j=0;
					foreach($eventdays as $eventday)
					{
						$eventret['eventdays'][$j] = array(
						'id'=>$eventday->id,
						'entity'=>$eventday->entity,
						'ref'=>$eventday->ref,
						'socid'=>$eventday->socid,
						'date_event'=>$eventday->date_event,
						'label'=>$eventday->label,
						'description'=>$eventday->description,
						'fk_statut' => $eventday->fk_statut,

						'note_public'=>$eventday->note_public,
						'registration_open'=>$eventday->registration_open
						);

						$levels = $eventdaystat->LoadLevelForDay($eventday->id);

						foreach($levels as $level) {

							$eventret['eventdays'][$j]['eventday_levels'][] = array(
							'id' => $level['id'],
							'label' => $level['label'],
							'place_dispo' => $level['place_dispo'],
							'full' => $level['full'],
							'place_left' => $level['place_left'],
							'registered' => $level['registered'],
							'rang' => $level['rang'],
							'statut_level' => $level['statut_level']
							);

						}
						$j++;
					}

				}

				// Now define invoice
				$linesevent[]=$eventret;

				$i++;
			}

			$objectresp=array(
			'result'=>array('result_code'=>'OK', 'result_label'=>''),
			'events'=>$linesevent

			);
		}
		else
		{
			$error++;
			$errorcode=$db->lasterrno(); $errorlabel=$db->lasterror();
		}
	}

	if ($error)
	{
		$objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
	}

	return $objectresp;
}


/**
 * Create an registration
 *
 * @param	array		$authentication		Array of authentication information
 * @param	Facture		$invoice			Registration
 * @return	array							Array result
 */
function createRegistration($authentication,$registration)
{
	global $db,$conf,$langs;

	$now=dol_now();

	dol_syslog("Function: createRegistration login=".$authentication['login']);

	if ($authentication['entity']) $conf->entity=$authentication['entity'];

	// Init and check authentication
	$objectresp=array();
	$errorcode='';$errorlabel='';
	$error=0;
	$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);

	if (! $error)
	{
		$newobject=new Registration($db);
		$newobject->fk_soc=$registration['fk_soc'];
		$newobject->fk_event=$registration['fk_event'];
		$newobject->fk_eventday=$registration['fk_eventday'];
		$newobject->fk_levelday=$registration['fk_levelday'];

		$newobject->fk_user_create=$registration['fk_user_create'];
		$newobject->fk_user_valid=$registration['fk_user_valid'];
		$newobject->fk_user_registered=$registration['fk_user_registered'];
		if($registration['fk_user_registered'] == '-1')
		$newobject->fk_user_registered=$fuser->id;

		$newobject->civilite=$registration['civilite'];
		$newobject->firstname=$registration['firstname'];
		$newobject->naiss=$registration['naiss'];
		$newobject->lastname=$registration['lastname'];
		$newobject->address=$registration['address'];
		$newobject->zip=$registration['zip'];
		$newobject->town=$registration['town'];
		$newobject->state_id=$registration['state_id'];
		$newobject->country_id=$registration['country_id'];
		$newobject->phone=$registration['phone'];
		$newobject->phone_perso=$registration['phone_perso'];
		$newobject->phone_mobile=$registration['phone_mobile'];
		$newobject->email_registration=$registration['email_registration'];
		$newobject->note_private=$registration['note_private'];
		$newobject->note_public=$registration['note_public'];


		$newobject->datec=$now;

		// Retrieve all extrafield for registration
		// fetch optionals attributes and labels
		$extrafields=new ExtraFields($db);
		$extralabels=$extrafields->fetch_name_optionals_label('event_registration',true);
		foreach($extrafields->attribute_label as $key=>$label)
		{
			$key='options_'.$key;
			$newobject->array_options[$key]=$registration[$key];
		}

		$db->begin();

		$result=$newobject->create($fuser);

		if ($result < 0)
		{
			$error++;
		}

		if ($registration['fk_statut'] == 1)   // We want registration validated
		{
			$result=$newobject->setValid($fuser);
			if ($result < 0)
			{
				$error++;
			}
			else {
				// Génération du PDF
				require_once("../core/modules/event/modules_event.php");
				require_once("../core/modules/registration/modules_registration.php");
				$newobject->fetch($newobject->id);
				$langs->load('event@event');
				$result=event_pdf_create($db, $newobject, 'registration', $langs);
				if ($result <= 0)
				{
					$error++;

				}

				// Send PDF

				require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
				require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');
				$contactforaction=new Contact($newobject->db);
				$societeforaction=new Societe($newobject->db);
				$contact_create = new Contact($newobject->db);
				$contact_registered = new Contact($newobject->db);
				if ($newobject->fk_soc > 0)    $societeforaction->fetch($newobject->fk_soc);

				if($newobject->fk_user_create > 0)	$contact_create->fetch($newobject->fk_user_create);
				if($newobject->fk_user_registered > 0)	$contact_registered->fetch($newobject->fk_user_registered);
				$sendtoid = 0;

				// Si c'est un user externe qui a fait l'inscription d'un invité on prend ses infos
				if(
				$contact_create->societe_id > 0
				AND ($newobject->fk_user_create != $newobject->fk_user_registered)
				){
					$sendto = $contact_create->email;
					$sendto_sms = $contact_create->phone_mobile;
					$sendtoid = $contact_create->id;
				}
				else // Dans les autres cas on prend les infos du participant
				{
					$sendto = $contact_registered->email;
					$sendto_sms = $contact_registered->phone_mobile;
					$sendtoid = $contact_registered->id;
				}

				$langs->load('event@event');
				$sujet=$langs->transnoentities('SendValidRegistration',$newobject->getValueFrom('event', $newobject->fk_event, 'label'));
				$message= $langs->transnoentities('EventHello');
				$message.= "\n\n";
				$message .= $langs->transnoentities('SendValidRegistrationBody',dol_print_date($newobject->getValueFrom('event_day', $newobject->fk_eventday, 'date_event'),'daytext'),$newobject->getValueFrom('event', $newobject->fk_event, 'label'));
				// Add sign
				// $message.= "\n\n--\n";
				$message.= $conf->global->EVENT_REGISTRATION_SIGN_EMAIL;

				$now=dol_now();

				$message = dol_nl2br($message,0,1);
				$result = $newobject->SendByEmail('', $sendto,$sendtoid,$sujet,$message);

				if($result) {
					dol_syslog('Envoi du mail de validation : OK');
				}
				else {
					$error++;
					dol_syslog("Echec de l'envoi du mail de validation : ".$newobject->error, LOG_ERR);
				}
			}

		}

		if (! $error)
		{
			$db->commit();
			$objectresp=array('result'=>array('result_code'=>'OK', 'result_label'=>''),'id'=>$newobject->id,'ref'=>$newobject->ref);
		}
		else
		{
			$db->rollback();
			$error++;
			$errorcode='KO';
			$errorlabel=$newobject->error;
		}

	}

	if ($error)
	{
		$objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
	}

	return $objectresp;
}


/**
 * Get Registration
 *
 * @param	array		$authentication		Array of authentication information
 * @param	int			$id					Id of object
 * @param	string		$ref				Ref of object
 * @param	ref_ext		$ref_ext			Ref external of object
 * @return	mixed
 */
function getRegistration($authentication,$id,$ref='')
{
	global $db,$conf,$langs;

	dol_syslog("Function: getRegistration login=".$authentication['login']." id=".$id." ref=".$ref." ref_ext=".$ref_ext);

	if ($authentication['entity']) $conf->entity=$authentication['entity'];

	// Init and check authentication
	$objectresp=array();
	$errorcode='';$errorlabel='';
	$error=0;
	$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
	// Check parameters
	if (! $error && (!$id && !$ref) || ($id && $ref))
	{
		$error++;
		$errorcode='BAD_PARAMETERS'; $errorlabel="Parameter id, ref can't be both provided. You must choose one or other but not both.";
	}

	if (! $error)
	{
		$fuser->getrights();

		if ($fuser->rights->event->read)
		{
			$object=new Registration($db);
			$result=$object->fetch($id,$ref);
			if ($object->id > 0)
			{
				
				$contactstat = new Contact($db);
				$contactstat->fetch($object->fk_user_registered);

				$objectret = array(

					'id' => $object->id,
					'entity' => $object->entity,
					'ref' => $object->ref,
					'fk_soc' => $object->fk_soc,
					'datec' => $object->datec,
					'tms' => $object->tms,
					'date_valid' => $object->date_valid,
					'paye' => $object->paye,
					'total_ht' => $object->total_ht,
					'total_tva' => $object->total_tva,
					'total_ttc' => $object->total_ttc,
					'tva_tx' => $object->tva_tx,
					'accountancy_code' => $object->accountancy_code,
					'fk_user_create' => $object->fk_user_create,
					'fk_user_valid' => $object->fk_user_valid,
					'fk_user_registered' => $object->fk_user_registered,
					'fk_statut' => $object->fk_statut,
					'fk_event' => $object->fk_event,
					'fk_eventday' => $object->fk_eventday,
					'fk_levelday' => $object->fk_levelday,
					'note_public' => $object->note_public,
					'lastname'	=> $contactstat->lastname,
					'fistname'	=> $contactstat->firstname,
					'statuts_short' => $object->getLibStatut(1)
				);

				if($object->fk_event > 0 )
				{
					$eventstat = new Event($db);
					$eventstat->fetch($object->fk_event);
					$objectret['event_label'] = $eventstat->label;

				}
				if($object->fk_eventday > 0 )
				{
					$eventdaystat = new Day($db);
					$eventdaystat->fetch($object->fk_eventday);
					$objectret['date_event'] = $eventdaystat->date_event;
				}
				if($object->fk_levelday > 0 )
				{
					$levelstat = new Eventlevel($db);
					$levelstat->fetch($object->fk_eventday);
					$objectret['level_label'] = $levelstat->label;
				}


				//Retreive all extrafield for thirdsparty
				// fetch optionals attributes and labels
				$extrafields=new ExtraFields($db);
				$extralabels=$extrafields->fetch_name_optionals_label('event_registration',true);
				//Get extrafield values
				$object->fetch_optionals($object->id,$extralabels);

				foreach($extrafields->attribute_label as $key=>$label)
				{
					$objectret=array_merge($objectret,array('options_'.$key => $object->array_options['options_'.$key]));
				}

				// Create
				$objectresp = array(
					'result'=>array('result_code'=>'OK', 'result_label'=>''),
					'registration'=>$objectret
				);
			}
			else
			{
				$error++;
				$errorcode='NOT_FOUND'; $errorlabel='Object not found for id='.$id.' nor ref='.$ref.' nor ref_ext='.$ref_ext;
			}
		}
		else
		{
			$error++;
			$errorcode='PERMISSION_DENIED'; $errorlabel='User does not have permission for this request';
		}
	}

	if ($error)
	{
		$objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
	}
	return $objectresp;
}


/**
 * getListOfRegistration
 *
 * @param	array		$authentication		Array of authentication information
 * @param	array		$filterregistration	Filter fields
 * @return	array							Array result
 */
function getListOfRegistration($authentication,$filterregistration)
{
	global $db,$conf,$langs;

	$now=dol_now();

	dol_syslog("Function: getListOfRegistration login=".$authentication['login']);

	if ($authentication['entity']) $conf->entity=$authentication['entity'];

	// Init and check authentication
	$objectresp=array();
	$arrayregistrations=array();
	$errorcode='';$errorlabel='';
	$error=0;
	$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
	// Check parameters
	$fuser->getrights();

	if (! $error)
	{
		$sql ="SELECT r.rowid, r.ref, r.fk_statut, r.datec, r.paye, r.fk_user_registered, r.fk_user_create, r.fk_levelday";
		$sql.=", e.label, ed.rowid as fk_eventday, ed.date_event, ed.fk_event";
		$sql.=", s.firstname, s.lastname";
		$sql.=", el.label as level_label";
		$sql.=" FROM ".MAIN_DB_PREFIX."event_registration as r";
		$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."event as e ON e.rowid=r.fk_event";
		$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."event_day as ed ON ed.rowid=r.fk_eventday";
		$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."event_level as el ON el.rowid=r.fk_levelday";
		$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."socpeople as s ON s.rowid=r.fk_user_registered";
		$sql.=" WHERE r.entity=".$conf->entity;

		foreach($filterregistration as $key => $val)
		{
			if ($key == 'fk_soc' && $val > 0)							$sql.=" AND r.fk_soc = ".$db->escape($val);
			if ($key == 'show_user_registration'&& $val > 0) 			$sql.=" AND r.fk_user_create = ".$db->escape($fuser->id);
			if ($key == 'show_user_registered' && $val > 0)  			$sql.=" OR r.fk_user_registered = ".$db->escape($val);
		}
		$resql=$db->query($sql);
		if ($resql)
		{
			$num=$db->num_rows($resql);
			dol_syslog('serer_event::getListOfRegistration sql='.$sql);
			$i=0;
			while ($i < $num)
			{
				$obj=$db->fetch_object($resql);
				$arrayregistrations[]=array(
					'id'=>$obj->rowid,
					'ref'=>$obj->ref,
					'fk_statut'=>$obj->fk_statut,
					'fk_user_registered' => $obj->fk_user_registered, 
					'datec'=>$obj->datec,
					'date_event'=>$obj->date_event,
					'fk_event'=>$obj->fk_event,
					'fk_eventday'=>$obj->fk_eventday,
					'paye'=>$obj->paye,
					'event_label'=>$obj->label,
					'firstname'=>$obj->firstname,
					'lastname'=>$obj->lastname,
					'fk_levelday'=>$obj->fk_levelday,
					'level_label'=>$obj->level_label
				);
				$i++;
			}

		}
		else
		{
			$error++;
			$errorcode=$db->lasterrno();
			$errorlabel=$db->lasterror();
		}
	}

	if ($error)
	{
		$objectresp = array(
		'result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel),
		'registrations'=>$arrayregistrations
		);
	}
	else
	{
		$objectresp = array(
		'result'=>array('result_code' => 'OK', 'result_label' => ''),
		'registrations'=>$arrayregistrations
		);
	}

	return $objectresp;
}


/**
 * Get Registration PDF
 *
 * @param	array		$authentication		Array of authentication information
 * @param	string		$ref				Ref of object
 * @return	mixed
 */
function getRegistrationPdf($authentication,$ref)
{
	global $db,$conf,$langs;

	dol_syslog("Function: getRegistrationPdf login=".$authentication['login']." ref=".$ref);

	if ($authentication['entity']) $conf->entity=$authentication['entity'];

	// Init and check authentication
	$objectresp=array();
	$errorcode='';$errorlabel='';
	$error=0;
	$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
	// Check parameters
	if (! $error && ( !$ref))
	{
		$error++;
		$errorcode='BAD_PARAMETERS'; $errorlabel="Parameter ref must be both provided. ";
	}

	if (! $error)
	{
		$fuser->getrights();

		if ($fuser->rights->event->read)
		{
			$object=new Registration($db);
			$result=$object->fetch($id,$ref);
			if ($object->id > 0)
			{

				include_once(DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php');
				$ref = dol_sanitizeFileName($object->ref);

				// Génération du PDF si non existant
				require_once("../core/modules/event/modules_event.php");
				require_once("../core/modules/registration/modules_registration.php");
				$langs->load("event@event");
				$result=event_pdf_create($db, $object, 'registration', $langs);

				$fileparams = dol_most_recent_file($conf->event->dir_output . '/' . $ref,'','',1);
				if(is_array($fileparams))
				{
					$file=$fileparams['fullname'];
					$filename = basename($file);


					$f = fopen($file,'r');
					$content_file = fread($f,filesize($file));
					dol_syslog("Function: getRegistrationPdf $original_file $filename content-type=$type");



					$objectret = array(
						'filename' => basename($file),
						'mimetype' => dol_mimetype($file),
						'content' => base64_encode($content_file),
						'length' => filesize($file)
					);

					// Create
					$objectresp = array(
					'result'=>array('result_code'=>'OK', 'result_label'=>''),
					'registration_pdf'=>$objectret
					);

				}
				else {
					$error++;
					$errorcode='NOT_FOUND'; $errorlabel='Object not listed for ref='.$ref;
				}

			}
			else
			{
				$error++;
				$errorcode='NOT_FOUND'; $errorlabel='Object not found for id='.$id.' nor ref='.$ref.' nor ref_ext='.$ref_ext;
			}
		}
		else
		{
			$error++;
			$errorcode='PERMISSION_DENIED'; $errorlabel='User does not have permission for this request';
		}
	}

	if ($error)
	{
		$objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
	}
	return $objectresp;
}



/**
 *   Return options infos for an event day
 *   
 */
function getOptionsForDay($authentication,$id)
{
	global $db,$conf,$langs;

	dol_syslog("Function: getOptionsForDay login=".$authentication['login']." id=".$id);

	if ($authentication['entity']) $conf->entity=$authentication['entity'];

	$objectresp=array();
	$errorcode='';$errorlabel='';
	$error=0;

	$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);

	if (! $error && !$id)
	{
		$error++;
		$errorcode='BAD_PARAMETERS'; $errorlabel="Parameter id must be provided.";
	}

	if (! $error)
	{
		$fuser->getrights();

		if ($fuser->rights->produit->lire)
		{
			
			
			$prod=new Eventoptions($db);
			$result=$prod->fetch($id);
			if ($result > 0)
			{
				//$prod->getOptionnalsArbo();
				// List of subproducts
				$prods_arbo = $prod->getProdOptionsForDay();
					
				if (is_array($prods_arbo) && count($prods_arbo) > 0)
				{
					foreach($prods_arbo as $value)
					{
						$obj = new Product($db);
						$obj->fetch($value['id']);
						if($obj->status > 0 )
						{
							$dir = (!empty($conf->product->dir_output)?$conf->product->dir_output:$conf->service->dir_output);
							$pdir = get_exdir($obj->id,2) . $obj->id ."/photos/";
							$dir = $dir . '/'. $pdir;

							$products[] = array(
									'id' => $obj->id,
									'option_id' => $value['option_id'],
									'ref' => $obj->ref,
									'ref_ext' => $obj->ref_ext,
									'label' => $obj->label,
									'description' => $obj->description,
									'date_creation' => dol_print_date($obj->date_creation,'dayhourrfc'),
									'date_modification' => dol_print_date($obj->date_modification,'dayhourrfc'),
									'note' => $obj->note,
									'status_tosell' => $obj->status,
									'status_tobuy' => $obj->status_buy,
									'type' => $obj->type,
									'barcode' => $obj->barcode,
									'barcode_type' => $obj->barcode_type,
									'country_id' => $obj->country_id>0?$obj->country_id:'',
									'country_code' => $obj->country_code,
									'custom_code' => $obj->customcode,

									'price_net' => $obj->price,
									'price' => $obj->price_ttc,
									'vat_rate' => $obj->tva_tx,
										
									'price_increase' => $value['price_increase'] > 0 ? $value['price_increase'] : "0",

									'price_base_type' => $obj->price_base_type,

									'stock_real' => $obj->stock_reel,
									'stock_alert' => $obj->seuil_stock_alerte,
									'pmp' => $obj->pmp,
									'import_key' => $obj->import_key,
									'dir' => $pdir,
									'images' => $obj->liste_photos($dir,$nbmax=10)
							);
						}
					}

					// Retour
					$objectresp = array(
							'result'=>array('result_code'=>'OK', 'result_label'=>''),
							'dayoptions'=> $products
					);
				}
				else
				{
					$errorcode='NORECORDS_FOR_ASSOCIATION'; $errorlabel='No products associated'.$sql;
					$objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
					dol_syslog("getOptionsForDay:: ".$c->error, LOG_DEBUG);
				}
			}
			else
			{
				$error++;
				$errorcode='NOT_FOUND'; $errorlabel='Object not found for id='.$id;
			}
		}
		else
		{
			$error++;
			$errorcode='PERMISSION_DENIED'; $errorlabel='User does not have permission for this request';
		}
	}

	if ($error)
	{
		$objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
	}

	return $objectresp;
}


// Return the results.
$server->service($HTTP_RAW_POST_DATA);

?>
