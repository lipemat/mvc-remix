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


//For Output Formatting
if( current_theme_supports('mvc_format') ){
    $MvcImageResize = new MvcFormat();
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
class MvcBootstrap extends MvcFramework{
    
    
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
    
}

$Bootstrap = new MvcBootstrap();


