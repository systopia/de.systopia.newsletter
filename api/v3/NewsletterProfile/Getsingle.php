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
 * API callback for "getsingle" call on "NewsletterProfile" entity.
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_newsletter_profile_getsingle($params) {
  try {
    if (!$profile = CRM_Newsletter_Profile::getProfile($params['name'] ?: 'default')) {
      throw new Exception(E::ts('Advanced Newsletter Management profile not found.'), 404);
    }

    // these fields are for back end configuration only, and wont be exposed to the frontend
    $blacklisted_options = ['mailing_lists_unsubscribe_all_profiles'];

    $profile_name = $profile->getName();
    $profile_data = $profile->getData();

    // Add contact field type and options, if applicable.
    $contact_fields = CRM_Newsletter_Profile::availableContactFields();
    foreach ($profile_data['contact_fields'] as $field_name => &$field) {
      $field['type'] = $contact_fields[$field_name]['type'];

      // Replace options defined in profile configuration.
      if (!empty($contact_fields[$field_name]['options'])) {
        $field['options'] = array_replace(
          $contact_fields[$field_name]['options'],
            array_filter($field['options'], function ($replacement) { return isset($replacement) && $replacement !== ''; }) ?? []
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

    $return = array($profile_name => $profile_data);
    return civicrm_api3_create_success($return);
  }
  catch (\Exception $exception) {
    return civicrm_api3_create_error($exception->getMessage(), array('error_code' => $exception->getCode()));
  }
}

/**
 * API specification for "getsingle" call on "NewsletterProfile" entity.
 *
 * @param $params
 */
function _civicrm_api3_newsletter_profile_getsingle_spec(&$params) {
  $params['name'] = array(
    'name' => 'name',
    'title' => 'Newsletter profile name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => 'default',
    'description' => 'The Newsletter profile name. If omitted, the default profile will be returned.',
  );
}
