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
 * Class CRM_Newsletter_Utils
 */
class CRM_Newsletter_Utils {

  /**
   * TODO.
   *
   * @param $contact_data
   *
   * @return int
   *
   * @throws Exception.
   */
  public static function getContact($contact_data) {
    $result = civicrm_api3('Contact', 'getorcreate', $contact_data);
    if ($result['count'] == 1) {
      return reset($result['values'])['contact_id'];
    }
    else {
      throw new Exception(E::ts('Could not get or create a contact for the given contact data.'));
    }
  }

  /**
   * Returns "From" e-mail addresses configured within CiviCRM.
   *
   * @param bool $default
   *   Whether to return only default addresses.
   *
   * @return string
   *   The first "From" e-mail address found.
   */
  public static function getFromEmailAddress($default = FALSE) {
    if ($default) {
      $condition = ' AND is_default = 1';
    }
    else {
      $condition = NULL;
    }
    return reset(CRM_Core_OptionGroup::values('from_email_address', NULL, NULL, NULL, $condition));
  }

  /**
   * Retrieves group subscription statuses for a given contact.
   *
   * @param $contact_id
   *   The CiviCRM ID of the contact to receive group subscription statuses for.
   *
   * @return array
   *   An associative array with group IDs as keys and an associative array as
   *   values as following:
   *   - title: The translated group title
   *   - status: The translated subscription status for the given contact
   *
   * @throws CiviCRM_API3_Exception
   *   When an API call failed.
   */
  public static function getSubscriptionStatus($contact_id) {
    $subscription = civicrm_api3('NewsletterSubscription', 'get', array(
      'contact_id' => $contact_id,
    ));
    $mailing_lists = array();
    foreach (reset($subscription['values'])['subscription_status'] as $group_id => $group_status) {
      $group = civicrm_api3('Group', 'getsingle', array(
        'id' => $group_id
      ));
      $mailing_lists[$group_id] = array(
        'title' => $group['title'],
        'status' => E::ts($group_status),
      );
    }

    return $mailing_lists;
  }

}
