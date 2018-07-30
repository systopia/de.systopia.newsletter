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
 * API callback for "get" call on "NewsletterSubscription" entity.
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_newsletter_subscription_get($params) {
  try {
    if (empty($params['contact_id']) && empty($params['contact_hash'])) {
      throw new CiviCRM_API3_Exception(E::ts('Either the contact ID or the contact hash is required.'), 'mandatory_missing');
    }

    if (!$profile = CRM_Newsletter_Profile::getProfile($params['profile'])) {
      throw new CiviCRM_API3_Exception(
        E::ts('No profile found with the given name.'),
        'api_error'
      );
    }

    // Retrieve contact data.
    $contact_params = array(
      'return' => array('id'),
    );
    if (!empty($params['contact_id'])) {
      $contact_params['id'] = $params['contact_id'];
    }
    else {
      $contact_params['hash'] = $params['contact_hash'];
    }
    foreach (array_keys($profile->getAttribute('contact_fields')) as $contact_field) {
      $contact_params['return'][] = $contact_field;
    }

    $contact = civicrm_api3('Contact', 'getsingle', $contact_params);
    if (!empty($contact['is_error'])) {
      throw new CiviCRM_API3_Exception(E::ts('Could not retrieve contact for given hash.'), 'api_error');
    }
    $contact_id = $contact['id'];

    // Get current group memberships for submitted group IDs.
    $current_groups = array();
    $added_group_contacts = civicrm_api3('GroupContact', 'get', array(
      'contact_id' => $contact_id,
      'status' => 'Added',
      'options.limit' => 0,
      'return' => array('group_id'),
    ));
    if (!empty($added_group_contacts['is_error'])) {
      throw new CiviCRM_API3_Exception(E::ts('Error retrieving current group membership.'), 'api_error');
    }
    foreach ($added_group_contacts['values'] as $group) {
      $current_groups[$group['group_id']] = 'Added';
    }
    $pending_group_contacts = civicrm_api3('GroupContact', 'get', array(
      'contact_id' => $contact_id,
      'status' => 'Pending',
      'options.limit' => 0,
      'return' => array('group_id'),
    ));
    if (!empty($pending_group_contacts['is_error'])) {
      throw new CiviCRM_API3_Exception(E::ts('Error retrieving current group membership.'), 'api_error');
    }
    foreach ($pending_group_contacts['values'] as $group) {
      $current_groups[$group['group_id']] = 'Pending';
    }
    // Restrict to groups defined within the profile.
    $current_groups = array_intersect_key($current_groups, $profile->getAttribute('mailing_lists'));

    $return = array(
      array(
        'contact' => $contact,
        'subscription_status' => $current_groups,
        ),
    );

    return civicrm_api3_create_success($return);
  }
  catch (Exception $exception) {
    $error_code = ($exception instanceof CiviCRM_API3_Exception ? $exception->getErrorCode() : $exception->getCode());
    return civicrm_api3_create_error($exception->getMessage(), array('error_code' => $error_code));
  }
}

/**
 * API specification for "get" call on "NewsletterSubscription" entity.
 *
 * @param $params
 */
function _civicrm_api3_newsletter_subscription_get_spec(&$params) {
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
    'title' => 'CiviCRM contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 0,
    'description' => 'The CiviCRM ID of the contact which to receive subscriptions for.',
  );
  $params['contact_hash'] = array(
    'name' => 'contact_hash',
    'title' => 'CiviCRM contact hash',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description' => 'The CiviCRM hash of the contact which to receive subscriptions for.',
  );
}
