<?php
/**
 * The template part for displaying link-format posts.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	/*
	 * Security: target="_blank" without rel="noopener noreferrer" gives the
	 * opened page a window.opener reference that can redirect this tab
	 * (reverse tabnapping). rel="noopener noreferrer" is required on all
	 * outbound _blank links per OWASP and WordPress VIP standards.
	 */
	?>
	<a class="post-link" href="<?php echo esc_url( canard_get_link_url() ); ?>" target="_blank" rel="noopener noreferrer"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg><span class="screen-reader-text"><?php
		printf(
			/* translators: %s: Post title. */
			esc_html__( 'External link to %s', 'canard' ),
			esc_html( get_the_title() )
		);
	?></span></a>

	<header class="entry-header">
		<?php
			canard_entry_categories();
			the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( canard_get_link_url() ) ), '</a></h1>' ); ?>
	</header><!-- .entry-header -->

	<?php get_template_part( 'entry', 'script' ); ?>

	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-content -->

	<div class="entry-meta">
		<?php canard_entry_meta(); ?>
	</div><!-- .entry-meta -->
</article><!-- #post-## -->
