# Advanced Newsletter Management

Sending Newsletters with CiviCRM is a common task and there is some useful
functionality in core for that purpose, including Double-Opt-In e-mails,
multiple newsletter groups, etc.

However, when offering multiple mailing lists, subscribers should have a
comfortable way to manage their preferences. Also, CiviCRM forms may not be
suitable for every scenario.

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

Read the docs [here](https://docs.civicrm.org/newsletter/en/latest/) (automatic publishing).

## We need your support
This CiviCRM extension is provided as Free and Open Source Software, 
and we are happy if you find it useful. However, we have put a lot of work into it 
(and continue to do so), much of it unpaid for. So if you benefit from our software, 
please consider making a financial contribution so we can continue to maintain and develop it further.

If you are willing to support us in developing this CiviCRM extension, 
please send an email to info@systopia.de to get an invoice or agree a different payment method. 
Thank you!
