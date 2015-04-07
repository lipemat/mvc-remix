<?php
/*
Plugin Name: Mvc Remix
Plugin URI: http://matlipe.com 
Description: Turns any Genesis theme into a Model View Controller driven framework.
Author: Mat Lipe
Version: 0.0.1

Author URI: http://matlipe.com
*/

define( 'MVC_DIR', plugin_dir_path(__FILE__).'/' );
define( 'MVC_ASSETS_URL', plugin_dir_url(__FILE__).'/assets/' );
$dir_name = explode('/', plugins_url('', __FILE__));
define( 'MVC_DIR_NAME', end($dir_name) );
define( 'MVC_SLUG', MVC_DIR_NAME.'/'.basename(__FILE__));

require( 'MVC/Autoloader.php' );
\MVC\Autoloader::add( "MVC\\", __DIR__ . '/MVC' );

require( 'template-tags.php' );

add_action( 'plugins_loaded', 'mvc_load', 99999999 );
function mvc_load(){
	\MVC\Core\Bootstrap::init();
}

