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
      'contact_fields',
      'mailing_lists',
      'conditions_public',
      'conditions_preferences',
      'template_optin',
      'template_info',
      'preferences_url',
    );
  }

  /**
   * Retrieves available contact fields for a profile.
   *
   * @return array
   *   An array with contact field names as keys and their translated labels as
   *   values.
   */
  public static function availableContactFields() {
    return array(
      'first_name' => E::ts('First name'),
      'last_name' => E::ts('Last name'),
      'email' => E::ts('E-mail address'),
    );
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
    return new CRM_Newsletter_Profile($name, array(
      'contact_fields' => array(), // TODO: A fixed set of common contact fields.
      'mailing_lists' => array(), // TODO: All active mailing lists.
      'conditions_public' => '',
      'conditions_preferences' => '',
      'template_optin' => '', // TODO: A default opt-in e-mail template with the link to the preferences page.
      'template_info' => '', // TODO: A default info e-mail template.
      'preferences_url' => '',
    ));
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
      if ($profiles_data = CRM_Core_BAO_Setting::getItem('de.systopia.newsletter', 'newsletter_profiles')) {
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
    CRM_Core_BAO_Setting::setItem((object) $profile_data, 'de.systopia.newsletter', 'newsletter_profiles');
  }
}
