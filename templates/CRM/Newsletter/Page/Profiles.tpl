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

<div class="crm-block crm-content-block crm-newsletter-content-block">

  <div class="crm-submit-buttons">
    <a href="{crmURL p="civicrm/admin/settings/newsletter/profile" q="op=create"}" title="{ts domain="de.systopia.newsletter"}New profile{/ts}" class="button">
      <span><i class="crm-i fa-plus-circle"></i> {ts domain="de.systopia.newsletter"}New profile{/ts}</span>
    </a>
  </div>

  {if !empty($profiles)}
    <table>
      <thead>
      <tr>
        <th>{ts domain="de.systopia.newsletter"}Profile name{/ts}</th>
        <th>{ts domain="de.systopia.newsletter"}Properties{/ts}</th>
        <th>{ts domain="de.systopia.newsletter"}Operations{/ts}</th>
      </tr>
      </thead>
      <tbody>
      {foreach from=$profiles item=profile}
        {assign var="profile_name" value=$profile.name}
        <tr>
          <td>{$profile.name}</td>
          <td>
            <a href="{crmURL p="civicrm/admin/settings/newsletter/profile" q="op=edit&name=$profile_name"}" title="{ts domain="de.systopia.newsletter" 1=$profile.name}Edit profile %1{/ts}" class="action-item crm-hover-button">{ts domain="de.systopia.newsletter"}Edit{/ts}</a>
            {if $profile_name == 'default'}
              <a href="{crmURL p="civicrm/admin/settings/newsletter/profile" q="op=delete&name=$profile_name"}" title="{ts domain="de.systopia.newsletter" 1=$profile.name}Reset profile %1{/ts}" class="action-item crm-hover-button">{ts domain="de.systopia.newsletter"}Reset{/ts}</a>
            {else}
              <a href="{crmURL p="civicrm/admin/settings/newsletter/profile" q="op=delete&name=$profile_name"}" title="{ts domain="de.systopia.newsletter" 1=$profile.name}Delete profile %1{/ts}" class="action-item crm-hover-button">{ts domain="de.systopia.newsletter"}Delete{/ts}</a>
            {/if}

          </td>
        </tr>
      {/foreach}
      </tbody>
    </table>
  {/if}

</div>
