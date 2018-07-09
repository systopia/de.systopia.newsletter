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
 * API callback for "request" call on "NewsletterSubscription" entity.
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_newsletter_subscription_request($params) {
  try {
    $profile = CRM_Newsletter_Profile::getProfile($params['profile']);

    $contact_fields = array_intersect_key($params, $profile->getAttribute('contact_fields'));
    foreach ($contact_fields as $field_name => $field_value) {
      if (!$field_value) {
        unset($params[$field_name]);
      }
    }

    // Get or create the contact.
    $missing_contact_fields = array_diff_key($profile->getAttribute('contact_fields'), $params);
    if (!empty($missing_contact_fields)) {
      throw new CiviCRM_API3_Exception(
        E::ts('Missing mandatory fields %1', array(
          1 => implode(', ', array_keys($missing_contact_fields)),
        )),
        'mandatory_missing'
      );
    }

    $contact_data = array_intersect_key($params, $profile->getAttribute('contact_fields'));
    $contact_id = CRM_Newsletter_Utils::getContact($contact_data);

    // TODO: Send an e-mail with the opt-in template.

    return civicrm_api3_create_success();
  }
  catch (Exception $exception) {
    $error_code = ($exception instanceof CiviCRM_API3_Exception ? $exception->getErrorCode() : $exception->getCode());
    return civicrm_api3_create_error($exception->getMessage(), array('error_code' => $error_code));
  }
}

/**
 * API specification for "request" call on "NewsletterSubscription" entity.
 *
 * @param $params
 */
function _civicrm_api3_newsletter_subscription_request_spec(&$params) {
  $params['profile'] = array(
    'name' => 'profile',
    'title' => 'Newsletter profile name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => 'default',
    'description' => 'The Newsletter profile name. If omitted, the default profile will be used.',
  );

  foreach (CRM_Newsletter_Profile::availableContactFields() as $field_name => $field_label) {
    $params[$field_name] = array(
      'name' => $field_name,
      'title' => $field_label,
      'type' => CRM_Utils_Type::T_STRING,
      'api.required' => 0,
    );
  }
}
