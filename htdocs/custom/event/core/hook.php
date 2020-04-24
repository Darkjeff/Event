<?php
//Execution du hook
$parameters=array('ref_facture' => $object->ref);
include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
$hookmanager=new HookManager($db);
$hookmanager->initHooks(array('paypal'));
$reshook=$hookmanager->executeHooks('paypaypal',$parameters,$object,$action); // See description below}
//Fin du hook
