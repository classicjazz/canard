<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
add_filter( 'body_class', 'canard_body_classes' );

if ( ! function_exists( 'canard_excerpt_more' ) ) :
/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis.
 *
 * @since Canard 2.0.0
 */
function canard_excerpt_more( $more ) {
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
 * @since Canard 2.0.0
 *
 * @param string $the_excerpt The post excerpt.
 * @return string The excerpt with a Continue reading link appended.
 */
function canard_continue_reading( string $the_excerpt ): string {
	/*
	 * Security: this filter runs at priority 9, before later excerpt filters
	 * have finished. The incoming $the_excerpt may contain markup injected by
	 * plugins hooked at priority 1–8. Pass it through wp_kses_post() before
	 * concatenating so that any injected content is sanitised to a safe
	 * HTML subset before it is returned to the_excerpt() callers.
	 */
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
	$has_url = ( $raw_url && has_post_format( 'link' ) )
		? wp_http_validate_url( $raw_url )
		: false;

	return $has_url ? $has_url : get_the_permalink();
}
