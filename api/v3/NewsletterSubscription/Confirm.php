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
 * API callback for "confirm" call on "NewsletterSubscription" entity.
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_newsletter_subscription_confirm($params) {
  try {
    if (!$profile = CRM_Newsletter_Profile::getProfile($params['profile'])) {
      throw new CiviCRM_API3_Exception(
        E::ts('No profile found with the given name.'),
        'api_error'
      );
    }

    // Validate contact ID and hash. This throws exceptions for invalid hashes.
    $contact = civicrm_api3('Contact', 'getsingle', array(
      'hash' => $params['contact_hash'],
    ));
    // Validate contact hash against given contact ID (in case a hash for an
    // existing contact was given but does not match the given contact ID).
    if ($contact['id'] != $params['contact_id']) {
      throw new CiviCRM_API3_Exception(E::ts('Invalid contact hash for given contact ID.'), 'api_error');
    }

    $contact_id = $contact['id'];

    $allowed_mailing_lists = array_keys($profile->getAttribute('mailing_lists'));
    $current_mailing_lists = array();
    foreach (CRM_Newsletter_Utils::getSubscriptionStatus($contact_id, $profile->getName()) as $group_id => $group_info) {
      $current_mailing_lists[$group_id] = $group_info['status_raw'];
    }

    $unsubscribe_from_all_profiles = FALSE;
    if (($params['unsubscribe_all'])) {
      // Mark all subscriptions for being removed.
      $params['mailing_lists'] = array_fill_keys(
        array_keys($current_mailing_lists),
      'Removed'
      );

      $unsubscribe_from_all_profiles = $profile->getAttribute('mailing_lists_unsubscribe_all_profiles');
      $email_template = 'unsubscribe_all';

      if (
        CRM_Newsletter_Utils::gdprx_installed()
        && $profile->getAttribute('gdprx_unsubscribe_all')
      ) {
        civicrm_api3(
          'ConsentRecord',
          'create',
          [
            'contact_id' => $contact_id,
            'category' => $profile->getAttribute('gdprx_unsubscribe_all_category'),
            'source' => $profile->getAttribute('gdprx_unsubscribe_all_source'),
            'type' => $profile->getAttribute('gdprx_unsubscribe_all_type'),
            'note' => $profile->getAttribute('gdprx_unsubscribe_all_note'),
            'terms' => $profile->getAttribute('conditions_preferences'),
          ]
        );
      }
    }
    elseif ($params['autoconfirm']) {
      // Mark all pending subscriptions for being added.
      $params['mailing_lists'] = array_fill_keys(
        array_keys(array_filter($current_mailing_lists, function($status) {
          return $status == 'Pending';
        })),
        'Added'
      );

      if (!empty($params['mailing_lists'])) {
        $email_template = 'info';
      }
    }
    else {
      // Validate submitted group IDs.
      if (empty($params['mailing_lists'])) {
        throw new CiviCRM_API3_Exception(
          E::ts('Mandatory key(s) missing from params array: mailing_lists'),
          'mandatory_missing'
        );
      }
      // TODO: This will not work, as we're expecting an array with status.
//      if (!is_array($params['mailing_lists'])) {
//        $params['mailing_lists'] = explode(',', $params['mailing_lists']);
//      }
      $disallowed_groups = array_diff(
        array_keys($params['mailing_lists']),
        $allowed_mailing_lists
      );
      if (!empty($disallowed_groups)) {
        throw new CiviCRM_API3_Exception(E::ts('Disallowed group ID(s): %1', array(
          1 => implode(', ', $disallowed_groups)
        )), 'api_error');
      }

      $email_template = 'info';
    }

    $group_contact_results = CRM_Newsletter_Utils::update_group_subscription($params['mailing_lists'], $contact_id, $unsubscribe_from_all_profiles);

    // Send an e-mail with the info template.
    if (!empty($email_template)) {
      CRM_Newsletter_Utils::send_configured_mail(
        $contact,
        $profile,
        $email_template
      );
    }

    return civicrm_api3_create_success($group_contact_results);
  }
  catch (Exception $exception) {
    $error_code = ($exception instanceof CiviCRM_API3_Exception ? $exception->getErrorCode() : $exception->getCode());
    return civicrm_api3_create_error($exception->getMessage(), array('error_code' => $error_code));
  }
}

/**
 * API specification for "confirm" call on "NewsletterSubscription" entity.
 *
 * @param $params
 */
function _civicrm_api3_newsletter_subscription_confirm_spec(&$params) {
  $params['profile'] = array(
    'name' => 'profile',
    'title' => 'Newsletter profile name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => 'default',
    'description' => 'The Newsletter profile name. If omitted, the default profile will be used.',
  );

  $params['contact_id'] = array(
    'name' => 'contact_id',
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'description' => 'The CiviCRM ID of the contact to confirm newsletter subscriptions for.',
  );

  $params['contact_hash'] = array(
    'name' => 'contact_hash',
    'title' => 'Contact hash',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'description' => 'The CiviCRM hash of the contact to confirm newsletter subscriptions for.',
  );

  $params['mailing_lists'] = array(
    'name' => 'mailing_lists',
    'title' => 'Mailing lists',
    'type' => CRM_utils_Type::T_ENUM,
    'api.required' => 0,
    'description' => E::ts('An array of group IDs as keys and the corresponding group status for the given contact as values.'),
  );

  $params['autoconfirm'] = array(
    'name' => 'autoconfirm',
    'title' => 'Automatic confirmation',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
    'description' => E::ts('Whether to automatically set all "Pending" group memberships to "Added".'),
  );

  $params['unsubscribe_all'] = array(
    'name' => 'unsubscribe_all',
    'title' => 'Unsubscribe all',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
    'api.default' => FALSE,
    'description' => E::ts('Whether to unsubscribe from all group memberships.'),
  );
}
