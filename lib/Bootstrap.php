<?php

                            /**
                             * Pull the Whole Framework Together
                             * @since 9.30.13
                             * @author Mat Lipe
                             */



//Require the proper files                      
require('functions.php');


//TODO Find a better way to do this
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); //Bring in the admin plugin functions

//Allow for autoloading framework Classes
spl_autoload_register('_mvc_autoload()');




require( 'MvcPostTypeTax.php' );  //bring in the custom post type maker
require( 'MvcMenuIcons.php'); //add ability for menu icons
require( 'MvcMobileDetect.php' );  //bring in mobile detect ability
require( 'MvcFramework.php' ); //The main Class
require( 'MvcInternalTax.php' ); //Internal Taxonomy Interaction
require( 'MvcLogin.php' ); //Wordpress login enhancements
require( 'MvcMetaBox.php' ); //Easy Meta Box Generator

require('helpers/init.php');
require('widgets/init.php');
require('SimpleEdits.php');
require('MvcGallery.php');

if( file_exists(THEME_FILE_DIR.'Controller/Controller.php') ){
    //for portability
    define( 'IS_MVC', true );
    require(THEME_FILE_DIR.'Controller/Controller.php' );
    require(THEME_FILE_DIR.'Model/Model.php' );
} else {
    define( 'IS_MVC', false );
}


/** The Config for this Theme **/
if( file_exists(THEME_FILE_DIR.'Config/theme-config.php') ){
    include(THEME_FILE_DIR.'Config/theme-config.php');
}
define( 'IS_MOBILE_THEME', current_theme_supports('mobile_responsive') );  

//For the Category Icons
if( current_theme_supports('category-images') ){
    require( 'MvcCategoryImage.php' );
    $MvcCategoryIcons = new MvcCategoryImage;   
}
//For the Colapsible Mobile Menu
if( current_theme_supports('mobile_menu', 'color') ){
    require('MvcMobileMenu.php');
    $MvcMobileMenu = new MvcMobileMenu(); 
}

//For On the Fly Image Resize
if( current_theme_supports('mvc_image_resize') ){
    $MvcImageResize = new MvcImageResize();
    require( 'MvcImageResize.php' ); //On the fly image resizer
}


/** The functions that can be used **/
$MvcFramework = new MvcFramework();

/** Extra wraps for styling **/
add_theme_support( 'genesis-structural-wraps', array( 'header', 'nav', 'subnav', 'inner', 'footer-widgets', 'footer' ) );


/** Add Template Files **/
//TODO Make this independent of the Advanced Custom Fields Plugin
require_if_theme_supports( 'tabs',THEME_FILE_DIR.'lib/tabs.php' ); //Add the tabbed template

if( IS_MVC ){
$classes = array();
#-- Bring in the Files and Construct The Classes
foreach( scandir(THEME_FILE_DIR.'Controller') as $file ){
    if( !in_array( $file, array('.','..','Controller.php')) ){
          //Add the Controller
          require(THEME_FILE_DIR.'Controller/'.$file);
          $name = str_replace(array('Controller','.php'), array('',''), $file);

          if( in_array($name, array('Admin','admin') ) && !IS_ADMIN ) continue;
          
          $class = str_replace('.php', '', $file);
          ${$class} = new $class;
          
          //Add the Model
          require(THEME_FILE_DIR.'Model/'.$name.'.php');
          ${$class}->{$name} = new $name;

          //For the custom Post types
          ${$class}->initMetaSave(); 
          
          //Keep track of all of the controllers and models
          $classes[$class] = $name;          
          
          //Check if the new child class has a before and runs it if it does
          //has to be done this way to prevent recalling the Controller->before() over and over
          $reflect = new ReflectionClass($class);
          if( $reflect->getMethod('before')->getDeclaringClass()->getName() == $class ){
               add_action('wp', array( ${$class}, 'before' ) );
          }

         if( method_exists($class, 'single') ){
             $GLOBALS['MvcClassesWithSingle'][$name] = $class;
         }
         
         if( method_exists($class, 'archive') ){
             $GLOBALS['MvcClassesWithArchive'][$name] = $class;
         }
    }
}


#-- Setup and run the Global, Controller, Model, and View
//Had to do it this way because of requirements by the rest
$Controller = new Controller();
$Controller->Model = new Model();
if( method_exists($Controller, 'before') ){
    add_action('wp', array( $Controller, 'before' ) );
}

$classes['Controller'] = 'Controller';


/**
 * Setup model inheritance through
 * @uses set controller var "uses" to array() for the Models to make available
 */
foreach ($classes as $controller => $class) {
    if (isset(${$controller}->uses)) {
        foreach (${$controller}->uses as $model) {
            if( !in_array($model,$classes) ){
                require_once(THEME_FILE_DIR.'Model/'.$model.'.php');
            }
            ${$controller}->{$model} = new $model;
        }
    }
    //Run the init
    ${$controller}->init();
}

} //End if is MVC

