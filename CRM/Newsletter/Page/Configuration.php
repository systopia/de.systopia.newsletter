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

use CRM_Newsletter_ExtensionUtil as E;

class CRM_Newsletter_Page_Configuration extends CRM_Core_Page {

  public function run(): void {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Advanced Newsletter Management configuration'));

    // Show button for settings form if there is anything to configure.
    $settings_form = new CRM_Newsletter_Form_Settings();
    $settings = $settings_form->getFormSettings();
    $this->assign('settings', !empty($settings));

    parent::run();
  }

}
