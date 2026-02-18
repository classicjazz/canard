( function() {

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
				const btn = document.createElement( 'button' );
				btn.classList.add( 'dropdown-toggle' );
				btn.setAttribute( 'aria-expanded', 'false' );
				link.appendChild( btn );
			}
		} );

		if ( window.innerWidth > 959 ) {
			document.querySelectorAll( '.main-navigation .dropdown-toggle' ).forEach( function( btn ) {
				btn.parentNode.removeChild( btn );
			} );
		}
	}

	window.addEventListener( 'load', menuDropdownToggle );
	window.addEventListener( 'resize', debounce( menuDropdownToggle, 500 ) );

	window.addEventListener( 'load', function() {
		// Targets the first div inside #masthead, which wraps the navigation.
		const masthead = document.getElementById( 'masthead' );
		const menu     = masthead ? masthead.querySelector( 'div' ) : null;
		if ( ! menu || ! menu.children.length ) {
			return;
		}

		// Delegate dropdown-toggle clicks on the document so dynamically
		// inserted buttons (from menuDropdownToggle above) are covered.
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
			menu.querySelectorAll( '.menu-item-has-children > a' ).forEach( function( link ) {
				link.addEventListener( 'touchstart', function( e ) {
					const li = this.parentElement;
					if ( ! li.classList.contains( 'focus' ) ) {
						e.preventDefault();
						li.classList.toggle( 'focus' );
						// Close siblings.
						Array.from( li.parentNode.children ).forEach( function( sibling ) {
							if ( sibling !== li ) {
								sibling.classList.remove( 'focus' );
							}
						} );
					}
				} );
			} );
		}

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
		const toggled = container.classList.contains( 'toggled' );
		container.classList.toggle( 'toggled' );
		button.setAttribute( 'aria-expanded', toggled ? 'false' : 'true' );
		menu.setAttribute( 'aria-expanded', toggled ? 'false' : 'true' );
	} );

} )();
