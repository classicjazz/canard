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
	$the_excerpt = sprintf( '%1$s <a href="%2$s" class="more-link">%3$s</a>',
		$the_excerpt,
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
 * @return string URL
 */
function canard_get_link_url() {
	$content = get_the_content();
	$has_url = get_url_in_content( $content );

	return ( $has_url && has_post_format( 'link' ) ) ? $has_url : get_the_permalink();
}
