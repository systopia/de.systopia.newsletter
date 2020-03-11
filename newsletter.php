<?php

require_once 'newsletter.civix.php';
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
  if(function_exists("flexmailer_civicrm_config")) {
    $container->addResource(new \Symfony\Component\Config\Resource\FileResource(__FILE__));
    $container->findDefinition('dispatcher')->addMethodCall('addListener',
      array(\Civi\FlexMailer\Validator::EVENT_CHECK_SENDABLE, '_newsletter_check_sendable', 100)
    );
  }
}

/**
 * Internal function for FlexMailer  Event callback
 * @param \Civi\FlexMailer\Event\CheckSendableEvent $e
 */
function _newsletter_check_sendable(\Civi\FlexMailer\Event\CheckSendableEvent $e){
  CRM_Newsletter_RegisterTokenFlexmailer::register_tokens();
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function newsletter_civicrm_xmlMenu(&$files) {
  _newsletter_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function newsletter_civicrm_postInstall() {
  _newsletter_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function newsletter_civicrm_uninstall() {
  _newsletter_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function newsletter_civicrm_enable() {
  _newsletter_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function newsletter_civicrm_disable() {
  _newsletter_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function newsletter_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _newsletter_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function newsletter_civicrm_managed(&$entities) {
  _newsletter_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function newsletter_civicrm_caseTypes(&$caseTypes) {
  _newsletter_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function newsletter_civicrm_angularModules(&$angularModules) {
  _newsletter_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function newsletter_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _newsletter_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function newsletter_civicrm_permission(&$permissions) {
  $permissions['access Advanced Newsletter Management API'] = 'Advanced Newsletter Management: Access API';
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterAPIPermissions
 */
function newsletter_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  // Restrict API calls to the permission.
  $permissions['newsletter_profile']['get'] = array('access Advanced Newsletter Management API');
  $permissions['newsletter_subscription']['get']  = array('access Advanced Newsletter Management API');
  $permissions['newsletter_subscription']['submit']  = array('access Advanced Newsletter Management API');
  $permissions['newsletter_subscription']['confirm']  = array('access Advanced Newsletter Management API');
  $permissions['newsletter_subscription']['request']  = array('access Advanced Newsletter Management API');
}

/**
 * Implements hook_civicrm_tokens().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tokens
 */
function newsletter_civicrm_tokens(&$tokens) {
  foreach (CRM_Newsletter_Profile::getProfiles() as $profile_name => $profile) {
    $tokens['newsletter']['newsletter.preferences_url_' . $profile_name] = E::ts(
      'Preferences URL for profile %1',
      array(
        1 => $profile->getName(),
      )
    );
    $tokens['newsletter']['newsletter.request_link_url_' . $profile_name] = E::ts(
      'Request link URL for profile %1',
      array(
        1 => $profile->getName(),
      )
    );
  }
}

/**
 * Implements hook_civicrm_tokenValues().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tokenValues
 */
function newsletter_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  if (array_key_exists('newsletter', $tokens)) {
    foreach (CRM_Newsletter_Profile::getProfiles() as $profile_name => $profile) {
      foreach ($cids as $cid) {
        $contact = civicrm_api3('Contact', 'getsingle', array('id' => $cid, 'return' => array('hash')));
        $preferences_url = $profile->getAttribute('preferences_url');
        $preferences_url = str_replace(
          '[CONTACT_HASH]',
          $contact['hash'],
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
          '[CONTACT_HASH]',
          $contact['hash'],
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
