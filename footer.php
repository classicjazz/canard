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


</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
