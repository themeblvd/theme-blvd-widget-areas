<?php
/**
 * Sidebar Manager Admin
 *
 * @author     Jason Bobich <info@themeblvd.com>
 * @copyright  2009-2017 Theme Blvd
 * @package    Theme Blvd Widget Areas
 * @since      1.0.0
 */

/**
 * Sets up sidebar managment admin components.
 *
 * 1. A page is setup to create custom widget areas and
 * manager their assignments Appearance > Widget Areas.
 *
 * 2. A meta box "Sidebar Overrides" is added when editing
 * pages and posts to quickly assign a custom sidebar.
 */
class Theme_Blvd_Sidebar_Manager {

	/**
	 * Constructor. Hook everything in.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add sidebar manager admin page.
		add_action( 'admin_menu', array( $this, 'add_page' ) );

		add_action( 'admin_init', array( $this, 'hijack_submenu' ) );

		/*
		 * Filter on javascript locals specifically for Widget
		 * Areas Manager onto Theme Blvd framework locals.
		 */
		add_filter( 'themeblvd_locals_js', array( $this, 'add_js_locals' ) );

		// Add ajax functionality to sidebar admin page.
		include_once( TB_SIDEBARS_PLUGIN_DIR . '/inc/admin/class-theme-blvd-sidebar-ajax.php' );

		$ajax = new Theme_Blvd_Sidebar_Ajax( $this );

		// Add "Sidebar Overrides" meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		add_action( 'save_post', array( $this, 'save_meta_box' ) );

		/*
		 * Add assets for the admin page and the edit post screen
		 * for meta box.
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );

	}

	/**
	 * Add a subpages for Sidebars to the appearance menu.
	 *
	 * @since 1.0.0
	 */
	public function add_page() {

		$title = __( 'Widget Areas', 'theme-blvd-widget-areas' );

		$admin_page = add_theme_page(
			$title,
			$title,
			themeblvd_admin_module_cap( 'sidebars' ),
			'themeblvd_widget_areas',
			array( $this, 'admin_page' )
		);

	}

	/**
	 * Add a meta box for managing sidebar overrides.
	 *
	 * @since 1.1.0
	 */
	function add_meta_box() {

		global $pagenow;

		global $typenow;

		/**
		 * Filters the arguments used to create the
		 * "Sidebar Overrides" meta box.
		 *
		 * @since 1.1.0
		 *
		 * @param array Arguments passed to `add_meta_box()`.
		 */
		$args = apply_filters( 'themeblvd_sidebar_meta_box', array(
			'id'        => 'tb-sidebars-meta-box',
			'name'      => __( 'Sidebar Overrides', 'theme-blvd-widget-areas' ),
			'callback'  => array( $this, 'meta_box' ),
			'post_type' => array( 'page', 'post' ), // Filter to null, to stop the meta box from being added.
			'context'   => 'normal',
			'priority'  => 'default',
		));

		if ( $args['post_type'] ) {

			foreach ( $args['post_type'] as $post_type ) {

				add_meta_box( $args['id'], $args['name'], $args['callback'], $post_type, $args['context'], $args['priority'] );

			}
		}
	}

