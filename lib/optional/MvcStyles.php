<?php

/**
 * Optional CSS and JS handler for the theme
 * Allow for targeting specific browsers and such with css file names
 * 
 * @since 1.7.14
 * 
 * @uses add_theme_support('mvc_styles');
 * 
 * @TODO Make a universal method to check for assets folders to pull files from as well as default locations
 */

if( class_exists('MvcStyles') ) return; 
 
class MvcStyles extends MvcFramework{
	
	public static $localize_admin = array();
	public static $localize       = array();
	
        
    function __construct(){
    	
         add_action( 'init', array( $this, 'browser_support' ) );
		 
             //Add the IE only Stylesheets
         add_action('wp_head', array( $this, 'ie_only'), 99 );
		 
            //Add Javascript to Site
         add_action('wp_enqueue_scripts', array( $this, 'add_js_css' ) );
		 
            //Add Js and CSS to Admin
         add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ) );
           
		    //Add stylesheet to editor
         add_filter('mce_css',array( $this, 'editor_style' ) );
		 
         add_filter( 'tiny_mce_before_init', array( $this, 'editorStyleColumns') );
		 
    }
	
	
	/**
	 * Localize
	 * 
	 * Quick function for adding variables available in the child.js
	 * 
	 * @param $name - name of var
	 * @param $data - data attached to var
	 * 
	 * @uses wp_localize_script
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
	 * @uses wp_localize_script
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
    function add_js($file){
        if( !MVC_IS_ADMIN ){
            wp_enqueue_script(
                'mvc-'.$file,
                MVC_JS_URL. $file.'.js',
                array('jquery', 'mvc-child-js' )
            );
        } else {
           wp_enqueue_script(
                'mvc-'.$file,
                MVC_JS_URL. $file.'.js',
                array('jquery', 'mvc-admin-js' )
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
     * @example Raleway:400,700,600:latin
     * 
     * @see added array() capabilities on 7.1.13 per sugestion from Tyler
     * @uses Must be called before the 'wp_head' hook fires
     */
    function add_font($families){
        if( is_array($families) ){
            $families = implode("','",$families);
        }
        
        
        ob_start();
        ?><script type="text/javascript">
            WebFontConfig = {
                google: { families: [ '<?php echo $families; ?>' ] }
            };
            (function() {
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
        
        add_action('wp_head', array( $this, 'echo_'.$output ) );
        add_action('admin_print_scripts', array( $this, 'echo_'.$output ) );
    }
	
	
    
     /**
     * Add column entries to the style dropdown.
     *
     * @since 1.7.14
     * @param array $settings Existing settings for all toolbar items
     * @return array $settings Amended settings
     * @uses added to the tiny_mce_before_init filter
     */
     function editorStyleColumns(array $settings) {

                $style_formats = array(
                    array('title' => 'Columns', ),
                    array(
                        'title' => 'First Half',
                        'block' => 'div',
                        'classes' => 'one-half first',
                    ),
                    array(
                        'title' => 'Half',
                        'block' => 'div',
                        'classes' => 'one-half',
                    ),
                    array(
                        'title' => 'First Third',
                        'block' => 'div',
                        'classes' => 'one-third first',
                    ),
                    array(
                        'title' => 'Third',
                        'block' => 'div',
                        'classes' => 'one-third',
                    ),
                    array(
                        'title' => 'First Quarter',
                        'block' => 'div',
                        'classes' => 'one-fourth first',
                    ),
                    array(
                        'title' => 'Quarter',
                        'block' => 'div',
                        'classes' => 'one-fourth',
                    ),
                    array(
                        'title' => 'First Fifth',
                        'block' => 'div',
                        'classes' => 'one-fifth first',
                    ),
                    array(
                        'title' => 'Fifth',
                        'block' => 'div',
                        'classes' => 'one-fifth',
                    ),
                    array(
                        'title' => 'First Sixth',
                        'block' => 'div',
                        'classes' => 'one-sixth first',
                    ),
                    array(
                        'title' => 'Sixth',
                        'block' => 'div',
                        'classes' => 'one-sixth',
                    ),
                );

                // Check if there are some styles already
                if (isset($settings['style_formats'])) {
                    // Decode any existing style formats
                    $existing_style_formats = json_decode($settings['style_formats']);

                    // Merge our new formats with any existing formats and re-encode
                    $settings['style_formats'] = json_encode(array_merge((array)$existing_style_formats, $style_formats));
                } else {
                    $settings['style_formats'] = json_encode($style_formats);
                }

                return $settings;

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
            if( file_exists(MVC_THEME_DIR . 'ie10.css') ){
                do_action('ie-head');
                printf( '<link rel="stylesheet" type="text/css" href="%sie10.css" /><![endif]-->', MVC_THEME_URL );
            }
        } 
                
        //ie 9 only
        if( file_exists(MVC_THEME_DIR . 'ie9.css') ){
            echo '<!--[if IE 9]>';
            do_action('ie9-head');
            printf( '<link rel="stylesheet" type="text/css" href="%sie9.css" /><![endif]-->', MVC_THEME_URL );
        }
        
        //ie 8 only
        if( file_exists(MVC_THEME_DIR . 'ie8.css') ){
            echo '<!--[if IE 8]>';
            do_action('ie8-head');
            printf('<link rel="stylesheet" type="text/css" href="%sie8.css" /><![endif]-->', MVC_THEME_URL );
        }
        
        //ie 7 only
        if( file_exists(MVC_THEME_DIR . 'ie7.css') ){
            echo '<!--[if IE 7]>';
            do_action('ie7-head');            
            printf( '<link rel="stylesheet" type="text/css" href="%sie7.css" /><![endif]-->', MVC_THEME_URL );
        }
        
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
        if( file_exists(MVC_THEME_DIR . $this->browser . '.css') ){
           wp_enqueue_style(
               $this->browser . '-child-css',
               MVC_THEME_URL . $this->browser . '.css'
                       );
        }
    }
    
    
    
    /**
     * Add the admin.js and admin.css file to the dashboard
     *
     * @see the js and css dirs in the child theme
     * 
     * @uses called by construct()
     * @uses to change the local admin css use the filter 'mat-local-admin-css'
     * 
     */
    function admin_js(){
        
        if( file_exists(MVC_THEME_DIR.'js/admin.js') ){
            wp_enqueue_script(
                'mvc-admin-js',
                 MVC_JS_URL. 'admin.js',
                 array('jquery' )
            );
            $dirs = array( 'IMG'          => MVC_IMAGE_URL,
                           'THEME'        => MVC_THEME_URL,
                           'LOADING_ICON' => MVC_IMAGE_URL.'loading.gif' 
            );

            wp_localize_script( 'mvc-admin-js', 'DIRS', $dirs );

			foreach( self::$localize_admin as $var => $data ){
				wp_localize_script( 'mvc-admin-js', $var, $data );	
			}
			

            //to localize stuff
            do_action('mvc_admin_js', 'mvc-admin-js');
        }

        if( file_exists(MVC_THEME_DIR.'admin.css') ){
            wp_enqueue_style(
                'mvc-admin-styles',
                MVC_THEME_URL . 'admin.css' //The location of the style
            );
        } elseif( file_exists(MVC_THEME_DIR.'css/admin.css') ){
            wp_enqueue_style(
                'mvc-admin-styles',
                MVC_THEME_URL . 'css/admin.css' //The location of the style
            );
        }

    }

    /**
     * Add the child.js file to the site
     * 
     * @since 12.5.13
     * 
     * @uses called by __construct()
     * @uses also add a global js variable with includes the commonly used dirs called 'DIR'
     * @uses adds the mobile stylesheets if defined in the theme config
     *
     */
    function add_js_css(){

        if( file_exists(MVC_THEME_DIR.'js/child.js') ){
            wp_enqueue_script(
                'mvc-child-js',
                MVC_JS_URL. 'child.js',
                array('jquery' )
            );

            $dirs = array(
                'IMG'          => MVC_IMAGE_URL,
                'THEME'        => MVC_THEME_URL,
                'LOADING_ICON' => MVC_IMAGE_URL.'loading.gif',
                'ADMIN_URL'    => get_admin_url()
            );

            wp_localize_script( 'mvc-child-js', 'DIRS', $dirs );
			
			foreach( self::$localize as $var => $data ){
				wp_localize_script( 'mvc-child-js', $var, $data );	
			}

            //to localize stuff
            do_action('mvc_child_js', 'mvc-child-js');
        }
        
        
        //For custom css files when using with plugins
        if( file_exists(MVC_THEME_DIR.'child.css') ){
            wp_enqueue_style(
                'mvc-child-styles',
                MVC_THEME_URL . 'child.css' //The location of the style
            );
        } elseif( file_exists(MVC_THEME_DIR.'css/child.css') ){
            wp_enqueue_style(
                'mvc-child-styles',
                MVC_THEME_URL . 'css/child.css' //The location of the style
            );
        }
        

        //Add the mobile Style if required
        if( current_theme_supports('mobile_responsive') ){
            if( file_exists(MVC_THEME_DIR.'mobile/mobile-responsive.css') ){
                wp_enqueue_style(
                    'mvc-mobile-styles',
                    MVC_MOBILE_URL . 'mobile-responsive.css' //The location of the style
                );
            }

            //Add the mobile script or the non mobile script based on device
            if( !self::is_mobile() ){
                if( file_exists(MVC_THEME_DIR.'mobile/desktop.js') ){
                    wp_enqueue_script(
                        'mvc-non-mobile-script',
                        MVC_MOBILE_URL. 'desktop.js',
                        array('jquery')
                    );

                }
            } else {

                //Add the tablet stuff
                if( self::is_tablet() ){
                    wp_enqueue_style(
                        'mvc-tablet-styles',
                        MVC_MOBILE_URL . 'tablet.css' //The location of the style
                    );

                    if( file_exists(MVC_THEME_DIR.'mobile/tablet.js') ){
                        wp_enqueue_script(
                            'mvc-tablet-script',
                             MVC_MOBILE_URL. 'tablet.js',
                             array('jquery')
                        );
                    }
                }

                //Add the phone stuff
                if( self::is_phone() ){
                    wp_enqueue_style(
                        'mvc-phone-styles',
                        MVC_MOBILE_URL . 'phone.css' //The location of the style
                    );

                    if( file_exists(MVC_THEME_DIR.'mobile/phone.js') ){
                        wp_enqueue_script(
                            'mvc-phone-script',
                            MVC_MOBILE_URL. 'phone.js',
                            array('jquery')
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
	public static function get_instance() {
		if( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}
    