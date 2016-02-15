<?php

namespace MVC\Core;


/**
 * Framework
 *
 * The heart of any mvc class
 * Extend your main Controller and Model class with this to give all your
 * classes the power of this class
 *
 * May also be called directly using mvc()
 *
 *
 * @author  Mat Lipe <mat@matlipe.com>
 *
 * @class   Framework
 * @package MVC
 *
 */
class Framework {
	const CACHE_GROUP = "MvcFramework";

	protected $controller;


	/**
	 * __call()
	 *
	 * Allow special non existant calls
	 *
	 * @example $this->view_%name% will call $this->view with $file set to %name%
	 * @example $this->fitler_%name% will return $this->view withe the $file set to %name%
	 * @example $this->return_%string% will return %string% for filters which require simple string arguments
	 *
	 * @return void
	 *
	 */
	function __call( $func, $args ){
		if( ( strpos( $func, 'view' ) !== false ) || ( strpos( $func, 'View' ) !== false ) ){
			$this->view( str_replace( array( 'View_', 'view_' ), array( '', '' ), $func ), false, $args );
			return;

		}

		if( ( strpos( $func, 'filter' ) !== false ) || ( strpos( $func, 'Filter' ) !== false ) ){
			return $this->filter( str_replace( array( 'Filter_', 'filter_' ), array( '', '' ), $func ), false, $args );
		}


		if( ( strpos( $func, 'return' ) !== false ) || ( strpos( $func, 'Return' ) !== false ) ){
			return str_replace( array( 'Return_', 'return_' ), array( '', '' ), $func );
		}


		if( ( strpos( $func, 'echo' ) !== false ) || ( strpos( $func, 'Echo' ) !== false ) ){
			echo str_replace( array( 'echo_', 'Echo_' ), array( '', '' ), $func );

			return;
		}


		if( ( strpos( $func, 'widget' ) !== false ) || ( strpos( $func, 'Widget' ) !== false ) ){
			mvc_template()->widgetArea( mvc_string()->human_format_slug( str_replace( array( 'Widget_', 'widget_' ), array(
				'',
				''
			), $func ) ) );

			return;
		}

		//For Sidebars
		if( ( strpos( $func, 'sidebar' ) !== false ) || ( strpos( $func, 'Sidebar' ) !== false ) ){
			mvc_template()->sidebar( mvc_string()->human_format_slug( str_replace( array( 'Sidebar_', 'sidebar_' ), array(
				'',
				''
			), $func ) ) );

			return;
		}

		echo '<pre>';
		debug_print_backtrace();
		echo '</pre>';
		trigger_error( $func . ' Does Not Exist as a Method ', E_USER_ERROR );

	}


	/**
	 *
	 * @deprecated Instantiate the class directly
	 */
	function __get( $object ){

		_deprecated_function( "Magic Method within MVC Framework", "12-16-15", "Instantiate the class directly" );

		if( !class_exists( $object ) ){
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
			trigger_error( $object . ' Does Not Exist as a Class ', E_USER_ERROR );

		}

		$this->{$object} = new $object;

		return $this->{$object};
	}


	/**
	 * Returns the output of the proper View File to a filter
	 *
	 * @since 11.27.13
	 *
	 * @uses  call with no param and it will pull the view file matching the method name from the controller named folder
	 * @uses  accepts extra param which will be turned into variables in the view
	 *
	 * @param       $file   the view file to use
	 * @param       $folder the view folder to use
	 * @param Array $args   will be extracted into usable args is associate array otherwise will be avaiable as is in view
	 * @param       bool    [$hideInfo] - to remove the <!-- comments --> (defaults to false);
	 *
	 * @return string
	 */
	function filter( $file = false, $folder = false, $args = array(), $hideInfo = false ){
		ob_start();
		$this->view( $file, $folder, $args, $hideInfo );

		return ob_get_clean();
	}


