<?php
/**
 * The template for displaying all single posts.
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

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'single' ); ?>

					<?php
						// Load the comment template if comments are open or there is at least one comment.
						if ( comments_open() || get_comments_number() ) :
							comments_template();
						endif;
					?>

					<?php
						// Previous/next post navigation.
						the_post_navigation( array(
							'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'canard' ) . '</span> ' . '<span class="screen-reader-text">' . __( 'Next post:', 'canard' ) . '</span> ' . '<span class="post-title">%title</span>',
							'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'canard' ) . '</span> ' . '<span class="screen-reader-text">' . __( 'Previous post:', 'canard' ) . '</span> ' . '<span class="post-title">%title</span>',
						) );
					?>

				<?php endwhile; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

		<?php get_sidebar(); ?>
	</div><!-- .site-content-inner -->

<?php get_footer(); ?>
