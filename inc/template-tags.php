<?php
/**
 * Custom template tags for this theme.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'canard_entry_categories' ) ) {
	/**
	 * Outputs the category list for the current post as an inline span.
	 *
	 * Emits a bare <span class="cat-links"> so it flows inline within .entry-meta
	 * alongside the byline and date spans without introducing block-level markup.
	 *
	 * Only rendered for the 'post' post type and only when the site has more than
	 * one non-empty category (see canard_categorized_blog()). Single-category sites
	 * omit this block to avoid redundant navigation.
	 *
	 * @return void
	 */
	function canard_entry_categories() {
		if ( 'post' === get_post_type() ) {
			/* Translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( __( ', ', 'canard' ) );
			if ( $categories_list && canard_categorized_blog() ) {
				printf( '<span class="cat-links">%1$s</span>', wp_kses_post( $categories_list ) );
			}
		}
	}
}

if ( ! function_exists( 'canard_entry_meta' ) ) {
	/**
	 * Outputs the author byline, post date, and comment count for the current post.
	 *
	 * On archive/index pages the date is formatted using the
	 * canard_entry_meta_date_format filter (default: 'M j, Y' — e.g. "Apr 22,
	 * 2019"). Only the publish date is shown on archive pages; the modified date
	 * is suppressed there to keep the meta line compact.
	 *
	 * On single posts the WordPress site date format is used unchanged, and both
	 * the publish and modified dates are shown when they differ. The author bio
	 * Customizer option suppresses the byline on single posts when an author-bio
	 * template is present.
	 *
	 * Avatar HTML is cached in the WordPress object cache keyed by blog ID, a hash
	 * of the author's email address, and the requested size to avoid redundant
	 * Gravatar lookups on archive pages with multiple posts by the same author.
	 * Values retrieved from the cache are passed through wp_kses() before use to
	 * guard against poisoned entries in shared persistent cache backends.
	 *
	 * @return void
	 */
	function canard_entry_meta() {
		/**
		 * Filters the author bio avatar size.
		 *
		 * @param int $size The avatar height and width in pixels.
		 * @return int The filtered avatar height and width in pixels.
		 */
		// Cast to int: apply_filters() returns mixed; get_avatar() requires int.
		$author_bio_avatar_size = (int) apply_filters( 'canard_author_bio_avatar_size', 20 );

		// Read the WP_User object once to avoid calling get_the_author_meta()
		// separately for email and ID.
		// Cast to int: get_the_author_meta( 'ID' ) returns string; get_userdata() requires int.
		$author = get_userdata( (int) get_the_author_meta( 'ID' ) );

		// Cache get_avatar() output keyed on email + size. On archive pages with
		// multiple posts by the same author this replaces N Gravatar lookups with 1.
		//
		// Security (multisite): prefix the cache key with the current blog ID so
		// that the same post/author ID on two different network sites cannot share
		// a cache entry when a non-blog-specific persistent cache backend is in use.
		$avatar_html = false;
		if ( $author ) {
			$avatar_cache_key = 'canard_avatar_' . get_current_blog_id() . '_' . md5( $author->user_email ) . '_' . $author_bio_avatar_size;
			$cached           = wp_cache_get( $avatar_cache_key, 'canard_theme' );

			if ( false === $cached ) {
				$avatar_html = get_avatar( $author->user_email, $author_bio_avatar_size );
				wp_cache_set( $avatar_cache_key, $avatar_html, 'canard_theme', (int) HOUR_IN_SECONDS );
			} else {
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
				$avatar_html = is_string( $cached ) ? wp_kses( $cached, $avatar_kses ) : false;
			}
		}

		$byline = sprintf( '<span class="author vcard">%1$s<a class="url fn n" href="%2$s">%3$s</a></span>',
			$avatar_html ?: '',
			esc_url( get_author_posts_url( $author ? $author->ID : 0 ) ),
			esc_html( get_the_author() )
		);

		/*
		 * Date formatting:
		 *
		 * On archive/index pages only the publish date is shown, abbreviated to a
		 * three-letter month (e.g. "Apr 22, 2019") to keep the meta line compact.
		 * The format is filterable via canard_entry_meta_date_format so child themes
		 * can override it without touching this function.
		 *
		 * On single posts the full behavior is retained: publish + modified dates
		 * using the site's configured date format.
		 */
		if ( ! is_single() ) {
			/**
			 * Filters the date format used in entry meta on archive and index pages.
			 *
			 * Defaults to 'M j, Y' (e.g. "Apr 22, 2019"). Accepts any format string
			 * valid for PHP's date() / WordPress's date_i18n().
			 *
			 * @param string $format A PHP date format string.
			 */
			// Cast to string: apply_filters() returns mixed; get_the_date() requires string.
			$date_format = (string) apply_filters( 'canard_entry_meta_date_format', 'M j, Y' );

			$pub_datetime = get_the_date( 'c' );
			$pub_display  = get_the_date( $date_format );

			$time_string = sprintf(
				'<time class="entry-date published updated" datetime="%1$s">%2$s</time>',
				esc_attr( false !== $pub_datetime ? (string) $pub_datetime : '' ),
				esc_html( false !== $pub_display  ? (string) $pub_display  : '' )
			);
		} else {
			$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
			if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
				$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
			}

			$pub_datetime  = get_the_date( 'c' );
			$pub_display   = get_the_date();
			$mod_datetime  = get_the_modified_date( 'c' );
			$mod_display   = get_the_modified_date();

			$time_string = sprintf( $time_string,
				esc_attr( false !== $pub_datetime ? (string) $pub_datetime : '' ),
				esc_html( false !== $pub_display  ? (string) $pub_display  : '' ),
				esc_attr( false !== $mod_datetime ? (string) $mod_datetime : '' ),
				esc_html( false !== $mod_display  ? (string) $mod_display  : '' )
			);
		}

		$permalink = get_permalink();
		$posted_on = sprintf(
			'<a href="%1$s" rel="bookmark">%2$s</a>',
			esc_url( false !== $permalink ? $permalink : '' ),
			$time_string
		);

		// Explicit allowlist rather than wp_kses_post() because the byline string
		// contains only these elements and we do not want to permit block-level markup.
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

		if ( is_single() && ( (bool) get_theme_mod( 'canard_author_bio' ) && (bool) get_the_author_meta( 'description' ) ) ) {
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
}

if ( ! function_exists( 'canard_entry_footer' ) ) {
	/**
	 * Outputs the entry footer: post meta, tags, and an edit link.
	 *
	 * Meta output is gated by the canard_entry_footer_show_meta filter, allowing
	 * child themes to suppress it without overriding this function. Tags are only
	 * rendered for the 'post' post type. The edit link is always rendered for
	 * users with sufficient capabilities.
	 *
	 * @uses canard_entry_meta()
	 * @return void
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
}

/**
 * Returns true if the blog has more than one non-empty category.
 *
 * Caches the result in a transient (canard_cat_count_v1) for up to one week as
 * a backstop; the transient is invalidated immediately by
 * canard_category_transient_flusher() whenever a category is edited or a post
 * is saved. Only two category IDs are fetched from the database since the
 * function only needs to distinguish "one" from "more than one."
 *
 * @return bool True when the site has 2 or more non-empty categories.
 */
function canard_categorized_blog(): bool {
	$cached_count = get_transient( 'canard_cat_count_v1' );

	if ( false === $cached_count ) {
		/** @var array<int, int> $results */
		$results   = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			'number'     => 2,
		) );
		$cat_count = is_countable( $results ) ? count( $results ) : 0;
		// Expire after a week as a backstop; the hooks below invalidate immediately on real changes.
		set_transient( 'canard_cat_count_v1', $cat_count, (int) WEEK_IN_SECONDS );
	} else {
		$cat_count = (int) $cached_count;
	}

	return $cat_count > 1;
}

/**
 * Flushes the transient used by canard_categorized_blog() when categories change.
 *
 * Hooked to both edit_category and save_post. Uses the $post_id argument passed
 * by save_post rather than get_the_ID(), which is unreliable outside the Loop.
 * The edit_category hook passes a term ID as $post_id; wp_is_post_revision()
 * correctly returns false for non-post integers so the early-return guard is safe.
 *
 * @param int $post_id The ID of the post being saved, or the term ID when called
 *                     from edit_category (both cases delete the transient).
 * @return void
 */
function canard_category_transient_flusher( int $post_id = 0 ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( $post_id > 0 && wp_is_post_revision( $post_id ) ) {
		return;
	}
	delete_transient( 'canard_cat_count_v1' );
}
add_action( 'edit_category', 'canard_category_transient_flusher' );
add_action( 'save_post',     'canard_category_transient_flusher' );

/**
 * Adds featured images as CSS background images to post navigation elements.
 *
 * Generates and registers inline CSS for .nav-previous and .nav-next when the
 * adjacent posts have featured images. Skips password-protected adjacent posts
 * to prevent leaking thumbnail URLs to unauthenticated visitors (IDOR guard).
 *
 * Generated CSS is cached in the WordPress object cache for one hour, keyed by
 * blog ID and post ID, to avoid redundant get_post_meta() calls on repeated
 * page views. The hook registration itself is deferred to template_redirect so
 * this function is never added to wp_enqueue_scripts on non-singular page loads.
 *
 * @see wp_add_inline_style()
 * @return void
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
	$current_post_id = get_the_ID();
	if ( false === $current_post_id ) {
		return;
	}
	$cache_key  = 'canard_nav_bg_' . get_current_blog_id() . '_' . $current_post_id;
	$cached_css = wp_cache_get( $cache_key, 'canard_theme' );

	if ( false === $cached_css ) {
		$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );
		$css      = '';

		// $previous from get_post() is WP_Post|array|null; from get_adjacent_post() is
		// string|WP_Post|null (empty string on no result). Normalise to WP_Post|null.
		if ( ! $previous instanceof WP_Post ) {
			$previous = null;
		}
		if ( is_string( $next ) ) {
			$next = null;
		}

		if ( is_attachment() && $previous instanceof WP_Post && 'attachment' === $previous->post_type ) {
			wp_cache_set( $cache_key, $css, 'canard_theme', (int) HOUR_IN_SECONDS );
			return;
		}

		if ( $previous instanceof WP_Post && ! post_password_required( $previous->ID ) && has_post_thumbnail( $previous->ID ) ) {
			$prev_thumb_id = get_post_thumbnail_id( $previous->ID );
			if ( false !== $prev_thumb_id ) {
				$prev_url = wp_get_attachment_image_url( $prev_thumb_id, 'post-thumbnail' );
				if ( false !== $prev_url ) {
					$css .= '
						.post-navigation .nav-previous { background-image: url("' . esc_url( $prev_url ) . '"); }
						.post-navigation .nav-previous .post-title, .post-navigation .nav-previous a:hover .post-title, .post-navigation .nav-previous .meta-nav { color: #fff; }
						.post-navigation .nav-previous a { background-color: rgba(0, 0, 0, 0.3); border: 0; text-shadow: 0 0 0.125em rgba(0, 0, 0, 0.3); }
						.post-navigation .nav-previous a:focus, .post-navigation .nav-previous a:hover { background-color: rgba(0, 0, 0, 0.6); }
						.post-navigation .nav-previous a:focus .post-title { color: #fff; }
					';
				}
			}
		}

		if ( $next instanceof WP_Post && ! post_password_required( $next->ID ) && has_post_thumbnail( $next->ID ) ) {
			$next_thumb_id = get_post_thumbnail_id( $next->ID );
			if ( false !== $next_thumb_id ) {
				$next_url = wp_get_attachment_image_url( $next_thumb_id, 'post-thumbnail' );
				if ( false !== $next_url ) {
					$css .= '
						.post-navigation .nav-next { background-image: url("' . esc_url( $next_url ) . '"); }
						.post-navigation .nav-next .post-title, .post-navigation .nav-next a:hover .post-title, .post-navigation .nav-next .meta-nav { color: #fff; }
						.post-navigation .nav-next a { background-color: rgba(0, 0, 0, 0.3); border: 0; text-shadow: 0 0 0.125em rgba(0, 0, 0, 0.3); }
						.post-navigation .nav-next a:focus, .post-navigation .nav-next a:hover { background-color: rgba(0, 0, 0, 0.6); }
						.post-navigation .nav-next a:focus .post-title { color: #fff; }
					';
				}
			}
		}

		wp_cache_set( $cache_key, $css, 'canard_theme', (int) HOUR_IN_SECONDS );
	} else {
		$css = is_string( $cached_css ) ? $cached_css : '';
	}

	if ( $css ) {
		wp_add_inline_style( 'canard-style', $css );
	}
}

/**
 * Conditionally registers canard_post_nav_background() on wp_enqueue_scripts.
 *
 * Deferred to template_redirect so the hook is registered only on singular
 * and attachment views, eliminating a no-op add_action call on archives,
 * the front page, and search result pages.
 *
 * @return void
 */
add_action( 'template_redirect', function() {
	if ( is_single() || is_attachment() ) {
		add_action( 'wp_enqueue_scripts', 'canard_post_nav_background' );
	}
} );
