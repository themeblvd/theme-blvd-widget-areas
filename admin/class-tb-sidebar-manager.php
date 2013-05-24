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
		
		// Filter on javascript locals specifically for Widget Areas Manager 
		// onto Theme Blvd framework locals.
		add_filter( 'themeblvd_locals_js', array( $this, 'add_js_locals' ) );

		// Add ajax functionality to sidebar admin page
		include_once( TB_SIDEBARS_PLUGIN_DIR . '/admin/class-tb-sidebar-ajax.php' );
		$ajax = new Theme_Blvd_Sidebar_Ajax( $this );	
		
		// Add meta box
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

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
	 * Add a meta box for editing/adding layout.
	 *
	 * @since 1.1.0 
	 */
	function add_meta_box() {
	
		global $pagenow;
		global $typenow;
			
		$args = apply_filters( 'themeblvd_sidebar_meta_box', array(
			'id' 		=> 'tb_sidebars',
			'name'		=> __('Sidebar Overrides', 'themeblvd_sidebars'),
			'callback'	=> array( $this, 'meta_box' ),
			'post_type'	=> array( 'page', 'post' ),
			'context'	=> 'normal',
			'priority'	=> 'default'
		));
		
		if( $args['post_type'] ){ // In theory, if you were trying to prevent the metabox or any of its elements from being added, you'd filter $args['post_type'] to null.
			// Include assets
			foreach( $args['post_type'] as $post_type ){
				// Include assets
				if( $pagenow == 'post.php' || $pagenow == 'post-new.php' && $typenow == $post_type ){
					add_action( 'admin_enqueue_scripts', array( $this, 'load_styles' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
					add_action( 'admin_enqueue_scripts', 'optionsframework_mlu_css', 0 );
					add_action( 'admin_enqueue_scripts', 'optionsframework_mlu_js', 0 );
				}
				// Add meta box
				add_meta_box( $args['id'], $args['name'], $args['callback'], $post_type, $args['context'], $args['priority'] );		
			}
		}
	}
	
	/**
	 * Save metabox for editing layouts from Edit Page screen.
	 *
	 * @since 1.1.0 
	 */
	function save_meta_box( $post_id ) {
		
		// Verify that this coming from the edit post page.
		if( ! isset( $_POST['action'] ) || $_POST['action'] != 'editpost' )
			return;
		
		// Verfiy nonce
		if( ! isset( $_POST['_tb_sidebar_overrides_nonce'] ) || ! wp_verify_nonce( $_POST['_tb_sidebar_overrides_nonce'], 'themeblvd_sidebar_overrides' ) )
			return;
		
		// Verify this is not an autosave
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		
		// Save sidebar overrides meta
		if( ! empty( $_POST['_tb_sidebars'] ) ){
			$clean = array();
			foreach( $_POST['_tb_sidebars'] as $key => $value ){
				$clean[$key] = apply_filters( 'themeblvd_sanitize_text', $value );
			}
			update_post_meta( $post_id, '_tb_sidebars', $clean );
		}
		
	}

	/**
	 * Loads the CSS 
	 *
	 * @since 1.0.0
	 */
	public function load_styles() {
		
		global $pagenow;
		
		wp_enqueue_style( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		
		if( $pagenow == 'post-new.php' || $pagenow == 'post.php' ) {
			wp_enqueue_style( 'themeblvd_sidebars', TB_SIDEBARS_PLUGIN_URI . '/admin/assets/css/sidebars.min.css', null, TB_SIDEBARS_PLUGIN_VERSION );
		}
	}
	
	/**
	 * Loads the javascript 
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {
		
		global $pagenow;

		// Theme Blvd scripts
		wp_enqueue_script( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/js/shared.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/js/options.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'themeblvd_sidebars', TB_SIDEBARS_PLUGIN_URI . '/admin/assets/js/sidebars.min.js', array('jquery'), TB_SIDEBARS_PLUGIN_VERSION );
		
		// Add JS locals. Not needed for Edit Page screen, already exists.
		if( $pagenow != 'post-new.php' && $pagenow != 'post.php' ) {
			wp_localize_script( 'themeblvd_sidebars', 'themeblvd', themeblvd_get_admin_locals( 'js' ) ); // @see add_js_locals()
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
			'edit_sidebar'			=> __( 'Edit', 'themeblvd_sidebars' ),
			'delete_sidebar'		=> __( 'Are you sure you want to delete the widget area(s)?', 'themeblvd_sidebars' ),
			'sidebar_created'		=> __( 'Widget Area created!', 'themeblvd_sidebars' ),
			'sidebar_layout_set'	=> __( 'With how you\'ve selected to start your layout, there is already a sidebar layout applied initially.', 'themeblvd_sidebars' )
		);
		return array_merge($current, $new);
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
			<div id="optionsframework" class="wrap tb-options-js">
			    
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
	 * Builds out the meta box to edit a page's custom layout.
	 *
	 * @since 1.1.0
	 */
	public function meta_box() {
		?>
		<div id="sidebar_blvd">
			<div id="optionsframework" class="tb-options-js">
				
				<!-- HEADER (start) -->
				
				<div class="meta-box-nav">
					<div class="select-layout">
						<div class="ajax-overlay"></div>
						<div class="icon-holder">
							<span class="tb-loader ajax-loading"></span>
							<?php screen_icon( 'themes' ); ?>
						</div>
						<span class="note"><?php _e('Select any custom sidebars you\'d like applied to this page.', 'themeblvd_sidebars'); ?></span>
					</div>
					<ul>
						<li><a href="#override_sidebars"><?php _e('Assign Overrides', 'themeblvd_sidebars'); ?></a></li>
						<li><a href="#add_sidebar"><?php _e('Add Sidebar', 'themeblvd_sidebars'); ?></a></li>
					</ul>
					<div class="clear"></div>
				</div><!-- .meta-box-nav (end) -->
				
				<!-- HEADER (end) -->
				
				<!-- ASSIGN OVERRIDES (start) -->
				
				<div id="override_sidebars" class="group">
					<div class="ajax-mitt">
						<?php $this->sidebar_overrides(); ?>
					</div><!-- .ajax-mitt (end) -->
				</div>
				
				<!-- ASSIGN OVERRIDES (end) -->
				
				<!-- ADD NEW (start) -->
				
				<div id="add_sidebar" class="group">
					<?php $this->add_sidebar_mini(); ?>
				</div><!-- #manage (end) -->
				
				<!-- ADD NEW (end) -->
			    
			</div><!-- #optionsframework (end) -->
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
	 * Generates the the interface to add a new slider when 
	 * in the meta box interface.
	 *
	 * @since 1.1.0
	 */
	public function add_sidebar_mini() {
		?>
		<div class="section">
			<div class="add-sidebar-items">
				<?php $nonce = wp_create_nonce( 'themeblvd_new_sidebar' ); ?>
				<input type="hidden" name="_tb_new_sidebar_nonce" value="<?php echo $nonce; ?>" />
				<input type="text" name="_tb_new_sidebar_name" placeholder="<?php _e('New Sidebar Name', 'themeblvd_sidebars'); ?>" />
				<a href="#" class="new-sidebar-submit button"><?php _e('Add Sidebar', 'themeblvd_sidebars'); ?></a>
				<p class="explain"><?php _e('Enter a user-friendly name for your new sidebar and add it.', 'themeblvd_sidebars'); ?></p>
			</div>
		</div>
		<div class="add-sidebar-note">
			<p><?php _e('Note: Any sidebars you create here will initially be created as "floating" widget areas with no assignments. If you need to, you can edit these in the future from Appearance > Widget Areas.', 'themeblvd_sidebars'); ?></p>
		</div>
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
	
	/**
	 * Generates interface to manage sidebar overrides 
	 * in meta box.
	 *
	 * @since 1.1.0
	 *
	 * @param array $settings Optional current selections for generated form
	 */
	public function sidebar_overrides( $settings = null ) {
		global $post;
		
		// If the page is loading, we're going to be pulling 
		// $settings from the meta data, but if this is being 
		// sent from Ajax, we're most likely passing the 
		// $settings in and we can skip this.
		if( ! $settings )
			$settings = get_post_meta( $post->ID, '_tb_sidebars', true );
		
		// For the meta box, if you want to show ALL widget 
		// area locations, you'd change this to false. Most 
		// people just want to use the fixed sidebars; so we 
		// can save some clutter for the average person by 
		// having this set to true.
		$fixed_only = apply_filters( 'themeblvd_sidebar_overrides_fixed_only', true );
		
		// Construct <select> of ALL custom widget areas. --
		// Because this is our override meta box, we don't care 
		// about the "location" and the user can just override 
		// with whatever custom widget area they want.
		$custom_sidebars = get_posts('post_type=tb_sidebar&numberposts=-1');
		$sidebars_select = array( 'default' => ' &#8211; '.__( 'No Override', 'themeblvd_sidebars' ).' &#8211; ');
		foreach( $custom_sidebars as $sidebar ) {
			$sidebars_select[$sidebar->post_name] = $sidebar->post_title;
		}
		
		// Setup options for sidebar locations
		$options = array(
			array(
				'type' 	=> 'info',
				'desc' 	=> __( 'Here you can select any custom widget areas you\'d like applied to the sidebars of this specific page. When utilizing this feature, current locations and assignments of your custom widget areas setup under <a href="themes.php?page=themeblvd_widget_areas">Appearance > Widget Areas</a> will be ignored.', 'themeblvd_sidebars' ),
				'class'	=> 'section-description'
			)
		);
		$locations = themeblvd_get_sidebar_locations();
		foreach( $locations as $location ) {
			
			// If we're only doing fixed sidebars and this 
			// isn't a fixed sidebar, move onto the next location.
			if( $fixed_only && $location['type'] != 'fixed' )
				continue;

			// Add option for this location
			$options[] = array( 
				'name' 		=> $location['location']['name'],
				'desc' 		=> sprintf( __('Select from any of your custom widget areas to override the %s location on this page only.', 'themeblvd_sidebars'), $location['location']['name'] ),
				'id' 		=> $location['location']['id'],
				'type' 		=> 'select',
				'options' 	=> $sidebars_select
			);
			
		}
		$options = apply_filters( 'themeblvd_sidebar_overrides', $options );

		// Build form
		$form = themeblvd_option_fields( '_tb_sidebars', $options, $settings, false );
		
		// And spit it out
		$nonce = wp_create_nonce( 'themeblvd_sidebar_overrides' );
		echo '<input type="hidden" name="_tb_sidebar_overrides_nonce" value="'.$nonce.'" />';
		echo $form[0];
		
	}
	
}