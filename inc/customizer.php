<?php
/**
 * Canard Theme Customizer
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function canard_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	/*
	 * SECURITY NOTE FOR FUTURE CONTRIBUTORS:
	 * The Customizer API handles nonce verification for registered settings
	 * internally. However, any new AJAX endpoints or custom form submissions
	 * added to this theme MUST implement nonce verification:
	 *
	 *   - Output:  wp_nonce_field( 'canard_action', 'canard_nonce' )
	 *   - Verify:  check_ajax_referer( 'canard_action', 'canard_nonce' )
	 *
	 * Omitting nonce checks on AJAX handlers is the most common source of
	 * CSRF vulnerabilities in WordPress themes. Always add them proactively.
	 */

	/* Theme Options */
	$wp_customize->add_section( 'canard_theme_options', array(
		'title'    => __( 'Theme Options', 'canard' ),
		'priority' => 130,
	) );

	/* Author Bio */
	$wp_customize->add_setting( 'canard_author_bio', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'canard_author_bio', array(
		'label'             => __( 'Show author bio on single posts.', 'canard' ),
		'section'           => 'canard_theme_options',
		'priority'          => 10,
		'type'              => 'checkbox',
	) );
}
add_action( 'customize_register', 'canard_customize_register' );

/**
 * Enqueues the live-preview JS for the Theme Customizer.
 */
function canard_customize_preview_js() {
	wp_enqueue_script( 'canard-customizer', get_theme_file_uri( '/js/customizer.js' ), array( 'customize-preview' ), CANARD_VERSION, true );
}
add_action( 'customize_preview_init', 'canard_customize_preview_js' );
