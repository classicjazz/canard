/**
 * @fileoverview Adds 'no-site-branding' to <body> when the site branding container has
 * zero height, indicating both the logo and site title/description are hidden.
 * CSS uses this class to adjust header layout for logo-less configurations.
 */

( function() {

	const siteBranding = document.getElementsByClassName( 'site-branding' )[0];

	if ( ! siteBranding || siteBranding.clientHeight > 0 ) {
		return;
	}

	document.body.classList.add( 'no-site-branding' );

} )();
