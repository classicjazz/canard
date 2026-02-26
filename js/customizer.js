/**
 * @fileoverview Theme Customizer live preview handlers.
 *
 * Reloads changes to site title, description, and header text color
 * asynchronously without a full page refresh.
 */

( function() {

	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			const el = document.querySelector( '.site-title a' );
			if ( el ) {
				el.textContent = to;
			}
		} );
	} );

	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			const el = document.querySelector( '.site-description' );
			if ( el ) {
				el.textContent = to;
			}
		} );
	} );

	// 'blank' means "hide header text" in the Customizer; any other value is a hex color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			document.querySelectorAll( '.site-title, .site-description' ).forEach( function( el ) {
				if ( 'blank' === to ) {
					el.classList.add( 'screen-reader-text' );
					el.style.color = '';
				} else {
					/*
					 * Validate the incoming value is a well-formed CSS hex color before
					 * assigning to style.color. The Customizer sends values via postMessage;
					 * a compromised iframe origin could otherwise inject CSS expressions.
					 *
					 * Accepts: #rgb, #rgba, #rrggbb, #rrggbbaa.
					 * Any non-matching value is ignored and color is cleared (safe no-op).
					 */
					const isValidHex = /^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test( to );

					el.classList.remove( 'screen-reader-text' );
					el.style.color = isValidHex ? to : '';
				}
			} );
		} );
	} );

} )();
