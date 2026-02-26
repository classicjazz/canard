<?php
/**
 * The template for displaying Author Bio
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="author-info">
	<div class="author-avatar">
		<?php
		/**
		 * Filter the author bio avatar size.
		 *
		 * @param int $size The avatar height and width size in pixels.
		 */
		$author_bio_avatar_size = apply_filters( 'canard_author_bio_avatar_size', 60 );

		/*
		 * Security: get_avatar() returns an <img> HTML string. Plugins or child
		 * themes may hook get_avatar to inject extra attributes or markup. Pass
		 * the output through wp_kses() with the same allowlist used in
		 * canard_entry_meta() so that any filter-injected content is stripped
		 * before it reaches the page.
		 */
		$avatar_allowlist = array(
			'img' => array(
				'src'           => array(),
				'class'         => array(),
				'alt'           => array(),
				'width'         => array(),
				'height'        => array(),
				'loading'       => array(),
				'decoding'      => array(),
				'fetchpriority' => array(),
			),
		);
		echo wp_kses( get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size ), $avatar_allowlist );
		?>
	</div><!-- .author-avatar -->

	<div class="author-heading">
		<h2 class="author-title"><?php esc_html_e( 'Published by', 'canard' ); ?></h2>
		<h3 class="author-name"><?php echo esc_html( get_the_author() ); ?></h3>
	</div><!-- .author-heading -->

	<p class="author-bio">
		<?php echo esc_html( get_the_author_meta( 'description' ) ); ?>
		<a class="author-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
			<?php
			printf(
				/* translators: %s: Author display name. */
				esc_html__( 'View all posts by %s', 'canard' ),
				esc_html( get_the_author() )
			);
			?>
		</a>
	</p><!-- .author-bio -->
</div><!-- .author-info -->
