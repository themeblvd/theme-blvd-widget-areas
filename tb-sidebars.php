<?php
/*
Plugin Name: Theme Blvd Widget Areas
Description: This plugin works in conjuction with the Theme Blvd framework and its core addons to allow you to create custom widget areas and apply them in various ways.
Version: 1.2.5
Author: Theme Blvd
Author URI: http://themeblvd.com
Text Domain: theme-blvd-widget-areas
License: GPL2

    Copyright 2017  Theme Blvd

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

define( 'TB_SIDEBARS_PLUGIN_VERSION', '1.2.5' );
define( 'TB_SIDEBARS_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_SIDEBARS_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Run Widget Area Manager
 *
 * In order for everything to run, we need to make
 * sure Theme Blvd framework v2.2+ is running. Also
 * to run the admin panel portion, we will also check
 * to make sure the user is allowed. -- This supports
 * the framework's filters on changing admin page
 * capabilities.
 *
 * @since 1.0.0
 */
function themeblvd_sidebars_init() {

	global $_themeblvd_sidebar_manager;

	// Include general functions
	include_once( TB_SIDEBARS_PLUGIN_DIR . '/includes/general.php' );

	// Check for disable of the admin nag
	add_action( 'admin_init', 'themeblvd_sidebars_disable_nag' );

	// Check to make sure Theme Blvd Framework 2.2+ is running
	if( ! defined( 'TB_FRAMEWORK_VERSION' ) || version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '<' ) ) {
		add_action( 'admin_notices', 'themeblvd_sidebars_warning' );
		return;
	}

	// If using framework v2.2.0, tell them they should now update to 2.2.1
	if( version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '=' ) ) {
		add_action( 'admin_notices', 'themeblvd_sidebars_warning_2' );
	}

	// General actions and filters
	add_action( 'init', 'themeblvd_sidebars_register_post_type' );
	add_action( 'widgets_init', 'themeblvd_register_custom_sidebars', 11 ); // Hooked directly after theme framework's sidebar registration
	add_filter( 'themeblvd_custom_sidebar_id', 'themeblvd_get_sidebar_id', 10, 3 ); // This filter happens in the theme framework's themeblvd_frontend_init()

	// Admin files, actions, and filters
	if( is_admin() ){
		// Check to make sure admin interface isn't set to be
		// hidden and for the appropriate user capability
		if ( themeblvd_supports( 'admin', 'sidebars' ) && current_user_can( themeblvd_admin_module_cap( 'sidebars' ) ) ) {
			include_once( TB_SIDEBARS_PLUGIN_DIR . '/includes/admin/class-tb-sidebar-manager.php' );
			$_themeblvd_sidebar_manager = new Theme_Blvd_Sidebar_Manager();
		}
	}
}
add_action( 'after_setup_theme', 'themeblvd_sidebars_init' );

/**
 * Register text domain for localization.
 *
 * @since 1.2.1
 */
function themeblvd_sidebars_localize() {
	load_plugin_textdomain('theme-blvd-widget-areas');
}
add_action('init', 'themeblvd_sidebars_localize');

/* Requires PHP 5.3+
add_action( 'init', function() {
	load_plugin_textdomain('theme-blvd-widget-areas');
} );
*/
