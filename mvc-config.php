<?php
/**
 * These items can be altered, adjusted, commented in or out
 * Add the file to your theme and make changes there
 *
 *
 *  @author Mat Lipe <mat@matlipe.com>
 *
 */


if( !isset( $content_width ) ) {
	/**
	 * 01 Set width of oEmbed
	 * genesis_content_width() will be applied; Filters the content width based on
	 * the user selected layout.
	 *
	 * @see genesis_content_width()
	 * @param integer $default Default width
	 * @param integer $small Small width
	 * @param integer $large Large width
	 */
	$content_width = apply_filters( 'content_width', 600, 430, 920 );
}

/** Changes Search Form Values **/
define( 'SEARCH_BUTTON_TEXT', 'Search' );
define( 'SEARCH_TEXT', 'Search this website' );

/** Change to remove class prefix from views folder **/
define( "MVC_CONTROLLER_PREFIX", false );

add_theme_support( 'mvc_styles' );
add_theme_support( 'mvc_widgets' );
add_theme_support( 'mvc_secure' );
add_theme_support( 'mvc_format' );
add_theme_support( 'mvc_ajax'   );
add_theme_support( 'mvc_update' );
add_theme_support( 'mvc_cache'  );

//add_theme_support( 'mvc_route' );
//add_theme_support('mvc_mobile_responsive');
//add_theme_support('mvc_mobile_menu','dark'); //light or dark
//add_theme_support('mvc_category_images');
//add_theme_support('mvc_image_resize');
//add_theme_support('mvc_api');