	/**
	 * Save metabox for editing layouts from Edit Page screen.
	 *
	 * @since 1.1.0
	 *
	 * @param string|int $post_id ID of current page or post being edited.
	 */
	function save_meta_box( $post_id ) {

		if ( ! isset( $_POST['action'] ) || $_POST['action'] != 'editpost' ) {
			return;
		}

		if ( ! isset( $_POST['_tb_sidebar_overrides_nonce'] ) || ! wp_verify_nonce( $_POST['_tb_sidebar_overrides_nonce'], 'themeblvd_sidebar_overrides' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! empty( $_POST['_tb_sidebars'] ) ) {

			$clean = array();

			foreach ( $_POST['_tb_sidebars'] as $key => $value ) {

				$clean[ $key ] = apply_filters( 'themeblvd_sanitize_text', $value );

			}

			update_post_meta( $post_id, '_tb_sidebars', $clean );

		}

	}

	/**
	 * Loads the CSS and JavaScript for the admin page and
	 * the Edit Post screen for the meta box.
	 *
	 * @since 1.3.0
	 */
	public function load_assets() {

		$screen = get_current_screen();

		$sidebar_screen = 'appearance_page_themeblvd_widget_areas';

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		/*
		 * For the Sidebar Manager page at Appearance > Widget
		 * Areas, we need to enqueue all framework admin assets.
		 */
		if ( $sidebar_screen === $screen->base ) {

			if ( function_exists( 'themeblvd_admin_assets' ) ) {

				themeblvd_admin_assets();

				wp_enqueue_style(
					'themeblvd-admin-options-page',
					esc_url( TB_FRAMEWORK_URI . "/admin/assets/css/options-page{$suffix}.css" ),
					null,
					TB_FRAMEWORK_VERSION
				);

				wp_enqueue_script(
					'themeblvd-admin-options-page',
					esc_url( TB_FRAMEWORK_URI . "/admin/assets/js/options-page{$suffix}.js" ),
					array( 'jquery' ),
					TB_FRAMEWORK_VERSION
				);

			} else {

				/*
				 * Legacy enqueue assets.
				 *
				 * The following is @deprecated as of framework 2.7.0.
				 */
				wp_enqueue_style(
					'themeblvd_admin',
					esc_url( trailingslashit( TB_FRAMEWORK_URI ) . "admin/assets/css/admin-style{$suffix}.css" ),
					null,
					TB_FRAMEWORK_VERSION
				);

				wp_enqueue_style(
					'themeblvd_options',
					esc_url( trailingslashit( TB_FRAMEWORK_URI ) . "admin/options/css/admin-style{$suffix}.css" ),
					null,
					TB_FRAMEWORK_VERSION
				);

				wp_enqueue_script(
					'themeblvd_admin',
					esc_url( trailingslashit( TB_FRAMEWORK_URI ) . "admin/assets/js/shared{$suffix}.js" ),
					array( 'jquery' ),
					TB_FRAMEWORK_VERSION
				);

				wp_enqueue_script(
					'themeblvd_options',
					esc_url( trailingslashit( TB_FRAMEWORK_URI ) . "admin/options/js/options{$suffix}.js" ),
					array( 'jquery' ),
					TB_FRAMEWORK_VERSION
				);

			}

		}

		/*
		 * And for the Sidebar Manager page AND the post edit
		 * screens, we need to enqueue out plugin's assets.
		 */
		if ( in_array( $screen->base, array( $sidebar_screen, 'post' ) ) ) {

			wp_enqueue_style(
				'theme-blvd-widget-areas',
				esc_url( trailingslashit( TB_SIDEBARS_PLUGIN_URI ) . "inc/admin/assets/css/sidebars{$suffix}.css" ),
				null,
				TB_SIDEBARS_PLUGIN_VERSION
			);

			wp_enqueue_script(
				'theme-blvd-widget-areas',
				esc_url( trailingslashit( TB_SIDEBARS_PLUGIN_URI ) . "inc/admin/assets/js/sidebars{$suffix}.js" ),
				array( 'jquery' ),
				TB_SIDEBARS_PLUGIN_VERSION
			);

		}

		/*
		 * And finally, only if we're on the Sidebar Manager
		 * page, we need to make sure our L10n text strings
		 * are present.
		 */
		if ( $sidebar_screen === $screen->base ) {

			$handle = 'theme-blvd-utilities';

			if ( version_compare( TB_FRAMEWORK_VERSION, '2.7.0', '<' ) ) {
				$handle = 'themeblvd_admin'; // @deprecated handle.
			}

			wp_localize_script(
				$handle,
				'themeblvdL10n',
				themeblvd_get_admin_locals( 'js' )
			);

		}

	}

	/**
	 * Add javascript locals for Widget Areas manager onto
	 * framework js locals that are already established.
	 *
	 * @since 1.1.2
	 */
	public function add_js_locals( $current ) {

		$new = array(
			'edit_sidebar'       => __( 'Edit', 'theme-blvd-widget-areas' ),
			'delete_sidebar'     => __( 'Are you sure you want to delete the widget area(s)?', 'theme-blvd-widget-areas' ),
			'sidebar_created'    => __( 'Widget Area created!', 'theme-blvd-widget-areas' ),
			'sidebar_layout_set' => __( 'With how you\'ve selected to start your layout, there is already a sidebar layout applied initially.', 'theme-blvd-widget-areas' ),
		);

		return array_merge( $current, $new );

	}

	/**
	 * Builds out the full admin page.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {
		?>
		<div id="sidebar_blvd" class="tb-options-page">
			<div id="optionsframework" class="wrap tb-options-wrap tb-options-js">

				<div class="admin-module-header">
					<?php do_action( 'themeblvd_admin_module_header', 'sidebars' ); ?>
				</div>

				<h2 class="nav-tab-wrapper">
					<a href="#manage_sidebars" id="manage_sidebars-tab" class="nav-tab" title="<?php _e( 'Custom Widget Areas', 'theme-blvd-widget-areas' ); ?>"><?php _e( 'Custom Widget Areas', 'theme-blvd-widget-areas' ); ?></a>
					<a href="#add_sidebar" id="add_sidebar-tab" class="nav-tab" title="<?php _e( 'Add New', 'theme-blvd-widget-areas' ); ?>"><?php _e( 'Add New', 'theme-blvd-widget-areas' ); ?></a>
					<a href="#edit_sidebar" id="edit_sidebar-tab" class="nav-tab nav-edit-sidebar" title="<?php _e( 'Edit', 'theme-blvd-widget-areas' ); ?>"><?php _e( 'Edit', 'theme-blvd-widget-areas' ); ?></a>
				</h2>

				<!-- MANAGE SIDEBARS (start) -->

				<div id="manage_sidebars" class="group hide">
					<form id="manage_current_sidebars">
						<?php
						$manage_nonce = wp_create_nonce( 'themeblvd-manage-sidebars' );

						echo '<input type="hidden" name="option_page" value="themeblvd-manage-sidebars" />';

						echo '<input type="hidden" name="_wpnonce" value="' . $manage_nonce . '" />';
						?>
						<div class="ajax-mitt"><?php $this->manage_sidebars(); ?></div>
					</form><!-- #manage_sidebars (end) -->
				</div><!-- #manage (end) -->

				<!-- MANAGE SIDEBARS (end) -->

				<!-- ADD SIDEBAR (start) -->

				<div id="add_sidebar" class="group hide">
					<form id="add_new_sidebar">
						<?php
						$add_nonce = wp_create_nonce( 'themeblvd-add-sidebar' );

						echo '<input type="hidden" name="option_page" value="themeblvd-add-sidebar" />';

						echo '<input type="hidden" name="_wpnonce" value="' . $add_nonce . '" />';

						$this->add_sidebar();
						?>
					</form><!-- #add_new_sidebars (end) -->
				</div><!-- #manage (end) -->

				<!-- ADD SIDEBAR (end) -->

				<!-- EDIT SIDEBAR (start) -->

				<div id="edit_sidebar" class="group hide">
					<form id="edit_current_sidebar" method="post">
						<?php

						$edit_nonce = wp_create_nonce( 'themeblvd-save-sidebar' );

						echo '<input type="hidden" name="action" value="update" />';

						echo '<input type="hidden" name="option_page" value="themeblvd-save-sidebar" />';

						echo '<input type="hidden" name="_wpnonce" value="' . $edit_nonce . '" />';
						?>
						<div class="ajax-mitt"><!-- AJAX inserts edit sidebars page here. --></div>
					</form>
				</div><!-- #manage (end) -->

				<!-- EDIT SIDEBAR (end) -->

				<div class="admin-module-footer">
					<?php do_action( 'themeblvd_admin_module_footer', 'sidebars' ); ?>
				</div>

			</div><!-- .tb-options-wrap.tb-options-js (end) -->
		</div><!-- #sidebar_blvd (end) -->
		<?php
	}

	/**
	 * Builds out the meta box to edit a page's custom layout.
	 *
	 * @since 1.1.0
	 */
	public function meta_box() {
		?>
		<div id="sidebar_blvd">
			<div id="optionsframework" class="tb-options-wrap tb-options-js">

				<!-- HEADER (start) -->

				<div class="meta-box-nav">
					<div class="select-layout">
						<div class="ajax-overlay"></div>
						<div class="icon-holder">
							<span class="tb-loader ajax-loading"></span>
							<span class="tb-sidebar-override-icon"></span>
						</div>
						<span class="note"><?php _e( 'Select any custom sidebars you\'d like applied to this page.', 'theme-blvd-widget-areas' ); ?></span>
					</div>
					<ul>
						<li><a href="#tb-override-sidebar"><?php esc_html_e( 'Assign Overrides', 'theme-blvd-widget-areas' ); ?></a></li>
						<li><a href="#tb-add-sidebar"><?php esc_html_e( 'Add Sidebar', 'theme-blvd-widget-areas' ); ?></a></li>
					</ul>
					<div class="clear"></div>
				</div><!-- .meta-box-nav (end) -->

				<!-- HEADER (end) -->

				<!-- ASSIGN OVERRIDES (start) -->

				<div id="tb-override-sidebar" class="group">
					<div class="ajax-mitt">
						<?php $this->sidebar_overrides(); ?>
					</div><!-- .ajax-mitt (end) -->
				</div>

				<!-- ASSIGN OVERRIDES (end) -->

				<!-- ADD NEW (start) -->

				<div id="tb-add-sidebar" class="group">
					<?php $this->add_sidebar_mini(); ?>
				</div><!-- #manage (end) -->

				<!-- ADD NEW (end) -->

			</div><!-- .tb-options-wrap.tb-options-js (end) -->
		</div><!-- #builder_blvd (end) -->
		<?php
	}

	/**
	 * Hack the appearance submenu a to get "Widget Areas" to
	 * show up just below "Widgets"
	 *
	 * @since 1.0.0
	 */
	public function hijack_submenu() {

		global $submenu;

		$new_submenu = array();

		if ( ! empty( $submenu ) ) {

			// Find the current "Widget Areas" array, copy it, and remove it.
			foreach ( $submenu['themes.php'] as $key => $value ) {
				if ( 'themeblvd_widget_areas' === $value[2] ) {
					$widget_areas = $value;
					unset( $submenu['themes.php'][ $key ] );
				}
			}

			// Reconstruct the new submenu
			if ( isset( $widget_areas ) ) {
				foreach ( $submenu['themes.php'] as $key => $value ) {

					// Add original item to new menu.
					$new_submenu[ $key ] = $value;

					/*
					 * If this is the "Widgets" item, add in our "Widget Areas"
					 * item directly after.
					 */
					if ( 'widgets.php' === $value[2] ) {
						$new_submenu[] = $widget_areas;
					}
				}
			}

			// Replace old submenu with new submenu.
			$submenu['themes.php'] = $new_submenu;

		}
	}

	/**
	 * Generates the the interface to manage sidebars.
	 *
	 * @since 1.0.0
	 */
	public function manage_sidebars() {

		/**
		 * Filters the columns for the sidebar management
		 * table.
		 *
		 * @since 1.0.0
		 *
		 * @param array Columns for table.
		 */
		$columns = apply_filters( 'themeblvd_manage_sidebars', array(
			array(
				'name' => __( 'Widget Area Title', 'theme-blvd-widget-areas' ),
				'type' => 'title',
			),
			array(
				'name' => __( 'ID', 'theme-blvd-widget-areas' ),
				'type' => 'slug',
			),
			/* Hiding the true post ID from user to avoid confusion.
			array(
			   'name'  => __( 'ID', 'theme-blvd-widget-areas' ),
			   'type'  => 'id',
			),
			*/
			array(
				'name' => __( 'Location', 'theme-blvd-widget-areas' ),
				'type' => 'sidebar_location',
			),
			array(
				'name' => __( 'Assignments', 'theme-blvd-widget-areas' ),
				'type' => 'assignments',
			),
		));

		// Display the table.
		echo '<div class="metabox-holder">' . themeblvd_post_table( 'tb_sidebar', $columns ) . '</div><!-- .metabox-holder (end) -->';

	}

	/**
	 * Generates the the interface to add a new sidebar.
	 *
	 * @since 1.0.0
	 */
	public function add_sidebar() {

		// Setup sidebar layouts.
		$sidebars = themeblvd_get_sidebar_locations();

		$sidebar_locations = array(
			'floating' => __( 'No Location (Floating Widget Area)', 'theme-blvd-widget-areas' ),
		);

		foreach ( $sidebars as $sidebar ) {
			$sidebar_locations[ $sidebar['location']['id'] ] = $sidebar['location']['name'];
		}

		/**
		 * Filters the data to create the options for
		 * adding a new custom sidebar.
		 *
		 * @link http://dev.themeblvd.com/tutorial/formatting-options
		 *
		 * @since 1.0.0
		 *
		 * @param array Options compatible with Theme Blvd framework options system.
		 */
		$options = apply_filters( 'themeblvd_add_sidebar', array(
			'sidebar_name' => array(
				'name'    => __( 'Widget Area Name', 'theme-blvd-widget-areas' ),
				'desc'    => __( 'Enter a user-friendly name for your widget area.<br><br><em>Example: My Sidebar</em>', 'theme-blvd-widget-areas' ),
				'id'      => 'sidebar_name',
				'type'    => 'text',
			),
			'sidebar_location' => array(
				'name'    => __( 'Widget Area Location', 'theme-blvd-widget-areas' ),
				'desc'    => __( 'Select which location on the site this widget area will be among the theme\'s currently supported widget area locations.<br><br><em>Note: A "Floating Widget Area" can be used in dynamic elements like setting up columns in the layout builder, for example.</em>', 'theme-blvd-widget-areas' ),
				'id'      => 'sidebar_location',
				'type'    => 'select',
				'options' => $sidebar_locations,
			),
			'sidebar_assignments' => array(
				'name'    => __( 'Widget Area Assignments', 'theme-blvd-widget-areas' ),
				'desc'    => __( 'Select the places on your site you\'d like this custom widget area to show in the location you picked previously.<br><br><em>Note: You can edit the location you selected previously and these assignments later if you change your mind.</em><br><br><em>Note: Assignments will be ignored on "Floating Widget Areas" but since you can always come back and change the location for a custom widget area, assignments still will always be stored.</em>', 'theme-blvd-widget-areas' ),
				'id'      => 'sidebar_assignments',
				'type'    => 'conditionals',
			),
		));

		$form = themeblvd_option_fields( 'options', $options, null, false );

		?>
		<div class="metabox-holder">
			<div class="postbox">

				<form id="add_new_sidebar">

					<div class="inner-group">
						<?php echo $form[0]; ?>
					</div><!-- .group (end) -->

					<div id="optionsframework-submit" class="options-page-footer">
						<input type="submit" class="button-primary" name="update" value="<?php esc_html_e( 'Add New Widget Area', 'theme-blvd-widget-areas' ); ?>">
						<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
						<div class="clear"></div>
					</div>

				</form><!-- #add_new_slider (end) -->

			</div><!-- .postbox (end) -->
		</div><!-- .metabox-holder (end) -->
		<?php
	}

	/**
	 * Generates the the interface to add a new slider when
	 * in the meta box interface.
	 *
	 * @since 1.1.0
	 */
	public function add_sidebar_mini() {
		?>
		<h3><?php _e( 'Add New Sidebar', 'theme-blvd-widget-areas' ); ?></h3>

		<div class="section">
			<div class="add-sidebar-items">
				<?php $nonce = wp_create_nonce( 'themeblvd-add-sidebar' ); ?>
				<input type="hidden" name="_tb_new_sidebar_nonce" value="<?php echo $nonce; ?>" />
				<input type="text" name="_tb_new_sidebar_name" placeholder="<?php _e( 'New Sidebar Name', 'theme-blvd-widget-areas' ); ?>" />
				<a href="#" class="new-sidebar-submit button"><?php _e( 'Add Sidebar', 'theme-blvd-widget-areas' ); ?></a>
				<p class="explain"><?php esc_html_e( 'Enter a user-friendly name for your new sidebar and add it.', 'theme-blvd-widget-areas' ); ?></p>
			</div>
		</div>

		<div class="add-sidebar-note">
			<p><?php esc_html_e( 'Note: Any sidebars you create here will initially be created as "floating" widget areas with no assignments. If you need to, you can edit these in the future from Appearance > Widget Areas.', 'theme-blvd-widget-areas' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Generates the the interface to edit the sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @param $id string ID of sidebar to edit.
	 */
	public function edit_sidebar( $id ) {

		$post = get_post( $id );

		// Setup sidebar layouts.
		$sidebars = themeblvd_get_sidebar_locations();

		$sidebar_locations = array(
			'floating' => __( 'No Location (Floating Widget Area)', 'theme-blvd-widget-areas' ),
		);

		foreach ( $sidebars as $sidebar ) {
			$sidebar_locations[ $sidebar['location']['id'] ] = $sidebar['location']['name'];
		}

		/**
		 * Filters the the options for adding a custom sidebar.
		 *
		 * @link http://dev.themeblvd.com/tutorial/formatting-options
		 *
		 * @since 1.3.0
		 *
		 * @param array Options compatible with Theme Blvd framework options system.
		 */
		$options = apply_filters( 'themeblvd_edit_sidebar', array(
			'post_title' => array(
				'name'    => __( 'Widget Area Name', 'theme-blvd-widget-areas' ),
				'desc'    => __( 'Here you can edit the name of your widget area. This will adjust how your widget area is labeled for you here in the WordPress admin panel.', 'theme-blvd-widget-areas' ),
				'id'      => 'post_title',
				'type'    => 'text',
			),
			'post_name' => array(
				'name'    => __( 'Widget Area ID', 'theme-blvd-widget-areas' ),
				'desc'    => __( 'Here you can edit the internal ID of your widget area.<br><br><em>Warning: This is how WordPress assigns your widgets and how the theme applies your widget area. If you change this ID, you will need to re-assign any widgets under Appearance > Widgets, and re-visit any areas you may have added this as a floating widget area.</em>', 'theme-blvd-widget-areas' ),
				'id'      => 'post_name',
				'type'    => 'text',
				'class'   => 'hide', // Hidden from user. For debugging can display and change with dev console.
			),
			'sidebar_location' => array(
				'name'    => __( 'Widget Area Location', 'theme-blvd-widget-areas' ),
				'desc'    => __( 'Select which location on the site this widget area will be among the theme\'s currently supported widget area locations.<br><br><em>Note: A "Floating Widget Area" can be used in dynamic elements like setting up columns in the layout builder, for example.</em>', 'theme-blvd-widget-areas' ),
				'id'      => 'sidebar_location',
				'type'    => 'select',
				'options' => $sidebar_locations,
			),
			'sidebar_assignments' => array(
				'name'    => __( 'Widget Area Assignments', 'theme-blvd-widget-areas' ),
				'desc'    => __( 'Select the places on your site you\'d like this custom widget area to show in the location you picked previously.<br><br><em>Note: Assignments will be ignored on "Floating Widget Areas" but since you can always come back and change the location for a custom widget area, assignments still will always be stored.</em>', 'theme-blvd-widget-areas' ),
				'id'      => 'sidebar_assignments',
				'type'    => 'conditionals',
			),
		));

		/**
		 * Filters the saved settings for the options form to
		 * edit a custom sidebar.
		 *
		 * If you're using the themeblvd_edit_sidebar filter to
		 * add custom options to a sidebar, you'll also want to
		 * make sure to use this filter to add in the saved settings
		 * for each of your options.
		 *
		 * @since 1.3.0
		 *
		 * @param array Retrieved settings for option values.
		 */
		$settings = apply_filters( 'themeblvd_edit_sidebar_settings', array(
			'post_title'          => $post->post_title,
			'post_name'           => $post->post_name,
			'sidebar_location'    => get_post_meta( $id, 'location', true ),
			'sidebar_assignments' => get_post_meta( $id, 'assignments', true ),
		));

		// Build form
		$form = themeblvd_option_fields( 'options', $options, $settings, false );
		?>
		<div class="metabox-holder">
			<div class="postbox">

				<h3><?php echo $post->post_title; ?></h3>

				<div class="inner-group">
					<input type="hidden" name="sidebar_id" value="<?php echo $id; ?>" />
					<?php echo $form[0]; ?>
				</div><!-- .group (end) -->

				<div id="optionsframework-submit" class="options-page-footer">
					<input type="submit" class="button-primary" name="update" value="<?php _e( 'Save Widget Area', 'theme-blvd-widget-areas' ); ?>">
					<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
					<div class="clear"></div>
				</div>

			</div><!-- .postbox (end) -->
		</div><!-- .metabox-holder (end) -->
		<?php
	}

	/**
	 * Generates interface to manage sidebar overrides
	 * in meta box.
	 *
	 * @since 1.1.0
	 *
	 * @param array $settings Optional current selections for generated form.
	 */
	public function sidebar_overrides( $settings = null ) {

		global $post;

		/*
		 * If the page is loading, we're going to be pulling
		 * $settings from the meta data, but if this is being
		 * sent from Ajax, we're most likely passing the
		 * $settings in and we can skip this.
		 */
		if ( ! $settings ) {
			$settings = get_post_meta( $post->ID, '_tb_sidebars', true );
		}

		/*
		 * For the meta box, if you want to show ALL widget
		 * area locations, you'd change this to false. Most
		 * people just want to use the fixed sidebars; so we
		 * can save some clutter for the average person by
		 * having this set to true.
		 */
		$fixed_only = apply_filters( 'themeblvd_sidebar_overrides_fixed_only', true );

		/*
		 * Construct <select> of ALL custom widget areas. --
		 * Because this is our override meta box, we don't care
		 * about the "location" and the user can just override
		 * with whatever custom widget area they want.
		 */
		$custom_sidebars = get_posts( 'post_type=tb_sidebar&numberposts=-1' );

		$sidebars_select = array(
			'default' => ' &#8211; ' . __( 'No Override', 'theme-blvd-widget-areas' ) . ' &#8211; ',
		);

		foreach ( $custom_sidebars as $sidebar ) {
			$sidebars_select[ $sidebar->post_name ] = $sidebar->post_title;
		}

		// Setup options for sidebar locations.
		$options = array(
			array(
				'type'  => 'info',
				'desc'  => __( 'Here you can select any custom widget areas you\'d like applied to the sidebars of this specific page. When utilizing this feature, current locations and assignments of your custom widget areas setup under <a href="themes.php?page=themeblvd_widget_areas">Appearance > Widget Areas</a> will be ignored.', 'theme-blvd-widget-areas' ),
				'class' => 'section-description',
			),
		);

		$locations = themeblvd_get_sidebar_locations();

		foreach ( $locations as $location ) {

			/*
			 * If we're only doing fixed sidebars and this
			 * isn't a fixed sidebar, move onto the next location.
			 */
			if ( $fixed_only && $location['type'] != 'fixed' ) {
				continue;
			}

			// Add option for this location.
			$options[] = array(
				'name'    => $location['location']['name'],
				'desc'    => sprintf( __( 'Select from any of your custom widget areas to override the %s location on this page only.', 'theme-blvd-widget-areas' ), $location['location']['name'] ),
				'id'      => $location['location']['id'],
				'type'    => 'select',
				'options' => $sidebars_select,
			);

		}

		/**
		 * Filters the options used for the sidebar overrides form.
		 *
		 * @link http://dev.themeblvd.com/tutorial/formatting-options
		 *
		 * @since 1.1.0
		 *
		 * @param array Options compatible with Theme Blvd framework options system.
		 */
		$options = apply_filters( 'themeblvd_sidebar_overrides', $options );

		$form = themeblvd_option_fields( '_tb_sidebars', $options, $settings, false );

		$nonce = wp_create_nonce( 'themeblvd_sidebar_overrides' );

		echo '<input type="hidden" name="_tb_sidebar_overrides_nonce" value="' . $nonce . '" />';

		echo $form[0];

	}

}
