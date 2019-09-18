<?php

/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2014-2016 Jean-François Ferry    <jfefe@aternatik.r>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 * FormStyler class
 *
 * \defgroup    formstyler      Module FormStyler
 * \brief       Improve forms
 *
 * \file       formstyler/formstyler.class.php
 * \ingroup    formstyler
 * \brief      Class for formstyler object
 *
 */
require_once DOL_DOCUMENT_ROOT . "/core/class/html.form.class.php";
require_once "spyc.class.php";

/**
 * Class file to build forms into Dolibarr ERP/CRM
 *
 * @package formstyler
 *
 */
class FormStyler extends Form
{

    /**
     * @var DoliDB  $db To store db handler
     */
    public $db;

    /**
     *
     * @var string  $error To return error code (or message)
     */
    public $error; // !<
    /**
     *
     * @var array   $errors  To return several error codes (or messages)
     */
    public $errors = array(); // !<
    /**
     *
     * @var string  $element     Id that identify managed objects
     */
    public $element = 'formstyler'; // !<

    /**
     *
     * @var string  $name   Form name
     */
    public $name; // Form name

    static $cols = 40;

    static $rows = 5;

    /**
     *
     * @var array   $fields_input_struct
     */
    static $input_fields_description = array(
        'name',
        'label',
        'value',
        'selected',
        'required',
        'id',
        'type', // input type
        'more_class',
        'more_options',
    );

    /**
     *
     * @var array   Form fields
     * @todo Implement ir!
     */
    public $fields;

    /**
     * Constructor
     *
     * @param DoliDb    $db     Database handler
     * @param string    $name   Form name
     */
    public function __construct($db, $name)
    {
        $this->db = $db;
        $this->name = $name;
    }

    /**
     * Return or print html string
     *
     * @param string    $out            Output string
     * @param bool      $return_html    If true, output will be returned instead of printed
     *
     * @return mixed    Return string or print it
     */
    private function return_value($out, $return_html = true)
    {

        if ($return_html) {
            return $out;
        } else {
            print $out;
            return '';
        }
    }

    public function getFormConfig($filename)
    {
        // Read form description into YAML file
        $yaml_conf = Spyc::YAMLLoad($filename);
        return $yaml_conf;
    }

    public function processFormStructure($filename, &$object = '')
    {
        $yaml_conf = self::getFormConfig($filename);

        foreach ($yaml_conf['structure'] as $form_element) {
            if ($form_element['type'] == "fieldset") {

                foreach ($form_element['fields'] as $key => $field) {
                    // If Object given in parameters, set fields values
                    if (isset($object->{$field['name']})) {
                        $form_element['fields'][$key]['value'] = $object->{$field['name']};
                    }
                }
                Formstyler::printInputFieldsGroup(
                    $form_element['legend'], $form_element['fields'], $form_element['id']
                );
            }
        }
    }

    /**
     * Begin a div .edit_field
     *
     * @param string    $name           Name of field
     * @param string    $more_class     More CSS class to add to this field
     * @param bool      $return_html    By default, string will be returned by function. Set to False in order to print result
     * @fixme unimplementded $outputmode 0=HTML select string, 1=Array
     *
     * @return FormStyler::return_value()
     */
    public static function fieldEditBegin($name, $more_class = "", $return_html = true)
    {
        $out = '';
        $out .= '<div class="edit_field edit_field_' . $name . (!empty($more_class) ? ' ' . $more_class : '') . '">';

        return self::return_value($out, $return_html);
    }

    /**
     * End a div .editfield
     *
     * @param bool  $return_html    By default, string will be returned by function. Set to False in order to print result
     * @return FormStyler::return_value()
     */
    public static function fieldEditEnd($return_html = true)
    {
        $out = '';
        $out .= "</div><!--  edit_field -->\n";

        return self::return_value($out, $return_html);
    }

    /**
     * Get html string to begin field value
     *
     * @param string    $name
     * @param bool      $return_html    By default, string will be returned by function. Set to False in order to print result
     *
     * @return FormStyler::return_value()
     */
    public static function fieldValueBegin($name, $return_html = true)
    {
        $out = '';
        $out .= '<div class="edit_value edit_value_' . $name . '">';

        return self::return_value($out, $return_html);
    }

