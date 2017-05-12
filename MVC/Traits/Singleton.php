<?php
/**
 * Singleton.php
 * 
 * @author mat
 * @since 11/5/2014
 *
 * @package edspire-full
 */

namespace MVC\Traits;


trait Singleton {

	/**
	 * Singleton constructor.
	 *
	 * @todo Deprecate hooks being called from here because we should lazy load classes
	 *       
	 */
	public function __construct(){
		if( method_exists( $this, 'hooks' ) ){
			$this->hooks();
		} elseif( method_exists( $this, 'hook' ) ){
			$this->hook();
		}
	}

	//********** SINGLETON FUNCTIONS **********/

	/**
	 * Instance of this class for use as singleton
	 */
	protected static $instance;


	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init(){
		static::$instance = static::instance();
	}


	/**
	 * @deprecated in favor of instance()
	 */
	public static function get_instance(){
		return static::instance();
	}


	/**
	 *
	 * @static
	 *
	 * @return $this
	 */
	public static function instance(){
		if( !is_a( static::$instance, __CLASS__ ) ){
			static::$instance = new static();
		}

		return static::$instance;
	}
} 