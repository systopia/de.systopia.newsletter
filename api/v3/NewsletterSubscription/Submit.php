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

use Civi\Newsletter\ContactChecksumService;
use CRM_Newsletter_ExtensionUtil as E;

/**
 * API callback for "submit" call on "NewsletterSubscription" entity.
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_newsletter_subscription_submit($params) {
  try {
    if (!$profile = CRM_Newsletter_Profile::getProfile($params['profile'])) {
      throw new CRM_Core_Exception(
        E::ts('No profile found with the given name.'),
        'api_error'
      );
    }

    // Check for missing mandatory contact fields.
    $missing_contact_fields = array_diff_key(
      array_filter(
        $profile->getAttribute('contact_fields'),
        function ($contact_field) {
          return !empty($contact_field['required']);
        }
      ),
      $params
    );
    if (!empty($missing_contact_fields)) {
      throw new CRM_Core_Exception(
        E::ts('Missing mandatory fields %1', array(
          1 => implode(', ', array_keys($missing_contact_fields)),
        )),
        'mandatory_missing'
      );
    }

    // Get or create the contact.
    $contact_data = array_intersect_key($params, $profile->getAttribute('contact_fields'));
    // add xcm profile
    $xcm_profile = $profile->getAttribute('xcm_profile');
    if (!empty($xcm_profile)) {
      $contact_data['xcm_profile'] = $xcm_profile;
    }
    $contact_result = CRM_Newsletter_Utils::getContact($contact_data);
    $contact_id = $contact_result['contact_id'];
    $contact = civicrm_api3('Contact', 'getsingle', array(
      'id' => $contact_id,
    ));
    $contact_checksum = ContactChecksumService::getInstance()->generateChecksum($contact_id);

    // Validate submitted group IDs.
    if (!is_array($params['mailing_lists'])) {
      $params['mailing_lists'] = explode(',', $params['mailing_lists']);
    }
    $disallowed_groups = array_diff(
      $params['mailing_lists'],
      array_keys($profile->getAttribute('mailing_lists'))
    );
    if (!empty($disallowed_groups)) {
      throw new CRM_Core_Exception(E::ts('Disallowed group ID(s): %1', array(
        1 => implode(', ', $disallowed_groups)
      )), 'api_error');
    }

    // Get current group memberships for submitted group IDs.
    $current_mailing_lists = array();
    foreach (CRM_Newsletter_Utils::getSubscriptionStatus($contact_id, $profile->getName()) as $group_id => $group_info) {
      $current_mailing_lists[$group_id] = $group_info['status_raw'];
    }

    // Add "pending" group membership for all new groups.
    $new_groups = array_diff($params['mailing_lists'], array_keys($current_mailing_lists));
    $group_contact_results = array();
    foreach ($new_groups as $group_id) {
      $group_contact_results[$group_id] = civicrm_api3('GroupContact', 'create', array(
        'group_id' => $group_id,
        'contact_id' => $contact_id,
        'status' => 'Pending',
      ));
    }

    // Add GDPRX record for newly created contacts.
    if (
      CRM_Newsletter_Utils::gdprx_installed()
      && $profile->getAttribute('gdprx_new_contact')
      && $contact_result['was_created']
    ) {
      civicrm_api3(
        'ConsentRecord',
        'create',
        [
          'contact_id' => $contact_id,
          'category' => $profile->getAttribute('gdprx_new_contact_category'),
          'source' => $profile->getAttribute('gdprx_new_contact_source'),
          'type' => $profile->getAttribute('gdprx_new_contact_type'),
          'note' => $profile->getAttribute('gdprx_new_contact_note'),
          'terms' => $profile->getAttribute('conditions_public'),
        ]
      );
    }

    // Send an e-mail with the opt-in template.
    CRM_Newsletter_Utils::send_configured_mail(
      $contact,
      $contact_checksum,
      $profile,
      'optin'
    );

    return $group_contact_results;
  }
  catch (Exception $exception) {
    $error_code = ($exception instanceof CRM_Core_Exception ? $exception->getErrorCode() : $exception->getCode());
    return civicrm_api3_create_error($exception->getMessage(), array('error_code' => $error_code));
  }
}

/**
 * API specification for "submit" call on "NewsletterSubscription" entity.
 *
 * @param $params
 */
function _civicrm_api3_newsletter_subscription_submit_spec(&$params) {
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
      'title' => $field_label['label'],
      'type' => CRM_Utils_Type::T_STRING,
      'api.required' => 0,
    );
  }

  $params['mailing_lists'] = array(
    'name' => 'mailing_lists',
    'title' => 'Mailing lists',
    'type' => 'CommaSeparatedIntegers',
    'api.required' => 1,
    'description' => E::ts('The IDs of the groups to add the contact to.'),
  );
}