/**
 * Put all the default stuff in motion
 * @since 7.19.13
 */
class Bootstrap extends MvcFramework{
    
    var $sidebar_changed = false; //to tell the framework the sidebar has been specified elsewhere
    
    function __construct(){
        
       //Allow for achive and single methods to work at the correct time
       add_action('wp', array( $this, 'singleAndArchiveMethods') );
        
       //Move the genesis meta boxe below our special ones
        remove_action( 'admin_menu', 'genesis_add_inpost_layout_box' );    
        add_action( 'do_meta_boxes', 'genesis_add_inpost_layout_box' ); 
        remove_action( 'admin_menu', 'genesis_add_inpost_seo_box' );     
        add_action( 'do_meta_boxes', 'genesis_add_inpost_seo_box' );  
             
        //Add Different Browser Stylesheet support
        add_action( 'init', array( $this, 'browser_support' ) );
        
        //Register Widgets from the theme's widget folder
        add_action('widgets_init', array( $this,'registerWidgets' ) );
        
        //Filter the Search Form
        add_filter('genesis_search_text', array( $this, 'return_'.SEARCH_TEXT) );
        add_filter('genesis_search_button_text', array( $this, 'return_'.SEARCH_BUTTON_TEXT) );
        
        
        //Wrap the content
        add_action( 'genesis_before_content', array( $this, 'wrap_topper') );
        add_action( 'genesis_after_content', array( $this, 'wrap_footer'), 1 );
        
        //Add the class 'first-class' to the first post
        add_filter( 'post_class',array( $this, 'first_post_class'), 0, 2);
        
        //Create the Vimm LInk Shortcode
        add_shortcode('vimm_link', array( $this, 'vimm_link_shortcode') );
        //Create the Vimm Sitemap Shortcode
        add_shortcode('vimm_sitemap_link', array( $this, 'vimmSitemapShortcode') );
        
        //Add the IE only Stylesheets
        add_action('wp_head', array( $this, 'ie_only'), 99 );
        
        //Add Wraps the body for extra background
        add_action('genesis_before', array( $this, 'start_outabody') );
        add_action('genesis_after', array( $this, 'end_outabody' ) );
        
        //Add stylesheet to editor
        add_filter('mce_css',array( $this, 'editor_style' ) );
        
        //Add Javascript to Site
        add_action( 'wp_enqueue_scripts', array( $this, 'add_js_css' ) );
        

        //Add Js and CSS to Admin
        add_action( 'admin_print_scripts', array( $this, 'admin_js' ) );
        
        //Add the special classes to the nav
        add_filter('wp_nav_menu_objects', array( $this, 'menu_classes') );
        
        //Changes the Sidebar for the Blog Pages
        add_action( 'wp', array( $this, 'blog_sidebar') );
        
        //Sets some defaults for Genesis Simple Edits
        self::simple_edits_settings();
        
        //Add a class matching the page name for styling - nice :)
        add_filter('body_class', array( $this, 'body_class' ) );

        //Add the meta Viewpoint for PHones
        if( current_theme_supports('mobile_responsive') ){
            if( $this->is_phone() ){
                add_action('genesis_meta', array( $this, 'metaViewPoint') );   
            }
        }
        
        //Add Dial Ability to Image Links
        add_filter( 'image_widget_image_link', array( $this, 'imageWidgetTelAllowed'),99,3 );
        
        // Tells you in the admin bar if mobile responive is tturned on
        add_action( 'admin_bar_menu', array( $this, 'adminBarResponsiveCheck'), 80 );  
        
    }


