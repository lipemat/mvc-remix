<?php
/*
Plugin Name: WP MVC
Plugin URI: http://matlipe.com 
Description: Turns any Genesis theme into a Model View Controller driven framework.
Author: Mat Lipe
Version: 1.0.0
Author URI: http://matlipe.com
*/


//Set some constants                             
define( 'MVC_THEME_URL', get_bloginfo( 'stylesheet_directory' ). '/' );
define( 'MVC_IMAGE_URL', MVC_THEME_URL.'images/');
define( 'MVC_JS_URL', MVC_THEME_URL.'js/' );
define( 'MVC_THEME_DIR', get_stylesheet_directory() . '/');
define( 'MVC_MOBILE_URL', MVC_THEME_URL.'mobile/');       
define( 'MVC_IS_ADMIN', MVC_IS_ADMIN() );

define( 'MVC_DIR', plugin_dir_path(__FILE__).'/' );


#-- Bring in the Framework
require( 'lib/Bootstrap.php' );
