=== Flexible Upload ===
Contributors: japonophile
Tags: upload, image, images, picture, thumbnail, resize, watermark
Requires at least: 2.0
Tested up to: 2.5
Stable tag: trunk

Resize picture at upload and make thumbnail creation configurable, optionally include a watermark to your uploaded images.

== Description ==

Flexible Upload is a plugin for Wordpress intended to extend Wordpress basic upload functionality.

The main features are:

* Automatically resize/crop picture at upload
* Create thumbnail of the desired size
* Include watermark in every uploaded picture
* Support for picture alignment (left/right/center)
* Support Lightbox (or other) "rel" or "target" tag
* Support for picture caption (not when using tinyMCE)
* Support multiple file upload
* Multi-language support
* Fully configurable

== Installation ==

1. Unzip the downloaded package and upload the `flexible-upload` folder into the `wp-content/plugins/` directory
1. Log into your WordPress admin panel
1. Activate the plugin through the 'Plugins' menu in WordPress (if you are upgrading from an earlier version, make sure to de-activate and re-activate the plugin so that all options are updated)
1. Be sure to check the option page (available from the 'Options' > 'Flexible Upload' menu)
1. A detailed explanation of each feature is available in the local help page (link at the top of the option page)

== Frequently Asked Questions ==

= How do I customize CSS tags for image alignment? =

Edit the `style.css` file of your theme and add some CSS like this:

`.imageframe { margin: 10px; padding: 5px; border: 1px solid #aaa; }
.alignleft { float: left; }
.alignright { float: right; }
.centered { margin-left: auto; margin-right: auto; }`

= Watermarking does not seem to work? =

Make sure:

1. Your web server's PHP includes the GD graphic library module
1. You have provided a signature file and specified its path in the option page

== Screenshots ==

1. Specify whether or not to resize the uploaded image, and whether or not to create a thumbnail
2. Specify image alignment when inserting images into your posts
3. Configure everything through the option page

== Help / Support ==

If the local help page did not answer some of the questions or problems you might have, don't hesitate to contact me on the [Flexible Upload forum](http://blog.japonophile.com/flexible-upload/sf-forum/ "Flexible Upload forum")

If you like the plugin or want some additional features, let me know too!
