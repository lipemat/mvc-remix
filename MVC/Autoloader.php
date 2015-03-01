<?php

namespace MVC;


/**
 * Autoloader
 *
 * Autoload class files from namespaced folders
 *
 * load a namespaced folder from root dir
 *
 * @example load a namespaced folder from root dir
 *          \MVC\Autoloader::add( "Products\\", __DIR__ . '/Products' );
 *

 *
 *
 * @author  Mat Lipe
 * @since   11/5/2014
 *
 * @package MVC
 */
class Autoloader {

	/**
	 * @var array
	 */
	private $prefixes = array();

	/**
	 * instance
	 *
	 * @var Autoloader
	 */
	private static $instance;


	public static function add( $prefix, $path ){
		$instance = self::get_loader();
		$instance->addPrefix( $prefix, $path );
	}


	/**
	 * @param string $prefix
	 * @param string $baseDir
	 */
	public function addPrefix( $prefix, $baseDir ){
		$prefix            = trim( $prefix, '\\' ) . '\\';
		$baseDir           = rtrim( $baseDir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
		$this->prefixes[ ] = array( $prefix, $baseDir );
	}


	/**
	 * @param string $class
	 *
	 * @return string|null
	 */
	public function findFile( $class ){
		$class = ltrim( $class, '\\' );

		foreach( $this->prefixes as $current ){
			list( $currentPrefix, $currentBaseDir ) = $current;
			if( 0 === strpos( $class, $currentPrefix ) ){
				$classWithoutPrefix = substr( $class, strlen( $currentPrefix ) );
				$file               = $currentBaseDir . str_replace( '\\', DIRECTORY_SEPARATOR, $classWithoutPrefix ) . '.php';
				if( file_exists( $file ) ){
					return $file;
				}
			}
		}
	}


	/**
	 * @param string $class
	 *
	 * @return bool
	 */
	public function loadClass( $class ){
		$file = $this->findFile( $class );
		if( null !== $file ){
			require $file;

			return true;
		}

		return false;
	}


	/**
	 * Registers this instance as an autoloader.
	 *
	 * @param bool $prepend
	 */
	public function register( $prepend = false ){
		spl_autoload_register( array( $this, 'loadClass' ), true, $prepend );
	}


	/**
	 * Removes this instance from the registered autoloaders.
	 */
	public function unregister(){
		spl_autoload_unregister( array( $this, 'loadClass' ) );
	}


	/**
	 * get_loader
	 *
	 * @static
	 *
	 * @return \MVC\Autoloader
	 */
	private static function get_loader(){
		if( empty( self::$instance ) ){
			self::$instance = new Autoloader();
			self::$instance->register( true );
		}

		return self::$instance;
	}

} 