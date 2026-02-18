( function( $ ) {

	const debounce = window.canardUtils.debounce;

	function menuDropdownToggle() {
		$( '.main-navigation .page_item_has_children > a, .main-navigation .menu-item-has-children > a, .widget_nav_menu .page_item_has_children > a, .widget_nav_menu .menu-item-has-children > a' ).each( function() {
			if ( ! $( this ).find( '.dropdown-toggle' ).length ) {
				$( this ).append( '<button class="dropdown-toggle" aria-expanded="false"/>' );
			}
		} );

		if ( $( window ).width() > 959 ) {
			$( '.main-navigation .dropdown-toggle' ).remove();
		}
	}

	$( window ).on( 'load', menuDropdownToggle ).on( 'resize', debounce( menuDropdownToggle, 500 ) );

	$( window ).on( 'load', function() {
		// Targets the first div inside #masthead, which wraps the navigation.
		const menu = $( '#masthead' ).find( 'div' );
		if ( ! menu || ! menu.children().length ) {
			return;
		}

		$( '.dropdown-toggle' ).on( 'click', function( event ) {
			event.preventDefault();
			$( this ).toggleClass( 'toggled' );
			$( this ).parent().next( '.children, .sub-menu' ).toggleClass( 'toggled' );
			$( this ).attr( 'aria-expanded', $( this ).attr( 'aria-expanded' ) === 'false' ? 'true' : 'false' );
		} );

		if ( 'ontouchstart' in window ) {
			menu.find( '.menu-item-has-children > a' ).on( 'touchstart', function( e ) {
				const el = $( this ).parent( 'li' );

				if ( ! el.hasClass( 'focus' ) ) {
					e.preventDefault();
					el.toggleClass( 'focus' );
					el.siblings( '.focus' ).removeClass( 'focus' );
				}
			} );
		}

		menu.find( 'a' ).on( 'focus blur', function() {
			$( this ).parents( '.menu-item' ).toggleClass( 'focus' );
		} );
	} );

} )( jQuery );

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

	if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
		menu.className += ' nav-menu';
	}

	button.onclick = function() {
		if ( -1 !== container.className.indexOf( 'toggled' ) ) {
			container.className = container.className.replace( ' toggled', '' );
			button.setAttribute( 'aria-expanded', 'false' );
			menu.setAttribute( 'aria-expanded', 'false' );
		} else {
			container.className += ' toggled';
			button.setAttribute( 'aria-expanded', 'true' );
			menu.setAttribute( 'aria-expanded', 'true' );
		}
	};

} )();
