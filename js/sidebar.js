/**
 * @fileoverview Sidebar toggle — shows/hides the #secondary sidebar on narrow viewports
 * and keeps aria-expanded in sync on both the sidebar and button for screen reader compatibility.
 */

( function() {

	const sidebar = document.getElementById( 'secondary' );
	if ( ! sidebar ) {
		return;
	}

	const button = document.getElementsByClassName( 'sidebar-toggle' )[0];
	if ( ! button ) {
		return;
	}

	sidebar.setAttribute( 'aria-expanded', 'false' );

	/**
	 * Toggles the sidebar open or closed on button click and synchronizes
	 * aria-expanded on both the sidebar element and the toggle button.
	 *
	 * @returns {void}
	 */
	button.addEventListener( 'click', function() {
		const toggled = sidebar.classList.contains( 'toggled' );
		sidebar.classList.toggle( 'toggled' );
		button.classList.toggle( 'toggled' );
		sidebar.setAttribute( 'aria-expanded', toggled ? 'false' : 'true' );
		button.setAttribute( 'aria-expanded', toggled ? 'false' : 'true' );
	} );

} )();
