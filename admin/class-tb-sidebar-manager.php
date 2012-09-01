<?php
/**
 * Sidebar Manager
 */
class Theme_Blvd_Sidebar_Manager {
	
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		
		// Add sidebar manager admin page
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'hijack_submenu' ) );
		add_action( 'widgets_admin_page', array( $this, 'widgets_page' ) );
		
		// Add ajax functionality to sidebar admin page
		include_once( TB_SIDEBARS_PLUGIN_DIR . '/admin/class-tb-sidebar-ajax.php' );
		$ajax = new Theme_Blvd_Sidebar_Ajax( $this );	

	}
	
	/**
	 * Add a subpages for Sidebars to the appearance menu.
	 *
	 * @since 1.0.0
	 */
	public function add_page() {
		$title = __( 'Widget Areas', 'themeblvd_sidebars' );
		$admin_page = add_theme_page( $title, $title, themeblvd_admin_module_cap( 'sidebars' ), 'themeblvd_widget_areas', array( $this, 'admin_page' ) );
		add_action( 'admin_print_styles-'.$admin_page, array( $this, 'load_styles' ) );
		add_action( 'admin_print_scripts-'.$admin_page, array( $this, 'load_scripts' ) );
		add_action( 'admin_print_styles-'.$admin_page, 'optionsframework_mlu_css', 0 );
		add_action( 'admin_print_scripts-'.$admin_page, 'optionsframework_mlu_js', 0 );	
	}

	/**
	 * Loads the CSS 
	 *
	 * @since 1.0.0
	 */
	public function load_styles() {
		wp_enqueue_style( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
	}	
	
	/**
	 * Loads the javascript 
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {
		wp_enqueue_script( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/js/shared.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_localize_script( 'themeblvd_admin', 'themeblvd', themeblvd_get_admin_locals( 'js' ) );
		wp_enqueue_script( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/js/options.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'themeblvd_sidebars', TB_SIDEBARS_PLUGIN_URI . '/admin/js/sidebars.min.js', array('jquery'), TB_SIDEBARS_PLUGIN_VERSION );
		wp_localize_script( 'themeblvd_sidebars', 'themeblvd', themeblvd_get_admin_locals( 'js' ) );
	}
	
	/**
	 * Message for Widgets page.
	 *
	 * @since 1.0.0 
	 */
	public function widgets_page() {
		// Kind of a sloppy w/all the yucky inline styles, but otherwise, 
		// we'd have to enqueue an entire stylesheet just for the widgets 
		// page of the admin panel.
		echo '<div style="width:300px;float:right;position:relative;z-index:1000"><p class="description" style="padding-left:5px">';
		printf( __( 'In the %s, you can create and manage widget areas for specific pages of your website to override the default locations you see below.', 'themeblvd_sidebars' ), '<a href="themes.php?page=themeblvd_widget_areas">'.__( 'Widget Area Manager', 'themeblvd_sidebars' ).'</a>' );
		echo '</p></div>';
	}	

	/**
	 * Builds out the full admin page.
	 *
	 * @since 1.0.0 
	 */
	public function admin_page() {
		?>
		<div id="sidebar_blvd">
			<div id="optionsframework" class="wrap">
			    
			    <div class="admin-module-header">
			    	<?php do_action( 'themeblvd_admin_module_header', 'sidebars' ); ?>
			    </div>
			    <?php screen_icon( 'themes' ); ?>
			    <h2 class="nav-tab-wrapper">
			        <a href="#manage_sidebars" id="manage_sidebars-tab" class="nav-tab" title="<?php _e( 'Custom Widget Areas', 'themeblvd_sidebars' ); ?>"><?php _e( 'Custom Widget Areas', 'themeblvd_sidebars' ); ?></a>
			        <a href="#add_sidebar" id="add_sidebar-tab" class="nav-tab" title="<?php _e( 'Add New', 'themeblvd_sidebars' ); ?>"><?php _e( 'Add New', 'themeblvd_sidebars' ); ?></a>
			        <a href="#edit_sidebar" id="edit_sidebar-tab" class="nav-tab nav-edit-sidebar" title="<?php _e( 'Edit', 'themeblvd_sidebars' ); ?>"><?php _e( 'Edit', 'themeblvd_sidebars' ); ?></a>
			    </h2>
			    
				<!-- MANAGE SIDEBARS (start) -->
				
				<div id="manage_sidebars" class="group">
			    	<form id="manage_current_sidebars">	
			    		<?php 
			    		$manage_nonce = wp_create_nonce( 'themeblvd_manage_sidebars' );
						echo '<input type="hidden" name="option_page" value="themeblvd_manage_sidebars" />';
						echo '<input type="hidden" name="_wpnonce" value="'.$manage_nonce.'" />';
						?>
						<div class="ajax-mitt"><?php $this->manage_sidebars(); ?></div>
					</form><!-- #manage_sidebars (end) -->
				</div><!-- #manage (end) -->
				
				<!-- MANAGE SIDEBARS (end) -->
				
				<!-- ADD SIDEBAR (start) -->
				
				<div id="add_sidebar" class="group">
					<form id="add_new_sidebar">
						<?php
						$add_nonce = wp_create_nonce( 'themeblvd_new_sidebar' );
						echo '<input type="hidden" name="option_page" value="themeblvd_new_sidebar" />';
						echo '<input type="hidden" name="_wpnonce" value="'.$add_nonce.'" />';
						$this->add_sidebar();
						?>
					</form><!-- #add_new_sidebars (end) -->
				</div><!-- #manage (end) -->
				
				<!-- ADD SIDEBAR (end) -->
				
				<!-- EDIT SIDEBAR (start) -->
				
				<div id="edit_sidebar" class="group">
					<form id="edit_current_sidebar" method="post">
						<?php
						$edit_nonce = wp_create_nonce( 'themeblvd_save_sidebar' );
						echo '<input type="hidden" name="action" value="update" />';
						echo '<input type="hidden" name="option_page" value="themeblvd_save_sidebar" />';
						echo '<input type="hidden" name="_wpnonce" value="'.$edit_nonce.'" />';
						?>
						<div class="ajax-mitt"><!-- AJAX inserts edit sidebars page here. --></div>
					</form>
				</div><!-- #manage (end) -->
			
				<!-- EDIT SIDEBAR (end) -->
				
				<div class="admin-module-footer">
					<?php do_action( 'themeblvd_admin_module_footer', 'sidebars' ); ?>
				</div>
				
			</div><!-- #optionsframework (end) -->
		</div><!-- #sidebar_blvd (end) -->
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
		
		if( ! empty( $submenu ) ) {
			
			// Find the current "Widget Areas" array, copy it, and remove it.
			foreach( $submenu['themes.php'] as $key => $value ) {
				if( $value[2] == 'themeblvd_widget_areas' ) {
					$widget_areas = $value;
					unset( $submenu['themes.php'][$key] );
				}
			}
			
			// Reconstruct the new submenu
			if( isset( $widget_areas ) ) {
				foreach( $submenu['themes.php'] as $key => $value ) {
					// Add original item to new menu
					$new_submenu[$key] = $value;
					// If this is the "Widgets" item, add in our 
					// "Widget Areas" item directly after.
					if( $value[2] == 'widgets.php' )
						$new_submenu[] = $widget_areas;
				}
			}
			
			// Replace old submenu with new submenu
			$submenu['themes.php'] = $new_submenu;
		}
	}
	
	/**
	 * Generates the the interface to manage sidebars.
	 *
	 * @since 1.0.0
	 */
	public function manage_sidebars() {
		
		// Setup columns for management table
		$columns = array(
			array(
				'name' 		=> __( 'Widget Area Title', 'themeblvd_sidebars' ),
				'type' 		=> 'title',
			),
			array(
				'name' 		=> __( 'ID', 'themeblvd_sidebars' ),
				'type' 		=> 'slug',
			),
			/* Hiding the true post ID from user to avoid confusion.
			array(
				'name' 		=> __( 'ID', 'themeblvd_sidebars' ),
				'type' 		=> 'id',
			),
			*/
			array(
				'name' 		=> __( 'Location', 'themeblvd_sidebars' ),
				'type' 		=> 'sidebar_location'
			),
			array(
				'name' 		=> __( 'Assignments', 'themeblvd_sidebars' ),
				'type' 		=> 'assignments',
			)
		);
		$columns = apply_filters( 'themeblvd_manage_sidebars', $columns );
		
		// Display the table
		echo '<div class="metabox-holder">'.themeblvd_post_table( 'tb_sidebar', $columns ).'</div><!-- .metabox-holder (end) -->';
	}

	/**
	 * Generates the the interface to add a new slider.
	 *
	 * @since 1.0.0
	 */
	public function add_sidebar() {
		
		// Setup sidebar layouts
		$sidebars = themeblvd_get_sidebar_locations();
		$sidebar_locations = array( 'floating' => __( 'No Location (Floating Widget Area)', 'themeblvd_sidebars' ) );
		foreach( $sidebars as $sidebar )
			$sidebar_locations[$sidebar['location']['id']] = $sidebar['location']['name'];
			
		// Setup options array to display form
		$options = array(
			array( 
				'name' 		=> __( 'Widget Area Name', 'themeblvd_sidebars' ),
				'desc' 		=> __( 'Enter a user-friendly name for your widget area.<br><br><em>Example: My Sidebar</em>', 'themeblvd_sidebars' ),
				'id' 		=> 'sidebar_name',
				'type' 		=> 'text'
			),
			array( 
				'name' 		=> __( 'Widget Area Location', 'themeblvd_sidebars' ),
				'desc' 		=> __( 'Select which location on the site this widget area will be among the theme\'s currently supported widget area locations.<br><br><em>Note: A "Floating Widget Area" can be used in dynamic elements like setting up columns in the layout builder, for example.</em>', 'themeblvd_sidebars' ),
				'id' 		=> 'sidebar_location',
				'type' 		=> 'select',
				'options' 	=> $sidebar_locations,
			),
			array( 
				'name' 		=> __( 'Widget Area Assignments', 'themeblvd_sidebars' ),
				'desc' 		=> __( 'Select the places on your site you\'d like this custom widget area to show in the location you picked previously.<br><br><em>Note: You can edit the location you selected previously and these assignments later if you change your mind.</em><br><br><em>Note: Assignments will be ignored on "Floating Widget Areas" but since you can always come back and change the location for a custom widget area, assignments still will always be stored.</em>', 'themeblvd_sidebars' ),
				'id' 		=> 'sidebar_assignments',
				'type' 		=> 'conditionals'
			)
		);
		$options = apply_filters( 'themeblvd_add_sidebar', $options );
		
		// Build form
		$form = themeblvd_option_fields( 'options', $options, null, false );
		?>
		<div class="metabox-holder">
			<div class="postbox">
				<h3><?php _e( 'Add New Widget Area', 'themeblvd_sidebars' ); ?></h3>
				<form id="add_new_sidebar">
					<div class="inner-group">
						<?php echo $form[0]; ?>
					</div><!-- .group (end) -->
					<div id="optionsframework-submit">
						<input type="submit" class="button-primary" name="update" value="<?php _e( 'Add New Widget Area', 'themeblvd_sidebars' ); ?>">
						<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
			            <div class="clear"></div>
					</div>
				</form><!-- #add_new_slider (end) -->
			</div><!-- .postbox (end) -->
		</div><!-- .metabox-holder (end) -->
		<?php
	}

	/**
	 * Generates the the interface to edit the sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @param $id string ID of sidebar to edit
	 */
	public function edit_sidebar( $id ) {
		
		$post = get_post( $id );
		
		// Setup sidebar layouts
		$sidebars = themeblvd_get_sidebar_locations();
		$sidebar_locations = array( 'floating' => __( 'No Location (Floating Widget Area)', 'themeblvd_sidebars' ) );
		foreach( $sidebars as $sidebar )
			$sidebar_locations[$sidebar['location']['id']] = $sidebar['location']['name'];
			
		// Setup options array to display form
		$options = array(
			array( 
				'name' 		=> __( 'Widget Area Name', 'themeblvd_sidebars' ),
				'desc' 		=> __( 'Here you can edit the name of your widget area. This will adjust how your widget area is labeled for you here in the WordPress admin panel.', 'themeblvd_sidebars' ),
				'id' 		=> 'post_title',
				'type' 		=> 'text'
			),
			array( 
				'name' 		=> __( 'Widget Area ID', 'themeblvd_sidebars' ),
				'desc' 		=> __( 'Here you can edit the internal ID of your widget area.<br><br><em>Warning: This is how WordPress assigns your widgets and how the theme applies your widget area. If you change this ID, you will need to re-assign any widgets under Appearance > Widgets, and re-visit any areas you may have added this as a floating widget area.</em>', 'themeblvd_sidebars' ),
				'id' 		=> 'post_name',
				'type' 		=> 'text',
				'class'		=> 'hide' // Hidden from user. For debugging can display and change with dev console.
			),
			array( 
				'name' 		=> __( 'Widget Area Location', 'themeblvd_sidebars' ),
				'desc' 		=> __( 'Select which location on the site this widget area will be among the theme\'s currently supported widget area locations.<br><br><em>Note: A "Floating Widget Area" can be used in dynamic elements like setting up columns in the layout builder, for example.</em>', 'themeblvd_sidebars' ),
				'id' 		=> 'sidebar_location',
				'type' 		=> 'select',
				'options' 	=> $sidebar_locations,
			),
			array( 
				'name' 		=> __( 'Widget Area Assignments', 'themeblvd_sidebars' ),
				'desc' 		=> __( 'Select the places on your site you\'d like this custom widget area to show in the location you picked previously.<br><br><em>Note: Assignments will be ignored on "Floating Widget Areas" but since you can always come back and change the location for a custom widget area, assignments still will always be stored.</em>', 'themeblvd_sidebars' ),
				'id' 		=> 'sidebar_assignments',
				'type' 		=> 'conditionals'
			)
		);
		
		// Settup current settings
		$settings = array(
			'post_title' 			=> $post->post_title,
			'post_name' 			=> $post->post_name,
			'sidebar_location' 		=> get_post_meta( $id, 'location', true ),
			'sidebar_assignments' 	=> get_post_meta( $id, 'assignments', true )
		);
		
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
				<div id="optionsframework-submit">
					<input type="submit" class="button-primary" name="update" value="<?php _e( 'Save Widget Area', 'themeblvd_sidebars' ); ?>">
					<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
		            <div class="clear"></div>
				</div>
			</div><!-- .postbox (end) -->
		</div><!-- .metabox-holder (end) -->
		<?php
	}
	
}