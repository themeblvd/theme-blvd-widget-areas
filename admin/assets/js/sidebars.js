jQuery(document).ready(function($) {
	
	/*-----------------------------------------------------------------------------------*/
	/* Static Methods
	/*-----------------------------------------------------------------------------------*/
	
	var sidebar_blvd = {
		
		// Delete Sidebar
    	delete_sidebar : function( ids, action )
    	{
    		var nonce  = $('#manage_current_sidebars').find('input[name="_wpnonce"]').val();
			tbc_confirm( themeblvd.delete_sidebar, {'confirm':true}, function(r)
			{
		    	if(r)
		        {
		        	$.ajax({
						type: "POST",
						url: ajaxurl,
						data:
						{
							action: 'themeblvd_delete_sidebar',
							security: nonce,
							data: ids
						},
						success: function(response)
						{	
							// Prepare response
							response = response.split('[(=>)]');
							
							// Scroll to top of page
							$('body').animate( { scrollTop: 0 }, 100, function(){						
								
								// Insert update message, fade it in, and then remove it 
								// after a few seconds.
								$('#sidebar_blvd #manage_sidebars').prepend(response[0]);
								$('#sidebar_blvd #manage_sidebars .ajax-update').fadeIn(500, function(){
									setTimeout( function(){
										$('#sidebar_blvd #manage_sidebars .ajax-update').fadeOut(500, function(){
											$('#sidebar_blvd #manage_sidebars .ajax-update').remove();
										});
							      	}, 1500);
								
								});
								
								// Update table
								$('#sidebar_blvd #manage_sidebars .ajax-mitt').hide().html(response[1]).fadeIn('fast');
							});
						}
					});
		        }
		    });
    	}

	};
	
	/*-----------------------------------------------------------------------------------*/
	/* General setup
	/*-----------------------------------------------------------------------------------*/
	
	// Items from themeblvd namespace
	$('#sidebar_blvd .accordion').themeblvd('accordion');
	
	// Hide secret tab when page loads
	$('#sidebar_blvd .nav-tab-wrapper a.nav-edit-sidebar').hide();
	
	// If the active tab is on edit layout page, we'll 
	// need to override the default functionality of 
	// the Options Framework JS, because we don't want 
	// to show a blank page.
	if (typeof(localStorage) != 'undefined' )
	{
		if( localStorage.getItem('activetab') == '#edit_sidebar')
		{
			$('#sidebar_blvd .group').hide();
			$('#sidebar_blvd .group:first').fadeIn();
			$('#sidebar_blvd .nav-tab-wrapper a:first').addClass('nav-tab-active');
		}
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Meta Box (layout builder used when editing pages directly)
	/*-----------------------------------------------------------------------------------*/

	$('#tb_sidebars').each(function(){
		
		var meta_box = $(this);
		
		// Setup Tabs for Builder meta box
		meta_box.find('.meta-box-nav li:first a').addClass('nav-tab-active');
		meta_box.find('.group').hide();
		meta_box.find('#override_sidebars').show();
		meta_box.find('.meta-box-nav li a').click(function(){
			var anchor = $(this), target = anchor.attr('href');
			meta_box.find('.meta-box-nav li a').removeClass('nav-tab-active');
			anchor.addClass('nav-tab-active');
			meta_box.find('.group').hide();
			meta_box.find(target).show();
			return false;
		});
		
		// Setup new sidebar submit
		meta_box.find('.new-sidebar-submit').click(function(){
			
			var el = $(this),
				parent = el.closest('.add-sidebar-items'),
				name = parent.find('input[name=_tb_new_sidebar_name]').val(),
				nonce = parent.find('input[name=_tb_new_sidebar_nonce]').val(),
				form_data = $('#post').serialize();
			
			// Tell user they forgot a name
			if(!name)
			{
				tbc_confirm(themeblvd.no_name, {'textOk':'Ok'});
			    return false;
			}
			
			// Trigger loading indicators
			meta_box.find('.meta-box-nav .ajax-overlay').css('visibility', 'visible').fadeIn('fast');
			meta_box.find('.meta-box-nav .ajax-loading').css('visibility', 'visible').fadeIn('fast');
			meta_box.find('#override_sidebars').prepend('<div class="ajax-overlay-sidebars-switch"></div>');
			meta_box.find('#override_sidebars .ajax-overlay-sidebars-switch').fadeIn('fast');
						
			// Prep and exececute first Ajax call.
			var data = {
				action: 'themeblvd_quick_add_sidebar',
				security: nonce,
				data: form_data
			};
			$.post(ajaxurl, data, function(response) {
				
				// Insert updated sidebar selects into meta box
				meta_box.find('.ajax-mitt').html(response);
				meta_box.find('.ajax-mitt').themeblvd('options', 'setup');
				
				// Wait 1 second before bringing everything back.
				setTimeout(function () {
		    		
		    		// Switch user back to editing overrides
					meta_box.find('.meta-box-nav li a').removeClass('nav-tab-active');
					meta_box.find('.meta-box-nav li:first a').addClass('nav-tab-active');
					meta_box.find('.group').hide();
					meta_box.find('#override_sidebars').show();
					
					// Disable loading indicaters
					meta_box.find('.meta-box-nav .ajax-overlay').fadeOut('fast');
					meta_box.find('.meta-box-nav .ajax-loading').fadeOut('fast');
					meta_box.find('#override_sidebars .ajax-overlay-sidebars-switch').fadeOut('fast').remove();
					
					// Put user at the start of meta box
					$('html,body').animate({
						scrollTop: $('#tb_sidebars').offset().top - 30
					}, 'fast');
					
					// Show success message
					tbc_alert.init(themeblvd.sidebar_created, 'success', '#tb_sidebars');
					
					// Clear name field back on new layout form
					meta_box.find('input[name="_tb_new_sidebar_name"]').val('');
						
				}, 1000);
				
			});
			return false;
		});
		
	});
	
	/*-----------------------------------------------------------------------------------*/
	/* Manage Widget Areas Page
	/*-----------------------------------------------------------------------------------*/
	
	// Edit slider (via Edit Link on manage page)
	$('#sidebar_blvd #manage_sidebars .edit-tb_sidebar').live( 'click', function(){
		var name = $(this).closest('tr').find('.post-title .title-link').text(),
			id = $(this).attr('href'), 
			id = id.replace('#', '');
		
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_edit_sidebar',
				data: id
			},
			success: function(response)
			{	
				// Get the ID from the beginning
				var page = response.split('[(=>)]');
				
				// Prepare the edit tab
				$('#sidebar_blvd .nav-tab-wrapper a.nav-edit-sidebar').text(themeblvd.edit_sidebar+': '+name).addClass('edit-'+page[0]);
				$('#sidebar_blvd #edit_sidebar .ajax-mitt').html(page[1]);
				
				// Setup accordion
				$('#sidebar_blvd #edit_sidebar .accordion').themeblvd('accordion');
				
				// Setup options
				$('#sidebar_blvd #edit_sidebar').themeblvd('options', 'setup');
				
				// Take us to the tab
				$('#sidebar_blvd .nav-tab-wrapper a').removeClass('nav-tab-active');
				$('#sidebar_blvd .nav-tab-wrapper a.nav-edit-sidebar').show().addClass('nav-tab-active');
				$('#sidebar_blvd .group').hide();
				$('#sidebar_blvd .group:last').fadeIn();
			}
		});
		return false;
	});
	
	// Delete sidebar (via Delete Link on manage page)
	$('#sidebar_blvd .row-actions .trash a').live( 'click', function(){
		var href = $(this).attr('href'), id = href.replace('#', ''), ids = 'posts%5B%5D='+id;
		sidebar_blvd.delete_sidebar( ids, 'click' );
		return false;
	});
	
	// Delete sidebars via bulk action
	$('#manage_current_sidebars').live( 'submit', function(){		
		var value = $(this).find('select[name="action"]').val(), ids = $(this).serialize();
		if(value == 'trash')
		{
			sidebar_blvd.delete_sidebar( ids, 'submit' );
		}
		return false;
	});
	
	/*-----------------------------------------------------------------------------------*/
	/* Add New Widget Area Page
	/*-----------------------------------------------------------------------------------*/
	
	// Add new layout
	$('#optionsframework #add_new_sidebar').submit(function(){		
		var el = $(this),
			data = el.serialize(),
			load = el.find('.ajax-loading'),
			name = el.find('input[name="options[sidebar_name]"]').val(),
			nonce = el.find('input[name="_wpnonce"]').val();
		
		// Tell user they forgot a name
		if(!name)
		{
			tbc_confirm(themeblvd.no_name, {'textOk':'Ok'});
		    return false;
		}
			
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: 
			{
				action: 'themeblvd_add_sidebar',
				security: nonce,
				data: data
			},
			beforeSend: function()
			{
				load.fadeIn('fast');
			},
			success: function(response)
			{	
				// Update management table
				$('#sidebar_blvd #manage_sidebars .ajax-mitt').html(response);
				
				// Scroll to top of page
				$('body').animate( { scrollTop: 0 }, 100, function(){						
					// Take us back to the management tab
					$('#sidebar_blvd .nav-tab-wrapper a').removeClass('nav-tab-active');
					$('#sidebar_blvd .nav-tab-wrapper a:first').addClass('nav-tab-active');
					$('#sidebar_blvd .group').hide();
					$('#sidebar_blvd .group:first').fadeIn();
					tbc_alert.init(themeblvd.sidebar_created, 'success');
				});
				
				// Clear form
				$('#sidebar_blvd #add_new_sidebar #sidebar_name').val('');
				$('#sidebar_blvd #add_new_sidebar input').removeAttr('checked');
								
				// Hide loader no matter what.												
				load.hide();
			}
		});
		return false;
	});
	
	/*-----------------------------------------------------------------------------------*/
	/* Edit Widget Area Page
	/*-----------------------------------------------------------------------------------*/
	
	// Save Widget Area
	$('#optionsframework #edit_current_sidebar').live('submit', function(){
		var el = $(this),
			data = el.serialize(),
			load = el.find('.ajax-loading'),
			nonce = el.find('input[name="_wpnonce"]').val();

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_save_sidebar',
				security: nonce,
				data: data
			},
			beforeSend: function()
			{
				load.fadeIn('fast');
			},
			success: function(response)
			{	
			
				response = response.split('[(=>)]');
				
				// Make sure all "Widget Area Names" match on current edit page.
				current_name = $('#sidebar_blvd #post_title').val();
				$('#sidebar_blvd #edit_sidebar-tab').text(themeblvd.edit_sidebar+': '+current_name);
				$('#sidebar_blvd .postbox h3').text(current_name);
				$('#sidebar_blvd #post_name').val(response[0]);
				
				// Scroll to top of page
				$('body').animate( { scrollTop: 0 }, 100, function(){						
					// Insert update message, fade it in, and then remove it 
					// after a few seconds.
					$('#sidebar_blvd #edit_sidebar').prepend(response[1]);
					$('#sidebar_blvd #edit_sidebar .ajax-update').fadeIn(500, function(){
						setTimeout( function(){
							$('#sidebar_blvd #edit_sidebar .ajax-update').fadeOut(500, function(){
								$('#sidebar_blvd #edit_sidebar .ajax-update').remove();
							});
				      	}, 1500);
					
					});
				});
			
				// Update management table in background
				$('#sidebar_blvd #manage_sidebars .ajax-mitt').html(response[2]);
				
				load.fadeOut('fast');
			}
		});
		return false;
	});
	
});