/**
 * @fileoverview Adds 'no-site-branding' to <body> when the site branding container has
 * zero height, indicating both the logo and site title/description are hidden.
 * CSS uses this class to adjust header layout for logo-less configurations.
 */

(() => {
	/**
	 * Returns the site branding container, or null when it is not present.
	 *
	 * @returns {Element|null}
	 */
	function getSiteBranding() {
		return document.querySelector(".site-branding");
	}

	/**
	 * Adds 'no-site-branding' to <body> when the site branding container
	 * renders with zero height, indicating both logo and title/description
	 * are hidden. Deferred to DOMContentLoaded to ensure stylesheets have
	 * been applied and clientHeight reflects the actual rendered state.
	 *
	 * Reading clientHeight synchronously at parse time (before DOMContentLoaded)
	 * can return 0 for an element that will have non-zero height once layout
	 * completes, causing the class to be applied incorrectly.
	 *
	 * A corrective check also runs on window 'load' to handle web font swap
	 * scenarios where clientHeight was transiently 0 at DOMContentLoaded
	 * because the custom font had not yet inflated the branding container.
	 *
	 * @returns {void}
	 */
	function checkSiteBranding() {
		const siteBranding = getSiteBranding();

		if (!siteBranding || siteBranding.clientHeight > 0) {
			return;
		}

		document.body.classList.add("no-site-branding");
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", checkSiteBranding);
	} else {
		checkSiteBranding();
	}

	/**
	 * Corrective branding check that runs after all resources — including web
	 * fonts — have loaded.
	 *
	 * If checkSiteBranding() incorrectly set 'no-site-branding' during the
	 * font-swap period (when clientHeight was transiently 0), this handler
	 * removes the class once the font has inflated the container. If the
	 * container is still zero-height at full load, the class is (re-)applied
	 * to ensure correctness.
	 *
	 * @returns {void}
	 */
	window.addEventListener("load", () => {
		const siteBranding = getSiteBranding();
		if (!siteBranding) {
			return;
		}
		if (siteBranding.clientHeight > 0) {
			// Font has loaded and inflated the container — remove any flag set
			// during the font-swap period at DOMContentLoaded.
			document.body.classList.remove("no-site-branding");
		} else {
			// Still zero at full load — branding is genuinely hidden.
			document.body.classList.add("no-site-branding");
		}
	});
})();
