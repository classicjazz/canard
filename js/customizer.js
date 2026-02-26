/**
 * Theme Customizer live preview handlers.
 *
 * Reloads changes to site title, description, and header text color
 * asynchronously without requiring a full page refresh.
 */

( function() {

	// Update site title text in real time.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			const el = document.querySelector( '.site-title a' );
			if ( el ) {
				el.textContent = to;
			}
		} );
	} );

	// Update site description text in real time.
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			const el = document.querySelector( '.site-description' );
			if ( el ) {
				el.textContent = to;
			}
		} );
	} );

	// Toggle site title and description visibility when header text color changes.
	// 'blank' means "hide header text" in the Customizer; any other value is a hex color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			document.querySelectorAll( '.site-title, .site-description' ).forEach( function( el ) {
				if ( 'blank' === to ) {
					el.classList.add( 'screen-reader-text' );
					el.style.color = '';
				} else {
					/*
					 * Security: validate that the incoming value is a well-formed CSS
					 * hex colour before assigning it to style.color. The Customizer
					 * sends values via postMessage; while WordPress sanitises
					 * header_textcolor server-side, an attacker who can manipulate
					 * the postMessage channel (e.g. via a compromised iframe origin)
					 * could otherwise inject CSS expressions or other values.
					 *
					 * Accepts 3-digit (#abc), 4-digit (#abcd), 6-digit (#aabbcc),
					 * and 8-digit (#aabbccdd) hex colours as used by core's color
					 * picker. Any non-matching value is ignored and the colour is
					 * cleared, which is a safe no-op.
					 */
					const isValidHex = /^#[0-9a-fA-F]{3,8}$/.test( to ) &&
						[ 4, 5, 7, 9 ].includes( to.length );

					el.classList.remove( 'screen-reader-text' );
					el.style.color = isValidHex ? to : '';
				}
			} );
		} );
	} );

} )();
