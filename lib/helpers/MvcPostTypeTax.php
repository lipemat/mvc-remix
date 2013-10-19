<?php 
/**
 * Creates a Meta Data box, Creates the fields and handles the saving
 * @author Mat Lipe <mat@vimm.com>
 * @since 10.19.13
 * 
 * 
 * 
 * @uses Add class arrays named "meta_fields" = array();
 *      * if key has check in it this will output a checkbox
 *      * if key has select in it will output a select using the array with same name as key
 *      * if the keys are set in select array they will become the selects values, otherwise the array values will become the selects values
 *      * array( 'select_state' => 'state' ) will look for an array $select_state
 *      * array( 'one','two', 'check_three' => 'three', 'select_state' => 'state' );
 * @uses Be sure the set the callback of a custom post type to array( $this, meta_box ) or this will not create the meta fields
 * @package MVC
 * 
 * 
 * //TODO Add Another Row as a type of meta fields
 * @see Evernotes/Wordpress/Add Another Row ability to Meta Data Section
 * @see MvcForm::repeater
 * 
 * //TODO Ponder a way to add revision support to meta fields 
 * //TODO Consider switching this to use MvcMetaBox to only have one place to make updates
 * 
 */     
              
class MvcPostTypeTax{
    
    /**
     * Registers a post type with default values which can be overridden as needed.
     * @param $title the name of the post type
     * @param [$args] the arguments to overwrite
     * @example register_post_type( 'newtest' , array() );
     * @since 5.20.13
     *
     **/
    function register_post_type( $title, $args = array() ){

        $sanitizedTitle = sanitize_title( $title );
    
        if( isset( $args['singleTitle'] ) ){
            $title = $args['singleTitle'];
        } else {
            $title = ucwords( str_replace( '_', ' ', $sanitizedTitle ) );
        }
    
    
        //If the plural title is not set make it.
        $pluralTitle = isset( $args['pluralTitle'])? $args['pluralTitle']: $this->plural_title($title);
    
        $defaults = array(
                'labels' => array(
                        'name'                       => $pluralTitle,
                        'singular_name'              => $title,
                        'search_items'               => sprintf( 'Search %s', $pluralTitle ),
                        'popular_items'              => sprintf( 'Popular %s', $pluralTitle ),
                        'all_items'                  => sprintf( 'All %s' , $pluralTitle),
                        'parent_item'                => sprintf( 'Parent %s', $title ),
                        'parent_item_colon'          => sprintf( 'Parent %s:', $title ),
                        'edit_item'                  => sprintf( 'Edit %s', $title ),
                        'update_item'                => sprintf( 'Update %s' , $title ),
                        'add_new_item'               => sprintf( 'Add New %s' , $title),
                        'new_item_name'              => sprintf( 'New %s Name', $title ),
                        'separate_items_with_commas' => sprintf( 'Seperate %s with commas', $title ),
                        'add_or_remove_items'        => sprintf( 'Add or remove %s', $pluralTitle ),
                        'choose_from_most_used'      => sprintf( 'Choose from the most used %s', $pluralTitle ),
                        'view_item'                  => sprintf( 'View %s', $title ),
                        'add_new'                    => sprintf( 'Add New %s', $title ),
                        'new_item'                   => sprintf( 'New %s', $title ),
                        'menu_name'                  => $pluralTitle
                ),
                'menu_position'     => null,
                'public'            => true,
                'show_in_nav_menus' => true,
                'show_ui'           => true,
                'exclude_from_search'=> false,
                'show_tagcloud'     => false,
                'hierarchical'      => true,
                'query_var'         => $sanitizedTitle,
                'rewrite'           => array( 'slug' => $sanitizedTitle ),
                '_builtin'          => false,
                'menu_icon'         => null,
                'has_archive'       => true,
                'show_in_menu'      => true, //Change this to a string for the menu to make this is submenu of
                'supports'      => array( 'title', 'editor', 'thumbnail', 'author', 'comments' , 'genesis-seo' , 'genesis-layouts' ,
                        'excerpt', 'trackbacks' , 'custom-fields' , 'comments' , 'revisions' ,'page-attributes',
                        'post-formats'  ),
                'register_meta_box_cb' => null
    
        );
    
    
        //Make this keep the no overwritten default labels
        if( isset( $args['labels'] ) ){
            $defaults['labels'] = wp_parse_args( $args['labels'], $defaults['labels'] );
            unset( $args['labels'] );
        }
    
        $args = apply_filters('mvc_register_post_type_args', wp_parse_args( $args, $defaults ) );
    
        $postType = isset( $args['postType'] ) ? $args['postType'] : $sanitizedTitle;

        register_post_type( $postType, $args );
    
    }
    
    
    /**
     * Registers a taxonomy with default values which can be overridden as needed.
     * @param $title is the name of the taxonomy
     * @param $post_type the post type to link it to
     * @param $args an array to overwrite the defaults
     * @example register_taxonomy( 'post-cat', 'custom-post-type', array( 'pluralTitle' => 'lots of cats' ) );
     * @since 12.12.12
     */
    function register_taxonomy( $title, $post_type = '', $args = array() ){
    
        $sanitizedTaxonomy = sanitize_title( $title );
    
        if( isset( $args['singleTitle'] ) ){
            $title = $args['singleTitle'];
        } else {
            $title = ucwords( str_replace( '_', ' ', $sanitizedTaxonomy ) );
        }
    
        //If the plural title is not set make it.
        $puralTitle = isset( $args['pluralTitle'])? $args['pluralTitle']: $this->plural_title($title);
    
        $defaults = array(
                'labels' => array(
                        'name'                       => $puralTitle,
                        'singular_name'              => $title,
                        'search_items'               => sprintf( __( 'Search %s'                   , 'gmp' ), $puralTitle ),
                        'popular_items'              => sprintf( __( 'Popular %s'                  , 'gmp' ), $puralTitle ),
                        'all_items'                  => sprintf( __( 'All %s'                      , 'gmp' ), $puralTitle ),
                        'parent_item'                => sprintf( __( 'Parent %s'                   , 'gmp' ), $title      ),
                        'parent_item_colon'          => sprintf( __( 'Parent %s:'                  , 'gmp' ), $title      ),
                        'edit_item'                  => sprintf( __( 'Edit %s'                     , 'gmp' ), $title      ),
                        'update_item'                => sprintf( __( 'Update %s'                   , 'gmp' ), $title      ),
                        'add_new_item'               => sprintf( __( 'Add New %s'                  , 'gmp' ), $title      ),
                        'new_item_name'              => sprintf( __( 'New %s Name'                 , 'gmp' ), $title      ),
                        'separate_items_with_commas' => sprintf( __( 'Seperate %s with commas'     , 'gmp' ), $title      ),
                        'add_or_remove_items'        => sprintf( __( 'Add or remove %s'            , 'gmp' ), $puralTitle ),
                        'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'gmp' ), $puralTitle ),
                        'menu_name'                  => $puralTitle,
                ),
                'public'            => true,
                'show_in_nav_menus' => true,
                'show_ui'           => true,
                'show_tagcloud'     => false,
                'hierarchical'      => true,
                'query_var'         => $sanitizedTaxonomy,
                'rewrite'           => array( 'slug' => $sanitizedTaxonomy ),
                '_builtin'          => false,
                'show_admin_column' => true //Only works in 3.5
    
    
        );
    
        //Make this keep the no overwritten default labels
        if( isset( $args['labels'] ) ){
            $defaults['labels'] = wp_parse_args( $args['labels'], $defaults['labels'] );
            unset( $args['labels'] );
        }
    
        $args = wp_parse_args( $args, $defaults );
    
        $taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : $sanitizedTaxonomy;
    
        register_taxonomy( $taxonomy, $post_type, $args );
    
    
    }
    
    /**
     * Generates plural version of title
     *
     * @param string $title
     * @return string
     * 
     * @since 9.30.13
     */
    function plural_title( $title ){
       
       $end = substr($title,-1);
        if( $end == 's' ){
            return $title.'es';
        } elseif( $end == 'y' ){
            return rtrim($title, 'y') . 'ies';
        }
        
        return $title.'s';
    }
    
    /**
     * Returns a human readable slug with the _ remove and words uppercase
     * @param string $slug
     * @return string
     * @since 8/2/12
     */
    function human_format_slug( $slug ){
        return ucwords( str_replace( '_', ' ', $slug) );
    }
    
    
    
    
}