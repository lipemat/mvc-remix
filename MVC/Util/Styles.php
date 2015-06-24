<?php

namespace MVC\Util;

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
		add_action( 'wp_head', array( $this, 'ie_only' ), 99 );

		//Add Javascript to Site
		add_action( 'wp_enqueue_scripts', array( $this, 'add_js_css' ) );

		//Add Js and CSS to Admin
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ) );

		//Add stylesheet to editor
		add_filter( 'mce_css', array( $this, 'editor_style' ) );

		add_filter( 'tiny_mce_before_init', array( $this, 'editorStyleColumns' ) );

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
	 *
	 * Add Js
	 *
	 * Quick way to add a js file to the site from the front-end themes js file
	 *
	 * @param string $file       - the file name
	 * @param bool   $debug_only - set to true to only add the js_file when SCRIPT_DEBUG is true
	 *
	 */
	function add_js( $file, $debug_only = false ){
		if( $debug_only ){
			if( !defined( 'SCRIPT_DEBUG' ) || !SCRIPT_DEBUG ){
				return;
			}
		}

		$url = $this->locate_js_file( $file );

		if( empty( $url ) ){
			return;
		}
		wp_enqueue_script(
			'mvc-' . $file,
			$url,
			array( 'jquery' ),
			mvc_util()->get_beanstalk_based_version()
		);

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

		add_action( 'wp_head', array( $this, 'echo_' . $output ) );
		add_action( 'admin_print_scripts', array( $this, 'echo_' . $output ) );
	}


	/**
	 * Add column entries to the style dropdown.
	 *
	 * @since 1.7.14
	 *
	 * @param array $settings Existing settings for all toolbar items
	 *
	 * @return array $settings Amended settings
	 * @uses  added to the tiny_mce_before_init filter
	 */
	function editorStyleColumns( array $settings ){

		$style_formats = array(
			array( 'title' => 'Columns', ),
			array(
				'title'   => 'First Half',
				'block'   => 'div',
				'classes' => 'one-half first',
			),
			array(
				'title'   => 'Half',
				'block'   => 'div',
				'classes' => 'one-half',
			),
			array(
				'title'   => 'First Third',
				'block'   => 'div',
				'classes' => 'one-third first',
			),
			array(
				'title'   => 'Third',
				'block'   => 'div',
				'classes' => 'one-third',
			),
			array(
				'title'   => 'First Quarter',
				'block'   => 'div',
				'classes' => 'one-fourth first',
			),
			array(
				'title'   => 'Quarter',
				'block'   => 'div',
				'classes' => 'one-fourth',
			),
			array(
				'title'   => 'First Fifth',
				'block'   => 'div',
				'classes' => 'one-fifth first',
			),
			array(
				'title'   => 'Fifth',
				'block'   => 'div',
				'classes' => 'one-fifth',
			),
			array(
				'title'   => 'First Sixth',
				'block'   => 'div',
				'classes' => 'one-sixth first',
			),
			array(
				'title'   => 'Sixth',
				'block'   => 'div',
				'classes' => 'one-sixth',
			),
		);

		// Check if there are some styles already
		if( isset( $settings[ 'style_formats' ] ) ){
			// Decode any existing style formats
			$existing_style_formats = json_decode( $settings[ 'style_formats' ] );

			// Merge our new formats with any existing formats and re-encode
			$settings[ 'style_formats' ] = json_encode( array_merge( (array) $existing_style_formats, $style_formats ) );
		} else {
			$settings[ 'style_formats' ] = json_encode( $style_formats );
		}

		return $settings;

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
			}
		}

		//ie 9 only
		if( $file = mvc_file()->locate_template( 'ie9.css', true ) ){
			echo '<!--[if IE 9]>';
			do_action( 'ie9-head' );
			printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
		}

		//ie 8 only
		if( $file = mvc_file()->locate_template( 'ie8.css', true ) ){
			echo '<!--[if IE 8]>';
			do_action( 'ie8-head' );
			printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
		}

		//ie 7 only
		if( $file = mvc_file()->locate_template( 'ie7.css', true ) ){
			echo '<!--[if IE 7]>';
			do_action( 'ie7-head' );
			printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
		}

	}


	/**
	 * Adds the style.css file to the editor
	 *
	 * @param string $wp exiting editor styles
	 *
	 * @return string
	 * @since 9/21/12
	 * @uses  called by __construct()
	 */
	function editor_style( $wp ){
		$wp .= ',' . get_bloginfo( 'stylesheet_url' );

		return $wp;
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
	function admin_js(){

		$file = $this->locate_js_file( 'admin' );

		if( !empty( $file ) ){
			wp_enqueue_script(
				'mvc-admin-js',
				$file,
				array( 'jquery' ),
				mvc_util()->get_beanstalk_based_version()
			);
			$dirs = array(
				'IMG'          => MVC_IMAGE_URL,
				'THEME'        => MVC_THEME_URL,
				'LOADING_ICON' => MVC_IMAGE_URL . 'loading.gif'
			);

			wp_localize_script( 'mvc-admin-js', 'DIRS', $dirs );

			foreach( self::$localize_admin as $var => $data ){
				wp_localize_script( 'mvc-admin-js', $var, $data );
			}

			//to localize stuff
			do_action( 'mvc_admin_js', 'mvc-admin-js' );
		}

		if( $file = $this->locate_css_file( 'admin.css' ) ){
			wp_enqueue_style(
				'mvc-admin-styles',
				$file,
				array(),
				mvc_util()->get_beanstalk_based_version()
			);
		}

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
				$file = mvc_file()->locate_template( "css/$file_name.min.css", true );
			}
		}

		if( empty( $file ) ){
			if( !$file = mvc_file()->locate_template( "$file_name.css", true ) ){
				$file = mvc_file()->locate_template( "css/$file_name.css", true );
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
	function add_js_css(){
		$version = mvc_util()->get_beanstalk_based_version();

		$file = $this->locate_js_file( 'front-end' );

		if( $file ){
			wp_enqueue_script( 'mvc-front-end-js', $file, array( 'jquery' ), $version, true );

			$dirs = array(
				'IMG'          => MVC_IMAGE_URL,
				'THEME'        => MVC_THEME_URL,
				'LOADING_ICON' => MVC_IMAGE_URL . 'loading.gif',
				'ADMIN_URL'    => get_admin_url()
			);

			wp_localize_script( 'mvc-front-end-js', 'DIRS', $dirs );

			foreach( self::$localize as $var => $data ){
				wp_localize_script( 'mvc-front-end-js', $var, $data );
			}

			//to localize stuff
			do_action( 'mvc_front-end_js', 'mvc-front-end-js' );
		}

		$file = $this->locate_css_file( 'front-end' );
		//For custom css files when using with plugins
		if( $file ){
			wp_enqueue_style( 'mvc-front-end-styles', $file, array(), $version );
		}

	}

}
    