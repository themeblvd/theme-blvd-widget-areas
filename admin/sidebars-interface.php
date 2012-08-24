<?php 
/**
 * Generates the the interface to manage sidebars.
 *
 * @since 1.0.0
 */

function themeblvd_manage_sidebars() {
	
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

function themeblvd_add_sidebar() {
	
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

function themeblvd_edit_sidebar( $id ) {
	
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