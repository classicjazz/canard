/**
 * @fileoverview Theme Customizer live preview handlers.
 *
 * Reloads changes to site title, description, and header text color
 * asynchronously without a full page refresh.
 */

( function() {

	// Guard against the script loading outside the Customizer preview iframe
	// (e.g. a misconfigured wp_enqueue_script condition or a bundler that
	// concatenates all theme scripts). Without this check, the bare
	// wp.customize() call throws ReferenceError: wp is not defined and kills
	// all subsequent JavaScript on the page.
	if ( typeof wp === 'undefined' || typeof wp.customize !== 'function' ) {
		return;
	}

	/**
	 * Updates the site title anchor text when the blogname setting changes.
	 *
	 * @param {wp.customize.Value} value - The Customizer value object for 'blogname'.
	 * @returns {void}
	 */
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			const el = document.querySelector( '.site-title a' );
			if ( el ) {
				el.textContent = to;
			}
		} );
	} );

	/**
	 * Updates the site description text when the blogdescription setting changes.
	 *
	 * @param {wp.customize.Value} value - The Customizer value object for 'blogdescription'.
	 * @returns {void}
	 */
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			const el = document.querySelector( '.site-description' );
			if ( el ) {
				el.textContent = to;
			}
		} );
	} );

	/**
	 * Updates the header text color when the header_textcolor setting changes.
	 *
	 * Accepts the special value 'blank' (meaning "hide header text") or a
	 * validated CSS hex color string. Non-hex, non-blank values are silently
	 * rejected to prevent arbitrary CSS tokens from reaching the DOM.
	 *
	 * @param {wp.customize.Value} value - The Customizer value object for 'header_textcolor'.
	 * @returns {void}
	 */
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			document.querySelectorAll( '.site-title, .site-description' ).forEach( function( el ) {
				if ( 'blank' === to ) {
					el.classList.add( 'screen-reader-text' );
					el.style.color = '';
				} else {
					/*
					 * Validate that the value is a well-formed CSS hex color before
					 * assigning to style.color.
					 *
					 * Threat model: the Customizer sends values via postMessage from
					 * a preview iframe. Although modern browsers do not evaluate CSS
					 * expressions in style assignments, a compromised or spoofed iframe
					 * could send arbitrary strings. Restricting to hex prevents any
					 * non-color token from reaching the DOM.
					 *
					 * If you extend this to accept rgb(), hsl(), or named colors,
					 * validate each format explicitly; do NOT pass arbitrary strings
					 * to style.color, as CSS custom-property syntax
					 * (e.g. "var(--x, url(...))") can leak data via CSS paint
					 * worklets in Chromium-based browsers.
					 *
					 * Accepts: #rgb, #rgba, #rrggbb, #rrggbbaa.
					 *
					 * When isValidHex is false, style.color is set to '' (no-op
					 * clear). The type — not the value — is logged to avoid
					 * leaking attacker-supplied payload strings to the console
					 * where browser extensions could read them.
					 */
					const isValidHex = /^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test( to );

					if ( ! isValidHex && typeof console !== 'undefined' ) {
						console.warn( 'Canard customizer.js: header_textcolor value rejected (not a hex color):', typeof to );
					}

					el.classList.remove( 'screen-reader-text' );
					el.style.color = isValidHex ? to : '';
				}
			} );
		} );
	} );

} )();
