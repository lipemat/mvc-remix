<?php

/**
 * Misc Functions for MVC
 * 
 * @uses do not use in theme
 * 
 * @access internal
 * 
 * @author Mat Lipe
 *
 */

/**
 * Outputs data about a variable|object|array
 *
 * @param mixed $data - the data to display
 * @param bool  $hide - wraps the output in a display none
 * @param bool  $adminOnly - only displays in the admin
 */
if( !function_exists( '_p' ) ) {
	function _p($data, $hide = false, $adminOnly = false) {

		if( $adminOnly && !MVC_IS_ADMIN )
			return;

		if( $hide ) {
			echo '<div style="display:none">';
		}
		echo '<pre>';
		print_r( $data );
		echo '</pre>';

		echo '<pre>';
		$debug = debug_backtrace( false );
		$args = array_shift( $debug );
		unset( $args[ 'args' ] );
		print_r( $args );
		echo '</pre>';
		if( $hide ) {
			echo '</div>';
		}
	}


}

/**
 * Loads classes on the fly per needs only
 *
 * @uses added ot the spl_autoload_register() function by bootstrap.php
 * @uses will load a class from the main lib folder or the helpers folder
 *
 *
 */
function _mvc_autoload($class) {
	$parts = explode( '\\', $class );

	if( file_exists( MVC_DIR . 'lib/' . $class . '.php' ) ) {
		require (MVC_DIR . 'lib/' . $class . '.php');
		return;
	} elseif( file_exists( MVC_DIR . 'lib/helpers/' . $class . '.php' ) ) {
		require (MVC_DIR . 'lib/helpers/' . $class . '.php');
		return;
	} elseif( file_exists( MVC_DIR . 'lib/optional/' . $class . '.php' ) ) {
		require (MVC_DIR . 'lib/optional/' . $class . '.php');
		return;
	} 
    
	if( defined( 'MVC_THEME_DIR' ) && function_exists( 'apply_filters' ) ){
		$dirs = apply_filters( 'mvc_theme_dirs', MvcFramework::get_mvc_dirs() );
    	foreach( $dirs as $dir ){
			if( file_exists( $dir . 'meta-boxes/' . $class . '.php' ) ){
				require( $dir . 'meta-boxes/' . $class . '.php' );
			}
	 	}
	}

}