    /**
     * End a div .editvalue
     *
     * @param bool  $return_html    If true string will be returned instead of print. By default, string is printed by the method
     *
     * @return mixed    FormStyler::return_value()
     */
    public static function fieldValueEnd($return_html = true)
    {
        $out = '';
        $out .= "</div><!--  / edit_value -->\n";

        return self::return_value($out, $return_html);
    }

    /**
     * Get label field html string
     *
     * @param string    $name           "for" attribute value
     * @param string    $label          String for label
     * @param int       $required       Required or not
     * @param bool      $return_html    By default, string will be returned by function. Set to False in order to print result
     *
     * @return FormStyler::return_value()
     */
    public static function labelField($name, $label, $required = 0, $return_html = true)
    {
        $out = '<div class="edit_label edit_label_' . $name . '">';
        $out .= '<label for="' . $name . '" ' . ($required ? 'class="fieldrequired"' : '') . '>' . $label . '</label>';
        $out .= '</div>';

        return self::return_value($out, $return_html);
    }

    /**
     * Get an input field
     *
     * @param string    $name           Input name
     * @param string    $label          Input label text (already translate)
     * @param string    $value          Input value
     * @param int       $required       If input is required or not
     * @param string    $id             Id tag for input. If empty, use $name value
     * @param string    $type_input     Display special input as a price (with currency).
     *      Possible choices are :
     *          - text (default)
     *          - price
     *          - date
     * @param string    $more_class     More HTML class to add to the field
     * @param array     $more_options   Array with more options
     * @param bool $return_html                 By default, string will be returned by function.
     *                                          Set to False in order to print result
     * @return FormStyler::return_value()
     *
     * @fixme Unimplemented $more_class and $more_options
     */
    public static function getInputField($name, $label, $value = '', $required = 0, $id = '', $type_input = 'text', $more_class = '', $more_options = array(), $return_html = true)
    {
        global $conf;

        $id = !empty($id) ? $id : $name;

        $out = '';

        $out.= self::fieldEditBegin($name, $more_class, $return_html);

        $out.= self::labelField($id, $label, $required);

        $out.= self::fieldValueBegin($name);

        switch ($type_input) :
            case "price" :
                $out.= '<input type="text" name="' . $name . '" id="' . $id . '" class="flat" size="12" value="' . $value . '" />' . $conf->currency;
                break;
            case "textarea" :
                $out.= '<textarea name="' . $name . '" id="' . $id . '" class="flat" cols="'.self::$cols.'" rows="'.self::$rows.'">' . $value . '</textarea>';
                break;
            case "note" :
                $out.= '<textarea name="' . $name . '" id="' . $id . '" class="flat">' . $value . '</textarea>';
                break;
            case "date" :
                $out.= parent::select_date($value, $name, $h, $m, $empty = 0, '', 1, 1, 1);
                break;
            case "yesno" :
                $out.= self::selectyesno($name,$value);
                break;
            case "file" :
                $out.= '<input type="file" name="' . $name . '" id="' . $id . '" class="flat" />';
                break;
            case "checkbox" :
                
            default :
                $out.= '<input type="text" name="' . $name . '" id="' . $id . '" class="flat" size="80" value="' . $value . '" />';
                break;
        endswitch;
        $out .= self::fieldValueEnd();

        $out .= self::fieldEditEnd();

        return self::return_value($out, $return_html);
    }

    /**
     * Print input field
     *
     * @param string $name                  Input name
     * @param string $label                 Input label text (already translate)
     * @param string $value                 Value
     * @param int $required                 Required or not
     * @param string $id                    Value for id html attibute (if different form name)
     * @param string $type_input            Input type (text, price)
     * @param string $more_class            More css class to add
     * @param array $more_options           More options
     * @return FormStyler::getInputField()
     */
    public function printInputField($name, $label, $value = '', $required = 0, $id = '', $type_input = 'text', $more_class = '', $more_options = array())
    {
            return self::getInputField($name, $label, $value, $required, $id, $type_input, $more_class, $more_options, false);
    }

