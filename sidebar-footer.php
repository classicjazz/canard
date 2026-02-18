<?php
/**
 * The sidebar containing the footer widget area.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-2' ) ) {
	return;
}
?>

<div id="tertiary" class="footer-widget">
	<div class="footer-widget-inner">
		<?php dynamic_sidebar( 'sidebar-2' ); ?>
	</div><!-- .footer-widget-inner -->
</div><!-- #tertiary -->
