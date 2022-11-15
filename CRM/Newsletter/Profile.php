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
 * A profile stores configuration for a newsletter form.
 */
class CRM_Newsletter_Profile {

  /**
   * The name of the mailing list group type.
   */
  const GROUP_TYPE_MAILING_LIST = 'Mailing List';

  /**
   * @var CRM_Newsletter_Profile[] $_profiles
   *   Caches the profile objects.
   */
  protected static $_profiles = NULL;

  /**
   * @var string $name
   *   The name of the profile.
   */
  protected $name = NULL;

  /**
   * @var array $data
   *   The attributes of the profile.
   */
  protected $data = NULL;

  /**
   * CRM_Newsletter_Profile constructor.
   *
   * @param string $name
   *   The name of the profile.
   * @param array $data
   *   The attributes of the profile
   */
  public function __construct($name, $data) {
    $this->name = $name;
    $allowed_attributes = self::allowedAttributes();
    $this->data = $data + array_combine(
        $allowed_attributes,
        array_fill(0, count($allowed_attributes), NULL)
      );
  }

  /**
   * Retrieves all data attributes of the profile.
   *
   * @return array
   *   The attributes of the profile.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Retrieves the profile name.
   *
   * @return string
   *   The name of the profile.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Sets the profile name.
   *
   * @param $name
   *   The new name of the profile.
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * Retrieves an attribute of the profile.
   *
   * @param string $attribute_name
   *   The name of the attribute of the profile.
   *
   * @return mixed | NULL
   *   The value of the attribute of the profile, or NULL if the attribute could
   *   not be found.
   */
  public function getAttribute($attribute_name) {
    if (isset($this->data[$attribute_name])) {
      return $this->data[$attribute_name];
    }
    else {
      return NULL;
    }
  }

  /**
   * Sets an attribute of the profile.
   *
   * @param string $attribute_name
   *   The name of the attribute of the profile.
   * @param mixed $value
   *   The new value of the attribute pf the profile.
   *
   * @throws \Exception
   *   When the attribute name is not known.
   */
  public function setAttribute($attribute_name, $value) {
    if (!in_array($attribute_name, self::allowedAttributes())) {
      throw new Exception("Unknown attribute {$attribute_name}.");
    }
    // TODO: Check if value is acceptable.
    $this->data[$attribute_name] = $value;
  }

  /**
   * Verifies whether the profile is valid.
   *
   * @throws Exception
   *   When the profile could not be successfully validated.
   */
  public function verifyProfile() {

  }

  /**
   * Persists the profile within the CiviCRM settings.
   */
  public function saveProfile() {
    self::$_profiles[$this->getName()] = $this;
    $this->verifyProfile();
    self::storeProfiles();
  }

  /**
   * Deletes the profile from the CiviCRM settings.
   */
  public function deleteProfile() {
    unset(self::$_profiles[$this->getName()]);
    self::storeProfiles();
  }

  /**
   * Retrieves allowed attributes for a profile.
   *
   * @return array
   *   A list of names of allowed attributes.
   */
  public static function allowedAttributes() {
    return array(
      'xcm_profile',
      'form_title',
      'contact_fields',
      'mailing_lists',
      'mailing_lists_label',
      'mailing_lists_description',
      'mailing_lists_unsubscribe_all',
      'mailing_lists_unsubscribe_all_profiles',
      'mailing_lists_unsubscribe_all_label',
      'mailing_lists_unsubscribe_all_submit_label',
      'mailing_lists_unsubscribe_all_description',
      'conditions_public',
      'conditions_public_label',
      'conditions_public_description',
      'conditions_preferences',
      'conditions_preferences_label',
      'conditions_preferences_description',
      'template_optin_subject',
      'template_optin',
      'template_optin_html',
      'template_info_subject',
      'template_info',
      'template_info_html',
      'template_unsubscribe_all_subject',
      'template_unsubscribe_all',
      'template_unsubscribe_all_html',
      'optin_url',
      'preferences_url',
      'request_link_url',
      'submit_label',
      'gdprx_new_contact',
      'gdprx_new_contact_category',
      'gdprx_new_contact_source',
      'gdprx_new_contact_type',
      'gdprx_new_contact_note',
      'gdprx_unsubscribe_all',
      'gdprx_unsubscribe_all_category',
      'gdprx_unsubscribe_all_source',
      'gdprx_unsubscribe_all_type',
      'gdprx_unsubscribe_all_note',
    );
  }

