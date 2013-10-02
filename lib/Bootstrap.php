<?php

                            /**
                             * Pull the Whole Framework Together
                             * @since 10.2.13
                             * @author Mat Lipe
                             */



//Require the proper files                      
require('functions.php');


//TODO Find a better way to do this
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); //Bring in the admin plugin functions

//Allow for autoloading framework Classes
spl_autoload_register('_mvc_autoload');


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
    $MvcLogin = new MvcLogin();   
}

//For the Category Icons
if( current_theme_supports('mvc_category_images') ){
    $MvcCategoryIcons = new MvcCategoryImage();   
}
//For the Colapsible Mobile Menu
if( current_theme_supports('mvc_mobile_menu', 'color') ){
    $MvcMobileMenu = new MvcMobileMenu(); 
}

//For On the Fly Image Resize
if( current_theme_supports('mvc_image_resize') ){
    $MvcImageResize = new MvcImageResize();
}


//For CSS and JS Files
if( current_theme_supports('mvc_styles') ){
    $MvcImageResize = new MvcStyles();
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

$Bootstrap = new Bootstrap();


