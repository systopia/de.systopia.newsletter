#!/usr/bin/env bash

# rebuild the translation database in machine readable format
# run this script after each update of translation text or
# once new messages had been added.

msgfmt --statistics -o l10n/de_DE/LC_MESSAGES/newsletter.mo l10n/de_DE/LC_MESSAGES/newsletter.po
