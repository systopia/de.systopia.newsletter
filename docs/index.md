# Overview

## Scope

Sending newsletters / mailings with CiviCRM is a common task and there are
useful features in core for that purpose, including Double-Opt-In e-mails,
multiple mailing groups, etc. However, when offering a lot of mailing lists,
users should have more coherent means for managing their subscriptions and
CiviCRM forms may not be suitable for every scenario.

Instead of creating external forms and custom API actions for each project,
this extension offers configurable form profiles for subscription preference
management, along with an API action to use as an endpoint for external forms.

In other words, your organization's staff can configure mailing subscription
preference pages in CiviCRM. The extension will then make this information
available via its REST API and an external system can generate the pages and
present them to your contacts. The extension's API also has built in logic and
actions to receive and update mailing preferences submitted from your external
forms. 

Finally, the extension will provide custom token that can be used in mailings to
direct users to your subscription management pages.

## Features
- Define subscription/preference pages for mailing lists in CiviCRM
- Display those pages on any external system
- Define custom email-templates for your subscription/preference pages (instead
  of CiviCRM's default workflow emails)
- Use a link to a subscription preference page in your mailings instead of an
  unsubscribe or opt out link

## Requirements

- PHP v7.0+
- CiviCRM 5.3
- Dependency on CiviCRM Extension "Extended Contact Matcher" (de.systopia.xcm)
- A system that will serve as your public system such as an external website

## Limits

Using this extension requires that you have or set up an external system to act
as a frontend for your forms. In case you would like to build your forms based
on Drupal you will most likely want to have a look at and/or use the Drupal
module
[CiviCRM Advanced Newsletter Management](https://github.com/systopia/civicrm_newsletter)
which includes a lot of pre-built features. Otherwise, you would have to build
your own endpoint in the system of your choice.

It would also be possible to implement CiviCRM-native forms for this extension,
so you do not have to use an additional system for the forms. If you are
interested in this feature and can provide some funding, please create an issue
and/or contact us (https://www.systopia.de).
