<?php

require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';


/**
 * Class InterfaceEventPayment
 *
 * Fonctions triggers des actions de paiements liées au module Event.
 *
 */
class InterfaceEventPayment
{
    var $db;

    /**
     * InterfaceEventPayment constructor.
     *
     * @param $db   DoliDB  $db     Database Handler
     */
    function __construct($db)
    {
        $this->db = $db ;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "business";
        $this->description = "Triggers of this module allows to manage payment result for an event.";
        $this->version = '1.1';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'event@event';
        $this->disabled_if_workflow = false;
    }


    /**
     *   \brief      Renvoi nom du lot de triggers
     *   \return     string      Nom du lot de triggers
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   \brief      Renvoi descriptif du lot de triggers
     *   \return     string      Descriptif du lot de triggers
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   \brief      Renvoi version du lot de triggers
     *   \return     string      Version du lot de triggers
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') return $langs->trans("Development");
        elseif ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

    /**
     *      \brief      Fonction appelee lors du declenchement d'un evenement Dolibarr.
     *                  D'autres fonctions run_trigger peuvent etre presentes dans core/triggers
     *      \param      action      Code de l'evenement
     *      \param      object      Objet concerne
     *      \param      user        Objet user
     *      \param      lang        Objet lang
     *      \param      conf        Objet conf
     *      \return     int         <0 if fatal error, 0 si nothing done, >0 if ok
     */


    /**
     * @param $action
     * @param $object   stdClass
     * @param $user     User
     * @param $langs
     * @param $conf
     * @throws Exception
     */
	function run_trigger($action,$object,$user,$langs,$conf)
    {
        global $conf, $db, $user, $langs;;

        // Utilisation de l'utilisateur n°1 (généralement l'admin) pour le traitement des factures
        $user->fetch(1);

        $customer = new User($db);
        $product = new Product($db);


    	// Le paiement a été validé, le trigger est appelé
        if ($action == 'PAYMENTONLINE_PAYMENT_OK')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

        	$datePaiement = date("d/m/Y à H:i:s", strtotime($object->resArray['TIMESTAMP']));


        	// ETAPE 1 : Validation de la facture

        	// Récupération de la référence de la facture
            $facture = new Facture($db);
            $facture->fetch('', $object->ref);

            // Validation de la facture et définition comme payée
            $facture->validate($user);
            $facture->set_paid($user);
            $facture->update_note("Paiement en ligne le $datePaiement", '_private');

            // ETAPE 2 : Incrémentation du compte utilisateur

            $facture->fetch_lines();
            $customer->fetch($facture->fk_account);

            $id_product = $facture->lines[0]->fk_product;
            $product->fetch($id_product);

            $extrafields = new ExtraFields($db);
            $extralabels=$extrafields->fetch_name_optionals_label('product');
            $product->fetch_optionals($product->id,$extralabels);

            $extrafields = new Extrafields($db);
            $extralabels=$extrafields->fetch_name_optionals_label('user');
            $customer->fetch_optionals($customer->id,$extralabels);

            $nb_unit_buy = $product->array_options['options_nbunitbuy'];
            $customer->array_options['options_event_counter'] += $nb_unit_buy * $facture->lines[0]->qty;
            $customer->update($user);
            echo '<script>parent.self.location="'.DOL_URL_ROOT.'/custom/event/public/index.php";</script>';

			$ret = 1;
        }
        return $ret;

    }
}

