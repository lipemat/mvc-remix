<?php

/**
 * Optional CSS and JS handler for the theme
 * Allow for targeting specific browsers and such with css file names
 * 
 * @since 10.2.13
 * 
 * @uses add_theme_support('mvc_styles');
 */
class MvcStyles extends MvcFramework{
        
    function __construct(){
         add_action( 'init', array( $this, 'browser_support' ) );
             //Add the IE only Stylesheets
         add_action('wp_head', array( $this, 'ie_only'), 99 );
            //Add Javascript to Site
         add_action('wp_enqueue_scripts', array( $this, 'add_js_css' ) );
            //Add Js and CSS to Admin
         add_action( 'admin_print_scripts', array( $this, 'admin_js' ) );
            //Add stylesheet to editor
         add_filter('mce_css',array( $this, 'editor_style' ) );
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
            if( file_exists(THEME_FILE_DIR . 'ie10.css') ){
                do_action('ie-head');
                printf( '<link rel="stylesheet" type="text/css" href="%sie10.css" /><![endif]-->', THEME_DIR );
            }
        } 
                
        //ie 9 only
        if( file_exists(THEME_FILE_DIR . 'ie9.css') ){
            echo '<!--[if IE 9]>';
            do_action('ie9-head');
            printf( '<link rel="stylesheet" type="text/css" href="%sie9.css" /><![endif]-->', THEME_DIR );
        }
        
        //ie 8 only
        if( file_exists(THEME_FILE_DIR . 'ie8.css') ){
            echo '<!--[if IE 8]>';
            do_action('ie8-head');
            printf('<link rel="stylesheet" type="text/css" href="%sie8.css" /><![endif]-->', THEME_DIR );
        }
        
        //ie 7 only
        if( file_exists(THEME_FILE_DIR . 'ie7.css') ){
            echo '<!--[if IE 7]>';
            do_action('ie7-head');            
            printf( '<link rel="stylesheet" type="text/css" href="%sie7.css" /><![endif]-->', THEME_DIR );
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
        if( file_exists(THEME_FILE_DIR . $this->browser . '.css') ){
           wp_enqueue_style(
               $this->browser . '-child-css',
               THEME_DIR . $this->browser . '.css'
                       );
        }
    }
    
    
    
            /**
     * Add the admin.js and admin.css file to the dashboard
     * @see the js and css dirs in the child theme
     * @uses called by construct()
     * @uses to change the local admin css use the filter 'mat-local-admin-css'
     * @since 8.1.13
     */
    function admin_js(){
        
       //Some Styling to let you know you are local 
       if( $_SERVER['SERVER_ADDR'] == '127.0.0.1' ){
           ob_start();
           ?><style type="text/css">
               #adminmenuback,#adminmenuwrap{background-color:#df01d7;background-image:-ms-linear-gradient(180deg,#04b431,#df01d7);background-image:-moz-linear-gradient(180deg,#619bbb,#df01d7);background-image:-o-linear-gradient(180deg,#619bbb,#df01d7);background-image:-webkit-linear-gradient(180deg,#619bbb,#df01d7);background-image:linear-gradient(180deg,#619bbb,#df01d7)}a,#adminmenu a,#the-comment-list p.comment-author strong a,#media-upload a.del-link,#media-items a.delete,.plugins a.delete,.ui-tabs-nav a{color:black}#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,#adminmenu li.current a.menu-top,.folded #adminmenu li.wp-has-current-submenu,.folded #adminmenu li.current.menu-top,#adminmenu .wp-menu-arrow,#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head{background-color:#777;background-image:-ms-linear-gradient(bottom,#610b5e,#3b0b17);background-image:-moz-linear-gradient(bottom,#610b5e,#3b0b17);background-image:-o-linear-gradient(bottom,#610b5e,#3b0b17);background-image:-webkit-linear-gradient(bottom,#610b5e,#3b0b17);background-image:linear-gradient(bottom,#610b5e,#3b0b17);transform:rotate(30deg)}#adminmenu li.wp-has-current-submenu{-moz-box-shadow:0 0 2px 2px black;-webkit-box-shadow:0 0 2px black;box-shadow:0 0 2px 2px black}
             </style>
           <?php 
           echo apply_filters('mvc-local-admin-css', ob_get_clean() );
       }
        
        if( file_exists(THEME_FILE_DIR.'js/admin.js') ){
             wp_enqueue_script(
                    'mvc-admin-js',
                    JS_DIR. 'admin.js',
                    array('jquery' )
             );
             $dirs = array( 'IMG'      => IMAGE_DIR,
                       'THEME'    => THEME_DIR,
                       'INCLUDES' => SCRIPT_DIR,
                       'LOADING_ICON' => IMAGE_DIR.'loading.gif' );
         
             wp_localize_script('mvc-admin-js', 'DIRS', $dirs);
        
            //to localize stuff
            do_action('mvc_admin_js', 'mvc-admin-js');
             
        }
        
        if( file_exists(THEME_FILE_DIR.'admin.css') ){
            wp_enqueue_style(
                'mvc-admin-styles',
                THEME_DIR . 'admin.css' //The location of the style
            );
        }
        
         
    }
    
    
    
