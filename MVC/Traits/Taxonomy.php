<?php

namespace MVC\Traits;

/**
 * Taxonomy
 *
 * @author  Mat
 * @since   7/24/2015
 *
 * @package MVC\Traits *
 */
trait Taxonomy {

	/**
	 * term_id
	 *
	 * @var int
	 */
	public $term_id;

	/**
	 * term
	 *
	 * @var object
	 */
	public $term = null;

	/**
	 * current
	 *
	 * @static
	 * @var self()
	 */
	static $current;


	public function __construct( $term_id ){
		$this->term_id = $term_id;
		self::$current = $this;
	}


	public function __get( $name ){
		$term = $this->get_term();
		if( isset( $term->$name ) ){
			return $this->{$name} = $term->$name;
		}

		return $this->{$name} = null;

	}


	public function get_term(){
		if( $this->term != null ){
			return $this->term;
		}
		$this->term = get_term( $this->term_id, self::TAXONOMY );

		return $this->term;

	}


	/***** Static *************/

	/**
	 * tax
	 *
	 * @static
	 * @var \MVC\Taxonomy $tax
	 */
	public static $tax;


	public static function register_taxonomy(){
		self::$tax = new \MVC\Taxonomy( self::TAXONOMY );
	}

}