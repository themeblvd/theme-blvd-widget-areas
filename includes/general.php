<?php
/**
 * Disable a nag message.
 *
 * @since 1.1.0
 */
function themeblvd_sidebars_disable_nag() {
	global $current_user;
    if ( isset( $_GET['tb_nag_ignore'] ) )
         add_user_meta( $current_user->ID, $_GET['tb_nag_ignore'], 'true', true );
}

/**
 * Disable a nag message URL.
 *
 * @since 1.1.3
 */
function themeblvd_sidebars_disable_url( $id ) {

	global $pagenow;

	$url = admin_url( $pagenow );

	if( ! empty( $_SERVER['QUERY_STRING'] ) )
		$url .= sprintf( '?%s&tb_nag_ignore=%s', $_SERVER['QUERY_STRING'], $id );
	else
		$url .= sprintf( '?tb_nag_ignore=%s', $id );

	return $url;
}

/**
 * Display warning telling the user they must have a
 * theme with Theme Blvd framework v2.2+ installed in
 * order to run this plugin.
 *
 * @since 1.0.0
 */
function themeblvd_sidebars_warning() {
	global $current_user;
	if( ! get_user_meta( $current_user->ID, 'tb_sidebars_warning' ) ) {
		echo '<div class="updated">';
		echo '<p>'.__( 'You currently have the "Theme Blvd Widget Areas" plugin activated, however you are not using a theme with Theme Blvd Framework v2.2+, and so this plugin will not do anything.', 'themeblvd_sidebars' ).'</p>';
		echo '<p><a href="'.themeblvd_sidebars_disable_url('tb_sidebars_warning').'">'.__('Dismiss this notice', 'themeblvd_sidebars').'</a> | <a href="http://www.themeblvd.com" target="_blank">'.__('Visit ThemeBlvd.com', 'themeblvd_sidebars').'</a></p>';
		echo '</div>';
	}
}

/**
 * Display warning telling the user they should be using
 * theme with Theme Blvd framework v2.2.1+.
 *
 * @since 1.1.0
 */
function themeblvd_sidebars_warning_2() {
	global $current_user;
    if( ! get_user_meta( $current_user->ID, 'tb_sidebars_warning_2' ) ) {
        echo '<div class="updated">';
        echo '<p>'.__( 'You are currently running a theme with Theme Blvd framework v2.2.0. To get the best results from this version of Theme Blvd Widget Areas, you should update your current theme to its latest version, which will contain framework v2.2.1+.', 'themeblvd_sidebars' ).'</p>';
        echo '<p><a href="'.themeblvd_sidebars_disable_url('tb_sidebars_warning_2').'">'.__('Dismiss this notice', 'themeblvd_sidebars').'</a></p>';
        echo '</div>';
    }
}

/**
 * Register "tb_sidebar" custom post type. This post
 * type is how the framework internally manages sidebars.
 * Each post is a custom sidebar. These can be
 * imported/exported with WP's tools.
 *
 * @since 1.0.0
 */
function themeblvd_sidebars_register_post_type(){
	$args = apply_filters( 'themeblvd_sidebars_post_type_args', array(
		'labels' 			=> array( 'name' => 'Widget Areas', 'singular_name' => 'Widget Area' ),
		'public'			=> false,
		//'show_ui' 		=> true,	// Can uncomment for debugging
		'query_var' 		=> true,
		'capability_type' 	=> 'post',
		'hierarchical' 		=> false,
		'rewrite' 			=> false,
		'supports' 			=> array( 'title', 'custom-fields' ),
		'can_export'		=> true
	));
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
			'name' 			=> __( 'Custom', 'themeblvd_sidebars' ).': '.$sidebar->post_title,
		    'id' 			=> $sidebar->post_name,
		    'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
			'after_widget' 	=> '</div></aside>',
			'before_title' 	=> '<h3 class="widget-title">',
			'after_title' 	=> '</h3>'
		);
		$location = get_post_meta( $sidebar->ID, 'location', true );
		if( $location && $location != 'floating' )
			$args['description'] = sprintf( __( 'This is a custom widget area to replace the %s on its assigned pages.', 'themeblvd_sidebars' ), themeblvd_get_sidebar_location_name( $location ) );
		else
			$args['description'] = __( 'This is a custom floating widget area.', 'themeblvd_sidebars' );

		// Extend
		$args = apply_filters( 'themeblvd_custom_sidebar_args', $args, $sidebar, $location );

		// Register the sidebar
		register_sidebar( $args );
	}
}

/**
 * Retrieve current sidebar ID for a location.
 *
 * @since 1.0.0
 *
 * @param string $location_id Current sidebar ID to be filtered, will match the location id
 * @param object $custom_sidebars All tb_sidebar custom posts
 * @param array $sidebar_overrides Current _tb_sidebars meta data for page/post
 * @return string $sidebar_id The final sidebar ID, whether it's been changed or not
 */
function themeblvd_get_sidebar_id( $location_id, $custom_sidebars, $sidebar_overrides ) {

	// Overrides come first
	if( ! empty( $sidebar_overrides ) && is_array( $sidebar_overrides ) ) {
		foreach( $sidebar_overrides as $key => $value ){
			if( $key == $location_id && $value != 'default' ){
				return $value;
			}
		}
	}

	// Innitiate assignments
	$assignments = array();

	// And now create a single array of just their assignments
	// formatted for the themeblvd_get_assigned_id function
	$custom_counter = 1;
	if( ! empty( $custom_sidebars ) ) {
		foreach( $custom_sidebars as $sidebar ) {

			// First, verify location
			if( $location_id != get_post_meta( $sidebar->ID, 'location', true ) )
				continue;

			// And now move onto assignments
			$current_assignments = get_post_meta( $sidebar->ID, 'assignments', true );
			if( is_array( $current_assignments ) && ! empty ( $current_assignments ) ) {
    			foreach( $current_assignments as $key => $value ) {
    				if( $key == 'custom' ) {
    					$assignments[$key.'_'.$custom_counter] = $value;
    					$custom_counter++;
    				} else {
    					$assignments[$key] = $value;
    				}
    			}
    		}

    	}
    }

	// Return new sidebar ID
	return themeblvd_get_assigned_id( $location_id, $assignments );
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