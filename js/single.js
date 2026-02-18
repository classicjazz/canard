( function() {

	const debounce = window.canardUtils.debounce;

	/**
	 * Entry-hero layout.
	 *
	 * When the server has determined that the entry-hero layout applies it adds
	 * the class 'has-entry-hero' to <body> (via entry-script.php / body_class
	 * filter). This block reads that class and performs the DOM rearrangement
	 * that was previously an inline <script> in entry-script.php.
	 *
	 * Runs synchronously (no DOMContentLoaded wrapper) to avoid layout flash.
	 * The has-entry-hero body class is set server-side in entry-script.php.
	 * If you move this to a load/DOMContentLoaded callback, test for FOUC.
	 */
	if ( document.body.classList.contains( 'has-entry-hero' ) ) {
		const entryHeader = document.querySelector( '.hentry.has-post-thumbnail .entry-header' );
		const siteContentInner = document.querySelector( '.site-content-inner' );

		if ( entryHeader && siteContentInner ) {
			// Wrap .entry-title and .entry-meta together inside two nested divs.
			const targets = entryHeader.querySelectorAll( '.entry-title, .entry-meta' );
			if ( targets.length ) {
				const inner   = document.createElement( 'div' );
				const wrapper = document.createElement( 'div' );
				inner.className   = 'entry-header-inner';
				wrapper.className = 'entry-header-wrapper';

				// Move matched elements into inner, then nest inner → wrapper.
				targets.forEach( function( el ) {
					inner.appendChild( el );
				} );
				wrapper.appendChild( inner );
				entryHeader.appendChild( wrapper );
			}

			// Hoist the entry header before .site-content-inner and mark it.
			siteContentInner.parentNode.insertBefore( entryHeader, siteContentInner );
			entryHeader.classList.add( 'entry-hero' );
		}
	}

	/**
	 * Moves the author info block into the sidebar on wide viewports,
	 * or below the entry content on narrow viewports.
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
			if ( entryContent && entryContent.nextSibling !== authorInfoEl ) {
				entryContent.after( authorInfoEl );
			}
		}
	}

	window.addEventListener( 'load', authorInfo );
	window.addEventListener( 'resize', debounce( authorInfo, 500 ) );

	window.addEventListener( 'load', function() {

		// Move Jetpack Sharedaddy and Related Posts into the entry footer area.
		// NOTE: These selectors target the classic Jetpack sharing module. If your
		// Jetpack version uses block-based sharing/related posts, these will match
		// nothing and silently no-op. Verify selectors against your Jetpack version
		// and remove this block if the classic module is not in use.
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
