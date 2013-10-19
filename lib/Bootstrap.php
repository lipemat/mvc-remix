<?php

                            /**
                             * Pull the Whole Framework Together
                             * @since 10.18.13
                             * @author Mat Lipe <mat@matlipe.com>
                             */



//Require the proper files                      
require('functions.php');


//TODO Find a better way to do this
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); //Bring in the admin plugin functions

//Allow for autoloading framework Classes
spl_autoload_register('_mvc_autoload');

if( is_admin() ){
    new MvcUpdate();   
}


/** The Config for this Theme **/
if( !locate_template('mvc-config.php', true) ){
   include( MVC_DIR.'mvc-config.php' );
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

//For Output Formatting
if( current_theme_supports('mvc_ajax') ){
    $MvcImageResize = new MvcAjax();
}



/**
 * Put all the default stuff in motion
 * @since 10.18.13
 */
class MvcBootstrap extends MvcFramework{
    
    
    /**
     * @since 10.18.13
     * 
     * @uses constructed at the bottom of this file
     */
    function __construct(){
         $this->setupMvc();   
        
       //Allow for achive and single methods to work at the correct time
       add_action('wp', array( $this, 'singleAndArchiveMethods') );
        
       //Move the genesis meta boxe below our special ones
        remove_action( 'admin_menu', 'genesis_add_inpost_layout_box' );    
        add_action( 'do_meta_boxes', 'genesis_add_inpost_layout_box' ); 
        remove_action( 'admin_menu', 'genesis_add_inpost_seo_box' );     
        add_action( 'do_meta_boxes', 'genesis_add_inpost_seo_box' );  
             
        //Register Widgets from the theme's widget folder
        if( current_theme_supports('mvc_widgets') ){
            add_action('widgets_init', array( $this,'registerWidgets' ) );
        }
        
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
     * @since 10.2.13
     */
    function registerWidgets(){
        
        if( !is_dir( MVC_THEME_DIR.'widgets' ) ) return;
        
        foreach( scandir(MVC_THEME_DIR.'widgets') as $widget ){
            if( $widget == '.' || $widget == '..') continue;
            require(MVC_THEME_DIR.'widgets/'.$widget);
            $widgets[] = str_replace('.php', '', $widget);
        }
        if( !isset( $widgets ) ) return;
        foreach ( $widgets as $widget ){
            register_widget($widget);
        }
    }


    
    /**
     * Includes and sets up inheritance on all MVC Files
     * 
     * @since 0.1.0
     * 
     * @since 10.18.13
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
                    ${$class}->{$name} = new $name;
                
                    //add to global var for later use
                    $mvc_theme['controllers'][$class] = ${$class};
                

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


           


            // Setup model inheritance through
            foreach ($classes as $controller => $class) {
                if (isset(${$controller}->uses)) {
                    foreach (${$controller}->uses as $model) {
                        if( !in_array($model,$classes) ){
                            require_once($dir.'Model/'.$model.'.php');
                        }
                        ${$controller}->{$model} = new $model;
                    }
                }
                //Run the init
                ${$controller}->init();
            }
        } //End foreach dir
    }


    
}

$Bootstrap = new MvcBootstrap();


