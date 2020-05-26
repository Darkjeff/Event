<?php

class ActionsEvent
{

	var $db;
	var $error;
	var $errors=array();

	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
	}
	/** Overloading the doActions function : replacing the parent's function with the one below
	 *  @param      parameters  meta datas of the hook (context, etc...)
	 *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 *  @param      action             current action (if set). Generally create or edit or null
	 *  @return       void
	 */

	 function paypaypal($parameters, &$object, &$action)
	 {
	 if (in_array('paypal', explode(':', $parameters['context']))){

		require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';

		global $conf, $db, $user, $langs;
		$ref = $parameters['ref_facture'];
		$user->fetch(1);

		$facture = new Facture($db);
		$nuser = new User($db);
		$product = new Product($db);

		$facture->fetch('', $ref);
		$facture->validate($user);
		$facture->set_paid($user);
		$facture->fetch_lines();

		$nuser->fetch($facture->fk_account);
		$id_product = $facture->lines[0]->fk_product;
		$product->fetch($id_product);

		$extrafields = new ExtraFields($db);
		$extralabels=$extrafields->fetch_name_optionals_label('product');
		$product->fetch_optionals($product->id,$extralabels);

		$extrafields = new Extrafields($db);
		$extralabels=$extrafields->fetch_name_optionals_label('user');
		$nuser->fetch_optionals($nuser->id,$extralabels);

		$nb_unit_buy = $product->array_options['options_nbunitbuy'];
		$nuser->array_options['options_event_counter'] += $nb_unit_buy * $facture->lines[0]->qty;
		$nuser->update($user);
	    echo '<script>parent.self.location="'.DOL_URL_ROOT.'/custom/event/public/index.php";</script>';
		}
	 }


	function printSearchForm($parameters, $object, $action)
	{
		global $langs;

		$error = '';
		if (in_array('searchform',explode(':',$parameters['context'])))
		{
			$title=$langs->trans('RegistrationRefShort');
			$htmlinputname='query';
			$urlobject=dol_buildpath('/event/index.php',1);

			$ret='';
			$ret.='<div class="menu_titre">';
			$ret.='<a class="vsmenu" href="'.$urlobject.'">';
			$ret.=img_object('','event_registration@event').' '.$title.'</a><br>';
			$ret.='</div>';
			$ret.='<form action="'.dol_buildpath('/event/index.php',1).'" method="post">';
			$ret.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			$ret.='<input type="hidden" name="action" value="search">';
			$ret.='<input type="text" class="flat" ';
			if (! empty($conf->global->MAIN_HTML5_PLACEHOLDER)) $ret.=' placeholder="'.$langs->trans("SearchOf").' '.strip_tags($title).'"';
			else $ret.=' title="'.$langs->trans("SearchOf").' '.strip_tags($title).'"';
			$ret.=' name="'.$htmlinputname.'" size="10" />&nbsp;';
			$ret.='<input type="submit" class="button" value="'.$langs->trans("Go").'">';
			$ret.="</form>\n";
		}
		if (! $error)
		{
			$this->results = array();
			$this->resprints = $ret;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
}
