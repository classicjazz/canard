<?php
/**
 * The template for displaying all pages.
 *
 * This is the WordPress construct of pages — it is not to be confused with
 * the theme's page.php template which handles page post type output.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
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

					<?php get_template_part( 'content', 'page' ); ?>

					<?php
						// Load the comment template if comments are open or there is at least one comment.
						if ( comments_open() || get_comments_number() ) :
							comments_template();
						endif;
					?>

				<?php endwhile; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

		<?php get_sidebar(); ?>
	</div><!-- .site-content-inner -->

<?php get_footer(); ?>