        /**
     * Add the child.js file to the site
     * @since 8.1.13
     * @uses called by __construct()
     * @uses also add a global js variable with includes the commonly used dirs called 'DIR'
     * @uses adds the mobile stylesheets if defined in the theme config
     * 
     */
    function add_js_css(){
        
        if( file_exists(THEME_FILE_DIR.'js/child.js') ){
            wp_enqueue_script(
                'mvc-child-js',
                JS_DIR. 'child.js',
                array('jquery' )
            );
         
            $dirs = array( 
                        'IMG'     => IMAGE_DIR,
                       'THEME'    => THEME_DIR,
                       'INCLUDES' => SCRIPT_DIR,
                       'LOADING_ICON' => IMAGE_DIR.'loading.gif' );
         
            wp_localize_script('mvc-child-js', 'DIRS', $dirs);
        
            //to localize stuff
            do_action('mvc_child_js', 'mvc-child-js');
        }
        
        
        //Add the mobile Style if required
        if( current_theme_supports('mobile_responsive') ){
              if( file_exists(THEME_FILE_DIR.'mobile/mobile-responsive.css') ){
                wp_enqueue_style(
                    'mvc-mobile-styles',
                    MOBILE_DIR . 'mobile-responsive.css' //The location of the style
                );
              }


            //Add the mobile script or the non mobile script based on device
            if( !self::is_mobile() ){
               if( file_exists(THEME_FILE_DIR.'mobile/desktop.js') ){
                    wp_enqueue_script(
                    'mvc-non-mobile-script',
                    MOBILE_DIR. 'desktop.js',
                    array('jquery')
                    );

                }
            } else {

                //Add the tablet stuff
                if( self::is_tablet() ){
                    wp_enqueue_style(
                        'mvc-tablet-styles',
                        MOBILE_DIR . 'tablet.css' //The location of the style
                    );


                    if( file_exists(THEME_FILE_DIR.'mobile/tablet.js') ){
                        wp_enqueue_script(
                            'mvc-tablet-script',
                            MOBILE_DIR. 'tablet.js',
                            array('jquery')
                        );
                    }
                }

                //Add the phone stuff
                if( self::is_phone() ){
                    wp_enqueue_style(
                        'mvc-phone-styles',
                        MOBILE_DIR . 'phone.css' //The location of the style
                    );


                    if( file_exists(THEME_FILE_DIR.'mobile/phone.js') ){
                        wp_enqueue_script(
                            'mvc-phone-script',
                            MOBILE_DIR. 'phone.js',
                            array('jquery')
                        );
                    }
                }
            } //-- End if mobile device

        } //-- End if Mobile Responsive Theme Support
  
    }
    
    
    
        
}
    