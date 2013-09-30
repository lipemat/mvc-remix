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
define( 'THEME_DIR', get_bloginfo( 'stylesheet_directory' ). '/' );
define( 'IMAGE_DIR', THEME_DIR.'images/');
define( 'SCRIPT_DIR', THEME_DIR.'includes/');
define( 'JS_DIR', THEME_DIR.'js/' );
define( 'THEME_FILE_DIR', get_stylesheet_directory() . '/');
define( 'MOBILE_DIR', THEME_DIR.'mobile/');       
define( 'CSS_DIR', THEME_DIR.'lib/css' ); 
define( 'IS_ADMIN', is_admin() );




#-- Bring in the Framework
require( 'lib/Bootstrap.php' );
