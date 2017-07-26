<?php

/**
 * Functions to be used in templates etc
 * 
 * @author Mat Lipe
 * 
 */

/**
 * mvc
 *
 * Get an instance of the main class
 *
 * @return \MVC\Core\Framework
 */
function mvc(){
	static $mvc = null;
	if( !empty( $mvc ) ){
		return $mvc;
	}

	return $mvc = new \MVC\Core\Framework();
}

/**
 * Mvc Url
 *
 * Quick function for interacting with MVC\Util\Url
 *
 * @example mvc_url()->get_current_url();
 *
 *
 * @return \MVC\Util\Url
 */
function mvc_url(){
	return \MVC\Util\Url::get_instance();
}

/**
 * mvc_image
 *
 * Retrieve a url to a resized version of
 * a specified url
 *
 * @param string       $url  Url of the full sized image.
 * @param string|array $size Any registered image size or an array( %width%, %height% )
 * @param bool         $crop Set to false to soft crop. Default true ( hard crop )
 *
 * @return string
 */
function mvc_image( $url, $size, $crop = true ){
	$image = \MVC\Util\Image_Resize::get_instance();

	$args = array(
		'size'   => $size,
		'src'    => $url,
		'crop'   => $crop,
		'output' => 'url'
	);

	return $image->image( $args, false );
}


/**
 * Mvc Versions
 * 
 * Quick function for interacting with MVC\Versions
 * 
 * @example mvc_versions()->add_update( 2.0, 'update_data' );
 * 
 * @return \MVC\Util\Versions
 * 
 */
function mvc_versions(){
	return MVC\Util\Versions::get_instance();
}

/**
 * @see \MVC\Meta_Box::register()
 * @deprecated  in favor of calling \MVC\Meta_Box::register() directly
 */
function mvc_meta_box( $post_type = null, $meta_box_class, $args = [] ) {
	return call_user_func( [ $meta_box_class, 'register' ], $post_type, $args );
} 
 

/**
 * Mvc Internal
 * 
 * quick function for interacting with MvcInternalTax
 * 
 * @example mvc_internal()->has_term( 'active' );
 * 
 * @return \MVC\Util\Internal_Tax
 */
function mvc_internal(){
	return \MVC\Util\Internal_Tax::get_instance();
}


/**
 * Mvc String
 * 
 * quick function for interacting with MvcString
 * 
 * @example  mvc_string()->theContentLimit();
 * 
 * @return \MVC\Util\String_Utils
 */
function mvc_string(){
	return \MVC\Util\String_Utils::get_instance();
}

/**
 * Mvc Styles
 * 
 * quick function for interacting with MvcStyles
 * 
 * @example  mvc_styles()->localize( %var%, %data% );
 * 
 * @return \MVC\Util\Styles
 */
function mvc_styles(){
	if( !current_theme_supports('mvc_styles') ){
		throw new \Exception( 'To use mvc_styles, your theme must declare mvc_styles support!' );
	}
	return \MVC\Util\Styles::get_instance();
}


/**
 * Mvc Util
 * 
 * quick function for interacting with MvcUtilities
 * 
 * @example mvc_util()->arrayFilterRecursive();
 * 
 * @return \MVC\Util\Utility
 */
function mvc_util(){
	return \MVC\Util\Utility::get_instance();
}

                    

/**
 * Mvc Template
 * 
 * quick function for interacting with Template
 * 
 * @example mvc_template()->change_sidebar( 'active' );
 * 
 * @return \MVC\Util\Template
 */
function mvc_template(){
	return \MVC\Util\Template::get_instance();
}


/**
 * mvc_file
 *
 * @example mvc_file()->locate_template( %file% )
 *
 * @return \MVC\Util\File
 */
function mvc_file(){
	return \MVC\Util\File::get_instance();
}

/**
 * Mvc Form
 * 
 * quick function for interacting with MvcForm
 * 
 * @example mvc_form()->text( 'active' );
 * 
 * @return \MVC\Util\Form
 */
function mvc_form(){
	return \MVC\Util\Form::get_instance();
}

/**
 * Mvc Form
 *
 * quick function for interacting with MvcApi
 *
 * @example mvc_api()->get_api_url();
 *
 * @return \MVC\Core\Api
 */
function mvc_api(){
    return \MVC\Core\Api::get_instance();
}

/**
 * Test for a submitted nonce
 * Assumes your nonce field's name is the same as the
 * nonce's name.
 *
 * Works off of the $_POST[ $nonce_name ]
 *
 * @param string $nonce_name
 *
 * @return bool
 */
