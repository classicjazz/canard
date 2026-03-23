/**
 * @fileoverview Applies featured post images as CSS backgrounds in the featured content area.
 */

( function() {

	/**
	 * Resolves canardUtils.safeCssUrl from the frozen utility namespace.
	 *
	 * Prefers the frozen canardUtils.safeCssUrl when available. Falls back to
	 * a no-op that always returns null rather than writing to window, which
	 * would leave an unfrozen, attacker-writable object on the global scope
	 * that a compromised third-party script could hijack.
	 *
	 * @returns {Function} A safeCssUrl implementation, or a null-returning stub.
	 */
	function resolveSafeCssUrl() {
		if (
			window.canardUtils &&
			typeof window.canardUtils.safeCssUrl === 'function'
		) {
			return window.canardUtils.safeCssUrl;
		}

		console.warn( 'Canard featured-content.js: canardUtils.safeCssUrl not available — background images disabled.' );

		/**
		 * Stub returned when canardUtils is unavailable.
		 *
		 * @returns {null} Always returns null to disable background application.
		 */
		return function stubSafeCssUrl() {
			return null;
		};
	}

	/** @type {Function} Sanitizes a raw src string into a safe CSS url() value. */
	const safeCssUrl = resolveSafeCssUrl();

	/**
	 * Applies a featured image as a CSS background on a post's thumbnail container.
	 *
	 * Uses currentSrc for responsive images and falls back to getAttribute('src')
	 * rather than the .src IDL property. Firefox returns an empty string for .src
	 * on cross-origin images (e.g. Jetpack Photon CDN) until decoding completes;
	 * getAttribute always returns the literal attribute value.
	 *
	 * @param {HTMLElement}      entryImage - Container element to receive the background-image style.
	 * @param {HTMLImageElement} thumbnail  - Image element whose src is used as the background.
	 * @param {HTMLElement}      article    - Parent article element; receives the 'background-done' class.
	 * @returns {void}
	 */
	function applyBackground( entryImage, thumbnail, article ) {
		const src    = thumbnail.currentSrc || thumbnail.getAttribute( 'src' );
		const cssUrl = safeCssUrl( src );
		if ( cssUrl ) {
			entryImage.style.backgroundImage = cssUrl;
			article.classList.add( 'background-done' );
		}
	}

	/**
	 * Iterates over all featured content articles and applies background images.
	 *
	 * Skips already-processed articles ('background-done') or those without a
	 * post thumbnail. Uses a per-image load listener rather than window 'load'
	 * because Jetpack Infinite Scroll fires window 'load' synchronously during
	 * its own init, before deferred scripts execute.
	 *
	 * @returns {void}
	 */
	function init() {
		const featuredContent = document.getElementById( 'featured-content' );
		if ( ! featuredContent ) {
			return;
		}

		featuredContent.querySelectorAll( 'article' ).forEach( function( article ) {
			if ( article.classList.contains( 'background-done' ) ||
				! article.classList.contains( 'has-post-thumbnail' ) ) {
				return;
			}

			const entryImage = article.querySelector( '.post-thumbnail' );
			const thumbnail  = article.querySelector( 'img' );

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
