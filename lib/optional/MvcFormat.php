<?php

/**
 * Formatting for Genesis and Other WordPress outputs
 * 
 * 
 * @since 10.2.13
 * 
 * @uses add_theme_support('mvc_format');
 */
 
class MvcFormat extends MvcFramework{
    
    //to tell the framework the sidebar has been specified elsewhere
    public $sidebar_changed = false; 
    
    
    /**
     * @since 10.2.13
     * @uses called at Bootstrap.php if theme supports it
     */
    function __construct(){
             
         //Filter the Search Form
        if( defined( 'SEARCH_TEXT' ) ){
            add_filter('genesis_search_text', array( $this, 'return_'.SEARCH_TEXT) );
        }
        if( defined( 'SEARCH_BUTTON_TEXT' ) ){
            add_filter('genesis_search_button_text', array( $this, 'return_'.SEARCH_BUTTON_TEXT) );
        }
        
        
          //Add the class 'first-class' to the first post
        add_filter( 'post_class',array( $this, 'first_post_class'), 0, 2);   
        
       //Add the special classes to the nav
        add_filter('wp_nav_menu_objects', array( $this, 'menu_classes') );
        
        //Changes the Sidebar for the Blog Pages
        add_action( 'wp', array( $this, 'blog_sidebar') );

        //Add a class matching the page name for styling - nice :)
        add_filter('body_class', array( $this, 'body_class' ) );
        
                //Add the meta Viewpoint for PHones
        if( current_theme_supports('mobile_responsive') ){
            if( $this->is_phone() ){
                add_action('genesis_meta', array( $this, 'metaViewPoint') );   
            }
        }
        
        
    }

    /**
     * Echos the meta viewpoint for Phones
     * @since 2.15.13
     * @uses call as is, will echo for you - probably in genesis_meta call
     * @uses automatically added when mobile_reponsiveness is turned on
     */
    function metaViewPoint(){
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    }
    

    
    /**
     * Changes all the sidebar on the Blog type "post" pages if a widget called "Blog Sidebar" exists
     * @uses create a widget area called 'Blog Sidebar' this will do the rest
     * @uses called by __construct();
     * @since 8.6.13
     */
    function blog_sidebar(){
        if( $this->sidebar_changed ) return;
        
        if(  genesis_site_layout() == 'full-width-content' ) return;
        
        if( mvc_dynamic_sidebar( 'Blog Sidebar', false ) ){
            if( $this->isBlogPage() ){
                remove_action( 'genesis_after_content', 'genesis_get_sidebar' );
                add_action( 'genesis_after_content', array( $this, 'sidebar_Blog_Sidebar') );
            }
        }
    }

    
    
        /**
     * Adds a class to the first and last item in every menu
     * @param array $items the menu Items
     * @return array
     * @uses called by Bootstrap::__construct()
     * @since 4.11.13
     */
    function menu_classes( $items ){
           $top_count = 1;
           while(next($items)){
                    $k = key($items); 
                    if( $items[$k]->menu_item_parent == 0 ){
                        $top_count++;  
                        $items[$k]->classes[] = 'item-count-'.$top_count;
                        //keep track of last menu item by setting it on each one
                        $last_menu_item = $k;
                    }
           }
            $items[$last_menu_item]->classes[] = 'last-menu-item';
            reset($items)->classes[] = 'first-menu-item';
            return $items;
    }
    
    
    
    /**
     * Add the 'first-post' class to the first post on any page
     * * Also adds and item-count class
     * @param array $classes existing classes for post
     * @return array
     * @uses called by __construct()
     * @since 8.1.13
     */
    function first_post_class( $classes ){
        global $post, $posts, $wp_query;
        if( ($wp_query->current_post === 0) || ($post == $posts[0]) ){
            $classes[] = 'first-post';
        }
        
        if( has_post_thumbnail() ){
            $classes[] = 'has-thumbnail';   
        }
        
        $classes[] = sprintf('item-count-%s', array_search($post, $posts)+1 );
        return $classes;
    }
    
    
    
    
}
