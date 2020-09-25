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
    <fieldset>
      <legend>{ts}General settings{/ts}</legend>
      <table class="form-layout-compressed">

        <tr class="crm-section">
          <td class="label">{$form.name.label}</td>
          <td class="content">{$form.name.html}</td>
        </tr>

        <tr class="crm-section">
          <td class="label">{$form.form_title.label}</td>
          <td class="content">{$form.form_title.html}</td>
        </tr>

        <tr class="crm-section">
          <td class="label">{$form.submit_label.label}</td>
          <td class="content">{$form.submit_label.html}</td>
        </tr>

        <tr class="crm-section">
          <td class="label">{$form.preferences_url.label}</td>
          <td class="content">
            {$form.preferences_url.html}
            <div class="description">
              {ts}A URL to the preferences form. Must include the tokens <code>[PROFILE]</code> and <code>[CONTACT_HASH]</code> which will be replaced with the actual contact hash for identifying the contact.{/ts}
            </div>
          </td>
        </tr>

        <tr class="crm-section">
          <td class="label">{$form.request_link_url.label}</td>
          <td class="content">
              {$form.request_link_url.html}
            <div class="description">
                {ts}A URL to the Request link form. Must include the token <code>[PROFILE]</code> and may include the token <code>[CONTACT_HASH]</code> which will be replaced with the actual contact hash for identifying the contact.{/ts}
            </div>
          </td>
        </tr>

      </table>
    </fieldset>

    <fieldset>
      <legend>{ts}Mailing lists{/ts}</legend>
      <table class="form-layout-compressed">

        <tr class="crm-section">
          <td class="label">{$form.mailing_lists_label.label}</td>
          <td class="content">{$form.mailing_lists_label.html}</td>
        </tr>
        <tr class="crm-section">
          <td class="label">{$form.mailing_lists_description.label}</td>
          <td class="content">{$form.mailing_lists_description.html}</td>
        </tr>
        <tr class="crm-section">
          <td class="label">{$form.mailing_lists.label}</td>
          <td class="content">{$form.mailing_lists.html}</td>
        </tr>

        <tr class="crm-section">
          <td class="label">{$form.mailing_lists_unsubscribe_all.label}</td>
          <td class="content">{$form.mailing_lists_unsubscribe_all.html}</td>
        </tr>

        <tr class="crm-section">
          <td class="label">{$form.mailing_lists_unsubscribe_all_profiles.label}</td>
          <td class="content">{$form.mailing_lists_unsubscribe_all_profiles.html}</td>
        </tr>

        <tr class="crm-section">
          <td class="label">{$form.mailing_lists_unsubscribe_all_submit_label.label}</td>
          <td class="content">{$form.mailing_lists_unsubscribe_all_submit_label.html}</td>
        </tr>
      </table>
    </fieldset>

    <fieldset>
      <legend>{ts domain="de.systopia.newsletter"}Contact fields{/ts}</legend>

      <table class="form-layout">

        {foreach from=$contact_field_names item=contact_field}
          {assign var=field_name_active value=$contact_field.active}
          {assign var=field_name_required value=$contact_field.required}
          {assign var=field_name_label value=$contact_field.label}
          {assign var=field_name_description value=$contact_field.description}

          <tr class="crm-section {cycle values="odd,even"}">
            <td>
              <table class="form-layout-compressed">
                <tr class="crm-section">
                  <td class="label">{$form.$field_name_active.label}</td>
                  <td class="content">{$form.$field_name_active.html}</td>
                </tr>
                <tr class="crm-section">
                  <td class="label">{$form.$field_name_required.label}</td>
                  <td class="content">{$form.$field_name_required.html}</td>
                </tr>
              </table>
            </td>

            <td>
              <table class="form-layout-compressed">
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
    </fieldset>

    <fieldset>
      <legend>{ts}Terms and conditions{/ts}</legend>
      <table>

        <tr class="crm-section {cycle values="odd,even"}">
          <td>
            <table class="form-layout-compressed">
              <tr class="crm-section">
                <td class="label">{$form.conditions_public_label.label}</td>
                <td class="content">{$form.conditions_public_label.html}</td>
              </tr>
              <tr class="crm-section">
                <td class="label">{$form.conditions_public_description.label}</td>
                <td class="content">{$form.conditions_public_description.html}</td>
              </tr>
              <tr class="crm-section">
                <td class="label">{$form.conditions_public.label}</td>
                <td class="content">{$form.conditions_public.html}</td>
              </tr>
            </table>
          </td>
        </tr>

        <tr class="crm-section {cycle values="odd,even"}">
          <td>
            <table class="form-layout-compressed">
              <tr class="crm-section">
                <td class="label">{$form.conditions_preferences_label.label}</td>
                <td class="content">{$form.conditions_preferences_label.html}</td>
              </tr>
              <tr class="crm-section">
                <td class="label">{$form.conditions_preferences_description.label}</td>
                <td class="content">{$form.conditions_preferences_description.html}</td>
              </tr>
              <tr class="crm-section">
                <td class="label">{$form.conditions_preferences.label}</td>
                <td class="content">{$form.conditions_preferences.html}</td>
              </tr>
            </table>
          </td>
        </tr>

      </table>
    </fieldset>

    <fieldset>
      <legend>{ts}E-mail templates{/ts}</legend>
    </fieldset>
    <table>
      <tr class="crm-section  {cycle values="odd,even"}">
        <td>
          <table class="form-layout-compressed">
            <tr class="crm-section">
              <td class="label">{$form.template_optin_subject.label}</td>
              <td class="content">{$form.template_optin_subject.html}</td>
            </tr>
            <tr class="crm-section">
              <td class="label">{$form.template_optin.label}</td>
              <td class="content">
                {$form.template_optin.html}
                <div class="description">
                  {ts}To include the preferences URL, use the variable <code>{literal}{$preferences_url}{/literal}</code>{/ts}
                </div>
              </td>
            </tr>
            <tr class="crm-section">
              <td class="label">{$form.template_optin_html.label}</td>
              <td class="content">
                {$form.template_optin_html.html}
                <div class="description">
                  {ts}To include the preferences URL, use the variable <code>{literal}{$preferences_url}{/literal}</code>{/ts}
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr class="crm-section  {cycle values="odd,even"}">
        <td>
          <table class="form-layout-compressed">
            <tr class="crm-section">
              <td class="label">{$form.template_info_subject.label}</td>
              <td class="content">{$form.template_info_subject.html}</td>
            </tr>
            <tr class="crm-section">
              <td class="label">{$form.template_info.label}</td>
              <td class="content">
                {$form.template_info.html}
                <div class="description">
                  {ts}To include the preferences URL, use the variable <code>{literal}{$preferences_url}{/literal}</code>{/ts}
                </div>
              </td>
            </tr>
            <tr class="crm-section">
              <td class="label">{$form.template_info_html.label}</td>
              <td class="content">
                {$form.template_info_html.html}
                <div class="description">
                  {ts}To include the preferences URL, use the variable <code>{literal}{$preferences_url}{/literal}</code>{/ts}
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr class="crm-section  {cycle values="odd,even"}">
        <td>
          <table class="form-layout-compressed">
            <tr class="crm-section">
              <td class="label">{$form.template_unsubscribe_all_subject.label}</td>
              <td class="content">{$form.template_unsubscribe_all_subject.html}</td>
            </tr>
            <tr class="crm-section">
              <td class="label">{$form.template_unsubscribe_all.label}</td>
              <td class="content">
                {$form.template_unsubscribe_all.html}
                <div class="description">
                  {ts}To include the preferences URL, use the variable <code>{literal}{$preferences_url}{/literal}</code>{/ts}
                </div>
              </td>
            </tr>
            <tr class="crm-section">
              <td class="label">{$form.template_unsubscribe_all_html.label}</td>
              <td class="content">
                {$form.template_unsubscribe_all_html.html}
                <div class="description">
                  {ts}To include the preferences URL, use the variable <code>{literal}{$preferences_url}{/literal}</code>{/ts}
                </div>
              </td>
            </tr>
          </table>
        </td>
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
