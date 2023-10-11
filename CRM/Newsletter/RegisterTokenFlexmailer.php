<?php
/*------------------------------------------------------------+
| SYSTOPIA Advanced Newsletter Management                     |
| Copyright (C) 2018 SYSTOPIA                                 |
| Author: P.Batroff (batroff@systopia.de)                     |
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
 * Class CRM_Newsletter_RegisterTokenFlexmailer
 */
class CRM_Newsletter_RegisterTokenFlexmailer {

  /**
   * Register Tokens with Flexmailer on \Civi\FlexMailer\Event\CheckSendableEvent
   */
  public static function register_tokens() {
    $additional_profiles = [];
    foreach (CRM_Newsletter_Profile::getProfiles() as $profile_name => $profile) {
      $additional_profiles['newsletter.optin_url_' . $profile_name] = E::ts("Newsletter Profile Link from de.systopia.newsletter for profile %1", [1 => $profile_name]);
      $additional_profiles['newsletter.preferences_url_' . $profile_name] = E::ts("Newsletter Profile Link from de.systopia.newsletter for profile %1", [1 => $profile_name]);
    }
    // get Tokens from Service
    $allowed_flexmailer_tokens = \Civi::service('civi_flexmailer_required_tokens')->getRequiredTokens();
    foreach ($allowed_flexmailer_tokens as $key => $value) {
      // check if minimal filter is in $key
      if (strstr($key, "action.optOutUrl or action.unsubscribeUrl")) {
        unset($allowed_flexmailer_tokens[$key]);
        $key .= ' or newsletter.optin_url';
        $key .= ' or newsletter.preferences_url';
        $allowed_flexmailer_tokens[$key] = array_merge($value, $additional_profiles);
        break;
      }
    }
    // set Tokens for Service
    \Civi::service('civi_flexmailer_required_tokens')->setRequiredTokens($allowed_flexmailer_tokens);
  }
}
