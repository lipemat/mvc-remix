<?php 

              /**
               * Methods for the GVL POLY Theme
               * @uses Called at functions.php
               * @since 10.25.12
               * @author Mat Lipe
               */
              
class gvl{
    
    /**
     * Runs the 4 methods depending on device
     * @since 10.25.12
     */
    function __construct(){
        global $mat_framework_func;
        
        if( $mat_framework_func->is_tablet() ){
            self::tabletChanges();
        } elseif ( $mat_framework_func->is_phone() ) {
            self::phoneChanges();
        } else {
            self::desktopChanges();
        }

    }
    
    
    /**
     * Makes adjustments to the output on the tablet
     * @since 10.25.12
     * @uses called at __construct
     */
    function tabletChanges(){
        
    }
    
    /**
     * Makes adjustments to the output on the phone
     * @since 10.25.12
     * @uses called at __construct
     */
    function phoneChanges(){
        
        
    }
    
    /**
     * Makes adjustments to the output on the desktop
     * @since 10.25.12
     * @uses called at __construct
     */
    function desktopChanges(){
       
    }
    
    
    
    
    
    
    
}