<?php

/**
 * Functions to be used in templates etc
 * 
 * @author Mat Lipe
 * 
 * @since 5.13.14
 */


/**
 * Mvc Meta Box
 * 
 * @param string $post_type
 * @param string $meta_box_class
 * @param array $args
 * 
 * @return MVC\Meta_Box
 */
function mvc_meta_box( $post_type, $meta_box_class, $args = array() ) {
	if ( !class_exists( $meta_box_class ) ) {
		return FALSE;
	}
	return new $meta_box_class( $post_type, $args );
} 
 

/**
 * Mvc Internal
 * 
 * quick function for interacting with MvcInternalTax
 * 
 * @example mvc_internal()->has_term( 'active' );
 * 
 * @return MvcInternalTax
 */
function mvc_internal(){
	return MvcInternalTax::get_instance(); 
}


/**
 * Mvc String
 * 
 * quick function for interacting with MvcString
 * 
 * @example  mvc_string()->theContentLimit();
 * 
 * @return MvcString
 */
function mvc_string(){
	return MvcString::get_instance(); 
}




/**
 * Mvc Util
 * 
 * quick function for interacting with MvcUtilities
 * 
 * @example mvc_util()->arrayFilterRecursive();
 * 
 * @return MvcUtilities
 */
function mvc_util(){
	return MvcUtilites::get_instance(); 
}

                    

/**
 * Mvc Format
 * 
 * quick function for interacting with MvcFormat
 * 
 * @example mvc_format()->change_sidebar( 'active' );
 * 
 * @return MvcFormat
 */
function mvc_format(){
	return MvcFormat::get_instance(); 
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