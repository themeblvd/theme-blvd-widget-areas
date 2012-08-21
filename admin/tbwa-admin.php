<?php
/**
 * Add a subpages for Sidebars to the appearance menu.
 *
 * @since 1.0.0
 */

function themeblvd_sidebar_admin_add_page() {
	
	$title = __( 'Widget Areas', 'tbwa' );
	$admin_page = add_theme_page( $title, $title, themeblvd_admin_module_cap( 'sidebars' ), 'themeblvd_widget_areas', 'themeblvd_sidebar_admin_page' );
	
	// Adds actions to hook in the required css and javascript
	add_action( 'admin_print_styles-'.$admin_page, 'optionsframework_load_styles' );
	add_action( 'admin_print_scripts-'.$admin_page, 'optionsframework_load_scripts' );
	add_action( 'admin_print_styles-'.$admin_page, 'themeblvd_sidebar_admin_load_styles' );
	add_action( 'admin_print_scripts-'.$admin_page, 'themeblvd_sidebar_admin_load_scripts' );
	add_action( 'admin_print_styles-'.$admin_page, 'optionsframework_mlu_css', 0 );
	add_action( 'admin_print_scripts-'.$admin_page, 'optionsframework_mlu_js', 0 );
	
}

/**
 * Hack the appearance submenu a to get "Widget Areas" to 
 * show up just below "Widgets"
 *
 * @since 1.0.0
 */

function themeblvd_sidebar_admin_hijack_submenu() {
	
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
 * Loads the CSS 
 *
 * @since 1.0.0
 */

function themeblvd_sidebar_admin_load_styles() {
	wp_enqueue_style( 'sharedframework-style', THEMEBLVD_ADMIN_ASSETS_URI . '/css/admin-style.css', null, TB_FRAMEWORK_VERSION );
}	

/**
 * Loads the javascript 
 *
 * @since 1.0.0
 */

function themeblvd_sidebar_admin_load_scripts() {
	wp_enqueue_script( 'jquery-ui-sortable');
	wp_enqueue_script( 'sharedframework-scripts', THEMEBLVD_ADMIN_ASSETS_URI . '/js/shared.js', array('jquery'), TB_FRAMEWORK_VERSION );
	wp_enqueue_script( 'sidebarsframework-scripts', TB_WA_PLUGIN_URL . '/admin/js/sidebars.js', array('jquery'), TB_WA_PLUGIN_VERSION );
	wp_localize_script( 'sharedframework-scripts', 'themeblvd', themeblvd_get_admin_locals( 'js' ) );
}

/**
 * Message for Widgets page.
 *
 * @since 1.0.0 
 */

function themeblvd_widgets_admin_page() {
	// Kind of a sloppy w/all the yucky inline styles, but otherwise, 
	// we'd have to enqueue an entire stylesheet just for the widgets 
	// page of the admin panel.
	echo '<div style="width:300px;float:right;position:relative;z-index:1000"><p class="description" style="padding-left:5px">';
	_e( 'In the <a href="themes.php?page=themeblvd_widget_areas">Widget Area Manager</a>, you can create and manage widget areas for specific pages of your website to override the default locations you see below.', 'tbwa');
	echo '</p></div>';
}	

/**
 * Builds out the full admin page.
 *
 * @since 1.0.0 
 */

function themeblvd_sidebar_admin_page() {
	?>
	<div id="sidebar_blvd">
		<div id="optionsframework" class="wrap">
		    
		    <div class="admin-module-header">
		    	<?php do_action( 'themeblvd_admin_module_header', 'sidebars' ); ?>
		    </div>
		    <?php screen_icon( 'themes' ); ?>
		    <h2 class="nav-tab-wrapper">
		        <a href="#manage_sidebars" id="manage_sidebars-tab" class="nav-tab" title="<?php _e( 'Custom Widget Areas', 'tbwa' ); ?>"><?php _e( 'Custom Widget Areas', 'tbwa' ); ?></a>
		        <a href="#add_sidebar" id="add_sidebar-tab" class="nav-tab" title="<?php _e( 'Add New', 'tbwa' ); ?>"><?php _e( 'Add New', 'tbwa' ); ?></a>
		        <a href="#edit_sidebar" id="edit_sidebar-tab" class="nav-tab nav-edit-sidebar" title="<?php _e( 'Edit', 'tbwa' ); ?>"><?php _e( 'Edit', 'tbwa' ); ?></a>
		    </h2>
		    
			<!-- MANAGE SIDEBARS (start) -->
			
			<div id="manage_sidebars" class="group">
		    	<form id="manage_current_sidebars">	
		    		<?php 
		    		$manage_nonce = wp_create_nonce( 'optionsframework_manage_sidebars' );
					echo '<input type="hidden" name="option_page" value="optionsframework_manage_sidebars" />';
					echo '<input type="hidden" name="_wpnonce" value="'.$manage_nonce.'" />';
					?>
					<div class="ajax-mitt"><?php themeblvd_manage_sidebars(); ?></div>
				</form><!-- #manage_sidebars (end) -->
			</div><!-- #manage (end) -->
			
			<!-- MANAGE SIDEBARS (end) -->
			
			<!-- ADD SIDEBAR (start) -->
			
			<div id="add_sidebar" class="group">
				<form id="add_new_sidebar">
					<?php
					$add_nonce = wp_create_nonce( 'optionsframework_new_sidebar' );
					echo '<input type="hidden" name="option_page" value="optionsframework_add_sidebars" />';
					echo '<input type="hidden" name="_wpnonce" value="'.$add_nonce.'" />';
					themeblvd_add_sidebar();
					?>
				</form><!-- #add_new_sidebars (end) -->
			</div><!-- #manage (end) -->
			
			<!-- ADD SIDEBAR (end) -->
			
			<!-- EDIT SIDEBAR (start) -->
			
			<div id="edit_sidebar" class="group">
				<form id="edit_current_sidebar" method="post">
					<?php
					$edit_nonce = wp_create_nonce( 'optionsframework_save_sidebar' );
					echo '<input type="hidden" name="action" value="update" />';
					echo '<input type="hidden" name="option_page" value="optionsframework_edit_sidebars" />';
					echo '<input type="hidden" name="_wpnonce" value="'.$edit_nonce.'" />';
					?>
					<div class="ajax-mitt"><!-- AJAX inserts edit sidebars page here. --></div>
				</form>
			</div><!-- #manage (end) -->
		
			<!-- EDIT SIDEBAR (end) -->
			
			<div class="admin-module-footer">
				<?php do_action( 'themeblvd_admin_module_footer', 'sidebars' ); ?>
			</div>
			
		</div> <!-- #optionsframework (end) -->
	</div><!-- #sidebar_blvd (end) -->
	<?php
}