    /**
     * Print several input fields
     *
     * @use printInputField()
     *
     * @param array $input_fields       Input fields structure.
     *                                  Ex array('name' => 'myinputname','label' => "MyTranslationChain", 'value'=>GETPOST('myinputname'), ...)
     * @see Formstyler::$input_fields_description for used keys into $input_fields array
     */
    public function printInputFields($input_fields)
    {
        $fields = array();
        $out = '';

        $fields = array_map("validInputFieldStruct", $input_fields);
        //$fields = array_map("combineFormInputFields", $fields);

        foreach ($fields as $field) {
            $name = $field['name'];
            $label = $field['label'];
            $selected = $field['value'];
            $required = $field['required'];
            $values = $field['values'];
            $options = $field['more_options'];

            switch ($field['type']):
        case "radio_yesno":
            $out .= self::printRadiosFieldWithLabel($name, $label, $selected, array('1' => 'Yes', '0' => "No"), '', '');
            break;
        case "radio":
            $out .= self::printRadiosFieldWithLabel($name, $label, $selected, $values, '', '');
            break;
        case "select":
            $out .= self::printSelectList($name, $label, $values, $selected, $required, $id, $options['type_select'], $more_classs, true);
            break;
        case "function":
            $function = $field['function'];
            $args = $field['args'];

            $out .= self::printFieldFromFunction($name, $label, $function, $args, true);
            break;

        case "method":
            $classname = $field['classname'];
            $method = $field['method'];
            $args = $field['args'];
            $out .= self::printFieldFromMethod($name, $label, $classname, $method, $args, true);
            break;
        default:
            $out .= self::getInputField($field['name'], $field['label'], $field['value'], $field['required'], $field['id'], $field['type'], $field['more_class'], $fields['more_options']);
            break;
            endswitch;
        }

        return self::return_value($out, false);
    }

    /**
     * Print a fieldset with legend and included fields
     *
     * @global type $langs
     * @param type $fieldset_label
     * @param type $fields
     */
    public function printInputFieldsGroup($fieldset_label, $fields, $id = '')
    {
            global $langs;

            self::printBeginFieldset($id, '', false);
            self::printFormLegend($langs->trans($fieldset_label), false);
            self::printInputFields($fields, false);
            self::printEndFieldset(false);

    }

    /**
     * Print a group of fields
     *
     * @use FormStyler::setInputFieldByLabel()  Set correct properties to the input fields
     * @uses setInputLabelTransKey()            Labels are translated
     *
     *
     * @param type $fieldset_label
     * @param type $names
     * @param type $key_trans
     * @return type
     */
    public function printInputFieldsGroupFromNames($fieldset_label, $names, $key_trans = "FormAddLabel_")
    {
        // Build an array with correct structure for printInputFields method
        $fields = array_map("setInputFieldByLabel", $names);
        // Add translation key
        array_walk($fields, "setInputLabelTransKey", $key_trans);
        return self::printInputFieldsGroup($fieldset_label, $fields);
    }

    /**
     * Print select date field
     *
     * @param string    $name           Input name
     * @param string    $label          Input label text (already translate)
     * @param string    $value          Input value
     * @param int       $required       If input is required or not
     * @param string    $id             Id tag for input. If empty, use $name value
     * @param int       $h              Show hour
     * @param int       $m              Show day
     * @param int       $empty          Show empty
     * @param int       $d              Show d
     * @param int       $addnowbutton   Show actual date into select date form
     * @param string    $more_class     Add more CSS class
     * @param bool      $return_html
     * @return FormStyler::return_value()
     */
    public function printSelectDate($name, $label, $value = '', $required = 0, $id = '', $h = 0, $m = 0, $empty = 0, $d = 1, $addnowbutton = 0, $more_class = '', $return_html = true)
    {
        $id = !empty($id) ? $id : $name;

        $out = '';
        $out .= self::fieldEditBegin($name, $more_class);

        $out .= self::labelField($id, $label, $required);

        $out .= self::fieldValueBegin($name);
        $out .= parent::select_date($value, $name, $h, $m, $empty, '', $d, $addnowbutton, 1);

        $out .= self::fieldValueEnd();

        $out .= self::fieldEditEnd();

        return self::return_value($out, $return_html);
    }

