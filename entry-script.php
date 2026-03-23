<?php
/**
 * Defines canard_entry_hero_body_class() for use as a body_class filter callback.
 *
 * When a single post or page has a featured image that should be displayed as
 * a hero banner, the class 'has-entry-hero' is added to <body> via a
 * body_class filter registered in functions.php. The DOM manipulation —
 * wrapping and repositioning the entry header — is then performed by
 * single.js, which reads this class to decide whether to act.
 *
 * This file is included via get_template_part() inside the Loop, so only the
 * function definition lives here. The add_filter() call is in functions.php,
 * which runs once at theme setup, preventing the callback from being
 * registered multiple times on archive pages where the Loop iterates over
 * several posts.
 *
 * This approach avoids inline scripts, which are blocked by strict Content
 * Security Policy headers and bypass WordPress asset management.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'canard_entry_hero_body_class' ) ) {
	/**
	 * Appends 'has-entry-hero' to the body class list when the entry hero layout applies.
	 *
	 * The class is applied on singular posts when all of the following are true:
	 * the post has a featured image, Jetpack's featured image display is enabled,
	 * and the post format is standard, aside, image, or gallery. It is also
	 * applied to pages that have a featured image with Jetpack display enabled.
	 * single.js reads this class to decide whether to rearrange the entry header
	 * into the hero banner.
	 *
	 * @param string[] $classes Existing body classes passed by WordPress.
	 * @return string[] Modified body classes, with 'has-entry-hero' appended when applicable.
	 */
	function canard_entry_hero_body_class( array $classes ): array {
		/*
		 * Guard against Jetpack being deactivated. canard_jetpack_featured_image_display()
		 * is defined in inc/jetpack.php, which is only loaded when Jetpack is active.
		 * Calling it without this check produces a fatal error when the plugin is absent.
		 */
		if ( ! function_exists( 'canard_jetpack_featured_image_display' ) ) {
			return $classes;
		}

		$has_displayable_thumbnail = has_post_thumbnail() && canard_jetpack_featured_image_display();

		$is_hero_post = is_single() && $has_displayable_thumbnail &&
			( ! has_post_format() || has_post_format( 'aside' ) || has_post_format( 'image' ) || has_post_format( 'gallery' ) );

		$is_hero_page = is_page() && $has_displayable_thumbnail;

		if ( $is_hero_post || $is_hero_page ) {
			$classes[] = 'has-entry-hero';
		}

		return $classes;
	}
}
