<?php
/**
 * Template part for displaying single post content.
 *
 * Renders a full single post including a hero header with an optional featured
 * image (shown only for standard, image, and gallery post formats), the post
 * categories and title, structured data via the entry-script template part,
 * paginated post content, and the entry footer (tags, edit link, etc.).
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header entry-hero">
		<?php if ( has_post_thumbnail() && ( ! has_post_format() || has_post_format( 'image' ) || has_post_format( 'gallery' ) ) ) : ?>
			<div class="post-thumbnail">
				<?php the_post_thumbnail( 'canard-single-thumbnail', [ 'loading' => 'eager', 'fetchpriority' => 'high' ] ); ?>
			</div>
		<?php endif; ?>

		<div class="entry-header-wrapper">
			<div class="entry-header-inner">
				<?php canard_entry_categories(); ?>
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</div>
		</div>

	</header><!-- .entry-header -->

	<?php get_template_part( 'entry', 'script' ); ?>

	<div class="entry-content">
		<?php the_content(); ?>
		<?php
			wp_link_pages( [
				'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'canard' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'canard' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			] );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php canard_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