    /**
     * Return an html block with a select combo box to choose yes or no
     *
     * @param string    $htmlname       Name of html select field
     * @param string    $label          Value for label
     * @param string    $value          Pre-selected value
     * @param bool      $required       Field required or not
     * @param int       $option         0 return yes/no, 1 return 1/0
     * @param bool      $disabled       Disabled field (TODO)
     * @param bool      $return_html    By default, string will be returned by function. Set to False in order to print result
     *
     * @return FormStyler::return_value()
     */
    public static function fieldSelectYesNo($htmlname, $label, $value = '', $required = false, $option = 0, $disabled = false, $return_html = true)
    {
        $out = '';
        $out .= self::fieldEditBegin($htmlname);

        $out .= self::labelField($htmlname, $label, $required);

        $out .= self::fieldValueBegin($htmlname);
        $out .= parent::selectyesno($htmlname, $value, $option, $disabled);
        $out .= self::fieldValueEnd();

        $out .= self::fieldEditEnd();

        return self::return_value($out, $return_html);
    }

    /**
     * Get form beginning string
     *
     * @param string    $method     Form method (GET or POST)
     * @param string    $url_action URL form action
     * @param array     $params     Hidden params
     * @param bool      $return_html    By default, string will be returned by function.
     *                                  Set to False in order to print result
     * @return  FormStyler::return_value()
     */
    public function getFormBegin($method = 'GET', $url_action = '', $params = array(), $return_html = true)
    {
        $out = '';
        $out .= "<!-- BEGIN FORM " . $this->name . " -->\n";
        $out .= '<form action="' . $url_action . '" id="' . $this->name . '"  name="' . $this->name . '" method="' . $method . '" class="dol_form" enctype="multipart/form-data">';
        $out .= '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';

        if (is_array($params) && count($params) > 0) {
            foreach ($params as $key => $value) {
                $out .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
            }
        }

        return self::return_value($out, $return_html);
    }

    /**
     * Print html form beginning
     *
     * @param string    $method     Form method (GET or POST)
     * @param string    $url_action URL form action
     * @param array     $params     Hidden params
     * @return FormStyler::getFormBegin()
     */
    public function printFormBegin($method = 'GET', $url_action = '', $params = array())
    {

        return self::getFormBegin($method, $url_action, $params, false);
    }

    /**
     * Get string of form end
     *
     * @param bool      $return_html    By default, string will be returned by function.
     *                                  Set to False in order to print result
     * @return FormStyler::return_value()
     */
    public function getFormEnd($return_html = true)
    {
        $out = '';
        $out .= '</form>';
        $out .= "<!-- END FORM " . $this->name . " -->\n";

        return self::return_value($out, $return_html);
    }

    /**
     * Print html form end
     *
     * @return FormStyler::getFormEnd()
     *
     */
    public function printFormEnd()
    {
        return self::getFormEnd(false);
    }

    /**
     * Print form legend
     *
     * @param string    $label  String already translated
     * @param bool      $return_html    By default, string will be returned by function.
     *                                  Set to False in order to print result
     */
    public static function printFormLegend($label, $return_html = true)
    {
        $out = '<legend>' . $label . '</legend>';
        return self::return_value($out, $return_html);
    }

    /**
     * Print begin of fieldset
     *
     * @param string    $more_class     More class to add
     * @param bool      $return_html    By default, string will be returned by function.
     *                                  Set to False in order to print result
     * @return type
     */
    public static function printBeginFieldset($id = '', $more_class = "", $return_html = true)
    {
        $out = '<fieldset' . (!empty($id) ? ' id="' . $id . '"' : '') . (!empty($more_class) ? ' class="' . $more_class . '"' : '') . '>';
        return self::return_value($out, $return_html);
    }

    /**
     * Print end of fieldset
     *
     * @param type $return_html
     */
    public static function printEndFieldset($return_html = true)
    {
        $out = '</fieldset>';
        return self::return_value($out, $return_html);
    }

    /**
     * Get string for submit buutons
     *
     * @param string    $name       Name of submit button
     * @param string    $value      Value of submit button
     * @param int       $withcancel Show cancel button (yes by default)
     * @param bool $return_html                 By default, string will be returned by function.
     *                                          Set to False in order to print result
     * @return FormStyler::return_value()
     */
    public static function getFormSubmitButton($name, $value, $withcancel = 1, $return_html = true)
    {
        $out = '';
        $out .= '<div style="text-align: center"><input type="submit"  class="button" name="' . $name . '"   value="' . $value . '" /></div>';

        return self::return_value($out, $return_html);
    }

