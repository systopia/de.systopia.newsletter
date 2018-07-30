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
class CRM_Newsletter_Form_Profile extends CRM_Core_Form {

  /**
   * @var CRM_Newsletter_Profile $profile
   *
   * The profile object the form is acting on.
   */
  protected $profile;

  /**
   * @var string
   *
   * The operation to perform within the form.
   */
  protected $_op;

  /**
   * Builds the form structure.
   */
  public function buildQuickForm() {
    // "Create" is the default operation.
    if (!$this->_op = CRM_Utils_Request::retrieve('op', 'String', $this)) {
      $this->_op = 'create';
    }

    // Verify that a profile with the given name exists.
    $profile_name = CRM_Utils_Request::retrieve('name', 'String', $this);
    if (!$this->profile = CRM_Newsletter_Profile::getProfile($profile_name)) {
      $profile_name = NULL;
    }

    switch ($this->_op) {
      case 'delete':
        if ($profile_name) {
          CRM_Utils_System::setTitle(E::ts('Delete Advanced Newsletter Management profile <em>%1</em>', array(1 => $profile_name)));
          $this->addButtons(array(
            array(
              'type' => 'submit',
              'name' => ($profile_name == 'default' ? E::ts('Reset') : E::ts('Delete')),
              'isDefault' => TRUE,
            ),
          ));
        }
        parent::buildQuickForm();
        return;
      case 'edit':
        // When editing without a valid profile name, edit the default profile.
        if (!$profile_name) {
          $profile_name = 'default';
          $this->profile = CRM_Newsletter_Profile::getProfile($profile_name);
        }
        CRM_Utils_System::setTitle(E::ts('Edit Advanced Newsletter Management profile <em>%1</em>', array(1 => $this->profile->getName())));
        break;
      case 'create':
        // Load factory default profile values.
        $this->profile = CRM_Newsletter_Profile::createDefaultProfile($profile_name);
        CRM_Utils_System::setTitle(E::ts('New Advanced Newsletter Management profile'));
        break;
      default:
        CRM_Core_Error::fatal('Invalid operation.');
        break;
    }

    // Assign template variables.
    $this->assign('op', $this->_op);
    $this->assign('profile_name', $profile_name);

    // Set redirect destination.
    $this->controller->_destination = CRM_Utils_System::url('civicrm/admin/settings/newsletter/profiles', 'reset=1');

    // Add form elements.
    $is_default = ($profile_name == 'default');
    $this->add(
      ($is_default ? 'static' : 'text'),
      'name',
      E::ts('Profile name'),
      array(),
      !$is_default
    );

    $this->add(
      'text',
      'form_title',
      E::ts('Form title'),
      array(),
      FALSE
    );

    $this->add(
      'text',
      'submit_label',
      E::ts('Submit button label'),
      array(),
      FALSE
    );

    $contact_fields = CRM_Newsletter_Profile::availableContactFields();
    $contact_field_names = array();
    foreach ($contact_fields as $contact_field_name => $contact_field) {
      $this->add(
        'checkbox',
        'contact_field_' . $contact_field_name . '_active',
        E::ts('Show contact field "%1"', array(
          1 => E::ts($contact_field['label'])
        ))
      );
      $contact_field_names[$contact_field_name]['active'] = 'contact_field_' . $contact_field_name . '_active';

      $this->add(
        'checkbox',
        'contact_field_' . $contact_field_name . '_required',
        E::ts('Contact field "%1" is required', array(
          1 => E::ts($contact_field['label'])
        ))
      );
      $contact_field_names[$contact_field_name]['required'] = 'contact_field_' . $contact_field_name . '_required';

      $this->add(
        'text',
        'contact_field_' . $contact_field_name . '_label',
        E::ts('Field label')
      );
      $contact_field_names[$contact_field_name]['label'] = 'contact_field_' . $contact_field_name . '_label';

      $this->add(
        'text',
        'contact_field_' . $contact_field_name . '_description',
        E::ts('Field description')
      );
      $contact_field_names[$contact_field_name]['description'] = 'contact_field_' . $contact_field_name . '_description';
    }
    $this->assign('contact_field_names', $contact_field_names);

    $this->add(
      'text',
      'mailing_lists_label',
      E::ts('Label for mailing lists selection'),
      array(),
      TRUE
    );
    $this->add(
      'text',
      'mailing_lists_description',
      E::ts('Description for mailing lists selection'),
      array(),
      FALSE
    );
    $this->add(
      'select',
      'mailing_lists',
      E::ts('Available mailing lists'),
      CRM_Newsletter_Profile::getGroups(),
      TRUE,
      array('class' => 'crm-select2 huge', 'multiple' => 'multiple')
    );

    $this->add(
      'text',
      'conditions_public_label',
      E::ts('Label for Terms and conditions for public form'),
      array(),
      FALSE
    );
    $this->add(
      'text',
      'conditions_public_description',
      E::ts('Description for Terms and conditions for public form'),
      array(),
      FALSE
    );
    $this->add(
      'textarea',
      'conditions_public',
      E::ts('Terms and conditions for public form'),
      array(),
      FALSE
    );

    $this->add(
      'text',
      'conditions_preferences_label',
      E::ts('Label for Terms and conditions for preferences form'),
      array(),
      FALSE
    );
    $this->add(
      'text',
      'conditions_preferences_description',
      E::ts('Description for Terms and conditions for preferences form'),
      array(),
      FALSE
    );
    $this->add(
      'textarea',
      'conditions_preferences',
      E::ts('Terms and conditions for preferences form'),
      array(),
      FALSE
    );

    $this->add(
      'text',
      'template_optin_subject',
      E::ts('Subject for opt-in e-mail'),
      array(),
      TRUE
    );

    $this->add(
      'textarea',
      'template_optin',
      E::ts('Template for opt-in e-mail'),
      array(),
      TRUE
    );

    $this->add(
      'text',
      'template_info_subject',
      E::ts('Subject for info e-mail'),
      array(),
      TRUE
    );

    $this->add(
      'textarea',
      'template_info',
      E::ts('Template for info e-mail'),
      array(),
      TRUE
    );

    $this->add(
      'text',
      'preferences_url',
      E::ts('Preferences URL'),
      array(),
      TRUE
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    // Export form elements.
    parent::buildQuickForm();
  }

  /**
   * @inheritdoc
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Newsletter_Form_Profile', 'validateProfileForm'));
  }

  /**
   * Validates the profile form.
   *
   * @param array $values
   *   The submitted form values, keyed by form element name.
   *
   * @return bool | array
   *   TRUE when the form was successfully validated, or an array of error
   *   messages, keyed by form element name.
   */
  public static function validateProfileForm($values) {
    $errors = array();

    // Restrict profile names to alphanumeric characters and the underscore.
    if (isset($values['name']) && preg_match("/[^A-Za-z0-9\_]/", $values['name'])) {
      $errors['name'] = E::ts('Only alphanumeric characters and the underscore (_) are allowed for profile names.');
    }

    // At least one contact field is mandatory.
    $available_fields = array();
    foreach (CRM_Newsletter_Profile::availableContactFields() as $available_name => $available) {
      $available_fields[] = 'contact_field_' . $available_name . '_active';
      if (!empty($values['contact_field_' . $available_name . '_active'])) {
        $mandatory_missing = FALSE;
        break;
      }
      else {
        $mandatory_missing = TRUE;
      }
    }
    if ($mandatory_missing) {
      foreach ($available_fields as $available_field) {
        $errors[$available_field] = E::ts('At least one contact field must be activated.');
      }
    }

    // Each active contact field needs a label.
    foreach (CRM_Newsletter_Profile::availableContactFields() as $available_name => $available) {
      if (
        !empty($values['contact_field_' . $available_name . '_active'])
        && empty($values['contact_field_' . $available_name . '_label'])
      ) {
        $errors['contact_field_' . $available_name . '_label'] = E::ts('Each active field needs a label.');
      }
    }

    // Preferences URL must be a valid URL.
    if (filter_var($values['preferences_url'], FILTER_VALIDATE_URL) === FALSE) {
      $errors['preferences_url'] = E::ts('Please enter a valid URL.');
    }

    // When terms and conditions are given, a label must be set as well.
    foreach (array('public', 'preferences') as $conditions_type) {
      if (
        !empty($values['conditions_' . $conditions_type])
        && empty($values['conditions_' . $conditions_type . '_label'])
      ) {
        $errors['conditions_' . $conditions_type . '_label'] = E::ts('Please enter a label for the terms and conditions.');
      }
    }

    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Set the default values (i.e. the profile's current data) in the form.
   */
  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    if (in_array($this->_op, array('create', 'edit'))) {
      $defaults['name'] = $this->profile->getName();
      foreach ($this->profile->getData() as $element_name => $value) {
        if ($element_name == 'contact_fields') {
          // Translate the array structure into individual fields.
          foreach ($value as $contact_field => $values) {
            $defaults['contact_field_' . $contact_field . '_active'] = $values['active'];
            if (!empty($values['required'])) {
              $defaults['contact_field_' . $contact_field . '_required'] = $values['required'];
            }
            $defaults['contact_field_' . $contact_field . '_label'] = $values['label'];
            $defaults['contact_field_' . $contact_field . '_description'] = $values['description'];
          }
        }
        elseif ($element_name == 'mailing_lists') {
          // Mailing lists are stored as ID => Group name, the form needs a sequential
          // array of IDs.
          $defaults[$element_name] = array_keys($value);
        }
        else {
          $defaults[$element_name] = $value;
        }
      }
    }
    return $defaults;
  }

  /**
   * Store the values submitted with the form in the profile.
   */
  public function postProcess() {
    $values = $this->exportValues();
    if (in_array($this->_op, array('create', 'edit'))) {
      if (empty($values['name'])) {
        $values['name'] = 'default';
      }
      $this->profile->setName($values['name']);
      foreach ($this->profile->getData() as $element_name => $value) {
        if ($element_name == 'contact_fields') {
          foreach (CRM_Newsletter_Profile::availableContactFields() as $contact_field_name => $contact_field) {
            if (!empty($values['contact_field_' . $contact_field_name . '_active'])) {
              $values['contact_fields'][$contact_field_name]['active'] = $values['contact_field_' . $contact_field_name . '_active'];
              if (!empty($values['contact_field_' . $contact_field_name . '_required'])) {
                $values['contact_fields'][$contact_field_name]['required'] = $values['contact_field_' . $contact_field_name . '_required'];
              }
              $values['contact_fields'][$contact_field_name]['label'] = $values['contact_field_' . $contact_field_name . '_label'];
              $values['contact_fields'][$contact_field_name]['description'] = $values['contact_field_' . $contact_field_name . '_description'];
            }
          }
        }

        if ($element_name == 'mailing_lists') {
          // Store ID => Group name.
          $values['mailing_lists'] = array_intersect_key(CRM_Newsletter_Profile::getGroups(), array_flip($values['mailing_lists']));
        }

        if (isset($values[$element_name])) {
          $this->profile->setAttribute($element_name, $values[$element_name]);
        }
      }
      $this->profile->saveProfile();
    }
    elseif ($this->_op == 'delete') {
      $this->profile->deleteProfile();
    }
    parent::postProcess();
  }

}
