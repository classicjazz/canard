<?php
/**
 * Template part for displaying an author biography block.
 *
 * Outputs the author's avatar, display name, biographical description, and
 * a link to the author's post archive. Intended to be included via
 * get_template_part() from single-post templates.
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
		$author_bio_avatar_size = (int) apply_filters( 'canard_author_bio_avatar_size', 60 );

		/*
		 * Security: get_avatar() returns an <img> HTML string. Plugins or child
		 * themes may hook get_avatar to inject extra attributes or markup. Pass
		 * the output through wp_kses() with an explicit allowlist so that any
		 * filter-injected content is stripped before it reaches the page.
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
		$avatar_html = get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size );
		echo wp_kses( $avatar_html !== false ? $avatar_html : '', $avatar_allowlist );
		?>
	</div><!-- .author-avatar -->

	<div class="author-heading">
		<h2 class="author-title"><?php esc_html_e( 'Published by', 'canard' ); ?></h2>
		<h3 class="author-name"><?php echo esc_html( get_the_author() ); ?></h3>
	</div><!-- .author-heading -->

	<p class="author-bio">
		<?php echo esc_html( get_the_author_meta( 'description' ) ); ?>
		<a class="author-link" href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
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
