<?php    
            /**
             * These items can be altered, adjusted, commented in or out
             * Add the file to your theme and make changes there
             * 
             * 
             *  @since 10.2.13
             *  @author Mat Lipe <mat@matlipe.com>
             *  
             */

/** Set the Width of your full width content **/

//TODO Add an action on the 'template_redirect' hook to allow for changing per template
//TODO Create an image size which matches this like 'max-width-image' and remove full size image ability from the media uploader for image larger than this
//SEE http://core.trac.wordpress.org/ticket/21256
if ( ! isset( $content_width ) ){
    $content_width = 920;
}

/** Changes Search Form Values **/
define('SEARCH_BUTTON_TEXT','Search');
define('SEARCH_TEXT','Search this website');


add_theme_support( 'mvc_styles' );
add_theme_support( 'mvc_widgets' );
add_theme_support( 'mvc_secure' );
add_theme_support( 'mvc_format' );

//add_theme_support('mvc_mobile_responsive');
//add_theme_support('mvc_mobile_menu','dark'); //light or dark
//add_theme_support('mvc_category_images');
//add_theme_support('mvc_image_resize');

