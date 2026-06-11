<?php
/**
 * Template for displaying search results pages.
 *
 * Renders the search results header (including the sanitized query string),
 * iterates the Loop using the content-search template part for each result,
 * outputs paginated navigation, and falls back to content-none.php when no
 * results are found. The sidebar is always included to keep the layout
 * consistent with other listing pages.
 *
 * @package Canard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<div class="site-content-inner">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">

			<?php if ( have_posts() ) : ?>

				<header class="page-header">
					<h1 class="page-title">
						<?php
						// wp_sprintf() prevents compromised translation strings from injecting printf format specifiers.
						echo wp_sprintf(
							/* translators: %s: search query */
							esc_html__( 'Search Results for: %s', 'canard' ),
							'<span>' . esc_html( get_search_query() ) . '</span>'
						);
						?>
					</h1>
				</header><!-- .page-header -->

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'search' ); ?>

				<?php endwhile; ?>

				<?php
				the_posts_pagination( [
					'mid_size'  => 2,
					'prev_text' => '<span class="meta-nav" aria-hidden="true">&larr;</span> ' . esc_html__( 'Previous', 'canard' ),
					'next_text' => esc_html__( 'Next', 'canard' ) . ' <span class="meta-nav" aria-hidden="true">&rarr;</span>',
				] ); ?>

			<?php else : ?>

				<?php get_template_part( 'content', 'none' ); ?>

			<?php endif; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

		<?php get_sidebar(); ?>
	</div><!-- .site-content-inner -->

<?php get_footer(); ?>
