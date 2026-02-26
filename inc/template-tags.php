<?php
/**
 * Custom template tags for this theme.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'canard_entry_categories' ) ) :
/**
 * Outputs HTML with meta information for the post categories.
 */
function canard_entry_categories() {
	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( __( ', ', 'canard' ) );
		if ( $categories_list && canard_categorized_blog() ) {
			printf( '<div class="entry-meta"><span class="cat-links">%1$s</span></div>', wp_kses_post( $categories_list ) );
		}
	}
}
endif;

if ( ! function_exists( 'canard_entry_meta' ) ) :
/**
 * Outputs HTML with meta information for the author, post date/time, and comments.
 */
function canard_entry_meta() {
	/**
	 * Filters the author bio avatar size.
	 *
	 * @param int $size The avatar height and width size in pixels.
	 */
	$author_bio_avatar_size = apply_filters( 'canard_author_bio_avatar_size', 20 );

	// Read the WP_User object once so we avoid two separate $authordata
	// dereferences for email and ID.
	$author = get_userdata( get_the_author_meta( 'ID' ) );

	// Cache get_avatar() output keyed on email + size. On archive pages with
	// multiple posts by the same author this replaces N gravatar lookups with 1.
	//
	// Security (multisite): prefix the cache key with the current blog ID so
	// that the same post/author ID on two different network sites cannot share
	// a cache entry when a non-blog-specific persistent cache backend is in use.
	$avatar_html = false;
	if ( $author ) {
		$avatar_cache_key = 'canard_avatar_' . get_current_blog_id() . '_' . md5( $author->user_email ) . '_' . $author_bio_avatar_size;
		$avatar_html      = wp_cache_get( $avatar_cache_key, 'canard_theme' );

		if ( false === $avatar_html ) {
			$avatar_html = get_avatar( $author->user_email, $author_bio_avatar_size );
			wp_cache_set( $avatar_cache_key, $avatar_html, 'canard_theme', HOUR_IN_SECONDS );
		} else {
			/*
			 * Security: the value was read from an external cache store (Redis /
			 * Memcached). In shared-keyspace or misconfigured multisite setups a
			 * poisoned entry could supply arbitrary HTML. Pass the cached value
			 * through wp_kses() with the img allowlist before use so that any
			 * injected markup is stripped before it reaches the byline string.
			 */
			$avatar_kses = array(
				'img' => array(
					'src'           => array(),
					'class'         => array(),
					'alt'           => array(),
					'width'         => array(),
					'height'        => array(),
					'loading'       => array(),
					'decoding'      => array(),
					'fetchpriority' => array(),
				),
			);
			$avatar_html = is_string( $avatar_html ) ? wp_kses( $avatar_html, $avatar_kses ) : false;
		}
	}

	$byline = sprintf( '<span class="author vcard">%1$s<a class="url fn n" href="%2$s">%3$s</a></span>',
		$avatar_html ?: '',
		esc_url( get_author_posts_url( $author ? $author->ID : 0 ) ),
		esc_html( get_the_author() )
	);

	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf( '<a href="%1$s" rel="bookmark">%2$s</a>', esc_url( get_permalink() ), $time_string );

	$allowed_meta_html = array(
		'span' => array( 'class' => array(), 'itemprop' => array() ),
		'a'    => array( 'class' => array(), 'href' => array(), 'rel' => array(), 'itemprop' => array(), 'property' => array() ),
		'time' => array( 'class' => array(), 'datetime' => array() ),
		'img'  => array(
			'src'           => array(),
			'class'         => array(),
			'alt'           => array(),
			'width'         => array(),
			'height'        => array(),
			'loading'       => array(),
			'decoding'      => array(),
			'fetchpriority' => array(),
		),
	);

	if ( is_single() && ( true === (bool) get_theme_mod( 'canard_author_bio' ) && get_the_author_meta( 'description' ) ) ) {
		echo wp_kses( '<span class="posted-on">' . $posted_on . '</span>', $allowed_meta_html );
	} else {
		echo wp_kses( '<span class="byline"> ' . $byline . '</span><span class="posted-on">' . $posted_on . '</span>', $allowed_meta_html );
	}

	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link( __( 'Leave a comment', 'canard' ), __( '1 Comment', 'canard' ), __( '% Comments', 'canard' ) );
		echo '</span>';
	}
}
endif;

if ( ! function_exists( 'canard_entry_footer' ) ) :
/**
 * Outputs HTML with meta information for the categories, tags, and comments.
 *
 * @uses canard_entry_meta() — disable via the canard_entry_footer_show_meta filter.
 */
function canard_entry_footer() {
	if ( apply_filters( 'canard_entry_footer_show_meta', true ) ) {
		canard_entry_meta();
	}

	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		the_tags( '<span class="tags-links">', esc_html__( ', ', 'canard' ), '</span>' );
	}

	edit_post_link( __( 'Edit', 'canard' ), '<span class="edit-link">', '</span>' );
}
endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function canard_categorized_blog(): bool {
	$cat_count = get_transient( 'canard_cat_count_v1' );

	if ( false === $cat_count ) {
		$results   = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );
		$cat_count = is_countable( $results ) ? count( $results ) : 0;
		// WEEK_IN_SECONDS TTL ensures stale data doesn't persist indefinitely
		// on sites without a persistent cache backend. The edit_category and
		// save_post hooks below still invalidate immediately on real changes.
		set_transient( 'canard_cat_count_v1', $cat_count, WEEK_IN_SECONDS );
	}

	return $cat_count > 1;
}