	/**
	 * Calls the Proper view file from a controller
	 *
	 * @since 12.2.13
	 *
	 * @uses  call with no param and it will pull the view file matching the method name from the controller named folder
	 * @uses  accepts extra param which will be turned into variables in the view
	 * @uses  all keys set using $this->set() will be extracted into usable variables in view
	 *
	 * @param       $file   the view file to use
	 * @param       $folder the view folder to use
	 * @param Array $args   will be extracted into usable args is associate array otherwise will be avaiable as is in view
	 * @param       bool    [$hideInfo] - to remove the <!-- comments --> (defaults to false);
	 *
	 * @return void
	 */
	function view( $file = false, $folder = false, $args = array(), $hideInfo = false ){
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if( !$folder ){
			$folder = $this->getController();
			if( defined( "MVC_CONTROLLER_PREFIX" ) && MVC_CONTROLLER_PREFIX ){
				$folder = str_replace( MVC_CONTROLLER_PREFIX, "", $folder );
			}
		}

		if( $folder != "" ){
			$folder .= "/";
		}

		if( !$file ){
			list( , $caller ) = debug_backtrace( false );
			$file = $caller[ 'function' ];
		}

		if( $path = mvc_file()->locate_template( 'View/' . $folder . $file . '.php' ) ){
			if( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) && !$hideInfo ){
				echo "<!-- $path -->";
			}
			extract( $args );
			extract( $this->get() );
			include( $path );

		} else {
			echo __( 'The file does not exist View/' . $folder . $file . '.php', 'mvc' );
		}

	}


	/**
	 * single_view
	 *
	 * Display a view only one time. Calling this more than once
	 * with the same folder and file will do nothing. Only the first
	 * call will actually call the view method.
	 *
	 * Great for js or thickbox templates
	 *
	 * @uses $this->view()
	 *
	 * @param string $file   the view file to use
	 * @param string $folder the view folder to use
	 * @param Array  $args   will be extracted into usable args is associate array otherwise will be available as is in view
	 * @param       bool    [$hideInfo] - to remove the <!-- comments --> (defaults to false);
	 *
	 * @return void
	 */
	function single_view( $file = false, $folder = false, $args = array(), $hideInfo = false ){
		static $views = array();
		if( !$folder ){
			$folder = get_class($this);
		}

		if( !$file ){
			list(, $caller) = debug_backtrace(false);
			$file = $caller['function'];
		}
		if( isset( $views[ $file ][ $folder ] ) ){
			return;
		}
		$views[ $file ][ $folder ] = 1;

		$this->view( $file, $folder, $args, $hideInfo );
	}


	/**
	 * Uses to set global variables which can be collected in views
	 *
	 * @since 1.11.13
	 * @uses  $this->set('helloData', 'hello' );
	 * @uses  sets a key in the global data array which matches the controller and holds the data
	 *        * Data can be retrieved by using $this->get('helloData') in the View
	 *
	 * @param string $name key
	 * @param mixed  $data the data to store
	 */
	function set( $name, $data ){
		global $controllerViewGlobals;
		$controllerViewGlobals[ $this->getController() ][ $name ] = $data;
	}


	/**
	 * Gets the name of the Current Controller to allow for automation
	 *
	 * @since 1.13.13
	 */
	function getController(){
		if( $this->controller ){
			return $this->controller;
		}
		$this->controller = str_replace( array( '_Controller', 'Controller' ), '', get_class( $this ) );

		return $this->controller;
	}


	/**
	 * Get a complete Controller Object
	 *
	 * @since 1.0.1
	 *
	 * @param string $controller - name of controller
	 */
	function getControllerObject( $controller ){
		global $mvc_theme;

		return $mvc_theme[ 'controllers' ][ $controller ];

	}


	/**
	 * Retreive data set in a controller with set()
	 *
	 *
	 * @uses $this->get('key');
	 *
	 * @uses may only be used inside a view to retreive data set from its controller
	 *
	 * @param string $name  [optional] of the key defaults to all that has been set
	 * @param        string [$controller] - the class to retrieve from default to current
	 *
	 */
	function get( $name = false, $controller = false ){
		global $controllerViewGlobals;

		if( empty( $controller ) ){
			$controller = $this->getController();
		}

		if( !$name ){
			if( empty( $controllerViewGlobals[ $controller ] ) ){
				return array();
			}

			return $controllerViewGlobals[ $controller ];
		}

		if( isset( $controllerViewGlobals[ $controller ][ $name ] ) ){
			return $controllerViewGlobals[ $controller ][ $name ];
		}

		return false; //nothing set
	}

}

