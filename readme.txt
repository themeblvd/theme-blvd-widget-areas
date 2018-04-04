=== Theme Blvd Widget Areas ===
Author URI: http://www.themeblvd.com
Contributors: themeblvd
Tags: custom sidebars, sidebar, sidebars, widget, widgets, widget areas, unlimited sidebars, Theme Blvd, themeblvd, Jason Bobich
Stable Tag: 1.3.0
Tested up to: 4.9

When using a Theme Blvd theme, this plugin extends the framework's widget area system.

== Description ==

**NOTE: This plugin requires Theme Blvd framework v2.2.1+**

In a nutshell, this plugin provides that infamous "Unlimited Sidebars" feature all the cool kids are asking for with your Theme Blvd theme.

When using a Theme Blvd theme, this plugin will allow to create custom widget areas for any of your theme's specific widget area locations. Custom widget areas can be created for specific locations and then assigned to pages throughout your website. Additionally, custom widget areas can be left as "floating" (i.e. no location) and then be inserted within dynamic areas such as elements of the [Layout Builder](http://wordpress.org/extend/plugins/theme-blvd-layout-builder), for example.

Custom widget areas have two attributes to understand when being setup.

1. **Location:** The location is where the widget area is on the page. Examples would be Right Sidebar, Left Sidebar, Ads Above Content, etc.
2. **Assignments:** The assignments are where a particular custom widget area will then be displayed on your website. The assignments can be specific pages, posts, archives, and various other pre-set WordPress conditionals. Additionally, advanced users can input their own custom [conditional statements](http://codex.wordpress.org/Conditional_Tags) within their widget area assignments.

**NOTE: For this plugin to do anything, you must have a theme with Theme Blvd framework v2.2+ activated.**

== Installation ==

1. Upload `theme-blvd-widget-areas` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to *Appearance > Widget Areas* in your WordPress admin panel to to use the Widget Area Manager

== Screenshots ==

1. Manage your custom widget areas.
2. Add a new custom widget areas and edit assignments.
3. Quickly override the sidebar when editing a page.

== Changelog ==

= 1.3.0 - 01/16/2018 =

* Minor admin styling fixes to work with framework 2.7 theme updates.
* Fixed minor styling quirk with "Add Sidebar" title of "Sidebar Overrides" meta box.
* Code formatting improvements to pass [WordPress Coding Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards).
* Added filter `themeblvd_sidebar_args` which is shared with theme framework (for framework 2.7+ themes); this filters arguments passed to `register_sidebar()` for custom and theme widget areas.

= 1.2.4 - 01/31/2015 =

* Added [WooCommerce](https://wordpress.org/plugins/woocommerce/) support (for framework 2.5+ themes).
* Added [bbPress](https://wordpress.org/plugins/bbpress/) support (for framework 2.5+ themes).
* Added [Theme Blvd Portfolios](https://wordpress.org/plugins/portfolios/) support (for framework 2.5+ themes).

= 1.2.3 - 09/09/2014 =

* Minor security fix.

= 1.2.2 - 11/16/2014 =

* Compatibility for servers still running PHP 5.2 for last update’s edits.

= 1.2.1 - 11/15/2014 =

* Fixed some admin page styling quirks.
* When widget area is created from Edit Page screen, it's now initially set to `floating`.
* GlotPress compatibility (for 2015 wordpress.org release).

= 1.2.0 - 02/17/2014 =

* Admin style updates for WordPress 3.8 and MP6 (requires Theme Blvd framework v2.4+).
* Fixed bug with not being able to use `&&` operator in custom conditionals.

= 1.1.5 - 11/07/2013 =

* Removed deprecated media uploader script.

= 1.1.4 - 09/20/2013 =

* Admin jQuery improvements for 1.9 - Converted all .live() to .on()

= 1.1.3 - 06/06/2013 =

* Fixed "Dismiss" link not working for notices on some admin pages.
* Fixed any conflicts when activated with [Theme Blvd Bundle](http://wordpress.org/extend/plugins/theme-blvd-bundle).

= 1.1.2 - 05/30/2013 =

* Some minor admin javascript improvements.
* Added fix for rare PHP warning resulting from `_tb_sidebars` meta not being saved properly.
* Added `themeblvd_sidebars_post_type_args` filter on registered `tb_sidebar` post type's `$args`.

= 1.1.1 - 05/16/2013 =

* Added `themeblvd_custom_sidebar_args` filter.

= 1.1.0 - 03/22/2013 =

* Added "Sidebar Overrides" meta box so custom sidebars can be applied
while editing posts and pages directly.
* Minor admin styling updates.
* Re-structured how custom sidebars are applied to cut down database
queries roughly 6 to 1.
* Update requires Theme Blvd framework v2.2.1+.

= 1.0.0 - 09/06/2012 =

* This is the first release.
