<?php
/**
 * Template for the main sidebar.
 *
 * Renders the primary sidebar (sidebar-1) and, on single posts when the
 * canard_author_bio Customizer option is enabled and the author has a
 * biography, also renders the author bio block via author-bio.php.
 *
 * Returns early without output when sidebar-1 has no active widgets AND the
 * author bio conditions are not met, so the two-column layout automatically
 * collapses to a single column.
 *
 * The sidebar toggle button is only emitted when sidebar-1 is active because
 * it controls the #secondary panel. Rendering the button when the panel is
 * empty would create an orphaned interactive element.
 *
 * @package Canard
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail when neither the sidebar widgets nor the author bio would render,
// to avoid emitting an empty #secondary column.
if ( ! is_active_sidebar( 'sidebar-1' ) && ( ( ! get_theme_mod( 'canard_author_bio' ) && ! get_the_author_meta( 'description' ) ) || ! is_single() ) ) {
	return;
}
?>

<?php if ( is_active_sidebar( 'sidebar-1' ) ) { ?>
	<button class="sidebar-toggle" aria-controls="secondary" aria-expanded="false"><span class="screen-reader-text"><?php esc_html_e( 'Sidebar', 'canard' ); ?></span></button>
<?php } ?>

<div id="secondary" class="widget-area">
	<?php
	// Author bio renders above widgets so it appears at the top of the sidebar column.
	if ( get_theme_mod( 'canard_author_bio' ) && get_the_author_meta( 'description' ) && is_single() ) {
		get_template_part( 'author-bio' );
	}

	dynamic_sidebar( 'sidebar-1' );
	?>
</div><!-- #secondary -->
