<?php
/**
 * The template for displaying archive pages.
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

			<?php if ( have_posts() ) : ?>

				<header class="page-header">
					<?php
						the_archive_title( '<h1 class="page-title">', '</h1>' );
						/*
						 * Security: the_archive_description() does not apply wp_kses_post()
						 * to the taxonomy term description field. Users with manage_categories
						 * capability can store arbitrary HTML in that field. Use
						 * get_the_archive_description() + wp_kses_post() so that script tags
						 * and other dangerous markup are stripped before output.
						 * See also: the get_the_archive_description filter in functions.php.
						 */
						$archive_desc = get_the_archive_description();
						if ( $archive_desc ) {
							echo '<div class="taxonomy-description">' . wp_kses_post( $archive_desc ) . '</div>';
						}
					?>
				</header><!-- .page-header -->

				<?php while ( have_posts() ) : the_post(); ?>

					<?php
						/*
						 * Include the post format-specific template part.
						 * To override in a child theme, create content-{format}.php.
						 */
						get_template_part( 'content', get_post_format() );
					?>

				<?php endwhile; ?>

				<?php
				/*
				 * Security: use esc_html__() rather than __() so that pagination
				 * link labels are treated as plain text.
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
