=== WP Feedback & Survey Manager ===
Contributors: swashata
Donate link: http://www.intechgrity.com/about/buy-us-some-beer/
Tags: feedback, survey, form, web-form, database form, quiz, opinion
Requires at least: 3.3
Tested up to: 3.5
Stable tag: 1.1.4
License: GPLv3

Gather feedbacks and run surveys on your WordPress Blog. Stores the gathered data in database. Displays the form & trends with shortcodes.

== Description ==

= Pro Version =

Now we have released a pro version with tons of new features and updates. Here are our favorite features (which are also exclusive to the pro version only):

* Unlimited form with 100 MCQ/FreeType questions (each) and additional 20 Personal Information fields.
* Custom notification email upon submission both for the user and administrator.
* Capability to channel different feedback topics to different email addresses.
* 24 Preset Themes to start with.
* Ability to customize all the colors/background/fonts of the forms differently for each of the tabs.
* Most beautiful Google WebFonts included. You select your own body and heading fonts.
* Quick analytical graph on the dashboard (FSQM Pro > Dashboard).
* Complete Survey (MCQ) Report Generator. Represents data in Pie Chart, Column Chart, Bar Chart or detailed Tabular form.
* Moderate every feedback/submission, add administrator comments which the user can see using the track link (which can be emailed to them upon submission), even edit a particular submission and update with newer data.
* Change the title and description of every tab and every area of the form. Nothing is hardcoded, you can change every single word.
* Mark submissions "Star". Works just like Gmail (click and done).
* Intuitive admin section, works just like WordPress' own admin panels. So will you feel like home in no time.

