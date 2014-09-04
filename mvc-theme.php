<?php
/*
Plugin Name: Mvc Theme
Plugin URI: http://matlipe.com 
Description: Turns any Genesis theme into a Model View Controller driven framework.
Author: Mat Lipe
Version: 1.24.3

Author URI: http://matlipe.com
*/

define( 'MVC_DIR', plugin_dir_path(__FILE__).'/' );
define( 'MVC_ASSETS_URL', plugin_dir_url(__FILE__).'/assets/' );
$dir_name = explode('/', plugins_url('', __FILE__));
define( 'MVC_DIR_NAME', end($dir_name) );
define( 'MVC_SLUG', MVC_DIR_NAME.'/'.basename(__FILE__));

require( 'functions.php' );
require( 'template-tags.php' );


//Allow for autoloading framework Classes
spl_autoload_register('_mvc_autoload');

#-- Bring in the Framework
add_action( 'plugins_loaded', 'mvc_load', 99999999 );
function mvc_load(){
	new MvcBootstrap();
}

