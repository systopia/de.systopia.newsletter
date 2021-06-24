# Advanced Newsletter Management

Sending Newsletters with CiviCRM is a common task and there is some useful
functionality in core for that purpose, including Double-Opt-In e-mails,
multiple newsletter groups, etc.

However, when offering a lot of mailing lists, users should have means for
managing their subscriptions. Also, CiviCRM forms may not be suitable for every
scenario.

Instead of creating external forms and custom API actions for each project, this
extension offers configurable newsletter form profiles, along with API actions
to use as an endpoint for external forms.

Note that the extension requires the
[Extended Contact Manager (XCM)](https://github.com/systopia/de.systopia.xcm)
extension in version `1.9` or later!

The extension supports for the
[GDPR Compliance](https://github.com/systopia/de.systopia.gdprx) extension, i.e.
providing the option of adding GDPR consent records for newly created contacts
during newsletter subscription, and for the unsubscribe from all mailing lists
event, both with all consent record attributes (such as category, source, etc.)
being configurable.

This extension is supposed to work with the
[civicrm_newsletter](https://github.com/systopia/civicrm_newsletter) Drupal
module for external newsletter management on a Drupal website, but may be used
without it, i.e. anything that implements the extension's API.
