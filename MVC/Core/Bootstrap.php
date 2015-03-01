<?php

namespace MVC\Core;


/**
 * Boot Strap
 *
 * Pull the Whole Framework Together
 *
 * @author Mat Lipe <mat@matlipe.com>
 */

$MvcFramework = new \MVC\Core\Framework();

/** The Config for this Theme **/
if( $file = $MvcFramework->locate_template( 'mvc-config.php' ) ){
	include( $file );

} else {
	include( MVC_DIR . 'mvc-config.php' );

}

if( !defined( 'MVC_THEME_URL' ) ){
	define( 'MVC_THEME_URL', get_bloginfo( 'stylesheet_directory' ) . '/' );
}
if( !defined( 'MVC_THEME_DIR' ) ){
	define( 'MVC_THEME_DIR', get_stylesheet_directory() . '/' );
}

if( !defined( 'MVC_IMAGE_URL' ) ){
	define( 'MVC_IMAGE_URL', MVC_THEME_URL . 'images/' );
}
if( !defined( 'MVC_JS_URL' ) ){
	define( 'MVC_JS_URL', MVC_THEME_URL . 'js/' );
}

if( current_theme_supports( 'mvc_update' ) && is_admin() ){
	\MVC\Core\Update::init();
}

define( 'IS_MOBILE_THEME', current_theme_supports( 'mvc_mobile_responsive' ) );

if( current_theme_supports( 'mvc_api' ) ){
	\MVC\Core\Api::init();
}

//For internal tax
if( current_theme_supports( 'mvc_internal_tax' ) ){
	\MVC\Util\Internal_Tax::init();
}


//For Cache
if( current_theme_supports( 'mvc_cache' ) ){
	\MVC\Util\Cache::init();
}

//For On the Fly Image Resize
if( current_theme_supports( 'mvc_image_resize' ) ){
	\MVC\Util\Image_Resize::init();
}

//For CSS and JS Files
if( current_theme_supports( 'mvc_styles' ) ){
	\MVC\Util\Styles::init();
}

//For Output Formatting
if( current_theme_supports( 'mvc_template' ) ){
	\MVC\Util\Template::init();
}

//For Output Formatting
if( current_theme_supports( 'mvc_ajax' ) ){
	\MVC\Core\Ajax::init();
}

//For custom urls
if( current_theme_supports( 'mvc_route' ) ){
	\MVC\Route::init();
}


/**
 * Bootstrap
 *
 * Put all the default stuff in motion
 *
 * @author Mat Lipe <mat@matlipe.com>
 *
 */
class Bootstrap {
	use \MVC\Traits\Singleton;


	/**
	 * Constructor
	 *
	 * @uses constructed at the bottom of this file
	 */
	function __construct(){
		$this->setupMvc();

		//Allow for achive and single methods to work at the correct time
		add_action( 'wp', array( $this, 'singleAndArchiveMethods' ) );

		//register widgets
		add_action( 'widgets_init', array( $this, 'registerWidgets' ) );

	}


	/**
	 * Runs the single and archive methods for the classes
	 *
	 * @since 5.5.0
	 *
	 * @uses  works off of the $GLOBALS set earlier in the file
	 *
	 */
	function singleAndArchiveMethods(){

		if( is_admin() ){
			return;
		}

		$type = get_post_type();

		if( is_single() ){
			if( isset( $GLOBALS[ 'MvcClassesWithSingle' ] ) ){
				foreach( $GLOBALS[ 'MvcClassesWithSingle' ] as $name => $class ){
					if( strtolower( $name ) == $type ){
						$GLOBALS[ $class ]->single();

						return;
					}
				}
			}
		}

		if( is_author() ){
			if( !empty( $GLOBALS[ 'MvcClassesWithSingle' ][ 'Author' ] ) ){
				$GLOBALS[ $GLOBALS[ 'MvcClassesWithSingle' ][ 'Author' ] ]->archive();
			}

			return;
		}

		if( is_page_template( 'page_blog.php' ) ){
			$type = 'post';
		}

		if( is_archive() || is_page_template( 'page_blog.php' ) ){
			if( isset( $GLOBALS[ 'MvcClassesWithArchive' ] ) ){
				foreach( $GLOBALS[ 'MvcClassesWithArchive' ] as $name => $class ){
					if( strtolower( $name ) == $type ){
						$GLOBALS[ $class ]->archive();

						return;
					}
				}
			}
		}

		if( is_page() ){
			if( isset( $GLOBALS[ 'PageController' ] ) ){
				if( method_exists( $GLOBALS[ 'PageController' ], 'single' ) ){
					$GLOBALS[ 'PageController' ]->single();

					return;
				}
			}
		}

		if( is_home() || is_front_page() ){
			if( isset( $GLOBALS[ 'HomeController' ] ) ){
				if( method_exists( $GLOBALS[ 'HomeController' ], 'single' ) ){
					$GLOBALS[ 'HomeController' ]->single();

					return;
				}
			}
		}

		if( is_search() ){
			if( isset( $GLOBALS[ 'SearchController' ] ) ){
				if( method_exists( $GLOBALS[ 'SearchController' ], 'single' ) ){
					$GLOBALS[ 'SearchController' ]->single();

					return;
				}
			}
		}


	}


