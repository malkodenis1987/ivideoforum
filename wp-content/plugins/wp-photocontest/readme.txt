=== PhotoContest Plugin ===
Contributors: Frank van der Stad
Donate link: http://www.vanderstad.nl/wp-photocontest/donate
Tags: photocontest, images, photos, photo contest, contest, thumbnail, pictures, post, automatic, voting, vote
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 1.5.6

This plugin automatically turns a WordPress-page into a photo contest.

== Description ==

= Important =
* WHEN USING AUTO-UPGRADE WITH VERSION BELOW 1.3.5, BE SURE TO BACKUP YOUR DATABASE, THE CONTEST_HOLDER AND SKINS DIRECTORY.
* From version 1.3.5 and higher, the auto-upgrading should work. If the restore fails, copy the upgrade/wp-photocontest_[date]/* to the plugins/wp-photocontest. Note: The following directories need write permission for the updater AND the user apache runs under:
  - /wp-content/upgrade
  - /wp-content/plugins
  - /wp-content/plugins/wp-photocontest
  - /wp-content/plugins/wp-photocontest/*
* If the contest doesn't work after the upgrade, please use the "Force database upgrade!" link in the setting page!

= Description =
This plugin permits you to create a 'voting for photos-contest' from the WordPress admin panel 
Subscribed users can uploads photos and everyone else can vote for the uploaded photos.

When creating a contest, you can set the voting period, the period uploaders can upload photos
and you can set the maximum number of photos per user.

This plugin will create a Wordpress page with all the necessary links to handle the uploading, 
voting and subscribing to your blog.

= Features =

* Simple form to create, edit and delete a contest in the Wordpress Admin panel
* Active/Deactivate photos in the Wordpress Admin panel
* View vote-details for each photo in the Wordpress Admin panel
* Sidemenu 'PhotoContest' in the Wordpress Admin panel
* Autocreate small, medium, large and user defined thumbnails on the upload
* Check for a human voter (with captcha). The voter is identified with cookie
* Sends mail to admin when a upload occurs in the contest
* Uploading is permitted to subscribed users only
* Last uploaded, most viewed and most voted pages
* Themeable with an template file (See Customization)
* (new) Uses user details when user is logged in.
* (new) Improved activate, deactivate, auto-upgrade and uninstall routines.
* (new) Different options for registering the votes. Option list, star rating or hidden votes.
* (new) Assign different page/post options for each contest. (group contests on beneath 1 page
  or add different contest in posts)
* (new) You can install a widget with the latest photos in you sidebar. (create by -gnom-)
* (new) To prevent dummy-voting, you can now ask the voter to confirm the vote by email

= Customization =

The plugin provides a config entry in wp-photocontest-config.php file to skins the pages 
created by WP-PhotoContest. This entry points to a directory underneath the 'skins' directory.
This directory must contain the following files:

* template.tpl	: This file is used as template for the pages created by WP-PhotoContest (*)
* theme.css		: This stylesheet is used for styling the pages created by WP-PhotoContest

To create a new skin, you can copy the aqua directory in the directory 'skins' into and other
directory and edit as aproperiate. Then change the 'CONTESTS_SKIN' contant in 
wp-photocontest-config.php file.

(*) Important: 
When using a theme, you need to copy page.php or index.php to template.tpl
and replace "The\_Loop" with [WP-PHOTOCONTEST CONTENT]. See for more information:
'http://codex.wordpress.org/The\_Loop'

There needs to be a class called 'content_wppc'. The contest will be placed in the HTML-tag
with this class. 


== Installation ==

1. Unzip the downloaded file, you should end up with a folder called "wp-photocontest".
2. Upload the "wp-photocontest" folder to your "plugin" directory (wp-content/plugins).
3. Make sure that the file permissions of the "wp-photocontest" directory are such that 
   the plugin is allowed to write to it (otherwise, the uploaded photos cannot be stored).
   (Double check the  file permissions for the "contests_holder". When you run php
   in safe_mode, you need to set it to the same UID (owner) as the script is being executed)
4. If your web hosting provider enabled the mod_security Apache module on your web server, 
   you need to add the following directives to your .htaccess file in order for batch uploads 
   to work:
<code>
<IfModule mod_security.c>
	SecFilterEngine Off
	SecFilterScanPOST Off
</IfModule>
</code>
5. Go to the "Plugins" Wordpress admin panel and activate the PhotoContest plugin.

= Optional =
1. When you would like people to upload, you need to let visitors subscribe to your blog)
2. When you use a theme, you need to create a template file. Read 'Customization')

= Uninstall =
1. Backup your CONTEST_HOLDER and SKINS directory. 
2. Remove the wp-photocontest directory.
3. Run the following sql commands (in phpmyadmin)
	`DROP TABLE IF EXISTS wp_photocontest;`
	`DROP TABLE IF EXISTS wp_photocontest_admin;`
	`DROP TABLE IF EXISTS wp_photocontest_config;`
	`DROP TABLE IF EXISTS wp_photocontest_votes;`
	`DELETE FROM wp_options WHERE option_name = 'wppc_redirect_to' LIMIT 1;`
	`DELETE FROM wp_options WHERE option_name = 'PC_DB_VERSION' LIMIT 1;`
	`DELETE FROM wp_options WHERE option_name = 'PC_PL_VERSION' LIMIT 1;`


== Screenshots ==
1. WordPress Admin panel:Listing the contests
2. WordPress Admin panel:Adding a contest
3. WordPress Admin panel:View contest details
4. WordPress Admin panel:Viewing contest voters
5. Blog: Introduction
6. Blog: Photodetails and voting
7. Blog: Photo uploading


== Frequently Asked Questions ==
= Howto uninstall the plugin manually =
Backup your CONTEST_HOLDER and SKINS directory. Remove the wp-photocontest directory and run the following sql commands (in phpmyadmin)
DROP TABLE IF EXISTS wp_photocontest;
DROP TABLE IF EXISTS wp_photocontest_admin;
DROP TABLE IF EXISTS wp_photocontest_config;
DROP TABLE IF EXISTS wp_photocontest_votes;
DELETE FROM wp_options WHERE option_name = 'wppc_redirect_to' LIMIT 1;
DELETE FROM wp_options WHERE option_name = 'PC_DB_VERSION' LIMIT 1;
DELETE FROM wp_options WHERE option_name = 'PC_PL_VERSION' LIMIT 1;
DELETE FROM wp_options WHERE option_name = 'widget_wp-photocontest-widget' LIMIT 1;
DELETE FROM wp_options WHERE option_name = 'wppc_redirect_to' LIMIT 1;

= Does the plugin use permalinks =
This plugin doesn't use any permalinks and will not be integrated in the near future (or anyone is interested to help development!)

= After voting the contest doesn't appear in my site (shows page without design) =
Two possible reasons:
1) You have some javascript error. Please fix that error first before trying again
2) Read the Customization-part of this readme!!

= Can I use a different template for the individual contests =
Nope, just one template is used!

= How does the templating works (developer view) =
The plugin creates a page with a link to enter the contest (or if you use the refresh option in the admin panel, a view of the latest 9 pictues). 
All other pages after that are generated by the plugin. Other have reported that the first versions of this plugin, broke their sites (mainly the position of the sidebar). I have created a function that will read the wp-photocontest/skins/<skinname>/templates.tpl file and replaces the string <blockquote>[WP-PHOTOCONTEST CONTENT]</blockquote> with the generated content. You need to create the templates file or else the plugin will print a blank page with a warning and the generated content. View the <a href="http://wordpress.org/extend/plugins/wp-photocontest/">Customization</a> part of the documentation.

= Will autoupgrading remove all my contests? =
If you are unlucky it will. We try to prevent this, but we can't promise it.
Version 1.3 will hold the user-generated into the wp-content folder.

= Tell me more about permissions =
Take a look here: http://www.nerdgrind.com/wordpress-automatic-upgrade-plugin-failed-or-not-working/

= Will the plugin delete the page/post when I delete a photocontest? =
No, it won't delete the page/post. To delete the page/post you need to follow the 
directions to delete a page/post.

= Can I change the directory where the contest-files are saved? =
Yes, the target directory can only be a subdirectory in the "wp-photocontest" directory. 

= How can I change the directory where the contest-files are saved? =
Create the directory, make it writeable and change the 'CONTESTS_PATH' contant in 
wp-photocontest-config.php file.

= Can I change the look and feel of the contest? =
Copy the aqua directory in the directory 'skins' and edit as aproperiate. Then change 
the 'CONTESTS_SKIN' contant in wp-photocontest-config.php file. Read 'Customization'!

= How to permit users to subscribe in order to upload the photo in the contest? =
Go in the admin panel, in the Settings section you can able visitors to subscribe to your blog. 
When you done, it will be visible the link "subscribe" in the requested login page.

= I am having a problem with mkdir. Everytime I try to create a contest I get an error. =
Check if you have safe_mode enabled, because when safe mode is enabled, PHP checks 
whether the directory in which the script is operating has the same UID (owner) as the 
script that is being executed. Note: Since version 1.2 we do some extra checks and return
more detailed errors.

= Is there any way to change the layout/text in the view of the contest? =
Yes, you can. Open images/polaroid.fla and edit as wanted. Upload polaroid.swf to you skins 
directory. (So, don't upload it in the images directory)

= Where can I get other answers to my questions regarding wp-PhotoContest? =
First check: http://wordpress.org/tags/wp-photocontest?forum_id=10
If your question is not there, put it there and send me a mail (antispam@vanderstad.nl)!!


== Changelog ==
= 1.5.6 - 16.03.2011  =
* (added) Added a previous and next entry link when viewing a entry
* (fixed) The pagination on the homepage / post links gave a 404-error. 

= 1.5.5 - 11.03.2011  =
* (added) The upload and intro text now can have the tags br, h1, h2 and h3 with classes
* (changed) When viewing photodetails, by default the full emailaddress is replaced by the part before the @-sign.
* (added) An administrator can change the status of the votes in the wp-admin panel.
* (added) Option to return the contest in the polariod theme or in plain HTML.
* (added) Integration of translation to Bulgarian
* (added) Integration of translation to German
* (added) Integration of translation to Russia
* (added) Automatic removal of any ! in the contest name, so the frontend is logical
* (fixed) The scrolling of the different pages (chrono, recent) are wrong. 
* (added) If the sending of a confirm vote-mail failed, the plugin tries to send it to the administrator
* (fixed) In some configurations, different people reported that after the first upload everyone else saw the error "You already uploaded a ...."
* (added) The variable voted is returned in view.php. So we can check if you already voted on that image
* (added) You can now sort the pictures in any way within the widget

= 1.5.4 - 23.09.2010  =
* (fixed) After returning to a photo on which you voted, you didn't see the picture only an error.
* (added) Javascript alert when no div with class content_wppc exits

= 1.5.3 - 22.09.2010  =
* (fixed) Check for the setting VISIBLE_VOTING was wrong. Reason why you alway had a confirm you vite by email
* (added) Mail sent for confirm vote now uses the Site Title and E-mail address details from General Settings

= 1.5.2 - 13.09.2010  =
* (fixed) Voting failed when chosing a dropdown as vote mechanism
* (added) Setting REDIRECT_AFTER_VOTE. Setting this to true, a link back to the contest is provided after voting

= 1.5.1 - 05.09.2010  =
* Support for WP3+
* You can edit the intro and the upload text after creating the contest
* Fix for "Photo uploading fails if user enters an email with one or more dots in the name (aka frank.from.the.city@bla.com)  [Partial fix by -rilana-]
* Added an setting VISIBLE_VOTING. It is now possible to moderate photo votes by sending out email to confirm emailaddress.
* Fix for checking the file types (only png, jpg or gif are allowed)
* Sending registering mail with the correct url to the contest
* Small bugfixes.

= 1.5.0 - 05.09.2010  =
* Skipped version

= 1.4.4 - 14.03.2010  =
* Fix for "You do not have sufficient permissions to access this page" error. (Open settingspage and click on the link)
* Fix a can't vote twice error
* Visible upload not working. Fix a saving to database error.
* Added option to skip the captha
* Fixed a saving setting bug after upgrading
* Fixed a strange thing, that after activating the plugin php-errors are shown on your site
* Fixed translation erro in the flash (x vote/votes)
* Now you can use HTML again in the intro/upload text

= 1.4.3 - 06.03.2010  =
* Skipped version

= 1.4.2.1 - 12.01.2010  =
* Fix for "There is no object with id upload_email" error.
* Fix a array_reduce error
* Integration of translation to Polish

= 1.4.2 - 11.01.2010  =
* Fix for wordpress 2.7.1 and "There is no object with id upload_email" error

= 1.4.1 - 11.01.2010  =
* Accedentaily release an unfinished version (really hate the fact you can't simulate a auto-upgrade).

= 1.4 - 11.01.2010  =
* Fixed a translation error (with relative to skins)
* Fix with creating thumbnails if gif-gd is not installed
* Fix msgid "The vote must be a number between 1 and 10." This message appears even if rating is set to 1-5.
* Provided a msgid msgid_plural for "%d votes".
* Added a translation option for all "No %s provided" messages.
* Added a translation option for 'Cancel Rating', jquery.rating.js line 322
* Added an setting VISIBLE_UPLOAD. It is now possible to moderate photo submissions in the backend.
* Added a 'already voted' check when viewing an image.
* Added an setting ROLE_VOTING. It is now possible to assign a role for allowing voting.
* Added an setting ROLE_UPLOAD. It is now possible to assign a role for allowing uploading.
* Added an setting N_PHOTO_X_PAGE. Defines the number of picutes per page.
* Exception thrown in IE 8 in swfobject.js [Fixed by -gnom-]
* Add a widget for you sidebar [Created by -gnom-]
* Fixed a problem with compatibility with wp-polls (Invalid Poll ID. Poll ID #0)
* Add lightbox rel to the photo in the photo details
* Fixed showing all the photos, instead of only 10 photos in admin panel 'View Photocontest'
* Fixed showing some photos twice in the frontend

= 1.3.6 - 20.11.2009  =
* Added functionality so after register you get redirected to contest page
* Fix a problem with wordpress 2.7 (Call to undefined function wp_lostpassword_url())
* Fix a problem with other conflicting plugins (calls to jQuery)
* Fix a php-notice in viewimg.php
* Fix a translation render error (charset changed to italian fix)

= 1.3.5 - 13.11.2009  =
* From this version on, the auto-upgrading should work. If the restore fails, copy the upgrade/wp-photocontest_[date]/* to the
  plugins/wp-photocontest. Note: The following directories need write permission for the updater AND the user apache runs under:
  - /wp-content/upgrade
  - /wp-content/plugins
  - /wp-content/plugins/wp-photocontest
  - /wp-content/plugins/wp-photocontest/*

= 1.3.4 - 13.11.2009  =
* Changed the way contests are restored.

= 1.3.3 - 11.11.2009  =
* Fixed a bug where the settingspage didn't have any values prefilled.
* Changed the way contest are deleted. Contest get deleted even when de checks fail. Warning are still printed.

= 1.3.2 - 11.11.2009  =
* Fixed a upgrade problem

= 1.3.1- 11.11.2009  =
* Added different options for registering the votes. Option list, star rating or hidden. [Requested by Ted]
* Changed default permissions for directories to 0755 and files to 0644 [Requested by Ovidiu]
* Added a parent page option, so contest can be grouped under one page [Requested by Ovidiu]
* When voting for a photo, the logged in user details (email) is allready filled in [Requested by Ovidiu]
* Added a settings page, where all the config will be editable (so when upgrading you keep your settings).
* Added a page details section when creating a photocontest (with defaults from the settings page).
* Added an extra content entry for the upload page (contest rules, disclaimers and/or what ever you do with the uploaded pictures).
* Added a cropped version of the uploaded picture so the center is shown in the view pages.
* Removed some unneccesery classes in view.php. Fixes a height problem with some themes.
* All error messages can be translated using the pot files.
* Relocated the screenshots for the readme file.

= 1.3- 01.11.2009  =
* Accedentaily release an unfinished version.

= 1.2.3.1- 29.10.2009  =
* Fixed an other typo in the functions file (sorry)

= 1.2.3- 29.10.2009  =
* Changed the view url (changed prid to post_id) in polariod.swf so it won't conflit with wp-polls
* Fixed a typo in the functions file

= 1.2.2- 29.10.2009  =
* Prefixed all the functions with wppc_ so there are no conflicts with other plugins [Thanks to Ken @ Prodesk for reporting]
  
= 1.2.1- 27.10.2009  =
* Auto upgrade fix, and uninstall checks. While upgrading the plugin tries to keep the contest_folder.
* In 1.2 the database changed, but when using auto-upgrade, this change didn't got executed.
  If you still have problems, run the follwing sql command: 
  ALTER TABLE `wp_photocontest_votes` CHANGE `voter_id` `voter_id` VARCHAR( 36 ) NOT NULL 

= 1.2 - 26.10.2009  =
* Applied security checks to solve the SQL injection leaks and at least one XSS leak [Thanks to Rene Schmidt for reporting]
* Uses a template file for the generation of the pages created by WP-PhotoContest.
* Fixed captcha-bug introduced in version 1.1
  
= 1.1 - 19.10.2009  =
* Applied security checks to solve the SQL injection leaks and at least one XSS leak [Thanks to Rene Schmidt for reporting]
* Removed all functionality from login.php. Now uses the wp-login.php file for handling login/logut requests
* Fixed a problem with users uploading photo's and had unsufficient rights to update the post.
  (Subscribers can't post <script>-tags, which is used by the plugin)
* Added a function that notifies (by sending a mail to the 'admin_email') if there is a new upload. 
  (As alternative for the automatich post updating)

= 1.0.1 - 17.10.2009  =
* Fixed a 'Fatal error' if PHP was compiled without mbstring

= 1.0 - 15.10.2009  =
* initial release



= Todos =
Special chars problems:
* When you create a contest with stressed characters in it's name than the plug-in fails to display thumbnails.

Login problems:
* Why do you do check for the version in login.php. I use WP Security Scan and my wp version is changed to abc. Could you please remove it?
* Login --> Register --> back to contest (already logged in)

Adding permalinks:
* problems accessing contest I just created (permalinks)

Feature requests:
* Move the CONTEST_HOLDER and skins-directory to the wp-content folder
* Documentation in the sources
* photocontest-add.php: 188  Add rollback of directory and page if db insert fails
* How to completely remove all traces of this plugin (create a uninstall option)
* Create non-flash support
* Add email check before voting
* Is it possible to not display the number of votes under the images? Vote total showed up when Hidden Voting was selected. 
* Also should the Admin receive an email when someone votes using Hidden Voting?
* Create "Terug naar overzicht" link
* Add Limited size for upload files

== Credits ==

Copyright 2009 by Frank van der Stad

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

---------------------------------------

== Thanks go to: == 
* Paolo Palmonari - because this plugin was greatly inspired by the PhotoRacer plugin.
* Pacuraru Ovidiu - for adding a lot of things to my todo list ;)
* Young Chu       - for bugfix the gif/png upload
* Fabio Gresta    - Italian translation AND testing my plugin
* gnom1gnom       - Polish translation, finding some bugs and translation errors
* Rene Schmidt    - For finding a couple of SQL Injection and XSS leaks (in version 1.0 and 1.01)
