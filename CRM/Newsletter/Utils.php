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
   * Retrieves a contact using the Extended Contact Matcher (XCM) extension.
   *
   * @link https://github.com/systopia/de.systopia.xcm
   *
   * @param $contact_data
   *   An associative array with contact data to find, create or update a
   *   contact with, according to the XCM configuration.
   *
   * @return int
   *   The CiviCRM ID of the contact found, created or updated by XCM.
   *
   * @throws Exception.
   *   When no contact could be found or created.
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

  /**
   * Builds a tree array for given groups to include their parents.
   *
   * @param $groups
   *
   * @return array
   *
   * @throws \CiviCRM_API3_Exception
   */
  public static function buildGroupTree($groups) {
    $group_tree = array();
    $group_tree_items = array();

    foreach ($groups as $group_id => $group_title) {
      // Retrieve group information.
      $group = civicrm_api3('Group', 'getsingle', array(
        'id' => $group_id,
        'return' => array('children', 'description', 'name', 'parents'),
      ));

      // Compose the group item.
      $group_tree_item = array(
        'title' => $group_title,
        'description' => !empty($group['description']) ? $group['description'] : '',
        'name' => !empty($group['name']) ? $group['name'] : '',
      );
      // Add children.
      if (!empty($group['children'])) {
        $group_tree_item['children_ids'] = $group['children'];
      }
      // Add first parent.
      if (!empty($group['parents'])) {
        foreach (explode(',', $group['parents']) as $parent_group_id) {
          if (array_key_exists($parent_group_id, $groups)) {
            $group_tree_item['parent'] = $parent_group_id;
            break;
          }
        }
      }

      // Add to the tree and mark processed.
      $group_tree_items[$group_id] = $group_tree_item;
    }

    // Re-arrange groups into their parents, using references for altering
    // already-processed items.
    foreach ($group_tree_items as $group_id => &$group_tree_item) {
      if (!empty($group_tree_item['children_ids'])) {
        foreach (explode(',', $group_tree_item['children_ids']) as $child_group_id) {
          if (array_key_exists($child_group_id, $group_tree_items)) {
            $group_tree_item['children'][$child_group_id] = &$group_tree_items[$child_group_id];
          }
        }
      }

      if (empty($group_tree_item['parent'])) {
        $group_tree[$group_id] = &$group_tree_item;
      }
    }

    return $group_tree;
  }

}
