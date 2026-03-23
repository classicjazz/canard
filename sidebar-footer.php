<?php
/**
 * Template for the footer widget area.
 *
 * Renders the footer widget area (sidebar-2) inside a #tertiary wrapper.
 * Returns early without output when sidebar-2 has no active widgets, keeping
 * the footer clean on sites that have not configured footer widgets.
 * Included via get_sidebar( 'footer' ) in footer.php.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guard: bail when the footer widget area has no active widgets.
 *
 * Returning early avoids emitting an empty #tertiary wrapper, which
 * would otherwise leave an unstyled gap in the footer on sites that
 * have not configured footer widgets.
 */
if ( ! is_active_sidebar( 'sidebar-2' ) ) {
	return;
}
?>

<div id="tertiary" class="footer-widget">
	<div class="footer-widget-inner">
		<?php
		/**
		 * Renders all widgets registered to the footer widget area (sidebar-2).
		 *
		 * Output is produced entirely by the registered widgets; this template
		 * only provides the wrapping markup.
		 */
		dynamic_sidebar( 'sidebar-2' ); ?>
	</div><!-- .footer-widget-inner -->
</div><!-- #tertiary -->
