<?php

/**
 * Mvc Format
 * 
 * Formatting for Genesis and Other WordPress outputs
 * 
 * @package MvcTheme
 * 
 * @uses add_theme_support('mvc_format');
 * 
 */
if( class_exists('MvcFormat') ) return;  
class MvcFormat extends MvcFramework{
    
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
        if( current_theme_supports('mvc_mobile_responsive') ){
            if( $this->is_phone() ){
                add_action('genesis_meta', array( $this, 'metaViewPoint') );   
            }
        }
		
		add_action( 'genesis_before', array( $this, 'outabody_open' ) );
		add_action( 'genesis_after',  array( $this, 'outabody_close' ) );
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
     * 
	 * Unhooks the genesis_post_info and genesis_post_meta 
     * 
	 * @uses used by self::removePostData()
	 * 
     * @uses could be called wherever you like as well but used by removePostData
	 * 
	 * @return void
     */
    function removePostDataHooks(){
    	remove_action( 'genesis_entry_header',        'genesis_post_info', 12 );
		remove_action( 'genesis_entry_footer',        'genesis_post_meta'     );
        remove_action( 'genesis_before_post_content', 'genesis_post_info'     );
        remove_action( 'genesis_after_post_content',  'genesis_post_meta'     );
    }
	
	 /**
     * Change the sidebar to another widget area
     * 
     * @since 5.22.0
     * 
     * @param string $sidebar - Name of widget area
     * @uses must be called before the 'genesis_after_content' hook is run
     * 
     * @see Will not work if not using Genesis
     */
    function changeSidebar($sidebar){
          $this->sidebar_changed = true;
          
          remove_action( 'genesis_after_content', 'genesis_get_sidebar' );
          add_action( 'genesis_after_content', array( $this, 'sidebar_'.$sidebar) );    
        
    }
	
	
	/**
     * Change Layout
	 * 
	 * Changes the pages layout
	 * 
     * @uses call this anytime before the get_head() hook
     * @uses - defaults to 'full-width-content'
	 * 
     * @param string $layout - desired layout
     *   -  'full-width-content'
     *   -  'content-sidebar' 
     *   -  'sidebar-content' 
     *   -  'content-sidebar-sidebar' 
     *   -  'sidebar-sidebar-content' 
     *   -  'sidebar-content-sidebar'
	 * 
	 * @example may be used in the single() or before() hooks etc
	 * 
	 * @return void
     */
    function change_layout( $layout = 'full-width-content' ){
        $this->layout = $layout; 
        add_filter( 'genesis_site_layout' , array( $this, 'return_'.$layout) );
    }


	/**
	 * Outabody Open
	 * 
	 * Open up the bg divs
	 * 
	 * @return void
	 */
	function outabody_open(){
		?>
			<div id="outabody">
				<div id="outabody2">
					<div id="outabody3">
					<?php	
	}
	
	
	/**
	 * Outabody Close
	 * 
	 * Close the bg divs
	 * 
	 * @return void
	 */
	function outabody_close(){
					?>
							
					</div>
				</div>
			</div>
		<?php	
	}


   /** 
    * Get how long ago a $post was posted
    * 
    * @since
    * 
    * @param WP_Post $post
    * 
    * @return String or false on future date
    * 
    */
   function getTimeAgo($post) {
       $date = get_post_time('G', true, $post);
       $chunks = array(
            array( 60 * 60 * 24 * 365 ,  'year', 'years',  ),
            array( 60 * 60 * 24 * 30 ,  'month', 'months',  ),
            array( 60 * 60 * 24 * 7,  'week', 'weeks',  ),
            array( 60 * 60 * 24 ,  'day', 'days',  ),
            array( 60 * 60 ,  'hour', 'hours',  ),
            array( 60 ,  'minute', 'minutes',  ),
            array( 1,  'second', 'seconds',  )
        );
 
        if ( !is_numeric( $date ) ) {
            $time_chunks = explode( ':', str_replace( ' ', ':', $date ) );
            $date_chunks = explode( '-', str_replace( ' ', '-', $date ) );
            $date = gmmktime( (int)$time_chunks[1], (int)$time_chunks[2], (int)$time_chunks[3], (int)$date_chunks[1], (int)$date_chunks[2], (int)$date_chunks[0] );
        }
 
        $current_time = current_time( 'mysql', $gmt = 0 );
        $newer_date = strtotime( $current_time );
 
        // Difference in seconds
        $since = $newer_date - $date;
 
        if ( 0 > $since ) return false;

        for ( $i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            if ( ( $count = floor($since / $seconds) ) != 0 )
                break;
        }
 
        $output = ( 1 == $count ) ? '1 '. $chunks[$i][1] : $count . ' ' . $chunks[$i][2];
        if ( !(int)trim($output) ){
            $output = '0 ' . 'seconds';
        }
 
        $output .= ' ago';
 
        return $output;
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
     * @since 1.7.14
     */
    function blog_sidebar(){
        if( $this->sidebar_changed ) return;
        
        if( !function_exists('genesis_site_layout') ) return;
        
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
