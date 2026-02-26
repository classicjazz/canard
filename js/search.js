/**
 * @fileoverview Search form hover/focus styling and header search toggle.
 */

( function() {

	/**
	 * Adds and removes a hover/focus class on each search form when its submit
	 * button is hovered or receives keyboard focus.
	 *
	 * Uses querySelectorAll so every .search-submit on the page (e.g. both a
	 * header search and a widget-area search) receives handlers — querySelector
	 * would only hook the first match, leaving subsequent forms inconsistent.
	 */
	window.addEventListener( 'load', function() {
		const searchSubmits = document.querySelectorAll( '.search-submit' );
		if ( ! searchSubmits.length ) {
			return;
		}

		/** @this {HTMLElement} */
		function searchAddClass() {
			const form = this.closest( '.search-form' );
			if ( form ) {
				form.classList.add( 'hover' );
			}
		}

		/** @this {HTMLElement} */
		function searchRemoveClass() {
			const form = this.closest( '.search-form' );
			if ( form ) {
				form.classList.remove( 'hover' );
			}
		}

		searchSubmits.forEach( function( searchSubmit ) {
			searchSubmit.addEventListener( 'mouseenter', searchAddClass );
			searchSubmit.addEventListener( 'mouseleave', searchRemoveClass );
			searchSubmit.addEventListener( 'focus',      searchAddClass );
			searchSubmit.addEventListener( 'blur',       searchRemoveClass );
		} );
	} );

} )();

( function() {

	/**
	 * Toggles the header search form open/closed and keeps aria-expanded in sync
	 * on both the button and the form.
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
		// Dismiss the nav panel before toggling search so the two drop-downs
		// never overlap in portrait mode on iPhone / iPad.
		const navContainer = document.getElementById( 'site-navigation' );
		if ( navContainer && navContainer.classList.contains( 'toggled' ) ) {
			navContainer.classList.remove( 'toggled' );
			const navButton = navContainer.getElementsByTagName( 'button' )[0];
			if ( navButton ) {
				navButton.setAttribute( 'aria-expanded', 'false' );
			}
			const navMenu = navContainer.getElementsByTagName( 'ul' )[0];
			if ( navMenu ) {
				navMenu.setAttribute( 'aria-expanded', 'false' );
			}
		}

		const isToggled = container.classList.contains( 'toggled' );
		document.body.classList.toggle( 'search-toggled', ! isToggled );
		container.classList.toggle( 'toggled', ! isToggled );
		button.setAttribute( 'aria-expanded', isToggled ? 'false' : 'true' );
		form.setAttribute( 'aria-expanded', isToggled ? 'false' : 'true' );
	} );

} )();