Take a look at the [Demonstration](http://ipanelthemes.com/fsqm/) and [Documentation](http://ipanelthemes.com/fsqm-doc/)

[BUY THE PRO VERSION NOW $20](http://codecanyon.net/item/wp-feedback-survey-quiz-manager-pro/3180835?ref=iPanelThemes)

= Free Version =

The concept and working of the Plugin is very simple.

* You setup the form from the Settings Page. Set how many survey questions to show, how many feedbacks to ask for and of course any other personal opinion (or completely disable a feature).
* You use the Shortcodes for displaying on your Site/Blog.
* Finally use the Survey Reports Or View all Feedbacks pages to analyze the submissions.

Sounds easy enough? Even more... Publish the Trends of the survey by showing report based on latest 100 database records cached every hour.

**Also compatible with WordPress MultiSite** Each of the sites can run their own instances of survey and a different set of databases and options will be created.
Infact, this is the only way (now) to have more than one feedback form on your site.

**Caution:** Please do not network activate the plugin. Rather activate it individually for each of the sites where you'd like to have a feedback/survey form.

**Note:** I had to pass in the documentation to the loader class because it is a shortcut to add the documentation to all the slide-in *Help* sidebar. That is why, during instantiating the loader, I have used:
`$wp_feedback = new wp_feedback_loader(__FILE__, 'fbsr', '1.0.1', 'wp_fbsr', 'http://www.intechgrity.com/wp-plugins/wp-feedback-survey-manager/', 'http://wordpress.org/support/plugin/wp-feedback-survey-manager');`
It does not callback the mentioned URL or send in any of your personal or sensitive WordPress information to my server.

= Documentation =

Check the Installation and FAQ page. For detailed documentation check [HERE at out blog](http://www.intechgrity.com/wp-plugins/wp-feedback-survey-manager/)

= Feature List =
* Add Good Looking "tabbed" Feedback/Survey form on your blog easily. The form submission is done through AJAX with a nice effect. Falls back well on browser without JS.
* Both JavaScript as well as PHP validation on form submission.
* Nice design of the form using Google Web Fonts and jQuery UI.
* You can have upto 20 survey questions and feedback topics.
* Each survey question can have any number of options. The options can be single or multiple type.
* Storage of survey and feedbacks on database.
* You can mail different Feedbacks on different emails. Useful if you are working with a collaborative team to collect feedbacks.
* Detailed survey report on admin backend. AJAX-ed fetching of all surveys and displaying using Google Chart (check the screenshots).
* Inline HELP (from the WordPress Admin like sliding Help from every screen) makes it easy for you to understand the various aspects of this plugin.
* Now Survey Questions can be made optional or required (New in 1.1.0)
* Now Feedback Questions can be made optional or required (New in 1.1.0)
* 4 predefined personal informations (First Name, Last Name, Email, Phone Number) are now configurable (New in 1.1.0)
* Can add upto 20 extra personal information questions (New in 1.1.0)
* Can easily rearrange the order of the tabs from Settings (New in 1.1.0)

And many more features... Just check the screenshots.

= Important Notes =
* Version 1.1.0 Released. It holds many new features (marked in the list)
* Version 1.0.0 Released. This is the first public release

= Shortcodes =
This plugin comes with two shortcodes. One for displaying the FORM and other for displaying the Trends (The same Latest 100 Survey Reports you see on this screen)

* `[feedback]` : Just use this inside a Post/Page and the form will start appearing.
* `[feedback_trend]` : Use this to show the Trends based on latest 100 Survey Reports for all available questions. Just like the dashboard widget on this screen.

= Credits =
The very basic & simplest form of the idea of this plugin came from my friend **Arnab Saha** during our Annual College Fest. As the development began, we pondered upon more ideas and finally we released it publicly.

The plugin uses a few free and/or open source products, which are:

* [Google WebFont](http://www.google.com/webfonts/) : To make the form look better.
* [jQuery UI](http://jqueryui.com/) : Renders the basic "Tab Like" appearance of the form.
* [Google Charts Tool](https://developers.google.com/chart/) : Renders the report charts on both backend as well as frontend.
* [jQuery Validation Engine](https://github.com/posabsolute/jQuery-Validation-Engine) : Wonderful form validation plugin from Position-absolute. Please note that we are using version 2.2 of this plugin which works while trying to validate a particular div and all form elements inside it.
* Icons : [Oxygen Icons](http://www.oxygen-icons.org/) and [WooFunctions Icon](http://www.woothemes.com/2009/09/woofunction-178-amazing-web-design-icons/)

Also special thanks to *Prateek Sarkar*, *Sayantan Mukherjee* for helping me with the beta testing of the plugin.

== Installation ==

= Automatic Install =

* Go to *WordPress Admin > Plugins > Add New*
* Search for WP Feedback & Survey Manager
* Install and activate

= Manual Installation =

* Download the latest stable release from here.

* Extract all files from the ZIP file, **making sure to keep the file/folder structure intact**, and then upload it to `/wp-content/plugins/`.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

= Plugin Activation =

Go to the admin area of your WordPress install and click on the "Plugins" menu. Click on "Activate" for the "WP Feedback & Survey Manager" plugin.

= Plugin Usage =

This is pretty much straight forward...

* **Setup**: Go to **Feedback > Settings** and setup the feedbacks and surveys. Remember, you can turn features off if you wish to.
* Use the shortcode `[feedback]` to display the feedback form on your blog
* Goto **Feedback > Survey Reports** or **Feedback > View all feedbacks** to view the reports
* Show the Trend of survey from latest 100 reports using `[feedback_trend]` shortcode

= Upgrading the Plugin =

Automatic update should work a charm. For manual updating, just:
* Deactivate the older version (DO NOT OVERWRITE IT).
* Replace all the files with the new ones.
* Activate the plugin again.
* Under Feedback > Dashboard, if you see same Script Version and Database Version then you are good.
* Otherwise, post your problem on the support forum here.


== Frequently Asked Questions ==

Please visit the [documentation](http://www.intechgrity.com/wp-plugins/wp-feedback-survey-manager/) page for an updated list of FAQs

== Screenshots ==

1. Survey Tab of the Form
2. Feedback Tab of the form
3. Personal Information Tab - showing JS validation
4. Admin - Dashboard
5. Admin - Survey Reports - Generates reports on the basis of all database entries (using ajax loader)
6. Admin - View a single feedback
7. Admin - View all feedbacks
8. Admin - Settings page - modify almost every aspects of the form
9. BONUS - Trends page shortcode - which shows latest 100 survey report (nicely)

== ChangeLog ==

= Version 1.1.4 =
* Maintenance release
* Fixed: A bug where users won't be able to submit without email, even if it is disabled from the admin section
* **Updated**: /classes/install-class.php
* **Updated**: /classes/admin-class.php
* **Updated**: /classes/form-class.php
* **Updated**: /feedback_survey.php
* **Updated**: /readme.txt
* **Updated**: /static/admin/css/admin.css
* **Added**: /static/admin/images/fsqm-banner.jpg
* **Added**: /static/admin/images/fsqm-preview.jpg

= Version 1.1.3 =
* Fixed: A bug in the form which was causing it to submit forever (It was a typo in the code)
* Fixed: Bug on plugin upgrade which won't update the database. (Lack of my knowledge on register_activation_hook)
* Added: Some default cosmetics for the form text-input
* **Updated**: /classes/form-class.php
* **Updated**: /classes/install-class.php
* **Updated**: /classes/loader.php
* **Updated**: /feedback_survey.php
* **Updated**: /readme.txt
* **Updated**: /static/front/css/form.css

= Version 1.1.2 =
* Fixed: Ever-loading Trends page on zero database entries
* Fixed: Readme.txt errors
* Fixed: Some cosmetics issue on the admin settings page
* Fixed: Admin dashboard trends bug (causing it not to show up)
* Added: Working Pro Version Link
* **Updated**: /classes/admin-class.php
* **Updated**: /classes/form-class.php
* **Updated**: /static/admin/css/metabox-tabs.css
* **Updated**: /classes/install-class.php
* **Updated**: /readme.txt

= Version 1.1.1 =
* Fixed: Accidental upload of 1.1.0 (Sorry for it)
* Removed: Pro Version link as it is not ready
* **Updated**: /readme.txt
* **Updated**: /admin-class.php
* **Updated**: /install-class.php
* **Updated**: /translation/fbsr-en_US.pot
* **Updated**: /feedback_survey.php

= Version 1.1.0 =
* Fixed: Shortcode output coming always on top of content
* Fixed: Security loophole under the mathematical captcha
* Fixed: MultiSite uninstallation bugs
* Added: Now Survey Questions can be made optional or required
* Added: Now Feedback Questions can be made optional or required
* Added: 4 predefined personal information are now configurable
* Added: Can add upto 20 extra personal information questions
* Added: Can easily rearrange the order of the tabs from Settings
* **Updated**: /classes/install-class.php
* **Updated**: /classes/admin-class.php
* **Updated**: /changelog
* **Updated**: /static/admin/js/admin.js
* **Updated**: /feedback_survey.php
* **Added** : /static/admin/css/jquery-ui-1.8.23.custom.css
* **Updated**: /static/admin/css/admin.css
* **Updated**: /static/front/css/form.css
* **Updated**: /uninstall.php

= Version 1.0.2 =
* Fixed: Google Chart Page (Trends) problem. Was using json_encode with JSON_FORCE_OBJECT with is available only with PHP 5.3
* Fixed: Cached Trends issue when deleting feedbacks
* **Updated**:/classes/form-class.php
* **Updated**:/classes/admin-class.php
* **Updated**:/feedback_survey.php

= Version 1.0.1 =
* Generated the POT file for translation.
* Fixed some typo in the readme file.
* Added the WP support link to the admin section.
* Added error message on [feedback_trend] for no enabled surveys
* **Added**: /translations/fbsr-en_US.pot
* **Added**: /changelog
* **Updated**: /readme.txt
* **Updated**: /feedback_survey.php
* **Updated**: /classes/install-class.php
* **Updated**: /classes/form-class.php

= Version 1.0.0 =
* Public Release

== Upgrade Notice ==
= 1.1.4 =
Maintenance release
Fixed: A bug where users won't be able to submit without email, even if it is disabled from the admin section

= 1.1.3 =
Fixed: A bug in the form which was causing it to submit forever (It was a typo in the code)
Fixed: Bug on plugin upgrade which won't update the database. (Lack of my knowledge on register_activation_hook)
Added: Some default cosmetics for the form text-input

= 1.1.2 =
Fixed: Ever-loading Trends page on zero database entries
Fixed: Some cosmetics issue on the admin settings page
Fixed: Admin dashboard trends bug (causing it not to show up)

= 1.1.1 =
Fixed: Accidental upload of the Pro version link (which is not working yet)
Fixed: Some minor glitches

= 1.1.0 =
Added: Exciting new features
Released: Pro Version with tons of new features, unlimited form and much more
Fixed: Shortcode output coming always on top of content
Fixed: Security loophole under the mathematical captcha
Fixed: MultiSite uninstallation bugs

= 1.0.2 =
Fixed never-ending loading bar on trends page and survey report generator page on PHP 5.2
Fixed Cached Trends data to update on deleting any feedback

= 1.0.1 =
Maintenance update
Added: POT file for translation

= 1.0.0 =
First public release of the plugin
