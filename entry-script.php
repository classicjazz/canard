<?php
if ( ( is_single() && ( ( has_post_thumbnail() && canard_jetpack_featured_image_display() ) && ( ! has_post_format() || has_post_format( 'aside' ) || has_post_format( 'image' ) || has_post_format( 'gallery' ) ) ) ) || ( is_page() && has_post_thumbnail() && canard_jetpack_featured_image_display() ) ) {
	?>
	<script>
		( function( $ ) {
			$( '.page .hentry.has-post-thumbnail .entry-header .entry-meta, .single .hentry.has-post-thumbnail .entry-header .entry-meta, .page .hentry.has-post-thumbnail .entry-header .entry-title, .single .hentry.has-post-thumbnail .entry-header .entry-title' )
				.wrapAll( '<div class="entry-header-inner" />' );
			$( '.entry-header-inner' ).wrap( '<div class="entry-header-wrapper" />' );
			$( '.page .hentry.has-post-thumbnail .entry-header, .single .hentry.has-post-thumbnail .entry-header' )
				.insertBefore( '.site-content-inner' )
				.addClass( 'entry-hero' );
		} )( jQuery );
	</script>
	<?php
}
