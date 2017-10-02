<?php
/**
 * General plugin functions.
 *
 * @author     Jason Bobich <info@themeblvd.com>
 * @copyright  2009-2017 Theme Blvd
 * @package    Theme Blvd Widget Areas
 * @since      1.0.0
 */

/**
 * Disable a nag message.
 *
 * @since 1.1.0
 */
function themeblvd_sidebars_disable_nag() {

	global $current_user;

	if ( ! isset( $_GET['nag-ignore'] ) ) {
		return;
	}

	if ( strpos( $_GET['nag-ignore'], 'tb-nag-' ) !== 0 ) { // Meta key must start with "tb-nag-".
		return;
	}

	if ( isset( $_GET['security'] ) && wp_verify_nonce( $_GET['security'], 'themeblvd-sidebars-nag' ) ) {
		add_user_meta( $current_user->ID, $_GET['nag-ignore'], 'true', true );
	}
}

/**
 * Disable a nag message URL.
 *
 * @since 1.1.3
 *
 * @param  string $id  ID of current nag being dismissed.
 * @return string $url URL to disable nag.
 */
function themeblvd_sidebars_disable_url( $id ) {

	global $pagenow;

	$url = admin_url( $pagenow );

	if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
		$url .= sprintf( '?%s&nag-ignore=%s', $_SERVER['QUERY_STRING'], 'tb-nag-' . $id );
	} else {
		$url .= sprintf( '?nag-ignore=%s', 'tb-nag-' . $id );
	}

	$url .= sprintf( '&security=%s', wp_create_nonce( 'themeblvd-sidebars-nag' ) );

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

	if ( ! get_user_meta( $current_user->ID, 'tb-nag-sidebars-framework-1' ) ) {

		echo '<div class="updated">';

		echo '<p><strong>Theme Blvd Widget Areas:</strong> ' . __( 'You are not using a theme with the Theme Blvd Framework v2.2+, and so this plugin will not do anything.', 'theme-blvd-widget-areas' ) . '</p>';

		echo '<p><a href="' . themeblvd_sidebars_disable_url( 'sidebars-framework-1' ) . '">' . __( 'Dismiss this notice', 'theme-blvd-widget-areas' ) . '</a> | <a href="http://www.themeblvd.com" target="_blank">' . __( 'Visit ThemeBlvd.com', 'theme-blvd-widget-areas' ) . '</a></p>';

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

	if ( ! get_user_meta( $current_user->ID, 'tb-nag-sidebars-framework-2' ) ) {

		echo '<div class="updated">';

		echo '<p><strong>Theme Blvd Widget Areas:</strong> ' . __( 'You are currently running a theme with Theme Blvd framework v2.2.0. To get the best results from this version of the plugin, you should update your current theme to its latest version.', 'theme-blvd-widget-areas' ) . '</p>';

		echo '<p><a href="' . themeblvd_sidebars_disable_url( 'sidebars-framework-2' ) . '">' . __( 'Dismiss this notice', 'theme-blvd-widget-areas' ) . '</a></p>';

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
function themeblvd_sidebars_register_post_type() {

	/**
	 * Filters the arguments used to register the post type
	 * for custom widget areas created by the end-user.
	 *
	 * @since 1.0.0
	 *
	 * @param array Arguments passed to register_post_type().
	 */
	$args = apply_filters( 'themeblvd_sidebars_post_type_args', array(
		'labels'           => array(
			'name'          => 'Widget Areas',
			'singular_name' => 'Widget Area',
		),
		'public'          => false,
		// 'show_ui'      => true,	// Can uncomment for debugging.
		'query_var'       => true,
		'capability_type' => 'post',
		'hierarchical'    => false,
		'rewrite'         => false,
		'supports'        => array( 'title', 'custom-fields' ),
		'can_export'      => true,
	));

	register_post_type( 'tb_sidebar', $args );

}

/**
 * Register custom sidebars.
 *
 * @since 1.0.0
 */
function themeblvd_register_custom_sidebars() {

	$custom_sidebars = get_posts( 'post_type=tb_sidebar&numberposts=-1&orderby=title&order=ASC' );

	foreach ( $custom_sidebars as $sidebar ) {

		$args = array(
			'name'          => __( 'Custom', 'theme-blvd-widget-areas' ) . ': ' . $sidebar->post_title,
			'id'            => $sidebar->post_name,
			'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
			'after_widget'  => '</div></aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		);

		$location = get_post_meta( $sidebar->ID, 'location', true );

		if ( $location && 'floating' !== $location ) {

			$args['description'] = sprintf(
				// translators: 1: widget area location name
				__( 'This is a custom widget area to replace the %s on its assigned pages.', 'theme-blvd-widget-areas' ),
				themeblvd_get_sidebar_location_name( $location )
			);

		} else {

			$args['description'] = __( 'This is a custom floating widget area.', 'theme-blvd-widget-areas' );

		}

		/**
		 * Filters the arguments used to register a custom
		 * widget areas.
		 *
		 * @since 1.1.1
		 *
		 * @param array   $args     Arguments passed to register_sidebar().
		 * @param WP_Post $sidebar  Post object for the custom sidebar.
		 * @param string  $location Name of location where custom sidebar is assigned.
		 */
		$args = apply_filters( 'themeblvd_custom_sidebar_args', $args, $sidebar, $location );

		/**
		 * Filters the arguments used in registering
		 * all widget area, default and custom.
		 *
		 * Note: This same filter is also used in the Theme
		 * Blvd theme framework, for default widget areas.
		 *
		 * @param array   $args     Arguments passed to register_sidebar().
		 * @param WP_Post $sidebar  Post object for the custom sidebar.
		 * @param string  $location ID of widget area being registered.
		 */
		$args = apply_filters( 'themeblvd_sidebar_args', $args, $sidebar, $location );

		register_sidebar( $args );

	}
}

/**
 * Retrieve current sidebar ID for a location.
 *
 * @since 1.0.0
 *
 * @param  string $location_id       Current sidebar ID to be filtered, will match the location id.
 * @param  array  $custom_sidebars   Array tb_sidebar custom post objects.
 * @param  array  $sidebar_overrides Current _tb_sidebars meta data for page/post.
 * @return string $sidebar_id        The final sidebar ID, whether it's been changed or not.
 */
function themeblvd_get_sidebar_id( $location_id, $custom_sidebars, $sidebar_overrides ) {

	// Overrides come first.
	if ( ! empty( $sidebar_overrides ) && is_array( $sidebar_overrides ) ) {

		foreach ( $sidebar_overrides as $key => $value ) {

			if ( $location_id === $key && 'default' !== $value ) {

				return $value;

			}
		}
	}

	$assignments = array();

	/*
	 * And now create a single array of just their assignments
	 * formatted for the themeblvd_get_assigned_id function.
	 */
	$custom_counter = 1;

	if ( ! empty( $custom_sidebars ) ) {

		foreach ( $custom_sidebars as $sidebar ) {

			// First, verify location.
			if ( get_post_meta( $sidebar->ID, 'location', true ) != $location_id ) {
				continue;
			}

			// And now move onto assignments.
			$current_assignments = get_post_meta( $sidebar->ID, 'assignments', true );

			if ( is_array( $current_assignments ) && ! empty( $current_assignments ) ) {

				foreach ( $current_assignments as $key => $value ) {

					if ( 'custom' === $key ) {

						$assignments[ $key . '_' . $custom_counter ] = $value;

						$custom_counter++;

					} else {

						$assignments[ $key ] = $value;

					}
				}
			}
		}
	}

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
 * @param  string $location    Current location of sidebar.
 * @param  array  $assignments All of elements assignments to check through.
 * @return string $id          ID of element to return.
 */
function themeblvd_get_assigned_id( $location, $assignments ) {

	$id = $location;

	/*
	 * If assignments is empty, we can't do anything in
	 * this function, so we'll just quit now!
	 */
	if ( empty( $assignments ) ) {
		return $id;
	}

	wp_reset_query();

	// Tier I conditionals
	foreach ( $assignments as $assignment ) {

		if ( 'top' !== $assignment['type'] ) {

			// Page
			if ( 'page' === $assignment['type'] ) {
				if ( is_page( $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Post
			if ( 'post' === $assignment['type'] || 'portfolio_item' === $assignment['type'] ) {
				if ( is_single( $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Category archive
			if ( 'category' === $assignment['type'] ) {
				if ( is_category( $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Tag archive
			if ( 'tag' === $assignment['type'] ) {
				if ( is_tag( $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Portfolio archive
			if ( 'portfolio' === $assignment['type'] ) {
				if ( is_tax( 'portfolio', $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Portfolio tag archive
			if ( 'portfolio_tag' === $assignment['type'] ) {
				if ( is_tax( 'portfolio_tag', $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Product category archive
			if ( 'product_cat' === $assignment['type'] ) {
				if ( is_tax( 'product_cat', $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Product tag archive
			if ( 'product_tag' === $assignment['type'] ) {
				if ( is_tax( 'product_tag', $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Forum or topic within that forum
			if ( 'forum' === $assignment['type'] ) {

				if ( is_single( $assignment['id'] ) ) {

					$id = $assignment['post_slug'];

				} elseif ( is_singular( array( 'topic' ) ) ) {

					$forum_id = get_post_meta( get_the_ID(), '_bbp_forum_id', true );

					$post = get_post( $forum_id );

					if ( $post && $post->post_name == $assignment['id'] ) {
						$id = $assignment['post_slug'];
					}
				}
			}

			/**
			 * Filters the sidebar ID at the tier 1 level.
			 *
			 * Use this filter to check a custom conditional for
			 * the tier 1 level.
			 *
			 * If your conditional is a match return the sidebar ID
			 * back with ID. Otherwise leave $id empty, and then
			 * the process will move to the next tier of checks.
			 *
			 * @since 1.0.0
			 *
			 * @param string $id         The ID of the element to return, like the ID of a post, or the slug of a tag.
			 * @param array  $assignment Current assignment being checked.
			 */
			$id = apply_filters( 'themeblvd_sidebar_id_tier_1', $id, $assignment );

		}
	}

	// If we found a tier I item, we're finished.
	if ( $id != $location ) {

		return $id;

	}

	// Tier II conditionals
	foreach ( $assignments as $assignment ) {

		if ( 'top' !== $assignment['type'] ) {

			// Posts in category
			if ( 'posts_in_category' === $assignment['type'] ) {
				if ( is_single() && in_category( $assignment['id'] ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Portfolio items in portfolio
			if ( 'portfolio_items_in_portfolio' === $assignment['type'] ) {
				if ( is_single() && has_term( $assignment['id'], 'portfolio' ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Products in category
			if ( 'products_in_cat' === $assignment['type'] ) {
				if ( is_single() && has_term( $assignment['id'], 'product_cat' ) ) {
					$id = $assignment['post_slug'];
				}
			}

			// Custom conditional
			if ( 'custom' === $assignment['type'] ) {
				$process = 'if (' . htmlspecialchars_decode( $assignment['id'] ) . ') $id = $assignment["post_slug"];';
				eval( $process );
			}

			/**
			 * Filters the sidebar ID at the tier 2 level.
			 *
			 * Use this filter to check a custom conditional for
			 * the tier 2 level.
			 *
			 * If your conditional is a match return the sidebar ID
			 * back with ID. Otherwise leave $id empty, and then
			 * the process will move to the next tier of checks.
			 *
			 * @since 1.0.0
			 *
			 * @param string $id         The ID of the element to return, like the ID of a post, or the slug of a tag.
			 * @param array  $assignment Current assignment being checked.
			 */
			$id = apply_filters( 'themeblvd_sidebar_id_tier_2', $id, $assignment );

		}
	}

	// If we found a tier II item, we're finished.
	if ( $id != $location ) {

		return $id;

	}

	// Tier III conditionals
	foreach ( $assignments as $assignment ) {

		if ( false !== strpos( $assignment['type'], '_top' ) || 'top' === $assignment['type'] ) {

			switch ( $assignment['id'] ) {

				// Standard posts
				case 'blog_posts':
					if ( is_singular( array( 'post' ) ) ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Portfolio items
				case 'portfolio_items':
					if ( is_singular( array( 'portfolio_item' ) ) ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Portfolio archives
				case 'portfolios':
					if ( is_tax( 'portfolio' ) ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Portfolio tag archives
				case 'portfolio_tags':
					if ( is_tax( 'portfolio_tag' ) ) {
						$id = $assignment['post_slug'];
					}
					break;

				// All products
				case 'products':
					if ( is_singular( 'product' ) ) {
						$id = $assignment['post_slug'];
					}
					break;

				// All product category archives
				case 'product_cat':
					if ( is_tax( 'product_cat' ) ) {
						$id = $assignment['post_slug'];
					}
					break;

				// All product tag archives
				case 'product_tag':
					if ( is_tax( 'product_tag' ) ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Product search
				case 'product_search':
					if ( function_exists( 'is_woocommerce' ) ) {
						if ( is_woocommerce() && is_search() ) {
							$id = $assignment['post_slug'];
						}
					}
					break;

				// All single topics
				case 'topic':
					if ( function_exists( 'bbp_is_single_topic' ) ) {
						if ( bbp_is_single_topic() ) {
							$id = $assignment['post_slug'];
						}
					}
					break;

				// All single forums
				case 'forum':
					if ( function_exists( 'bbp_is_single_forum' ) ) {
						if ( bbp_is_single_forum() ) {
							$id = $assignment['post_slug'];
						}
					}
					break;

				// All forum archives
				case 'topic_tag':
					if ( function_exists( 'bbp_is_topic_tag' ) ) {
						if ( bbp_is_topic_tag() ) {
							$id = $assignment['post_slug'];
						}
					}
					break;

				// All forum archives
				case 'forum_user':
					if ( function_exists( 'bbp_is_single_user' ) ) {
						if ( bbp_is_single_user() && ! bbp_is_user_home() ) {
							$id = $assignment['post_slug'];
						}
					}
					break;

				// All forum archives
				case 'forum_user_home':
					if ( function_exists( 'bbp_is_user_home' ) ) {
						if ( bbp_is_user_home() ) {
							$id = $assignment['post_slug'];
						}
					}
					break;

			}

			/**
			 * Filters the sidebar ID at the tier 3 level.
			 *
			 * Use this filter to check a custom conditional for
			 * the tier 3 level.
			 *
			 * If your conditional is a match return the sidebar ID
			 * back with ID. Otherwise leave $id empty, and then
			 * the process will move to the next tier of checks.
			 *
			 * @since 1.0.0
			 *
			 * @param string $id         The ID of the element to return, like the ID of a post, or the slug of a tag.
			 * @param array  $assignment Current assignment being checked.
			 */
			$id = apply_filters( 'themeblvd_sidebar_id_tier_3', $id, $assignment );

		}
	}

	// Tier IV conditionals
	foreach ( $assignments as $assignment ) {

		if ( strpos( $assignment['type'], '_top' ) !== false ) {

			switch ( $assignment['id'] ) {

				// All WooCommerce - shop, search, archives, and pages
				case 'woocommerce':
					if ( function_exists( 'is_woocommerce' ) ) {
						if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
							$id = $assignment['post_slug'];
						}
					}
					break;

				// All bbPress
				case 'bbpress':
					if ( function_exists( 'is_bbpress' ) ) {
						if ( is_bbpress() ) {
							$id = $assignment['post_slug'];
						}
					}
					break;

			} // End switch $assignment['id'].

			/**
			 * Filters the sidebar ID at the tier 4 level.
			 *
			 * Use this filter to check a custom conditional for
			 * the tier 4 level.
			 *
			 * If your conditional is a match return the sidebar ID
			 * back with ID. Otherwise leave $id empty, and then
			 * the process will move to the next tier of checks.
			 *
			 * @since 1.0.0
			 *
			 * @param string $id         The ID of the element to return, like the ID of a post, or the slug of a tag.
			 * @param array  $assignment Current assignment being checked.
			 */
			$id = apply_filters( 'themeblvd_sidebar_id_tier_4', $id, $assignment );

		}
	}

	// Tier V conditionals
	foreach ( $assignments as $assignment ) {

		if ( 'top' === $assignment['type'] ) {

			switch ( $assignment['id'] ) {

				// Homepage
				case 'home':
					if ( is_home() ) {
						$id = $assignment['post_slug'];
					}
					break;

				// All Posts
				case 'posts':
					if ( is_single() ) {
						$id = $assignment['post_slug'];
					}
					break;

				// All Pages
				case 'pages':
					if ( is_page() ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Archives
				case 'archives':
					if ( is_archive() ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Categories
				case 'categories':
					if ( is_category() ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Tags
				case 'tags':
					if ( is_tag() ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Authors
				case 'authors':
					if ( is_author() ) {
						$id = $assignment['post_slug'];
					}
					break;

				// Search Results
				case 'search':
					if ( is_search() ) {
						$id = $assignment['post_slug'];
					}
					break;

				// 404
				case '404':
					if ( is_404() ) {
						$id = $assignment['post_slug'];
					}
					break;

			} // End switch $assignment['id'].

			/**
			 * Filters the sidebar ID at the tier 5 level.
			 *
			 * Use this filter to check a custom conditional for
			 * the tier 5 level.
			 *
			 * If your conditional is a match return the sidebar ID
			 * back with ID.
			 *
			 * @since 1.0.0
			 *
			 * @param string $id         The ID of the element to return, like the ID of a post, or the slug of a tag.
			 * @param array  $assignment Current assignment being checked.
			 */
			$id = apply_filters( 'themeblvd_sidebar_id_tier_5', $id, $assignment );

		}
	}

	return $id;

}
