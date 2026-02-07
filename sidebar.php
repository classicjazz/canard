<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-1' ) && ( ( true !== (bool) get_theme_mod( 'canard_author_bio' ) && ! get_the_author_meta( 'description' ) ) || ! is_single() ) ) {
	return;
}
?>

<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<button class="sidebar-toggle" aria-controls="secondary" aria-expanded="false"><span class="screen-reader-text"><?php esc_html_e( 'Sidebar', 'canard' ); ?></span></button>
<?php endif; ?>

<div id="secondary" class="widget-area">
	<?php
		// Author Bio.
		if ( true === (bool) get_theme_mod( 'canard_author_bio' ) && get_the_author_meta( 'description' ) && is_single() ) {
			get_template_part( 'author-bio' );
		}

		// Sidebar widgets.
		dynamic_sidebar( 'sidebar-1' );
	?>
</div><!-- #secondary -->
