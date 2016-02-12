<?php

namespace MVC\Util;
use MVC\Script;
use MVC\Style;

/**
 * MVC Styles
 *
 * Optional CSS and JS handler for the theme
 * Allow for targeting specific browsers and such with css file names
 *
 *
 * @uses  add_theme_support('mvc_styles');
 *
 */


class Styles {
	use \MVC\Traits\Singleton;

	public static $localize_admin = array();

	public static $localize = array();


	function __construct(){
		//Add the IE only Stylesheets
		/** To be removed on 1/19/2017 */
		add_action( 'wp_head', array( $this, 'ie_only' ), 99 );

		if( is_admin() ){
			add_action( 'admin_enqueue_scripts', array( $this, 'cue_admin_js_css' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'cue_js_css' ) );
		}
	}


	/**
	 * Localize
	 *
	 * Quick function for adding variables available in the front-end.js
	 *
	 * @param $name - name of var
	 * @param $data - data attached to var
	 *
	 * @uses    wp_localize_script
	 *
	 * @example must be called before wp_enqueue_scripts
	 *
	 * @return void
	 */
	public function localize( $name, $data ){
		self::$localize[ $name ] = $data;
	}


	/**
	 * Localize
	 *
	 * Quick function for adding variables available in the admin.js
	 *
	 * @param $name - name of var
	 * @param $data - data attached to var
	 *
	 * @uses    wp_localize_script
	 *
	 * @example must be called before admin_enqueue_scripts
	 *
	 * @return void
	 */
	public function localize_admin( $name, $data ){
		self::$localize_admin[ $name ] = $data;

	}


    /**
     * Quick and Dirty way to cue a js file from any resources folder
     * Handles front-end, admin, and min files for each just by specifying
     * the url path to the js folder.
     *
     * Based off or my own grunt folder structure, so should probably be
     * considered @internal due to non universal usage
     * /front-end.js
     * /admin.js
     * /min/admin.min.js
     * /min/front-end.min.js
     *
     * @internal
     *
     * @param string $js_var       - Needs to match the js var from the grunt config
     * @param array  $dependencies - these must already be cued
     * @param string $folder       - Full url path to the js folder
     * @param array  $config       - data passed as the js config
     *
     * @return void
     */
    public function add_external_js( $js_var, $dependencies, $folder, $config  ){
        $folder = trailingslashit( $folder );

        if( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ){
            $folder .= 'min/';
            $extension = '.min.js';
        } else {
            $extension = '.js';
        }

        if( is_admin() ){
            $file = $folder . 'admin' . $extension;
            $action = 'admin_enqueue_scripts';
        } else {
            $file = $folder . 'front-end' . $extension;
            $action = 'wp_enqueue_scripts';
        }

        add_action( $action, function() use( $js_var, $dependencies, $file, $config ){
            wp_enqueue_script( $js_var, $file, $dependencies, mvc_util()->get_beanstalk_based_version() );
            wp_localize_script( $js_var, $js_var . '_config' , $config );
        });
    }


	/**
	 *
	 * Add Js
	 *
	 * Quick way to add a js file to the site from the front-end themes js file
	 *
	 * @param string $file       - the file name
	 * @param bool   $debug_only - set to true to only add the js_file when SCRIPT_DEBUG is true
	 * @param string [$handle] - optional script handle (defaults to mvc-%file% )
	 *
	 */
	function add_js( $file, $debug_only = false, $handle = false ){
		if( $debug_only ){
			_deprecated_argument( '\MVC\Styles::add_js->debug_only', "1.19.16", "Use your own conditional if you want to exclude this js");
			if( !defined( 'SCRIPT_DEBUG' ) || !SCRIPT_DEBUG ){
				return;
			}
		}
		new Script( $file );
	}


	/**
	 * Quick cue of a stylesheet
	 * Will honor .min version if no SCRIPT_DEBUG
	 *
	 * Searches:
	 * /
	 * /css
	 * /resources/css
	 *
	 * @param string $file - name of file minus the .css
	 *
	 * @return void
	 */
	function add_css( $file ){
		new Style( $file );
	}



