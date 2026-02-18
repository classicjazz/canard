/**
 * Canard shared utility functions.
 *
 * Exposed on window.canardUtils so they are accessible to all theme scripts
 * that declare canard-utils as a dependency. Do not wrap in an IIFE — the
 * namespace must be visible at window scope.
 *
 * @package Canard
 */

window.canardUtils = {

	/**
	 * Debounce — defers execution of func until after wait milliseconds have
	 * elapsed since the last time the returned function was invoked.
	 *
	 * @param {Function} func  The function to debounce.
	 * @param {number}   wait  Delay in milliseconds.
	 * @return {Function} Debounced wrapper function.
	 */
	debounce: function( func, wait ) {
		let timeout, args, context, timestamp;
		return function() {
			context   = this;
			args      = [].slice.call( arguments, 0 );
			timestamp = new Date();
			const later = function() {
				const last = ( new Date() ) - timestamp;
				if ( last < wait ) {
					timeout = setTimeout( later, wait - last );
				} else {
					timeout = null;
					func.apply( context, args );
				}
			};
			if ( ! timeout ) {
				timeout = setTimeout( later, wait );
			}
		};
	}

};
