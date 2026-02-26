/**
 * @fileoverview Navigation interactions: dropdown toggles, touch menus, and keyboard focus management.
 */

( function() {

	// Guard against utils.js failing to load; provides a no-op passthrough so
	// navigation features continue to function without debouncing.
	if ( ! window.canardUtils || typeof window.canardUtils.debounce !== 'function' ) {
		console.warn( 'Canard navigation.js: canardUtils not available — debounced resize handler disabled.' );
		window.canardUtils = window.canardUtils || {};
		window.canardUtils.debounce = function( fn ) { return fn; };
	}

	const debounce = window.canardUtils.debounce;

	/**
	 * Injects dropdown toggle buttons next to parent-item links on mobile,
	 * and removes them on desktop where hover handles sub-menu visibility.
	 */
	function menuDropdownToggle() {
		const parentLinks = document.querySelectorAll(
			'.main-navigation .page_item_has_children > a, ' +
			'.main-navigation .menu-item-has-children > a, ' +
			'.widget_nav_menu .page_item_has_children > a, ' +
			'.widget_nav_menu .menu-item-has-children > a'
		);

		parentLinks.forEach( function( link ) {
			if ( ! link.querySelector( '.dropdown-toggle' ) ) {
				const btn      = document.createElement( 'button' );
				const linkText = link.firstChild && link.firstChild.nodeValue
					? link.firstChild.nodeValue.trim()
					: link.textContent.replace( /\s+/g, ' ' ).trim();
				btn.classList.add( 'dropdown-toggle' );
				btn.setAttribute( 'aria-expanded', 'false' );
				// Provide an accessible name per WCAG 2.1 SC 4.1.2.
				btn.setAttribute( 'aria-label', linkText ? 'Toggle ' + linkText + ' submenu' : 'Toggle submenu' );
				link.appendChild( btn );
			}
		} );

		if ( window.innerWidth > 959 ) {
			// Remove buttons from BOTH navigation contexts; omitting .widget_nav_menu
			// causes those buttons to persist permanently after a mobile→desktop resize.
			document.querySelectorAll(
				'.main-navigation .dropdown-toggle, .widget_nav_menu .dropdown-toggle'
			).forEach( function( btn ) {
				btn.parentNode.removeChild( btn );
			} );
		}
	}

	window.addEventListener( 'load', menuDropdownToggle );
	window.addEventListener( 'resize', debounce( menuDropdownToggle, 500 ) );

	window.addEventListener( 'load', function() {
		const masthead = document.getElementById( 'masthead' );
		// Use the stable #site-navigation ID rather than a positional div selector,
		// which would match the wrong element when the secondary nav is absent.
		const menu     = masthead ? masthead.querySelector( '#site-navigation' ) : null;
		if ( ! menu || ! menu.children.length ) {
			return;
		}

		// Delegate dropdown-toggle clicks so dynamically inserted buttons are covered.
		document.addEventListener( 'click', function( event ) {
			const btn = event.target.closest( '.dropdown-toggle' );
			if ( ! btn ) {
				return;
			}
			event.preventDefault();

			const isExpanded = btn.getAttribute( 'aria-expanded' ) === 'true';
			btn.classList.toggle( 'toggled' );
			btn.setAttribute( 'aria-expanded', isExpanded ? 'false' : 'true' );

			const subMenu = btn.parentNode.nextElementSibling;
			if ( subMenu && ( subMenu.classList.contains( 'children' ) || subMenu.classList.contains( 'sub-menu' ) ) ) {
				subMenu.classList.toggle( 'toggled' );
			}
		} );

		if ( 'ontouchstart' in window ) {
			// On touch devices, the first tap on a parent menu item opens the submenu
			// rather than following the link, matching hover behaviour on desktop.
			menu.querySelectorAll( '.menu-item-has-children > a' ).forEach( function( link ) {
				link.addEventListener( 'touchstart', function( e ) {
					const li = this.parentElement;
					if ( ! li.classList.contains( 'focus' ) ) {
						e.preventDefault();
						li.classList.toggle( 'focus' );
						Array.from( li.parentNode.children ).forEach( function( sibling ) {
							if ( sibling !== li ) {
								sibling.classList.remove( 'focus' );
							}
						} );
					}
				} );
			} );

			// passive:true because this handler never calls e.preventDefault();
			// omitting it forces the browser to wait before committing each scroll frame.
			document.addEventListener( 'touchstart', function( e ) {
				if ( ! e.target.closest( '.main-navigation' ) ) {
					document.querySelectorAll( '.main-navigation .focus' )
						.forEach( function( el ) { el.classList.remove( 'focus' ); } );
				}
			}, { passive: true } );
		}

		// Add/remove 'focus' on ancestor menu items so CSS can show sub-menus
		// when keyboard focus is inside them (keyboard nav parity with hover).
		menu.querySelectorAll( 'a' ).forEach( function( link ) {
			link.addEventListener( 'focus', function() {
				let el = this.parentElement;
				while ( el && el !== menu ) {
					if ( el.classList.contains( 'menu-item' ) ) {
						el.classList.add( 'focus' );
					}
					el = el.parentElement;
				}
			} );
			link.addEventListener( 'blur', function() {
				let el = this.parentElement;
				while ( el && el !== menu ) {
					if ( el.classList.contains( 'menu-item' ) ) {
						el.classList.remove( 'focus' );
					}
					el = el.parentElement;
				}
			} );
		} );
	} );

} )();

/**
 * Mobile menu toggle — shows/hides the primary navigation list and keeps
 * aria-expanded in sync on both the button and the <ul>.
 */
( function() {

	const container = document.getElementById( 'site-navigation' );
	if ( ! container ) {
		return;
	}

	const button = container.getElementsByTagName( 'button' )[0];
	if ( ! button ) {
		return;
	}

	const menu = container.getElementsByTagName( 'ul' )[0];
	if ( ! menu ) {
		button.style.display = 'none';
		return;
	}
	menu.setAttribute( 'aria-expanded', 'false' );

	if ( ! menu.classList.contains( 'nav-menu' ) ) {
		menu.classList.add( 'nav-menu' );
	}

	button.addEventListener( 'click', function() {
		// Dismiss the search panel before toggling nav so the two drop-downs
		// never overlap in portrait mode on iPhone / iPad.
		const searchContainer = document.getElementById( 'search-header' );
		if ( searchContainer && searchContainer.classList.contains( 'toggled' ) ) {
			searchContainer.classList.remove( 'toggled' );
			document.body.classList.remove( 'search-toggled' );
			const searchButton = searchContainer.getElementsByTagName( 'button' )[0];
			if ( searchButton ) {
				searchButton.setAttribute( 'aria-expanded', 'false' );
			}
			const searchForm = searchContainer.getElementsByTagName( 'form' )[0];
			if ( searchForm ) {
				searchForm.setAttribute( 'aria-expanded', 'false' );
			}
		}

		const toggled = container.classList.contains( 'toggled' );
		container.classList.toggle( 'toggled' );
		button.setAttribute( 'aria-expanded', toggled ? 'false' : 'true' );
		menu.setAttribute( 'aria-expanded', toggled ? 'false' : 'true' );
	} );

} )();
