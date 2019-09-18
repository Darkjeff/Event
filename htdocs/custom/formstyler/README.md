FormStyler / builder for Dolibarr ERP/CRM
===============================

Complementary module for Dolibarr 3.5+ which help to develop forms.

# Example #
--------------------

## Start new form ##

~~~~~~~~~~~~~~~~~~~~~
$formstyler = new FormStyler($db,'add_time_basket');
$params = array(
        'action' => 'confirm_add_object',
        'socid'	=> $socid
);
$formstyler->printFormBegin('POST', $_SERVER['PHP_SELF'], $params);</code>
~~~~~~~~~~~~~~~~~~~~~

## Show input field  ##

~~~~~~~~~~~~~~~~~~~~~
FormStyler::printInputField('comment',$langs->trans('CommentOnPayment'),$value,1,'text');
~~~~~~~~~~~~~~~~~~~~~

## Show price field ##

~~~~~~~~~~~~~~~~~~~~~
FormStyler::printInputField('amount',$langs->trans('PaymentAmount'),GETPOST('amount'),1,'','price');
~~~~~~~~~~~~~~~~~~~~~

## Show payments types select list ##

~~~~~~~~~~~~~~~~~~~~~
FormStyler::printSelectList('paiementcode',$langs->trans('PaymentMode'),GETPOST('paiementcode'),'',1,'selectpaiementcode','types_paiements');
~~~~~~~~~~~~~~~~~~~~~

## Show date select ##

~~~~~~~~~~~~~~~~~~~~~
$formstyler->printSelectDate('validity',$langs->trans('ValidityDate'),'');
~~~~~~~~~~~~~~~~~~~~~

## Show submit buttons ##

~~~~~~~~~~~~~~~~~~~~~
FormStyler::printFormSubmitButton('confirm_add',$langs->trans('Create'));
~~~~~~~~~~~~~~~~~~~~~

## End form ##

~~~~~~~~~~~~~~~~~~~~~
$formstyler->printFormEnd();
~~~~~~~~~~~~~~~~~~~~~



## Build an entire form with a YAML file descriptor ##

Create file forms/myform.fields.yaml :

~~~~~~~~~~~~~~~
---
name: my_form_name
structure:
  - type: fieldset
    id: fieldset_one
    legend: MYFieldsetLegend_one
    fields:
      - name: field_one
        label: MyLabelFieldOne
      - name: field_two
        label: MyLabelFieldOne
  - type: fieldset
    id: fieldset_two
    legend: MYFieldsetLegend_two
    fields:
      - name: field_three
        label: MyLabelFieldThree
~~~~~~~~~~~~~~~

Then in your page, use this code :

~~~~~~~~~~~~~~~~~~
$form=new FormStyler($db,'my_form');
$params = array('action' => "add", 'backtopage' => $backtopage);
$filename = dol_buildpath('forms/myform.fields.yaml');
$form->printFormBegin('POST', $_SERVER['PHP_SELF'], $params);

// Magic method :)
$form->processFormStructure($filename);

$form->printFormSubmitButton('ok','go');
$form->printFormEnd();
~~~~~~~~~~~~~~~~~~

See validInputFieldStruct() function to see all possibilities for yaml file.
