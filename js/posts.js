/**
 * @fileoverview Applies background images to image/gallery post thumbnails.
 * Handles initial load, window resize, and Jetpack Infinite Scroll batches.
 */

(() => {
	/**
	 * Resolves a safe debounce function.
	 *
	 * Prefers the frozen canardUtils.debounce when available. Falls back to a
	 * local minimal debounce rather than a passthrough identity, preventing
	 * resize-handler storms when the utility script fails to load. Never writes
	 * to window.canardUtils, which would leave an unfrozen, attacker-writable
	 * object on the global scope that a compromised third-party script could
	 * hijack.
	 *
	 * @returns {Function} A debounce implementation.
	 */
	function resolveDebounce() {
		if (typeof window.canardUtils?.debounce === "function") {
			return window.canardUtils.debounce;
		}

		console.warn(
			"Canard posts.js: canardUtils not available — using local debounce fallback.",
		);

		/**
		 * Minimal debounce fallback that never touches window.canardUtils.
		 *
		 * @param {Function} fn   - The function to debounce.
		 * @param {number}   wait - Delay in milliseconds.
		 * @returns {Function} Debounced wrapper function.
		 */
		return function localDebounce(fn, wait) {
			let timer;
			/**
			 * Resets the debounce timer on each invocation and fires fn after the delay.
			 *
			 * @param {...*} args - Arguments forwarded to the debounced function.
			 * @returns {void}
			 */
			return function (...args) {
				clearTimeout(timer);
				timer = setTimeout(() => {
					fn.apply(this, args);
				}, wait || 500);
			};
		};
	}

	/** @type {Function} Debounce implementation resolved from canardUtils or local fallback. */
	const debounce = resolveDebounce();

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
		if (typeof window.canardUtils?.safeCssUrl === "function") {
			return window.canardUtils.safeCssUrl;
		}

		console.warn(
			"Canard posts.js: canardUtils.safeCssUrl not available — background images disabled.",
		);

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
	 * Applies background-image to .post-thumbnail for format-image and
	 * format-gallery posts.
	 *
	 * CSS hides the <img> (opacity:0) and uses background-image on
	 * .post-thumbnail instead, so the background must be set via JS.
	 * .post-thumbnail is already position:absolute with top:0 and bottom:0,
	 * so it fills the card height automatically — no JS height normalization
	 * is needed or correct here.
	 *
	 * @param {Element|Document} scope - Root to search within; pass a container
	 *   to limit work to newly injected nodes.
	 * @returns {void}
	 */
	function applyPostStyles(scope) {
		const entries = [];
		(scope || document).querySelectorAll(".hentry").forEach((entry) => {
			if (
				!entry.classList.contains("has-post-thumbnail") ||
				(!entry.classList.contains("format-image") &&
					!entry.classList.contains("format-gallery")) ||
				entry.closest(".featured-content, #featured-content")
			) {
				return;
			}

			const postThumbnail = entry.querySelector(".post-thumbnail");
			const thumbnail = entry.querySelector("img");

			if (!postThumbnail || !thumbnail) {
				return;
			}

			entries.push({ postThumbnail: postThumbnail, thumbnail: thumbnail });
		});

		if (!entries.length) {
			return;
		}

		/**
		 * Sets background-image on the thumbnail container.
		 *
		 * Uses currentSrc (srcset-resolved) when available, falls back to the
		 * src attribute. Skips the DOM write when the computed value is
		 * unchanged, avoiding a style recalculation on every resize tick for
		 * already-processed thumbnails.
		 *
		 * @param {{ postThumbnail: HTMLElement, thumbnail: HTMLImageElement }} item - Entry object containing the thumbnail container and image.
		 * @returns {void}
		 */
		function applyBackground(item) {
			const src =
				item.thumbnail.currentSrc || item.thumbnail.getAttribute("src");
			const cssUrl = safeCssUrl(src);
			if (!cssUrl) {
				return;
			}
			// Guard: skip write when value is already correct to avoid a
			// redundant style recalculation — especially important on resize
			// where this runs across all loaded posts after Infinite Scroll.
			if (item.postThumbnail.style.backgroundImage !== cssUrl) {
				item.postThumbnail.style.backgroundImage = cssUrl;
			}
		}

		entries.forEach((item) => {
			if (item.thumbnail.complete && item.thumbnail.naturalWidth > 0) {
				// Cache hit — currentSrc already resolved.
				applyBackground(item);
			} else {
				// Apply src attribute immediately as a placeholder, then upgrade
				// to srcset-resolved currentSrc once the load event fires.
				applyBackground(item);
				item.thumbnail.addEventListener(
					"load",
					() => {
						applyBackground(item);
					},
					{ once: true },
				);
			}
		});
	}

	// Script is deferred; readyState is already 'interactive' in practice.
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", () => {
			applyPostStyles(document);
		});
	} else {
		applyPostStyles(document);
	}

	// Hoist the debounced wrapper to module scope so only one reference ever
	// exists — prevents duplicate listener accumulation if 'load' fires more
	// than once (e.g. Jetpack Infinite Scroll synthetic load events).
	const debouncedApplyPostStyles = debounce(() => {
		applyPostStyles(document);
	}, 500);

	// Re-apply backgrounds on resize in case srcset resolves to a different src.
	window.addEventListener("resize", debouncedApplyPostStyles);

	// Jetpack Infinite Scroll dispatches 'is.post-load' on document.body.
	// (Not 'inf_scr_posts_loaded' on document — confirmed against infinity.min.js, Jetpack 15.x.)
	/**
	 * Handles Jetpack Infinite Scroll post-load events, applying backgrounds to newly injected posts.
	 *
	 * @param {CustomEvent} event - The `is.post-load` event, with new nodes in event.detail.nodes.
	 * @returns {void}
	 */
	document.body.addEventListener("is.post-load", (event) => {
		// Jetpack passes the new nodes in event.detail.nodes on Jetpack >= 13.
		// Fall back to .infinite-wrap scanning for older versions.
		const newNodes = event?.detail?.nodes;

		if (newNodes && newNodes.length) {
			// Process each newly injected element node directly to avoid
			// re-scanning the entire document.
			newNodes.forEach((node) => {
				if (node.nodeType === Node.ELEMENT_NODE) {
					applyPostStyles(node);
				}
			});
			return;
		}

		const wraps = document.querySelectorAll(".infinite-wrap");
		const latest = wraps.at(-1);

		if (latest) {
			applyPostStyles(latest);
		} else {
			// Genuine fallback — log so this edge case is visible in development.
			console.warn(
				"Canard posts.js: is.post-load fired but no .infinite-wrap found — falling back to full document scan.",
			);
			applyPostStyles(document);
		}
	});
})();
