<?php

/**
 * Formatting for Genesis and Other WordPress outputs
 * 
 * 
 * @since 10.2.13
 * 
 * @uses add_theme_support('mvc_format');
 */
 
trait MvcFormat{
    
    //to tell the framework the sidebar has been specified elsewhere
    public $sidebar_changed = false; 
    
    
        /**
     * Inits Formatting filters and hooks etc if currrent theme supports it
     * 
     * @since 10.2.13
     * @uses called from Bootstrap if theme supports it
     * 
     */
    function initFormats(){
             
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
        
        //Add Wraps the body for extra background
        add_action('genesis_before', array( $this, 'start_outabody') );
        add_action('genesis_after', array( $this, 'end_outabody' ) );  
    }
    

        /**
     * Opens divs which wrap the body for extra backgrounds
     * @uses called by __construct()
     * @since 9/21/12
     * @see end_outabody()
     */
    function start_outabody(){
        echo '<div id="outabody"><div id="outabody2"><div id="outabody3">';
    }
    
    /**
     * Closes divs which wrap the body for extra backgrounds
     * @uses called by __construct()
     * @since 9/21/12
     * @see start_outabody()
     */
    function end_outabody(){
        echo '</div></div></div>'; 
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
     * Changes the pages layout
     * @uses call this anytime before the get_head() hook
     * @uses - defaults to 'full-width-content'
     * @param string $layout - desired layout
     * @since 5.8.13
     *   *  'full-width-content'
     *   *  'content-sidebar' 
     *   *  'sidebar-content' 
     *   *  'content-sidebar-sidebar' 
     *   *  'sidebar-sidebar-content' 
     *   *  'sidebar-content-sidebar'
     */
    function change_layout( $layout = 'full-width-content' ){ 
        add_filter( 'genesis_pre_get_option_site_layout' , array( $this, 'return_'.$layout) );
    }
    
        /**
     * Outputs a Sidebar for Page or Posts for Whatever
     * Use widgetArea for a standard widget and this for a true sidebar
     * 
     * @param string $name of widget area
     * @param bool $echo defaults to true
     * @since 4.16.13
     */
    function sidebar($name, $echo = true){
        $output = '<div id="sidebar" class="widget-area sidebar '.self::slug_format_human($name).'">';
           $output .= mvc_dynamic_sidebar($name, false);
        $output .= '</div>';
        
        if( !$echo ) return $output;
        
        echo $output;
        
    }
    
   /**
     * Outputs a Widget Area By Name
     * Use sidebar for a true sidebar and this for a standard widget area 
    * 
     * @param string $name of widget area
     * @param bool $echo defaults to true
     * @since 4.16.13
     */
     function widgetArea($name, $echo = true){
        $output = '<div id="'.self::slug_format_human($name).'" class="widget-area">';
           $output .= mvc_dynamic_sidebar($name, false);
        $output .=  '</div>';
        
     
        if( !$echo ) return $output;
        
        echo $output;
        
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
    
        /**
     * Wraps all of the read more on the site with a div for styling
     * 
     * @uses must call before section is rendered which needs wraps 
     *
     * @since 3.82.0
     */
    function readMoreWrap(){        
        add_filter('the_content_more_link', array($this, 'readMoreWrapOutput') );
        add_filter('get_the_content_more_link', array($this, 'readMoreWrapOutput') );
    }
    

    /**
     * The newly wrapped output of the read more links
     * 
     * @uses used by self::readMoreWrap()
     * @param string $content
     * @return string
     */
    function readMoreWrapOutput($content){
        return '<div class="read-more-wrap">'.$content.'</div>';
    }
    
    
        /**
     * Removes the post meta and info from the output
     * @since 3.5.13
     * @uses can be called anywhere before the loop
     */
    function removePostData(){
        add_action('genesis_before_loop', array( $this,'removePostDataHooks') );
    }
    
    /**
     * Unhooks the genesis_post_info and genesis_post_meta 
     * @uses used by self::removePostData()
     * @uses could be called wherever you like as well but used by removePostData
     * @since 11.19.12
     */
    function removePostDataHooks(){
        remove_action( 'genesis_before_post_content', 'genesis_post_info' );
        remove_action('genesis_after_post_content', 'genesis_post_meta');
    }
    
    
        /**
     * Outputs the Tabbed Section
     * @uses used by Tabbed Template
     * @since 12.3.12
     * 
     * //TODO Make independent of the js file by adding $(#tabs) etc here
     */
    function tabsOutput(){
        global $printfriendly;
        /**
         * Remove the extras created by plugins we use
         */
        if( isset( $printfriendly ) ){
            remove_filter( 'the_content', array( $printfriendly, 'show_link' ) );
            remove_filter( 'the_excerpt', array( $printfriendly, 'show_link' ) );
            remove_action( 'wp_head', array( $printfriendly, 'front_head' ) );
        }
        remove_filter( 'the_content', 'auto_sociable' );
        
        
        //Filter for adding extra Tabs
        $extra_tabs = apply_filters('mat_extra_tabs', array('labels' => array(), 'content'=> array()) );
        
        
        echo '<div id="tabs">
            <ul>';
        
        /**
         * Echo all the labels for the tabs
         */
        $count = 0;
        for( $i = 1; $i <8; $i ++ ){
            if(get_field( 'tab_'. $i. '_content') != '' ){
                printf( '<li><a href="#tab%s">%s</a></li>', $i , get_field("tab_". $i. "_label") );
                $count++;
            }
        }
        //extra labels
        foreach( $extra_tabs['labels'] as $label ){
            $count++;
            printf( '<li><a href="#tab%s">%s</a></li>', $count , $label );
        }
        
        echo '</ul>' ;
        
        
        /**
         * Echo all the tabs contents
         */
        $count = 0;
        for( $i = 1; $i<8; $i++ ){
            if(get_field( "tab_".$i ."_content" ) != '' ){
                printf('<div id="tab%s">%s</div>' , $i, get_field( 'tab_'. $i. '_content') );
                $count++;
            }
        }
        //extra content
        foreach( $extra_tabs['content'] as $content ){
            $count++;
            printf('<div id="tab%s">%s</div>' , $count, $content);
        }
        
        echo  '</div><!-- end #tabs -->';
    }
    
        /**
     * Echos html post meta data list 
     * 
     * @param $fields the meta fields to output
     * @param string $format - the format to use with each item via printf
     *  * defaults to <li class="meta-item %s"><span>%s:</span>%s</li>
     * 
     * @uses defaults to the $meta_fields setup in the class
     * @return string|HTML
     * 
     * @since 6.3.13
     */
    function postMetaDataList($fields = array(), $format = '<li class="meta-item %s"><span>%s:</span>%s</li>'){
        echo $this->getPostMetaDataList($fields, $format);
    }
    
        /**
     * returns html post meta data list 
     * 
     * @param $fields the meta fields to output
     * @param string $format - the format to use with each item via printf
     *  * defaults to <li class="meta-item %s"><span>%s:</span>%s</li>
     * 
     * @uses defaults to the $meta_fields setup in the class
     * @return string|HTML
     * 
     * @since 5.15.13
     */
    function getPostMetaDataList($fields = array(), $format = '<li class="meta-item %s"><span>%s:</span>%s</li>'){
        global $post;
        if( empty($fields) && isset($this->meta_fields) ){
            $fields = $this->meta_fields;
        }
        ob_start();
        ?><ul class="post-meta-list"><?php
            foreach( $fields as $field ){
                $data = get_post_meta( $post->ID, $field, true );
                if( empty($data) ) continue;
                printf($format,
                            strtolower($field), $this->human_format_slug($field), $data );
                               
            }  
        ?></ul><?php
        
        return ob_get_clean();
    }
    
        /**
     * Move the sociable icons below any changes to the content
     * @since 11.2.12
     * @uses call before the content is loaded
     */
    function move_sociable(){
        if( function_exists( 'auto_sociable' ) ){
            remove_action( 'the_content', 'auto_sociable' );
            add_action( 'genesis_post_content', array( $this, 'sociable'), 99);
        }
    }
    
    /**
     * Outputs sociable where called
     * @since 11.2.12
     * @uses can be called anywhere 
     * * Used by move_sociable();
     */
    function sociable(){
        if( function_exists( 'auto_sociable' ) ){
            echo auto_sociable(null);
        }
    }
    
        /**
     * Creates the links for the calendar categories as images
     * @since 9/12/12
     * @uses call as is to display all categories as links that have matching images 
     * @uses images should be titled 'cat_%slug%.png'
     * @uses display and view all button if a 'view-all.png' exists
     *  * Images go in the standard theme images dir
     *  * Typically used in gridview.php file of the events calendar template
     */
    function events_category_display(){
        $terms = get_terms( "tribe_events_cat");
        $count = count( $terms);
        if ( $count > 0 ){
            echo '<ul id="events-category-icons">';
            foreach ( $terms as $term ) {
                echo "<li class=\"cat_". $term -> slug. "\"><a href='" .  get_site_url() . "/events/category/". $term-> slug. "'>
                <img src='" . MVC_IMAGE_URLECTORY . 'cat_' . $term->slug . ".png' class='events-cat-images'/></a></li>";
    
            }
            //add the view all button
            echo '<li class="cat_view_all"><a href="/events"><img src="' . MVC_IMAGE_URLECTORY . 'view-all.png" class="events-cat-images"/></a></li>' ;
    
            echo "</ul>";
        }
    
        ?>
    
        <!-- Make any categories that don't have pictures not show up. -->
        <script type= "text/javascript">
          jQuery(document).ready(function ($) {
    
              $( '.events-cat-images').each( function(){
                  this.onerror = function(){
                      this.style.display = "none" ;
                      }
                  });
    
              });
          </script>
    
        <?php
        
    }
    
}