    /**
     * Print form submit buttons
     *
     * @param string    $name       Name of submit button
     * @param string    $value      Value of submit button
     * @param int       $withcancel Show cancel button (yes by default)
     *
     * @fixme Unimplemented $withcancel
     */
    public static function printFormSubmitButton($name, $value, $withcancel = 1)
    {
        return self::getFormSubmitButton($name, $value, $withcancel, false);
    }

    /**
     * Print a select list
     *
     * @param string    $name           Input name
     * @param string    $label          Input label text (already translate)
     * @param array     $values         Array contains values list
     * @param string    $selected       Default selection
     * @param int       $required       If input is required or not
     * @param string    $id             Id tag for input. If empty, use $name value
     * @param string    $type_select    Display special input as a payment list (with currency)
     * @param string    $more_class     More CSS class to add
     * @return FormStyler::return_value()
     *
     * @fixme Unused variable $out
     */
    public static function printSelectList($name, $label, $values = array(), $selected = '', $required = 0, $id = '', $type_select = '', $more_class = '', $return_html = true)
    {
        global $form, $langs;

        $id = !empty($id) ? $id : $name;

        $out = self::fieldEditBegin($name, $more_class, $return_html);

        $out .= self::labelField($name, $label, $required, $return_html);

        $out .= self::fieldValueBegin($name, $return_html);
        switch ($type_select):
    case "types_paiements":
        $form->select_types_paiements($selected, $name, '', 2);
        break;
    case "categories":
        $cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, '', 'parent', 64, 0, 1);
        $arrayselected = explode(',', $selected);
        $out .= $form->multiselectarray($name, $cate_arbo, $arrayselected, '', 0, '', 0, '100%');
    default:
        if (is_array($values) and count($values) > 0) {
                $out .= '<select name="' . $name . '" id="' . $id . '" class="flat" >';
                foreach ($values as $key => $val) {
                    $out .= '<option value="' . $key . '">' . $langs->trans($val) . '</option>';
                }
                $out .= '</select>';
        }
        endswitch;

        $out .= self::fieldValueEnd($return_html);

