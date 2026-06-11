<?php
/**
 * Canard Theme Customizer
 *
 * @package Canard
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds postMessage transport support and registers theme-specific Customizer settings.
 *
 * Switches the blogname, blogdescription, and header_textcolor settings to the
 * postMessage transport so the live preview can update them without a full page
 * refresh. Also registers the "Theme Options" section with a single "Show author
 * bio on single posts" checkbox setting.
 *
 * @param WP_Customize_Manager $wp_customize The active Customizer manager instance.
 * @return void
 */
function canard_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	$wp_customize->add_section( 'canard_theme_options', [
		'title'    => __( 'Theme Options', 'canard' ),
		'priority' => 130,
	] );

	$wp_customize->add_setting( 'canard_author_bio', [
		'default'           => '',
		'sanitize_callback' => 'wp_validate_boolean',
	] );
	$wp_customize->add_control( 'canard_author_bio', [
		'label'    => __( 'Show author bio on single posts.', 'canard' ),
		'section'  => 'canard_theme_options',
		'priority' => 10,
		'type'     => 'checkbox',
	] );
}
add_action( 'customize_register', 'canard_customize_register' );

/**
 * Enqueues the Customizer live-preview script.
 *
 * Loaded only inside the Customizer preview iframe via the customize_preview_init
 * action. The script depends on the core customize-preview handle so it is
 * deferred until after the preview frame is ready.
 *
 * @return void
 */
function canard_customize_preview_js() {
	wp_enqueue_script(
		'canard-customizer',
		get_theme_file_uri( '/js/customizer.js' ),
		[ 'customize-preview' ],
		CANARD_VERSION,
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);
}
add_action( 'customize_preview_init', 'canard_customize_preview_js' );
