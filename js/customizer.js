/**
 * Theme Customizer live preview handlers.
 *
 * Reloads changes to site title, description, and header text color
 * asynchronously without requiring a full page refresh.
 */

( function( $ ) {

	// Update site title text in real time.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );

	// Update site description text in real time.
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );

	// Toggle site title and description visibility when header text color changes.
	// 'blank' means "hide header text" in the Customizer; any other value is a hex color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			if ( 'blank' === to ) {
				$( '.site-title, .site-description' )
					.addClass( 'screen-reader-text' )
					.css( 'color', '' );
			} else {
				$( '.site-title, .site-description' )
					.removeClass( 'screen-reader-text' )
					.css( 'color', to );
			}
		} );
	} );

} )( jQuery );
