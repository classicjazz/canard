<?php
/**
 * Template for displaying the featured content hero area.
 *
 * Retrieves featured posts via canard_get_featured_posts() and renders each
 * one using the content-featured-post template part. Returns early without
 * output when no featured posts are configured. Called from index.php only
 * when is_home() is true.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$featured_posts = canard_get_featured_posts();
if ( empty( $featured_posts ) ) {
	return;
}
?>

<div id="featured-content" class="featured-content">
	<div class="featured-content-inner">
		<?php
			foreach ( $featured_posts as $post ) {
				setup_postdata( $post );
				get_template_part( 'content', 'featured-post' );
			}

			wp_reset_postdata();
		?>
	</div><!-- .featured-content-inner -->
</div><!-- #featured-content -->
