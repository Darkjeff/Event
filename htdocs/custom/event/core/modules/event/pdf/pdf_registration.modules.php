<?php
/* Copyright (C) 2010 Regis Houssin  <regis@dolibarr.fr>

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
 * or see http://www.gnu.org/
 */

/**
 *	\file       core/modules/event/pdf/pdf_registration.modules.php
 *	\ingroup    event
 *	\brief      Fichier de la classe permettant de generer les inscriptions Baleine
 *	\author	    JF FERRY
 *	\version    $Id: pdf_registration.modules.php,v 1.39 2011/07/31 23:28:18 eldy Exp $
 */

dol_include_once('/event/core/modules/registration/modules_registration.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/class/extrafields.class.php");



/**
 *	\class      pdf_fiche
 *	\brief      Classe permettant de generer les events au modele Baleine
 */

class pdf_registration extends ModeleRegistration
{
	var $emetteur;	// Objet societe qui emet

	/**
	 *		\brief  Constructor
	 *		\param	db		Database handler
	 */
	function pdf_registration($db)
	{
		global $conf,$langs,$mysoc;

		$langs->load("main");
		$langs->load("event@event");
		$langs->load("companies");

		$this->db = $db;
		$this->name = "registration";
		$this->description = $langs->trans("DocumentModelRegistration");

		// Dimension page pour format A4
		$this->type = 'pdf';
		$this->page_largeur = 210;
		$this->page_hauteur = 297;
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=10;
		$this->marge_droite=10;
		$this->marge_haute=10;
		$this->marge_basse=10;

		$this->option_logo = 1;                    // Affiche logo FAC_PDF_LOGO
		$this->option_tva = 1;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_codeproduitservice = 1;      // Affiche code produit-service

		// Recupere emmetteur
		$this->emetteur=$mysoc;
		if (! $this->emetteur->pays_code) $this->emetteur->pays_code=substr($langs->defaultlang,-2);    // Par defaut, si n'�tait pas d�fini

		// Defini position des colonnes
		$this->posxref=$this->marge_gauche+1;
		$this->posxlabel=$this->marge_gauche+25;
		$this->posxprogress=$this->marge_gauche+140;
		$this->posxdatestart=$this->marge_gauche+150;
		$this->posxdateend=$this->marge_gauche+170;
	}


	/**
	 *	\brief      Fonction generant le event sur le disque
	 *	\param	    object   		Object registration a generer
	 *	\param		outputlangs		Lang output object
	 *	\return	    int         	1 if OK, <=0 if KO
	 */
	function write_file($object,$outputlangs)
	{
		global $user,$langs,$conf;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		// Add pdfgeneration hook
		if (! is_object($hookmanager))
		{
			include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
			$hookmanager=new HookManager($this->db);
		}

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("event@event");

		if ($conf->event->dir_output)
		{
			// Customer
			if (is_null($object->thirdparty)) { $object->fetch_thirdparty();}


			$nblignes = sizeof($object->lines);

			$default_font_size = pdf_getPDFFontsize($outputlangs);

			$objectref = dol_sanitizeFileName($object->ref);
			$dir = $conf->event->dir_output;
			if (! preg_match('/specimen/i',$objectref)) $dir.= "/" . $objectref;
			$file = $dir . "/" . $objectref . ".pdf";
			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
                $pdf=pdf_getInstance($this->format);

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Registration"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->id)." ".$outputlangs->transnoentities("Registration"));
				if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				$pdf->SetAutoPageBreak(1,0);

				// New page
				$pdf->AddPage();
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColor(0,0,0);

				$tab_top = 80;
				$tab_height = 200;
				$tab_top_newpage = 50;
                $tab_height_newpage = 210;

				// Affiche notes
				if (! empty($object->note_public))
				{
					$pdf->SetFont('','', $default_font_size - 1);
					$pdf->SetXY ($this->posxref-1, $tab_top-2);
					$pdf->MultiCell(190, 3, $outputlangs->convToOutputCharset($object->note_public), 0, 'L');
					$nexY = $pdf->GetY();
					$height_note=$nexY-($tab_top-2);

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->Rect($this->marge_gauche, $tab_top-3, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1);

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+6;
				}
				else
				{
					$height_note=0;
				}

				$iniY = $tab_top + 2;
				$curY = $tab_top + 2;
				$nexY = $tab_top + 2;


				// Info event and day
				$object->LoadEventInfo();
				$object->LoadEventDayInfo();
				$ref=$object->lines[$i]->ref;
				$date_eventday=dol_print_date($object->eventday->date_event,'daytext');
				$label_event=$object->event->label;
				$progress=$object->lines[$i]->progress.'%';
				$datestart=dol_print_date($object->lines[$i]->date_start,'day');
				$dateend=dol_print_date($object->lines[$i]->date_end,'day');


				$pdf->SetXY($this->posxref, $curY);
				$pdf->SetFont('','B',14);
				$pdf->MultiCell(0, 0, $object->eventday->label.' - '.$outputlangs->transnoentities('LabelEventDayOf',$date_eventday).' '.$outputlangs->convToOutputCharset($object->$label_event), 0, 'C');

				// Groupe
				if($object->fk_levelday > 0)
				{
					$level=new Eventlevel($this->db);
					$result=$level->fetch($object->fk_levelday);
					$pdf->SetFont('','B',12);
					$pdf->MultiCell(0,10,$outputlangs->transnoentities("EventLevel").' : '.$outputlangs->convToOutputCharset($level->label),0,'C');

				}

				$pdf->Ln(6);

				$pdf->SetFont('','B',12);
				$pdf->MultiCell($largeur_col1,5,$outputlangs->transnoentities("RegistrationUserInfos"),'B','L');


				$object->fetch_contact($object->fk_user_registered);

				$largeur_col1 = 40;
				$largeur_col2 = 60;
				// Nom
				$pdf->SetFont('','B',10);
				$pdf->Cell($largeur_col1,10,$outputlangs->transnoentities("Name"),0);
				$pdf->SetFont('','',11);
				$pdf->Cell($largeur_col2,10,$object->contact->lastname,0);

				// Prénom
				$pdf->SetFont('','B',10);
				$pdf->Cell($largeur_col1,10,$outputlangs->transnoentities("Firstname"),0);
				$pdf->SetFont('','',11);
				$pdf->Cell($largeur_col2,10,$object->contact->firstname,0);

				//Saut de ligne
				$pdf->Ln(6);

				// Adresse
				$pdf->SetFont('','B',10);
				$pdf->Cell($largeur_col1,10,$outputlangs->transnoentities("Address"),0);
				$pdf->SetFont('','',11);
				$pdf->Cell($largeur_col2,10,$object->contact->address,0);
				$pdf->Ln(6);

				// Zip & town
				$pdf->SetFont('','B',10);
				$pdf->Cell($largeur_col1,10,$outputlangs->transnoentities("Zip"),0);
				$pdf->SetFont('','',11);
				$pdf->Cell($largeur_col2,10,$object->contact->zip,0);
				$pdf->SetFont('','B',10);
				$pdf->Cell($largeur_col1,10,$outputlangs->transnoentities("Town"),0);
				$pdf->SetFont('','',11);
				$pdf->Cell($largeur_col2,10,$object->contact->town,0);

				$pdf->Ln(6);

				// Téléphone et mail
				$pdf->SetFont('','B',10);
				$pdf->Cell($largeur_col1,10,$outputlangs->transnoentities("Phone"),0);
				$pdf->SetFont('','',11);
				$pdf->Cell($largeur_col2,10,$object->contact->phone_mobile,0);

				$pdf->SetFont('','B',10);
				$pdf->Cell($largeur_col1,10,"E-mail  ",0);
				$pdf->SetFont('','',11);
				$pdf->Cell($largeur_col2,10,$object->contact->email,0);


				//Saut de ligne
				$pdf->Ln(7);


				$extrafields = new ExtraFields($this->db);
				// fetch optionals attributes and labels
				$extralabels=$extrafields->fetch_name_optionals_label('event_registration');
				// Get extrafield values
				$object->fetch_optionals($object->id,$extralabels);
				// Other attributes
				$parameters=array();
				$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
				if (empty($reshook) && ! empty($extrafields->attribute_label))
				{
					$z=0;
					foreach($extrafields->attribute_label as $key=>$label)
					{

						$value=$object->array_options["options_$key"];
						$pdf->SetFont('','B',10);
						$pdf->Cell($largeur_col1,10,$outputlangs->convToOutputCharset("$label"),0);
						$pdf->SetFont('','',11);
						$pdf->Cell($largeur_col2,10,$extrafields->showOutputField($key,$value),0);

						($z % 2 == 1 ? $pdf->Ln(7) : '');

						 $z++;
					}
				}
				$pdf->Ln(10);




				$extrafields = new ExtraFields($this->db);
				// Infos assurance du contact inscrit
				// fetch optionals attributes and labels
				$extralabels=$extrafields->fetch_name_optionals_label('contact');
				// Get extrafield values
				$object->contact->fetch_optionals($object->fk_user_registered,$extralabels);

				if(count($object->contact->array_options) > 0) {
					$pdf->SetFont('','B',12);
					$pdf->MultiCell('',5,$outputlangs->transnoentities("RegistrationExtraInfos"),'B','L');
				}
				// Other attributes
				$parameters=array();
				$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
				if (empty($reshook) && ! empty($extrafields->attribute_label))
				{
					$z=0;
					foreach($extrafields->attribute_label as $key=>$label)
					{

						$value=$object->contact->array_options["options_$key"];
						$pdf->SetFont('','B',10);
						$pdf->Cell($largeur_col1,10,$outputlangs->convToOutputCharset("$label"),0);
						$pdf->SetFont('','',11);
						$pdf->Cell($largeur_col2,10,$extrafields->showOutputField($key,$value),0);

						($z % 2 == 1 ? $pdf->Ln(7) : '');
						$z++;
					}
				}




				$pdf->Ln(10);

				// Info to return document if valid (not confirm)
				if($object->fk_statut == 1)
				{
					$pdf->SetFont('','',12);
					$pdf->MultiCell(190,5,$outputlangs->transnoentities("MsgPleaseReturnThisDocOnEventDay"),0,'C');
					$pdf->SetFont('','B',12);
					$pdf->Ln(5);
				}

				// Info to bring document if confirmed
				if($object->fk_statut == 4)
				{
					$pdf->SetFont('','',12);
					$pdf->MultiCell(190,5,$outputlangs->transnoentities("MsgPleaseBringThisDocOnEventDay"),0,'C');
					$pdf->SetFont('','B',12);
					$pdf->Ln(5);
				}


				$pdf->Ln(20);

				$pdf->Cell(100,10,"A .......................................");
				$pdf->Cell(100,10,"Le ......................................");
				$pdf->Ln(5);
				$pdf->SetFont('','B',12);
				$pdf->Ln(5);
				$pdf->Cell(100,10,"Signature ");
				$pdf->Ln(15);
				$pdf->SetFont('','I',10);
				$pdf->MultiCell(190,5,$outputlangs->transnoentities("PDFMsgLegal",$this->emetteur->url),0);

				$pdf->Ln(10);

				// Show square
				$bottomlasttab=$tab_top + $tab_height + 1;

				/*
				 * Pied de page
				 */
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');

				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks


				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;   // Pas d'erreur
			}
			else
			{
				$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}

		$this->error=$langs->transnoentities("ErrorConstantNotDefined","EVENT_OUTPUTDIR");
		return 0;
	}


	/*
	 *   \brief      Affiche la grille des lignes
	 *   \param      pdf     objet PDF
	 */
	function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs)
	{
		global $conf,$mysoc;

        $default_font_size = pdf_getPDFFontSize($outputlangs);

		$pdf->SetDrawColor(128,128,128);

		// Rect prend une longueur en 3eme param
		$pdf->Rect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height);
		// line prend une position y en 3eme param
		$pdf->line($this->marge_gauche, $tab_top+6, $this->page_largeur-$this->marge_droite, $tab_top+6);

		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','', $default_font_size);

		$pdf->SetXY ($this->posxref-1, $tab_top+2);
		$pdf->MultiCell(80,2, $outputlangs->transnoentities("RegistrationTicket"),'','L');

	}

	/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			&$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $conf,$langs;
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("interventions");

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		//Affiche le filigrane brouillon - Print Draft Watermark
		if($object->statut==0 && (! empty($conf->global->FICHINTER_DRAFT_WATERMARK)) )
		{
			pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->FICHINTER_DRAFT_WATERMARK);
		}

		//Prepare la suite
		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posx=$this->page_largeur-$this->marge_droite-100;
		$posy=$this->marge_haute;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Logo
		$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
		if ($this->emetteur->logo)
		{
			if (is_readable($logo))
			{
			    $height=pdf_getHeightForLogo($logo);
			    $pdf->Image($logo, $this->marge_gauche, $posy, 0, $height);	// width=0 (auto)
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B',$default_font_size - 2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		}
		else
		{
			$text=$object->thirdparty->name ;//swap
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}

		$pdf->SetFont('','B',$default_font_size + 3);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$title=$outputlangs->transnoentities("RegistrationTicket");
		$pdf->MultiCell(100, 4, $title, '', 'R');

		$pdf->SetFont('','B',$default_font_size + 2);

		$posy+=5;
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Ref")." : " . $outputlangs->convToOutputCharset($object->ref), '', 'R');

		$posy+=1;
		$pdf->SetFont('','', $default_font_size);

		$posy+=4;
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell(100, 3, $outputlangs->transnoentities("DateValid")." : " . dol_print_date($object->date_valid,"day",false,$outputlangs,true), '', 'R');

		if ($object->thirdparty->code_client)
		{
			$posy+=4;
			$pdf->SetXY($posx,$posy);
			$pdf->SetTextColor(0,0,60);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("CustomerCode")." : " . $outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		// Show list of linked objects
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size, $hookmanager);


		if ($showaddress)
		{
			//$conf->global->MAIN_INVERT_SENDER_RECIPIENT = 1;

			// Sender properties
			$carac_emetteur='';

			//$carac_emetteur .= pdf_build_address($outputlangs,$object->emetteur,$object->thirdparty,'','','target');
			$carac_emetteur = pdf_build_address($outputlangs,$object->thirdparty,$this->emetteur,$object->contact,$usecontact,'target');
			//die($carac_emetteur);
			//if ($carac_emetteur == -1)
		//	$carac_emetteur = pdf_build_address($outputlangs,$object->thirdparty,$this->emetteur,$object->contact,1,'target');
			// Recipient name
			if (! empty($usecontact))
			{
				// On peut utiliser le nom de la societe du contact
				if ($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT) $socname = $object->contact->socname;
				else $socname = $this->emetteur->name;
				$carac_client_name=$outputlangs->convToOutputCharset($socname);
			}
			else
			{
				$carac_client_name=$outputlangs->convToOutputCharset($this->emetteur->name);
			}

			// Show sender
			$posy=42;
			$posx=$this->marge_gauche;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->page_largeur-$this->marge_droite-80;
			$hautcadre=35;

			// Show sender frame
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx,$posy-5);
			$pdf->SetXY($posx,$posy);
			$pdf->SetFillColor(230,230,230);
			$pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);

			// Show sender name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetTextColor(0,0,60);
			$pdf->SetFont('','B',$default_font_size);
			$pdf->MultiCell(80, 3, $outputlangs->convToOutputCharset($carac_client_name), 0, 'L');

			// Show sender information
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($posx+2,$posy+8);
			$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');


			/*
			 *  Destinataire : participant si crée par user externe et participant != celui qui a crée l'inscription
			 */
			$userstat = new User($this->db);
			$userstat->fetch($object->fk_user_create);
			$contact_create = $userstat->fk_socpeople;

			if($userstat->societe_id && $object->fk_user_create != $object->fk_user_registered) {
				$contact_to_use = $contact_create;
			}
			else $contact_to_use = $object->fk_user_registered;

			$usecontact=false;
			if ($contact_to_use )
			{
				$usecontact=true;
				$result=$object->fetch_contact($contact_to_use);
			}


			if (!(isset($object->contact))){
			$us = new User($this->db);
			$us->fetch($object->fk_user_create);
			$object->contact = new Contact($this->db);
			$object->contact->fetch($us->contactid);
			}
			if (!(isset($object->thirdparty))){
				if (!(isset($us))){
				$us = new User($this->db);
				$us->fetch($object->fk_user_create);
			}
				$object->thirdparty = new Societe($this->db);
				$object->thirdparty->fetch($us->socid);
			}

//			die(var_dump($us));

			//$carac_client=pdf_build_address($outputlangs,$object->thirdparty,$this->emetteur,$object->contact,$usecontact,'target');
			$carac_client=pdf_build_address($outputlangs,$object->emetteur,$object->thirdparty,$object->contact,$usecontact,'target');
//			$carac_emetteur = pdf_build_address($outputlangs,$object->thirdparty,$this->emetteur,$object->contact,1,'target');
			//$object->thirdparty = new Contact($this->db);
			//$object->thirdparty->fetch();


			// Show recipient
			$posy=42;
			$posx=$this->page_largeur-$this->marge_droite-100;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->marge_gauche;

			// Show recipient frame
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx,$posy-5);
			$pdf->Rect($posx, $posy, 100, $hautcadre);
			$pdf->SetTextColor(0,0,0);

			// Show recipient name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell(100,4, $object->thirdparty->name , 0, 'L');//swap

			// Show recipient information
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($posx+2,$posy+4+(dol_nboflines_bis($carac_client_name,50)*4));
			$pdf->MultiCell(100,4, $carac_client, 0, 'L');
		}
	}

	/**
	 *   	\brief      Show footer of page
	 *   	\param      pdf     		PDF factory
	 * 		\param		object			Object invoice
	 *      \param      outputlangs		Object lang for output
	 * 		\remarks	Need this->emetteur object
	 */
	function _pagefoot(&$pdf,$object,$outputlangs)
	{
		return pdf_pagefoot($pdf,$outputlangs,'DELIVERY_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object);
	}

}

?>
