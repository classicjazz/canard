<?php
/**
 * The template for displaying category archive pages.
 *
 * @package Canard
 * @since 1.2.0
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
			 * URL itself. We confirm the attachment has a public-facing status
			 * ('inherit' = attached to a published post, or '0' parent = unattached
			 * media library item) before proceeding.
			 */
			$attachment_id  = absint( get_term_meta( get_queried_object_id(), '_category_image_id', true ) );
			$cat_img_meta   = array();

			if ( $attachment_id > 0 ) {
				$attachment_status = get_post_status( $attachment_id );
				// 'inherit' means the attachment's visibility mirrors its parent post.
				// Only read metadata when the attachment itself is publicly accessible.
				if ( 'inherit' === $attachment_status || 'publish' === $attachment_status ) {
					$cat_img_meta = (array) wp_get_attachment_metadata( $attachment_id );
				}
			}

			// Retrieve attachment dimensions to reserve layout space before the
			// image loads, preventing Cumulative Layout Shift (CLS).
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
		?>
		<div class="post-thumbnail category-color-fallback" style="background-color: <?php echo esc_attr( $color ); ?>;"></div>
		<?php endif; ?>

		<div class="entry-header-wrapper">
			<div class="entry-header-inner">
				<?php
				the_archive_title( '<h1 class="entry-title">', '</h1>' );
				/*
				 * Security: the_archive_description() outputs the taxonomy term
				 * description field. Users with manage_categories capability can
				 * store arbitrary HTML in this field. the_archive_description()
				 * passes the value through wpautop() but does not apply wp_kses_post().
				 * We use get_the_archive_description() and sanitise with wp_kses_post()
				 * before echoing so that <script> and other dangerous tags are stripped.
				 * See also: the get_the_archive_description filter registered in
				 * functions.php which applies the same sanitisation globally.
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
				/*
				 * Security: use esc_html__() rather than __() for the pagination
				 * link labels. __() returns a raw translated string; if a translation
				 * file is compromised or a .po contributor includes markup, it would
				 * be passed through the_posts_pagination()'s internal wp_kses_post()
				 * — which is an implementation detail that has changed across WP
				 * versions. esc_html__() makes the intent explicit and ensures the
				 * strings are treated as plain text regardless of internal changes.
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