    /**
     * Runs the single and archive methods for the classes
     * 
     * @since 5.5.0
     * 
     * @since 8.1.13
     * 
     * @uses works off of the $GLOBALS set earlier in the file
     * 
     */
    function singleAndArchiveMethods(){
        $type = get_post_type();
        
        if( is_single() ){
            if( isset($GLOBALS['MvcClassesWithSingle']) ){
                foreach( $GLOBALS['MvcClassesWithSingle'] as $name => $class ){
                    if( strtolower($name) == $type ){
                        $GLOBALS[$class]->single();   
                    }  
                }
            }
         }
        
         if( is_page_template('page_blog.php') ){
             $type = 'post';
         }

         if( is_archive() || is_page_template('page_blog.php') ){
             if( isset($GLOBALS['MvcClassesWithArchive']) ){
                foreach( $GLOBALS['MvcClassesWithArchive'] as $name => $class ){
                    if( strtolower($name) == $type ){
                        $GLOBALS[$class]->archive();   
                    }  
                }
             }
         }
    }


    /**
     * Adds notifications to the admin Bar if we are using Mobile Responsive
     * 
     * @since 7.2.13
     * 
     * @author Tyler Steinhaus
     */
    function adminBarResponsiveCheck() {
        global $wp_admin_bar;
    
        if( !is_user_logged_in() || !is_super_admin() || !is_admin_bar_showing() ) {
            return;
        }
    
        if( current_theme_supports( 'mobile_responsive' ) ) {
            $wp_admin_bar->add_menu( array( 
                'id'            =>  'responsive_check',
                'title'         =>  __( 'Responsive' ),
                'href'          => ''
            ) );
        }
    }




    /**
     * Allow tel: links in the Image widget
     * 
     * @since 5.11.1
     */
    function imageWidgetTelAllowed($link, $args, $instance){
       $prot = array('tel','http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn' );
        
       $link = esc_url( $instance['link'], $prot ); 
        
       return $link;
    }

