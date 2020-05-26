<?PHP
/* Copyright (C) 2005-2011 Laurent Destailleur <eldy@users.sourceforge.net>
* Copyright (C) 2012	  JF FERRY <jfefe@aternatik.fr>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *       \file       event/lib/html.formregistration.class.php
 *       \ingroup    core
 *       \brief      Fichier de la classe permettant la generation des formulaires html du module event
 */
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT ."/core/class/html.formcompany.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/extrafields.class.php");
require_once("../class/event.class.php");
require_once("../class/day.class.php");
require_once("../class/eventlevel.class.php");
require_once("../class/html.formevent.class.php");
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';



/** 	\class FormRegistration
* 		\brief Classe permettant la generation du formulaire d'une nouvelle inscription
* 		\remarks Utilisation: $formregister = new FormRegistration($db)
* 		\remarks $formregister->proprietes=1 ou chaine ou tableau de valeurs
* 		\remarks $formregister->show_form() affiche le formulaire
*/
class FormRegistration
{
    var $db;

    var $fk_event;
    var $fk_eventday;
    var $fk_level;
    var $fk_soc;
    var $fk_user_create;
    var $fk_user_registered;
    var $fk_user_valid;
    var $naiss;
    var $message;
	var $backtopage;
	var $select_tag;

	var $action;

	var $witheventday;
	var $withusercreate;
	var $withfromsocid; // affiche liste déroulante
	var $withlevel;
	var $registrationbyday;
	var $allowedregistration;


	var $withcancel;

    var $substit=array();
    var $param=array();

    var $error;


    /**
    * Constructor
    *
    * @param DoliDB $DB Database handler
    */
    function __construct($DB)
    {
        $this->db = $DB;

        $this->action = 'add';
        $this->witheventday=0;
        $this->withusercreate=0;
        $this->withfromsocid=0;
        $this->withlevel=1;

		$this->withusercreate=1;


		$this->ref = 0;



        return 1;
    }

