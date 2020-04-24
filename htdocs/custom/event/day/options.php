<?php
/* Copyright (C) 2014		Jean-François FERRY	<jfefe@aternatik.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		prodoptions.php
 *	\ingroup	prodoptions
 *	\brief		Manage option for product
 */

$res = 0;
if (! $res && file_exists("../main.inc.php")) {
	$res = @include("../main.inc.php");
}
if (! $res && file_exists("../../main.inc.php")) {
	$res = @include("../../main.inc.php");
}
if (! $res && file_exists("../../../main.inc.php")) {
	$res = @include("../../../main.inc.php");
}

if (! $res) {
	die("Main include failed");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

require_once "../class/event.class.php";
require_once "../class/day.class.php";
require_once "../class/eventoptions.class.php";
require_once("../lib/event.lib.php");
require_once('./fonctions.php');

// Load translation files required by the page
$langs->load("event@event");

$id=GETPOST('id','int');
$ref=GETPOST('ref','alpha');
$dayid		= GETPOST('dayid','int');
$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$cancel=GETPOST('cancel','alpha');
$key=GETPOST('key');
$parent=GETPOST('parent');

// Security check
if (! empty($user->societe_id)) $socid=$user->societe_id;
$fieldvalue = (! empty($id) ? $id : (! empty($ref) ? $ref : ''));
$fieldtype = (! empty($ref) ? 'ref' : 'rowid');
$result=restrictedArea($user,'produit|service',$fieldvalue,'product&product','','',$fieldtype);

$mesg = '';

$event = new Eventoptions($db);

/*
 * ACTIONS
 *
 */
// Action association d'un produit en tant qu'option
if ($action == 'add_prod' &&
$cancel <> $langs->trans("Cancel") &&
($user->rights->produit->creer || $user->rights->service->creer))
{

	$result = $event->fetch($dayid,$ref);
	if ( $result )
	{

	}
	$error=0;
	for ( $i=0; $i < GETPOST("max_prod",'int'); $i++ )
	{
		if ( GETPOST("prod_id_chk".$i,'int') != "" )
		{
			if ( $event->add_event_option($dayid, GETPOST("prod_id_".$i,'int') ) > 0 )
			{
				$action = 'edit';
				$nb_add++;
			}
			else
			{
				$error++;
				$action = 're-edit';
				if ($event->error == "isFatherOfThis") $mesg = $langs->trans("ErrorAssociationIsFatherOfThis");
				else $mesg=$event->error;

				setEventMessage($mesg,'error');
			}
		}
		else
		{
			if ($event->del_event_option($dayid, $_POST["prod_id_".$i]) > 0)
			{
				$action = 'edit';
			}
			else
			{
				$error++;
				$action = 're-edit';
				setEventMessage($event->error,'errors');
			}
		}
	}
	if (! $error)
	{
		setEventMessage($langs->trans('EventOptionsSuccessfullyEdit',isset($nb_add) ? $nb_add : 0));
		header("Location: ".$_SERVER["PHP_SELF"].'?dayid='.$event->id);
		exit;
	}
}


/*
 * VIEW
 *
 */

// action recherche des produits par mot-cle et/ou par categorie
	$current_lang = $langs->getDefaultLang();

	$sql = 'SELECT DISTINCT p.rowid, p.ref, p.label, p.fk_product_type as type';
	if (! empty($conf->global->MAIN_MULTILANGS)) $sql.= ', pl.label as labelm, pl.description as descriptionm';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'product as p';
	$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product as cp ON p.rowid = cp.fk_product';
	if (! empty($conf->global->MAIN_MULTILANGS)) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_lang as pl ON pl.fk_product = p.rowid AND lang='".($current_lang)."'";
	$sql.= ' WHERE p.entity IN ('.getEntity("product", 1).')';
	if ($key != "")
	{
		if (! empty($conf->global->MAIN_MULTILANGS))
		{
			$sql.= " AND (p.ref LIKE '%".$key."%'";
			$sql.= " OR pl.label LIKE '%".$key."%')";
		}
		else
		{
			$sql.= " AND (p.ref LIKE '%".$key."%'";
			$sql.= " OR p.label LIKE '%".$key."%')";
		}
	}
	if (! empty($conf->categorie->enabled) && ! empty($parent) && $parent != -1)
	{
		$sql.= " AND cp.fk_categorie ='".$db->escape($parent)."'";
	}
	$sql.= " ORDER BY p.ref ASC";

	$resql = $db->query($sql);

$productstatic = new Product($db);
$form = new Form($db);

llxHeader("","",$langs->trans("CardProduct".$product->type));



if ($dayid || $ref)
{
	$result = $event->fetch($dayid,$ref);
	if ($result)
	{

		$head = eventday_prepare_head($event);
		dol_fiche_head($head, 'options', $langs->trans("EventDay"),0,($event->public?'event@event':'event@event'));

		
		
   ///////////////////:: recherche jour pecedent jour suivant /////////////////////////////////
		 $js = jour_suivant($db,$event->fk_event,dol_print_date($event->date_event, '%Y-%m-%d'));
		 $jp = jour_precedent($db,$event->fk_event,dol_print_date($event->date_event, '%Y-%m-%d'));
		 
         //////////////////////////////////////////////////////////////////			

		   ?><div style="vertical-align: middle">
					<div class="pagination paginationref">
						<ul class="right">
						<!--<li class="noborder litext">
						<a href="/dolibarr/societe/list.php?restore_lastsearch_values=1">Retour liste</a>
						</li>-->
						<?php 
						if($jp!='')
							echo '<li class="pagination"><a accesskey="p" href="'.DOL_URL_ROOT.'/custom/event/day/options.php?dayid='.$jp.'"><i class="fa fa-chevron-left"></i></a></li>';
						else
							echo '<li class="pagination"><span class="inactive"><i class="fa fa-chevron-left opacitymedium"></i></span></li>';
						if($js!='')
							echo '<li class="pagination"><a accesskey="p" href="'.DOL_URL_ROOT.'/custom/event/day/options.php?dayid='.$js.'"><i class="fa fa-chevron-right"></i></a></li>';
						else
							echo '<li class="pagination"><span class="inactive"><i class="fa fa-chevron-right opacitymedium"></i></span></li>';
						?>
						</ul></div>
				</div>
				
		  <?php		
		
		
		print '<table class="border" width="100%">';

		$nblignes=6;

		// Label
		print '<tr><td width="30%">'.$langs->trans("LabelDay").'</td><td>'.$event->label.'</td>';
		print '</tr>';
		
		// Date
		$dayofweek = strftime("%w",$event->date_event);
		print '<tr><td>'.$langs->trans("EventDayDate").'</td><td>'. $langs->trans("Day".$dayofweek) . ' ' . dol_print_date($event->date_event,'daytext').'</td>';
		print '</tr>';
		
		// Time start
        print '<tr><td>' . $langs->trans("TimeStart") . '</td><td>' . $event->time_start . '</td></tr>';

        // Time end
        print '<tr><td>' . $langs->trans("TimeEnd") . '</td><td>' . $event->time_end . '</td></tr>';

		$prodoptions = $event->getProdOptionsForDay(); //Parent Products
		// Number of option products
		print '<tr><td>'.$langs->trans("EventProductOptionsNumber").'</td><td>'.count($prodoptions).'</td>';

		print "</table>\n";
		dol_fiche_end();


		if ($action <> 'edit' && $action <> 'search' && $action <> 're-edit')
		{
			/*
			 *  En mode visu
			 */


			/* ************************************************************************** */
			/*                                                                            */
			/* Barre d'action                                                             */
			/*                                                                            */
			/* ************************************************************************** */

			print "\n<div class=\"tabsAction\">\n";

			if ($action == '')
			{
				if ($user->rights->produit->creer || $user->rights->service->creer)
				{
					print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=edit&amp;dayid='.$event->id.'">'.$langs->trans("EditEventDayOptions").'</a>';
				}
			}

			print "\n</div>\n";

			if (count($prodoptions) > 0)
			{
				print_titre($langs->trans("EventProductOptionsList"));

				print '<table class="nobordernopadding " width="100%">';
				print '<tr class="liste_titre">';
				print '<th class="liste_titre" width="20%">'.$langs->trans("Ref").'</th>';
				print '<th class="liste_titre" width="40%">'.$langs->trans("Label").'</th>';
				print '<th class="liste_titre" width="20%" align="right">'.$langs->trans("Price").'</th>';
				if (! empty($conf->stock->enabled)) print '<th class="liste_titre" align="center">'.$langs->trans("Stock").'</th>';
				print '</tr>';

				$var='';
				foreach($prodoptions as $value)
				{
					$idprod= $value["id"];
					$productstatic->id=$idprod;
					$productstatic->type=$value["fk_product_type"];
					$productstatic->ref=$value['ref'];
					$productstatic->label=$value['label'];
					$productstatic->price=$value['price'];
					if (! empty($conf->stock->enabled)) $productstatic->load_stock();
					$var=!$var;
					print "\n<tr ".$bc[$var].">";
					print '<td>'.$productstatic->getNomUrl(1,'prodoptions').'</td>';
					print '<td>'.str_replace("\'", "'", $productstatic->label).'</td>';
					print '<td> '.price($productstatic->price).' '.$conf->currency.'</td>';
					if (! empty($conf->stock->enabled)) print '<td>'.$langs->trans("Stock").' : <b>'.$productstatic->stock_reel.'</b></td>';
					print '</tr>';
				}
				print '</table>';

			}
		}



		/*
		 * Fiche en mode edition
		*/
		if (($action == 'edit' || $action == 'search' || $action == 're-edit') && ($user->rights->produit->creer || $user->rights->service->creer))
		{


			print_fiche_titre($langs->trans("ProductToAddSearch"),'','');
			print '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
			print '<table class="border" width="100%"><tr><td>';
			print '<table class="nobordernopadding">';

			print '<tr><td>';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print $langs->trans("KeywordFilter").' &nbsp; ';
			print '</td>';
			print '<td><input type="text" name="key" value="'.$key.'">';
			print '<input type="hidden" name="action" value="search">';
			print '<input type="hidden" name="dayid" value="'.$event->id.'">';
			print '</td>';
			print '<td rowspan="'.$rowspan.'" valign="middle">';
			print '<input type="submit" class="button" value="'.$langs->trans("Search").'">';
			print '</td></tr>';
			if (! empty($conf->categorie->enabled))
			{
				print '<tr><td>'.$langs->trans("CategoryFilter").' &nbsp; </td>';
				print '<td>'.$form->select_all_categories(0, $parent).'</td></tr>';
			}

			print '</table>';
			print '</td></td></table>';
			print '</form>';

				print '<br>';
				print '<form action="'.$_SERVER['PHP_SELF'].'?dayid='.$event->id.'" method="post">';
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
				print '<input type="hidden" name="action" value="add_prod">';
				print '<input type="hidden" name="dayid" value="'.$event->id.'">';
				print '<table class="nobordernopadding" width="100%">';
				print '<tr class="liste_titre">';
				print '<th class="liste_titre">'.$langs->trans("Ref").'</td>';
				print '<th class="liste_titre">'.$langs->trans("Label").'</td>';
				print '<th class="liste_titre" align="center">'.$langs->trans("AddDel").'</td>';
				print '</tr>';
				if ($resql)
				{
					$num = $db->num_rows($resql);
					$i=0;
					$var=true;

					if($num == 0) print '<tr><td colspan="4">'.$langs->trans("NoMatchFound").'</td></tr>';

					while ($i < $num)
					{
						$objp = $db->fetch_object($resql);

						$is_pere=0;

						if (count($prodoptions) > 0)
						{
							foreach($prodoptions as $key => $value)
							{
								if ($value['id']==$objp->rowid)
								{
									$is_pere=1;
								}
							}
						}

						$var=!$var;
						print "\n<tr ".$bc[$var].">";
						$productstatic->id=$objp->rowid;
						$productstatic->ref=$objp->ref;

						print '<td>'.$productstatic->getNomUrl(1,'',24).'</td>';
						$labeltoshow=$objp->label;
						if ($conf->global->MAIN_MULTILANGS && $objp->labelm) $labeltoshow=$objp->labelm;

						print '<td>'.$labeltoshow.'</td>';

						if($is_pere)
						{
							$addchecked = ' checked="checked"';

						}
						else
						{
							$addchecked = '';
						}

						print '<td align="center"><input type="hidden" name="prod_id_'.$i.'" value="'.$objp->rowid.'">';
						print '<input type="checkbox" '.$addchecked.'name="prod_id_chk'.$i.'" value="'.$objp->rowid.'"></td>';

						print '</tr>';

						$i++;
					}

				}
				else
				{
					dol_print_error($db);
				}
				print '</table>';
				print '<input type="hidden" name="max_prod" value="'.$i.'">';

				if($num > 0)
				{
					print '<br><center><input type="submit" class="button" value="'.$langs->trans("Add").'/'.$langs->trans("Update").'">';
					print ' &nbsp; &nbsp; <a class="button button_napf" href="'.DOL_URL_ROOT.'/custom/event/day/options.php?dayid='.$dayid.'">'.$langs->trans("Cancel").'</a>';
					print '</center>';
				}

				print '</form>';

		}
	}
}


// Put here content of your page
// Example 1 : Adding jquery code
echo '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_needroot();
	});
});
</script>';


// Example 2 : Adding jquery code



// End of page
llxFooter();
$db->close();
