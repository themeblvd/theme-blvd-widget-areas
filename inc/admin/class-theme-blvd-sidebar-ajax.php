<?php
/**
 * Ajax Functionality
 *
 * @author     Jason Bobich <info@themeblvd.com>
 * @copyright  2009-2017 Theme Blvd
 * @package    Theme Blvd Widget Areas
 * @since      1.0.0
 */

/**
 * Setup any Ajax functionality needed for
 * managing sidebars.
 *
 * @since 1.0.0
 */
class Theme_Blvd_Sidebar_Ajax {

	/**
	 * Sidebar Manager admin page object.
	 *
	 * @since 1.0.0
	 * @var Theme_Blvd_Sidebar_Manager
	 */
	public $admin_page;

	/**
	 * Constructor. Hook everything in.
	 *
	 * @since 1.0.0
	 *
	 * @param Theme_Blvd_Sidebar_Manager $admin_page Admin page object for managing sidebar.
	 */
	public function __construct( $admin_page ) {

		$this->admin_page = $admin_page;

		add_action( 'wp_ajax_themeblvd-add-sidebar', array( $this, 'add_sidebar' ) );

		add_action( 'wp_ajax_themeblvd-quick-add-sidebar', array( $this, 'quick_add_sidebar' ) );

		add_action( 'wp_ajax_themeblvd-save-sidebar', array( $this, 'save_sidebar' ) );

		add_action( 'wp_ajax_themeblvd-delete-sidebar', array( $this, 'delete_sidebar' ) );

		add_action( 'wp_ajax_themeblvd-edit-sidebar', array( $this, 'edit_sidebar' ) );

	}

	/**
	 * Add new sidebar.
	 *
	 * @since 1.0.0
	 */
	public function add_sidebar() {

		check_ajax_referer( 'themeblvd-add-sidebar', 'security' );

		parse_str( $_POST['data'], $config );

		$args = array(
			'post_type'      => 'tb_sidebar',
			'post_title'     => $config['options']['sidebar_name'],
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		);

		$post_id = wp_insert_post( $args );

		$post = get_post( $post_id );

		$post_slug = $post->post_name;

		$location = null;

		if ( isset( $config['options']['sidebar_location'] ) ) {

			if ( 'floating' === $config['options']['sidebar_location'] ) {

				$location = $config['options']['sidebar_location'];

			} else {

				$exists = false;

				$framework_sidebars = themeblvd_get_sidebar_locations();

				foreach ( $framework_sidebars as $framework_sidebar ) {

					if ( $framework_sidebar['location']['id'] == $config['options']['sidebar_location'] ) {

						$exists = true;

					}
				}

				if ( $exists ) {

					$location = $config['options']['sidebar_location'];

				}
			}
		}

		$assignments = array();

		$name = null;

		if ( isset( $config['options']['sidebar_assignments'] ) && has_filter( 'themeblvd_sanitize_conditionals' ) ) {

			/**
			 * Filters sanitizing a conditional set for determining
			 * what pages a sidebar displays on a site.
			 *
			 * @since 1.0.0
			 *
			 * @param array  $sidebar_assignments Assignments for current sidebar.
			 * @param string $post_slug           Current sidebar slug.
			 * @param string $post_id             Current sidebar ID.
			 */
			$assignments = apply_filters( 'themeblvd_sanitize_conditionals', $config['options']['sidebar_assignments'], $post_slug, $post_id );

		}

		update_post_meta( $post_id, 'location', $location );

		update_post_meta( $post_id, 'assignments', $assignments );

		$this->admin_page->manage_sidebars();

		die();

	}

	/**
	 * Add new sidebar from meta box on edit
	 * page/post screen.
	 *
	 * @since 1.1.0
	 */
	public function quick_add_sidebar() {

		check_ajax_referer( 'themeblvd-add-sidebar', 'security' );

		parse_str( $_POST['data'], $data );

		$args = array(
			'post_type'      => 'tb_sidebar',
			'post_title'     => $data['_tb_new_sidebar_name'],
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		);

		$post_id = wp_insert_post( $args );

		update_post_meta( $post_id, 'location', 'floating' );

		update_post_meta( $post_id, 'assignments', array() );

		$this->admin_page->sidebar_overrides( $data['_tb_sidebars'] );

		die();

	}

