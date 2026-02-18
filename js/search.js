( function() {

	/**
	 * Adds and removes a hover/focus class on the search form when the submit
	 * button is hovered or receives keyboard focus.
	 *
	 * Uses native DOM events — no jQuery required.
	 */
	window.addEventListener( 'load', function() {
		const searchSubmit = document.querySelector( '.search-submit' );
		if ( ! searchSubmit ) {
			return;
		}

		function searchAddClass() {
			const form = this.closest( '.search-form' );
			if ( form ) {
				form.classList.add( 'hover' );
			}
		}

		function searchRemoveClass() {
			const form = this.closest( '.search-form' );
			if ( form ) {
				form.classList.remove( 'hover' );
			}
		}

		searchSubmit.addEventListener( 'mouseenter', searchAddClass );
		searchSubmit.addEventListener( 'mouseleave', searchRemoveClass );
		searchSubmit.addEventListener( 'focus',      searchAddClass );
		searchSubmit.addEventListener( 'blur',       searchRemoveClass );
	} );

} )();

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

	button.addEventListener( 'click', function() {
		const isToggled = container.classList.contains( 'toggled' );
		document.body.classList.toggle( 'search-toggled', ! isToggled );
		container.classList.toggle( 'toggled', ! isToggled );
		button.setAttribute( 'aria-expanded', isToggled ? 'false' : 'true' );
		form.setAttribute( 'aria-expanded', isToggled ? 'false' : 'true' );
	} );

} )();
