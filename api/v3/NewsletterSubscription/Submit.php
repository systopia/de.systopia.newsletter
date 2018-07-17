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
 * API callback for "submit" call on "NewsletterSubscription" entity.
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_newsletter_subscription_submit($params) {
  try {
    $profile = CRM_Newsletter_Profile::getProfile($params['profile']);

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

    // Validate submitted group IDs.
    $disallowed_groups = array_diff($params['mailing_lists'], array_keys($profile->getAttribute('mailing_lists')));
    if (!empty($disallowed_groups)) {
      throw new CiviCRM_API3_Exception(E::ts('Disallowed group ID(s): %1', array(
        1 => implode(', ', $disallowed_groups)
      )), 'api_error');
    }

    // Get current group memberships for submitted group IDs.
    $current_groups = civicrm_api3('Contact', 'getsingle', array(
      'id' => $contact_id,
      'return' => 'group',
    ));
    if (!empty($current_groups['is_error'])) {
      throw new CiviCRM_API3_Exception(E::ts('Error retrieving current group membership.'), 'api_error');
    }
    $current_groups = explode(',', $current_groups['groups']);

    // Add "pending" group membership for all new groups.
    $new_groups = array_diff($params['mailing_lists'], $current_groups);
    $groups = array();
    foreach ($new_groups as $group_id) {
      $groups = civicrm_api3('GroupContact', 'create', array(
        'group_id' => $group_id,
        'contact_id' => $contact_id,
        'status' => 'Pending',
      ));
    }

    $contact = civicrm_api3('Contact', 'getsingle', array(
      'id' => $contact_id,
    ));
    $mailing_lists = CRM_Newsletter_Utils::getSubscriptionStatus($contact_id);

    // Send an e-mail with the opt-in template.
    $mail_params = array(
      'from' => CRM_Newsletter_Utils::getFromEmailAddress(TRUE),
      'toName' => $contact['display_name'],
      'toEmail' => $contact['email'],
      'cc' => '',
      'bc' => '',
      'subject' => $profile->getAttribute('template_optin_subject'),
      'text' => CRM_Core_Smarty::singleton()->fetchWith(
        'string:' . $profile->getAttribute('template_optin'),
        array(
          'contact' => $contact,
          'mailing_lists' => $mailing_lists,
          'preferences_url' => $profile->getAttribute('preferences_url'),
        )),
      'html' => '', // TODO: New profile attribute "template_optin_html".
      'replyTo' => '', // TODO: Make configurable?
    );
    if (!CRM_Utils_Mail::send($mail_params)) {
      // TODO: Mail not sent. Maybe do not cancel the whole API call?
    }

    return $groups;
  }
  catch (Exception $exception) {
    $error_code = ($exception instanceof CiviCRM_API3_Exception ? $exception->getErrorCode() : $exception->getCode());
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
      'title' => $field_label,
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
