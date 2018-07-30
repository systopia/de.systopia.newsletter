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

    // Validate submitted group IDs.
    $disallowed_groups = array_diff(array_keys($params['mailing_lists']), array_keys($profile->getAttribute('mailing_lists')));
    if (!empty($disallowed_groups)) {
      throw new CiviCRM_API3_Exception(E::ts('Disallowed group ID(s): %1', array(
        1 => implode(', ', $disallowed_groups)
      )), 'api_error');
    }

    // Add/remove group membership.
    $group_contact_results = array();
    foreach ($params['mailing_lists'] as $group_id => $group_status) {
      $group_contact_results[$group_id] = civicrm_api3('GroupContact', 'create', array(
        'group_id' => $group_id,
        'contact_id' => $params['contact_id'],
        'status' => $group_status,
      ));
    }

    $mailing_lists = CRM_Newsletter_Utils::getSubscriptionStatus($contact_id);

    // Send an e-mail with the info template.
    $mail_params = array(
      'from' => CRM_Newsletter_Utils::getFromEmailAddress(TRUE),
      'toName' => $contact['display_name'],
      'toEmail' => $contact['email'],
      'cc' => '',
      'bc' => '',
      'subject' => $profile->getAttribute('template_info_subject'),
      'text' => CRM_Core_Smarty::singleton()->fetchWith(
        'string:' . $profile->getAttribute('template_info'),
        array(
          'contact' => $contact,
          'mailing_lists' => $mailing_lists,
          'preferences_url' => str_replace(
            '[CONTACT_HASH]',
            $contact['hash'],
            $profile->getAttribute('preferences_url')
          ),
        )
      ),
      'html' => '', // TODO: New profile attribute "template_optin_html".
      'replyTo' => '', // TODO: Make configurable?
    );
    if (!CRM_Utils_Mail::send($mail_params)) {
      // TODO: Mail not sent. Maybe do not cancel the whole API call?
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
    'api.required' => 1,
    'description' => E::ts('An array of group IDs as keys and the corresponding group status for the given contact as values.'),
  );
}
