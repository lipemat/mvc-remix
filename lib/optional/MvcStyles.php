<?php

/**
 * Optional CSS and JS handler for the theme
 * Allow for targeting specific browsers and such with css file names
 *
 *
 * @uses  add_theme_support('mvc_styles');
 *
 * @TODO  Make a universal method to check for assets folders to pull files from as well as default locations
 */

if( class_exists( 'MvcStyles' ) ){
	return;
}

class MvcStyles extends MvcFramework {

	public static $localize_admin = array();
	public static $localize = array();


	function __construct(){

		add_action( 'init', array( $this, 'browser_support' ) );

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
	 * Quick function for adding variables available in the child.js
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
	 * Quick way to add a js file to the site from the child themes js file
	 *
	 * @param string $file - the file name
	 *
	 */
	function add_js( $file ){
		if( !MVC_IS_ADMIN ){
			wp_enqueue_script(
				'mvc-' . $file,
				MVC_JS_URL . $file . '.js',
				array( 'jquery', 'mvc-child-js' )
			);
		} else {
			wp_enqueue_script(
				'mvc-' . $file,
				MVC_JS_URL . $file . '.js',
				array( 'jquery', 'mvc-admin-js' )
			);

		}
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
				google: { families: [ '<?php echo $families; ?>' ] }
			};
			(function () {
				var wf = document.createElement('script');
				wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
					'://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
				wf.type = 'text/javascript';
				wf.async = 'true';
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(wf, s);
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
			$settings[ 'style_formats' ] = json_encode( array_merge( (array)$existing_style_formats, $style_formats ) );
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
			if( $file = $this->locate_template( 'ie10.css', true ) ){
				do_action( 'ie-head' );
				printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
			}
		}

		//ie 9 only
		if( $file = $this->locate_template( 'ie9.css', true ) ){
			echo '<!--[if IE 9]>';
			do_action( 'ie9-head' );
			printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
		}

		//ie 8 only
		if( $file = $this->locate_template( 'ie8.css', true ) ){
			echo '<!--[if IE 8]>';
			do_action( 'ie8-head' );
			printf( '<link rel="stylesheet" type="text/css" href="%s" /><![endif]-->', $file );
		}

		//ie 7 only
		if( $file = $this->locate_template( 'ie7.css', true ) ){
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
	 * Adds a stylesheet with the same name as the browser if file exists
	 *
	 * @uses  create a stylesheet in the themes root with the same name as the browser
	 *        * e.g. chrome.css
	 * @uses  added to the init hook by construct
	 * @since 6.7.13
	 */
	function browser_support(){
		foreach( array( 'is_chrome', 'is_gecko', 'is_safari', 'is_IE' ) as $browser ){
			global ${$browser};
			if( ${$browser} ){
				$this->browser = str_replace( array( 'is_', 'gecko' ), array( '', 'firefox' ), $browser );
				add_action( 'wp_enqueue_scripts', array( $this, 'browser_style' ) );
			}
		}

	}


	/**
	 * Adds a stylesheet of the matching browser if the stylesheet exists
	 *
	 * @uses  called by browser_support() and used the class var $browser
	 */
	function browser_style(){
		if( $file = $this->locate_css_file( $this->browser . '.css' ) ){
			wp_enqueue_style(
				$this->browser . '-child-css',
				$file
			);
		}
	}


	/**
	 * Add the admin.js and admin.css file to the dashboard
	 *
	 * @see  the js and css dirs in the child theme
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
				array( 'jquery' )
			);
			$dirs = array( 'IMG'          => MVC_IMAGE_URL,
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
				$file
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
			if( !$file = $this->locate_template( "$file_name.min.css", true ) ){
				$file = $this->locate_template( "css/$file_name.min.css", true );
			}
		}

		if( empty( $file ) ){
			if( !$file = $this->locate_template( "$file_name.css", true ) ){
				$file = $this->locate_template( "css/$file_name.css", true );
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
			if( !$file = $this->locate_template( "js/$file_name.min.js", true ) ){
				$file = $this->locate_template( "resources/js/min/$file_name.min.js", true );
			}
		}

		if( empty( $file ) ){
			if( !$file = $this->locate_template( "js/$file_name.js", true ) ){
				$file = $this->locate_template( "resources/js/$file_name.js", true );
			}
		}

		return $file;

	}


	/**
	 * Add the child.js file to the site
	 *
	 * If SCRIPT_DEBUG is defined and true this will check for a chid.min.js file
	 * If not defined or false will check for a child.js file
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

		$file = $this->locate_js_file( 'child' );

		if( $file ){
			wp_enqueue_script(
				'mvc-child-js',
				$file,
				array( 'jquery' ),
				false,
				true
			);

			$dirs = array(
				'IMG'          => MVC_IMAGE_URL,
				'THEME'        => MVC_THEME_URL,
				'LOADING_ICON' => MVC_IMAGE_URL . 'loading.gif',
				'ADMIN_URL'    => get_admin_url()
			);

			wp_localize_script( 'mvc-child-js', 'DIRS', $dirs );

			foreach( self::$localize as $var => $data ){
				wp_localize_script( 'mvc-child-js', $var, $data );
			}

			//to localize stuff
			do_action( 'mvc_child_js', 'mvc-child-js' );
		}


		$file = $this->locate_css_file( 'child' );
		//For custom css files when using with plugins
		if( $file  ){
			wp_enqueue_style(
				'mvc-child-styles',
				$file
			);
		}


		//Add the mobile Style if required
		if( current_theme_supports( 'mobile_responsive' ) ){
			if( $file = $this->locate_css_file( 'mobile/mobile-responsive.css' ) ){
				wp_enqueue_style(
					'mvc-mobile-styles',
					$file //The location of the style
				);
			}

			//Add the mobile script or the non mobile script based on device
			if( !self::is_mobile() ){
				if( $file = $this->locate_js_file( 'mobile/desktop.js' ) ){
					wp_enqueue_script(
						'mvc-non-mobile-script',
						$file,
						array( 'jquery' ),
						false,
						true
					);

				}
			} else {

				//Add the tablet stuff
				if( self::is_tablet() ){
					if( $file = $this->locate_css_file( 'mobile/tablet.css' ) ){
						wp_enqueue_style(
							'mvc-tablet-styles',
							$file
						);
					}


					if( $file = $this->locate_js_file(  'mobile/tablet.js' ) ){
						wp_enqueue_script(
							'mvc-tablet-script',
							$file,
							array( 'jquery' ),
							false,
							true
						);
					}
				}

				//Add the phone stuff
				if( self::is_phone() ){
					if( $file = $this->locate_css_file(  'mobile/phone.css' ) ){
						wp_enqueue_style(
							'mvc-phone-styles',
							$file //The location of the style
						);
					}

					if( $file = $this->locate_js_file( 'mobile/phone.js' ) ){
						wp_enqueue_script(
							'mvc-phone-script',
							$file,
							array( 'jquery' ),
							false,
							true
						);
					}
				}
			} //-- End if mobile device

		} //-- End if Mobile Responsive Theme Support

	}


	/********** SINGLETON FUNCTIONS **********/

	/**
	 * Instance of this class for use as singleton
	 */
	private static $instance;


	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return self
	 */
	public static function get_instance(){
		if( !is_a( self::$instance, __CLASS__ ) ){
			self::$instance = new self();
		}

		return self::$instance;
	}

}
    