  /**
   * Retrieves available contact fields for a profile.
   *
   * @return array
   *   An array with contact field names as keys and their translated labels as
   *   values.
   *
   * @throws \CiviCRM_API3_Exception
   *   When retrieving field data failed.
   */
  public static function availableContactFields() {
    $individual_prefix_values = civicrm_api3('OptionValue', 'get', array(
      'return' => array("value", "label"),
      'option_group_id' => "individual_prefix",
      'is_active' => 1,
    ));
    $individual_prefix_options = array();
    foreach ($individual_prefix_values['values'] as $individual_prefix_value) {
      $individual_prefix_options[$individual_prefix_value['value']] = $individual_prefix_value['label'];
    }

    $static = array(
      'prefix_id' => array(
        'label' => E::ts('Prefix'),
        'type' => 'Select',
        'options' => $individual_prefix_options,
      ),
      'formal_title' => array(
        'label' => E::ts('Formal title'),
        'type' => 'Text',
      ),
      'first_name' => array(
        'label' => E::ts('First name'),
        'type' => 'Text',
      ),
      'last_name' => array(
        'label' => E::ts('Last name'),
        'type' => 'Text',
      ),
      'email' => array(
        'label' => E::ts('E-mail address'),
        'type' => 'Text',
      ),
      'url' => array(
        'label' => E::ts('Website'),
        'type' => 'Text',
      ),
      'phone' => array(
        'label' => E::ts('Phone number'),
        'type' => 'Text',
      ),
      // TODO: phone2 is only available when it is activated in the XCM profile.
      'phone2' => array(
        'label' => E::ts('Phone number 2'),
        'type' => 'Text',
      ),
    );

    $static += array_map(
      function ($addressField) {
        $field = [
          'label' => $addressField['label'],
          'type' => $addressField['input_type'],
        ];
        if (!empty($addressField['options'])) {
          $field['options'] = $addressField['options'];
        }
        return $field;
      },
      // Add reordered address fields.
      array_replace(
        array_flip([
          'street_address',
          'supplemental_address_1',
          'supplemental_address_2',
          'supplemental_address_3',
          'postal_code',
          'city',
          'county_id',
          'state_province_id',
          'country_id',
        ]),
        \Civi\Api4\Address::getFields()
          ->setLoadOptions(TRUE)
          ->addWhere('name', 'IN', CRM_Xcm_Tools::getAddressFields())
          ->addSelect('name', 'label', 'options', 'input_type')
          ->execute()
          ->indexBy('name')
          ->getArrayCopy())
    );

    $dynamic = array();

    // Add custom fields on contacts.
    // Note: This adds all available custom fields for contact entities, however
    // not all field types will work correctly, especially when they are special
    // select widgets or non-text field types.
    $contact_field_groups = civicrm_api3('CustomGroup', 'get', array(
      'extends' => "contact",
    ));
    if (!empty($contact_field_groups['values'])) {
      $contact_fields = civicrm_api3('CustomField', 'get', array(
        'custom_group_id' => array(
          'IN' => array_keys($contact_field_groups['values'])
        ),
      ));
      foreach ($contact_fields['values'] as $contact_field) {
        $dynamic['custom_' . $contact_field['id']] = array(
          'label' => $contact_field['label'],
          'type' => $contact_field['html_type'],
        );
        if (in_array($contact_field['html_type'], array(
          'Multi-Select',
          'CheckBox',
          'Select'
        ))) {
          $option_values = civicrm_api3('OptionValue', 'get', array(
            'option_group_id' => $contact_field['option_group_id'],
          ));
          $dynamic['custom_' . $contact_field['id']]['options'] = array();
          foreach ($option_values['values'] as $option_value) {
            $dynamic['custom_' . $contact_field['id']]['options'][$option_value['value']] = $option_value['label'];
          }
        }
      }
    }
    return $static + $dynamic;
  }

