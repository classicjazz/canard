( function( $ ) {

	const debounce = window.canardUtils.debounce;

	/**
	 * Sets background images and heights on post thumbnail elements for image
	 * and gallery format posts on archive/home pages.
	 */
	function postsStyles() {
		let marginSize = 30;
		if ( $( window ).width() > 599 ) {
			marginSize = 60;
		}
		$( '.site-main .hentry' ).each( function() {
			if ( $( this ).hasClass( 'has-post-thumbnail' ) && ( $( this ).hasClass( 'format-image' ) || $( this ).hasClass( 'format-gallery' ) ) && ! $( this ).parent().hasClass( 'featured-content' ) ) {
				const postThumbnail = $( this ).find( '.post-thumbnail' );
				const thumbnail     = $( this ).find( 'img' );
				postThumbnail.css( {
					'background-image': 'url(' + thumbnail.attr( 'src' ) + ')',
					'height': $( this ).outerHeight() - marginSize
				} );
			}
		} );
	}

	$( window ).on( 'load', postsStyles ).on( 'resize', debounce( postsStyles, 500 ) );

	// Re-apply styles after Jetpack Infinite Scroll loads new posts.
	$( document ).on( 'post-load', postsStyles );

} )( jQuery );
