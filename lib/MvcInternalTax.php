<?php
if( class_exists('MvcInternalTax') ) return;  

/**
 * The internal taxonomy Class
 * 
 * @uses for interacting with the hidden internal taxonomy
 * 
 * @uses This is really just to solve the heavy meta query issue. This may be used to reuse terms and keep queries faster. It  is highly recommended you use this for checkboxes instead of meta data which will allow for much faster queries later.
 * 
 * @example mvc_internal()->has_term( 'active' );
 * 
 * @package Mvc Theme
 * @class MvcInternalTax
 * 
 * @author Mat Lipe
 * 
 */
class MvcInternalTax extends MvcPostTypeTax{

     public $terms = array(); //The previously retrieved terms
     

    /**
     * @since 7.31.13
     * 
     * @uses registers the taxonomy and sets everything up
     */
    function __construct(){
       $this->register_taxonomy('internal');
    }       
    
    
    
    /**
     * Assigns an Internal Term to a Post
     * 
     * @since 9.25.13
     * 
     * @param mixed int|obj|string $term - the term pretty much any way you want it
     * @param int|obj [$post] - defaults to current global $post;
     * @param array [$removeTerms] - terms to remove if post should be assigned to only one term in a group 
     *      will accept all terms including to one to be set due to event order
     * 
     */
    function assignTerm($term, $post = false, $removeTerms = array() ){
         
        $post_id = $this->getPostId($post);

         if( !empty($removeTerms) ) {
            if( $terms = wp_get_post_terms($post_id, 'internal' ) ){
                foreach( $terms as $t ){
                    if( in_array( $t->name, $removeTerms ) ){
                         $this->removeTerm($t->term_id, $post_id);
                    }
                }   
            }
        }
        if( empty( $term ) ) return false;

        $term_id = $this->getTermId($term);
        return wp_set_post_terms($post_id, $term_id, 'internal', true);
    }
    
    
    /**
     * Extract the post id from an object or string
     * 
     * @param int|obj [$post] - (defaults to global $post );
     * 
     * @since 8.21.13
     */
    function getPostId( $post = false ){
    	
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
    function removeTerm($term, $post = false ){
        
        $post_id = $this->getPostId($post);
        $term_id = $this->getTermId($term);
        
        $current_terms = wp_get_post_terms( $post_id, 'internal', array( 'fields' => 'ids' ) );
 
        foreach ( $current_terms as $current_term ) {
            if ( $current_term != $term_id ){
                $new_terms[] = intval( $current_term );
            }
        }
 
        return wp_set_object_terms( $post_id, $new_terms, 'internal' );
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
    function hasTerm($term, $post = false ){
        
		$post = get_post( $post );
        
        $term_id = $this->getTermId($term);
        
        return has_term($term_id, 'internal', $postId );
    }
    
    
    
    /**
     * Creates a checkbox using an internal term
     * 
     * @since 7.31.13
     * 
     * @param mixed int|obj|string $term - the term pretty much any way you want it
     * @param bool [$echo] to echo or return the checkbox 
     * 
     * @param int|obj [$post] - defaults to the current global post
     */
    function checkbox($term, $echo = true, $post = null ){
        
		$post = get_post( $post );
        
        $term_id = $this->getTermId($term);
        
        if( isset( $post->ID ) ){
            $checked = $this->hasTerm($term, $post);  
        } else {
            $checked = false;
        }
        
        $output = '<input type="hidden" name="tax_input[internal][]" value="0">';
        
        $output .= sprintf('<input value="%s" type="checkbox" name="tax_input[internal][]" %s>', $term_id, checked(true, $checked, false) );

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
     * @uses will create the term if not exists
     * 
     * @return int the Id of the term
     * 
     */
    function getTermId($term){
        
        if( is_object($term) ){
            return $term->term_id;
        }
        if( is_numeric($term) ){
            return $term;   
        }
        
        //If not an id or an object the term must be the name
        $termName = $term;
        
        if( isset( $this->terms[$termName] ) ) return $this->terms[$termName];   
        
        if( $term = get_term_by('name', $termName, 'internal') ){
            return $this->terms[$termName] = $term->term_id;
        } else {

           $term = wp_insert_term($termName, 'internal'); 
           return $this->terms[$termName] = $term['term_id'];
        }
    }
	
	
	/********** SINGLETON FUNCTIONS **********/

	/**
	 * Instance of this class for use as singleton
	 */
	private static $instance;
		

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return self
	 */
	 public static function get_instance() {
		if ( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	   
}
    
