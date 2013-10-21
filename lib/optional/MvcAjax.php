<?php

/**
 * Ajax interaction with Mvc Structure
 * 
 * @since 0.1.0
 * 
 * @uses add_theme_support('mvc_ajax');
 * @uses JS MvcAjax.Request(%controller%, %method%, %data%);
 * 
 * @uses add either $ajax_allow or $ajax_nopriv_allow to a class var with an array of allowed methods
 */
if( class_exists('MvcAjax') ) return;  
class MvcAjax extends MvcFramework{
     
     private $no_priv = false;
     
     
    /**
     * @since 10.2.13
     * @uses 
     */
    function __construct(){
        
        //enque the js
        add_action('wp_enqueue_scripts', array( $this, 'addJs') );
        add_action('admin_print_scripts', array( $this, 'addJs') );
        
        //run the ajax request
        add_action('wp_ajax_mvc_ajax' , array( $this, 'handleRequest') );
        add_action('wp_ajax_nopriv_mvc_ajax' , array( $this, 'handleNoPrivRequest') );
    }   
    
    
    /**
     * Calls the correct controller and method and returns a proper json object
     * Check for a nopriv_allow class var before passing to $this->request
     * @since 10.2.13
     * 
     * @uses method name must be in an $ajax_nopriv_allow class var
     * @uses added to the wp_ajax hooks by self::__construct()
     */
    function handleNoPrivRequest(){
        $class = apply_filters( 'mvc_theme_ajax_handle_class', $this->getControllerObject( $_POST['controller'] ), $_POST );
        
        if( !isset( $class->ajax_nopriv_allow ) || !in_array( $_POST['method'], $class->ajax_nopriv_allow ) ){
              echo 'This method has not been added to the ajax_nopriv_allow allowed list';
              exit();
          }
        
        $this->no_priv = true;
        
        
        $this->handleRequest();
        
    }
    
    
    /**
     * Calls the correct controller and method and returns a proper json object
     * 
     * @since 10.2.13
     * 
     * @uses added to the wp_ajax hooks by self::__construct()
     * @uses method name must be in an $ajax__allow class var
     * 
     */
    function handleRequest(){

          check_ajax_referer( 'mvc-ajax' );
          
          $class = apply_filters( 'mvc_theme_ajax_handle_class', $this->getControllerObject( $_POST['controller'] ), $_POST );

          if( !$this->no_priv ){
              if( !isset( $class->ajax_allow ) || !in_array( $_POST['method'], $class->ajax_allow ) ){
                 echo 'This method has not been added to the ajax_allow allowed list';
                exit();
              } 
          }

          $data = $class->{$_POST['method']}($_POST['args']);
          
          if( !is_string( $data ) ){
              echo json_encode( $data );
          } else {
              echo $data;
          }
        exit();   
    }
    
    
    
    /**
     * Ques up the js required for the ajax interaction
     * 
     * @since 10.2.13
     */
    function addJs(){
        wp_enqueue_script(
            'mvc-ajax',
            MVC_ASSETS_URL.'js/mvc-ajax.js',
            array('jquery')
        );
        
        //Add any data needed in a global js object to this array
        $data = array ( 'URL' => esc_url( wp_nonce_url ( admin_url('admin-ajax.php?action=mvc_ajax' ), 'mvc-ajax' )) );
        
        wp_localize_script ( 'mvc-ajax' , 'MVCAjaxData' , $data ) ;
        
    }
    
}
