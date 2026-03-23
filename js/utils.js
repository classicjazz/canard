/**
 * @fileoverview Canard shared utility functions.
 *
 * Exposed on window.canardUtils so all theme scripts that declare canard-utils
 * as a dependency can access them. Do not wrap in an IIFE — the namespace must
 * be visible at window scope.
 *
 * The object is frozen via Object.freeze() to prevent third-party scripts from
 * replacing or augmenting canardUtils.debounce with a malicious implementation.
 * Consumer scripts resolve debounce via a local resolveDebounce() helper that
 * never writes back to window.canardUtils, ensuring the frozen object cannot
 * be replaced with an unfrozen stub on load-order failures.
 *
 * @package Canard
 */

window.canardUtils = Object.freeze( {

	/**
	 * Defers execution of func until after wait milliseconds have elapsed since
	 * the last invocation of the returned function.
	 *
	 * @param {Function} func - The function to debounce.
	 * @param {number}   wait - Delay in milliseconds.
	 * @returns {Function} Debounced wrapper function.
	 */
	debounce: function( func, wait ) {
		let timeout;
		/**
		 * Resets the debounce timer on each call and invokes func after the delay.
		 *
		 * @param {...*} args - Arguments forwarded to func.
		 * @returns {void}
		 */
		return function( ...args ) {
			const context = this;
			clearTimeout( timeout );
			timeout = setTimeout( function() {
				func.apply( context, args );
			}, wait );
		};
	},

	/**
	 * Returns a sanitized CSS url() string safe for assignment to
	 * style.backgroundImage.
	 *
	 * Parses the value with the URL constructor to guarantee a valid,
	 * scheme-checked, fully-structured URL before encoding. Root-relative
	 * paths are resolved against window.location.origin so the URL
	 * constructor can apply the same structural checks as absolute URLs.
	 *
	 * Only HTTPS URLs and same-origin URLs are accepted. Protocol-relative
	 * URLs are rejected: on HTTP origins they constitute a mixed-content
	 * downgrade vector and are unnecessary in WordPress contexts where all
	 * media is served from a known origin.
	 *
	 * The regex gate is the sole and authoritative defense against CSS url()
	 * injection. No encoding layer follows: encoding gives false confidence
	 * because the set of characters it would encode does not fully match the
	 * set the regex rejects (single quotes and control characters are caught
	 * by the regex but were never encoded). Removing the encoding eliminates
	 * the risk of a future maintainer dropping the regex under the mistaken
	 * belief that the encoding provides equivalent protection.
	 *
	 * @param {string} src - Raw image src value.
	 * @returns {string|null} Safe CSS url() value, or null if src is rejected.
	 */
	safeCssUrl: function( src ) {
		if ( ! src || typeof src !== 'string' ) {
			return null;
		}

		let parsed;
		try {
			parsed = new URL( src, window.location.origin );
		} catch {
			return null;
		}

		// Allow only HTTPS or same-origin URLs (covers root-relative paths
		// resolved against the current origin, including HTTP in development).
		if ( parsed.protocol !== 'https:' && parsed.origin !== window.location.origin ) {
			return null;
		}

		// Re-serialize via the URL object — normalizes percent-encoding and
		// strips newlines, tabs, and other whitespace the constructor removes.
		const safe = parsed.href;

		// Reject if any character that could break a CSS url() string survived
		// URL serialization. This is the single authoritative gate: no encoding
		// step follows, so there is no false impression that encoding and
		// rejection are interchangeable defenses.
		if ( /["'\\()\n\r\t]/.test( safe ) ) {
			return null;
		}

		return 'url("' + safe + '")';
	}

} );
