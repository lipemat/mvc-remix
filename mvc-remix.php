<?php
/*
Plugin Name: Mvc Remix
Plugin URI: https://github.com/lipemat/mvc-remix
Description: Turns any Genesis theme into a Model View Controller driven framework.
Author: Mat Lipe
Version: 2.1.0
Author URI: https://matlipe.com
*/

define( 'MVC_DIR', plugin_dir_path(__FILE__).'/' );
define( 'MVC_ASSETS_URL', plugin_dir_url(__FILE__).'/assets/' );
$dir_name = explode('/', plugins_url('', __FILE__));
define( 'MVC_DIR_NAME', end($dir_name) );
define( 'MVC_SLUG', MVC_DIR_NAME.'/'.basename(__FILE__));

require( 'MVC/Autoloader.php' );
\MVC\Autoloader::add( "MVC\\", __DIR__ . '/MVC' );
\MVC\Autoloader::add( "MVC\\", __DIR__ . '/Deprecated' );
spl_autoload_register( function( $class ){
	if( stripos( $class, 'MVC' ) !== false && file_exists( __DIR__ . '/Deprecated/' . $class . '.php' ) ){
		require( __DIR__ . '/Deprecated/' . $class . '.php' );
	}
});

require( 'template-tags.php' );

add_action( 'plugins_loaded', 'mvc_load', 99999999 );
function mvc_load(){
	static $loaded = false;
	if( $loaded ){
		return;
	}

	\MVC\Core\Bootstrap::init();
	$loaded = true;
}

