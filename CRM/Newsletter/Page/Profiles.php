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

class CRM_Newsletter_Page_Profiles extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Advanced Newsletter Management profiles'));

    $profiles = [];
    foreach (CRM_Newsletter_Profile::getProfiles() as $profile_name => $profile) {
      $profiles[$profile_name]['name'] = $profile_name;
      foreach (CRM_Newsletter_Profile::allowedAttributes() as $attribute) {
        $profiles[$profile_name][$attribute] = $profile->getAttribute($attribute);
      }
    }
    $this->assign('profiles', $profiles);

    parent::run();
  }

}