  /**
   * Retrieves the default profile with "factory" defaults.
   *
   * @param string $name
   *   The profile name. Defaults to "default".
   *
   * @return CRM_Newsletter_Profile
   *   A profile with the given name and default attribute values.
   */
  public static function createDefaultProfile($name = 'default') {
    $default_data = array(
      'xcm_profile' => '',
      'form_title' => '',
      'contact_fields' => array(),
      'mailing_lists' => self::getGroups(),
      'mailing_lists_label' => E::ts('Mailing lists'),
      'mailing_lists_description' => '',
      'mailing_lists_unsubscribe_all' => '',
      'mailing_lists_unsubscribe_all_profiles' => '',
      'mailing_lists_unsubscribe_all_label' => E::ts('Unsubscribe'),
      'mailing_lists_unsubscribe_all_submit_label' => '',
      'mailing_lists_unsubscribe_all_description' => '',
      'conditions_public' => '',
      'conditions_public_label' => '',
      'conditions_public_description' => '',
      'conditions_preferences' => '',
      'conditions_preferences_label' => '',
      'conditions_preferences_description' => '',
      'template_optin_subject' => E::ts('Your newsletter subscription'),
      'template_optin' => '', // TODO: A default opt-in e-mail template with a token for the link to the preferences page.
      'template_info_subject' => E::ts('Your newsletter subscription preferences'),
      'template_info' => '', // TODO: A default info e-mail template.
      'template_unsubscribe_all_subject' => E::ts('Your unsubscription'),
      'template_unsubscribe_all' => '',
      'template_unsubscribe_all_html' => '',
      'optin_url' => CRM_Core_Config::singleton()->userFrameworkBaseURL,
      'preferences_url' => CRM_Core_Config::singleton()->userFrameworkBaseURL,
      'request_link_url' => CRM_Core_Config::singleton()->userFrameworkBaseURL,
      'submit_label' => '',
    );
    foreach (self::availableContactFields() as $field_name => $field) {
      $default_data['contact_fields'][$field_name] = array(
        'active' => ($field_name == 'email' ? 1 : 0),
        'required' => ($field_name == 'email' ? 1 : 0),
        'label' => $field['label'],
        'description' => '',
      );
    }
    return new CRM_Newsletter_Profile($name, $default_data);
  }

  /**
   * Retrieves the profile with the given name.
   *
   * @param $name
   *   The name of the profile.
   *
   * @return CRM_Newsletter_Profile | NULL
   *   The profile with the given name, or NULL if it does not exist.
   */
  public static function getProfile($name) {
    $profiles = self::getProfiles();
    if (isset($profiles[$name])) {
      return $profiles[$name];
    }
    else {
      return NULL;
    }
  }

  /**
   * Retrieves the list of all profiles persisted within the current CiviCRM
   * settings, including the default profile.
   *
   * @return CRM_Newsletter_Profile[]
   *   A list of all profiles currently persisted.
   */
  public static function getProfiles() {
    if (self::$_profiles === NULL) {
      self::$_profiles = array();
      if ($profiles_data = Civi::settings()->get('newsletter_profiles')) {
        foreach ($profiles_data as $profile_name => $profile_data) {
          self::$_profiles[$profile_name] = new CRM_Newsletter_Profile($profile_name, $profile_data);
        }
      }
    }

    // Include the default profile if it was not overridden within the settings.
    if (!isset(self::$_profiles['default'])) {
      self::$_profiles['default'] = self::createDefaultProfile();
      self::storeProfiles();
    }

    return self::$_profiles;
  }


  /**
   * Persists the list of profiles into the CiviCRM settings.
   */
  public static function storeProfiles() {
    $profile_data = array();
    foreach (self::$_profiles as $profile_name => $profile) {
      $profile_data[$profile_name] = $profile->data;
    }
    civi::settings()->set('newsletter_profiles', $profile_data);
  }

  /**
   * Retrieves active groups used as mailing lists within the system as options
   * for select form elements.
   */
  public static function getGroups() {
    $groups = array();
    try {
      $group_types = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'group_type',
        'name' => CRM_Newsletter_Profile::GROUP_TYPE_MAILING_LIST,
      ));
      if ($group_types['count'] > 0) {
        $group_type = reset($group_types['values']);
        $query = civicrm_api3('Group', 'get', array(
          'is_active' => 1,
          'group_type' => array('LIKE' => '%' . CRM_Utils_Array::implodePadded($group_type['value']) . '%'),
          'option.limit'   => 0,
          'return'         => 'id,title,frontend_title'
        ));
        foreach ($query['values'] as $group) {
          $groups[$group['id']] = $group['frontend_title'] ?: $group['title'];
        }
      }
    }
    catch (CiviCRM_API3_Exception $exception) {
      $error = CRM_Core_Error::createError($exception->getMessage(), 0);
      CRM_Core_Error::displaySessionError($error);
    }
    return $groups;
  }
}
