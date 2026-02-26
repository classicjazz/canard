/**
 * @fileoverview Canard shared utility functions.
 *
 * Exposed on window.canardUtils so all theme scripts that declare canard-utils
 * as a dependency can access them. Do not wrap in an IIFE — the namespace must
 * be visible at window scope.
 *
 * The object is frozen via Object.freeze() to prevent third-party scripts from
 * replacing or augmenting canardUtils.debounce with a malicious implementation.
 * Consumer scripts also guard against a missing or non-function debounce as a
 * second layer of defence against load-order failures.
 *
 * @package Canard
 */

window.canardUtils = Object.freeze( {

	/**
	 * Defers execution of func until after wait milliseconds have elapsed since
	 * the last invocation of the returned function.
	 *
	 * @param {Function} func - The function to debounce.
	 * @param {number}   wait - Delay in milliseconds.
	 * @returns {Function} Debounced wrapper function.
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

} );
