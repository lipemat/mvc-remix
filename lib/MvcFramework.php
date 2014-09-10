<?php 

/**
 * MvcFramework
 * 
 * Adds Many enhancements to the Genesis Theme
 * 
 * @uses automatically extended into the Model Views and Controllers and Bootstrap
 * 
 * @see Bootstrap.php
 * 
 * @author Mat Lipe <mat@matlipe.com>

 * @TODO Create a fragment caching class - run tests database vs files
 * @TODO create an auto shortcode registering class - see NUSD theme
 * @TODO create a way to serve up all js or css files from one php file like mvc_add_style() and mvc_add_js() to prevent all the requests - maybe grunt.js
 * @TODO Add the Custom Image Sizes to the Media Uploader. Ponder a way to decided which ones are requires so the user won't see like a billion of them
 * 
 * @class MvcFramework
 * @package MVC
 *
 */
if( class_exists('MvcFramework') ) return;  
class MvcFramework{
    public $browser     = false; //Keep track to the views browser
    private $mobile     = false; //Allows for constructing mobile detect class only once
    protected $controller; //Keep track of what controller is controlling to call stuff dynamically
    
    
    private static $body_classes = array();
    

    /** Placeholder to prevent issues **/
    function init(){
        return false;
    }
    
    
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
    function __call($func, $args){
        
        //For Formatting Methods
        if( current_theme_supports('mvc_format') ){
            if( method_exists('MvcFormat', $func) ){
				mvc_format()->{$func}($args);    
            }
        }
        
        
        //For Special Views
        if( (strpos($func,'view') !== false) || (strpos($func,'View') !== false) ){
            $this->view(str_replace(array('View_','view_'),array('',''), $func), false, $args );   
            return;
        }
        
        //For Calling Fitlers Directly
        if( (strpos($func,'filter') !== false) || (strpos($func,'Filter') !== false) ){
           return $this->filter(str_replace(array('Filter_','filter_'),array('',''), $func), false, $args );   
        } 
        
        //For Returning Special Strings
        if( (strpos($func,'return') !== false) || (strpos($func,'Return') !== false) ){
           return str_replace( array('Return_','return_'), array('',''), $func );
        } 
        
        //For echoing Special Strings
        if( (strpos($func,'echo') !== false) || (strpos($func,'Echo') !== false) ){
           echo str_replace( array('echo_','Echo_'), array('',''), $func );
           return;
        } 
        
        //For Widget Areas
        if( (strpos($func,'widget') !== false) || (strpos($func,'Widget') !== false) ){
            $this->widgetArea( self::human_format_slug(str_replace(array('Widget_','widget_'),array('',''), $func)) ); 
            return;  
        }
        
        //For Sidebars
        if( (strpos($func,'sidebar') !== false) || (strpos($func,'Sidebar') !== false) ){
            $this->sidebar( self::human_format_slug(str_replace(array('Sidebar_','sidebar_'),array('',''), $func)) ); 
            return;  
        }
        
        
     
        echo '<pre>';
            debug_print_backtrace();
        echo '</pre>';
        trigger_error($func. ' Does Not Exist as a Method ', E_USER_ERROR);
               
    }


    /**
     * Magic function which allows for calling pretty much any class available by name using $this->%name%
     *
     * @uses call any helper or whatever using $this->%helperClass%->%method%
     * @since 3.5.0
     * 
     * @since 10.2.13
     */
    function __get($object){

        if( !class_exists($object) ){
            if( file_exists(MVC_THEME_DIR.'lib/'.$object.'.php') ){
                require_once( MVC_THEME_DIR.'lib/'.$object.'.php' );
            } elseif( file_exists(MVC_THEME_DIR.'lib/helpers/'.$object.'.php') ){
                require_once( MVC_THEME_DIR.'lib/helpers/'.$object.'.php' );
            } elseif( file_exists(MVC_THEME_DIR.'lib/optional/'.$object.'.php') ){
                require_once( MVC_THEME_DIR.'lib/optional/'.$object.'.php' );       
            } else {
                echo '<pre>';
                        debug_print_backtrace();
                echo '</pre>';
                trigger_error($object. ' Does Not Exist as a Class ', E_USER_ERROR);
            }
        }
        
        $this->{$object} = new $object;
        return $this->{$object};
    }
    
    
     /**
     * @deprecated
      * @see /classes/Custom_Post_Type.php
     **/
    function registerPostType($title, $args = array()){
        $this->MvcPostTypeTax->register_post_type($title, $args);
        
    }
    
    
     /**
     * @deprecated
     * @see /classes/Taxonomy.php
     */
    function registerTaxonomy( $title, $post_type = '', $args = array() ){
        $this->MvcPostTypeTax->register_taxonomy($title, $post_type, $args );
    }


