/**
 * @fileoverview Single post page: entry-hero layout, author-info repositioning,
 * and Jetpack Sharedaddy/Related Posts placement.
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
		if (
			window.canardUtils &&
			typeof window.canardUtils.debounce === "function"
		) {
			return window.canardUtils.debounce;
		}

		console.warn(
			"Canard single.js: canardUtils not available — using local debounce fallback.",
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
				timer = setTimeout(() => fn.apply(this, args), wait ?? 500);
			};
		};
	}

	/** @type {Function} Debounce implementation resolved from canardUtils or local fallback. */
	const debounce = resolveDebounce();

	/**
	 * Entry-hero layout: reposition the header outside .site-content-inner
	 * so it spans the full viewport width.
	 *
	 * The wrapper divs and entry-hero class are now output by PHP in
	 * content-single.php, so this block only handles the DOM reposition.
	 * Runs synchronously to avoid a layout flash.
	 */
	if (document.body.classList.contains("has-entry-hero")) {
		const entryHeader = document.querySelector(
			".hentry.has-post-thumbnail .entry-header",
		);
		const siteContentInner = document.querySelector(".site-content-inner");

		if (entryHeader && siteContentInner) {
			siteContentInner.parentNode.insertBefore(entryHeader, siteContentInner);
		}
	}

	/**
	 * Moves .author-info into the sidebar on viewports wider than 959px,
	 * or below .entry-content on narrower viewports.
	 *
	 * The function is idempotent: both branches check whether the element is
	 * already in the target position before moving it, preventing redundant DOM
	 * mutations on every debounced resize tick. If .widget-area is absent on a
	 * wide viewport, the element is left in place rather than stranded, relying
	 * on the .single-has-author-info body class output by PHP for visual layout.
	 *
	 * Uses firstElementChild rather than firstChild to avoid false mismatches
	 * against whitespace text nodes between elements. Uses nextElementSibling
	 * rather than nextSibling for the same reason.
	 *
	 * @returns {void}
	 */
	function authorInfo() {
		const authorInfoEl = document.querySelector(".author-info");
		if (!authorInfoEl) {
			return;
		}

		if (window.innerWidth > 959) {
			const widgetArea = document.querySelector(".widget-area");
			if (widgetArea && widgetArea.firstElementChild !== authorInfoEl) {
				widgetArea.insertBefore(authorInfoEl, widgetArea.firstElementChild);
			}
			// If no widget area exists on wide viewports, leave the element in place
			// rather than stranding it; CSS should handle the visual layout via
			// the .single-has-author-info body class output by PHP.
		} else {
			const entryContent = document.querySelector(".entry-content");
			if (entryContent && entryContent.nextElementSibling !== authorInfoEl) {
				entryContent.after(authorInfoEl);
			}
		}
	}

	// Hoist the debounced wrapper to module scope so only one reference ever
	// exists — prevents duplicate listener accumulation if 'load' fires more
	// than once (e.g. Jetpack Infinite Scroll synthetic load events).
	/**
	 * Debounced version of {@link authorInfo}, safe to attach to the resize event.
	 *
	 * @type {Function}
	 */
	const debouncedAuthorInfo = debounce(authorInfo, 500);

	window.addEventListener("load", authorInfo);
	window.addEventListener("resize", debouncedAuthorInfo);

	/**
	 * Repositions Jetpack sharing/rating widgets and fixes table overflow after load.
	 *
	 * Moves Jetpack Sharedaddy and Related Posts elements into the entry footer,
	 * and applies fixed table layout to any content tables wider than their container.
	 *
	 * @returns {void}
	 */
	window.addEventListener("load", () => {
		// Targets the classic Jetpack sharing / rating module. If block-based
		// sharing is in use, these selectors will not match and are harmless no-ops.
		const entryFooter = document.querySelector(".entry-footer");
		if (entryFooter) {
			document
				.querySelectorAll(
					".sd-sharing-enabled:not(#jp-post-flair), .sd-like.jetpack-likes-widget-wrapper, .sd-rating",
				)
				.forEach((el) => {
					entryFooter.appendChild(el);
				});

			const relatedPosts = document.getElementById("jp-relatedposts");
			if (relatedPosts) {
				const postFlair = document.getElementById("jp-post-flair");
				if (postFlair) {
					entryFooter.after(postFlair);
				}
			}
		}

		// Prevent tables from overflowing their container in entry content.
		document.querySelectorAll(".entry-content table").forEach((table) => {
			if (table.offsetWidth > table.parentElement?.offsetWidth) {
				table.style.tableLayout = "fixed";
			}
		});
	});
})();
