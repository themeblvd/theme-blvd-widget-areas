/**
 * Widget Area Management
 *
 * @package Theme_Blvd_Widget_Area
 * @license GPL-2.0+
 */
( function( $, admin ) {

	var l10n = {};

	if ( 'undefined' !== typeof themeblvdL10n ) {
		l10n = themeblvdL10n;
	} else if ( 'undefined' !== typeof themeblvd ) {
		l10n = themeblvd;
	}

	admin = admin || {};

	admin.sidebars = {};

	if ( 'undefined' === typeof admin.confirm ) {
		admin.confirm = tbc_confirm;
	}

	/**
	 * Delete sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @param {String} ids    Serialized string of sidebar IDs to delete.
	 * @param {String} action Event triggering the deletion.
	 */
	admin.sidebars.deleteSidebar = function( ids, action ) {

		var nonce = $( '#manage_current_sidebars' ).find( 'input[name="_wpnonce"]' ).val();

		admin.confirm( l10n.delete_sidebar, { 'confirm': true }, function( response ) {

			if ( response ) {
				$.ajax( {
					type: "POST",
					url: ajaxurl,
					data: {
						action: 'themeblvd-delete-sidebar',
						security: nonce,
						data: ids
					},
					success: function( response ) {

						// Prepare response.
						response = response.split('[(=>)]');

						// Scroll to top of page.
						$( 'body' ).animate( { scrollTop: 0 }, 100, function() {

							/*
							 * Insert update message, fade it in, and then remove it
							 * after a few seconds.
							 */

							$('#sidebar_blvd #manage_sidebars').prepend( response[ 0 ] );

							$('#sidebar_blvd #manage_sidebars .ajax-update').fadeIn( 500, function() {

								setTimeout( function() {

									$('#sidebar_blvd #manage_sidebars .ajax-update').fadeOut( 500, function() {
										$('#sidebar_blvd #manage_sidebars .ajax-update').remove();
									} );

								}, 1500 );

							});

							// Update table.
							$( '#sidebar_blvd #manage_sidebars .ajax-mitt' )
								.hide()
								.html( response[ 1 ] )
								.fadeIn( 'fast' );

						} );
					}
				} );
			}
		} );

	};

	$( document ).ready( function( $ ) {

		/**
		 * Setup accordion options.
		 *
		 * @since 1.0.0
		 */
		$( '#sidebar_blvd .accordion' ).themeblvd( 'accordion' );

		/**
		 * Hides the empty "Edit" tab on initial page load.
		 *
		 * @since 1.0.0
		 */
		$( '#sidebar_blvd .nav-tab-wrapper a.nav-edit-sidebar' ).hide();

		/**
		 * Don't show an empty "Edit" tab.
		 *
		 * If the active tab is currently the "Edit" tab we want
		 * to make sure it doesn't show on initial page load,
		 * because it will be empty.
		 *
		 * @since 1.0.0
		 */
		if ( 'undefined' != typeof localStorage ) {

			if ( '#edit_sidebar' === localStorage.getItem( 'activetab' ) ) {

				$( '#sidebar_blvd .group' ).hide();

				$( '#sidebar_blvd .group:first' ).fadeIn();

				$( '#sidebar_blvd .nav-tab-wrapper a:first' ).addClass( 'nav-tab-active' );

			}
		}

		/**
		 * Cache meta box object on Edit Post screens.
		 *
		 * @since 1.1.0
		 */
		var $metaBox = $( '#tb-sidebars-meta-box' );

		/**
		 * Setup tabs for meta box.
		 *
		 * @since 1.1.0
		 */
		$metaBox.find( '.meta-box-nav li:first a' ).addClass( 'nav-tab-active' );

		$metaBox.find( '.group' ).hide();

		$metaBox.find( '#tb-override-sidebar' ).show();

		$metaBox.find( '.meta-box-nav li a' ).on( 'click', function( event ) {

			event.preventDefault();

			var $link  = $( this ),
				target = $link.attr( 'href' );

			$metaBox.find( '.meta-box-nav li a' ).removeClass( 'nav-tab-active' );

			$link.addClass( 'nav-tab-active' );

			$metaBox.find( '.group' ).hide();

			$metaBox.find( target ).show();

		} );

		/**
		 * Handles submission to create a new sidebar.
		 *
		 * @since 1.1.0
		 */
		$metaBox.find( '.new-sidebar-submit' ).on( 'click', function( event ) {

			event.preventDefault();

			var $btn      = $( this ),
				$parent   = $btn.closest( '.add-sidebar-items' ),
				name      = $parent.find( 'input[name=_tb_new_sidebar_name]' ).val(),
				nonce     = $parent.find( 'input[name=_tb_new_sidebar_nonce]' ).val(),
				formData  = $( '#post' ).serialize();

			// Tell user they forgot a name.
			if ( ! name ) {

				admin.confirm( l10n.no_name, { 'textOk':'Ok' } );

				return false;

			}

			// Trigger loading indicators.
			$metaBox.find( '.meta-box-nav .ajax-overlay' ).css( 'visibility', 'visible' ).fadeIn( 'fast' );

			$metaBox.find( '.meta-box-nav .ajax-loading' ).css( 'visibility', 'visible' ).fadeIn( 'fast' );

			$metaBox.find( '#tb-override-sidebar' ).prepend( '<div class="ajax-overlay-sidebars-switch"></div>' );

			$metaBox.find( '#tb-override-sidebar .ajax-overlay-sidebars-switch' ).fadeIn( 'fast' );

			// Prep and exececute first Ajax call.
			var data = {
				action: 'themeblvd-quick-add-sidebar',
				security: nonce,
				data: formData
			};
			$.post( ajaxurl, data, function( response ) {

				// Insert updated sidebar selects into meta box.
				$metaBox.find( '.ajax-mitt' ).html( response );

				$metaBox.find( '.ajax-mitt' ).themeblvd( 'options', 'setup' );

				// Wait 1 second before bringing everything back.
				setTimeout( function () {

		    		// Switch user back to editing overrides
					$metaBox.find( '.meta-box-nav li a' ).removeClass( 'nav-tab-active' );

					$metaBox.find( '.meta-box-nav li:first a' ).addClass( 'nav-tab-active' );

					$metaBox.find( '.group' ).hide();

					$metaBox.find( '#tb-override-sidebar' ).show();

					// Disable loading indicaters
					$metaBox.find( '.meta-box-nav .ajax-overlay' ).fadeOut( 'fast' );

					$metaBox.find( '.meta-box-nav .ajax-loading' ).fadeOut( 'fast' );

					$metaBox.find( '#tb-override-sidebar .ajax-overlay-sidebars-switch' ).fadeOut( 'fast' ).remove();

					// Put user at the start of meta box.
					$( 'html, body' ).animate( {
						scrollTop: $( '#tb-sidebars-meta-box' ).offset().top - 30
					}, 'fast' );

					// Clear name field back on new layout form.
					$metaBox.find( 'input[name="_tb_new_sidebar_name"]' ).val( '' );

				}, 1000 );

			} );

		} );

		/**
		 * Edit custom sidebar.
		 *
		 * @since 1.0.0
		 */
		$( '#sidebar_blvd #manage_sidebars' ).on( 'click', '.edit-tb_sidebar', function( event ) {

			event.preventDefault();

			var $link = $( this ),
				name  = $link.closest( 'tr' ).find( '.post-title .title-link' ).text(),
				id    = $link.attr( 'href' ),
				id    = id.replace( '#', '' );

			$.ajax( {
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'themeblvd-edit-sidebar',
					data: id
				},
				success: function( response ) {

					var page = response.split( '[(=>)]' );

					// Prepare the edit tab.
					$( '#sidebar_blvd .nav-tab-wrapper a.nav-edit-sidebar' ).text( l10n.edit_sidebar + ': ' + name).addClass( 'edit-' + page[0] );

					$( '#sidebar_blvd #edit_sidebar .ajax-mitt' ).html( page[1] );

					// Setup options.
					$( '#sidebar_blvd #edit_sidebar .accordion' ).themeblvd( 'accordion' );

					$( '#sidebar_blvd #edit_sidebar' ).themeblvd( 'options', 'setup' );

					// Take us to the tab.
					$( '#sidebar_blvd .nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );

					$( '#sidebar_blvd .nav-tab-wrapper a.nav-edit-sidebar' ).show().addClass( 'nav-tab-active' );

					$( '#sidebar_blvd .group' ).hide();

					$( '#sidebar_blvd #edit_sidebar' ).fadeIn();

					// Put user to the top of the page.
					$( 'html, body' ).animate( {
						scrollTop: 0
					}, 'fast' );

				}
			} );

		} );

		/**
		 * Delete a sidebar.
		 *
		 * @since 1.0.0
		 */
		$( '#sidebar_blvd' ).on( 'click', '.row-actions .trash a', function( event ) {

			event.preventDefault();

			var href = $( this ).attr( 'href' ),
				id   = href.replace( '#', '' ),
				ids  = 'posts%5B%5D=' + id;

			admin.sidebars.deleteSidebar( ids, 'click' );

		} );

		/**
		 * Delete a sidebar (in bulk).
		 *
		 * @since 1.0.0
		 */
		$( '#sidebar_blvd' ).on( 'submit', '#manage_current_sidebars', function( event ) {

			event.preventDefault();

			var $form = $( this ),
				value = $form.find( 'select[name="action"]').val(),
				ids   = $form.serialize();

			if ( 'trash' === value ) {
				admin.sidebars.deleteSidebar( ids, 'submit' );
			}

		} );

		/**
		 * Add new sidebar.
		 *
		 * @since 1.0.0
		 */
		$( '.tb-options-wrap #add_new_sidebar' ).on( 'submit', function( event ) {

			event.preventDefault();

			var $form = $( this ),
				data  = $form.serialize(),
				load  = $form.find( '.ajax-loading' ),
				name  = $form.find( 'input[name="options[sidebar_name]"]' ).val(),
				nonce = $form.find( 'input[name="_wpnonce"]' ).val();

			// Tell user they forgot a name.
			if ( ! name ) {

				admin.confirm( l10n.no_name, { 'textOk':'Ok' } );

				return false;

			}

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'themeblvd-add-sidebar',
					security: nonce,
					data: data
				},
				beforeSend: function() {

					load.fadeIn( 'fast' );

				},
				success: function( response ) {

					// Update management table.
					$( '#sidebar_blvd #manage_sidebars .ajax-mitt' ).html( response );

					// Scroll to top of page.
					$( 'body' ).animate( { scrollTop: 0 }, 100, function() {

						// Take us back to the management tab.
						$( '#sidebar_blvd .nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );

						$( '#sidebar_blvd .nav-tab-wrapper a:first' ).addClass( 'nav-tab-active' );

						$( '#sidebar_blvd .group' ).hide();

						$( '#sidebar_blvd .group:first' ).fadeIn();

					});

					// Clear form.
					$( '#sidebar_blvd #add_new_sidebar #sidebar_name' ).val( '' );

					$( '#sidebar_blvd #add_new_sidebar input' ).removeAttr( 'checked' );

					// Hide loader no matter what.
					load.hide();

				}
			} );

		} );

		/**
		 * Save a sidebar, being edited from the Edit tab.
		 *
		 * @since 1.0.0
		 */
		$('.tb-options-wrap').on( 'submit', '#edit_current_sidebar', function( event ) {

			event.preventDefault();

			var $form = $( this ),
				data  = $form.serialize(),
				load  = $form.find( '.ajax-loading'),
				nonce = $form.find( 'input[name="_wpnonce"]').val();

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'themeblvd-save-sidebar',
					security: nonce,
					data: data
				},
				beforeSend: function() {

					load.fadeIn( 'fast' );

				},
				success: function( response ) {

					response = response.split( '[(=>)]' );

					// Make sure all "Widget Area Names" match on current edit page.
					var currentName = $( '#sidebar_blvd #post_title' ).val();

					$( '#sidebar_blvd #edit_sidebar-tab' ).text( l10n.edit_sidebar + ': ' + currentName );

					$( '#sidebar_blvd .postbox h3' ).text( currentName );

					$( '#sidebar_blvd #post_name' ).val( response[0] );

					// Scroll to top of page.
					$( 'body' ).animate( { scrollTop: 0 }, 100, function() {

						/*
						 * Insert update message, fade it in, and then remove it
						 * after a few seconds.
						 */
						$( '#sidebar_blvd #edit_sidebar' ).prepend( response[1] );

						$( '#sidebar_blvd #edit_sidebar .ajax-update' ).fadeIn( 500, function() {

							setTimeout( function() {

								$( '#sidebar_blvd #edit_sidebar .ajax-update' ).fadeOut( 500, function() {

									$( '#sidebar_blvd #edit_sidebar .ajax-update' ).remove();

								} );

						  	}, 1500 );

						} );
					} );

					// Update management table in background.
					$( '#sidebar_blvd #manage_sidebars .ajax-mitt' ).html( response[2] );

					load.fadeOut( 'fast' );
				}
			} );

		} );

	} ); // End $( document ).ready().

} )( jQuery, window.themeblvd );
