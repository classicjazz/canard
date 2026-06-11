<?php
/**
 * Template part for displaying a "no posts found" message.
 *
 * Shown when a query returns no results. Outputs context-sensitive guidance:
 * a prompt to create the first post (for admins on the home page), a search
 * form with a retry message (for search results pages), or a generic fallback
 * search form for all other contexts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Canard
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'canard' ); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<p><?php
				printf(
					/* translators: %s: URL to the new post admin screen. */
					wp_kses( __( 'Ready to publish your first post? <a href="%s">Get started here</a>.', 'canard' ), [ 'a' => [ 'href' => [] ] ] ),
					esc_url( admin_url( 'post-new.php' ) )
				);
			?></p>

		<?php elseif ( is_search() ) : ?>

			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'canard' ); ?></p>
			<?php get_search_form(); ?>

		<?php else : ?>

			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'canard' ); ?></p>
			<?php get_search_form(); ?>

		<?php endif; ?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
