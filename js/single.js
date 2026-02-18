( function( $ ) {

	const debounce = window.canardUtils.debounce;

	/**
	 * Moves the author info block into the sidebar on wide viewports,
	 * or below the entry content on narrow viewports.
	 */
	function authorInfo() {
		const authorInfoEl = $( '.author-info' );
		if ( authorInfoEl.length ) {
			if ( $( window ).width() > 959 ) {
				authorInfoEl.prependTo( '.widget-area' );
			} else {
				authorInfoEl.insertAfter( '.entry-content' );
			}
		}
	}

	$( window ).on( 'load', authorInfo ).on( 'resize', debounce( authorInfo, 500 ) );

	$( window ).on( 'load', function() {

		// Move Jetpack Sharedaddy and Related Posts into the entry footer area.
		// NOTE: These selectors target the classic Jetpack sharing module. If your
		// Jetpack version uses block-based sharing/related posts, these will match
		// nothing and silently no-op. Verify selectors against your Jetpack version
		// and remove this block if the classic module is not in use.
		const sharedaddy  = $( '.sd-sharing-enabled:not(#jp-post-flair), .sd-like.jetpack-likes-widget-wrapper, .sd-rating' );
		const relatedPosts = $( '#jp-relatedposts' );
		if ( sharedaddy.length ) {
			sharedaddy.appendTo( '.entry-footer' );
		}
		if ( relatedPosts.length ) {
			$( '#jp-post-flair' ).insertAfter( '.entry-footer' );
		}

		// Prevent tables from overflowing their container in entry content.
		$( '.entry-content' ).find( 'table' ).each( function() {
			if ( $( this ).width() > $( this ).parent().width() ) {
				$( this ).css( 'table-layout', 'fixed' );
			}
		} );
	} );

} )( jQuery );
