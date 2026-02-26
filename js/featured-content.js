/**
 * @fileoverview Applies featured post images as CSS backgrounds in the featured content area.
 */

( function() {

	/**
	 * Returns a sanitized CSS url() string for use in style.backgroundImage.
	 *
	 * Accepts absolute http/https URLs, protocol-relative URLs, and root-relative paths.
	 * Rejects src values containing characters that could escape the url("…") wrapper.
	 * Double-quotes are percent-encoded as a second layer of defence.
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
	 * Applies a featured image as a CSS background on a post's thumbnail container.
	 *
	 * Uses currentSrc for responsive images and falls back to getAttribute('src') rather
	 * than the .src IDL property. Firefox returns an empty string for .src on cross-origin
	 * images (e.g. Jetpack Photon CDN) until decoding completes; getAttribute always
	 * returns the literal attribute value.
	 *
	 * @param {HTMLElement} entryImage - Container element to receive the background-image style.
	 * @param {HTMLImageElement} thumbnail - Image element whose src is used as the background.
	 * @param {HTMLElement} article - Parent article element; receives the 'background-done' class.
	 */
	function applyBackground( entryImage, thumbnail, article ) {
		var src = thumbnail.currentSrc || thumbnail.getAttribute( 'src' );
		var cssUrl = safeCssUrl( src );
		if ( cssUrl ) {
			entryImage.style.backgroundImage = cssUrl;
			article.classList.add( 'background-done' );
		}
	}

	/**
	 * Iterates over all featured content articles and applies background images.
	 *
	 * Skips already-processed articles ('background-done') or those without a post
	 * thumbnail. Uses a per-image load listener rather than window 'load' because
	 * Jetpack Infinite Scroll fires window 'load' synchronously during its own init,
	 * before deferred scripts execute.
	 */
	function init() {
		var featuredContent = document.getElementById( 'featured-content' );
		if ( ! featuredContent ) {
			return;
		}

		featuredContent.querySelectorAll( 'article' ).forEach( function( article ) {
			if ( article.classList.contains( 'background-done' ) ||
				! article.classList.contains( 'has-post-thumbnail' ) ) {
				return;
			}

			var entryImage = article.querySelector( '.post-thumbnail' );
			var thumbnail  = article.querySelector( 'img' );

			if ( ! entryImage || ! thumbnail ) {
				return;
			}

			if ( thumbnail.complete && thumbnail.naturalWidth > 0 ) {
				applyBackground( entryImage, thumbnail, article );
			} else {
				thumbnail.addEventListener( 'load', function() {
					applyBackground( entryImage, thumbnail, article );
				}, { once: true } );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
