<?php
use CRM_Newsletter_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Newsletter_Upgrader extends CRM_Newsletter_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Convert serialized settings from objects to arrays.
   *
   * @link https://civicrm.org/advisory/civi-sa-2019-21-poi-saved-search-and-report-instance-apis
   */
  public function upgrade_5009() {
    // Do not use CRM_Core_BAO::getItem() or Civi::settings()->get().
    // Extract and unserialize directly from the database.
    $newsletter_profiles_query = CRM_Core_DAO::executeQuery("
        SELECT `value`
          FROM `civicrm_setting`
        WHERE `name` = 'newsletter_profiles';");
    if ($newsletter_profiles_query->fetch()) {
      $profiles = unserialize($newsletter_profiles_query->value);
      Civi::settings()->set('newsletter_profiles', (array) $profiles);
    }

    return TRUE;
  }

  /**
   * Add default value for new opt-in URL profile property.
   */
  public function upgrade_5100() {
    foreach (CRM_Newsletter_Profile::getProfiles() as $profile_name => $profile) {
      if (empty($profile->getAttribute('optin_url'))) {
        $profile->setAttribute('optin_url', $profile->getAttribute('preferences_url'));
        $profile->saveProfile();
      }
    }

    return TRUE;
  }

  /**
   * Replace "[CONTACT_HASH]" with "[CONTACT_CHECKSUM"] in URLs.
   */
  public function upgrade_5101(): bool {
    foreach (CRM_Newsletter_Profile::getProfiles() as $profile) {
      foreach(['optin_url', 'preferences_url', 'request_link_url'] as $attribute_name) {
        $profile->setAttribute(
          $attribute_name,
          str_replace(
            '[CONTACT_HASH]',
            '[CONTACT_CHECKSUM]',
            $profile->getAttribute($attribute_name) ?? ''
          )
        );
        $profile->saveProfile();
      }
    }

    return TRUE;
  }

}
