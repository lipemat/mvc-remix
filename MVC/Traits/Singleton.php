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
	private static $instance;


	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init(){
		self::$instance = self::get_instance();
	}


	/**
	 * Get (and instantiate, if necessary) the instance of the
	 * class
	 *
	 * @static
	 * @return $this
	 */
	public static function get_instance(){
		if( !is_a( self::$instance, __CLASS__ ) ){
			self::$instance = new self();
		}

		return self::$instance;
	}
} 