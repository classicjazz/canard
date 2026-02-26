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

	<footer id="colophon" class="site-footer" role="contentinfo">
	<!-- role="contentinfo" is explicit because #colophon is a child of #page
	     (a div), not a direct child of <body>. The implicit landmark role on
	     <footer> only applies when it is not nested inside sectioning content.
	     Without the explicit role, screen readers do not expose this as the
	     page's contentinfo landmark. -->

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
