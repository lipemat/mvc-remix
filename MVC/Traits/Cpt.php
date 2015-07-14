<?php

namespace MVC\Traits;

/**
 * Cpt
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

	/**
	 * @var self
	 */
	static $current;

	/**
	 * @var \Mvc\Custom_Post_type $cpt
	 */
	private static $cpt;


	public function __construct( $id ){
		$this->post_id = $id;
		self::$current = $this;
	}


	public function __get( $name ){
		return $this->{$name} = $this->get_post()->{$name};
	}


	public function get_post(){
		if( empty( $this->post ) ){
			$this->post = get_post( $this->post_id );
		}

		return $this->post;
	}


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
		self::$cpt = new \MVC\Custom_Post_Type( self::POST_TYPE );
	}


}