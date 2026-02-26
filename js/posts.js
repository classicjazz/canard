/**
 * @fileoverview Applies background images and normalized heights to image/gallery post thumbnails.
 * Handles initial load, window resize, and Jetpack Infinite Scroll batches.
 */

( function() {

	'use strict';

	// Guard against utils.js failing to load; without this a missing canardUtils
	// causes a TypeError that silences the entire file.
	if ( ! window.canardUtils || typeof window.canardUtils.debounce !== 'function' ) {
		console.warn( 'Canard posts.js: canardUtils not available — debounced resize handler disabled.' );
		window.canardUtils = window.canardUtils || {};
		window.canardUtils.debounce = function( fn ) { return fn; };
	}

	const debounce = window.canardUtils.debounce;

	/**
	 * Returns a sanitized CSS url() string for use in style.backgroundImage.
	 *
	 * Accepts absolute http/https URLs, protocol-relative URLs, and root-relative
	 * paths. Rejects src values containing characters that could escape the url("…")
	 * wrapper. Double-quotes are percent-encoded as a second layer of defence.
	 *
	 * @param {string} src - Raw image src value.
	 * @returns {string|null} Safe CSS url() value, or null if src is rejected.
	 */
	function safeCssUrl( src ) {
		if ( ! src ) {
			return null;
		}
		if ( ! /^(https?:)?\/\/[^\s"'()\\]|^\/[^/]/.test( src ) ) {
			return null;
		}
		return 'url("' + src.replace( /"/g, '%22' ) + '")';
	}

	/**
	 * Applies background-image and uniform height to .post-thumbnail for
	 * format-image and format-gallery posts.
	 *
	 * CSS hides the <img> (opacity:0) and uses background-image on .post-thumbnail
	 * instead, so the background must be set via JS. Heights are normalized to the
	 * tallest article in each batch so all cards are uniform. padding-top is read
	 * from getComputedStyle because the parent stylesheet changes it at the 600px
	 * breakpoint (60px → 90px).
	 *
	 * @param {Element|Document} scope - Root to search within; pass a container to limit work to newly injected nodes.
	 */
	function applyPostStyles( scope ) {

		const entries = [];
		( scope || document ).querySelectorAll( '.hentry' ).forEach( function( entry ) {
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

			entries.push( { entry: entry, postThumbnail: postThumbnail, thumbnail: thumbnail } );
		} );

		if ( ! entries.length ) {
			return;
		}

		/**
		 * Sets background-image on the thumbnail container.
		 * Uses currentSrc (srcset-resolved) when available, falls back to the src attribute.
		 *
		 * @param {{ postThumbnail: HTMLElement, thumbnail: HTMLImageElement }} item
		 */
		function applyBackground( item ) {
			const src    = item.thumbnail.currentSrc || item.thumbnail.getAttribute( 'src' );
			const cssUrl = safeCssUrl( src );
			if ( cssUrl ) {
				item.postThumbnail.style.backgroundImage = cssUrl;
			}
		}

		/**
		 * Measures all articles in the batch, finds the maximum thumbnail height,
		 * and applies it uniformly after a single rAF to avoid layout thrash.
		 */
		function normalizeHeights() {
			requestAnimationFrame( function() {
				let maxThumbnailHeight = 0;
				const measurements = entries.map( function( item ) {
					const articleHeight = item.entry.offsetHeight;
					if ( articleHeight <= 0 ) {
						return null;
					}
					const paddingTop      = parseInt( getComputedStyle( item.entry ).paddingTop, 10 ) || 0;
					const thumbnailHeight = articleHeight - paddingTop;
					if ( thumbnailHeight > maxThumbnailHeight ) {
						maxThumbnailHeight = thumbnailHeight;
					}
					return thumbnailHeight;
				} );

				if ( maxThumbnailHeight <= 0 ) {
					normalizeHeights();
					return;
				}

				entries.forEach( function( item, i ) {
					if ( measurements[ i ] !== null ) {
						item.postThumbnail.style.height = maxThumbnailHeight + 'px';
					}
				} );
			} );
		}

		let pending = 0;

		entries.forEach( function( item ) {
			if ( item.thumbnail.complete && item.thumbnail.naturalWidth > 0 ) {
				// Cache hit — currentSrc already resolved; single call suffices.
				applyBackground( item );
			} else {
				// Apply src attribute immediately as a placeholder, then upgrade to
				// srcset-resolved currentSrc once the load event fires.
				applyBackground( item );
				pending++;
				item.thumbnail.addEventListener( 'load', function() {
					applyBackground( item );
					pending--;
					if ( pending === 0 ) {
						normalizeHeights();
					}
				}, { once: true } );
			}
		} );

		if ( pending === 0 ) {
			normalizeHeights();
		}
	}

	// Script is deferred; readyState is already 'interactive' in practice.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function() {
			applyPostStyles( document );
		} );
	} else {
		applyPostStyles( document );
	}

	// Re-normalize all batches on resize since the tallest article may change across breakpoints.
	window.addEventListener( 'resize', debounce( function() {
		applyPostStyles( document );
	}, 500 ) );

	// Jetpack Infinite Scroll dispatches 'is.post-load' on document.body.
	// (Not 'inf_scr_posts_loaded' on document — confirmed against infinity.min.js, Jetpack 15.x.)
	document.body.addEventListener( 'is.post-load', function() {
		const wraps  = document.querySelectorAll( '.infinite-wrap' );
		const latest = wraps[ wraps.length - 1 ];

		if ( latest ) {
			applyPostStyles( latest );
		} else {
			applyPostStyles( document );
		}
	} );

} )();
