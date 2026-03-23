<?php
/**
 * Template for displaying the comments section.
 *
 * Outputs the existing comment list with optional paginated navigation,
 * a "comments are closed" notice when appropriate, and the comment submission
 * form. Returns early without any output when the post is password-protected
 * and the visitor has not yet authenticated, preventing content leakage.
 *
 * Comment form field hardening (URL field removal, email input type, and
 * autocomplete hints) is registered via add_filter( 'comment_form_default_fields' )
 * in inc/extras.php rather than here, so it fires exactly once per request
 * regardless of how many times comments_template() is called.
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
			<span class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'canard' ); ?></span>
			<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'canard' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'canard' ) ); ?></div>
		</nav><!-- #comment-nav-below -->
		<?php endif; ?>

	<?php endif; ?>

	<?php
		if ( ! comments_open() && 0 !== (int) get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'canard' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>

</div><!-- #comments -->