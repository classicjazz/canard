<?php
/**
 * The template for displaying comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prevent comments from leaking content hints before a visitor authenticates.
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
				printf(
					_nx(
						'One thought on &ldquo;%2$s&rdquo;',
						'%1$s thoughts on &ldquo;%2$s&rdquo;',
						get_comments_number(),
						'comments title',
						'canard'
					),
					number_format_i18n( get_comments_number() ),
					'<span>' . esc_html( get_the_title() ) . '</span>'
				);
			?>
		</h2>

		<ol class="comment-list">
			<?php
				wp_list_comments( array(
					'avatar_size' => 60,
					'short_ping'  => true,
					'style'       => 'ol',
				) );
			?>
		</ol><!-- .comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
		<nav id="comment-nav-below" class="comment-navigation" aria-label="<?php esc_attr_e( 'Comment Navigation', 'canard' ); ?>">
			<!-- The aria-label on <nav> provides the accessible name for this landmark.
			     A redundant heading inside the nav has been replaced with <span> to
			     avoid creating a spurious heading in the document outline. -->
			<span class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'canard' ); ?></span>
			<div class="nav-previous"><?php previous_comments_link( __( 'Older Comments', 'canard' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments', 'canard' ) ); ?></div>
		</nav><!-- #comment-nav-below -->
		<?php endif; ?>

	<?php endif; ?>

	<?php
		if ( ! comments_open() && 0 !== (int) get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'canard' ); ?></p>
	<?php endif; ?>

	<?php
	/*
	 * Note: comment form field hardening (removing the URL field, setting
	 * type="email", adding autocomplete hints) is registered via
	 * add_filter( 'comment_form_default_fields', ... ) in inc/extras.php.
	 * It was previously registered here inside the template, which caused
	 * multiple filter registrations on pages that call comments_template()
	 * in a custom loop. Moving it to inc/extras.php ensures it registers
	 * exactly once per request.
	 */
	comment_form();
	?>

</div><!-- #comments -->
