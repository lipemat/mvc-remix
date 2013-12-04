<?php

                            /**
                             * Pull the Whole Framework Together
                             * @since 12.4.13
                             * @author Mat Lipe <mat@matlipe.com>
                             */



//Require the proper files                      
require('functions.php');

//Bring in the admin plugin functions
//TODO Find a better way to do this
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 

//Allow for autoloading framework Classes
spl_autoload_register('_mvc_autoload');


/** The Config for this Theme **/
if( !locate_template('mvc-config.php', true) ){
   include( MVC_DIR.'mvc-config.php' );
}

if( current_theme_supports('mvc_update') && is_admin() ){
    new MvcUpdate();   
}



define( 'IS_MOBILE_THEME', current_theme_supports('mvc_mobile_responsive') );  


$MvcFramework = new MvcFramework();

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

//For Output Formatting
if( current_theme_supports('mvc_ajax') ){
    $MvcImageResize = new MvcAjax();
}



/**
 * Put all the default stuff in motion
 * 
 * @since 11.27.13
 * 
 * @author Mat Lipe <mat@matlipe.com>
 * 
 */
class MvcBootstrap extends MvcFramework{
    
    
    /**
     * @since 11.27.13
     * 
     * @uses constructed at the bottom of this file
     */
    function __construct(){
         $this->setupMvc();   
        
       //Allow for achive and single methods to work at the correct time
       add_action('wp', array( $this, 'singleAndArchiveMethods') );
        
       //Move the genesis meta box below our special ones
       if( function_exists('genesis_add_inpost_layout_box') ){
            remove_action( 'admin_menu', 'genesis_add_inpost_layout_box' );    
            add_action( 'do_meta_boxes', 'genesis_add_inpost_layout_box' ); 
            remove_action( 'admin_menu', 'genesis_add_inpost_seo_box' );     
            add_action( 'do_meta_boxes', 'genesis_add_inpost_seo_box' );
       }  
  
        //register widgets
        add_action('widgets_init', array( $this,'registerWidgets' ) );

        
    }


    /**
     * Runs the single and archive methods for the classes
     * 
     * @since 5.5.0
     * 
     * @since 10.18.13
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
        
         if( is_page() ){
             if( method_exists($GLOBALS['PageController'], 'single') ){
                 $GLOBALS['PageController']->single();   
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
     * 
     * @since 10.18.13
     * 
     * @filters mvc_theme_dirs - allows for other themes or plugins to have a widgets folder
     */
    function registerWidgets(){
        $dirs = apply_filters( 'mvc_theme_dirs', array( MVC_THEME_DIR ) );
                   
        //Register Widgets from the theme's widget folder
        if( !current_theme_supports('mvc_widgets') ){
            if( count($dirs) === 1 ){
                 return;
            } else {
                unset( $dirs[array_search( MVC_THEME_DIR, $dirs )] );   
            }
        }

        //go through all files in all widget dirs
        foreach( $dirs as $dir ){
            if( !is_dir( $dir.'widgets' ) ) continue;
        
            foreach( scandir($dir.'widgets') as $widget ){
                if( $widget == '.' || $widget == '..') continue;
                require($dir.'widgets/'.$widget);
                $widgets[] = str_replace('.php', '', $widget);
            }
            if( !isset( $widgets ) ) continue;
            foreach ( $widgets as $widget ){
                register_widget($widget);
            }
        }
    }


    
    /**
     * Includes and sets up inheritance on all MVC Files
     * 
     * @since 0.1.0
     * 
     * @since 12.4.13
     * 
     * @uses if the theme has a Controllers/Controller.php file this will run automatically
     * @filters mvc_theme_dirs - allows for other plugins or themes to use the magic of this
     */
    function setupMvc(){
        global $mvc_theme;
        
        $mvc_theme['mvc_dirs'] = apply_filters( 'mvc_theme_dirs', array( MVC_THEME_DIR ) );

        foreach( $mvc_theme['mvc_dirs'] as $dir ){
            
            $classes = array();
            
            if( file_exists($dir.'Controller/Controller.php') ){
                
                require($dir.'Controller/Controller.php' );
                require($dir.'Model/Model.php' );
                #-- Setup and run the Global, Controller, Model, and View
                //Had to do it this way because of requirements by the rest
                $Controller = new Controller();
                $Controller->Model = new Model();
                if( method_exists($Controller, 'before') ){
                    add_action('wp', array( $Controller, 'before' ) );
                }

                $classes['Controller'] = 'Controller';
             } 

            
            if( !file_exists( $dir.'Controller' ) ) continue;
            
            #-- Bring in the Files and Construct The Classes
            foreach( scandir($dir.'Controller') as $file ){
                if( !in_array( $file, array('.','..','Controller.php')) ){
                    //Add the Controller
                    require($dir.'Controller/'.$file);
                    $name = str_replace(array('Controller','.php'), array('',''), $file);

                    if( in_array($name, array('Admin','admin') ) && !MVC_IS_ADMIN ) continue;
          
               
                    $class = str_replace('.php', '', $file);
                    global ${$class};
                    ${$class} = new $class;
                
          
                    //Add the Model
                    require($dir.'Model/'.$name.'.php');
                    
                    if( defined('MVC_CONTROLLER_PREFIX') && MVC_CONTROLLER_PREFIX ){
                        $var = str_replace( MVC_CONTROLLER_PREFIX , '', $name );   
                    } else {
                        $var = $name;
                    }
                    
                    ${$class}->{$var} = new $name;
                
                    //add to global var for later use
                    $mvc_theme['controllers'][$class] = ${$class};
                
                    //Keep track of all of the controllers and models
                    $classes[$class] = $name;          
          
                    if( method_exists(${$class}, 'before' ) ){
                        //Check if the new child class has a before and runs it if it does
                        //has to be done this way to prevent recalling the Controller->before() over and over
                        $reflect = new ReflectionClass($class);
                        if( $reflect->getMethod('before')->getDeclaringClass()->getName() == $class ){
                            add_action('wp', array( ${$class}, 'before' ) );
                        }
                    }

                    if( method_exists($class, 'single') ){
                        $GLOBALS['MvcClassesWithSingle'][$name] = $class;
                    }
         
                    if( method_exists($class, 'archive') ){
                        $GLOBALS['MvcClassesWithArchive'][$name] = $class;
                    }
                }
            }

            // Setup model inheritance through
            foreach ($classes as $controller => $class) {
                if (isset(${$controller}->uses)) {
                    foreach (${$controller}->uses as $model) {
                        if( !in_array($model,$classes) ){
                            require_once($dir.'Model/'.$model.'.php');
                        }
                        
                        if( defined('MVC_CONTROLLER_PREFIX') && MVC_CONTROLLER_PREFIX ){
                            $var = str_replace( MVC_CONTROLLER_PREFIX , '', $model );   
                        } else {
                            $var = $model;
                        }
                        ${$controller}->{$var} = new $model;
                    }
                }
                //Run the init
                if( method_exists(${$controller}, 'init') ){
                    add_action('init', array( ${$controller}, 'init' ) );
                }
            }
        } //End foreach dir
    }


    
}

$Bootstrap = new MvcBootstrap();


