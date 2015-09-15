<?php

namespace MVC\Util;


/**
 * The internal taxonomy Class
 *
 * @see This must be constructed before the init hook loads!!!!!
 *
 * For interacting with the hidden internal taxonomy
 *
 * This is really just to solve the heavy meta query issue.
 * This may be used to reuse terms and keep queries faster.
 *
 * It is highly recommended you use this for checkboxes instead
 * of meta data which will allow for much faster queries later.
 *
 *
 * @example mvc_internal()->has_term( 'active' );
 *
 * @package Mvc Theme
 *
 *
 */
class Internal_Tax {
	use \MVC\Traits\Singleton;

	const TAXONOMY = 'internal';

	public $term_cache = array();

	
	/**
	 * Registers the Taxonomy upon the first call to
	 * self::get_instance() or self::init()
	 * 
	 */
	function __construct(){
		$tax               = new \MVC\Taxonomy( self::TAXONOMY );
		$tax->public       = false;
		$tax->show_ui      = false;
		$tax->hierarchical = true;
	}


	/**
	 * Assigns an Internal Term to a Post
	 *
	 * @since 9.25.13
	 *
	 * @param mixed int|obj|string $term - the term pretty much any way you want it
	 * @param int|obj [$post] - defaults to current global $post;
	 * @param array [$removeTerms] - terms to remove if post should be assigned to only one term in a group
	 *              will accept all terms including to one to be set due to event order
	 *
	 */
	function assignTerm( $term, $post = false, $removeTerms = array() ) {
		$post_id = $this->getPostId( $post );

		if( !empty( $removeTerms ) ){
			$terms = array();
			if( $_terms = wp_get_post_terms( $post_id, self::TAXONOMY ) ){
				foreach( $_terms as $k => $_term ){
					if( !in_array( $_term->name, $removeTerms ) ){
						$terms[] = $_term->term_id;
					}
				}
			}
			$term_id = $this->getTermId( $term );
			if( !empty( $term_id ) ){
				$terms[] = $term_id;
			}
			wp_set_object_terms( $post_id, (int)$term_id, self::TAXONOMY, false );

		} else {
			$term_id = $this->getTermId( $term );
			wp_set_object_terms( $post_id, (int)$term_id, self::TAXONOMY, true );
		}

	}


	/**
	 * Extract the post id from an object or string
	 *
	 * @param int|obj [$post] - (defaults to global $post );
	 *
	 * @since 8.21.13
	 */
	function getPostId( $post = false ) {

		$post = get_post( $post );

		return $post->ID;
	}


	/**
	 * Removes and Internal Term from a post
	 *
	 * @since 8.21.13
	 *
	 * @param mixed int|obj|string $term - the term pretty much any way you want it
	 * @param int|obj [$post] - defaults to current global $post;
	 *
	 */
	function removeTerm( $term, $post = false ) {

		$post_id = $this->getPostId( $post );
		$term_id = $this->getTermId( $term );

		$current_terms = wp_get_post_terms( $post_id, self::TAXONOMY, array( 'fields' => 'ids' ) );

		foreach( $current_terms as $current_term ){
			if( $current_term != $term_id ){
				$new_terms[ ] = intval( $current_term );
			}
		}

		return wp_set_object_terms( $post_id, $new_terms, self::TAXONOMY );
	}


	/**
	 * Checks if an internal term is set to a post
	 *
	 * @since 7.31.13
	 *
	 * @param mixed int|obj|string $term - the term pretty much any way you want it
	 * @param int|obj [$post] - defaults to current global $post;
	 *
	 * @return bool
	 */
	function hasTerm( $term, $post = false ) {

		$post = get_post( $post );

		$term_id = $this->getTermId( $term );

		return has_term( $term_id, self::TAXONOMY, $post->ID );
	}


	/**
	 * Toggle Terms
	 *
	 * Like a switch between two terms. If a post has the first one it will get the second one
	 * etc.
	 *
	 * @param string $term_one
	 * @param string $term_two
	 *
	 * @param WP_Post|int|false - defaults to global post
	 *
	 * @return void
	 *
	 */
	public function toggle_terms( $term_one, $term_two, $post = false ) {
		if( $this->hasTerm( $term_one, $post ) ){
			$this->removeTerm( $term_one, $post );
			$this->assignTerm( $term_two, $post );

		} else {
			$this->removeTerm( $term_two, $post );
			$this->assignTerm( $term_one, $post );
		}

	}


	/**
	 * Creates a checkbox using an internal term
	 *
	 * @since 7.31.13
	 *
	 * @param mixed int|obj|string $term - the term pretty much any way you want it
	 * @param bool  [$echo] to echo or return the checkbox
	 *
	 * @param int|obj [$post] - defaults to the current global post
	 */
	function checkbox( $term, $echo = true, $post = null ) {
		$post = get_post( $post );

		$term_id = $this->getTermId( $term );

		if( isset( $post->ID ) ){
			$checked = $this->hasTerm( $term, $post );
		} else {
			$checked = false;
		}

		$output = '<input type="hidden" name="tax_input[' . self::TAXONOMY . '][]" value="0">';

		$output .= sprintf( '<input value="%s" type="checkbox" name="tax_input[' . self::TAXONOMY . '][]" %s>', $term_id, checked( true, $checked, false ) );

		if( $echo ){
			echo $output;
		} else {
			return $output;
		}

	}


	/**
	 * Retrieves a Term id from the internal taxonomy
	 *
	 * @since 7.31.13
	 *
	 * @param mixed int|obj|string $term - the term pretty much any way you want it
	 *
	 * @uses  will create the term if not exists
	 *
	 * @return int the Id of the term
	 *
	 */
	function getTermId( $term ) {

		if( is_object( $term ) ){
			return $term->term_id;
		}
		if( is_numeric( $term ) ){
			return $term;
		}

		//If not an id or an object the term must be the name
		$termName = $term;

		if( isset( $this->term_cache[ $termName ] ) ){
			return $this->term_cache[ $termName ];
		}

	    if( $term = get_term_by( 'name', $termName, self::TAXONOMY ) ){
			return $this->term_cache[ $termName ] = $term->term_id;
		} else {
			$term = wp_insert_term( $termName, self::TAXONOMY );
			if( !is_wp_error( $term ) ){
				return $this->term_cache[ $termName ] = $term[ 'term_id' ];
			} else {
				if( !empty( $term->error_data[ 'term_exists' ] ) ){
					return $this->term_cache[ $termName ] = $term->error_data[ 'term_exists' ];
				}
				return false; //failed
			}
		}
	}

}
    
