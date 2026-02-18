( function() {

	const debounce = window.canardUtils.debounce;

	/**
	 * Sets background images and heights on post thumbnail elements for image
	 * and gallery format posts on archive/home pages.
	 */
	function postsStyles() {
		const marginSize = window.innerWidth > 599 ? 60 : 30;

		document.querySelectorAll( '.site-main .hentry' ).forEach( function( entry ) {
			if (
				entry.classList.contains( 'has-post-thumbnail' ) &&
				( entry.classList.contains( 'format-image' ) || entry.classList.contains( 'format-gallery' ) ) &&
				! entry.parentElement.classList.contains( 'featured-content' )
			) {
				const postThumbnail = entry.querySelector( '.post-thumbnail' );
				const thumbnail     = entry.querySelector( 'img' );
				if ( postThumbnail && thumbnail ) {
					postThumbnail.style.backgroundImage = 'url(' + thumbnail.src + ')';
					postThumbnail.style.height          = ( entry.offsetHeight - marginSize ) + 'px';
				}
			}
		} );
	}

	window.addEventListener( 'load', postsStyles );
	window.addEventListener( 'resize', debounce( postsStyles, 500 ) );

	// Re-apply styles after Jetpack Infinite Scroll loads new posts.
	document.addEventListener( 'post-load', postsStyles );

} )();
