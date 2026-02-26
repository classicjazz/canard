<?php
/**
 * The template for displaying search results pages.
 *
 * @package Canard
 */

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
							printf(
								/* translators: %s: search query */
								esc_html__( 'Search Results for: %s', 'canard' ),
								'<span>' . esc_html( get_search_query() ) . '</span>'
							);
						?>
					</h1>
				</header><!-- .page-header -->

				<?php while ( have_posts() ) : the_post(); ?>

					<?php
						/*
						 * Include the search-specific template part.
						 * To override in a child theme, create content-search.php.
						 */
						get_template_part( 'content', 'search' );
					?>

				<?php endwhile; ?>

				<?php
				/*
				 * Security: use esc_html__() rather than __() so that the pagination
				 * link labels are treated as plain text. A compromised translation
				 * file cannot inject markup into the prev/next link text.
				 */
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => esc_html__( '&larr; Previous', 'canard' ),
					'next_text' => esc_html__( 'Next &rarr;', 'canard' ),
				) ); ?>

			<?php else : ?>

				<?php get_template_part( 'content', 'none' ); ?>

			<?php endif; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

		<?php get_sidebar(); ?>
	</div><!-- .site-content-inner -->

<?php get_footer(); ?>
