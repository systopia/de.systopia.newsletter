{*------------------------------------------------------------+
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
+-------------------------------------------------------------*}
<div class="crm-block crm-content-block">
  <div class="crm-submit-buttons">

    <a href="{crmURL p="civicrm/admin/settings/newsletter/profiles"}" title="{ts escape='htmlattribute' domain="de.systopia.newsletter"}Profiles{/ts}" class="button">
      <span>{ts domain="de.systopia.newsletter"}Configure profiles{/ts}</span>
    </a>

    {if $settings}
      <a href="{crmURL p="civicrm/admin/settings/newsletter/settings"}" title="{ts escape='htmlattribute' domain="de.systopia.newsletter"}Settings{/ts}" class="button">
        <span>{ts domain="de.systopia.newsletter"}Configure extension settings{/ts}</span>
      </a>
    {/if}

  </div>
</div>