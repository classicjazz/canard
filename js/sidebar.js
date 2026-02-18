( function() {

	const sidebar = document.getElementById( 'secondary' );
	if ( ! sidebar ) {
		return;
	}

	const button = document.getElementsByClassName( 'sidebar-toggle' )[0];
	if ( ! button ) {
		return;
	}

	// Note: footer was declared in the original but never used — removed.

	sidebar.setAttribute( 'aria-expanded', 'false' );

	button.onclick = function() {
		if ( -1 !== sidebar.className.indexOf( 'toggled' ) ) {
			sidebar.className = sidebar.className.replace( ' toggled', '' );
			button.className  = button.className.replace( ' toggled', '' );
			sidebar.setAttribute( 'aria-expanded', 'false' );
			button.setAttribute( 'aria-expanded', 'false' );
		} else {
			sidebar.className += ' toggled';
			button.className  += ' toggled';
			sidebar.setAttribute( 'aria-expanded', 'true' );
			button.setAttribute( 'aria-expanded', 'true' );
		}
	};

} )();