    /**
     * Registers the Widgets from the themes widgets folder
     * 
     * @since 3.0
     */
    function registerWidgets(){
        foreach( scandir(THEME_FILE_DIR.'widgets') as $widget ){
            if( $widget == '.' || $widget == '..') continue;
            require(THEME_FILE_DIR.'widgets/'.$widget);
            $widgets[] = str_replace('.php', '', $widget);
        }
        if( !isset( $widgets ) ) return;
        foreach ( $widgets as $widget ){
            register_widget($widget);
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
     * Adds a stylesheet with the same name as the browser if file exists
     * @uses create a stylesheet in the themes root with the same name as the browser
     *  * e.g. chrome.css
     * @uses added to the init hook by construct
     * @since 6.7.13
     */
    function browser_support(){
        foreach( array( 'is_chrome','is_gecko','is_safari','is_IE' ) as $browser ){
            global ${$browser};
            if( ${$browser} ){
                $this->browser = str_replace(array('is_','gecko'), array('','firefox'), $browser );
                add_action('wp_enqueue_scripts', array( $this, 'browser_style' ) );
            }
        }
        
    }
    
    /**
     * Adds a stylesheet of the matching browser if the stylesheet exists
     * @since 10.17.12
     * @uses called by browser_support() and used the class var $browser
     */
    function browser_style(){
        if( file_exists(THEME_FILE_DIR . $this->browser . '.css') ){
           wp_enqueue_style(
               $this->browser . '-child-css',
               THEME_DIR . $this->browser . '.css'
                       );
        }
    }
    
     
     
        /**
     * Creates an activation hook to change the default simple Links settings
     * @since 9/21/12
     * @uses called by __construct()
     */
    function simple_edits_settings(){
        $simple_edits_file = WP_PLUGIN_DIR . '/genesis-simple-edits/plugin.php';
        if( file_exists( $simple_edits_file ) && !is_plugin_active($simple_edits_file) ){
            register_activation_hook($simple_edits_file, array( $this, 'simple_edits_change') );
        }
        
    } 
     
     
     
   /**
     * Changes the default simple links settings
     * @since 9/21/12
     * @see simple_links_settings()
     * @uses called by simple_links_settings():
     * @example outputs the [vimm_link] shortcode so don't have to find it
     */
    function simple_edits_change(){
        $simple_edits_options = get_option( GSE_SETTINGS_FIELD );
        $simple_edits_options['footer_output_on'] = 1;
        $simple_edits_options['footer_output'] = '[vimm_link]';
        $simple_edits_options['footer_creds_text'] = '';
        $simple_edits_options['footer_backtotop_text'] = '';
        $simple_edits_options['post_meta'] ='[post_categories] [post_tags]';
        $simple_edits_options['post_info']='[post_date] By [post_author_posts_link] [post_comments] [post_edit]';
        update_option( GSE_SETTINGS_FIELD, $simple_edits_options );
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
     * Add the admin.js and admin.css file to the dashboard
     * @see the js and css dirs in the child theme
     * @uses called by construct()
     * @uses to change the local admin css use the filter 'mat-local-admin-css'
     * @since 8.1.13
     */
    function admin_js(){
        
       //Some Styling to let you know you are local 
       if( $_SERVER['SERVER_ADDR'] == '127.0.0.1' ){
           ob_start();
           ?><style type="text/css">
               #adminmenuback,#adminmenuwrap{background-color:#df01d7;background-image:-ms-linear-gradient(180deg,#04b431,#df01d7);background-image:-moz-linear-gradient(180deg,#619bbb,#df01d7);background-image:-o-linear-gradient(180deg,#619bbb,#df01d7);background-image:-webkit-linear-gradient(180deg,#619bbb,#df01d7);background-image:linear-gradient(180deg,#619bbb,#df01d7)}a,#adminmenu a,#the-comment-list p.comment-author strong a,#media-upload a.del-link,#media-items a.delete,.plugins a.delete,.ui-tabs-nav a{color:black}#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,#adminmenu li.current a.menu-top,.folded #adminmenu li.wp-has-current-submenu,.folded #adminmenu li.current.menu-top,#adminmenu .wp-menu-arrow,#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head{background-color:#777;background-image:-ms-linear-gradient(bottom,#610b5e,#3b0b17);background-image:-moz-linear-gradient(bottom,#610b5e,#3b0b17);background-image:-o-linear-gradient(bottom,#610b5e,#3b0b17);background-image:-webkit-linear-gradient(bottom,#610b5e,#3b0b17);background-image:linear-gradient(bottom,#610b5e,#3b0b17);transform:rotate(30deg)}#adminmenu li.wp-has-current-submenu{-moz-box-shadow:0 0 2px 2px black;-webkit-box-shadow:0 0 2px black;box-shadow:0 0 2px 2px black}
             </style>
           <?php 
           echo apply_filters('mvc-local-admin-css', ob_get_clean() );
       }
        
        if( file_exists(THEME_FILE_DIR.'js/admin.js') ){
             wp_enqueue_script(
                    'mvc-admin-js',
                    JS_DIR. 'admin.js',
                    array('jquery' )
             );
             $dirs = array( 'IMG'      => IMAGE_DIR,
                       'THEME'    => THEME_DIR,
                       'INCLUDES' => SCRIPT_DIR,
                       'LOADING_ICON' => IMAGE_DIR.'loading.gif' );
         
             wp_localize_script('mvc-admin-js', 'DIRS', $dirs);
        
            //to localize stuff
            do_action('mvc_admin_js', 'mvc-admin-js');
             
        }
        
        if( file_exists(THEME_FILE_DIR.'admin.css') ){
            wp_enqueue_style(
                'mvc-admin-styles',
                THEME_DIR . 'admin.css' //The location of the style
            );
        }
        
         
    }
    
    
    
    
        
    
    /**
     * Add the child.js file to the site
     * @since 8.1.13
     * @uses called by __construct()
     * @uses also add a global js variable with includes the commonly used dirs called 'DIR'
     * @uses adds the mobile stylesheets if defined in the theme config
     * 
     */
    function add_js_css(){
        
        //Cue some scripts
        wp_register_script( 'slides', THEME_DIR.'lib/js/registered/slides.js', array('jquery'));
        wp_register_script( 'jcarousel', THEME_DIR.'lib/js/registered/jcarousel.js', array('jquery'));
        
        if( file_exists(THEME_FILE_DIR.'js/child.js') ){
            wp_enqueue_script(
                'mvc-child-js',
                JS_DIR. 'child.js',
                array('jquery' )
            );
         
            $dirs = array( 'IMG'      => IMAGE_DIR,
                       'THEME'    => THEME_DIR,
                       'INCLUDES' => SCRIPT_DIR,
                       'LOADING_ICON' => IMAGE_DIR.'loading.gif' );
         
            wp_localize_script('mvc-child-js', 'DIRS', $dirs);
        
            //to localize stuff
            do_action('mvc_child_js', 'mvc-child-js');
        }
        
        
        //Add the mobile Style if required
        if( current_theme_supports('mobile_responsive') ){
              if( file_exists(THEME_FILE_DIR.'mobile/mobile-responsive.css') ){
                wp_enqueue_style(
                    'mvc-mobile-styles',
                    MOBILE_DIR . 'mobile-responsive.css' //The location of the style
                );
              }


            //Add the mobile script or the non mobile script based on device
            if( !self::is_mobile() ){
               if( file_exists(THEME_FILE_DIR.'mobile/desktop.js') ){
                    wp_enqueue_script(
                    'mvc-non-mobile-script',
                    MOBILE_DIR. 'desktop.js',
                    array('jquery')
                    );

                }
            } else {

                //Add the tablet stuff
                if( self::is_tablet() ){
                    wp_enqueue_style(
                        'mvc-tablet-styles',
                        MOBILE_DIR . 'tablet.css' //The location of the style
                    );


                    if( file_exists(THEME_FILE_DIR.'mobile/tablet.js') ){
                        wp_enqueue_script(
                            'mvc-tablet-script',
                            MOBILE_DIR. 'tablet.js',
                            array('jquery')
                        );
                    }
                }

                //Add the phone stuff
                if( self::is_phone() ){
                    wp_enqueue_style(
                        'mvc-phone-styles',
                        MOBILE_DIR . 'phone.css' //The location of the style
                    );


                    if( file_exists(THEME_FILE_DIR.'mobile/phone.js') ){
                        wp_enqueue_script(
                            'mvc-phone-script',
                            MOBILE_DIR. 'phone.js',
                            array('jquery')
                        );
                    }
                }
            } //-- End if mobile device

        } //-- End if Mobile Responsive Theme Support
  
    }



    

    
    
    /**
     * Adds the style.css file to the editor
     * @param string $wp exiting editor styles
     * @return string
     * @since 9/21/12
     * @uses called by __construct()
     */
    function editor_style($wp){
        $wp .= ',' . get_bloginfo('stylesheet_url');
        return $wp;
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
     * Add stylesheets that Targer Specific version of IE
     * @uses create a file named ie.css, ie8.css, or ie7.css and this will do the rest
     * 
     * @actions ie-head, ie9-head, ie8-head, ie7-head
     * @since 6.10.13
     */
    function ie_only(){
        
        //ie 10 only
        if( strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 10' ) ){
            if( file_exists(THEME_FILE_DIR . 'ie10.css') ){
                do_action('ie-head');
                printf( '<link rel="stylesheet" type="text/css" href="%sie10.css" /><![endif]-->', THEME_DIR );
            }
        } 
                
        //ie 9 only
        if( file_exists(THEME_FILE_DIR . 'ie9.css') ){
            echo '<!--[if IE 9]>';
            do_action('ie9-head');
            printf( '<link rel="stylesheet" type="text/css" href="%sie9.css" /><![endif]-->', THEME_DIR );
        }
        
        //ie 8 only
        if( file_exists(THEME_FILE_DIR . 'ie8.css') ){
            echo '<!--[if IE 8]>';
            do_action('ie8-head');
            printf('<link rel="stylesheet" type="text/css" href="%sie8.css" /><![endif]-->', THEME_DIR );
        }
        
        //ie 7 only
        if( file_exists(THEME_FILE_DIR . 'ie7.css') ){
            echo '<!--[if IE 7]>';
            do_action('ie7-head');            
            printf( '<link rel="stylesheet" type="text/css" href="%sie7.css" /><![endif]-->', THEME_DIR );
        }
        
    }
    
    
    /**
     * Ouputs a Shortcode Linking to the Sitemap Page
     * * @param array $atts the arguments for the shortcode
       * @uses Supported shortcode attributes are:
      * *  url (The links url ),
      * *  link (The text of the link
      *   @example [vimm_sitemap_link link="sitemap"]
     * 
     * 
      * @filters  apply_filters( 'vimm_sitemap_link', $output, $atts );
     * 
     * @since 4.2.0
     */
    function vimmSitemapShortcode($atts){
        $defaults = array(
                'url' => '/sitemap',
                'link' => 'Sitemap'
        
        );
        $atts = shortcode_atts( $defaults, $atts );
        
        if( $atts['url'] == '/sitemap' && !get_option('vimm-sitemap-shortcode', false) ){
            if( !get_page_by_title('Sitemap') ){
                wp_insert_post( array(
                    'post_name'      => 'sitemap',
                    'post_status'    => 'publish',
                    'post_title'     => 'Sitemap',
                    'post_type'      => 'page'
                ) ); 
            }
            
            add_option('vimm-sitemap-shortcode', 1, null, 'yes' );
        }
        
        
        $output = '<a href="' . $atts['url'].'" />'. $atts['link'] . '</a>';
        return apply_filters( 'vimm_sitemap_link', $output, $atts );
    }
    
    
    /**
     * Creates a shortcode which links to vivid Image's Site
     * @param array $atts the arguments for the shortcode
     * @since 9/21/12
      * @uses Supported shortcode attributes are:
      * *  by (The Developed by 0,
      * *  url (The links url ),
      * *  link (The text of the link
      *   @example [vimm_link by="Designed by"]
      * @uses all support filtering of output using 'vimm_shortcode_link' filter with 2 args
     */
    function vimm_link_shortcode( $atts ){
        $defaults = array(
                'by' => 'Developed By',
                'url' => 'http://www.vimm.com',
                'link' => 'Vivid Image'
        
        );
        $atts = shortcode_atts( $defaults, $atts );
        
        
        $output = $atts['by'] . ' <a href="' . $atts['url'].'" target="blank" />'. $atts['link'] . '</a>';
        return apply_filters( 'vimm_shortcode_link', $output, $atts );
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
     * Opens the divs that wrap the post content
     * @uses called by __construct()
     * @since 9/21/12
     */
    function wrap_topper(){
        ?><div id="content-wrap"><div id="content-wrap2"><?php
    }
    
    /**
     * Closes the divs that wrap the content\
     * @uses contains a hook called 'mat_after_content' for executing functions after these wraps
     * @uses calle by __contruct()
     * @since 7.2.13
     */
    function wrap_footer(){
        ?></div></div><!-- End #content-wrap --><?php
        //An extra hook just in case
        //Left here simply for deprectation
        do_action('mat_after_content');
        
        //Proper Hook to use
        do_action('mvc_after_content');
        
    }
    
    
    

}

$Bootstrap = new Bootstrap();


