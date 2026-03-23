<?php
/**
 * Template for the site header.
 *
 * Outputs the complete HTML document opening: the <!DOCTYPE>, <html>, <head>
 * (including wp_head()), the opening <body>, the skip-link, the #page wrapper,
 * the #masthead site header (secondary nav, branding, optional header image,
 * primary nav, and search toggle), and the opening <div id="content">. The
 * corresponding closing tags are in footer.php.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'canard' ); ?></a>

	<header id="masthead" class="site-header">
		<?php if ( has_nav_menu( 'secondary' ) ) : ?>
			<div class="site-top">
				<div class="site-top-inner">
					<nav class="secondary-navigation" aria-label="<?php esc_attr_e( 'Secondary Navigation', 'canard' ); ?>">
						<?php
							wp_nav_menu( array(
								'theme_location'  => 'secondary',
								'depth'           => 1,
							) );
						?>
					</nav><!-- .secondary-navigation -->
				</div><!-- .site-top-inner -->
			</div><!-- .site-top -->
		<?php endif; ?>

		<div class="site-branding">
			<?php canard_the_site_logo(); ?>

			<?php
			/*
			 * Heading hierarchy: use <h1> for the site title on the front page and
			 * blog home, where no post title competes for the top-level heading slot.
			 * Use <p> on all other views (singular, archive, search, etc.) where the
			 * post or page title is the primary <h1>. This prevents duplicate <h1>
			 * elements and aligns with WCAG 2.4.6 and core theme best practice.
			 */
			/**
			 * HTML tag name used for the site title element.
			 *
			 * 'h1' on the front page and blog home; 'p' on all other views
			 * where a post or page title already occupies the top-level heading slot.
			 *
			 * @var string $site_title_tag
			 */
			$site_title_tag = ( is_front_page() || is_home() ) ? 'h1' : 'p';
			?>
			<<?php echo esc_html( $site_title_tag ); ?> class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a></<?php echo esc_html( $site_title_tag ); ?>>
			<p class="site-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
		</div><!-- .site-branding -->

		<?php if ( get_header_image() ) : ?>
			<div class="header-image">
				<div class="header-image-inner">
					<?php
					/*
					 * Security: Use get_header_image() + esc_url() rather than the
					 * header_image() template tag, which echoes internally and bypasses
					 * the escaping layer. Any javascript: or data: URI stored as the
					 * custom header would otherwise be reflected unescaped into the src
					 * attribute.
					 *
					 * The loading and fetchpriority values are ternary-controlled static
					 * strings, but are passed through esc_attr() so the pattern remains
					 * safe if the conditional is ever extended to read from a theme
					 * option or filter. WordPress VIP coding standards require esc_attr()
					 * on all attribute echoes without exception.
					 *
					 * Performance: get_custom_header() is called once and cached in a
					 * local variable to avoid redundant function calls for width/height.
					 */

					// Front page gets eager/high-priority loading to avoid an LCP penalty;
					// all other views defer loading and use default fetch priority.
					$header_loading   = is_front_page() ? 'eager' : 'lazy';
					$header_fetchprio = is_front_page() ? 'high'  : 'auto';
					$custom_header    = get_custom_header();
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> &#x2014; <?php esc_attr_e( 'Home', 'canard' ); ?>"><img src="<?php echo esc_url( get_header_image() ); ?>" width="<?php echo absint( $custom_header->width ); ?>" height="<?php echo absint( $custom_header->height ); ?>" loading="<?php echo esc_attr( $header_loading ); ?>" fetchpriority="<?php echo esc_attr( $header_fetchprio ); ?>"
				<?php
				/*
				 * Accessibility: alt="" is intentionally empty. The wrapping <a>
				 * has aria-label="Site Name — Home", which provides a complete
				 * accessible name for the link. Adding an alt description on the
				 * image itself would be redundant and would cause screen readers
				 * to announce the same text twice. WCAG 1.1.1 explicitly permits
				 * empty alt when the image is decorative within a labeled link.
				 */ ?>
				alt=""></a>
				</div><!-- .header-image-inner -->
			</div><!-- .header-image -->
		<?php endif; ?>

		<div id="search-navigation" class="search-navigation">
			<div class="search-navigation-inner">
				<?php if ( has_nav_menu( 'primary' ) ) : ?>
					<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'canard' ); ?>">
						<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><span class="screen-reader-text"><?php esc_html_e( 'Primary Menu', 'canard' ); ?></span></button>
						<?php wp_nav_menu( array( 'theme_location'  => 'primary', 'menu_id' => 'primary-menu' ) ); ?>
					</nav><!-- #site-navigation -->
				<?php endif; ?>
				<div id="search-header" class="search-header">
					<button class="search-toggle" aria-controls="search-form" aria-expanded="false"><span class="screen-reader-text"><?php esc_html_e( 'Search', 'canard' ); ?></span></button>
					<?php get_search_form(); ?>
				</div><!-- #search-header -->
			</div><!-- .search-navigation-inner -->
		</div><!-- #search-navigation -->
	</header><!-- #masthead -->

	<div id="content" class="site-content" tabindex="-1">