function mvc_post_nonce( $nonce_name ){
    if( !empty( $_POST[ $nonce_name ] ) && wp_verify_nonce( $_POST[ $nonce_name ], $nonce_name ) ){
        return true;
    } else {
        return false;
    }
}

/**
 * Registers a sidebar with all the proper args for name usage later on
 * * Takes care of all the common args but allows for overriding
 * 
 * @since 5.17.0
 * 
 * @param string $name - name of sidebar
 * @param string [$description] - description for sidebar - defaults ot 'Sidebar $name'
 * @param array [$args] - args to override defaults if desired
 * 
 * @uses Tyler will probabaly ask "Why don't we use the default one?", well the answer is the defualt one requires you to fill out the id param and if you do not, you can use function like is_active_sidebar() with a name because you get a numeric id generated. Sure you can add that param and everything will work but its extra work. 
 */
function mvc_register_sidebar($name, $description = false, $args = array()){
    
    if( !$description ){
        $description = 'Sidebar - '.$name;
    }
    
    $defaults = (array) apply_filters(
        'mvc_register_sidebar_defaults',
        array(
            'id'            => sanitize_title($name),
            'name'          => $name,
            'description'   => $description
        )
    );
    
    $args = wp_parse_args( $args, $defaults );

    return genesis_register_sidebar( $args );  
}                        
                         
                         
                         
/**
 * Same as default dynamic Sidebar with some added filters and options
 * 
 * @param int|string $index Optional, default is 1. Name or ID of dynamic sidebar.
 * @param bool $echo defaults to true
 * @param bool $wrap - add a matching div wrap (defaults to false);
 * 
 * @filters apply_filters('mvc_dynamic_sidebar_output', ob_get_clean(), $callback, $params, $id );
 * 
 * @return Mixed|bool True, if widget sidebar was found and called. False if not found or not called.
 * @since 6.21.13
 */
function mvc_dynamic_sidebar($index = 1, $echo = true, $wrap = false) {
    global $wp_registered_sidebars, $wp_registered_widgets;

    if ( is_int($index) ) {
        $index = "sidebar-$index";
    } else {
        $index = sanitize_title($index);
        foreach ( (array) $wp_registered_sidebars as $key => $value ) {
            if ( sanitize_title($value['name']) == $index ) {
                $index = $key;
                break;
            }
        }
    }

    $sidebars_widgets = wp_get_sidebars_widgets();
    if ( empty( $sidebars_widgets ) )
        return false;

    if ( empty($wp_registered_sidebars[$index]) || !array_key_exists($index, $sidebars_widgets) || !is_array($sidebars_widgets[$index]) || empty($sidebars_widgets[$index]) )
        return false;

    $sidebar = $wp_registered_sidebars[$index];

    $output = false;

    foreach ( (array) $sidebars_widgets[$index] as $id ) {

        if ( !isset($wp_registered_widgets[$id]) ) continue;
        
    
        $params = array_merge(
            array( array_merge( $sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']) ) ),
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
        $params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);

        $params = apply_filters( 'dynamic_sidebar_params', $params, $id );
     
        $callback = $wp_registered_widgets[$id]['callback'];

        do_action( 'dynamic_sidebar', $wp_registered_widgets[$id] );


        if ( is_callable($callback) ) {
            ob_start();
            call_user_func_array($callback, $params);
            $output .= apply_filters('mvc_dynamic_sidebar_output', ob_get_clean(), $callback, $params, $id ); 
        }
    }


    //Adds a wrap around the sidebar - since 5.2.0
    if( $wrap ){
        $top = sprintf('<div id="%s" class="widget-area">',$index ); 
        $bottom = sprintf('</div><!-- End #%s -->', $index );
        
        $output = $top.$output.$bottom;   
    }


    if( $echo ){
        echo $output;
    } 
    return $output;
}


/**
*  mvc_esc_attr
*
*  This function will return a render of an array of attributes to be used in markup
*
*  @since	9.9.16
*
*  @param	array $atts
*  @return	string
*/
function mvc_esc_attr( $atts ) {
	if( is_string($atts) ) {
		$atts = trim( $atts );
		return esc_attr( $atts );
	}

	if( empty($atts) ) {
		return '';
	}

	$e = array();
	foreach( $atts as $k => $v ) {
		if( is_array($v) || is_object($v) ) {
			$v = json_encode($v);
		} elseif( is_bool($v) ) {
			$v = $v ? 1 : 0;
		} elseif( is_string($v) ) {
			$v = trim($v);
		}
		$e[] = $k . '="' . esc_attr( $v ) . '"';
	}

	return implode(' ', $e);
}


function mvc_esc_attr_e( $atts ) {
	echo mvc_esc_attr( $atts );
}