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

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
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
			<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'canard' ); ?></h2>
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
	 * Security: harden the default comment form fields.
	 *
	 * 1. Remove the URL / website field. It is an unauthenticated free-text
	 *    field that is a primary spam vector and a stored-XSS surface if any
	 *    downstream template echoes the value without esc_url(). Canard does
	 *    not display commenter URLs anywhere in its templates, so the field
	 *    provides no user value.
	 *
	 * 2. Set type="email" on the email field. The HTML5 attribute triggers
	 *    native browser validation and helps password managers differentiate
	 *    the field from text inputs.
	 *
	 * 3. Add autocomplete hints so browsers can pre-fill the name and email
	 *    fields correctly without guessing.
	 *
	 * WordPress core handles nonce generation and verification for the comment
	 * submission form internally — no additional wp_nonce_field() call is
	 * needed here.
	 */
	add_filter( 'comment_form_default_fields', function( array $fields ): array {
		// Remove the website / URL field entirely.
		unset( $fields['url'] );

		// Harden the email field: set type="email" and add autocomplete.
		if ( isset( $fields['email'] ) ) {
			$fields['email'] = str_replace(
				array( 'type="text"', "type='text'" ),
				'type="email"',
				$fields['email']
			);
			// Add autocomplete="email" if not already present.
			if ( false === strpos( $fields['email'], 'autocomplete' ) ) {
				$fields['email'] = str_replace(
					'type="email"',
					'type="email" autocomplete="email"',
					$fields['email']
				);
			}
		}

		// Add autocomplete="name" to the author (name) field if present.
		if ( isset( $fields['author'] ) && false === strpos( $fields['author'], 'autocomplete' ) ) {
			$fields['author'] = str_replace(
				'id="author"',
				'id="author" autocomplete="name"',
				$fields['author']
			);
		}

		return $fields;
	} );

	comment_form();
	?>

</div><!-- #comments -->