	/**
	 * Move Genesis Meta Boxes To Bottom
	 *
	 * Move the genesis layout and seo to bottom of post edit screen
	 *
	 *
	 * @return void
	 */
	function move_genesis_meta_boxes_to_bottom(){
		//Move the genesis meta box below our special ones
		if( function_exists( 'genesis_add_inpost_layout_box' ) ){
			remove_action( 'admin_menu', 'genesis_add_inpost_layout_box' );
			add_action( 'do_meta_boxes', 'genesis_add_inpost_layout_box' );
			remove_action( 'admin_menu', 'genesis_add_inpost_seo_box' );
			add_action( 'do_meta_boxes', 'genesis_add_inpost_seo_box' );
		}

	}


    
    /**
     * @deprecated
     *
     * @see mvc_string()->getYoutubeImage()
     */
    public function getYoutubeImage($embed) {
	    return mvc_string()->getYoutubeImage( $embed );
    }
    
    
    
    
    /** 
     * Get the first image of the post's content
     * 
     * @param int [$post_id] - defaults to global $post
     * @since 9.13.13
     * 
     *      
     * */
    public function getFirstContentImage( $post_id = false ) {
        global $post;
        
        if ( ! $post_id && ! isset( $post->ID ) ) return;
        
        if ( $post_id != false && $post_id == $post->ID ) {
            $content = $post->post_content;
        } else {
            $content = get_post_field( 'post_content', $post_id );
        }
        
        if ( is_wp_error( $content ) || empty( $content ) ) return;
        
        $first_img = '';
        ob_start();
        ob_end_clean();
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
        if ( ! isset( $matches[1][0] ) ) return;
        
        $first_img = $matches[1][0];
        
        return $first_img;
    }
    
    
    /**
     * Check if we are on a blog type page
     * 
     * @uses returns true for Blog Template, Post, Post Archive, 'Date Archive', 'Category'
     * 
     * @return bool
     * @uses must bee called after 'wp' like using before()
     * 
     * @since 5.42.0
     */
    function isBlogPage(){
        if ( is_page_template('page_blog.php') || is_post_type_archive('post') || is_singular('post') || is_category() ||   (is_date() && get_post_type() == 'post')) {
            return true; 
        } 
        
        return false;
        
    }
	
	/**
     * @deprecated use mvc_format()->changeSidebar()
	 * 
	 * @uses mvc_format()->changeSidebar();
     */
    function changeSidebar( $sidebar ){
    	
    	mvc_format()->changeSidebar( $sidebar );
		
    }
    
    
    /**
     * Adds the mediaUploader js to the site
     * 
     * @since 5.3.0
     * 
     * 
     * @since 12.16.13
     * @param string [$screen] - the screen to add this to
     * 
     * @uses button to trigger uploader must have class 'upload_image' and rel which matches id of text field
     * @example <input type="button" rel="category-image" value="Click to Upload" class="button-secondary upload-image"/>
     * 
     * @uses add_action('admin_head', array($this, 'mediaUploader') ); // For admin
     * @uses add_action('get_header', array($this, 'mediaUploader') ); // For Frontend
     */
    function mediaUploader($screen = false){
        
        if( $screen ){
            if( function_exists('get_current_screen') ){
                if( $screen = get_current_screen() ){
                    if( get_current_screen()->base != $screen ) return;
                } else {
                    return false;
                }
            }
        }
         
        wp_enqueue_media();
        ?>
        <script type="text/javascript">

          jQuery(document).ready(function($){
                var _custom_media = true,
                _orig_send_attachment = wp.media.editor.send.attachment;

                $('.image_upload').click(function(e) {
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        var button = $(this);
                        var id = button.attr('rel');
                         _custom_media = true;
                        wp.media.editor.send.attachment = function(props, attachment){
                            if ( _custom_media ) {
                                jQuery.event.trigger('MVCImageUploadReturn', [attachment.url, attachment, props]);
                                $("#"+id).val(attachment.url);
                            } else {
                                return _orig_send_attachment.apply( this, [props, attachment] );
                            };
                        }

                        wp.media.editor.open(button);
                        return false;
                  });

                $('.add_media').on('click', function(){
                    _custom_media = false;
                });
           });
      </script>
      <?php  
    }
     
        
    /**
     * @deprecated 6.10.14
	 * @see MvcStyles::add_font()
     */
    function addFont( $families ){  	
		mvc_styles()->add_font( $families );
    }


    /**
     * @deprecated 6.10.14
	 * @see MvcStyles::add_js()
     */
    function addJs($file){
        mvc_styles()->add_js( $file );
	
    }


    /**
     * Returns the Attachments ID using the url
     * 
     * @since 4.3.0
     * @param string $attachment_url - the url
     * 
     * @uses must be a url of an image uploaded via wordpress
     */
    function getAttachmentIdbyUrl($attachment_url = '' ) {
 
        global $wpdb;
        $attachment_id = false;
 
        // If there is no url, return.
        if ( '' == $attachment_url ) return;
 
        // Get the upload directory paths
        $upload_dir_paths = wp_upload_dir();
 
        // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
        if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
 
            // If this is the URL of an auto-generated thumbnail, get the URL of the original image
            $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
 
            // Remove the upload path base directory from the attachment URL
            $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
            
            // Finally, run a custom database query to get the attachment ID from the modified attachment URL
            $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
 
            }
 