        $out .= self::fieldEditEnd($return_html);
        return self::return_value($out, $return_html);
    }

    /**
     * Get checkbox
     *
     * @param string $name                      Input name
     * @param string $label                     Input label text (already translate)
     * @param string $value                     Input value
     * @param bool $checked                     Default state
     * @param int $required                     If input is required or not
     * @param string $id                        Id tag for input. If empty, use $name value
     * @param bool $is_array                    Is checkbox contains array or not
     * @param bool $return_html                 By default, string will be returned by function.
     *                                          Set to False in order to print result
     * @return FormStyler::return_value()
     */
    public static function getCheckBox($name, $label, $value = '', $checked = false, $required = 0, $id = '', $is_array = false, $return_html = true)
    {
        $id = !empty($id) ? $id : $name;

        $out = self::fieldEditBegin($name, 'checkbox');

        $out .= self::labelField($id, $label, $required, 1);

        $out .= self::fieldValueBegin($name);
        $out .= '<input type="checkbox" id="' . $id . '" name="' . $name . ($is_array ? '[]' : '') . '" value="' . $value . '"' . ($checked ? ' checked' : '') . ' />';
        $out .= self::fieldValueEnd();

        $out .= self::fieldEditEnd();

        return self::return_value($out, $return_html);
    }

    /**
     * Print a checkbox
     *
     * @param string    $name       Input name
     * @param string    $label      Input label text (already translate)
     * @param mixed     $value      Input value / Array values
     * @param bool      $checked    Default state
     * @param int       $required   If input is required or not
     * @param string    $id         Id tag for input. If empty, use $name value
     * @param boolean   $is_array   Is checkbox contains array or not
     * @return FormStyler::getCheckBox()
     *
     * @fixme Undefined $more_class used in method
     * @fixme Unused variable $out
     */
    public static function printCheckbox($name, $label, $value = '', $checked = false, $required = 0, $id = '', $is_array = false)
    {
        return self::getCheckBox($name, $label, $value, $checked, $required, $id, $is_array, false);
    }

    /**
     * Print a clear block
     */
    public static function printClearBlock()
    {
        print '<div class="clear"></div>';
    }

    /**
     * Print radio button
     *
     * @param string $name        name of radio button
     * @param int $selected    selected value
     * @param array $array        Array of values if several buttons
     * @param int $id            Id for button list
     * @param bool $return_html    Return or print html
     * @return FormStyler::return_value()
     */
    public static function printRadioButtons($name, $selected, $array = array(), $id = '', $return_html = true)
    {
        global $langs;

        $id = !empty($id) ? $id : $name;

        $out .= self::fieldValueBegin($name);
        $out .= '<ul class="choice" id="' . $id . '">';
        if (is_array($array) and count($array) > 0) {
            foreach ($array as $key => $value) {
                $is_selected = '';
                $is_selected = ($key == $selected ? 1 : '');
                $out .= '<li class="choice_item">'
                . '<input type="radio" id="' . $name . '_' . $key . '" class="radio" name="' . $name . '" value="' . $key . '"' . ($is_selected ? ' checked="checked"' : '') . "/>"
                . ' <label for="' . $name . '_' . $key . '">' . $langs->trans($value) . '</label>'
                    . '</li>';
            }
        }
        $out .= '</ul>';
        $out .= self::fieldValueEnd();
        return self::return_value($out, $return_html);
    }

    /**
     *
     * @param type $name
     * @param type $label
     * @param type $selected
     * @param type $array
     * @param type $required
     * @param type $id
     * @param type $return_html
     * @return type
     */
    public function printRadiosFieldWithLabel($name, $label, $selected, $array = array(), $required = '', $id = '', $return_html = true, $more_class = "")
    {
        global $langs;

        $out = self::fieldEditBegin($name, 'radios ' . $more_class);
        $out .= self::labelField($id, $langs->trans($label), $required, $return_html);
        $out .= self::printRadioButtons($name, $selected, $array, $id, $return_html);
        $out .= self::fieldEditEnd();

        return self::return_value($out, $return_html);

    }

    /**
     * Call function given in parameters with defined args
     *
     * @param type $function
     * @param type $params
     * @param type $return_html
     * @return type
     */
    public function getFieldFromFunction($function, $params = array(), $return_html = true)
    {
        $out = '';
        if (function_exists($function)) {
            $out .= call_user_func_array($function, $params);
        }

        return self::return_value($out, $return_html);
    }

    /**
     * Print an entire field input with output of function given in parameter
     *
     * @param type $name
     * @param type $label
     * @param type $function
     * @param type $args
     * @param type $return_html
     * @return type
     */
    public function printFieldFromFunction($name, $label, $function, $args, $return_html = true)
    {

        $out = '';
        $out = self::fieldEditBegin($name, 'radios', $return_html);
        $out .= self::labelField($id, $label, $required, $return_html);

        $out .= self::fieldValueBegin($name, $return_html);

        $out .= self::getFieldFromFunction($function, $args, $return_html);

        $out .= self::fieldValueEnd($return_html);
        $out .= self::fieldEditEnd($return_html);

        return self::return_value($out, $return_html);
    }

    /**
     * Call method given in parameters with defined args
     *
     * @param type $function
     * @param type $params
     * @param type $return_html
     * @return type
     */
    public function getFieldFromMethod($classname, $method, $params = array(), $return_html = true)
    {

        $out = '';
        if (class_exists($classname)) {
            $obj = new $classname($this->db);
            $out .= call_user_func_array(array($obj, $method), $params);
        }
        return self::return_value($out, $return_html);
    }

    /**
     *
     * @param type $name
     * @param type $label
     * @param type $classname
     * @param type $method
     * @param type $args
     * @param type $return_html
     * @return type
     */
    function printFieldFromMethod($name, $label, $classname, $method, $args=array(), $return_html=true, $required=0) {
        
        $out='';
        $out = self::fieldEditBegin($name, '', $return_html);
        $out.= self::labelField($name, $label, $required, $return_html);
        
        $out.= self::fieldValueBegin($name, $return_html);
        
        $out.= self::getFieldFromMethod($classname, $method, $args, $return_html);
        
        $out .= self::fieldValueEnd($return_html);
        $out .= self::fieldEditEnd($return_html);

        return self::return_value($out, $return_html);
    }

    /**
     * Fill form fields with object values
     * @param type $fields
     * @param type $object
     */
    public function setFieldsValueFromObject(&$fields, &$object)
    {
        if (isset($object->{$fields['name']})) {
            $fields['value'] = $object->{$fields['name']};
        }
        //$yaml_conf = self::getFormConfig($filename);

        // Build an array with correct structure for printInputFields method
        //$fields = array_map("setInputFieldByLabel", $names);
        // Add translation key
        //array_walk($fields, "setFieldsValueFromObject", $object);

    }

    /**
     * Set object properties with input datas
     *
     * @param type $form_filename
     * @param type $object
     */
    public function setPropFromForm($form_filename, &$object)
    {
        if (file_exists($form_filename)) {
            $form_names = getFormFieldsNames($form_filename);
            foreach ($form_names as $key => $name) {
                $object->{$name} = GETPOST($name);
                if (strpos($name, 'date') !== false) {
                    $object->{$name} = dol_mktime($_POST[$name . "hour"], $_POST[$name . "min"], $_POST[$name . "seconds"], $_POST[$name . "month"], $_POST[$name . "day"], $_POST[$name . "year"]);
                }
            }
        }
    }
}

