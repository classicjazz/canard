<?php
/**
 * The template part for displaying content on the blog index and archive pages.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( has_post_thumbnail() && 'post' === get_post_type() && ( ! has_post_format() || has_post_format( 'image' ) || has_post_format( 'gallery' ) ) ) : ?>

		<?php
			if ( ! has_post_format() ) {
				echo '<a class="post-thumbnail" href="' . esc_url( get_permalink() ) . '">';
			} elseif ( has_post_format( 'image' ) || has_post_format( 'gallery' ) ) {
				echo '<div class="post-thumbnail">';
			}
			the_post_thumbnail( 'canard-post-thumbnail' );
		?>

		<?php if ( is_sticky() ) : ?>
			<span class="sticky-post"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"><path d="M16 3a1 1 0 00-1 1v1H9V4a1 1 0 00-2 0v1H6a2 2 0 00-2 2v11a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1V4a1 1 0 00-1-1zM6 7h12v2H6V7zm0 4h12v7H6v-7z"/></svg><span class="screen-reader-text"><?php esc_html_e( 'Sticky post', 'canard' ); ?></span></span>
		<?php endif; ?>

		<?php
			if ( ! has_post_format() ) {
				echo '</a>';
			} elseif ( has_post_format( 'image' ) || has_post_format( 'gallery' ) ) {
				echo '</div>';
			}
		?>

	<?php endif; ?>

	<header class="entry-header">
		<?php
			canard_entry_categories();
			the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' ); ?>
	</header><!-- .entry-header -->

	<?php get_template_part( 'entry', 'script' ); ?>

	<div class="entry-summary">
		<?php
		/*
		 * Use the_content() when a <!--more--> tag is present so the "Continue
		 * reading" link respects the manual break point. Fall back to the_excerpt()
		 * for posts without one. get_the_content() is used only for the check —
		 * the actual output is always through the template tag so filters run.
		 */
		if ( str_contains( get_the_content(), '<!--more' ) ) {
			the_content(
				sprintf(
					/* translators: %s: Name of current post. */
					wp_kses( __( 'Continue reading %s', 'canard' ), array( 'span' => array( 'class' => array() ) ) ),
					the_title( '<span class="screen-reader-text">"', '"</span>', false )
				)
			);
		} else {
			the_excerpt();
		}
		?>
	</div><!-- .entry-summary -->

	<?php if ( 'post' === get_post_type() ) : ?>
		<div class="entry-meta">
			<?php canard_entry_meta(); ?>
		</div><!-- .entry-meta -->
	<?php endif; ?>
</article><!-- #post-## -->
