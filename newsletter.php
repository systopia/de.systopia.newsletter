<?php

require_once 'newsletter.civix.php';

use Civi\Newsletter\ContactChecksumService;
use CRM_Newsletter_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function newsletter_civicrm_config(&$config) {
  _newsletter_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_container().
 * @param $container
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container
 */
function newsletter_civicrm_container($container) {
  //  we can only register for flexmailer events if it is installed
  if (function_exists('flexmailer_civicrm_config')) {
    $container->addResource(new \Symfony\Component\Config\Resource\FileResource(__FILE__));
    $container->findDefinition('dispatcher')->addMethodCall('addListener',
      [\Civi\FlexMailer\Validator::EVENT_CHECK_SENDABLE, '_newsletter_check_sendable', 100]
    );
  }
}

/**
 * Internal function for FlexMailer  Event callback
 * @param \Civi\FlexMailer\Event\CheckSendableEvent $e
 */
function _newsletter_check_sendable(\Civi\FlexMailer\Event\CheckSendableEvent $e) {
  CRM_Newsletter_RegisterTokenFlexmailer::register_tokens();
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function newsletter_civicrm_install() {
  _newsletter_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function newsletter_civicrm_enable() {
  _newsletter_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function newsletter_civicrm_permission(&$permissions) {
  $permissions['access Advanced Newsletter Management API'] = [
    'label' => E::ts('Advanced Newsletter Management: Access API'),
    'description' => E::ts(
      'Allows accessing the API for retrieving, creating, and confirming newsletter subscriptions via the CiviCRM Advanced Newsletter Management API.'
    ),
  ];
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterAPIPermissions
 */
function newsletter_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  // Restrict API calls to the permission.
  $permissions['newsletter_profile']['get'] = ['access Advanced Newsletter Management API'];
  $permissions['newsletter_subscription']['get']  = ['access Advanced Newsletter Management API'];
  $permissions['newsletter_subscription']['submit']  = ['access Advanced Newsletter Management API'];
  $permissions['newsletter_subscription']['confirm']  = ['access Advanced Newsletter Management API'];
  $permissions['newsletter_subscription']['request']  = ['access Advanced Newsletter Management API'];
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function newsletter_civicrm_navigationMenu(&$menu) {
  _newsletter_civix_insert_navigation_menu(
    $menu,
    'Administer/Communications',
    [
      'label' => E::ts('Advanced Newsletter Management'),
      'name' => 'newsletter',
      'url' => 'civicrm/admin/settings/newsletter',
      // TODO: Adjust permission once there is a separate one.
      'permission' => 'administer CiviCRM',
      'operator' => 'OR',
      'separator' => 0,
      'icon' => 'crm-i fa-newspaper-o',
    ]
  );
}

/**
 * Implements hook_civicrm_tokens().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tokens
 */
function newsletter_civicrm_tokens(&$tokens) {
  foreach (CRM_Newsletter_Profile::getProfiles() as $profile_name => $profile) {
    $tokens['newsletter']['newsletter.optin_url_' . $profile_name] = E::ts(
      'Opt-in URL for profile %1',
      [
        1 => $profile->getName(),
      ]
    );
    $tokens['newsletter']['newsletter.preferences_url_' . $profile_name] = E::ts(
      'Preferences URL for profile %1',
      [
        1 => $profile->getName(),
      ]
    );
    $tokens['newsletter']['newsletter.request_link_url_' . $profile_name] = E::ts(
      'Request link URL for profile %1',
      [
        1 => $profile->getName(),
      ]
    );
  }
}

/**
 * Implements hook_civicrm_tokenValues().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tokenValues
 */
function newsletter_civicrm_tokenValues(&$values, $cids, $job = NULL, $tokens = [], $context = NULL) {
  if (is_array($cids) && array_key_exists('newsletter', $tokens)) {
    foreach (CRM_Newsletter_Profile::getProfiles() as $profile_name => $profile) {
      foreach ($cids as $cid) {
        $contact_checksum = ContactChecksumService::getInstance()->generateChecksum($cid);

        $optin_url = $profile->getAttribute('optin_url');
        $optin_url = str_replace(
          '[CONTACT_CHECKSUM]',
          $contact_checksum,
          $optin_url
        );
        $optin_url = str_replace(
          '[PROFILE]',
          $profile_name,
          $optin_url
        );
        $values[$cid]['newsletter.optin_url_' . $profile_name] = $optin_url;

        $preferences_url = $profile->getAttribute('preferences_url');
        $preferences_url = str_replace(
          '[CONTACT_CHECKSUM]',
          $contact_checksum,
          $preferences_url
        );
        $preferences_url = str_replace(
          '[PROFILE]',
          $profile_name,
          $preferences_url
        );
        $values[$cid]['newsletter.preferences_url_' . $profile_name] = $preferences_url;

        $request_link_url = $profile->getAttribute('request_link_url');
        $request_link_url = str_replace(
          '[CONTACT_CHECKSUM]',
          $contact_checksum,
          $request_link_url
        );
        $request_link_url = str_replace(
          '[PROFILE]',
          $profile_name,
          $request_link_url
        );
        $values[$cid]['newsletter.request_link_url_' . $profile_name] = $request_link_url;
      }
    }
  }
}
