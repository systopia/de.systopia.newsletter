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
 * API callback for "get" call on "NewsletterSubscription" entity.
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_newsletter_subscription_get($params) {
  try {
    if (empty($params['contact_id']) && !isset($params['contact_checksum']) && empty($params['contact_hash'])) {
      throw new CRM_Core_Exception(E::ts('Either the contact ID or the contact checksum is required.'), 'mandatory_missing');
    }

    if (!$profile = CRM_Newsletter_Profile::getProfile($params['profile'])) {
      throw new CRM_Core_Exception(
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
      $contact_checksum = $params['contact_checksum'] ?? $params['contact_hash'];
      $contact_params['id'] = ContactChecksumService::getInstance()->resolveChecksum($contact_checksum);
      if (NULL === $contact_params['id'] && !empty($contact_params['contact_hash'])) {
        // @todo: Remove code used for backward compatibility.
        $contact_params['id'] = civicrm_api3('Contact', 'getsingle', array(
          'return' => ['id'],
          'hash' => $params['contact_hash'],
        ))['id'] ?? NULL;
      }
      if (NULL === $contact_params['id']) {
        throw new CRM_Core_Exception(E::ts('Invalid contact checksum.'), 'unauthorized');
      }
    }

    foreach (array_keys($profile->getAttribute('contact_fields')) as $contact_field) {
      $contact_params['return'][] = $contact_field;
      if ($contact_field == 'country_id') {
        $contact_params['return'][] = 'country';
      }
    }

    $contact = civicrm_api3('Contact', 'getsingle', $contact_params);
    if (!empty($contact['is_error'])) {
      throw new CRM_Core_Exception(E::ts('Could not retrieve contact for given checksum.'), 'api_error');
    }
    // Add secondary phone number if used.
    if (in_array('phone2', $contact_params['return'])) {
      $secondary_phone_type = CRM_Xcm_Configuration::getConfigProfile($profile->getAttribute('xcm_profile'))
        ->secondaryPhoneType();
      if (!empty($secondary_phone_type)) {
        $secondary_phone = \Civi\Api4\Phone::get(false)
          ->addSelect('phone')
          ->addWhere('contact_id', '=', $contact['id'])
          ->addWhere('phone_type_id', '=', $secondary_phone_type)
          ->execute()
          ->first();
        if ($secondary_phone) {
          $contact['phone2'] = $secondary_phone['phone'];
        }
      }
    }
    // Add tertiary phone number if used.
    if (
      method_exists(\CRM_Xcm_Configuration::class, 'tertiaryPhoneType')
      && in_array('phone3', $contact_params['return'])
    ) {
      $tertiary_phone_type = CRM_Xcm_Configuration::getConfigProfile($profile->getAttribute('xcm_profile'))
        ->tertiaryPhoneType();
      if (!empty($tertiary_phone_type)) {
        $tertiary_phone = \Civi\Api4\Phone::get(false)
          ->addSelect('phone')
          ->addWhere('contact_id', '=', $contact['id'])
          ->addWhere('phone_type_id', '=', $tertiary_phone)
          ->execute()
          ->first();
        if ($tertiary_phone) {
          $contact['phone3'] = $tertiary_phone['phone'];
        }
      }
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
      throw new CRM_Core_Exception(E::ts('Error retrieving current group membership.'), 'api_error');
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
      throw new CRM_Core_Exception(E::ts('Error retrieving current group membership.'), 'api_error');
    }
    foreach ($pending_group_contacts['values'] as $group) {
      $current_groups[$group['group_id']] = 'Pending';
    }
    // Restrict to groups defined within the profile.
    $current_groups = array_intersect_key($current_groups, $profile->getAttribute('mailing_lists'));

    $contact_checksum ??= ContactChecksumService::getInstance()->generateChecksum($contact_id);
    $contact['checksum'] = $contact_checksum;
    // @todo: Remove code used for backward compatibility.
    $contact['hash'] = $contact_checksum;

    $return = array(
      array(
        'contact' => $contact,
        'subscription_status' => $current_groups,
        ),
    );

    return civicrm_api3_create_success($return);
  }
  catch (Exception $exception) {
    $error_code = ($exception instanceof CRM_Core_Exception ? $exception->getErrorCode() : $exception->getCode());
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
  $params['contact_checksum'] = array(
    'name' => 'contact_checksum',
    'title' => 'Contact checksum',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description' => 'Generated checksum of the contact to receive subscriptions for.',
  );
  $params['contact_hash'] = array(
    'name' => 'contact_hash',
    'title' => 'Contact hash',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'deprecated' => TRUE,
    'description' => 'Generated checksum of the contact to receive subscriptions for. (Deprecated: Use contact_checksum instead.)',
  );
}
