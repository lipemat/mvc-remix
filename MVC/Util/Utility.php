<?php

namespace MVC\Util;

/**
 * Mvc Utility
 *
 * Utility Type Methods for interacting with data and such
 *
 * @author  Mat Lipe
 *
 * @example mvc_util()->arrayFilterRecursive
 *
 * @package MVC
 *
 */

class Utility {
	use \MVC\Traits\Singleton;

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
	 *
	 * @param array $arr
	 */
	public function arrayFilterRecursive( $arr ){
		$rarr = array();
		foreach( $arr as $k => $v ){
			if( is_array( $v ) ){
				$rarr[ $k ] = self::arrayFilterRecursive( $v );
			} else {
				if( !empty( $v ) ){
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
	public function stringToMysqlTimeStamp( $date ){
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
	public function MysqlTimeStampToString( $date, $format = 'm/d/Y' ){
		$timestamp = strtotime( $date );

		return date( $format, $timestamp );
	}


	/**
	 * Get how long ago a $post was posted
	 *
	 * @since
	 *
	 * @param WP_Post $post
	 *
	 * @return String or false on future date
	 *
	 */
	function getTimeAgo( $post ){
		$date   = get_post_time( 'G', true, $post );
		$chunks = array(
			array( 60 * 60 * 24 * 365, 'year', 'years', ),
			array( 60 * 60 * 24 * 30, 'month', 'months', ),
			array( 60 * 60 * 24 * 7, 'week', 'weeks', ),
			array( 60 * 60 * 24, 'day', 'days', ),
			array( 60 * 60, 'hour', 'hours', ),
			array( 60, 'minute', 'minutes', ),
			array( 1, 'second', 'seconds', )
		);

		if( !is_numeric( $date ) ){
			$time_chunks = explode( ':', str_replace( ' ', ':', $date ) );
			$date_chunks = explode( '-', str_replace( ' ', '-', $date ) );
			$date        = gmmktime( (int) $time_chunks[ 1 ], (int) $time_chunks[ 2 ], (int) $time_chunks[ 3 ], (int) $date_chunks[ 1 ], (int) $date_chunks[ 2 ], (int) $date_chunks[ 0 ] );
		}

		$current_time = current_time( 'mysql', $gmt = 0 );
		$newer_date   = strtotime( $current_time );

		// Difference in seconds
		$since = $newer_date - $date;

		if( 0 > $since ){
			return false;
		}

		for( $i = 0, $j = count( $chunks ); $i < $j; $i ++ ){
			$seconds = $chunks[ $i ][ 0 ];
			if( ( $count = floor( $since / $seconds ) ) != 0 ){
				break;
			}
		}

		$output = ( 1 == $count ) ? '1 ' . $chunks[ $i ][ 1 ] : $count . ' ' . $chunks[ $i ][ 2 ];
		if( !(int) trim( $output ) ){
			$output = '0 ' . 'seconds';
		}

		$output .= ' ago';

		return $output;
	}




}
