<?php

/**
 * Mvc Utility
 * 
 * Utility Type Methods for interacting with data and such
 *
 * @author Mat Lipe
 * 
 * @example mvc_util()->arrayFilterRecursive
 * 
 * @package MVC
 *
 */

if( class_exists( 'MvcUtilites' ) )
	return;

class MvcUtilites {

	/**
	 * get_beanstalk_based_version
	 *
	 * Beanstalk adds a .revision file to deployments this grabs that
	 * revision and return it.
	 * If no .revison file available returns false
	 *
	 * @see lib/build/post-commit for the hook to use locally to increment the .revision and test
	 *
	 *
	 * @return bool|string
	 */
	public function get_beanstalk_based_version(){
		static $version = null;
		if( $version !== null ){
			return $version;
		}
		$version = false;

		$file = $_SERVER[ 'DOCUMENT_ROOT' ] . '/.revision';
		if( file_exists( $file ) ){
			$version = trim( file_get_contents( $file ) );
		}
		return $version;
	}


	/**
	 * Filters an array on every level
	 *
	 * @since 2.0
	 * @param array $arr
	 */
	public function arrayFilterRecursive($arr) {
		$rarr = array( );
		foreach( $arr as $k   => $v ) {
			if( is_array( $v ) ) {
				$rarr[ $k ] = self::arrayFilterRecursive( $v );
			} else {
				if( !empty( $v ) ) {
					$rarr[ $k ] = $v;
				}
			}
		}
		$rarr = array_filter( $rarr );
		return $rarr;
	}


	/**
	 * Coverts a string date to a Mysql Time Stamp
	 *
	 * @since 11.27.13
	 *
	 * @param string $date - the date string
	 *
	 * @return string
	 *
	 */
	public function stringToMysqlTimeStamp($date) {
		$timestamp = strtotime( $date );
		return date( "Y-m-d H:i:s", $timestamp );
	}


	/**
	 * Coverts Mysql Time Stamp to string Date
	 *
	 * @since 11.27.13
	 *
	 * @param string $date - the date string
	 *
	 * @return string
	 *
	 */
	public function MysqlTimeStampToString($date, $format = 'm/d/Y') {
		$timestamp = strtotime( $date );
		return date( $format, $timestamp );
	}


	/********** SINGLETON FUNCTIONS **********/

	/**
	 * Instance of this class for use as singleton
	 */
	private static $instance;

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return self
	 */
	public static function get_instance() {
		if( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


}
