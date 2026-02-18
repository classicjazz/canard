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

	<?php if ( has_nav_menu( 'social' ) ) : ?>
		<nav class="social-navigation bottom-social" aria-label="<?php esc_attr_e( 'Social Navigation', 'canard' ); ?>">
			<?php
				wp_nav_menu( array(
					'theme_location'  => 'social',
					'link_before'     => '<span class="screen-reader-text">',
					'link_after'      => '</span>',
					'depth'           => 1,
				) );
			?>
		</nav><!-- .social-navigation -->
	<?php endif; ?>

	<footer id="colophon" class="site-footer">
		<div id="site-info" class="site-info">
			<a href="<?php echo esc_url( 'https://wordpress.org/' ); ?>"><?php printf( esc_html__( 'Proudly powered by %s', 'canard' ), 'WordPress' ); ?></a>
			<span class="genericon genericon-wordpress sep"></span>
			<?php
				printf(
					/* translators: 1: Theme name, 2: Theme author link */
					esc_html__( 'Theme: %1$s by %2$s.', 'canard' ),
					'Canard',
					'<a href="https://wordpress.com/themes/" rel="designer">Automattic</a>'
				);
			?>
		</div><!-- #site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