        return $attachment_id;
    }
    


   /**
     * Retrieve the widgets instance data and id
     * optionaly specify the widgets Area name to only retrieve those
     * 
     * @since 3.8.0
     * @uses Must be Called after the functions.php file has loaded or non default sidebars do not exist yet - unless you don't care about none default ones
     *
     * @param array $args = array()
     *
     * Available Args:
     *                  'string [sidebar_name] - Name of Widget Area'
     *                  'string [widget_name] - Name of Registered Widget'
     *                  'bool [inactive_widgets] - to include inactive widgets - only works when not specifing $sidebar_name'
     *                  'bool [object_data] - to return full object including class information'
     *                  'bool [include_output'] - to include the output of the widgets'
     * 
     * @since 4.18.13
     */
    function getWidgetData($args = array()) {
        
        $defaults = array(
                        'sidebar_name'     => false,
                        'widget_name'      => false,
                        'inactive_widgets' => false,
                        'object_data'      => false,
                        'include_output'   => false
                        );
        $args = wp_parse_args($args, $defaults);
        
        extract( $args );

        global $wp_registered_sidebars, $wp_registered_widgets;
    
        // Holds the final data to return
        $output = array();
        if( $sidebar_name ){
            // Loop over all of the registered sidebars looking for the one with the same name as $sidebar_name
            $sibebar_id = false;
            foreach( $wp_registered_sidebars as $sidebar ) {
                if( $sidebar['name'] == $sidebar_name ) {
                    // We now have the Sidebar ID, we can stop our loop and continue.
                    $sidebar_id = $sidebar['id'];
                    break;
                }
            }

            if( !$sidebar_id ) {
                // There is no sidebar registered with the name provided.
                return $output;
            } 
            $sidebars_widgets = wp_get_sidebars_widgets();
            $widget_ids = $sidebars_widgets[$sidebar_id];
            
        } else {
            $sidebars_widgets = wp_get_sidebars_widgets();

            $widget_ids = array();
            foreach( $sidebars_widgets as $sidebar_id => $widgets ){
                if( $sidebar_id != 'wp_inactive_widgets' || $inactive_widgets ){
                    $widget_ids = array_merge($widget_ids, $widgets); 
                }
            }
        }

      if( !$widget_ids ) {
          // Without proper widget_ids we can't continue. 
          return array();
      }
    
        // Loop over each widget_id so we can fetch the data out of the wp_options table.
        foreach( $widget_ids as $id ) {
            if( $widget_name && $wp_registered_widgets[$id]['name'] != $widget_name ) continue;
            // The name of the option in the database is the name of the widget class.  
            $option_name = $wp_registered_widgets[$id]['callback'][0]->option_name;
            
            
            //If selected to include the output of the widget
            if( $include_output ){
                $params = array_merge( array( array_merge( $sidebar, 
                    array('widget_id' => $id, 
                    'widget_name' => $wp_registered_widgets[$id]['name']) ) ),
                    (array) $wp_registered_widgets[$id]['params']
                );
                // Substitute HTML id and class attributes into before_widget
                $classname_ = '';
                foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
                    if ( is_string($cn) )
                            $classname_ .= '_' . $cn;
                    elseif ( is_object($cn) )
                        $classname_ .= '_' . get_class($cn);
                }
                $classname_ = ltrim($classname_, '_');
                $classname_ .= ' from-get-widget-data';
                $params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);

                $callback = $wp_registered_widgets[$id]['callback'];

                if ( is_callable($callback) ) {
                    ob_start();
                    call_user_func_array($callback, $params);
                    $output[$id]['output'] = apply_filters('mvc_dynamic_sidebar_output', ob_get_clean(), $callback, $params, $id ); 
                } else {
                    $output[$id]['output'] = false;
                }
            }
            
        
            // Widget data is stored as an associative array. To get the right data we need to get the right key which is stored in $wp_registered_widgets
            $key = $wp_registered_widgets[$id]['params'][0]['number'];
            $widget_data = get_option($option_name);    
            $output[$id]['data'] = (object) $widget_data[$key];
            $output[$id]['name'] = $wp_registered_widgets[$id]['name'];
            if( $object_data ){
                $output[$id]['object_data'] = $wp_registered_widgets[$id];
            }
           
        }
    
        return $output;
    }
    
    
    /**
     * Returns the output of the proper View File to a filter
     * 
     * @since 11.27.13
     * 
     * @uses call with no param and it will pull the view file matching the method name from the controller named folder
     * @uses accepts extra param which will be turned into variables in the view
     * 
     * @param $file the view file to use
     * @param $folder the view folder to use
     * @param Array $args will be extracted into usable args is associate array otherwise will be avaiable as is in view
     * @param bool [$hideInfo] - to remove the <!-- comments --> (defaults to false);
     * 
     * @return string
     */
    function filter( $file = false, $folder = false, $args = array(), $hideInfo = false ){
         ob_start();
         $this->view($file, $folder, $args, $hideInfo);
         return ob_get_clean();
    }
    
    
    
    /**
     * Calles the Proper view file from a controller
     * 
     * @since 12.2.13
     * 
     * @uses call with no param and it will pull the view file matching the method name from the controller named folder
     * @uses accepts extra param which will be turned into variables in the view
     * @uses all keys set using $this->set() will be extracted into usable variables in view
     * 
     * @param $file the view file to use
     * @param $folder the view folder to use
     * @param Array $args will be extracted into usable args is associate array otherwise will be avaiable as is in view
     * @param bool [$hideInfo] - to remove the <!-- comments --> (defaults to false);
     * 
     * @return void
     */
    function view( $file = false, $folder = false, $args = array(), $hideInfo = false ){
        $MvcString = $this->MvcString;
        $MvcForm = $this->MvcForm;
        
        if( !$folder ){
            $folder = $this->getController();
            if( defined( "MVC_CONTROLLER_PREFIX" ) && MVC_CONTROLLER_PREFIX ){
                $folder = str_replace(  MVC_CONTROLLER_PREFIX, "", $folder ); 
            }
        }

	    if( $folder != "" ){
		    $folder .= "/";
	    }

        if( !$file ){
            list(, $caller) = debug_backtrace(false);
            $file = $caller['function'];
        }


        if( !$hideInfo ){
            echo '<!-- View/'.$folder . $file . '.php -->';
        }

	    if( $file = $this->locate_template( 'View/'.$folder . $file . '.php' ) ){
		    //Any arguments will be available via variable
		    extract($args);

		    //Any keys set for this view will also be extracted
		    extract( $this->get() );
		    
		    include( $file );
	    } else {
		    echo __( 'The file does not exist View/'.$folder . $file . '.php', 'mvc' );
	    }

    }


	/**
	 * locate_template
	 *
	 * Check in each mvc_dir for a matching file
	 * Starts with the 0 key in the mvc_theme_dirs array which is typically the active theme
	 *
	 * @param array|string $path_relative_to_mvc_dir
	 * @param bool $url - return the url ( defaults to false )
	 *
	 * @example 'View/Product/title.php'
	 *
	 * @return bool|string - full path to file or false on failure to locate
	 */
	public function locate_template( $paths_relative_to_mvc_dir, $url = false, $load = false ){
		foreach( self::get_mvc_dirs() as $dir ){

			$dir = untrailingslashit( $dir );

			foreach ( (array) $paths_relative_to_mvc_dir as $path_relative_to_mvc_dir ) {
				if( file_exists( $dir . '/' . $path_relative_to_mvc_dir ) ){
					if( $url ){
						$content_url = untrailingslashit( dirname( dirname( get_stylesheet_directory_uri() ) ) );
						$content_dir = str_replace('\\', '/', untrailingslashit( dirname( dirname( get_stylesheet_directory() ) ) ) );
						$dir = str_replace('\\', '/', $dir );
						$dir = str_replace( $content_dir, $content_url, $dir );
					}

					if( $load ){
						include( $dir . '/' . $path_relative_to_mvc_dir );
						return true;
					} else {
						return $dir . '/' . $path_relative_to_mvc_dir;
					}
				}
			}
		}

		return false;
	}


	/**
	 * get_mvc_dirs
	 *
	 * Retrieve the list of mvc_dirs based on theme, parent theme, and filter
	 *
	 * @static
	 *
	 * @return array|mixed|void
	 */
	public static function get_mvc_dirs(){
		$dirs = array();

		$dirs[] = get_stylesheet_directory();

		if( get_template_directory() != get_stylesheet_directory() ){
			$dirs[] = get_template_directory();
		}

		if( defined( "MVC_THEME_DIR" ) ){
			if( MVC_THEME_DIR != get_stylesheet_directory() && MVC_THEME_DIR != get_template_directory() ){
				$dirs[] = MVC_THEME_DIR;
			}
		}

		$dirs = apply_filters( 'mvc_theme_dirs', $dirs );

		return $dirs;

	}


    

    /**
     * Uses to set global variables which can be collected in views
     * @since 1.11.13
     * @uses $this->set('helloData', 'hello' );
     * @uses sets a key in the global data array which matches the controller and holds the data
     * * Data can be retrieved by using $this->get('helloData') in the View
     * @param string $name key
     * @param mixed $data the data to store
     */
    function set($name, $data){
        global $controllerViewGlobals;
        $controllerViewGlobals[$this->getController()][$name] = $data;        
    }
    
    /**
     * Gets the name of the Current Controller to allow for automation
     * @since 1.13.13
     */
    function getController(){
        if( $this->controller ) return $this->controller;
        $this->controller = str_replace('Controller', '', get_class($this));
        return $this->controller;
    }
    
    /**
     * Get a complete Controller Object
     * @since 1.0.1
     * 
     * @param string $controller - name of controller
     */
    function getControllerObject($controller){
        global $mvc_theme;

        return $mvc_theme['controllers'][$controller];
        
    }
    

    /**
     * Retreive data set in a controller with set()
     * 
     * 
     * @uses $this->get('key');
     *
     * @uses may only be used inside a view to retreive data set from its controller
     * 
     * @param string $name [optional] of the key defaults to all that has been set
     * @param string [$controller] - the class to retrieve from default to current
     *
     */
    function get($name = false, $controller = false){
        global $controllerViewGlobals;
        
        if( empty( $controller ) ){
            $controller =  $this->getController();  
        }
        
        if( !$name ){
            if( empty( $controllerViewGlobals[$controller] ) ){
                return array();
            }
           return $controllerViewGlobals[$controller];  
        }
        
        if( isset( $controllerViewGlobals[$controller][$name] ) ){
            return $controllerViewGlobals[$controller][$name];
        }
        return false; //nothing set
    }
    

    
    /**
     * Checks to see if on a mobile device
     * @uses will return true if on a phone or tablet
     * @see is_phone() or is_tablet() for more refined
     * @return boolean
     * @since 1.7.13
     */
    function is_mobile(){
        if( !$this->mobile ){
            $this->mobile = new MvcMobileDetect();
        }
        
        //placeholder for the results so we don't have to run again
        if( isset( $this->ismobile ) ) return $this->ismobile;
        
        if( $this->mobile->isMobile() ){
            $this->ismobile = true;
        
        } else {
            $this->ismobile = false;
        }
        return $this->ismobile;
    }
    
    
    /**
     * Detects if on a specific device
     * @param string $device the device by
     *  * Mobile Browser
     *  * Operating System
     *  * Name
     * @uses for a complete list see the protected vars on the mat_Mobile_Detect Class
     * @return boolean
     * @since 4.22.13
     */
    function is_mobile_device( $device ){
        if( !$this->mobile ){
            $this->mobile = new MvcMobileDetect();
        }
        
        if( $this->mobile->{'is'.$device}() ){
            return true;
        }
        return false;
    }

    /**
     * Checks to see if on a tablet
     * @uses will return true if on a tablet
     * @see is_mobile() or is_phone() for other detections
     * @return boolean
     * @since 4.22.13
     */
    function is_tablet(){
    
        if( !$this->mobile ){
            $this->mobile = new MvcMobileDetect();
        }

        //placeholder for the results so we don't have to run again
        if( isset( $this->istablet ) ) return $this->istablet;
        
        if( $this->mobile->isTablet() ){
            $this->istablet = true;
        } else {
            $this->istablet = false;
        }
        
        return $this->istablet;

    }
    
    
    /**
     * Checks to see if on a phone
     * @uses will return true if on a phone and not on a tablet
     * @see is_mobile() or is_tablet() for other detections
     * @return boolean
     * @since 2.19.12
     */
    function is_phone(){
    
        if( !$this->mobile ){
            $this->mobile = new MvcMobileDetect();
        }
    
       //placeholder for the results so we don't have to run again
        if( isset( $this->isphone ) ) return $this->isphone;
    
        if( $this->mobile->isMobile() && !$this->mobile->isTablet() ){
            $this->isphone = true;
        } else {
            $this->isphone = false;
        }
        return $this->isphone;
    }
    
    


    /**
     * Returns the featured image or the first on uploaded if no feature exists
     * @since 5.5.0
     * 
     * @since 7.22.13
     * @param string $size the size of the image defaults to 'thumbnail'
     * @param int [optional] $post_id the id of the post
     * @param bool $html or object format defaults html
     */
    function getFirstImage( $size = 'thumbnail', $postId = false, $html = true ){
    
        //Use the current post's id of one was not sent
        if( !$postId ){
            global $post;
            $postId = $post->ID;
        }
    
        //If the post has a thumbnail
        if( has_post_thumbnail($postId) ){
            if( $html ){
                return get_the_post_thumbnail( $postId, $size );
            } else {
                $image['ID']  = get_post_thumbnail_id( $postId );          
                return get_image_data($image['ID'], $size);
            }
        }
 
        //Retrieve the First Image uploaded to the post if no thumbnail
        $image = get_children(
                array(
                        'post_parent'    => $postId,
                        'post_type'      => 'attachment',
                        'post_mime_type' => 'image',
                        'orderby'        => 'menu_order',
                        'order'          => 'ASC',
                        'numberposts'    => '1',
                        'fields'         => 'ids'
                )
        );
        
        if( empty( $image ) ){
            return false;
        }
        
        $image = (array) reset( $image );
        $image['ID'] = $image[0];
    
    
        //If just needs an html image return the image
        if( $html ){
            return wp_get_attachment_image($image['ID'], $size );
        } else {
            return get_image_data( $image['ID'], $size );
        }
    
    }
    
    /**
     * Retrieves all data for a particluar image
     * @param  $image_id
     * @return array|boolean
     * @uses returns false if no image returned
     * @uses called by self::get_first_image()
     * @since 2.11.14
     */
    function getImageData( $image_id, $size = 'thumbnail' ){

        if( !is_numeric( $image_id ) ){
            $image_id = $this->getAttachmentIdbyUrl($image_id);   
        }
        
        if( empty( $image_id ) ) return false;
        
        $image['ID'] = $image_id;
        $src = wp_get_attachment_image_src($image['ID'], $size);   
        $image['url'] = wp_get_attachment_image_src($image['ID'], $size);

        $image['meta'] = wp_get_attachment_metadata( $image['ID'], true);
        $folder = explode('/', $image['meta']['file']);
        array_pop($folder);
        $dir = wp_upload_dir();
        $folder = $dir['baseurl'].'/'.implode('/', $folder);
        
        foreach( $image['meta']['sizes'] as $size => $data ){
            $image[$size] = $folder.'/'.$data['file'];
        }
        
        if ($src){
            list($src, $width, $height) = $src;
            $hwstring = image_hwstring($width, $height);
            if ( is_array($size) )
                $size = join('x', $size);
            $attachment =& get_post($image['ID']);
            $data = array(
                    'src'   => $src,
                    'class' => "attachment-$size",
                    'alt'   => trim(strip_tags( get_post_meta($image['ID'], '_wp_attachment_image_alt', true) )), // Use Alt field first
                    'title' => trim(strip_tags( $attachment->post_title )),
            );
            // If not, Use the Caption
            if ( empty($data['alt']) )
                $data['alt'] = trim(strip_tags( $attachment->post_excerpt ));
            // Finally, use the title
            if ( empty($data['alt']) )
                $data['alt'] = trim(strip_tags( $attachment->post_title ));
            //Combine the image with the data
            $image = array_merge( $image, $data );
        } else {
            return false;
        }
  
        $image['full_size_url'] = wp_get_attachment_image_src($image['ID'], 'full');
        
        return $image;
    }
    



    /**
     * Retrieves all data for a particluar image
     * 
     * @deprecated use self::getImageData();
     *
     * @since 2.11.14
     */
    function get_image_data( $image_id, $size = 'thumbnail' ){
        
        return $this->getImageData($image_id, $size);
    }
    
    
    
    
    
    /**
     * Body Class
	 * 
	 * Adds a class to the body
	 * 
     * @example send a string to append to the body classes
     * @uses will be called automatically on the body_class filter to add some classed automatically
	 * 
     * @param string $classes  
     *
     */
    function body_class( $class ){
        global $post;

        //Handy little due for quick adding of classes
        if( is_string( $class ) ){
            self::$body_classes[] = $class;
            return;
			
        } elseif( is_array( $class ) ){
			$classes = $class;	
			
		} else {
			return;	
			
		}
           
        if( !empty( $post->ID ) ){   
            if( has_post_thumbnail() ){
                $classes[] = 'has-thumbnail';
            }
        }

        if( $this->isBlogPage() ){
            $classes[] = 'blog-page';   
        }

        //Add device classes
        if( current_theme_supports('mobile_responsive') ){
            if( self::is_mobile() ){
                $classes[] = 'mobile';
                if( self::is_phone() ){
                    $classes[] = 'phone';
                } elseif( self::is_tablet() ){
                    $classes[] = 'tablet';
                }
            } else{
               $classes[] = 'desktop';   
            }
        }
        
    
        
        //Add an archive class for the blog template
        if( $this->getPageTemplateName() ==  'page_blog' ){
            $classes[] = 'archive';
        }
              
 
        //Add a class for sub pages
        if( !is_home() && (strpos( $this->getPageTemplateName(), 'home') === false) ){
            $classes[] = 'sub';
        }

        //Add the page title as a class
        $classes[] = self::slug_format_human($post->post_title);


        if( empty( self::$body_classes ) ){
            return $classes;
        }

        return array_merge($classes, self::$body_classes );
    }
    
    
    /**
     * Returns the Current Templates Name
     * 
     * @since 2.3.0
     * 
     * @since 10.18.13
     * 
     * @return string | false in in admin
     */
    function getPageTemplateName(){
        global $post;    
        if( MVC_IS_ADMIN ) return false; 
        return str_replace('.php', '', get_post_meta($post->ID, '_wp_page_template', true));
    }
        
        
    
    
    /**
     * Outputs all the filters attached to a particular hook
     * @uses us with no arg to display all filters
     * @uses specify a hook to display the filter for just that one hook
     * @param string $hook The hook to display
     * @since 1.11.13
     */
    function showFilters( $hook = false ){
        global $wp_filter;
        
        //display all if not one specified
        if( !$hook ){
            print '<pre>';
                print_r( $wp_filter );
            print '</pre>';
        }
        
        //If the specified one is bogus
        if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
            return;
        
        print '<pre>';
            print_r( $wp_filter[$hook] );
        print '</pre>';
    }
    
    /**
     * Returns a human readable slug with the _ remove and words uppercase
     * @param string $slug
     * @return string
     * @since 3.11.13
     * 
     * @deprecated Use MvcString

     */
    public function human_format_slug( $slug ){
        return $this->MvcString->human_format_slug($slug);
    }
    
    /**
     * Turns and human readable phrase into a slug
     * @param string $human
     * @return string
     * @since 3.11.13
     * 
     * @deprecated Use MvcString
     */
    public function slug_format_human( $human ){
        return $this->MvcString->slug_format_human($human);
    }


    
    /**
     * Extract the post id from the global post or and object or int
     * 
     * @param int|WP_Post [$post] - (defaults to global $post );
     * 
     */
    function getPostId($post = false){
        
		$post = get_post( $post );
		
		return $post->ID;
		
    }
    
    /**
     * Checks to see if this page has a parent or is child of a specified page
     * @param mixed $page can be a page name or an id
     * @since 7/24/12
     */
    function is_subpage( $page = null ){
        global $post;
        // does it have a parent?
        if ( ! isset( $post->post_parent ) OR $post->post_parent <= 0 )
            return false;
        // is there something to check against?
        if ( ! isset( $page ) ) {
            // yup this is a sub-page
            return true;
        } else {
            // if $page is an integer then its a simple check
            if ( is_int( $page ) ) {
                // check
                if ( $post->post_parent == $page )
                    return true;
            } else if ( is_string( $page ) ) {
                // get ancestors
                $parent = get_ancestors( $post->ID, 'page' );
                // does it have ancestors?
                if ( empty( $parent ) )
                    return false;
                // get the first ancestor
                $parent = get_post( $parent[0] );
                // compare the post_name
                if ( $parent->post_name == $page )
                    return true;
            }
            return false;
        }
    }
    
    
    /**
     * Get Post Images
	 * 
	 * Retrives all the images attached to a post
	 * 
	 * 
     * @param array $args array(
     * -  bool   'html' - to return pre formatted images ( defaults to true )
     * -  bool   'include_featured' - to include the featured image or not ( defaults to false )
     * -  string 'size' - the image size as specified in add_image_size()
     * -  string 'wrap_start' - if using html what to wrap the element it e.g  <div>
     * -  string 'wrap_end' -  the closing wrap e.g. </div>
     * -  bool   'include_content_images' - to include images which appear in content - ( default false )
     * -  bool   'include_meta_images' -  to include images added to meta fields like tabs  ( default false )
     * -  string 'mvc-gallery' - The name of the gallery used when constructing MvcGallery(, $gallery)
     * 
     * @param WP_Post [$post] - ( defaults to global $post )
     * 
     */
    function getPostImages( $args, $post = null ){
        

		$post = get_post( $post );
        
        //Caching of the retrived image per gallery in case of multiple gallery calls on same page
        static $retrieved;
        static $retrieved_gallery;
        
         $defaults = array(
                    'html'                   => true,
                    'include_featured'       => false,
                    'size'                   => 'thumbnail',
                    'wrap_start'             => '',
                    'wrap_end'               => '',
                    'include_content_images' => false,
                    'include_meta_images'    => false,
                    'mvc_gallery'            => false
                 );
        
        $args = wp_parse_args($args, $defaults);

        extract( $args );

        $content_images = array();
        
        //to exclude the featured image
        if( $include_featured ){
            $exclude = '';
        } else {
            $exclude = get_post_thumbnail_id();
        }
    
        if( isset( $retrieved[$post->ID] ) && ($retrieved_gallery[$post->ID] == $mvc_gallery) ){
            //Use cached version if available
             $all_images = $retrieved[$post->ID];
        } else {
            $img_args = array(
                 'post_parent'    => $post->ID,
                 'post_status'    => 'inherit',
                 'post_type'      => 'attachment',
                 'post_mime_type' => 'image',
                 'order'          => 'ASC',
                 'orderby'        => 'menu_order ID',
                 'exclude'        => $exclude 
             );
  
            //Retrieve all the images in this posts gallery
            if( $mvc_gallery ){
                $images = get_post_meta( $post->ID, 'mvc-gallery-'.$mvc_gallery, true );
                if( empty( $images ) ) return false;
                
                unset( $img_args['post_parent'] );
                $img_args['numberposts'] = -1;
                $img_args['orderby'] = 'post__in'; 
                $img_args['post__in'] = $images;
                $all_images = get_posts( $img_args );
            } else {
                //REtrieve all the images attached to this post
                $all_images = get_children($img_args);
            }
        }
        

       //Retrieve the other possible sizes
       foreach( $all_images as $image ){
       		if( $size != 'default' ){
            	$image->{$size} = wp_get_attachment_image_src( $image->ID, $size );
                $image->guid = $image->{$size}[0];
            }
            $image->thumb = wp_get_attachment_image_src( $image->ID, 'thumbnail' );
            $image->medium = wp_get_attachment_image_src( $image->ID, 'medium' );
            $image->large = wp_get_attachment_image_src( $image->ID, 'large' );
       }

        //for caching;
        $retrieved[$post->ID] = $all_images;
        $retrieved_gallery[$post->ID] = $mvc_gallery;
      

        
        //To Exclude images in post meta like tabs
        if( !$include_images_meta ){
            foreach( get_post_meta( $post->ID) as $meta ){
                preg_match_all( '/src="([^"]*)"/i', $meta[0], $images );
                if( !empty( $images[1] ) ){
                    $content_images = array_merge( $content_images, $images[1] );
                }
            }
        }
    
    
        //To exclude any in the content
        if( !$include_content_images ){
            preg_match_all( '/src="([^"]*)/i', $post->post_content, $images );
            if( !empty( $images[1] ) ){
                $content_images = array_merge( $content_images, $images[1] );
            }
        }
    
    
    
        //Remove the images in the content from the $all_images array
        foreach( $all_images as $image ){
    
            //If any of the images considered content images are this image remove it
            if( in_array($image->guid, $content_images ) ||
                    in_array($image->thumb[0], $content_images ) ||
                    in_array($image->medium[0], $content_images ) ||
                    in_array($image->large[0], $content_images )  ){
                //remove it from the global arrray
                unset( $all_images[$image->ID] );
            }
        }
    
   
    
        //to return the images in html form
        if( $html ){
            foreach( $all_images as $image ){
                $html_images .= $wrap_start .'<img src="'.$image->guid.'" title="'.$image->post_title.'" />'.$wrap_end;
            }
            if( !isset( $html_images ) ){
                return false;
            }
            return $html_images;
        } else {
            return $all_images;
        }
        
    }
    
    
    
    /**
     * Retreives all the image from a post
     * @deprecated in favor of self::getPostImages
     * @see self::getPostImages
     * @since 5.9.13
     */
    function get_images( $html = true, $include_featured = false, $size = 'default', $wrap_start = '', $wrap_end = '', $include_content_images = false, $include_meta_images = false ){
       
       $args = array(
                    'html' => $html,
                    'include_featured' => $include_featured,
                    'size' => $size,
                    'wrap_start' => $wrap_start,
                    'wrap_end'   => $wrap_end,
                    'include_content_images' => $include_content_images,
                    'include_meta_images'      => $include_meta_images
                 );
       return $this->getPostImages( $args );
    }
    
    

    /**
     * Returns nothing - Uses to erase outputs from filters
     * @uses call on any filter hook to remove the output completely
     * @return NULL
     */
    function erase(){
      return null;       
    }
    
        /**
     * Outputs a Sidebar for Page or Posts for Whatever
     * Use widgetArea for a standard widget and this for a true sidebar
     * 
     * @param string $name of widget area
     * @param bool $echo defaults to true
     * @since 10.22.13
     *
     * @uses genesis_markup() and mvc_dynamic_sidebar() - if not using genesis will just display sidebar
     */
    function sidebar($name, $echo = true){
        
        global $wp_registered_sidebars;
        
        
         ob_start();
        //we are not rocking genesis
        if( !function_exists('genesis_markup') ){
            mvc_dynamic_sidebar($name);
        } else {
            genesis_markup( array(
                'html5'   => '<aside '. genesis_attr( self::slug_format_human($name) ) .'>',
                'xhtml'   => '<div id="sidebar" class="sidebar widget-area '.self::slug_format_human($name).'">',
                'context' => 'sidebar-primary',
            ) );

                do_action( 'genesis_before_sidebar_widget_area' );
                    mvc_dynamic_sidebar($name);
                do_action( 'genesis_after_sidebar_widget_area' );   
             
            genesis_markup( array(
                'html5' => '</aside>', //* end .sidebar-primary
                'xhtml' => '</div>', //* end #sidebar
            ) );
        }
        
        $output = ob_get_clean();
        
        if( !$echo ) return $output;
        
        echo $output;
        
    }
    
   /**
     * Outputs a Widget Area By Name
     * Use sidebar for a true sidebar and this for a standard widget area 
    * 
     * @param string $name of widget area
     * @param bool $echo defaults to true
     * @since 4.16.13
     */
     function widgetArea($name, $echo = true){
        $output = '<div id="'.self::slug_format_human($name).'" class="widget-area">';
           $output .= mvc_dynamic_sidebar($name, false);
        $output .=  '</div>';
        
     
        if( !$echo ) return $output;
        
        echo $output;
        
    }
    

}

