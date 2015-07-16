<?php
/**
 * User.php
 *
 * @author  Mat
 * @since   7/14/2015
 *
 * @package MVC\Traits *
 */

namespace MVC\Traits;

trait User {

	/**
	 * user_id
	 *
	 * @var int
	 */
	public $user_id = null;

	/**
	 * current
	 *
	 * @static
	 * @var \Edspire\User
	 */
	public static $current;

	/**
	 * user
	 *
	 * @var \WP_User
	 */
	public $user;


	public function __construct( $user_id = null ){
		if( $user_id === null ){
			$user_id = get_current_user_id();
		}
		$this->user_id = $user_id;
		self::$current = $this;
	}


	/************ magic **************************/
	public function __get( $property ){
		$user = $this->get_user();

		return $user->{$property};
	}


	public function __call( $method, $args ){
		$user = $this->get_user();
		return call_user_func_array( array( $user, $method ), $args );
	}


	public function get_user(){
		if( empty( $this->user ) ){
			$this->user = get_user_by( 'id', $this->user_id );
		}
		return $this->user;
	}

}