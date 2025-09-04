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
use Civi\Api4\OptionValue;

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
    $result = civicrm_api3('Contact', 'createifnotexists', $contact_data);
    if ($result['count'] == 1) {
      return reset($result['values']);
    }
    else {
      throw new Exception(E::ts('Could not get or create a contact for the given contact data.'));
    }
  }

  /**
   * Returns "From" e-mail addresses configured within CiviCRM.
   *
   * @param CRM_Newsletter_Profile|NULL $profile
   *   The newsletter profile. If not given, the default "From" e-mail address
   *   is returned.
   *
   * @return string
   *   The "From" e-mail address defined in $profile. If it doesn't exist, or no
   *   profile is given, the default "From" e-mail address is returned.
   */
  public static function getFromEmailAddress(?CRM_Newsletter_Profile $profile = NULL): string {
    // TODO: Remove check when minimum core version requirement is >= 6.0.0.
    if (class_exists('\Civi\Api4\SiteEmailAddress')) {
      $from_addresses = \Civi\Api4\SiteEmailAddress::get(FALSE)
        ->addSelect('display_name', 'email', 'id')
        ->addWhere('domain_id', '=', 'current_domain')
        ->addWhere('is_active', '=', TRUE)
        ->addOrderBy('is_default', 'DESC')
        ->execute()
        ->indexBy('id')
        ->getArrayCopy();
      // Include "email" column as the option value label did.
      $from_addresses = array_map(
        fn($address) => sprintf('"%s" <%s>', $address['display_name'], $address['email']),
        $from_addresses
      );
    }
    else {
      $from_addresses = OptionValue::get(FALSE)
        ->addSelect('value', 'label')
        ->addWhere('domain_id', '=', 'current_domain')
        ->addWhere('option_group_id:name', '=', 'from_email_address')
        ->addWhere('is_active', '=', TRUE)
        ->addOrderBy('is_default', 'DESC')
        ->execute()
        ->indexBy('value')
        ->column('label');
    }
    // Assuming there is always a default, it's the first element (due to
    // sorting).
    $default = reset($from_addresses);
    return isset($profile) ? $from_addresses[$profile->getAttribute('sender_email')] ?? $default : $default;
  }

  /**
   * Retrieves group subscription statuses for a given contact.
   *
   * @param $contact_id
   *   The CiviCRM ID of the contact to receive group subscription statuses for.
   *
   * @param string $profile_name
   *   The profile name to get subscriptions for. if not provided, default profile is used.
   *
   * @return array
   *   An associative array with group IDs as keys and an associative array as
   *   values as following:
   *   - title: The translated group title
   *   - status: The translated subscription status for the given contact
   *
   * @throws CiviCRM_API3_Exception When an API call failed.
   */
  public static function getSubscriptionStatus($contact_id, $profile_name = 'default') {
    $subscription = civicrm_api3('NewsletterSubscription', 'get', array(
      'contact_id' => $contact_id,
      'profile'    => $profile_name,
    ));
    $mailing_lists = array();
    foreach (reset($subscription['values'])['subscription_status'] as $group_id => $group_status) {
      $group = civicrm_api3('Group', 'getsingle', array(
        'id' => $group_id,
        'return' => ['id', 'title', 'frontend_title']
      ));
      $translatedStatus = [
        'Added' => E::ts('Added'),
        'Pending' => E::ts('Pending'),
        'Removed' => E::ts('Removed'),
      ];
      $mailing_lists[$group_id] = array(
        'title' => !empty($group['frontend_title']) ? $group['frontend_title'] : $group['title'],
        'status' => $translatedStatus[$group_status],
        'status_raw' => $group_status,
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
        'return' => array('children', 'description', 'frontend_description', 'name', 'title', 'frontend_title', 'parents'),
      ));

      // Compose the group item.
      $group_tree_item = array(
        'title' => !empty($group['frontend_title']) ? $group['frontend_title'] : $group['title'],
        'description' => !empty($group['frontend_description']) ? $group['frontend_description'] : (!empty($group['description']) ? $group['description'] : ''),
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

  /**
   * unsubscribes given contact from all group_ids in $groups
   *
   * @param $groups
   *    All current groups the contact shall be unsubscribed from
   * @param $contact_id
   *    Contact Id for unsubscription
   *
   * @return array
   *    Returns array with unsubscribed_ids
   *
   * @throws CiviCRM_API3_Exception
   */
  public static function unsubscribe_all($groups, $contact_id) {
    $unsubscribe_results = [];
    foreach ($groups as $group_id) {
      $unsubscribe_results[$group_id] = civicrm_api3('GroupContact', 'create', array(
        'group_id' => $group_id,
        'contact_id' => $contact_id,
        'status' => 'Removed',
      ));
    }
    return $unsubscribe_results;
  }

  /**
   * Sends a configured e-mail from the profile for the given type.
   *
   * @param $contact
   *   Complete array with contact info from Contact.get.
   * @param $profile
   *   Profile object with current configuration.
   * @param $type
   *   Type of Mail to be created (optin|info|unsubscribe).
   *
   * @throws \Exception
   */
  public static function send_configured_mail($contact, string $contact_checksum, $profile, $type) {
    // Prepare token (Smarty variables) values.
    switch ($type) {
      case 'unsubscribe_all':
        $subject = $profile->getAttribute('template_unsubscribe_all_subject');
        $text_content = $profile->getAttribute('template_unsubscribe_all');
        $html_content = $profile->getAttribute('template_unsubscribe_all_html');
        break;
      case 'info':
        $subject = $profile->getAttribute('template_info_subject');
        $text_content = $profile->getAttribute('template_info');
        $html_content = $profile->getAttribute('template_info_html');
        break;
      case 'optin':
      default:
        $subject = $profile->getAttribute('template_optin_subject');
        $text_content = $profile->getAttribute('template_optin');
        $html_content = $profile->getAttribute('template_optin_html');
        break;
    }

    // Get subscription status.
    $mailing_lists = CRM_Newsletter_Utils::getSubscriptionStatus($contact['id'], $profile->getName());

    // Construct opt-in URL.
    $optin_url = $profile->getAttribute('optin_url');
    $optin_url = str_replace(
      '[CONTACT_CHECKSUM]',
      $contact_checksum,
      $optin_url
    );
    $optin_url = str_replace(
      '[PROFILE]',
      $profile->getName(),
      $optin_url
    );

    // Construct preferences URL.
    $preferences_url = $profile->getAttribute('preferences_url');
    $preferences_url = str_replace(
      '[CONTACT_CHECKSUM]',
      $contact_checksum,
      $preferences_url
    );
    $preferences_url = str_replace(
      '[PROFILE]',
      $profile->getName(),
      $preferences_url
    );

    // Construct e-mail parameters.
    $mail_params = array(
      'from' => CRM_Newsletter_Utils::getFromEmailAddress($profile),
      'toName' => $contact['display_name'],
      'toEmail' => $contact['email'],
      'cc' => '',
      'bc' => '',
      'subject' => $subject,
      'text' => CRM_Core_Smarty::singleton()->fetchWith(
        'string:' . $text_content,
        array(
          'contact' => $contact,
          'mailing_lists' => $mailing_lists,
          'optin_url' => $optin_url,
          'preferences_url' => $preferences_url,
        )
      ),
      'html' => CRM_Core_Smarty::singleton()->fetchWith(
        'string:' . $html_content,
        array(
          'contact' => $contact,
          'mailing_lists' => $mailing_lists,
          'optin_url' => $optin_url,
          'preferences_url' => $preferences_url,
        )
      ),
      'replyTo' => '', // TODO: Make configurable?
    );
    // Send the e-mail.
    if (!CRM_Utils_Mail::send($mail_params)) {
      // TODO: Mail not sent. Maybe do not cancel the whole API call?
      Civi::log()->error(E::LONG_NAME . ': Error sending configured e-mail.');
    }
  }

  /**
   * Update Group subscription for all groups in $mailinglists for given contact_id.
   * Special case for when unsubscribe shall be done for all groups in all profiles
   *
   * @param $mailinglists
   *    Array with group_id => groups_status
   * @param $contact_id
   *    Contact Id for user
   * @param $unsubscribe_from_all_profiles
   *    If true, all groups from all profiles are checked and unsubscribed for this contact
   *
   * @return array
   *    results from unsubscribe keyed by group_id
   *
   * @throws CiviCRM_API3_Exception
   */
  public static function update_group_subscription($mailinglists, $contact_id, $unsubscribe_from_all_profiles) {

    // check if unsubscribe shall be done for **all** groups in all profiles
    if($unsubscribe_from_all_profiles) {
      // get all groups from all profiles and set group status to removed for that id
      $profiles = CRM_Newsletter_Profile::getProfiles();
      foreach ($profiles as $profile) {
        $current_profile_groups = CRM_Newsletter_Utils::getSubscriptionStatus($contact_id, $profile->getName());
        foreach (CRM_Newsletter_Utils::getSubscriptionStatus($contact_id, $profile->getName()) as $group_id => $group_info) {
          $current_profile_groups[$group_id] = 'Removed';
        }
        $mailinglists = $mailinglists + $current_profile_groups;
      }
    }

    // Add/remove group membership.
    $group_contact_results = array();
    foreach ($mailinglists as $group_id => $group_status) {
      $group_contact_results[$group_id] = civicrm_api3('GroupContact', 'create', array(
        'group_id' => $group_id,
        'contact_id' => $contact_id,
        'status' => $group_status,
      ));
    }
    return $group_contact_results;
  }

  public static function gdprx_installed() {
    $manager = CRM_Extension_System::singleton()->getManager();
    return ($manager->getStatus('de.systopia.gdprx') === CRM_Extension_Manager::STATUS_INSTALLED);
  }

}
