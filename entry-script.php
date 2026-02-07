<?php
/**
 * Defines the condition for the entry-hero body class.
 *
 * When a single post or page has a featured image that should be displayed,
 * the class 'has-entry-hero' is added to <body> via a body_class filter
 * registered in functions.php. The actual DOM manipulation (wrapping and
 * repositioning the entry header) is performed in single.js, which reads
 * this class to determine whether to act.
 *
 * entry-script.php is included via get_template_part() inside the Loop, so
 * only the function definition lives here. The add_filter() call is in
 * functions.php, which runs once at theme setup — this prevents the callback
 * from being registered multiple times on archive pages where the Loop
 * iterates over several posts.
 *
 * This approach avoids inline scripts, which are blocked by strict
 * Content Security Policy headers and bypass WordPress asset management.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'canard_entry_hero_body_class' ) ) :
/**
 * Adds the 'has-entry-hero' body class when the entry hero layout applies.
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function canard_entry_hero_body_class( $classes ) {
	if (
		( is_single() &&
			has_post_thumbnail() &&
			canard_jetpack_featured_image_display() &&
			( ! has_post_format() || has_post_format( 'aside' ) || has_post_format( 'image' ) || has_post_format( 'gallery' ) )
		) ||
		( is_page() && has_post_thumbnail() && canard_jetpack_featured_image_display() )
	) {
		$classes[] = 'has-entry-hero';
	}

	return $classes;
}
endif;