/**
 * Flushes the transient used in canard_categorized_blog() when categories change.
 *
 * @param int $post_id The ID of the post being saved, passed by the save_post hook.
 */
function canard_category_transient_flusher( int $post_id = 0 ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	/*
	 * Bug fix: the previous implementation called get_the_ID() here, which
	 * returns the global Loop post ID — not the post being saved. In the
	 * save_post admin context the Loop is not running, so get_the_ID() returned
	 * false or a stale value, making the wp_is_post_revision() guard unreliable.
	 *
	 * The save_post hook passes the post ID as its first argument. We accept it
	 * as $post_id and use that for the revision check.
	 *
	 * The edit_category hook passes the term ID, not a post ID. Passing a
	 * non-post integer to wp_is_post_revision() returns false (not a revision),
	 * which is the correct behaviour — category edits should always flush.
	 */
	if ( $post_id > 0 && wp_is_post_revision( $post_id ) ) {
		return;
	}
	delete_transient( 'canard_cat_count_v1' );
}
add_action( 'edit_category', 'canard_category_transient_flusher' );
add_action( 'save_post',     'canard_category_transient_flusher' );

/**
 * Adds featured image as background image to post navigation elements.
 *
 * @see wp_add_inline_style()
 */
function canard_post_nav_background() {
	if ( ! is_single() ) {
		return;
	}

	if ( ! canard_jetpack_featured_image_display() ) {
		return;
	}

	// Cache the generated CSS in the object cache so that the
	// wp_get_attachment_image_url() / get_post_thumbnail_id() calls (each a
	// get_post_meta() hit) are paid at most once per post per cache TTL.
	//
	// Security (multisite): prefix the key with the current blog ID to prevent
	// post ID collisions across sites on a shared persistent cache backend.
	$cache_key = 'canard_nav_bg_' . get_current_blog_id() . '_' . get_the_ID();
	$css       = wp_cache_get( $cache_key, 'canard_theme' );

	if ( false === $css ) {
		$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );
		$css      = '';

		if ( is_attachment() && $previous && 'attachment' === $previous->post_type ) {
			wp_cache_set( $cache_key, $css, 'canard_theme', HOUR_IN_SECONDS );
			return;
		}

		/*
		 * Security (IDOR): get_adjacent_post() returns password-protected posts
		 * (post_status = 'publish') to all visitors. If such a post has a
		 * featured image, its thumbnail URL would be injected as a visible CSS
		 * background-image before the visitor has entered the password — leaking
		 * the image without authentication. Skip the thumbnail for any adjacent
		 * post that requires a password.
		 */
		if ( $previous && ! post_password_required( $previous->ID ) && has_post_thumbnail( $previous->ID ) ) {
			$prev_url = wp_get_attachment_image_url( get_post_thumbnail_id( $previous->ID ), 'post-thumbnail' );
			$css .= '
				.post-navigation .nav-previous { background-image: url(' . esc_url( $prev_url ) . '); }
				.post-navigation .nav-previous .post-title, .post-navigation .nav-previous a:hover .post-title, .post-navigation .nav-previous .meta-nav { color: #fff; }
				.post-navigation .nav-previous a { background-color: rgba(0, 0, 0, 0.3); border: 0; text-shadow: 0 0 0.125em rgba(0, 0, 0, 0.3); }
				.post-navigation .nav-previous a:focus, .post-navigation .nav-previous a:hover { background-color: rgba(0, 0, 0, 0.6); }
				.post-navigation .nav-previous a:focus .post-title { color: #fff; }
			';
		}

		if ( $next && ! post_password_required( $next->ID ) && has_post_thumbnail( $next->ID ) ) {
			$next_url = wp_get_attachment_image_url( get_post_thumbnail_id( $next->ID ), 'post-thumbnail' );
			$css .= '
				.post-navigation .nav-next { background-image: url(' . esc_url( $next_url ) . '); }
				.post-navigation .nav-next .post-title, .post-navigation .nav-next a:hover .post-title, .post-navigation .nav-next .meta-nav { color: #fff; }
				.post-navigation .nav-next a { background-color: rgba(0, 0, 0, 0.3); border: 0; text-shadow: 0 0 0.125em rgba(0, 0, 0, 0.3); }
				.post-navigation .nav-next a:focus, .post-navigation .nav-next a:hover { background-color: rgba(0, 0, 0, 0.6); }
				.post-navigation .nav-next a:focus .post-title { color: #fff; }
			';
		}

		wp_cache_set( $cache_key, $css, 'canard_theme', HOUR_IN_SECONDS );
	}

	if ( $css ) {
		wp_add_inline_style( 'canard-style', $css );
	}
}

// Register the hook inside template_redirect so it is never added on archives,
// the front page, or search — removing a no-op call from wp_enqueue_scripts on
// every non-singular page load.
add_action( 'template_redirect', function() {
	if ( is_single() || is_attachment() ) {
		add_action( 'wp_enqueue_scripts', 'canard_post_nav_background' );
	}
} );
