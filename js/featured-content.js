( function() {

	/**
	 * Use a featured image as a CSS background image on each featured content article.
	 * Runs after all assets have loaded so image dimensions are available.
	 *
	 * Uses native DOM APIs — no jQuery required.
	 */
	window.addEventListener( 'load', function() {

		const featuredContent = document.getElementById( 'featured-content' );
		if ( ! featuredContent ) {
			return;
		}

		featuredContent.querySelectorAll( 'article' ).forEach( function( article ) {
			if ( article.classList.contains( 'background-done' ) || ! article.classList.contains( 'has-post-thumbnail' ) ) {
				return;
			}

			const entryImage = article.querySelector( '.post-thumbnail' );
			const thumbnail  = article.querySelector( 'img' );

			if ( entryImage && thumbnail ) {
				entryImage.style.backgroundImage = 'url(' + thumbnail.src + ')';
				article.classList.add( 'background-done' );
			}
		} );

	} );

} )();