/**
 * Return a formatted array with corrects keys and values in order to
 * build an input field
 */
function combineFormInputFields($datas)
{
    return array_combine(FormStyler::$input_fields_description, $datas);
}

/**
 * Be sure that array contains field description has all value needed to build
 * html input field
 */
function validInputFieldStruct($field)
{
    global $langs;
    return array(
        'name' => $field['name'],
        'label' => (isset($field['label']) ? $langs->trans($field['label']) : ''),
        'value' => (isset($field['value']) ? $field['value'] : (GETPOST($field['name']) ? GETPOST($field['name']) : '')),
        //'selected'      => ($field['selected'] ? $field['selected'] : false),
        'required' => ($field['required'] ? $field['required'] : false),
        'id' => $field['id'],
        'type' => ($field['type'] ? $field['type'] : "text"),
        'more_class' => $field['more_class'],
        'values' => $field['values'],
        'classname' => $field['classname'],
        'method' => $field['method'],
        'function' => $field['function'],
        'args' => (array) $field['args'],
        'more_class' => $field['more_class'],
        'more_options' => $field['more_options']

    );

}

/*
 * From a simple string, return an array with minimal structure for an input field
 * used as callback : see
 */

function setInputFieldByLabel($label)
{

    return array(
        'name' => $label,
        'value' => GETPOST($label),
        'label' => $label,
    );
}

/**
 * Add a prefix to label value for translation key
 *
 * @see printInputFieldsGroupFromNames
 *
 * @param array $fields
 * @param type $key
 * @param type $prefix
 */
function setInputLabelTransKey(&$fields, $key, $prefix)
{
    $fields['label'] = $prefix . $fields['name'];
}

/*
 * Return an array with just names of fields, after reading YAML config file
 */
function getFormFieldsNames($filename)
{
    $yaml_conf = FormStyler::getFormConfig($filename);
    $form_names = array();
    foreach ($yaml_conf['structure'] as $idx => $element) {
        if ($element['type'] == "fieldset") {
            foreach ($element['fields'] as $key => $input) {
                $form_names[] = $input['name'];
            }
        } else {

        }
    }
    return $form_names;
}

/*
 * Tool to show YAML file content with form structure
 * The content will be saved into forms/formname.fields.yaml
 */
function buildYamlFile($fields_name, $trans_key = "FormInputLabel_")
{
    print '<pre>';
    foreach ($fields_name as $name) {
        print "- name: $name\n";
        print "  label: $trans_key$name\n";
        //print "  selected: \n";
        //print "  required: \n";
        //print "  id: \n";
        //print "  type: \n";
        //print "  more_class: \n";
        //print "  more_options: \n";
    }
    print '</pre>';
}

/*
 * Tool to show all translations keys for a form
 */
function buildTranslationKeys($fields_name, $trans_key = "FormInputLabel_")
{
    print '<pre>';
    foreach ($fields_name as $name) {
        print $trans_key . '' . $name . "=\n";
    }
    print '</pre>';
}

function buildListFormKeys($fields)
{
    print '<pre>';
    foreach ($fields as $name) {
        print $name . "\n";
    }
    print '</pre>';
}
