<?php
/**
 * Template for displaying category archive pages.
 *
 * Renders a full-width hero header that shows either a custom category header
 * image (stored in term meta) or a solid color fallback, followed by the
 * archive title, optional taxonomy description, post loop, and pagination.
 * Falls back to content-none.php when the query returns no posts.
 *
 * @package Canard
 * @since 2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<header class="entry-header entry-hero">
		<?php
		$category_image = canard_get_category_header_image();
		if ( $category_image ) :
			/*
			 * Security (IDOR): retrieve the attachment ID stored in term meta and
			 * verify that the attachment is publicly accessible before reading its
			 * metadata. An editor-role user who sets _category_image_id to the
			 * attachment ID of a private post's image could otherwise retrieve that
			 * image's dimensions (a side-channel) and potentially expose the image
			 * URL itself. Confirm the attachment has a public-facing status
			 * ('inherit' = attached to a published post, or '0' parent = unattached
			 * media-library item) before proceeding.
			 */
			$attachment_id = absint( get_term_meta( get_queried_object_id(), '_category_image_id', true ) );
			$cat_img_meta  = array();

			if ( $attachment_id > 0 ) {
				$attachment_status = get_post_status( $attachment_id );
				/*
				 * 'inherit' means the attachment's visibility mirrors its parent post.
				 * Only read metadata when the attachment itself is publicly accessible.
				 */
				if ( 'inherit' === $attachment_status || 'publish' === $attachment_status ) {
					$cat_img_meta = (array) wp_get_attachment_metadata( $attachment_id );
				}
			}

			// Dimensions reserve layout space before the image loads, preventing CLS.
			$img_w = isset( $cat_img_meta['width'] )  ? absint( $cat_img_meta['width'] )  : 1920;
			$img_h = isset( $cat_img_meta['height'] ) ? absint( $cat_img_meta['height'] ) : 420;
		?>
		<div class="post-thumbnail">
			<img class="category-header"
			     src="<?php echo esc_url( $category_image ); ?>"
			     width="<?php echo absint( $img_w ); ?>"
			     height="<?php echo absint( $img_h ); ?>"
			     alt="<?php echo esc_attr( single_cat_title( '', false ) ); ?>"
			     loading="eager"
			     fetchpriority="high"
			     sizes="100vw" />
		</div>
		<?php else :
			$color = canard_get_category_color();
			/*
			 * The background color is injected via wp_add_inline_style() rather
			 * than a style="" attribute. This changes specificity from inline
			 * (highest) to class-level, so child-theme rules targeting
			 * .category-color-fallback will win if they declare a background-color.
			 * Treat as a minor breaking change — document in CHANGES.md and bump
			 * the theme version.
			 *
			 * The term ID is included in the CSS rule so concurrent page loads for
			 * different categories each receive the correct color. WordPress
			 * deduplicates identical inline style strings automatically.
			 */
			$term_id = absint( get_queried_object_id() );
			wp_add_inline_style(
				'canard-style',
				sprintf(
					'body.term-%1$d .category-color-fallback { background-color: %2$s; }',
					$term_id,
					sanitize_hex_color( $color ) ?: '#d11415'
				)
			);
		?>
		<div class="post-thumbnail category-color-fallback"></div>
		<?php endif; ?>

		<div class="entry-header-wrapper">
			<div class="entry-header-inner">
				<?php
				the_archive_title( '<h1 class="entry-title">', '</h1>' );
				/*
				 * Security: the_archive_description() outputs the taxonomy term
				 * description field. Users with the manage_categories capability can
				 * store arbitrary HTML in that field. the_archive_description() passes
				 * the value through wpautop() but does not apply wp_kses_post().
				 * Use get_the_archive_description() and sanitize with wp_kses_post()
				 * before echoing so that <script> and other dangerous tags are stripped.
				 * See also: the get_the_archive_description filter registered in
				 * functions.php which applies the same sanitization globally.
				 */
				$archive_desc = get_the_archive_description();
				if ( $archive_desc ) {
					echo '<div class="taxonomy-description">' . wp_kses_post( $archive_desc ) . '</div>';
				}
				?>
			</div>
		</div>
	</header><!-- .entry-header -->

	<div class="site-content-inner">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', get_post_format() ); ?>

				<?php endwhile; ?>

				<?php
				/*
				 * Security: esc_html__() is used instead of __() for pagination
				 * link labels so that markup in a compromised translation file
				 * cannot reach the page, regardless of how the_posts_pagination()
				 * handles its arguments internally.
				 */
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => esc_html__( '&larr; Previous', 'canard' ),
					'next_text' => esc_html__( 'Next &rarr;', 'canard' ),
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
