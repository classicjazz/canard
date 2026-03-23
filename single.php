<?php
/**
 * Template for displaying all single posts.
 *
 * Loads the site header, then renders the single post inside a two-column
 * layout (#primary + sidebar). After the post content it conditionally loads
 * the comments template (when comments are open or at least one comment
 * exists) and outputs previous/next post navigation. Delegates actual post
 * markup to content-single.php via get_template_part().
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
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;
					?>

					<?php
					// __() not esc_html__() — labels contain HTML span elements that must render, not be escaped.
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
