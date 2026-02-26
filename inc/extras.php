<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'canard_body_classes' ) ) :
/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function canard_body_classes( array $classes ): array {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	return $classes;
}
endif;
add_filter( 'body_class', 'canard_body_classes' );

if ( ! function_exists( 'canard_excerpt_more' ) ) :
/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis.
 *
 * @since 2.0.0
 */
function canard_excerpt_more( string $more ): string {
	return ' &hellip;';
}
endif;

if ( ! is_admin() ) {
	add_filter( 'excerpt_more', 'canard_excerpt_more' );
}

if ( ! function_exists( 'canard_continue_reading' ) ) :
/**
 * Appends a "Continue reading" link to all instances of the_excerpt.
 *
 * @since 2.0.0
 *
 * @param string $the_excerpt The post excerpt.
 * @return string The excerpt with a Continue reading link appended.
 */
function canard_continue_reading( string $the_excerpt ): string {
	// Sanitise before appending; this filter runs at priority 9 and earlier
	// hooks (priority 1–8) may have injected plugin markup into $the_excerpt.
	$the_excerpt = sprintf( '%1$s <a href="%2$s" class="more-link">%3$s</a>',
		wp_kses_post( $the_excerpt ),
		esc_url( get_permalink( get_the_ID() ) ),
		/* translators: %s: Name of current post */
		sprintf( __( 'Continue reading %s', 'canard' ), '<span class="screen-reader-text">' . esc_html( get_the_title( get_the_ID() ) ) . '</span>' )
	);
	return $the_excerpt;
}
endif;

if ( ! is_admin() ) {
	add_filter( 'the_excerpt', 'canard_continue_reading', 9 );
}

/**
 * Sets a custom excerpt length.
 *
 * @param int $length Default excerpt word count.
 * @return int
 */
function canard_excerpt_length( int $length ): int {
	// 65 words gives roughly two lines of body text at the default font size —
	// enough context to entice the reader without overflowing the archive card layout.
	// The $length parameter (WordPress default: 55) is intentionally ignored;
	// this function is the authoritative excerpt length for the theme.
	// Child themes that need a different length should hook excerpt_length at
	// a lower priority (e.g. priority 998) so their value takes precedence.
	return 65;
}
add_filter( 'excerpt_length', 'canard_excerpt_length', 999 );

/**
 * Returns the URL from the post.
 *
 * Uses get_url_in_content() to retrieve the URL in the post meta (if it exists)
 * or the first link found in the post content. Falls back to the post permalink
 * if no URL is found.
 *
 * Security: get_url_in_content() returns the raw href value from post content
 * without protocol validation. The result is passed through
 * wp_http_validate_url() to reject any non-HTTP/HTTPS scheme (e.g. javascript:,
 * data:, mailto:) before the URL is used in an href or passed to esc_url().
 * A false return from wp_http_validate_url() falls through to get_the_permalink()
 * so the link always resolves to a safe canonical URL.
 *
 * @return string URL
 */
function canard_get_link_url() {
	$content   = get_the_content();
	$raw_url   = get_url_in_content( $content );

	/*
	 * Validate that the extracted URL uses an HTTP or HTTPS scheme.
	 * wp_http_validate_url() returns false for javascript:, data:, and any
	 * other non-HTTP scheme, as well as for malformed URLs.
	 */
	// Only trust the extracted URL when the post actually uses the link format.
	// A standard post whose content happens to open with a link should fall
	// through to get_the_permalink() so the card links to the post itself.
	$has_url = ( $raw_url && has_post_format( 'link' ) )
		? wp_http_validate_url( $raw_url )
		: false;

	return $has_url ? $has_url : get_the_permalink();
}

/**
 * Hardens the default comment form fields.
 *
 * Previously registered inside comments.php (a template file). Registering a
 * filter inside a template causes multiple registrations on any page that calls
 * comments_template() more than once in a custom loop. Moving to inc/extras.php
 * ensures the filter registers exactly once per request.
 *
 * Changes applied:
 *   1. Removes the URL / website field — spam vector and potential XSS surface.
 *   2. Sets type="email" on the email field for native browser validation.
 *   3. Adds autocomplete hints so browsers can pre-fill name and email.
 *
 * WordPress core handles nonce generation and verification for the comment
 * submission form internally — no additional wp_nonce_field() call is needed.
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
