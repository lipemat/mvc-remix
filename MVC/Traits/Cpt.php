<?php

namespace MVC\Traits;

use MVC\Custom_Post_Type;

/**
 * Custom Post Type
 *
 * @author  Mat Lipe
 * @since   7/14/2015
 *
 * @package MVC\Traits
 */
trait Cpt {

	public $post_id;

	/**
	 * post
	 *
	 * @var \WP_Post
	 */
	private $post;

	//$current no longer exists due to memory considerations
	//You must use a separate global key

	
	public function __construct( $id ){
		$this->post_id = $id;
	}


	public function __get( $name ){
		_deprecated_function( '__get', '2.4.1', '$this->get_post()->{key}');
		return $this->{$name} = $this->get_post()->{$name};
	}


	public function get_id(){
		return $this->post_id;
	}

	/**
	 * Get the WP post from current context
	 *
	 * @return null|\WP_Post
	 */
	public function get_post(){
		if( empty( $this->post ) ){
			$this->post = get_post( $this->post_id );
		}

		return $this->post;
	}

	/********* static *******************/

	/**
	 * @var \Mvc\Custom_Post_type $cpt
	 */
	private static $cpt;


	/**
	 * register_post_type
	 *
	 * @static
	 *
	 * @uses \MVC\Custom_Post_Type
	 *
	 * @return void
	 */
	public static function register_post_type(){
		self::$cpt = new Custom_Post_Type( self::POST_TYPE );
	}


	/**
	 *
	 * @param int $post_id
	 *
	 * @static
	 *
	 * @return self
	 */
	public static function factory( $post_id ){
		return new self( $post_id );
	}


}