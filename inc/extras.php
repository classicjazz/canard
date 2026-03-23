<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'canard_body_classes' ) ) {
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * Currently appends 'group-blog' when the site has more than one published
	 * author, enabling author-specific layout adjustments in the stylesheet.
	 *
	 * @param array<int, string> $classes Classes already queued for the body element.
	 * @return array<int, string> The filtered classes array, potentially with 'group-blog' appended.
	 */
	function canard_body_classes( array $classes ): array {
		if ( is_multi_author() ) {
			$classes[] = 'group-blog';
		}

		return $classes;
	}
}
add_filter( 'body_class', 'canard_body_classes' );

if ( ! function_exists( 'canard_excerpt_more' ) ) {
	/**
	 * Replaces the default "[...]" auto-excerpt suffix with an HTML ellipsis entity.
	 *
	 * Hooked to excerpt_more on the front end only (see the add_filter call below).
	 *
	 * @since 2.0.0
	 * @param string $more The string appended to auto-generated excerpts (default "[...]").
	 * @return string An HTML ellipsis entity (&hellip;) with a leading space.
	 */
	function canard_excerpt_more( string $more ): string {
		return ' &hellip;';
	}
}

if ( ! is_admin() ) {
	add_filter( 'excerpt_more', 'canard_excerpt_more' );
}

if ( ! function_exists( 'canard_continue_reading' ) ) {
	/**
	 * Appends a "Continue reading" link to every auto-generated excerpt.
	 *
	 * Runs at priority 9 on the the_excerpt filter. The excerpt is passed through
	 * wp_kses_post() before the link is appended because earlier hooks (priority
	 * 1–8) may have injected plugin markup into $the_excerpt.
	 *
	 * @since 2.0.0
	 * @param string $the_excerpt The post excerpt as modified by earlier filter hooks.
	 * @return string The sanitized excerpt with a "Continue reading" link appended.
	 */
	function canard_continue_reading( string $the_excerpt ): string {
		// Fix 3.1 / 3.2: get_the_ID() returns false|int outside the Loop.
		// Guard the false branch before forwarding to get_permalink() and get_the_title().
		$post_id = get_the_ID();

		return sprintf( '%1$s <a href="%2$s" class="more-link">%3$s</a>',
			wp_kses_post( $the_excerpt ),
			esc_url( $post_id !== false ? get_permalink( $post_id ) : '' ),
			/* translators: %s: Name of current post */
			sprintf( __( 'Continue reading %s', 'canard' ), '<span class="screen-reader-text">' . esc_html( $post_id !== false ? get_the_title( $post_id ) : '' ) . '</span>' )
		);
	}
}

if ( ! is_admin() ) {
	add_filter( 'the_excerpt', 'canard_continue_reading', 9 );
}

/**
 * Sets a custom excerpt length of 65 words for all auto-generated excerpts.
 *
 * 65 words gives roughly two lines of body text at the default font size —
 * enough context to entice the reader without overflowing the archive card
 * layout. The $length parameter (WordPress default: 55) is intentionally
 * ignored; this function is the authoritative excerpt length for the theme.
 *
 * Child themes that need a different length should hook excerpt_length at a
 * lower priority (e.g. priority 998) so their value takes precedence.
 *
 * @param int $length The default word count supplied by WordPress (unused).
 * @return int Always returns 65.
 */
function canard_excerpt_length( int $length ): int {
	return 65;
}
add_filter( 'excerpt_length', 'canard_excerpt_length', 999 );

/**
 * Returns the most appropriate URL for a post, prioritizing external link-format URLs.
 *
 * Uses get_url_in_content() to retrieve the first URL found in post meta or
 * post content. The extracted URL is only trusted when the post uses the 'link'
 * post format; for standard posts the function falls back to the permalink so
 * that archive cards always link to the post itself rather than to an
 * incidentally leading link in the content.
 *
 * The extracted URL is validated with wp_http_validate_url() to reject any
 * non-HTTP/HTTPS scheme (e.g. javascript:, data:, mailto:) or malformed URL
 * before it is returned. A failed validation also falls through to
 * get_the_permalink().
 *
 * @return string A validated HTTP/HTTPS URL, or the post's canonical permalink.
 */
function canard_get_link_url(): string {
	$content   = get_the_content();
	$raw_url   = get_url_in_content( $content );

	/*
	 * Only trust the extracted URL when the post actually uses the link format.
	 * A standard post whose content happens to open with a link should fall
	 * through to get_the_permalink() so the card links to the post itself.
	 */
	$has_url = ( $raw_url && has_post_format( 'link' ) )
		? wp_http_validate_url( $raw_url )
		: false;

	// Fix 3.3: get_the_permalink() returns false|string; guard the false branch
	// so the declared string return type is always honoured.
	$permalink = get_the_permalink();

	return $has_url ?: ( $permalink !== false ? $permalink : '' );
}

/**
 * Hardens the default comment form fields.
 *
 * WordPress core handles nonce generation and verification for the comment
 * submission form internally — no additional wp_nonce_field() call is needed.
 *
 * @param array<string, string> $fields Associative array of default comment form fields keyed by field name.
 * @return array<string, string> The modified fields array.
 */
add_filter( 'comment_form_default_fields', function( array $fields ): array {
	// Remove the website/URL field entirely.
	unset( $fields['url'] );

	// Harden the email field: set type="email" and add autocomplete.
	if ( isset( $fields['email'] ) ) {
		// Fix 3.4: cast str_replace() return to string so that the result is
		// a guaranteed string before being passed to strpos() and a second
		// str_replace(). str_replace() returns array|string when its subject
		// is typed as mixed; the (string) cast narrows it for static analysis
		// and is a no-op at runtime because the subject is always a string here.
		$fields['email'] = (string) str_replace(
			array( 'type="text"', "type='text'" ),
			'type="email"',
			$fields['email']
		);
		// Add autocomplete="email" if not already present.
		if ( false === strpos( $fields['email'], 'autocomplete' ) ) {
			$fields['email'] = (string) str_replace(
				'type="email"',
				'type="email" autocomplete="email"',
				$fields['email']
			);
		}
	}

	// Add autocomplete="name" to the author (name) field if present.
	// Fix 3.5: same str_replace() narrowing as the email block above.
	if ( isset( $fields['author'] ) && false === strpos( $fields['author'], 'autocomplete' ) ) {
		$fields['author'] = (string) str_replace(
			'id="author"',
			'id="author" autocomplete="name"',
			$fields['author']
		);
	}

	return $fields;
} );
