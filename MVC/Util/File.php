<?php

namespace MVC\Util;

/**
 * File
 *
 * @author Mat Lipe
 * @since 4/7/2015
 *
 * @package MVC\Util
 */
class File {
	use \MVC\Traits\Singleton;

	/**
	 * get_mvc_dirs
	 *
	 * Retrieve the list of mvc_dirs based on theme, parent theme, and filter
	 *
	 * @static
	 *
	 * @return array|mixed|void
	 */
	public static function get_mvc_dirs(){
		static $dirs = array();
		if( !empty( $dirs ) ){
			return $dirs;
		}

		$dirs[ ] = trailingslashit( get_stylesheet_directory() );

		if( get_template_directory() != get_stylesheet_directory() ){
			$dirs[ ] = trailingslashit( get_template_directory() );
		}

		if( defined( "MVC_THEME_DIR" ) ){
			if( MVC_THEME_DIR != get_stylesheet_directory() && MVC_THEME_DIR != get_template_directory() ){
				$dirs[ ] = MVC_THEME_DIR;
			}
		}

		$dirs = apply_filters( 'mvc_theme_dirs', $dirs );

		return $dirs;

	}


	/**
	 * locate_template
	 *
	 * Check in each mvc_dir for a matching file
	 * Starts with the 0 key in the mvc_theme_dirs array which is typically the active theme
	 *
	 * @param array|string $path_relative_to_mvc_dir
	 * @param bool         $url - return the url ( defaults to false )
	 * @param bool $load - to include the file (defaults to false)
	 * @param string $directory - to check in a particular directory only
	 *
	 * @example 'View/Product/title.php'
	 *
	 * @todo Make this into an object
	 *
	 * @return bool|string - full path to file or false on failure to locate
	 */
	public function locate_template( $paths_relative_to_mvc_dir, $url = false, $load = false, $directory = false ){

		if( $directory ){
			$directories = (array)$directory;
		} else {
			$directories = self::get_mvc_dirs();
		}

		foreach( $directories as $dir ){

			$dir = untrailingslashit( $dir );

			foreach( (array) $paths_relative_to_mvc_dir as $path_relative_to_mvc_dir ){
				if( file_exists( $dir . '/' . $path_relative_to_mvc_dir ) ){
					if( $url ){
						$content_url = untrailingslashit( dirname( dirname( get_stylesheet_directory_uri() ) ) );
						$content_dir = str_replace( '\\', '/', untrailingslashit( dirname( dirname( get_stylesheet_directory() ) ) ) );
						$dir         = str_replace( '\\', '/', $dir );
						$dir         = str_replace( $content_dir, $content_url, $dir );
					}

					if( $load ){
						include( $dir . '/' . $path_relative_to_mvc_dir );

						return true;
					} else {
						return $dir . '/' . $path_relative_to_mvc_dir;
					}
				}
			}
		}

		return false;
	}
}