    /**
    * Show the form to create a new registration
    *
    * @param string $width Width of form
    * @return void
    */
    function show_form($width='120px')
    {
        global $conf, $langs, $user;

        $langs->load("other");
        $langs->load("mails");
        $langs->load("event@event");

        $form=new Form($this->db);
        $formcompany = new FormCompany($this->db);
        $formevent = new FormEvent($this->db);

        $eventstat=new Event($this->db);
        $eventdaystat=new Day($this->db);
        $registration = new Registration($this->db);


        $soc=new Societe($this->db);

        $extrafields = new ExtraFields($this->db);
        $extrafields_contact = new ExtraFields($this->db);

        // Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
        include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
        $hookmanager=new HookManager($db);
        $hookmanager->initHooks(array('eventregistration'));

        // fetch optionals attributes and labels
        $extralabels=$extrafields->fetch_name_optionals_label('event_registration');
        $extralabels_contact=$extrafields_contact->fetch_name_optionals_label('socpeople');

        print "\n<!-- Begin form REGISTRATION -->\n";

        $this->fk_departement = $_POST["departement_id"];

        // We set country_id, country_code and country for the selected country
        $this->country_id=GETPOST('country_id','int')?GETPOST('country_id','int'):$mysoc->country_id;
        if ($this->country_id)
        {
        	$tmparray=getCountry($this->country_id,'all');
        	$this->pays_code=$tmparray['code'];
        	$this->pays=$tmparray['code'];
        	$this->country_code=$tmparray['code'];
        	$this->country=$tmparray['label'];
        }


        if ($conf->use_javascript_ajax)
        {
        	print "\n".'<script type="text/javascript" language="javascript">';
        	print 'jQuery(document).ready(function () {
        	jQuery("#fk_event, #selectcountry_id, #fk_soc, #fk_user_registered").change(function() {
	        	document.registration.action.value="'.$this->action.'";
	        	document.registration.submit();
        		});

	        	$(".creating_tirdparty").hide();
	        	$("#create_thirdparty_confirm").click(function() {
                   	$(".creating_tirdparty").show();
                });
	        	$("#create_thirdparty_cancel").click(function() {
                   	$(".creating_tirdparty").hide();
                });

        		$(".creating_contact").hide();
	        	$("#create_contact_confirm").click(function() {
                   	$(".creating_contact").show();
                });
	        	$("#create_contact_cancel").click(function() {
                   	$(".creating_contact").hide();
                });
	        ';

        	// If selected, show fields
        	if(GETPOST('create_thirdparty','int') == "1"){
        		print '$(".creating_tirdparty").show();';
        	}
        	if(GETPOST('create_contact','int') == "1"){
        		print '$(".creating_contact").show();';
        	}


        	print '});';
        	print '</script>'."\n";
        }

        print "<form method=\"POST\" name=\"registration\" enctype=\"multipart/form-data\" action=\"".$this->param["returnurl"]."\">\n";
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="'.$this->action.'">';
		print '<input type="hidden" name="fk_eventday" value="'.$this->fk_eventday.'">';
        foreach ($this->param as $key=>$value)
        {
            print "<input type=\"hidden\" name=\"$key\" value=\"$value\">\n";
        }
        print "<table class=\"border\" width=\"100%\">\n";

        print '<tr class="liste_titre liste_titre_napf">';
        print '<td class="liste_titre" colspan="4"><strong>';
        print $langs->trans('RegistrationInfos');
        print '</strong></td>';
        print '</tr>';

		// FK_USER_CREATE
		if ($this->fk_user_create > 0)
		{
			print '<input type="hidden" name="fk_user_create" value="'.$this->fk_user_create.'">';

			print "<tr><td width=\"".$width."\">".$langs->trans("RegistrationFrom")."</td><td colspan='3'>";
			$langs->load("users");
			$fuser=new User($this->db);
			$fuser->fetch($this->fk_user_create);
			print $fuser->getNomUrl(1);
			print ' &nbsp; ';
			print "</td></tr>\n";

		}

        // Event Infos
        if ($this->withevent)
        {
        	print '<tr><td><span class="fieldrequired">'.$langs->trans('Event').'</span></td>';
        	if ($this->fk_event > 0)
        	{
        		$eventstat->fetch($this->fk_event);
        		$this->registration_byday=$eventstat->registration_byday;
        		print '<td colspan="3">'.$eventstat->getNomUrl(4).' '.$eventstat->label ;
        		print ' &nbsp; ';
        		print '<a href="'.$_SERVER['PHP_SELF'].'?action=create">'.img_picto($langs->trans('ChooseAnotherEvent'), 'edit').'</a>';
        		print '</td>';
        	}
        	else
        	{
        		print '<td colspan="3">';
        		print $formevent->select_event(GETPOST('fk_event','int'), 'fk_event','intitule',1);
        		print '</td>';
        	}
        	print '</tr>';
        }


        // Day of event
        if ($this->witheventday)
        {
        	print '<tr>';
        	print '<td width="25%;">'.$langs->trans('EventDay').'</td>';
        	if ($this->fk_eventday > 0)
        	{
        		$eventdaystat->fetch($this->fk_eventday);
        		print '<td  width="25%;"><a href="'.DOL_URL_ROOT.'/custom/event/day/fiche.php?id='.$this->fk_eventday.'"><img src="'.DOL_URL_ROOT.'/custom/event/img/object_day.png" border="0" alt="" title="Voir la journée: '.$eventdaystat->ref.' - '.$eventdaystat->label.'"> '.$eventdaystat->label.'</a>';
        		print ' &nbsp; ';
        		print '<a href="'.$_SERVER['PHP_SELF'].'?action=create">'.img_picto($langs->trans('ChooseAnotherDay'), 'edit').'</a></td>';
        		// Date of eventday
        		print '<td width="25%;">'.$langs->trans('EventDayDate').'</td>';
        		print '<td width="25%;">'.dol_print_date($this->datec,'daytext').'</td>';
        	}
        	else
        	{
        		print '<td colspan="3">';
        		print $this->select_day(GETPOST('fk_event','int'));
        		print '</td>';
        	}
	        print '</tr>';
        }
        if ($conf->global->EVENT_HIDE_GROUP=='-1' && GETPOST('dayid') > 0 && $eventstat->registration_byday)
        {
			print '<tr>';
			print '<td><span class="fieldrequired">'.$langs->trans("EventLevel").'</span></td>';
			print '<td colspan="3"><div>';
			print $this->show_select_level_for_day(0,1);
			print '</div></td>';
			print '</tr>';
        }

        // REF
        if($this->ref)
        	print '<tr><td><span class="fieldrequired">'.$langs->trans("Ref").'</span></td><td><input size="12" type="text" name="ref" value="'.$this->ref.'"></td></tr>';
		if($this->allowedregistration)
		{
			/*
			 * Ask if validation wanted after creation
			*/
			print '<tr><td colspan="">'.$langs->trans('ValidAfterCreation');
			// print img_picto($langs->transnoentities("ValidRegistrationAfterCreationInfo"),'help');
			print '</td>';
			print '<td colspan="3">';
			if (GETPOST('registration_valid_after_create','int')=='1') {
				$checked2='';
				$checked1='checked="checked"';
				$checked0='';
			}elseif(GETPOST('registration_valid_after_create','int')=='-1'){
				$checked2='';
				$checked1='';
				$checked0='checked="checked"';
			}elseif(GETPOST('registration_valid_after_create','int')=='2'){
				$checked2='checked="checked"';
				$checked1='';
				$checked0='';
			}elseif($conf->global->REGISTRATION_VALID_AFTER_CREATE=='1') {
				$checked2='';
				$checked1='checked="checked"';
				$checked0='';
			}elseif($conf->global->REGISTRATION_VALID_AFTER_CREATE=='-1'){
				$checked2='';
				$checked1='';
				$checked0='checked="checked"';
			}elseif($conf->global->REGISTRATION_VALID_AFTER_CREATE=='2'){
				$checked2='checked="checked"';
				$checked1='';
				$checked0='';
			}

			print '<input type="radio" id="registration_valid_after_create" name="registration_valid_after_create" value="2" '.$checked2.'/> <label for="registration_valid_after_create_confirm">'.$langs->trans('ValidParticipationAfterCreation').'</label>';
			print '<br/>';
			print '<input type="radio" id="registration_valid_after_create" name="registration_valid_after_create" value="1" '.$checked1.'/> <label for="registration_valid_after_create_confirm">'.$langs->trans('ValidRegistrationAfterCreation').'</label>';
			print '<br/>';
			print '<input type="radio" id="registration_valid_after_create" name="registration_valid_after_create" value="-1"/ '.$checked0.'> <label for="registration_valid_after_create_cancel">'.$langs->trans('StandbyRegistrationAfterCreation').'</label>';
			print '</td>';
			print '	</tr>';

			// Envoi de mail
			if (GETPOST('event_send_email','int'))
			{
				if (GETPOST('event_send_email','int')=='1') {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
			}
			else
			{
				if ($conf->global->EVENT_SEND_EMAIL > 0) {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
			}
			print '<tr><td colspan="">'.$langs->trans('ValidSendEmail');
			print '</td>';
			print '<td colspan="3">';
			print '<input type="radio" id="event_send_email" name="event_send_email" value="1" '.$checkedYes.'/> <label for="send_email_confirm">'.$langs->trans('Yes').'</label>';
			print '<br/>';
			print '<input type="radio" id="event_send_email" name="event_send_email" '.$checkedNo.' value="-1"/> <label for="send_email_cancel">'.$langs->trans('No').'</label>';
			print '</td>';
			print '</tr>';

			// Envoi du pdf
			if (isset($conf->global->EVENT_SEND_PDF))
			{
				if ($conf->global->EVENT_SEND_PDF > 0) {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
			}
			else
			{
				if (GETPOST('event_send_pdf','int')>0) {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
							
			print '<tr><td colspan="">'.$langs->trans('ValidSendPDF');
			print '</td>';
			print '<td colspan="3">';
			print '<input type="radio" id="send_pdf_confirm" name="event_send_pdf" value="1" '.$checkedYes.'/> <label for="send_pdf_confirm">'.$langs->trans('Yes').'</label>';
			print '<br/>';
			print '<input type="radio" id="send_pdf_cancel" name="event_send_pdf" '.$checkedNo.' value="-1"/> <label for="send_pdf_cancel">'.$langs->trans('No').'</label>';
			print '</td>';
			print '</tr>';
			}


			/*
			 * Participant
			 */

			// Societe
			if($conf->global->EVENT_REGISTRATION_BLOCK_TIERS=='-1')
				{
				print '<tr class="liste_titre liste_titre_napf">';
				print '<td class="liste_titre" colspan="4"><strong>';
				print $langs->trans('ThirdParty');
				print '</strong></td>';
				print '</tr>';
				
				if($this->withsocid > 0)
				{
					print '<tr><td>'.$langs->trans("SelectThirdParty").'</td><td  colspan="4">';
					$events_form=array();
					$events_form[]=array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php',1), 'htmlname' => 'fk_user_registered', 'params' => array('add-customer-contact' => 'disabled'));
					print $form->select_company($this->fk_soc,'fk_soc','',1,0,0,$events_form);

					print '</td></tr>';


					// Create thirdparty ?
					print '<tr><td colspan="">'.$langs->trans('CreateNewThirdParty');
					print img_picto($langs->trans("CreateNewThirdPartyInfo"),'help');
					print '</td>';
					print '<td colspan="3">';
					if (GETPOST('create_thirdparty','int')>0) {
						$checkedYes='checked="checked"';
						$checkedNo='';
					}else {
						$checkedYes='';
						$checkedNo='checked="checked"';
					}
					print '<input type="radio" id="create_thirdparty_confirm" name="create_thirdparty" value="1" '.$checkedYes.'/> <label for="create_thirdparty_confirm">'.$langs->trans('Yes').'</label>';
					print '<br/>';
					print '<input type="radio" id="create_thirdparty_cancel" name="create_thirdparty" '.$checkedNo.' value="-1"/> <label for="create_thirdparty_cancel">'.$langs->trans('No').'</label>';
					print '</td>';
					print '	</tr>';

					// Thirdparty name
					print '<tr class="creating_tirdparty"><td><span class="fieldrequired">'.$langs->trans("ThirdPartyName").'</span></td>';
					print '<td colspan="3"><input name="societe_name" class="flat" size="50" value="'.GETPOST('societe_name','alpha').'"></td></tr>';

					// Address
					print '<tr class="creating_tirdparty"><td valign="top">'.$langs->trans('Address').'</td><td colspan="3"><textarea name="adresse" cols="40" rows="3" wrap="soft">';
					print GETPOST('adresse','alpha');
					print '</textarea></td></tr>';

					// Zip / Town
					print '<tr class="creating_tirdparty"><td>'.$langs->trans('Zip').'</td><td>';
					print $formcompany->select_ziptown(GETPOST('zipcode','alpha'),'zipcode',array('town','selectcountry_id','departement_id'),6);
					print '</td><td>'.$langs->trans('Town').'</td><td>';
					print $formcompany->select_ziptown(GETPOST('town','alpha'),'town',array('zipcode','selectcountry_id','departement_id'));
					print '</td></tr>';
				}
			}

			// FK_USER_REGISTERED
			print '<tr class="liste_titre liste_titre_napf">';
			print '<td class="liste_titre" colspan="4"><strong>';
			print $langs->trans('UserRegistrationInfos');
			print '</strong></td>';
			print '</tr>';

			if ($this->withuserregistered)
			{
				if (!$user->socid)
				{
					print "<tr><td width=\"".$width."\">".$langs->trans("UserToRegistration")."</td>";

					print '<td colspan="3">';
					$rescontacts = $form->select_contacts($this->fk_soc,$this->fk_user_registered,'fk_user_registered',1);
					print ' <input type="submit" name="select_contact" class="button" value="Valider" />';
					print '</td>';
					print "</tr>\n";
				}
			}
			$userstat = new Contact($this->db);

			if(!empty($this->fk_user_registered))
			{
				$userstat->fetch($this->fk_user_registered);

				$lastname = $userstat->lastname;
				unset($_POST['lastname']);
				$firstname = $userstat->firstname;
				unset($_POST['firstname']);
				$address = $userstat->address;
				unset($_POST['address']);
				$zip = $userstat->zip;
				unset($_POST['zipcode']);
				$town = $userstat->town;
				unset($_POST['town']);
				$country_id = $userstat->country_id;
				unset($_POST['country_id']);
				$fk_departement = $userstat->fk_departement;
				unset($_POST['fk_departement']);
				$phone = $userstat->phone;
				$phone_perso = $userstat->phone_perso;
				unset($_POST['phone_perso']);
				$phone_mobile = $userstat->phone_mobile;
				unset($_POST['phone_mobile']);
				$email_registration = $userstat->email;

				// Civility
				print '<tr><td>'.$langs->trans("UserTitle").'</td><td colspan="3">';
				print $userstat->civility_id;
				print '</td>';
				print '</tr>';

				// Lastname
				print '<tr><td><span class="fieldrequired">'.$langs->trans("Lastname").'</span></td><td>';//.$userstat->getNomUrl(1);
				print ' <a target="_blank" href="'.DOL_URL_ROOT.'/contact/card.php?id='.$userstat->id.'">'.img_object($langs->trans('ShowContact'), 'contact', 'class="classfortooltip"').' '.$langs->trans('ShowContact').'</a>';
				print ' <a target="_blank" href="'.DOL_URL_ROOT.'/contact/card.php?id='.$userstat->id.'&amp;action=edit">'.img_picto($langs->trans('EditContact'),'edit').' '.$langs->trans('EditContact').'</a>';
				print '</td>';

				// Address
				print '<tr>';
				print '<td valign="top"  rowspan="3">'.$langs->trans("Address").'</td>';
				print '<td rowspan="3">';
				print ($address?$address:$langs->trans('NotDefined'));
				print '</td>';

				// Zip / Town
				print '<td>'.$langs->trans("Zip").' / '.$langs->trans("Town").'</td>';

				print '<td>';
				print $zip;
				print ' ';
				print ($town?$town:$langs->trans('NotDefined'));
				print '</td>';
				print '</tr>';

				print '<tr>';

				// Country
				$this->country_id=$userstat->country_id?$userstat->country_id:$mysoc->country_id;
				print '<td width="25%">'.$langs->trans('Country').'</td><td>';
				$img=picto_from_langcode($userstat->country_code);
				if ($img) print $img.' ';
				print $userstat->country;
				print '</td></tr>';

				// State
				if (empty($conf->global->SOCIETE_DISABLE_STATE))
				{
					print '<tr><td>'.$langs->trans('State').'</td><td>';
					if ($userstat->state)
					{
						print $userstat->state;
					}
					else
					{
						print $langs->trans('NotDefined');
					}
					print '</td></tr>';
				}
				// Tel perso
//				print '<tr><td>'.$langs->trans("PhonePerso").'</td><td>'.($phone_perso?$phone_perso:$langs->trans('NotDefined')).'</td>';

				// Tel mobile
				print '<td>'.$langs->trans("PhoneMobile").'</td><td>'.($phone_mobile?img_picto('','tick').' '.$phone_mobile:img_picto($langs->trans('NotDefined'),'high').' '.$langs->trans('NotDefined')).'</td></tr>';

				// Tel pro
				print '<tr><td>'.$langs->trans("PhonePro").'</td><td>'.($phone?$phone:$langs->trans('NotDefined')).'</td>';

				// EMail
				print '<td>';
				if($this->mailrequired) print '<span class="fieldrequired">';
				print $langs->trans("EMail");
				if($this->mailrequired) print '</span>';
				print '</td><td>'.($email_registration?img_picto('','tick').' '.$email_registration:img_picto($langs->trans('NotDefined'),'high').' '.$langs->trans('NotDefined'));
				print '<input type="hidden" name="registration_email" value="'.$email_registration.'" /></td></tr>';



			}
//		else
//			{
				/*
				 * Contact info (create new)
				 */
/*				print '<tr><td colspan="">'.$langs->trans('CreateANewContactFromRegistration');
				print img_picto($langs->trans("CreateANewContactFromRegistrationInfo"),'help');
				print '</td>';
				print '<td colspan="3">';
				if (GETPOST('create_contact','int')>0) {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
				print '<input type="radio" id="create_contact_confirm" name="create_contact" value="1" '.$checkedYes.'/> <label for="create_contact_confirm">'.$langs->trans('Yes').'</label>';
				print '<br/>';
				print '<input type="radio" id="create_contact_cancel" name="create_contact" '.$checkedNo.' value="-1"/> <label for="create_contact_cancel">'.$langs->trans('No').'</label>';
				print '</td>';
				print '	</tr>';

				// Civility
				print '<tr class="creating_contact"><td><span class="fieldrequired">'.$langs->trans("UserTitle").'</span></td>';
				print '<td colspan="3">'.$formcompany->select_civility(GETPOST('civility_id','alpha')).'</td>';
				print '</tr>';

				// Name
				print '<tr class="creating_contact"><td><span class="fieldrequired">'.$langs->trans("Lastname").'</span></td>';
				print '<td><input name="name" class="flat" size="30" value="'.GETPOST('name','alpha').'"></td>';

				// firstname
				print '<td><span class="fieldrequired">'.$langs->trans("Firstname").'</span></td>';
				print '<td><input name="firstname" class="flat" size="30" value="'.GETPOST('firstname','alpha').'"></td></tr>';

				// Address
				print '<tr class="creating_contact"><td valign="top">'.$langs->trans('Address').'</td><td colspan="3"><textarea name="address" cols="40" rows="3" wrap="soft">';
				print GETPOST('address','alpha');
				print '</textarea></td></tr>';

				// Zip / Town
				print '<tr class="creating_contact"><td>'.$langs->trans('Zip').'</td><td>';
				print $formcompany->select_ziptown(GETPOST('zipcode','int'),'zipcode',array('town','selectcountry_id','departement_id'),6);
				print '</td><td>'.$langs->trans('Town').'</td><td>';
				print $formcompany->select_ziptown(GETPOST('town','alpha'),'town',array('zipcode','selectcountry_id','departement_id'));
				print '</td></tr>';

				// Country
				print '<tr class="creating_contact">';
				$this->country_id=$userstat->country_id?$userstat->country_id:$mysoc->country_id;
				print '<td width="25%">'.$langs->trans('Country').'</td><td colspan="3">';
				print $form->select_country(GETPOST('country_id','int')?GETPOST('country_id','int'):$country_id,'country_id');
				if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);
				print '</td></tr>';

				// State
				if (empty($conf->global->SOCIETE_DISABLE_STATE))
				{
					print '<tr class="creating_contact"><td>'.$langs->trans('State').'</td><td colspan="3">';
					print $formcompany->select_state($object->state_id,isset($_POST["country_id"])?$_POST["country_id"]:$object->country_id,'state_id');
					print '</td></tr>';
				}

				print '<tr class="creating_contact"><td>'.$langs->trans("PhonePerso").'</td>';
				print '<td><input name="tel_phone" class="flat" size="20" value="'.GETPOST('tel_phone').'"></td>';

				print '<td>'.$langs->trans("Mobile").'</td>';
				print '<td><input name="tel_mobile" class="flat" size="20" value="'.GETPOST('tel_mobile').'"></td></tr>';

				print '<tr class="creating_contact"><td>'.$langs->trans("Mail").'</td>';
				print '<td colspan="3"><input name="mail" class="flat" size="20" value="'.GETPOST('mail').'"></td></tr>';


				// Other extra attributes for contact
				$param='';
				$param[style]='class="creating_contact"';
				$param[colspan]='3';
				print $userstat->showOptionals($extrafields_contact,'edit',$param);

			}
*/
			// Other extra attributes for registration
			print $registration->showOptionals($extrafields,'edit');

			// MESSAGE
			print "<tr><td width=\"".$width."\"><label for=\"message\">".$langs->trans("MessageDesc")."</label></td><td colspan='3'>";
			$doleditor = new DolEditor('message', $this->message, '', 400, 'dolibarr_emailing', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 8, 160);
    		$doleditor->Create();
			print '</td></tr>';
		}

        print "</table>\n";
        print '<div style="text-align: center; margin:15px 0;">';

        if($this->allowedregistration)
	    {
	        print "<input class=\"button\" type=\"submit\" name=\"add_registration\" value=\"".$langs->trans("RegistrationCreate")."\"";
	        print ">";
			print ' &nbsp; &nbsp; ';
			print '<input type="button" class="button" name="cancel" onclick="history.go(-1);" value="'.$langs->trans("Cancel").'">';
	    }
        if ($this->withcancel)
        {
            print " &nbsp; &nbsp; ";
			print '&nbsp; &nbsp;<input type="button" class="button" name="cancel" onclick="history.go(-1);" value='.$langs->trans('Cancel').'>';
        }
        print "</div>\n";

        print "</form>\n";
        print "<!-- End form REGISTRATION -->\n";
    }

/**
    * Show the form to create a new registration
    *
    * @param string $width Width of form
    * @return void
    */
    function show_form_tag($width='120px')
    {
        global $conf, $langs, $user;

        $langs->load("other");
        $langs->load("mails");
        $langs->load("event@event");

        $form=new Form($this->db);
        $formcompany = new FormCompany($this->db);
        $formevent = new FormEvent($this->db);

        $eventstat=new Event($this->db);
        $eventdaystat=new Day($this->db);
        $registration = new Registration($this->db);


        $soc=new Societe($this->db);

        $extrafields = new ExtraFields($this->db);
        $extrafields_contact = new ExtraFields($this->db);

        // Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
        include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
        $hookmanager=new HookManager($db);
        $hookmanager->initHooks(array('eventregistration'));

        // fetch optionals attributes and labels
        $extralabels=$extrafields->fetch_name_optionals_label('event_registration');
        $extralabels_contact=$extrafields_contact->fetch_name_optionals_label('socpeople');

        print "\n<!-- Begin form REGISTRATION -->\n";

        $this->fk_departement = $_POST["departement_id"];

        // We set country_id, country_code and country for the selected country
        $this->country_id=GETPOST('country_id','int')?GETPOST('country_id','int'):$mysoc->country_id;
        if ($this->country_id)
        {
        	$tmparray=getCountry($this->country_id,'all');
        	$this->pays_code=$tmparray['code'];
        	$this->pays=$tmparray['code'];
        	$this->country_code=$tmparray['code'];
        	$this->country=$tmparray['label'];
        }


        if ($conf->use_javascript_ajax)
        {
        	print "\n".'<script type="text/javascript" language="javascript">';
        	print 'jQuery(document).ready(function () {
        	jQuery("#fk_event, #selectcountry_id, #fk_soc, #fk_user_registered").change(function() {
	        	document.registration.action.value="'.$this->action.'";
	        	document.registration.submit();
        		});

	        	$(".creating_tirdparty").hide();
	        	$("#create_thirdparty_confirm").click(function() {
                   	$(".creating_tirdparty").show();
                });
	        	$("#create_thirdparty_cancel").click(function() {
                   	$(".creating_tirdparty").hide();
                });

        		$(".creating_contact").hide();
	        	$("#create_contact_confirm").click(function() {
                   	$(".creating_contact").show();
                });
	        	$("#create_contact_cancel").click(function() {
                   	$(".creating_contact").hide();
                });
	        ';

        	// If selected, show fields
        	if(GETPOST('create_thirdparty','int') == "1"){
        		print '$(".creating_tirdparty").show();';
        	}
        	if(GETPOST('create_contact','int') == "1"){
        		print '$(".creating_contact").show();';
        	}


        	print '});';
        	print '</script>'."\n";
        }

        print "<form method=\"POST\" name=\"registration\" enctype=\"multipart/form-data\" action=\"".$this->param["returnurl"]."\">\n";
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="'.$this->action.'">';
		print '<input type="hidden" name="fk_eventday" value="'.$this->fk_eventday.'">';
        foreach ($this->param as $key=>$value)
        {
            print "<input type=\"hidden\" name=\"$key\" value=\"$value\">\n";
        }
        print "<table class=\"border\" width=\"100%\">\n";

        print '<tr class="liste_titre liste_titre_napf">';
        print '<td class="liste_titre" colspan="4"><strong>';
        print $langs->trans('RegistrationInfos');
        print '</strong></td>';
        print '</tr>';

		// FK_USER_CREATE
		if ($this->fk_user_create > 0)
		{
			print '<input type="hidden" name="fk_user_create" value="'.$this->fk_user_create.'">';

			print "<tr><td width=\"".$width."\">".$langs->trans("RegistrationFrom")."</td><td colspan='3'>";
			$langs->load("users");
			$fuser=new User($this->db);
			$fuser->fetch($this->fk_user_create);
			print $fuser->getNomUrl(1);
			print ' &nbsp; ';
			print "</td></tr>\n";

		}

        // Event Infos
        if ($this->withevent)
        {
        	print '<tr><td><span class="fieldrequired">'.$langs->trans('Event').'</span></td>';
        	if ($this->fk_event > 0)
        	{
        		$eventstat->fetch($this->fk_event);
        		$this->registration_byday=$eventstat->registration_byday;
        		print '<td colspan="3">'.$eventstat->getNomUrl(4);
        		print '</td>';
        	}
        	else
        	{
        		print '<td colspan="3">';
        		print $formevent->select_event(GETPOST('fk_event','int'), 'fk_event','intitule',1);
        		print '</td>';
        	}
        	print '</tr>';
        }


        // Day of event
        if ($this->witheventday)
        {
        	print '<tr>';
        	print '<td width="25%;">'.$langs->trans('EventDay').'</td>';
        	if ($this->fk_eventday > 0)
        	{
        		$eventdaystat->fetch($this->fk_eventday);
        		print '<td  width="25%;"><a href="'.DOL_URL_ROOT.'/custom/event/day/fiche.php?id='.$this->fk_eventday.'"><img src="'.DOL_URL_ROOT.'/custom/event/img/object_day.png" border="0" alt="" title="Voir la journée: '.$eventdaystat->ref.' - '.$eventdaystat->label.'"> '.$eventdaystat->label.'</a>';
        		// Date of eventday
        		print '<td width="25%;">'.$langs->trans('EventDayDate').'</td>';
        		print '<td width="25%;">'.dol_print_date($this->datec,'daytext').'</td>';
        	}
        	else
        	{
        		print '<td colspan="3">';
        		print $this->select_day(GETPOST('fk_event','int'));
        		print '</td>';
        	}
	        print '</tr>';
        }

        // REF
        if($this->ref)
        	print '<tr><td><span class="fieldrequired">'.$langs->trans("Ref").'</span></td><td><input size="12" type="text" name="ref" value="'.$this->ref.'"></td></tr>';
		
		if($this->allowedregistration)
		{
			/*
			 * Ask if validation wanted after creation
			*/
			print '<tr><td colspan="">'.$langs->trans('ValidRegistrationAfterCreation');
			print img_picto($langs->transnoentities("ValidRegistrationAfterCreationInfo"),'help');
			print '</td>';
			print '<td colspan="3">';
			if (GETPOST('registration_valid_after_create','int')>0) {
				$checkedYes='checked="checked"';
				$checkedNo='';
			}else {
				$checkedYes='checked="checked"';
				$checkedNo='';
			}
			print '<input type="radio" id="registration_valid_after_create_confirm" name="registration_valid_after_create" value="1" '.$checkedYes.'/> <label for="registration_valid_after_create_confirm">'.$langs->trans('Yes').'</label>';
			print '<br/>';
			print '<input type="radio" id="registration_valid_after_create_cancel" name="registration_valid_after_create" '.$checkedNo.' value="-1"/> <label for="registration_valid_after_create_cancel">'.$langs->trans('No').'</label>';
			print '</td>';
			print '	</tr>';

			// Envoi de mail
			if (isset($conf->global->EVENT_SEND_EMAIL))
			{
				if ($conf->global->EVENT_SEND_EMAIL > 0) {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
			}
			else
			{
				if (GETPOST('event_send_email','int')>0) {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
			}
			print '<tr><td colspan="">'.$langs->trans('ValidSendEmail');
			print '</td>';
			print '<td colspan="3">';
			print '<input type="radio" id="send_email_confirm" name="event_send_email" value="1" '.$checkedYes.'/> <label for="send_email_confirm">'.$langs->trans('Yes').'</label>';
			print '<br/>';
			print '<input type="radio" id="send_email_cancel" name="event_send_email" '.$checkedNo.' value="-1"/> <label for="send_email_cancel">'.$langs->trans('No').'</label>';
			print '</td>';
			print '</tr>';

			// Envoi du pdf
			if (isset($conf->global->EVENT_SEND_PDF))
			{
				if ($conf->global->EVENT_SEND_PDF > 0) {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
			}
			else
			{
				if (GETPOST('event_send_pdf','int')>0) {
					$checkedYes='checked="checked"';
					$checkedNo='';
				}else {
					$checkedYes='';
					$checkedNo='checked="checked"';
				}
			}				
/*			print '<tr><td colspan="">'.$langs->trans('ValidSendPDF');
			print '</td>';
			print '<td colspan="3">';
			print '<input type="radio" id="send_pdf_confirm" name="event_send_pdf" value="1" '.$checkedYes.'/> <label for="send_pdf_confirm">'.$langs->trans('Yes').'</label>';
			print '<br/>';
			print '<input type="radio" id="send_pdf_cancel" name="event_send_pdf" '.$checkedNo.' value="-1"/> <label for="send_pdf_cancel">'.$langs->trans('No').'</label>';
			print '</td>';
			print '</tr>';*/

			print '<tr class="liste_titre liste_titre_napf">';
			print '<td class="liste_titre" colspan="4"><strong>';
			print $langs->trans('UserRegistrationInfos');
			print '</strong></td>';
			print '</tr>';

			// FK_USER_REGISTERED
			if ($this->withuserregistered)
			{
				if (!$user->socid)
				{
					print "<tr><td width=\"".$width."\">".$langs->trans("GroupToRegistration")."</td>";

					if ($this->select_tag > 0)
		        	{
		        		$sql = "SELECT c.label, c.color";
					    $sql.= " FROM ".MAIN_DB_PREFIX."categorie as c";
					    $sql.= " WHERE ";
					    $sql.= " c.rowid=".$this->select_tag;
					    $resql = $this->db->query($sql);
					    $res = $resql->fetch_assoc();
		        		print '<td colspan="3"><div class="classfortooltip" style="width:20px;height:13px;border:1px solid #000;background-color:#'.$res['color'].';float:left;margin-top:0.3%"></div>&nbsp'.$res['label'];
		        		// print '<a href="'.$_SERVER['PHP_SELF'].'?dayid='.$this->fk_eventday.'">'.img_picto($langs->trans('Edit'),'edit').'</a>';
		        		print '</td>';
		        	}
		        	else
		        	{
			        	print '<td colspan="3">';
						print $this->select_tag($this->fk_eventday);
						print '</td>';
		        	}

					print "</tr>\n";
				}

			}

			$userstat = new Contact($this->db);

			// Other extra attributes for registration
			print $registration->showOptionals($extrafields,'edit');

			// MESSAGE
			print "<tr><td width=\"".$width."\"><label for=\"message\">".$langs->trans("MessageDesc")."</label></td><td colspan='3'>";
			$doleditor = new DolEditor('message', $this->message, '', 400, 'dolibarr_emailing', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 8, 160);
    		$doleditor->Create();
			print '</td></tr>';
		}

        print "</table>\n";
        print '<div style="text-align: center; margin:15px 0;">';

        if($this->allowedregistration) {
	        print "<input class=\"button\" type=\"submit\" name=\"add_registration\" value=\"".$langs->trans("RegistrationCreate")."\"";
	        print ">";
			print ' &nbsp; &nbsp; ';
			print '&nbsp; &nbsp;<input type="button" class="button" name="cancel" onclick="history.go(-1);" value='.$langs->trans('Cancel').'>';

	    }
        if ($this->withcancel) {
            print " &nbsp; &nbsp; ";
			print '&nbsp; &nbsp;<input type="button" class="button" name="cancel" onclick="history.go(-1);" value='.$langs->trans('Cancel').'>';
        }
        print "</div>\n";

        print "</form>\n";
        print "<!-- End form REGISTRATION -->\n";
    }

	function select_day($selectid='')
	{
		global $conf,$user,$langs;

		$sql = "SELECT ed.rowid, ed.label, ed.date_event";
    	$sql.= " FROM ".MAIN_DB_PREFIX."event_day as ed";
    	if ($selectid)
    		$sql.= " WHERE fk_event = '".$selectid."' AND";
    	else
    		$sql.= " WHERE ";
    	$sql.= " DATE(NOW()) <= DATE(date_event)";
    	$sql.= " ORDER BY ed.date_event";

    	$out.= '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form">';
    	$out.= '<input type="hidden" name="action" value="set">';
    	$out.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    	dol_syslog(get_class($this)."::select_event sql=".$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		if ($conf->use_javascript_ajax && ! $forcecombo)
    		{
    			$out.= ajax_combobox('dayid', $event);
    		}

    		$out.= '<select id="dayid" class="flat" name="dayid">';
    		$num = $this->db->num_rows($resql);
    		$i = 0;
    		if ($num)
    		{
    			while ($i < $num)
    			{
    				$obj = $this->db->fetch_object($resql);
    				$label=$obj->label;
    				$out.= '<option value="'.$obj->rowid.'">'.dol_print_date($this->db->jdate($obj->date_start),'day').$label.'</option>';
    				$i++;
    			}
    		}
    		$out.= '</select>';

    		$out.= '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
    		$out.= '</form>';
    	}
    	else
    	{
    		dol_print_error($this->db);
    	}
    	$this->db->free($resql);
    	return $out;
	}

	function select_tag($dayid)
	{
		global $conf,$user,$langs;

		$sql = "SELECT c.rowid, c.label, c.color";
    	$sql.= " FROM ".MAIN_DB_PREFIX."categorie as c";
    	$sql.= " WHERE ";
    	$sql.= " c.type=4";
    	$sql.= " ORDER BY c.label";

    	$out.= '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form">';
    	//$out.= '<input type="hidden" name="action" value="set_tag">';
    	$out.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    	dol_syslog(get_class($this)."::select_tag sql=".$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		$out.= '<select id="selet_tag" class="flat selectpicker show-tick" name="select_tag" data-live-search="true">';
    		$num = $this->db->num_rows($resql);
    		$i = 0;
    		if ($num)
    		{
    			while ($i < $num)
    			{
    				$obj = $this->db->fetch_object($resql);
    				$label=$obj->label;
    				if($conf->theme=='allscreen') $out.= '<option value="'.$obj->rowid.'" data-content="<div style=\'width:20px;height:13px;border:1px solid #000;background-color:#'.$obj->color.';float:left;margin-top:1.5%\'></div>&nbsp;'.$label.'"></option>';
    				else $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
    				$i++;
    			}
    		}
    		$out.= '</select>';

    		$out.= '&nbsp;<input type="submit" class="button" value="'.$langs->trans("Valid").'">';
    		$out.= '</form>';
    	}
    	else
    	{
    		dol_print_error($this->db);
    	}
    	$this->db->free($resql);
    	return $out;
	}
	
    function show_select_day_for_an_event($full_form=0)
    {
    	if($full_form)
    	{
    		print "\n<!-- Begin select LEVELFORDAY -->\n";


    		print "<form method=\"POST\" name=\"dayforanevent\" enctype=\"multipart/form-data\" action=\"".$this->param["returnurl"]."\">\n";
    		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    		print '<input type="hidden" name="action" value="'.$this->action.'">';
    		foreach ($this->param as $key=>$value)
    		{
    			print '<input type="hidden" name="$key" value="'.$value.'"'. ($value == $this->fk_levelday?'selected="selected"':'').' />';
    		}
    	}


    	// Affichage des groupes et des places dispo
    	$level=new Eventlevel($this->db);

    	$sql = "SELECT ed.rowid, ed.ref, ed.label, ed.date_event, ed.registration_open  FROM  ".MAIN_DB_PREFIX."event_day as ed WHERE ed.fk_event='".$this->fk_event."'  ORDER BY ed.date_event ASC;";
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		$num = $this->db->num_rows($resql);
    		if ($num)
    		{
    			$j = 0;
    			// Affichage des groupes et indication réservation (full, etc...)
    			echo "<div class=\"dayforanevent\">\n";
    			echo '<ul style="list-style-type:none; margin: 0; padding:0;">';

    			while ($j < $num)
    			{
    				$objd = $this->db->fetch_object($resql);
    				$event_daytmp = $this->fk_eventday;
    				echo "<li>";
    				$selected='';
    				if($objd->registration_open)
    					$selected=' checked="checked" ';

    				if($this->fk_eventday < 0)
    				{
    					$selected.=' disabled="disabled" ';
    					print '<input type="hidden" name="fk_eventday[]" value="'.$objd->rowid.'" />';
    				}
    				print '<input type="checkbox" value="'.$objd->rowid.'" name="fk_eventday[]" '.$selected.'/> ';

    				$daystat = new Day($db);
    				$daystat->id = $objd->rowid;
    				$daystat->label = $objd->label;
    				$daystat->ref = $objd->ref;

    				echo "<label style=\"float: none;\">".$daystat->getNomUrl(4)." - ".dol_print_date($objd->date_event,'daytext')."</label>";

    				if($objd->rowid > 0 && $objd->registration_open)
    				{
	    				$this->fk_eventday=$objd->rowid;
	    				print $this->show_select_level_for_day(0,1);
	    				$this->fk_eventday=$event_daytmp;

    				}
    				else
    				{
    					print '&nbsp;<span class="error">Inscriptions désactivées</span>';
    				}

    				echo "</li>\n";
    				// Affichage des places


    				$j++;
    			}
    			echo '</ul></div><!-- groupe -->';
    		}
    	}

    	if($full_form)
    	{


    		print '<input type="submit" class="button" name="select_level" value="'.$langs->trans("Select").'">';
    		print ' &nbsp; &nbsp; ';
    		print '<input type="button" class="button" name="cancel" action="action" onclick="history.go(-1);" value="'.$langs->trans("Cancel").'">';

    		print '</form>';
    		print "\n<!-- end form REGISTRATION select level -->\n";
    	}
    }


    function show_select_level_for_day($full_form=0,$with_array=0)
    {
    	global $langs;
    	if($full_form)
    	{
	    	print "\n<!-- Begin select LEVELFORDAY -->\n";


	    	print "<form method=\"POST\" name=\"leveldoraday\" enctype=\"multipart/form-data\" action=\"".$this->param["returnurl"]."\">\n";
	    	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	    	print '<input type="hidden" name="action" value="'.$this->action.'">';
	    	foreach ($this->param as $key=>$value)
	    	{
	    		print '<input type="hidden" name="$key" value="'.$value.'"'. ($value == $this->fk_level?'selected="selected"':'').' />';
	    	}
    	}


    	// Affichage des groupes et des places dispo
    	$level=new Eventlevel($this->db);

    	$sql = "SELECT ld.rowid, ld.fk_level, l.label, ld.place, ld.full FROM ".MAIN_DB_PREFIX."event_level as l, ".MAIN_DB_PREFIX."event_level_day as ld WHERE l.rowid = ld.fk_level AND ld.fk_eventday='".$this->fk_eventday."' ORDER BY l.rang ASC;";
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		$num = $this->db->num_rows($resql);
    		if ($num > 0 && $this->fk_eventday > 0)
    		{
    			$j = 0;
    			// Affichage des groupes et indication réservation (full, etc...)
    			echo "<div class=\"groupes_insc\">\n";
    			echo '<ul style="list-style-type:none">';
    			while ($j < $num)
    			{
    				$groupesql = $this->db->fetch_object($resql);

    				echo "<li>";
    				$level->countRegistrationForLevel($this->fk_eventday,$groupesql->fk_level);

    				if($groupesql->place <= $level->nb_inscrits && $level->nb_inscrits < $level->place) {
    					$img = img_picto($alt, 'statut3');
    					$insc = true;
    					$msg = "Plus que quelques places disponible!";
    				}
    				// nb d'inscrit < ($groupesql->place - $event->conf_limite_niveau_orange)
    				elseif ($groupesql->place > $level->nb_inscrits ) {
    					$img = img_picto($alt, 'statut4');
    					$msg = "";
    					$insc = true;
    				}
    				// si le nombre d'inscrit >= place : liste d'attente
    				else {
    					$img = img_picto($alt, 'high');
    					$insc = true;
    					$msg = "Liste d'attente";
    				}
    				// Prise en compte des groupe complets
    				if($groupesql->full == "1") {
    					$img = img_picto($alt, 'stcomm-1');
    					$insc = false;
    					$msg = "FULL! L'inscription n'est plus possible pour cette session ";
    				}
    				$selected='';
    				if(is_array($this->fk_level))
    				{
    					if ($groupesql->fk_level == $this->fk_level[$this->fk_eventday][$j])
    						$selected = 'checked="checked"';
    				}
    				else {
    					if ($groupesql->fk_level == $this->fk_level)
    						$selected = 'checked="checked"';
    				}

    				$disabled = ($insc ? '':'disabled="disabled"');
   					$name = ($with_array?'fk_level['.$this->fk_eventday.'][]':'fk_level');

    				print '<input type="checkbox" value="'.$groupesql->fk_level.'" id="level-'.$this->fk_eventday.'-'.$groupesql->fk_level.'" name="'.$name.'" '.$selected.' '.$disabled.'  /> ';
       				echo "<label for=\"level-".$this->fk_eventday."-".$groupesql->fk_level."\" style=\"float: none;\"><strong>".$img.' '.$groupesql->label." </strong></label>";
    				if($msg) echo "<span class=\"texte\">".$msg."</span>";

    				echo "</li>\n";

    				$j++;
    			}
    			echo '</ul></div><!-- groupe -->';
    		}
    		else
    		{
    			print '&nbsp<span class="error">'.$langs->trans('NoLevelDefinedForThisDay').'</span>';
    		}

    	}

    	if($full_form)
    	{


    		print '<input type="submit" class="button" name="select_level" value="'.$langs->trans("Select").'">';
    		print ' &nbsp; &nbsp; ';
    		print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';

    		print '</form>';
    		print "\n<!-- end form REGISTRATION select level -->\n";
    	}
    }




}

?>
