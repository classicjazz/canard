<?php
/**
 * Template part for displaying a single post card on index and archive pages.
 *
 * Renders one post card inside the Loop. Handles featured image display with
 * format-aware wrapper elements, the sticky-post badge, entry header, entry
 * meta (author, date, comments, categories), and a smart excerpt that
 * respects manual <!--more--> breaks.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	// Cached once — used in both the thumbnail conditional and the entry-meta conditional.
	$post_type = get_post_type();
	?>
	<?php if ( has_post_thumbnail() && 'post' === $post_type && ( ! has_post_format() || has_post_format( 'image' ) || has_post_format( 'gallery' ) ) ) : ?>

		<?php
			if ( ! has_post_format() ) {
				echo '<a class="post-thumbnail" href="' . esc_url( get_permalink() ) . '">';
			} elseif ( has_post_format( 'image' ) || has_post_format( 'gallery' ) ) {
				echo '<div class="post-thumbnail">';
			}
			// The sizes attribute is narrowed from the default 100vw because the
			// post list sits inside #primary .content-area, which is narrower than
			// the viewport when the sidebar is present. Using 100vw would cause
			// the browser to download a full-width image on desktop where the
			// container is approximately 620 px wide.
			the_post_thumbnail( 'canard-post-thumbnail', array(
				'loading' => 'lazy',
				'sizes'   => '(max-width: 767px) 100vw, (max-width: 1039px) 50vw, 620px',
			) );
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
		<?php the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' ); ?>
	</header><!-- .entry-header -->

	<?php get_template_part( 'entry', 'script' ); ?>

	<?php if ( 'post' === $post_type ) : ?>
		<div class="entry-meta">
			<?php
			/*
			 * Output order: author · date (abbreviated) · comments · categories.
			 * canard_entry_meta() emits the byline, posted-on, and comments spans.
			 * canard_entry_categories() emits the cat-links span inline after them.
			 * Separator slashes between spans are handled by CSS via the
			 * .content-area .entry-meta > span rules.
			 */
			canard_entry_meta();
			canard_entry_categories();
			?>
		</div><!-- .entry-meta -->
	<?php endif; ?>

	<div class="entry-summary">
		<?php
		/*
		 * Use the_content() when a <!--more--> tag is present so the "Continue
		 * reading" link respects the manual break point. Fall back to the_excerpt()
		 * for posts that have no manual break. get_the_content() is used only for
		 * the check — actual output always goes through the template tag so
		 * all registered content filters run.
		 *
		 * Cast to string: get_the_content() can return false outside the Loop,
		 * which would raise a PHP 8 TypeError in str_contains().
		 */
		if ( str_contains( (string) get_the_content(), '<!--more' ) ) {
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

</article><!-- #post-## -->