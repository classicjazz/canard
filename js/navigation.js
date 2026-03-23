/**
 * @fileoverview Navigation interactions: dropdown toggles, touch menus, and keyboard focus management.
 */

( function() {

	/**
	 * Resolves a safe debounce function.
	 *
	 * Prefers the frozen canardUtils.debounce when available. Falls back to a
	 * local minimal debounce rather than a passthrough identity, preventing
	 * resize-handler storms when the utility script fails to load. Never writes
	 * to window.canardUtils, which would leave an unfrozen, attacker-writable
	 * object on the global scope that a compromised third-party script could
	 * hijack.
	 *
	 * @returns {Function} A debounce implementation.
	 */
	function resolveDebounce() {
		if (
			window.canardUtils &&
			typeof window.canardUtils.debounce === 'function'
		) {
			return window.canardUtils.debounce;
		}

		console.warn( 'Canard navigation.js: canardUtils not available — using local debounce fallback.' );

		/**
		 * Minimal debounce fallback that never touches window.canardUtils.
		 *
		 * @param {Function} fn   - The function to debounce.
		 * @param {number}   wait - Delay in milliseconds.
		 * @returns {Function} Debounced wrapper function.
		 */
		return function localDebounce( fn, wait ) {
			let timer;
			/**
			 * Resets the debounce timer on each invocation and fires fn after the delay.
			 *
			 * @returns {void}
			 */
			return function() {
				const ctx  = this;
				const args = arguments;
				clearTimeout( timer );
				timer = setTimeout( function() {
					fn.apply( ctx, args );
				}, wait || 500 );
			};
		};
	}

	/** @type {Function} Debounce implementation resolved from canardUtils or local fallback. */
	const debounce = resolveDebounce();

	/**
	 * Returns a plain-text menu item label safe for use in aria-label.
	 *
	 * Reads only direct text node children, skipping icon or SVG child
	 * elements injected by menu icon plugins. Strips HTML-special characters
	 * as a belt-and-suspenders measure against assistive technology parsers
	 * that may re-interpret label strings. Truncates to 100 characters to
	 * limit the payload surface if this value is ever misused by a downstream
	 * tooltip or accessibility plugin that re-injects attribute values into
	 * the DOM.
	 *
	 * @param {HTMLAnchorElement} link - The anchor element whose label is needed.
	 * @returns {string} Sanitized, truncated plain-text label.
	 */
	function getLinkLabel( link ) {
		// Collect only direct text node content, skipping child elements
		// such as icon <span>s or <svg>s injected by menu icon plugins.
		let text = '';
		link.childNodes.forEach( function( node ) {
			if ( node.nodeType === Node.TEXT_NODE ) {
				text += node.nodeValue;
			}
		} );

		return text
			.replace( /[<>&"']/g, '' )
			.replace( /\s+/g, ' ' )
			.trim()
			.slice( 0, 100 );
	}

	/**
	 * Injects dropdown toggle buttons next to parent-item links on mobile,
	 * and removes them on desktop where hover handles sub-menu visibility.
	 *
	 * Uses a data-dropdown-injected stamp on the parent <a> element rather
	 * than a querySelector guard. The querySelector approach is TOCTOU-prone:
	 * the check and the DOM mutation are not atomic, so rapid or programmatic
	 * calls can inject duplicate buttons before the first one is queryable.
	 * The data attribute stamp persists for the lifetime of the injection,
	 * making the guard immune to timing races.
	 *
	 * NOTE: The data-dropdown-injected stamp lives on the <a> element.
	 * If a third-party menu plugin replaces the entire <ul> subtree on resize,
	 * the detached <a> elements and their orphaned .dropdown-toggle buttons are
	 * not reachable by the document-scoped querySelectorAll removal loop.
	 * In that case, duplicate buttons may appear briefly until the plugin's
	 * re-render completes. This is a known integration constraint; the fix
	 * belongs in the third-party plugin's re-render lifecycle hook.
	 *
	 * @returns {void}
	 */
	function menuDropdownToggle() {
		const parentLinks = document.querySelectorAll(
			'.main-navigation .page_item_has_children > a, ' +
			'.main-navigation .menu-item-has-children > a, ' +
			'.widget_nav_menu .page_item_has_children > a, ' +
			'.widget_nav_menu .menu-item-has-children > a'
		);

		/**
		 * Injects a dropdown toggle button into a parent menu item link if not already present.
		 *
		 * @param {HTMLAnchorElement} link - The parent menu item anchor element.
		 * @returns {void}
		 */
		parentLinks.forEach( function( link ) {
			if ( link.dataset.dropdownInjected ) {
				return;
			}

			const btn       = document.createElement( 'button' );
			const linkLabel = getLinkLabel( link );
			btn.classList.add( 'dropdown-toggle' );
			btn.setAttribute( 'aria-expanded', 'false' );
			// Provide an accessible name per WCAG 2.1 SC 4.1.2.
			btn.setAttribute( 'aria-label', linkLabel ? 'Toggle ' + linkLabel + ' submenu' : 'Toggle submenu' );
			link.appendChild( btn );
			link.dataset.dropdownInjected = 'true';
		} );

		if ( window.innerWidth > 959 ) {
			// Remove buttons from BOTH navigation contexts; omitting .widget_nav_menu
			// causes those buttons to persist permanently after a mobile→desktop resize.
			document.querySelectorAll(
				'.main-navigation .dropdown-toggle, .widget_nav_menu .dropdown-toggle'
			).forEach( function( btn ) {
				// Clear the stamp on the parent link so re-injection works if the
				// viewport returns to narrow.
				if ( btn.parentNode ) {
					delete btn.parentNode.dataset.dropdownInjected;
					btn.parentNode.removeChild( btn );
				}
			} );
		}
	}

	// Hoist the debounced wrapper to module scope so only one reference ever
	// exists — prevents duplicate listener accumulation if 'load' fires more
	// than once (e.g. Jetpack Infinite Scroll synthetic load events).
	/**
	 * Debounced version of {@link menuDropdownToggle}, safe to attach to the resize event.
	 *
	 * @type {Function}
	 */
	const debouncedMenuDropdownToggle = debounce( menuDropdownToggle, 500 );

	window.addEventListener( 'load', menuDropdownToggle );
	window.addEventListener( 'resize', debouncedMenuDropdownToggle );

	/**
	 * Initializes navigation interactions after all resources have loaded.
	 *
	 * Sets up dropdown-toggle click delegation, touch-device submenu handling,
	 * and keyboard focus management for the primary navigation menu.
	 *
	 * @returns {void}
	 */
	window.addEventListener( 'load', function() {
		const masthead = document.getElementById( 'masthead' );
		// Use the stable #site-navigation ID rather than a positional div selector,
		// which would match the wrong element when the secondary nav is absent.
		const menu     = masthead ? masthead.querySelector( '#site-navigation' ) : null;
		if ( ! menu || ! menu.children.length ) {
			return;
		}

		// Delegate dropdown-toggle clicks so dynamically inserted buttons are covered.
		/**
		 * Handles delegated clicks on dropdown toggle buttons within the document.
		 *
		 * @param {MouseEvent} event - The click event.
		 * @returns {void}
		 */
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
			//
			// e.preventDefault() is called unconditionally so the browser can treat
			// this listener as effectively passive during scroll-pipeline planning.
			// Open/closed state is tracked via a data-touch-open stamp on the <li>
			// rather than a classList.contains check, removing the conditional branch
			// that previously forced e.preventDefault() to be called inside an if —
			// which prevented the browser from optimising scroll commit timing.
			menu.querySelectorAll( '.menu-item-has-children > a' ).forEach( function( link ) {
				link.addEventListener( 'touchstart', function( e ) {
					e.preventDefault();
					const li   = this.parentElement;
					const open = li.dataset.touchOpen === 'true';

					// Close all siblings before toggling this item.
					Array.from( li.parentNode.children ).forEach( function( sibling ) {
						sibling.dataset.touchOpen = 'false';
						sibling.classList.remove( 'focus' );
					} );

					if ( ! open ) {
						li.dataset.touchOpen = 'true';
						li.classList.add( 'focus' );
					}
				} );
			} );

			// passive:true because this handler never calls e.preventDefault();
			// omitting it forces the browser to wait before committing each scroll frame.
			/**
			 * Closes all open submenus when a touch occurs outside the main navigation.
			 *
			 * @param {TouchEvent} e - The touchstart event.
			 * @returns {void}
			 */
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
 *
 * @returns {void}
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

	/**
	 * Toggles the mobile navigation menu open or closed on button click.
	 *
	 * Dismisses the search panel first if it is open, then toggles the nav
	 * container and synchronizes aria-expanded on both the button and the menu list.
	 *
	 * @returns {void}
	 */
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
