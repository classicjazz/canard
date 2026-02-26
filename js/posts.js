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
	 * WHY WE NO LONGER USE window 'load':
	 * Every image on the page uses loading="lazy". On image-heavy archive pages the
	 * browser keeps the window load event open until all lazy images that entered the
	 * viewport during initial layout have finished downloading. With 15+ posts per
	 * page this can delay load by several seconds, causing format-image / format-
	 * gallery thumbnails to render with no background and wrong heights until load
	 * finally fires. We now run on DOMContentLoaded and resolve background and
	 * height independently.
	 *
	 * WHY BACKGROUND IS SET IMMEDIATELY (NOT GATED ON img LOAD):
	 * Previously both applyBackground() and setHeight() were tied to the img 'load'
	 * event. For lazy images that are not in the initial viewport, this event never
	 * fires until the user scrolls the image into view — which could be minutes
	 * after page load, leaving a solid-black box visible the whole time. The
	 * background-image can be set immediately using the src attribute as a fallback
	 * (the srcset-chosen currentSrc is not available until after load, but the src
	 * is always present in the markup). This eliminates the persistent black-box
	 * state for all posts regardless of viewport position.
	 *
	 * WHY HEIGHT IS STILL SET ON IMAGE LOAD:
	 * At DOMContentLoaded, images have not loaded. The browser reserves space for
	 * them based on their explicit width/height attributes (aspect-ratio
	 * preservation). Even though .post-thumbnail is position:absolute (out of flow
	 * from the article), reading offsetHeight during a transitional layout state
	 * can return an inflated value. Deferring setHeight() until the img fires its
	 * 'load' event (or to a rAF for cache hits) guarantees a stable, fully-painted
	 * layout — at which point offsetHeight is correct.
	 *
	 * WHY WE READ paddingTop FROM getComputedStyle, NOT A HARDCODED CONSTANT:
	 * The parent stylesheet changes the article's padding-top at the 600px
	 * breakpoint: below 600px it is 60px; at 600px and above it becomes 90px.
	 * A hardcoded marginSize of 60 was always 30px wrong on desktop. Reading
	 * the live computed value keeps JS in sync with CSS automatically, even if
	 * the breakpoint values change in the future.
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

			/**
			 * Applies the resolved image URL as the background-image.
			 *
			 * Called immediately — no need to wait for the image to load.
			 * currentSrc reflects the srcset candidate chosen by the browser and
			 * is only populated after the image has loaded; we fall back to the
			 * src attribute, which is always present in the markup.
			 *
			 * If the image later loads a higher-resolution srcset candidate, we
			 * upgrade to currentSrc via the 'load' listener that also handles
			 * setHeight(), so the best available source is always used once known.
			 */
			function applyBackground() {
				const src = thumbnail.currentSrc || thumbnail.getAttribute( 'src' );
				if ( src ) {
					postThumbnail.style.backgroundImage = 'url(' + src + ')';
				}
			}

			/**
			 * Sets the explicit pixel height on .post-thumbnail.
			 *
			 * .post-thumbnail is position:absolute anchored to bottom:0. Its height
			 * should fill the article from the bottom up to where the content begins,
			 * i.e. entry.offsetHeight minus the article's padding-top. We read
			 * padding-top from getComputedStyle so the value stays in sync with CSS
			 * media queries automatically (60px below 600px, 90px above).
			 *
			 * The article may not yet have its final layout height when this is
			 * called. Defer one rAF to let the browser complete at least one
			 * layout pass before reading offsetHeight.
			 */
			function setHeight() {
				requestAnimationFrame( function() {
					const articleHeight = entry.offsetHeight;
					if ( articleHeight > 0 ) {
						const paddingTop = parseInt( getComputedStyle( entry ).paddingTop, 10 ) || 0;
						postThumbnail.style.height = ( articleHeight - paddingTop ) + 'px';
					} else {
						// No layout yet — defer again.
						setHeight();
					}
				} );
			}

			// Apply background-image immediately from src attribute.
			// This prevents the solid-black-box state for posts outside the
			// initial viewport, where the img 'load' event would not fire
			// until the user scrolled down.
			applyBackground();

			if ( thumbnail.complete && thumbnail.naturalWidth > 0 ) {
				// Image already decoded (browser cache hit). Height layout is
				// stable since this path only runs after full parse; upgrade
				// background to the resolved currentSrc and set height now.
				applyBackground(); // upgrade to currentSrc (no-op if same URL)
				setHeight();
			} else {
				// Image not yet loaded. Register a single 'load' listener that:
				// 1. upgrades the background to the resolved currentSrc (the
				//    srcset-chosen candidate, typically higher-resolution than src), and
				// 2. recalculates height now that the img has its final decoded
				//    dimensions and the surrounding layout has fully settled.
				thumbnail.addEventListener( 'load', function() {
					applyBackground();
					setHeight();
				}, { once: true } );
			}
		} );
	}

	/**
	 * Initial run.
	 *
	 * DOMContentLoaded fires as soon as HTML parsing completes, far earlier than
	 * window 'load'. Because this script is enqueued with strategy:defer, it
	 * executes after parsing completes, so document.readyState is already
	 * 'interactive'. The 'loading' guard is a safety net only.
	 */
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function() {
			applyPostStyles( document );
		} );
	} else {
		applyPostStyles( document );
	}

	// Re-calculate heights on resize (e.g. orientation change, window resize).
	window.addEventListener( 'resize', debounce( function() {
		applyPostStyles( document );
	}, 500 ) );

	/**
	 * Jetpack Infinite Scroll — modern event name (Jetpack 9.2+, including 15.x).
	 * Fired on `document` after each batch of new posts is injected into the DOM.
	 *
	 * Scope work to the most recently added .infinite-wrap so we only touch newly
	 * injected posts. Their lazy images haven't loaded yet; applyBackground() sets
	 * the src-fallback background immediately; the per-image 'load' listener
	 * upgrades to currentSrc and sets height once the browser fetches each image.
	 */
	document.addEventListener( 'inf_scr_posts_loaded', function() {
		const wraps  = document.querySelectorAll( '.infinite-wrap' );
		const latest = wraps[ wraps.length - 1 ];

		if ( latest ) {
			applyPostStyles( latest );
		} else {
			// Fallback: no .infinite-wrap found (unexpected markup), process all.
			applyPostStyles( document );
		}
	} );

} )();