	/**
	 * Save sidebar.
	 *
	 * @since 1.0.0
	 */
	public function save_sidebar() {

		check_ajax_referer( 'themeblvd-save-sidebar', 'security' );

		parse_str( $_POST['data'], $config );

		$post_id = $config['sidebar_id'];

		$sidebar = get_post( $post_id );

		$post_slug = $sidebar->post_name;

		$location = null;

		if ( isset( $config['options']['sidebar_location'] ) ) {

			if ( 'floating' === $config['options']['sidebar_location'] ) {

				$location = $config['options']['sidebar_location'];

			} else {

				$exists = false;

				$framework_sidebars = themeblvd_get_sidebar_locations();

				foreach ( $framework_sidebars as $framework_sidebar ) {

					if ( $framework_sidebar['location']['id'] == $config['options']['sidebar_location'] ) {

						$exists = true;

					}
				}

				if ( $exists ) {

					$location = $config['options']['sidebar_location'];

				}
			}
		}

		$assignments = array();

		$name = null;

		if ( isset( $config['options']['sidebar_assignments'] ) && has_filter( 'themeblvd_sanitize_conditionals' ) ) {

			/**
			 * Filters sanitizing a conditional set for determining
			 * what pages a sidebar displays on a site.
			 *
			 * @since 1.0.0
			 *
			 * @param array  $sidebar_assignments Assignments for current sidebar.
			 * @param string $post_slug           Current sidebar slug.
			 * @param string $post_id             Current sidebar ID.
			 */
			$assignments = apply_filters( 'themeblvd_sanitize_conditionals', $config['options']['sidebar_assignments'], $post_slug, $post_id );

		}

		update_post_meta( $post_id, 'location', $location );

		update_post_meta( $post_id, 'assignments', $assignments );

		if ( isset( $config['options']['post_title'] ) && isset( $config['options']['post_name'] ) ) {

			$post_atts = array(
				'ID'         => $post_id,
				'post_title' => $config['options']['post_title'],
				'post_name'  => $config['options']['post_name'],
			);

			wp_update_post( $post_atts );

		}

		$updated_sidebar = get_post( $post_id );

		$current_sidebar_id = $updated_sidebar->post_name;

		// Start output.

		echo $current_sidebar_id . '[(=>)]';

		echo '<div id="setting-error-save_options" class="updated fade settings-error ajax-update">';

		echo '<p><strong>' . __( 'Widget Area saved.', 'themeblvd' ) . '</strong></p>';

		echo '</div>';

		echo '[(=>)]';

		$this->admin_page->manage_sidebars();

		die();

	}

	/**
	 * Delete sidebar.
	 *
	 * @since 1.0.0
	 */
	public function delete_sidebar() {

		check_ajax_referer( 'themeblvd-manage-sidebars', 'security' );

		parse_str( $_POST['data'], $data );

		if ( isset( $data['posts'] ) ) {

			foreach ( $data['posts'] as $id ) {

				wp_delete_post( $id );

			}

			// Start output.

			echo '<div id="setting-error-delete_sidebar" class="updated fade settings-error ajax-update">';

			echo '<p><strong>' . __( 'Sidebar(s) deleted.', 'theme-blvd-widget-areas' ) . '</strong></p>';

			echo '</div>';

			echo '[(=>)]';

			$this->admin_page->manage_sidebars();

		}

		die();

	}

	/**
	 * Edit a sidebar.
	 *
	 * @since 1.0.0
	 */
	public function edit_sidebar() {

		$sidebar_id = $_POST['data'];

		echo $sidebar_id . '[(=>)]';

		$this->admin_page->edit_sidebar( $sidebar_id );

		die();

	}

}
