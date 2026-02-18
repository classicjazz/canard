( function( $ ) {

	/**
	 * Use a featured image as a CSS background image on each featured content article.
	 * Runs after all assets have loaded so image dimensions are available.
	 */
	$( window ).on( 'load', function() {

		const featuredContent = $( '#featured-content' );
		if ( ! featuredContent.length ) {
			return;
		}

		featuredContent.find( 'article' ).each( function() {
			if ( ! $( this ).hasClass( 'background-done' ) && $( this ).hasClass( 'has-post-thumbnail' ) ) {
				const entryImage = $( this ).find( '.post-thumbnail' );
				const thumbnail  = $( this ).find( 'img' );
				entryImage.css( 'background-image', 'url(' + thumbnail.attr( 'src' ) + ')' );
				$( this ).addClass( 'background-done' );
			}
		} );

	} );

} )( jQuery );
