( function() {

	'use strict';

	const debounce = window.canardUtils.debounce;

	/**
	 * Sets background-image and height on .post-thumbnail for format-image and
	 * format-gallery posts in the main content area.
	 *
	 * The CSS positions .post-thumbnail absolutely with background-size:cover and
	 * sets the <img> to opacity:0, so the background-image is what renders visually.
	 * The height must be set explicitly in JS because the absolutely-positioned
	 * thumbnail sits outside normal flow and won't inherit the article's height
	 * on its own.
	 *
	 * @param {Element|Document} scope  Root to search within. Pass a specific
	 *   container to limit work to newly injected nodes (infinite scroll).
	 */
	function applyPostStyles( scope ) {
		( scope || document ).querySelectorAll( '.site-main .hentry' ).forEach( function( entry ) {
			if (
				! entry.classList.contains( 'has-post-thumbnail' ) ||
				( ! entry.classList.contains( 'format-image' ) && ! entry.classList.contains( 'format-gallery' ) ) ||
				( entry.parentElement && entry.parentElement.classList.contains( 'featured-content' ) )
			) {
				return;
			}

			const postThumbnail = entry.querySelector( '.post-thumbnail' );
			const thumbnail     = entry.querySelector( 'img' );

			if ( ! postThumbnail || ! thumbnail ) {
				return;
			}

			// Set background-image from the resolved src. currentSrc reflects the
			// chosen srcset candidate; fall back to src if not yet resolved.
			const src = thumbnail.currentSrc || thumbnail.getAttribute( 'src' );
			if ( src ) {
				postThumbnail.style.backgroundImage = 'url(' + src + ')';
			}

			// Set height. For freshly injected posts the article may not yet have
			// layout (offsetHeight === 0); defer one rAF so the browser has
			// performed at least one layout pass before we read the dimension.
			function setHeight() {
				const articleHeight = entry.offsetHeight;
				if ( articleHeight > 0 ) {
					const marginSize = window.innerWidth > 599 ? 60 : 30;
					postThumbnail.style.height = ( articleHeight - marginSize ) + 'px';
				} else {
					// Still no layout — try again on the next frame.
					requestAnimationFrame( setHeight );
				}
			}

			requestAnimationFrame( setHeight );
		} );
	}

	// Initial run after all assets (including images) have loaded so that
	// currentSrc is resolved and offsetHeight is stable.
	window.addEventListener( 'load', function() {
		applyPostStyles( document );
	} );

	// Re-calculate heights on resize (e.g. orientation change, window resize).
	window.addEventListener( 'resize', debounce( function() {
		applyPostStyles( document );
	}, 500 ) );

	// Jetpack Infinite Scroll — modern event name (Jetpack 9.2+, including 15.x).
	// Fired on `document` after each batch of new posts is injected into the DOM.
	document.addEventListener( 'inf_scr_posts_loaded', function() {
		// New posts have just been inserted but may not yet have a layout height.
		// applyPostStyles defers the height read via rAF internally, so passing
		// document here is safe — already-processed posts get a no-op background
		// re-set (same value) and a fresh height calculation.
		applyPostStyles( document );
	} );

} )();
