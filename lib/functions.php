<?php

                        /**
                         * Misc Functions for MVC
                         * @author Mat Lipe
                         * @since 10.19.13
                         */

                 
/**        
 * Loads classes on the fly per needs only
 * 
 * @uses added ot the spl_autoload_register() function by bootstrap.php
 * @uses will load a class from the main lib folder or the helpers folder
 * 
 * @since 10.2.13
 * 
 */
function _mvc_autoload($class){

    if( file_exists(MVC_DIR.'lib/'.$class.'.php') ){
        require( MVC_DIR.'lib/'.$class.'.php');
    } elseif( file_exists(MVC_DIR.'lib/helpers/'.$class.'.php') ){
        require( MVC_DIR.'lib/helpers/'.$class.'.php');
    } elseif( file_exists(MVC_DIR.'lib/optional/'.$class.'.php') ){
        require( MVC_DIR.'lib/optional/'.$class.'.php');
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
     * Outputs data about a variable|object|array
     * 
     * @param mixed $data - the data to display
     * @param bool  $hide - wraps the output in a display none
     * @param bool  $adminOnly - only displays in the admin
     */
if( !function_exists('_p') ){
   function _p($data, $hide = false, $adminOnly = false){
        
        if( $adminOnly && !MVC_IS_ADMIN ) return;
        
        if( $hide ){
            echo '<div style="display:none">';
        }
            echo '<pre>';
                print_r( $data );
            echo '</pre>';
        
            echo '<pre>';
              $debug = debug_backtrace(false);
              $args = array_shift($debug);
              unset( $args['args'] );    
              print_r( $args );
            echo '</pre>';
        if( $hide ){
            echo '</div>';
        }
}
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

function admin_bar_responsive_check() {
	global $wp_admin_bar;
	
	if( !is_user_logged_in() || !is_super_admin() || !MVC_IS_ADMIN_bar_showing() ) {
		return;
	}
	
	if( current_theme_supports( 'mobile_responsive' ) ) {
		$wp_admin_bar->add_menu( array( 
				'id'			=>	'responsive_check',
				'title'			=>	__( 'Responsive' ),
				'href'			=> ''
		) );
	}
}
    
   
    