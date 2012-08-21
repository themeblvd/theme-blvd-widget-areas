<?php
/*
Plugin Name: Theme Blvd Widget Areas
Plugin URI: 
Description: This plugin works in conjuction with the Theme Blvd framework and its core addons to allow you to create custom widget areas and apply them in various ways.
Version: 1.0.0
Author: Jason Bobich
Author URI: http://jasonbobich.com
License: GPL2

    Copyright 2012  Jason Bobich

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

define( 'TB_WA_PLUGIN_VERSION', '1.0.0' );
define( 'TB_WA_PLUGIN_DIR', dirname( __FILE__ ) ); 
define( 'TB_WA_PLUGIN_URL', plugins_url( '' , __FILE__ ) );

/**
 * Run Widget Area Manager
 *
 * We check the user-role before running the 
 * widget area framework.
 *
 * @since 1.0.0
 */

function themeblvd_sidebars_init() {

	// Check to make sure Theme Blvd Framework 2.2+ is running
	if( ! defined( 'TB_FRAMEWORK_VERSION' ) || version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '<' ) ) {
		add_action( 'admin_notices', 'themeblvd_sidebars_warning' );
		return;
	}
	
	// General actions and filters
	add_action( 'init', 'themeblvd_sidebars_register_post_type' );
	add_action( 'after_setup_theme', 'themeblvd_register_custom_sidebars', 1001 ); // Hooked directly after theme framework's sidebar registration
	add_filter( 'themeblvd_custom_sidebar_id', 'themeblvd_get_sidebar_id' );
	
	// Admin files, actions, and filters
	if( is_admin() ){
		
		// Check to make sure admin interface isn't set to be 
		// hidden and for the appropriate user capability
		if ( themeblvd_supports( 'admin', 'sidebars' ) && current_user_can( themeblvd_admin_module_cap( 'sidebars' ) ) ) {
			
			// Include admin files
			include_once( TB_WA_PLUGIN_DIR . '/admin/tbwa-admin.php' );
			include_once( TB_WA_PLUGIN_DIR . '/admin/tbwa-ajax.php' );
			include_once( TB_WA_PLUGIN_DIR . '/admin/tbwa-interface.php' );
		
			// Run admin items
			add_action( 'admin_menu', 'themeblvd_sidebar_admin_add_page' );
			add_action( 'admin_init', 'themeblvd_sidebar_admin_hijack_submenu' );
			add_action( 'widgets_admin_page', 'themeblvd_widgets_admin_page' );
			
		}
		
	}
}
add_action( 'after_setup_theme', 'themeblvd_sidebars_init' );

/**
 * Register text domain for localization.
 *
 * @since 1.0.0
 */

function themeblvd_sidebars_textdomain() {
	load_plugin_textdomain( 'tbwa', false, TB_WA_PLUGIN_DIR . '/lang' );
}
add_action( 'plugins_loaded', 'themeblvd_sidebars_textdomain' );

/**
 * Display warning telling the user they must have a 
 * theme with Theme Blvd framework v2.2+ installed in 
 * order to run this plugin.
 *
 * @since 1.0.0
 */

function themeblvd_sidebars_warning() {
	echo '<div class="error">';
	echo '<p>'.__( 'You currently have the "Theme Blvd Widget Areas" plugin activated, however you are not using a theme with Theme Blvd Framework v2.2+, and so this plugin will not do anything.', 'tbwa' ).'</p>';
	echo '</div>';
}

/**
 * Regiter "tb_sidebar" custom post type. This post 
 * type is how the framework internally manages sidebars. 
 * Each post is a custom sidebar. These can be 
 * imported/exported with WP's tools.
 *
 * @since 1.0.0
 */

function themeblvd_sidebars_register_post_type(){
	$args = array(
		'labels' 			=> array( 'name' => 'Widget Areas', 'singular_name' => 'Widget Area' ),
		'public'			=> false,
		//'show_ui' 		=> true,	// Can uncomment for debugging
		'query_var' 		=> true,
		'capability_type' 	=> 'post',
		'hierarchical' 		=> false,
		'rewrite' 			=> false,
		'supports' 			=> array( 'title', 'custom-fields' ), 
		'can_export'		=> true
	);
	register_post_type( 'tb_sidebar', $args );
}

/**
 * Register custom sidebars.
 *
 * @since 1.0.0
 */

function themeblvd_register_custom_sidebars() {

	// Get custom sidebars
	$custom_sidebars = get_posts( 'post_type=tb_sidebar&numberposts=-1&orderby=title&order=ASC' );
	
	// Register custom sidebars
	foreach( $custom_sidebars as $sidebar ) {
		
		// Setup arguments for register_sidebar()
		$args = array(
			'name' 			=> __( 'Custom', 'tbwa' ).': '.$sidebar->post_title,
		    'id' 			=> $sidebar->post_name,
		    'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
			'after_widget' 	=> '</div></aside>',
			'before_title' 	=> '<h3 class="widget-title">',
			'after_title' 	=> '</h3>'
		);
		$location = get_post_meta( $sidebar->ID, 'location', true );
		if( $location && $location != 'floating' )
			$args['description'] = sprintf( __( 'This is a custom widget area to replace the %s on its assigned pages.', 'tbwa' ), themeblvd_get_sidebar_location_name( $location ) );
		else
			$args['description'] = __( 'This is a custom floating widget area.', 'tbwa' );
		
		// Register the sidebar
		register_sidebar( $args );
	}
}

