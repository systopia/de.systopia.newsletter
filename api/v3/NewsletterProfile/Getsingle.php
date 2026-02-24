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

declare(strict_types = 1);

use CRM_Newsletter_ExtensionUtil as E;

/**
 * API callback for "getsingle" call on "NewsletterProfile" entity.
 *
 * @param array<string, mixed> $params
 *
 * @return array<string, mixed>
 */
function civicrm_api3_newsletter_profile_getsingle(array $params): array {
  try {
    if (!$profile = CRM_Newsletter_Profile::getProfile($params['name'] ?: 'default')) {
      throw new Exception(E::ts('Advanced Newsletter Management profile not found.'), 404);
    }

    // these fields are for back end configuration only, and wont be exposed to the frontend
    $blacklisted_options = [];

    $profile_name = $profile->getName();
    $profile_data = $profile->getData();

    // Set locale to profile language for translated option values.
    $current_locale = CRM_Core_I18n::getLocale();
    $profile_locale = $profile_data['language'] ?: Civi::settings()->get('lcMessages');
    CRM_Core_Session::singleton()->set('lcMessages', $profile_locale);
    CRM_Core_I18n::singleton()->setLocale($profile_locale);

    // Add contact field type and options, if applicable.
    $contact_fields = CRM_Newsletter_Profile::availableContactFields();
    foreach ($profile_data['contact_fields'] as $field_name => &$field) {
      $field['type'] = $contact_fields[$field_name]['type'];

      // Replace options defined in profile configuration.
      if (!empty($contact_fields[$field_name]['options'])) {
        $field['options'] = array_replace(
          $contact_fields[$field_name]['options'],
          array_filter($field['options'] ?? [], function ($replacement) {
            return isset($replacement) && $replacement !== '';
          })
        );
      }
    }

    // filter blacklisted parameters
    foreach ($blacklisted_options as $option) {
      if (isset($profile_data[$option])) {
        unset($profile_data[$option]);
      }
    }

    // Build group tree.
    $group_tree = CRM_Newsletter_Utils::buildGroupTree($profile_data['mailing_lists']);
    $profile_data['mailing_lists_tree'] = $group_tree;

    $return = [$profile_name => $profile_data];

    // Reset locale to original language.
    CRM_Core_Session::singleton()->set('lcMessages', $current_locale);
    CRM_Core_I18n::singleton()->setLocale($current_locale);

    return civicrm_api3_create_success($return);
  }
  catch (\Exception $exception) {
    // @ignoreException
    // Reset locale to original language.
    CRM_Core_Session::singleton()->set('lcMessages', $current_locale);
    CRM_Core_I18n::singleton()->setLocale($current_locale);

    return civicrm_api3_create_error($exception->getMessage(), ['error_code' => $exception->getCode()]);
  }
}

/**
 * API specification for "getsingle" call on "NewsletterProfile" entity.
 *
 * @param array<string, array<string, mixed>> $params
 */
function _civicrm_api3_newsletter_profile_getsingle_spec(array &$params): void {
  $params['name'] = [
    'name' => 'name',
    'title' => 'Newsletter profile name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => 'default',
    'description' => 'The Newsletter profile name. If omitted, the default profile will be returned.',
  ];
}
