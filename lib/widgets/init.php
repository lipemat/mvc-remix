<?php 


           /**
            * Adds all the custom widgets
            * @author Mat Lipe
            * @since 2.28.13
            * 
           */

require( 'vimm-profiles-widget/vimm.profiles.widget.php' );
require( 'enews-widget.php' );

add_action( 'widgets_init', 'mat_load_widgets' );


/**
 * Registers all specified Widgets
 * @uses Add widgets here to have them registered
 */
function mat_load_widgets() {
       register_widget('Vimm_Profiles_Widget');
       register_widget('MVC_eNews_Updates');     
}
