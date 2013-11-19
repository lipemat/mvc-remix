<?php 
/**
 * Register a Post Type and/or taxonomy
 * 
 * @uses This fills in all the defaults for you
 * 
 * @author Mat Lipe <mat@matlipe.com>
 * 
 * @since 11.19.13
 * 
 * 
 * @package MVC
 * 
 * 
 */     
if( class_exists('MvcPostTypeTax') ) return;              
class MvcPostTypeTax{
    
    /**
     * Registers a post type with default values which can be overridden as needed.
     * @param $title the name of the post type
     * @param [$args] the arguments to overwrite
     * @example register_post_type( 'newtest' , array() );
     * @since 11.19.13
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
                        'menu_name'                  => $pluralTitle,
                        'all_items'                  => sprintf( 'All %s' , $pluralTitle),
                        'add_new'                    => sprintf( 'Add New %s', $title ),
                        'add_new_item'               => sprintf( 'Add New %s' , $title),
                        'edit_item'                  => sprintf( 'Edit %s', $title ),
                        'new_item'                   => sprintf( 'New %s', $title ),
                        'view_item'                  => sprintf( 'View %s', $title ),
                        'search_items'               => sprintf( 'Search %s', $pluralTitle ),
                        'not_found'                  => sprintf( 'No %s found', $pluralTitle ),
                        'not_found_in_trash'         => sprintf( 'No %s found in trash', $pluralTitle ),
                        'parent_item_colon'          => sprintf( 'Parent %s:', $title ),   
                ),
                
                
                'description'          => null, //describe me
                'public'               => true, //General publicly usable
                'exclude_from_search'  => false, //hide from search
                'publicly_queryable'   => true, //can reach on front end
                'show_ui'              => true, //admin CPT section
                'show_in_nav_menus'    => true, //custom menus
                'show_in_menu'         => true, //Change this to a string for the menu to make this is submenu of
                'show_in_admin_bar'    => true, //show in top bar
                'menu_position'        => null, //spot in admin menu
                'menu_icon'            => null, //icon for admin menu
                'capability_type'      => 'post', //custom caps map_meta_cap must be true
                'capabilities'         => false, //can specify custom caps - not needed in most cases because capability_type does this for you
                'map_meta_cap'         => true, //to use specified caps    
                'hierarchical'         => true,
                'supports'             => array( 'title', 'editor', 'thumbnail', 'author', 'comments' , 'genesis-seo' , 'genesis-layouts' ,'excerpt', 'trackbacks' , 'custom-fields' , 'comments' , 'revisions' ,'page-attributes','post-formats' ),
                'register_meta_box_cb' => null, //if a meta box generator shoudld be called
                'taxonomies'           => false, //taxonomies to assign to this post type
                'has_archive'          => true, //if can get a list by going to name               
                'rewrite'              => array( 
                                            'slug' => $sanitizedTitle, //first part
                                            'with_front' => true, //show slug in all urls
                                            'pages'      => true, //paginate ability?
                 ), //how the link should look
                 'query_var'           => $sanitizedTitle, //key show up in queries
                 'can_export'          => true, //can you export?
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
     * 
     * Registers a taxonomy with default values which can be overridden as needed.
     * 
     * @param $title is the name of the taxonomy
     * @param $post_type the post type to link it to
     * @param $args an array to overwrite the defaults
     * 
     * @example register_taxonomy( 'post-cat', 'custom-post-type', array( 'pluralTitle' => 'lots of cats' ) );
     * 
     * 
     * @since 11.19.13
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
                        'menu_name'                  => $puralTitle,
                        'all_items'                  => sprintf(  'All %s', $puralTitle ),
                        'edit_item'                  => sprintf( 'Edit %s', $title ),
                        'view_item'                  => sprintf( 'View %s', $title ),
                        'update_item'                => sprintf( 'Update %s', $title ),
                        'add_new_item'               => sprintf( 'Add New %s', $title ),
                        'new_item_name'              => sprintf( 'New %s Name', $title ),
                        'parent_item'                => sprintf( 'Parent %s', $title ),
                        'parent_item_colon'          => sprintf( 'Parent %s:', $title ),
                        'search_items'               => sprintf( 'Search %s', $puralTitle ),
                        'popular_items'              => sprintf( 'Popular %s', $puralTitle ),
                        'separate_items_with_commas' => sprintf( 'Separate %s with commas', $puralTitle ),
                        'add_or_remove_items'        => sprintf( 'Add or remove %s', $puralTitle ),
                        'choose_from_most_used'      => sprintf( 'Choose from the most used %s', $puralTitle ),
                ),
                'public'                => true, //general public nature
                'show_ui'               => true, //show admin ui
                'show_in_nav_menus'     => true, //custom menus
                'show_tagcloud'         => false, //if tagcloud widget may use this
                'show_admin_column'     => true, //show admin taxonomy columns
                'hierarchical'          => true, //allow for parents
                'update_count_callback' => false, //call a function when the _update_post_term_count is run
                'query_var'             => $sanitizedTaxonomy, //the key used in the query
                'rewrite'               => array( //how the urls will look
                                                'slug' => $sanitizedTaxonomy, //slug
                                                'with_front' => true, //show slug before link
                                                'hierarchical' => false, //show parents in url
                ),
                'capabilities'          => null, //can be array of custom capabilites
                'sort'                  => false, //remember the order terms are added to objects
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