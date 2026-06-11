<?php
/**
 * Template part for displaying page content in page.php.
 *
 * Renders a single static page including an optional featured image, the page
 * title, structured data via the entry-script template part, paginated content,
 * and an edit link for users with the appropriate capability.
 *
 * The featured image does not force eager loading because page layouts vary
 * widely and the thumbnail is not reliably the Largest Contentful Paint
 * element. Override loading behavior in a child theme when needed.
 *
 * @package Canard
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-thumbnail">
				<?php the_post_thumbnail( 'canard-single-thumbnail' ); ?>
			</div>
		<?php endif; ?>

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
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
		<?php edit_post_link( esc_html__( 'Edit', 'canard' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
