/**
 * @fileoverview Single post page: entry-hero layout, author-info repositioning,
 * and Jetpack Sharedaddy/Related Posts placement.
 */

( function() {

	// Guard against utils.js failing to load; provides a no-op passthrough so
	// entry-hero layout and author-info repositioning continue without debouncing.
	if ( ! window.canardUtils || typeof window.canardUtils.debounce !== 'function' ) {
		console.warn( 'Canard single.js: canardUtils not available — debounced resize handler disabled.' );
		window.canardUtils = window.canardUtils || {};
		window.canardUtils.debounce = function( fn ) { return fn; };
	}

	const debounce = window.canardUtils.debounce;

	/**
	 * Entry-hero layout.
	 *
	 * Runs synchronously (no DOMContentLoaded wrapper) to avoid a layout flash.
	 * The 'has-entry-hero' body class is set server-side in entry-script.php.
	 * If you move this to a load/DOMContentLoaded callback, test for FOUC.
	 */
	if ( document.body.classList.contains( 'has-entry-hero' ) ) {
		const entryHeader      = document.querySelector( '.hentry.has-post-thumbnail .entry-header' );
		const siteContentInner = document.querySelector( '.site-content-inner' );

		if ( entryHeader && siteContentInner ) {
			const targets = entryHeader.querySelectorAll( '.entry-title, .entry-meta' );
			if ( targets.length ) {
				const inner   = document.createElement( 'div' );
				const wrapper = document.createElement( 'div' );
				inner.className   = 'entry-header-inner';
				wrapper.className = 'entry-header-wrapper';

				targets.forEach( function( el ) {
					inner.appendChild( el );
				} );
				wrapper.appendChild( inner );
				entryHeader.appendChild( wrapper );
			}

			siteContentInner.parentNode.insertBefore( entryHeader, siteContentInner );
			entryHeader.classList.add( 'entry-hero' );
		}
	}

	/**
	 * Moves .author-info into the sidebar on viewports wider than 959px,
	 * or below .entry-content on narrower viewports.
	 *
	 * Uses nextElementSibling rather than nextSibling to avoid false mismatches
	 * against whitespace text nodes between elements, which would cause a
	 * redundant DOM move on every debounced resize tick.
	 */
	function authorInfo() {
		const authorInfoEl = document.querySelector( '.author-info' );
		if ( ! authorInfoEl ) {
			return;
		}
		if ( window.innerWidth > 959 ) {
			const widgetArea = document.querySelector( '.widget-area' );
			if ( widgetArea ) {
				widgetArea.insertBefore( authorInfoEl, widgetArea.firstChild );
			}
		} else {
			const entryContent = document.querySelector( '.entry-content' );
			if ( entryContent && entryContent.nextElementSibling !== authorInfoEl ) {
				entryContent.after( authorInfoEl );
			}
		}
	}

	window.addEventListener( 'load', authorInfo );
	window.addEventListener( 'resize', debounce( authorInfo, 500 ) );

	window.addEventListener( 'load', function() {

		// Targets the classic Jetpack sharing / rating module. If block-based
		// sharing is in use, these selectors will not match and are harmless no-ops.
		const entryFooter = document.querySelector( '.entry-footer' );
		if ( entryFooter ) {
			document.querySelectorAll( '.sd-sharing-enabled:not(#jp-post-flair), .sd-like.jetpack-likes-widget-wrapper, .sd-rating' ).forEach( function( el ) {
				entryFooter.appendChild( el );
			} );

			const relatedPosts = document.getElementById( 'jp-relatedposts' );
			if ( relatedPosts ) {
				const postFlair = document.getElementById( 'jp-post-flair' );
				if ( postFlair ) {
					entryFooter.after( postFlair );
				}
			}
		}

		// Prevent tables from overflowing their container in entry content.
		document.querySelectorAll( '.entry-content table' ).forEach( function( table ) {
			if ( table.offsetWidth > table.parentElement.offsetWidth ) {
				table.style.tableLayout = 'fixed';
			}
		} );

	} );

} )();
