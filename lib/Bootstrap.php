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


define( 'IS_MOBILE_THEME', current_theme_supports('mvc_mobile_responsive') );  


//For Secure Themes
if( current_theme_supports('mvc_secure') ){
    $MvcLogin = new MvcLogin;   
}

//For the Category Icons
if( current_theme_supports('mvc_category_images') ){
    $MvcCategoryIcons = new MvcCategoryImage;   
}
//For the Colapsible Mobile Menu
if( current_theme_supports('mvc_mobile_menu', 'color') ){
    $MvcMobileMenu = new MvcMobileMenu(); 
}

//For On the Fly Image Resize
if( current_theme_supports('mvc_image_resize') ){
    $MvcImageResize = new MvcImageResize();
}



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
 * @since 9.30.13
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
        if( current_theme_supports('mvc_stylesheets') ){
            add_action( 'init', array( $this, 'browser_support' ) );
             //Add the IE only Stylesheets
            add_action('wp_head', array( $this, 'ie_only'), 99 );
            //Add Javascript to Site
            add_action('wp_enqueue_scripts', array( $this, 'add_js_css' ) );
            //Add Js and CSS to Admin
            add_action( 'admin_print_scripts', array( $this, 'admin_js' ) );
            //Add stylesheet to editor
            add_filter('mce_css',array( $this, 'editor_style' ) );
        
        }
        
        //Register Widgets from the theme's widget folder
        if( current_theme_support('mvc_widgets') ){
            add_action('widgets_init', array( $this,'registerWidgets' ) );
        }
        
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
        
        if( file_exists(THEME_FILE_DIR.'js/child.js') ){
            wp_enqueue_script(
                'mvc-child-js',
                JS_DIR. 'child.js',
                array('jquery' )
            );
         
            $dirs = array( 
                        'IMG'     => IMAGE_DIR,
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

$Bootstrap = new Bootstrap();


