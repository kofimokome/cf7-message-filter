=== Message Filter for Contact Form 7 ===
Contributors: kofimokome
Donate link: https://ko-fi.com/kofimokome
Tags: spam, filter, spam-filter, contact form 7, wpforms
Requires at least: 6.3
Tested up to: 6.6
Stable tag: 1.6.2.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Filter messages submitted through contact form 7 based on words and/or emails listed as restricted.

== Description ==

Do you receive spams every day? have you installed a dozen plugins and you still get spammed? Well this may be the solution to your problem.
This plugin filters messages submitted from contact form 7. You can decide to either filter messages based on restricted words found in the content of the message or filter based on the email of the person submitting the form.
Filters will be extended to other contact form plugins with time.

== Supported Plugins ==

1. Contact form 7
2. WPForms

Note: This is just an extension. This plugin is not affiliated with or endorsed by Contact Form 7 or WPForms.

== Pro Features ==
Upgrade to the pro version from the Account submenu page to have access the following features:
1. Unlimited words and emails: Add as many words and emails as you want
2. Month Reports: Receive monthly spam reports directly to your email
3. Spam Suggestion: Receive suggestions for new spam words and emails
4. Blacklist/Whitelist forms: Decide which forms to validate or not to validate
5. CSV Upload: Upload CSV with spam words/emails
6. Add custom filters: Create your own custom filters

== Installation ==

1. Download the plugin
2. Install and activate
3. Open CF7 Form Filter from your admin menu
4. Go to Options and fill your restricted words and/or emails
5. You can decide to activate the words filter and/or email filter
6. Save and wait for someone to submit

== PRIVACY ==
We may collect ONLY the following information, if accepted by the site administrator:
- The messages blocked by the plugin and
- Words added to the plugin as spam
This is used solely for the purpose of making improvements to the plugin.

In addition to the above, Freemius, a third party plugin used to manage plugin licences may also collect additional information, if the site administrator accepts.




== Frequently Asked Questions ==
= Where do I report security bugs found in this plugin? =
How can I report security bugs?

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/cf7-message-filter)

== Changelog ==

= 1.6.2.1=
* Fix Ukrainian anthem playing on Russian sites
* Update Freemius

= 1.6.2=
* Fix XXS issue on old messages page
* Fix filters with apostrophes not working as expected

= 1.6.1.1 =
* Add ability to enable or disable filter modifiers in the new tag UI
* Update Freemius

= 1.6.1 =
* Fix values from checkboxes and select fields not showing when viewing a blocked message
* Fixes to the new tag UI

= 1.6.0 =
* Updated the frequency charts.
* Fixes to the new tag UI: Assign different colors to different types of filters.
* You can now create your own custom filters.
* Added 8 new filter modifiers: startsWith, endsWith, startsWithExcluding, endsWithExcluding, contains, containsExcluding, containsExcludingEnd and containsExcludingStart

= 1.5.5 =
* Make visible columns persistent across different browsers/sessions.
* Minor fixes to the new tag UI.

= 1.5.4.1 =
* Fix wrong values on yearly statistics graph.

= 1.5.4 =
* New tag UI. This new UI will enable you to easily make changes to a single filter.
* You can now select & modify the words you want to import from a CSV file.
* Fix January 2024 showing at the beginning of the 1 year statistics graph
* Fix filters being case sensitive.
* Update Freemius

= 1.5.3.1 =
* Remove Black Friday notice

= 1.5.3 =
* Fix Flamingo saving blocked messages

= 1.5.2 =
* Update Freemius
* Add Black Friday coupon

= 1.5.1 =
* Update WordPressTools library

= 1.5.0 =
* Fix bug with the [emoji] filter.
* Updated the graphs on the dashboard
* Added monthly email report feature

= 1.4.8 =
* Fix bug with the CF7 Conditional Fields Plugin.
* Add Ability to select the forms to validate or not to validate (Blacklist/Whitelist).

= 1.4.7 =
* Fix bug when adding a custom field on the WPForm settings page.
* Fix text fields not checked for spam.

= 1.4.6 =
* Fix bug when selecting multiple messages. Sometimes the delete button disappears when selecting multiple messages.

= 1.4.5 =
* Fix bug when deleting multiple messages

= 1.4.4 =
* Add the ability to mark a message as not spam

= 1.4.3 =
* Fix disappearing fields on save

= 1.4.2 =
* Upgrade guide updated

= 1.4.1 =
* Fix migrations not running on upgrade

= 1.4.0 =
* Add support for WPForms
* Add premium plugin

= 1.3.6 =
* Fix emoji in filter not working
* Add [emoji] filter
* Add option to show a success message if a spam is found.
* Fix single line text filters not working

= 1.3.5 =
* Fix wrong filter name on the filters page

= 1.3.4 =
* Add [japanesse] [hiragana] [katakana] and [kanji] filters
* Compatibility with WordPress 6.0

= 1.3.3 =
* Add tests
* Compatibility with WordPress 5.9
* Update min php version to 5.6

= 1.3.2 =
* Escape HTML tags in message content

= 1.3.1 =
* Add compatibility with WordPress 5.8

= 1.3.0 =
* Fix bug when filter sees words separated with space as two words
* Fix filter not working for words like ".online"

= 1.2.5 =
* Add auto-clear messages function
* Move log file to uploads folder
* Grouped blocked messages per form
* Bug fixes

= 1.2.4 =
* Fix bug with some messages with links bypassing the checks

= 1.2.3 =
* Add option to filter other text fields
* Update [link] filter to filter urls without protocols
* Other Bug Fixes

= 1.2.2 =
* Add custom error messages
* Add ability to filter messages containing links using [link] keyword
* Bug Fixes

= 1.2.1 =
* Fix unicode characters not displaying in all messages page

= 1.2.0 =
* Refactored Codes
* New dashboard UI


== Screenshots ==

1. Dashboard
2. Blocked messages
3. Options

== How to Contribute ==
The source codes can be downloaded here [GitHub](https://github.com/kofimokome/cf7-message-filter)