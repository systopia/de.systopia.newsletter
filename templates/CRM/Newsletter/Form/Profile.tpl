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

<div class="crm-block crm-form-block">

  {if $op == 'create' or $op == 'edit'}
    <table class="form-layout-compressed">

      <tr class="crm-section">
        <td class="label">{$form.name.label}</td>
        <td class="content">{$form.name.html}</td>
      </tr>

      <tr class="form-section">
        <td><h2>{ts domain="de.systopia.newsletter"}Contact fields{/ts}</h2></td>
      </tr>

      {foreach from=$contact_field_names item=contact_field}
        {assign var=field_name_active value=$contact_field.active}
        {assign var=field_name_label value=$contact_field.label}
        {assign var=field_name_description value=$contact_field.description}

        <tr class="crm-section">
          <td class="label">{$form.$field_name_active.label}</td>
          <td class="content">{$form.$field_name_active.html}</td>

          <td>
            <table class="form-layout">
              <tr class="crm-section">
                <td class="label">{$form.$field_name_label.label}</td>
                <td class="content">{$form.$field_name_label.html}</td>
              </tr>
              <tr class="crm-section">
                <td class="label">{$form.$field_name_description.label}</td>
                <td class="content">{$form.$field_name_description.html}</td>
              </tr>
            </table>
          </td>
        </tr>
      {/foreach}

    </table>

    <table class="form-layout-compressed">

      <tr class="crm-section">
        <td class="label">{$form.mailing_lists.label}</td>
        <td class="content">{$form.mailing_lists.html}</td>
      </tr>

      <tr class="crm-section">
        <td class="label">{$form.conditions_public.label}</td>
        <td class="content">{$form.conditions_public.html}</td>
      </tr>

      <tr class="crm-section">
        <td class="label">{$form.conditions_preferences.label}</td>
        <td class="content">{$form.conditions_preferences.html}</td>
      </tr>

      <tr class="crm-section">
        <td class="label">{$form.template_optin.label}</td>
        <td class="content">{$form.template_optin.html}</td>
      </tr>

      <tr class="crm-section">
        <td class="label">{$form.template_info.label}</td>
        <td class="content">{$form.template_info.html}</td>
      </tr>

      <tr class="crm-section">
        <td class="label">{$form.preferences_url.label}</td>
        <td class="content">{$form.preferences_url.html}</td>
      </tr>

    </table>
  {elseif $op == 'delete'}
    {if $profile_name}
      {if $profile_name == 'default'}
        <div class="status">{ts domain="de.systopia.newsletter" 1=$profile_name}Are you sure you want to reset the default profile?{/ts}</div>
      {else}
        <div class="status">{ts domain="de.systopia.newsletter" 1=$profile_name}Are you sure you want to delete the profile <em>%1</em>?{/ts}</div>
      {/if}
    {else}
      <div class="crm-error">{ts domain="de.systopia.newsletter"}Profile name not given or invalid.{/ts}</div>
    {/if}
  {/if}

  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>

</div>