/**
 * Retrieve current sidebar ID for a location.
 *
 * @since 1.0.0
 * 
 * @param string $sidebar_id Current sidebar ID to be filtered, will match the location id
 * @return string $sidebar_id The final sidebar ID, whether it's been changed or not
 */

function themeblvd_get_sidebar_id( $sidebar_id ) {
	
	// Innitiate assignments
	$assignments = array();
	
	// Get all the custom sidebars for this location
	$args = array(
		'post_type' 	=> 'tb_sidebar',
		'numberposts' 	=> -1,
		'meta_key' 		=> 'location',
		'meta_value' 	=> $sidebar_id
	);
	
	// And now create a single array of just their assignments 
	// formatted for the themeblvd_get_assigned_id function
	$custom_sidebars = get_posts( $args );
	if( $custom_sidebars ) {
		foreach( $custom_sidebars as $sidebar ) {
			$current_assignments = get_post_meta( $sidebar->ID, 'assignments', true );
			if( is_array( $current_assignments ) && ! empty ( $current_assignments ) ) {
    			foreach( $current_assignments as $key => $value ) {
    				$assignments[$key] = $value;
    			}
    		}
    	}
    }
	
	// Return new sidebar ID
	return themeblvd_get_assigned_id( $sidebar_id, $assignments );
}

/**
 * Locate the id for sidebar or override to be used based 
 * on the current WP query compared with assignments.
 *
 * These conditionals are split into three tiers. This 
 * means that we loop through conditionals each tier and 
 * only move onto the next tier if we didn't find an 
 * assignment to set.
 *
 * @since 1.0.0
 * 
 * @param $location string Current location of sidebar
 * @param $assignments array all of elements assignments to check through
 * @return $id string id of element to return
 */

function themeblvd_get_assigned_id( $location, $assignments ) {
	
	// Initialize $id
	$id = $location;
	
	// If assignments is empty, we can't do anything in 
	// this function, so we'll just quit now!
	if( empty( $assignments ) )
		return $id;
	
	// Reset the query
	wp_reset_query();
	
	// Tier I conditionals
	foreach( $assignments as $assignment ) {
		if( $assignment['type'] != 'top') {
			// Page
			if( $assignment['type'] == 'page' ) {
				if( is_page( $assignment['id'] ) )			
					$id = $assignment['post_slug'];
			}
			// Post
			if( $assignment['type'] == 'post' ) {
				if( is_single( $assignment['id'] ) )		
					$id = $assignment['post_slug'];
			}
			// Category
			if( $assignment['type'] == 'category' ) {
				if( is_category( $assignment['id'] ) )			
					$id = $assignment['post_slug'];
			}
			// Tag
			if( $assignment['type'] == 'tag') {
				if( is_tag( $assignment['id'] ) )		
					$id = $assignment['post_slug'];
			}
			// Extend Tier I
			$id = apply_filters( 'themeblvd_sidebar_id_tier_1', $id, $assignment );
		}
	}
	
	// If we found a tier I item, we're finished
	if( $id != $location )
		return $id;
	
	// Tier II conditionals
	foreach( $assignments as $assignment ) {
		if( $assignment['type'] != 'top' ) {				
			// Posts in Category
			if( $assignment['type'] == 'posts_in_category' ) {
				if( is_single() && in_category( $assignment['id'] ) )		
					$id = $assignment['post_slug'];
			}
			// Custom conditional
			if( $assignment['type'] == 'custom' ) {
				$process = 'if('.$assignment['id'].') $id = $assignment["post_slug"];';
				eval( $process );
			}	
			// Extend Tier II
			$id = apply_filters( 'themeblvd_sidebar_id_tier_2', $id, $assignment );
		}
	}
	
	// If we found a tier II item, we're finished
	if( $id != $location )
		return $id;
	
	// Tier III conditionals
	foreach( $assignments as $assignment ) {
		if( $assignment['type'] == 'top' ) {				
			switch( $assignment['id'] ) {

				// Homepage
				case 'home' :
					if( is_home() )		
						$id = $assignment['post_slug'];
					break;
				
				// All Posts	
				case 'posts' :
					if( is_single() )
						$id = $assignment['post_slug'];
					break;
					
				// All Pages	
				case 'pages' :
					if( is_page() )
						$id = $assignment['post_slug'];
					break;
					
				// Archives	
				case 'archives' :
					if( is_archive() )
						$id = $assignment['post_slug'];
					break;
					
				// Categories	
				case 'categories' :
					if( is_category() )
						$id = $assignment['post_slug'];
					break;
					
				// Tags	
				case 'tags' :
					if( is_tag() )
						$id = $assignment['post_slug'];
					break;
					
				// Authors	
				case 'authors' :
					if( is_author() )
						$id = $assignment['post_slug'];
					break;
					
				// Search Results
				case 'search' :
					if( is_search() )
						$id = $assignment['post_slug'];
					break;
					
				// 404	
				case '404' :
					if( is_404() )
						$id = $assignment['post_slug'];
					break;
			} // End switch $assignment['id']					
			
			// Extend Tier III
			$id = apply_filters( 'themeblvd_sidebar_id_tier_3', $id, $assignment );
		}
	}
	return $id;	
}