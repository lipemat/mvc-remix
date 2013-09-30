<?php    
            /**
             * These items can be altered, adjusted, commented in or out
             *  * This is where you can make changes to the framework when required
             *  @since 9.19.13
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



/** Removes the secondary sidebar - comment this out to use it **/
unregister_sidebar( 'sidebar-alt' );

/** Changes Search Form Values **/
define('SEARCH_BUTTON_TEXT','Search');
define('SEARCH_TEXT','Search this website');


add_theme_support( 'mvc_browsers' );
add_theme_support( 'mvc_widgets' );

//add_theme_support('mobile_responsive');
//add_theme_support('mobile_menu','dark'); //light or dark
//add_theme_support('category-images');
//add_theme_support('genesis-footer-widgets', 4 ); 
//add_theme_support('mvc_image_resize');

//When using the Slideshow
#add_image_size('slide-thumb','104','104',true);
#add_image_size('main-slide','440','330',true);



//remove_theme_support('genesis-inpost-layouts');

/** Unregister Layouts **/
//genesis_unregister_layout( 'full-width-content' );
//genesis_unregister_layout( 'content-sidebar' );
//genesis_unregister_layout( 'sidebar-content' );
//genesis_unregister_layout( 'content-sidebar-sidebar' );
//genesis_unregister_layout( 'sidebar-sidebar-content' );
//genesis_unregister_layout( 'sidebar-content-sidebar' );


add_filter( 'genesis_breadcrumb_args', 'child_breadcrumb_args' );
/**
* Modifys the breadcrumb display
 */              
function child_breadcrumb_args( $args ) {
                              $args['home']                    = 'Home';
                              $args['sep']                     = ' - ';
                              $args['list_sep']                = ', '; // Genesis 1.5 and later
                              $args['prefix'] = '<div class="breadcrumb">';
                              $args['suffix']  = '</div>';
                              $args['heirarchial_attachments'] = true; // Genesis 1.5 and later
                              $args['heirarchial_categories']  = true; // Genesis 1.5 and later
                              $args['display']                 = true;
                              $args['labels']['prefix']        = 'You are here: ';
                              $args['labels']['author']        = 'Archives for ';
                              $args['labels']['category']      = 'Archives for '; // Genesis 1.6 and later
                              $args['labels']['tag']           = 'Archives for ';
                              $args['labels']['date']          = 'Archives for ';
                              $args['labels']['search']        = 'Search for ';
                              $args['labels']['tax']           = 'Archives for ';
                              $args['labels']['404']           = 'Not found: '; // Genesis 1.5 and later
                              return $args;
}