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

}
