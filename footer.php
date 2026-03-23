<?php
/**
 * Template for displaying the site footer.
 *
 * Closes the #content div opened in header.php, renders the #colophon
 * <footer> element (containing the optional footer widget area and footer
 * navigation menu), closes the #page wrapper, calls wp_footer(), and closes
 * the <body> and <html> tags.
 *
 * The role="contentinfo" attribute is applied explicitly on #colophon because
 * the implicit landmark role on <footer> only applies when it is a direct
 * child of <body>. Here it is nested inside the #page div, so the implicit
 * role is not exposed by assistive technology without the explicit attribute.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">

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

	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
