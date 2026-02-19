<?php
/**
 * The template for displaying category archive pages.
 *
 * @package Canard
 * @since 1.2.0
 */

get_header(); ?>

	<header class="entry-header entry-hero">
		<?php
		$category_image = canard_get_category_header_image();
		if ( $category_image ) : ?>
		<div class="post-thumbnail">
			<img class="category-header"
			     src="<?php echo esc_url( $category_image ); ?>"
			     alt="<?php echo esc_attr( single_cat_title( '', false ) ); ?>"
			     loading="lazy" />
		</div>
		<?php else :
			$color = canard_get_category_color();
		?>
		<div class="post-thumbnail category-color-fallback" style="background-color: <?php echo esc_attr( $color ); ?>;"></div>
		<?php endif; ?>

		<div class="entry-header-wrapper">
			<div class="entry-header-inner">
				<?php
				the_archive_title( '<h1 class="entry-title">', '</h1>' );
				the_archive_description( '<div class="taxonomy-description">', '</div>' );
				?>
			</div>
		</div>
	</header><!-- .entry-header -->

	<div class="site-content-inner">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">

			<?php if ( have_posts() ) : ?>

				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<?php
					/*
					 * Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', get_post_format() );
					?>

				<?php endwhile; ?>

				<?php
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => __( '&larr; Previous', 'canard' ),
					'next_text' => __( 'Next &rarr;', 'canard' ),
				) );
				?>

			<?php else : ?>

				<?php get_template_part( 'content', 'none' ); ?>

			<?php endif; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

		<?php get_sidebar(); ?>
	</div><!-- .site-content-inner -->

<?php get_footer(); ?>
