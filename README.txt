=== Message Filter for Contact Form 7 ===
Contributors: kofimokome
Donate link: https://ko-fi.com/kofimokome
Tags: spam, filter
Requires at least: 5.9
Tested up to: 6.0
Stable tag: 1.3.6
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Filter messages submitted through contact form 7 based on words and/or emails listed as restricted.

== Description ==

Do you receive spams every day? have you installed a dozen plugins and you still get spammed? Well this may be the solution to your problem.
This plugin filters messages submitted from contact form 7. You can decide to either filter messages based on restricted words found in the content of the message or filter based on the email of the person submitting the form.
Filters will be extended to other contact form plugins with time.

Note: This is just an extension. This plugin is not affiliated with or endorsed by Contact Form 7.

== Installation ==

1. Download the plugin
2. Install and activate
3. Open Form Filter from your admin menu
4. Go to Options and fill your restricted words and/or emails
5. You can decide to activate the words filter and/or email filter
6. Save and wait for someone to submit

== Changelog ==

= 1.3.6 =
* Fix emoji in filter not working
* Add [emoji] filter

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