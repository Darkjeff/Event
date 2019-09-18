<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$res = 0;
if (!$res && file_exists("../main.inc.php")) {
    $res = @include '../main.inc.php';
}
// to work if your module directory is into dolibarr root htdocs directory
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include '../../main.inc.php';
}
// to work if your module directory is into a subdir of root htdocs directory
if (!$res) {
    die("Include of main fails");
}

// Change this following line to use the correct relative path from htdocs
include_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/class/html.formcontract.class.php';
include_once './class/formstyler.class.php';

include_once DOL_DOCUMENT_ROOT . "/core/lib/company.lib.php";

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");

llxHeader('', 'FormStylerDemo', '');

print_fiche_titre($langs->trans("FormStylerDemo"));

//$form_names = getFormFieldsNames('forms/skeleton.fields.yaml');

dol_fiche_head();

$form = new FormStyler($db, 'formstyler_demo');
$params = array('action' => "add", 'backtopage' => $backtopage);
$form->printFormBegin('POST', $_SERVER['PHP_SELF'], $params);

// Magic method :)
$form->processFormStructure('forms/skeleton.fields.yaml');

dol_fiche_end();
$form->printFormSubmitButton('ok', 'Done !');
$form->printFormEnd();
