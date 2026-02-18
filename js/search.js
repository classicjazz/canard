( function( $ ) {

	/**
	 * Adds and removes a hover/focus class on the search form when the
	 * submit button is hovered or receives keyboard focus.
	 */
	$( window ).on( 'load', function() {

		function searchAddClass() {
			$( this ).closest( '.search-form' ).addClass( 'hover' );
		}
		function searchRemoveClass() {
			$( this ).closest( '.search-form' ).removeClass( 'hover' );
		}
		const searchSubmit = $( '.search-submit' );
		searchSubmit.hover( searchAddClass, searchRemoveClass );
		searchSubmit.focusin( searchAddClass );
		searchSubmit.focusout( searchRemoveClass );

	} );

} )( jQuery );

( function() {

	/**
	 * Toggles the header search form open and closed when the search button
	 * is clicked, and keeps aria-expanded attributes in sync.
	 */

	const container = document.getElementById( 'search-header' );
	if ( ! container ) {
		return;
	}

	const button = container.getElementsByTagName( 'button' )[0];
	if ( ! button ) {
		return;
	}

	const form = container.getElementsByTagName( 'form' )[0];
	if ( ! form ) {
		button.style.display = 'none';
		return;
	}
	form.setAttribute( 'aria-expanded', 'false' );

	button.onclick = function() {
		if ( -1 !== container.className.indexOf( 'toggled' ) ) {
			document.body.className = document.body.className.replace( ' search-toggled', '' );
			container.className     = container.className.replace( ' toggled', '' );
			button.setAttribute( 'aria-expanded', 'false' );
			form.setAttribute( 'aria-expanded', 'false' );
		} else {
			document.body.className += ' search-toggled';
			container.className     += ' toggled';
			button.setAttribute( 'aria-expanded', 'true' );
			form.setAttribute( 'aria-expanded', 'true' );
		}
	};

} )();
