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
					el.classList.remove( 'screen-reader-text' );
					el.style.color = to;
				}
			} );
		} );
	} );

} )();