	/**
	 * Registers the Widgets from the themes widgets folder
	 *
	 * @since   3.0
	 *
	 * @since   10.18.13
	 *
	 * @filters mvc_theme_dirs - allows for other themes or plugins to have a widgets folder
	 */
	function registerWidgets(){
		$dirs = mvc()->get_mvc_dirs();

		//Register Widgets from the theme's widget folder
		if( !current_theme_supports( 'mvc_widgets' ) ){
			if( count( $dirs ) === 1 ){
				return;
			} else {
				unset( $dirs[ array_search( MVC_THEME_DIR, $dirs ) ] );
			}
		}

		//go through all files in all widget dirs
		foreach( $dirs as $dir ){
			if( !is_dir( $dir . 'widgets' ) ){
				continue;
			}

			foreach( scandir( $dir . 'widgets' ) as $widget ){
				if( $widget == '.' || $widget == '..' ){
					continue;
				}
				require( $dir . 'widgets/' . $widget );
				$widgets[ ] = str_replace( '.php', '', $widget );
			}
			if( !isset( $widgets ) ){
				continue;
			}
			foreach( $widgets as $widget ){
				register_widget( $widget );
			}
		}
	}


	/**
	 * Includes and sets up inheritance on all MVC Files
	 *
	 * @since   0.1.0
	 *
	 * @since   1.20.14
	 *
	 * @uses    if the theme has a Controllers/Controller.php file this will run automatically
	 * @filters mvc_theme_dirs - allows for other plugins or themes to use the magic of this
	 */
	function setupMvc(){
		global $mvc_theme;

		$mvc_theme[ 'mvc_dirs' ] = mvc()->get_mvc_dirs();

		foreach( $mvc_theme[ 'mvc_dirs' ] as $dir ){

			$classes = array();

			if( file_exists( $dir . 'Controller/Controller.php' ) ){

				require( $dir . 'Controller/Controller.php' );
				require( $dir . 'Model/Model.php' );
				#-- Setup and run the Global, Controller, Model, and View
				//Had to do it this way because of requirements by the rest
				$Controller        = new Controller();
				$Controller->Model = new Model();
				if( method_exists( $Controller, 'before' ) ){
					add_action( 'wp', array( $Controller, 'before' ) );
				}

				$classes[ 'Controller' ] = 'Controller';
			}

			if( !file_exists( $dir . 'Controller' ) ){
				continue;
			}

			#-- Bring in the Files and Construct The Classes
			foreach( scandir( $dir . 'Controller' ) as $file ){
				if( !in_array( $file, array( '.', '..', 'Controller.php' ) ) ){
					//Add the Controller
					require( $dir . 'Controller/' . $file );
					$name = str_replace( array( '_Controller', 'Controller', '.php', ), '', $file );

					if( in_array( $name, array( 'Admin', 'admin' ) ) && !MVC_IS_ADMIN ){
						continue;
					}

					$class = str_replace( '.php', '', $file );
					global ${$class};
					${$class} = new $class;

					//Add the Model
					require( $dir . 'Model/' . $name . '.php' );

					if( defined( 'MVC_CONTROLLER_PREFIX' ) && MVC_CONTROLLER_PREFIX ){
						$var = str_replace( MVC_CONTROLLER_PREFIX, '', $name );
					} else {
						$var = $name;
					}

					${$class}->{$var} = new $name;

					//add to global var for later use
					$mvc_theme[ 'controllers' ][ $class ] = ${$class};

					//Keep track of all of the controllers and models
					$classes[ $class ] = $name;

					if( method_exists( ${$class}, 'before' ) ){
						//Check if the new child class has a before and runs it if it does
						//has to be done this way to prevent recalling the Controller->before() over and over
						$reflect = new ReflectionClass( $class );
						if( $reflect->getMethod( 'before' )->getDeclaringClass()->getName() == $class ){
							add_action( 'wp', array( ${$class}, 'before' ) );
						}
					}

					if( method_exists( $class, 'single' ) ){
						$GLOBALS[ 'MvcClassesWithSingle' ][ $name ] = $class;
					}

					if( method_exists( $class, 'archive' ) ){
						$GLOBALS[ 'MvcClassesWithArchive' ][ $name ] = $class;
					}
				}
			}

			// Setup model inheritance through
			foreach( $classes as $controller => $class ){
				if( isset( ${$controller}->uses ) ){
					foreach( ${$controller}->uses as $model ){
						if( !in_array( $model, $classes ) ){
							require_once( $dir . 'Model/' . $model . '.php' );
							$classes[ ] = $model;
						}

						if( defined( 'MVC_CONTROLLER_PREFIX' ) && MVC_CONTROLLER_PREFIX ){
							$var = str_replace( MVC_CONTROLLER_PREFIX, '', $model );
						} else {
							$var = $model;
						}
						${$controller}->{$var} = new $model;
					}
				}
				//Run the init
				if( method_exists( ${$controller}, 'init' ) ){
					add_action( 'init', array( ${$controller}, 'init' ) );
				}

				//Admin method
				if( method_exists( ${$controller}, 'admin' ) ){
					add_action( 'admin_init', array( ${$controller}, 'admin' ) );
				}
			}
		} //End foreach dir
	}

}
