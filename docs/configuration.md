# Configuration

The Advanced Newsletter Management extension allows multiple configuration sets
be created to provide different mailing list environments that can be made use
of with different forms on the external system (e.g. with the
[Advanced Newsletter Management](https://github.com/systopia/civicrm_newsletter)
Drupal module).

Find the extension configuration form on CiviCRM's *Administration Console*
within the *System settings* section (civicrm/admin/settings/newsletter). This
page offers you to *Configure profiles* along with a
*Configure extension settings* button, does not yet have any function.

## Profiles

There will always be a *default* profile with some more or less meaningful
factory defaults. You should always at least configure this default profile or
create a new one.

Profiles define properties of newsletter subscription and accompanying
preferences forms for users to manage their subscriptions.

### General settings

- *Profile name*: This is the identifier of a configuration set.
- *Form title*: This will be the page title of the subscription form
- *Submit button label*: The form's submit button will have this as a caption.
- *Preferences URL*: This must be set to the URL of the form for users to manage
  their subscription preferences and must include placeholders for the profile
  name `[PROFILE]` and a hash string identifying the CiviCRM contact
  `[CONTACT_HASH]`. The URL will be used for links in e-mails generated and sent
  by the extension to inform users about their subscription and how to manage
  them.

### Mailing lists

This section defines which CiviCRM mailing list groups are available as
selectable newsletters on the subscription and preferences forms.

- *Label for mailing lists selection*: This will be the field label for the
  mailing lists selection form element.
- *Description for mailing lists selection*: This will be a description for the
  mailing lists selection form element.
- *Available mailing lists*: Select all mailing list groups that user should be
  able to subscribe to on forms for this profile.

### Contact fields

This section defines which contact properties should be available and/or
required for subscriptions with this profile.

Along with some pre-defined core contact fields, every custom field for contacts
can be added to the form configuration.

For each field, define:

- whether to *Show contact field <field_name>*
- whether the *Contact field <field_name> is required*
- the *Field label* on the form
- the *Field description* on the form

!!!tip
    If you want to have subscription forms for different languages, define two
    identical profiles with translated field labels and descriptions.
    Of course, selecting different mailing list groups for each particular
    language may be useful, e.g. if you want to send out different newsletters
    per language.

### Terms and conditions

Since newsletter subscriptions involves collecting personal data, the
subscription form should include privacy regulations (or any other kind of terms
and regulations you like).

You can configure different terms and conditions for

- the subscription form
- the preferences form

each with a configurable label and description.

### E-mail templates

When receiving a subscription via the API, the extension will generate and send
an e-mail to the user, informing them about their subscription and how to manage
them with a personalised link to the preferences form.

Also, each time a user submits their preferences form, an info e-mail will be
sent to them, confirming their submission.

For both types of e-mail, you can configure

- the e-mail subject
- the e-mail body

For each, the input is run through CiviCRM's Smarty processor, replacing tokens
for the following data:

- the contact
- the available mailing lists (with subscription status)
- the preferences URL for the contact
