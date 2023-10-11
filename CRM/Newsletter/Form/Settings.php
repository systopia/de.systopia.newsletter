<?php
/*------------------------------------------------------------+
| SYSTOPIA Advanced Newsletter Management                     |
| Copyright (C) 2018 SYSTOPIA                                 |
| Author: J. Schuppe (schuppe@systopia.de)                    |
+-------------------------------------------------------------+
| This program is released as free software under the         |
| Affero GPL license. You can redistribute it and/or          |
| modify it under the terms of this license which you         |
| can read by viewing the included agpl.txt or online         |
| at www.gnu.org/licenses/agpl.html. Removal of this          |
| copyright header is strictly prohibited without             |
| written permission from the original author(s).             |
+-------------------------------------------------------------*/

use CRM_Newsletter_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Newsletter_Form_Settings extends CRM_Core_Form {

  private $_settingFilter = array('group' => 'de.systopia.newsletter');

  //everything from this line down is generic & can be re-used for a setting form in another extension
  //actually - I lied - I added a specific call in getFormSettings
  private $_submittedValues = array();
  private $_settings = array();

  /**
   * @inheritdoc
   */
  function buildQuickForm() {
    // Set redirect destination.
    $this->controller->_destination = CRM_Utils_System::url('civicrm/admin/settings/newsletter', 'reset=1');

    $settings = $this->getFormSettings();

    if (!empty($settings)) {
      foreach ($settings as $name => $setting) {
        if (isset($setting['quick_form_type'])) {
          $add = 'add' . $setting['quick_form_type'];
          if ($add == 'addElement') {
            $this->$add($setting['html_type'], $name, $setting['title'], CRM_Utils_Array::value('html_attributes', $setting, array ()));
          }
          elseif ($setting['html_type'] == 'Select') {
            $optionValues = array();
            if (!empty($setting['pseudoconstant']) && !empty($setting['pseudoconstant']['optionGroupName'])) {
              $optionValues = CRM_Core_OptionGroup::values($setting['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
            }
            $this->add('select', $setting['name'], $setting['title'], $optionValues, FALSE, $setting['html_attributes']);
          }
          else {
            $this->$add($name, $setting['title']);
          }
          $form_elements[$setting['name']] = array('description' => $setting['description']);
        }
      }

      $this->assign('formElements', $form_elements);

      $this->addButtons(array(
        array (
          'type' => 'submit',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        )
      ));
    }
    else {
      CRM_Core_Session::setStatus('There are no settings for this extension.', '', 'no-popup');
    }

    // Export form elements.
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  /**
   * @inheritdoc
   */
  function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function getFormSettings() {
    if (empty($this->_settings)) {
      $settings = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
    }
    return $settings['values'];
  }
  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function saveSettings() {
    $settings = $this->getFormSettings();
    $values = array_intersect_key($this->_submittedValues, $settings);
    civicrm_api3('setting', 'create', $values);
  }
  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  function setDefaultValues() {
    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $defaults = array();
    $domainID = CRM_Core_Config::domainID();
    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
    }
    return $defaults;
  }

  /**
   * @inheritdoc
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Newsletter_Form_Settings', 'validateSettingsForm'));
  }

  /**
   * Validates the settings form.
   *
   * @param array $values
   *   The submitted form values, keyed by form element name.
   *
   * @return bool | array
   *   TRUE when the form was successfully validated, or an array of error
   *   messages, keyed by form element name.
   */
  public static function validateSettingsForm($values) {
    $errors = array();

    return empty($errors) ? TRUE : $errors;
  }

}
