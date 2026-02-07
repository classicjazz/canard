( function() {

	const siteBranding = document.getElementsByClassName( 'site-branding' )[0];

	// Guard against pages where .site-branding is absent, and skip if it has height.
	if ( ! siteBranding || siteBranding.clientHeight > 0 ) {
		return;
	}

	document.body.classList.add( 'no-site-branding' );

} )();