	/**
	 * Add Font
	 *
	 * Add a google font the head of the webpage in the front end and admin
	 *
	 *
	 * @param mixed string|array $families - the family to include
	 *
	 * @example Raleway:400,700,600:latin
	 *
	 * @see     added array() capabilities on 7.1.13 per sugestion from Tyler
	 * @uses    Must be called before the 'wp_head' hook fires
	 */
	function add_font( $families ){
		if( is_array( $families ) ){
			$families = implode( "','", $families );
		}

		ob_start();
		?>
		<script type="text/javascript">
			WebFontConfig = {
				google : {families : ['<?php echo $families; ?>']}
			};
			(function(){
				var wf = document.createElement( 'script' );
				wf.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
				wf.type = 'text/javascript';
				wf.async = 'true';
				var s = document.getElementsByTagName( 'script' )[0];
				s.parentNode.insertBefore( wf, s );
			})(); </script>
		<?php

		$output = ob_get_clean();

		add_action( 'wp_head', function() use( $output ){
           echo $output;
        } );
		add_action( 'admin_print_scripts', function() use( $output ){
            echo $output;
        } );
	}


	/**
	 * Add stylesheets that Targer Specific version of IE
	 *
	 * @uses    create a file named ie.css, ie8.css, or ie7.css and this will do the rest
	 *
	 * @actions ie-head, ie9-head, ie8-head, ie7-head
	 *
	 *
	 */
	function ie_only(){

		//ie 10 only
		if( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'MSIE 10' ) ){
			if( $file = mvc_file()->locate_template( 'ie10.css', true ) ){
				echo '<!--[if IE 10]>';
				do_action( 'ie-head' );
				printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );

				_deprecated_function( '\MVC\Styles::ie_only', '1/19/2016', 'If you need to support ie stylesheets do so in your theme' );
			}
		}

		//ie 9 only
		if( $file = mvc_file()->locate_template( 'ie9.css', true ) ){
			echo '<!--[if IE 9]>';
			do_action( 'ie9-head' );
			printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
			_deprecated_function( '\MVC\Styles::ie_only', '1/19/2016', 'If you need to support ie stylesheets do so in your theme' );
		}

		//ie 8 only
		if( $file = mvc_file()->locate_template( 'ie8.css', true ) ){
			echo '<!--[if IE 8]>';
			do_action( 'ie8-head' );
			printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
			_deprecated_function( '\MVC\Styles::ie_only', '1/19/2016', 'If you need to support ie stylesheets do so in your theme' );
		}

		//ie 7 only
		if( $file = mvc_file()->locate_template( 'ie7.css', true ) ){
			echo '<!--[if IE 7]>';
			do_action( 'ie7-head' );
			printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
			_deprecated_function( '\MVC\Styles::ie_only', '1/19/2016', 'If you need to support ie stylesheets do so in your theme' );
		}

	}


	/**
	 * Add the admin.js and admin.css file to the dashboard
	 *
	 * @see  the js and css dirs in the front-end theme
	 *
	 * @uses called by construct()
	 * @uses to change the local admin css use the filter 'mat-local-admin-css'
	 *
	 */
	function cue_admin_js_css(){
		foreach( mvc_file()->get_mvc_dirs() as $key => $_dir ){
			$script = new Script( 'admin' );

			$script->include_in_admin    = true;
			$script->include_in_frontend = false;
			$script->folder              = $_dir;
			if( $key == 0 ){
				$script->handle = 'mvc-admin-js';
				$dirs           = array(
					'IMG'          => MVC_IMAGE_URL,
					'THEME'        => MVC_THEME_URL,
					'LOADING_ICON' => MVC_IMAGE_URL . 'loading.gif',
				);

				$script->set_data( 'DIRS', $dirs );
				foreach( self::$localize_admin as $var => $data ){
					$script->set_data( $var, $data );
				}
			}

			$style                      = new Style( 'admin' );
			$style->include_in_frontend = false;
			$style->include_in_admin    = true;
			$style->folder             = $_dir;
		}

		//to localize stuff
		do_action( 'mvc_admin_js', 'mvc-admin-js' );

	}


	/**
	 * locate_css_file
	 *
	 * Locates the proper css file based on SCRIPT_DEBUG
	 *
	 * Will check in the root of the theme then the /css folder
	 *
	 * If !SCRIPT_DEBUG will look for .min.css file
	 *
	 * @param $file_name
	 *
	 * @return bool|string
	 */
	public function locate_css_file( $file_name ){
		$file_name = str_replace( '.css', '', $file_name );

		if( !defined( 'SCRIPT_DEBUG' ) || !SCRIPT_DEBUG ){
			if( !$file = mvc_file()->locate_template( "$file_name.min.css", true ) ){
				if( !$file = mvc_file()->locate_template( "css/$file_name.min.css", true ) ){
					$file = mvc_file()->locate_template( "resources/css/$file_name.min.css", true );
				}
			}
		}

		if( empty( $file ) ){
			if( !$file = mvc_file()->locate_template( "$file_name.css", true ) ){
				if( !$file = mvc_file()->locate_template( "css/$file_name.css", true ) ){
					$file = mvc_file()->locate_template( "resources/css/$file_name.css", true );
				}
			}
		}

		return $file;
	}


	/**
	 * locate_js_file
	 *
	 * Locates the proper js file based on SCRIPT_DEBUG
	 * And theme structure
	 *
	 * Will look in a /js folder first then /resources/js
	 *
	 * if !SCRIPT_DEBUG will look for a $file_name.min.js file
	 *
	 * @param $file_name
	 *
	 * @return bool|string
	 */
	public function locate_js_file( $file_name ){
		$file_name = str_replace( '.js', '', $file_name );

		if( !defined( 'SCRIPT_DEBUG' ) || !SCRIPT_DEBUG ){
			if( !$file = mvc_file()->locate_template( "js/$file_name.min.js", true ) ){
				if( !$file = mvc_file()->locate_template( "js/min/$file_name.min.js", true ) ){
					$file = mvc_file()->locate_template( "resources/js/min/$file_name.min.js", true );
				}

			}
		}

		if( empty( $file ) ){
			if( !$file = mvc_file()->locate_template( "js/$file_name.js", true ) ){
				if( !$file = mvc_file()->locate_template( "js/min/$file_name.js", true ) ){
					$file = mvc_file()->locate_template( "resources/js/$file_name.js", true );
				}
			}
		}

		return $file;

	}


	/**
	 * Add the front-end.js file to the site
	 *
	 * If SCRIPT_DEBUG is defined and true this will check for a chid.min.js file
	 * If not defined or false will check for a front-end.js file
	 *
	 * Will check in the %theme%/js dir first then the %theme%/resources/js dir next
	 *
	 *
	 * @uses  called by __construct()
	 * @uses  also add a global js variable with includes the commonly used dirs called 'DIR'
	 * @uses  adds the mobile stylesheets if defined in the theme config
	 *
	 */
	function cue_js_css(){

		foreach( mvc_file()->get_mvc_dirs() as $key => $_dir ){
			$script         = new Script( 'front-end' );
			$script->folder = $_dir;
			if( $key == 0 ){
				$script->handle = 'mvc-front-end-js';
				$dirs           = array(
					'IMG'          => MVC_IMAGE_URL,
					'THEME'        => MVC_THEME_URL,
					'LOADING_ICON' => MVC_IMAGE_URL . 'loading.gif',
					'ADMIN_URL'    => get_admin_url(),
				);
				$script->set_data( 'DIRS', $dirs );
				foreach( self::$localize as $var => $data ){
					$script->set_data( $var, $data );
				}
			}

			$css         = new Style( 'front-end' );
			$css->folder = $_dir;
		}

		//to localize stuff
		do_action( 'mvc_front-end_js', 'mvc-front-end-js' );
	}

}
    