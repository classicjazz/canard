<?php
/**
 * The main template file.
 *
 * The most generic template file in a WordPress theme and one of the two
 * required files (the other being style.css). It is used to display a page
 * when nothing more specific matches a query. On the blog home page
 * (is_home()) it also renders the featured content hero area above the post
 * list. Post format-specific template parts (content-{format}.php) are used
 * for each post in the Loop; content-none.php is shown when no posts are
 * found.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<?php
		if ( is_home() ) {
			get_template_part( 'featured-content' );
		}
	?>

	<div class="site-content-inner">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php
						/*
						 * Include the post format-specific template part.
						 * To override in a child theme, create content-{format}.php.
						 */
						get_template_part( 'content', get_post_format() );
					?>

				<?php endwhile; ?>

				<?php the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => __( '&larr; Previous', 'canard' ),
					'next_text' => __( 'Next &rarr;', 'canard' ),
				) ); ?>

			<?php else : ?>

				<?php get_template_part( 'content', 'none' ); ?>

			<?php endif; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

		<?php get_sidebar(); ?>
	</div><!-- .site-content-inner -->

<?php get_footer(); ?>
