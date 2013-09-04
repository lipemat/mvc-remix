<?php 
/**
 * Creates a Meta Data box, Creates the fields and handles the saving
 * @author Mat Lipe <mat@vimm.com>
 * @since 8.27.13
 * @uses The Class's Methods are already available in controllers and Models etc
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
     * Setup the Saving of meta box data
     * @since 1.13.13
     * @uses Called automagically at Bootstrap
     */
    function initMetaSave(){
        add_action('save_post', array( $this , 'meta_save' ) );
    }
    
    /**
     * Creates the Meta Box
     * @param obj $post the post object
     * @since 1.11.13
     */
    function meta_box( $post ){
        $type = $post->post_type;
        //For deprecation
        if( isset( $this->{$type.'_meta_fields'}) ){
            $this->meta_fields = $this->{$type.'_meta_fields'};
        }
        
        //if not fields, no need to be here
        if( !isset($this->meta_fields)) return;
        add_meta_box( $type.'_meta_box', self::human_format_slug($type) . ' Data ' , array( $this, 'meta_box_output' ), $type , 'normal' , 'high' );
    
    }
    
    /**
     * Creates the output for the meta box - Goes through the array with the %meta-name%_meta_Fields
     * @param obj $post
     * @param if key has check in it this will output a checkbox
     * @param if key has select in it will output a select using the array with same name as key
     * @param if the keys are set in select array they will become the selects values, otherwise the array values will become the selects values
     * @example array( 'select_state' => 'state' ) will look for an array $select_state
     * @example array( 'one','two', 'check_three' => 'three', 'select_state' => 'state' );
     * @example array( 'textarea_desc' => array( 'name' => string, ['mce' => bool] ) will output a text area
     * @example array( 'image_1' => 'Image Name') will create an image upload form
     * @since 7.10.13
     */
    function meta_box_output( $post ){
        $type = $post->post_type;
        //Get the proper class array 
        if( isset( $this->{$type .'_meta_fields'} ) ){
            $fields = $this->{$type .'_meta_fields'};
        } else {
            $fields = $this->meta_fields;
        }
        
        
        wp_nonce_field( plugin_basename( __FILE__ ), $type. '_meta_box', true );
    
        //Go through all the fields in the array
        foreach( $fields as $key => $field ){
            
            //Retrieve the field name
            if( is_array( $field ) ){
                 if( isset( $field['name'] ) ){
                    $field_name = $field['name'];
                 } else {
                    $field_name = $field;
                 }
            } else {
                 $field_name = $field;
            }
            
            
            echo '<li style="list-style: none; margin: 0 0 15px 10px">'; 
            if( is_array( $field ) ){
                echo self::human_format_slug($field['name']);
            } else {
                echo self::human_format_slug($field);
            }
            
                //Checkbox
                if( strpos($key,'check') !== false){
                    echo ': &nbsp; &nbsp; <input type="checkbox" name="' . $field . '" value="1" '. checked( get_post_meta( $post->ID , $field , true ), true, false ) . '/>';
                
                //Select Field  
                } elseif( strpos($key,'select') !== false ){
                    echo ': &nbsp; <select name="'. $field . '">';
                    
                       //Get this classes array with the same name as the key
                       $values_array = $this->{$key};
                
                        //To Determine if this is an associative array or not
                        $ass = ($values_array != array_values($values_array));
                    
                        //Go through the matching array
                        foreach( $values_array as $key => $value ){
                            if( $ass ){
                                //use the key as the value
                                printf( '<option value="%s" %s>%s</option>', $key, selected( get_post_meta($post->ID,$field,true), $key), $value );
                            } else {
                                //use the value as the value
                                printf( '<option value="%s" %s>%s</option>', $value, selected( get_post_meta($post->ID,$field,true), $value), $value );
                            }
                        }
                        echo '</select><!-- End ' . $field . ' -->';
                        
                //textarea field
                } elseif( strpos($key,'textarea') !== false ){
                    $this->MvcForm->textarea( $field_name, get_post_meta( $post->ID , $field_name, true ), array(), true, $field );
                    
                //Image Upload Form
                } elseif( strpos($key,'image') !== false ){
                               
                    $this->MvcForm->imageUploadForm( $field_name, get_post_meta( $post->ID , $field_name, true ) );
                                
                        
                //Standard Text Field   
                } else {
                    echo ': <input type="text" name="' . $field . '" value="'. htmlspecialchars(get_post_meta( $post->ID , $field , true )) . '" size="75"/>';
                }
            
            echo '</li>';

        }
    
    }
    
    
    /**
     * Saves the meta fields
     * @since 8.27.13
     */
    function meta_save($postId){
        global $post;
        
        //Make sure this is valid
        if( !isset( $post->post_type ) ) return;    
        $type = $post->post_type; 
         
        if ( wp_is_post_revision( $postId ) ) return;
        if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( !wp_verify_nonce( $_POST[$type.'_meta_box'], plugin_basename(__FILE__ ) ) ) return;
    
        //Go through the options extra fields
        if( isset( $this->meta_fields) ){
          foreach( $this->meta_fields as $field ){
              if( is_array( $field ) ){
                update_post_meta( $post->ID, $field['name'], $_POST[$field['name']] );
              } else {
                update_post_meta( $post->ID, $field, $_POST[$field ] );  
              }
          }
        }
    }
    
    
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
                'menu_icon' => get_bloginfo( 'stylesheet_directory' ) . '/images/'.$sanitizedTitle.'_icon.png',
                'has_archive'       => true,
                'show_in_menu'      => true, //Change this to a string for the menu to make this is submenu of
                'supports'      => array( 'title', 'editor', 'thumbnail', 'author', 'comments' , 'genesis-seo' , 'genesis-layouts' ,
                        'excerpt', 'trackbacks' , 'custom-fields' , 'comments' , 'revisions' ,'page-attributes',
                        'post-formats'  ),
                'register_meta_box_cb' => array( $this, 'meta_box' )
    
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
     */
    function plural_title( $title ){
    
        return'y' == substr($title,-1) ? rtrim($title, 'y') . 'ies' : $title . 's';
    
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