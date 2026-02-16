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

declare(strict_types = 1);

/**
 * API callback for "get" call on "NewsletterProfile" entity.
 *
 * @param array<string, mixed> $params
 *
 * @return array<string, mixed>
 */
function civicrm_api3_newsletter_profile_get(array $params): array {
  if (isset($params['name'])) {
    /** @var array<string, mixed> */
    return civicrm_api3('NewsletterProfile', 'getsingle', $params);
  }

  $result = [];

  foreach (CRM_Newsletter_Profile::getProfiles() as $profile_name => $profile) {
    $profile_result = civicrm_api3(
      'NewsletterProfile',
      'getsingle',
      ['name' => $profile_name]
    );
    if (!$profile_result['is_error']) {
      $result[$profile_name] = $profile_result['values'][$profile_name];
    }
  }

  return civicrm_api3_create_success($result);
}

/**
 * API specification for "getsingle" call on "NewsletterProfile" entity.
 *
 * @param array<string, array<string, mixed>> $params
 */
function _civicrm_api3_newsletter_profile_get_spec(array &$params): void {
  $params['name'] = [
    'name' => 'name',
    'title' => 'Newsletter profile name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description' => 'The Newsletter profile name. If omitted, the default profile will be returned.',
  ];
}
