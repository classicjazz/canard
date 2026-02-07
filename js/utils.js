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
		let timeout;
		return function( ...args ) {
			const context = this;
			clearTimeout( timeout );
			timeout = setTimeout( function() {
				func.apply( context, args );
			}, wait );
		};
	}

};
