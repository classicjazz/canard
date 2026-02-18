<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	</div><!-- #content -->

	<?php get_sidebar( 'footer' ); ?>

	<?php if ( has_nav_menu( 'footer' ) ) : ?>
		<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer Navigation', 'canard' ); ?>">
			<?php
				wp_nav_menu( array(
					'theme_location'  => 'footer',
					'depth'           => 1,
				) );
			?>
		</nav><!-- .footer-navigation -->
	<?php endif; ?>

	<?php if ( has_nav_menu( 'secondary' ) ) : ?>
		<nav class="bottom-navigation" aria-label="<?php esc_attr_e( 'Secondary Navigation', 'canard' ); ?>">
			<?php
				wp_nav_menu( array(
					'theme_location'  => 'secondary',
					'depth'           => 1,
				) );
			?>
		</nav><!-- .bottom-navigation -->
	<?php endif; ?>

	<footer id="colophon" class="site-footer">
		<div id="site-info" class="site-info">
			<a href="<?php echo esc_url( 'https://wordpress.org/' ); ?>"><?php printf( esc_html__( 'Proudly powered by %s', 'canard' ), 'WordPress' ); ?></a>
			<svg class="sep" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zM3.511 11.58l2.617 7.168A8.545 8.545 0 013.5 12c0-.142.006-.283.011-.42zM12 20.5a8.506 8.506 0 01-2.4-.344l2.548-7.405.028-.087 2.443 6.694a.41.41 0 00.031.064A8.497 8.497 0 0112 20.5zm1.26-11.459l2.127 6.173-5.99-1.784 1.949-5.811c.322.09.658.14 1.006.14.32 0 .626-.043.908-.118zm4.69 7.22l1.787-5.17a8.55 8.55 0 01.763 3.531 8.533 8.533 0 01-2.55 1.639zM12 5a2.999 2.999 0 110 6 2.999 2.999 0 010-6zm0 1.5a1.499 1.499 0 100 2.999A1.499 1.499 0 0012 6.5zm-5.49 2.007A8.5 8.5 0 017.78 4.1l2.696 7.39-3.966-1.183v.001z"/></svg>
			<?php
			printf(
				/* translators: 1: Theme name */
				esc_html__( 'Theme: %s by', 'canard' ),
				'Canard'
			);
			echo ' <a href="' . esc_url( 'https://wordpress.com/themes/' ) . '" rel="designer">Automattic</a>.';
		?>
		</div><!-- #site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
