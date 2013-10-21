<?php

                /**
                 * Adds some security to the Wordpress Login
                 * @since 4.15.13
                 * @since version 4.1.0
                 * @author Mat Lipe
                 * 
                 * @uses prevents brute force attacks on sites not on one of my crazy secure servers.
                 */
                 
if( class_exists('MvcLogin') ) return;                 
class MvcLogin{
       
      private $ip; //the ip of the user trying to login
      private $locked_out = false;
     
     /**
      * Setup up all processes here
      * 
      * @since 4.15.13
      */  
     function __construct(){
         $this->ip = $_SERVER['REMOTE_ADDR'];
         //tap into the functionality of the built in login
         add_action('wp_login_failed', array( $this, 'failedLogin') );
         add_filter('wp_authenticate_user', array( $this, 'authenticateUser'), 99999, 2 );
         add_filter( 'login_errors', array( $this, 'errorMessage') );
         
         //Schedule the cleanup event
         add_action('mvc-failed-logins-cleanup', array($this,'clearFailedLogins') );
         if ( !wp_next_scheduled( 'mvc-failed-logins-cleanup' ) ) {
                wp_schedule_event( time(), 'daily', 'mvc-failed-logins-cleanup');
         }
     }
     

     /**
      * Changes the Error Msg Once Someone has been locked out
      * 
      * @since 4.15.13
      * @uses filter added to 'login_errors'
      */
     function errorMessage($msg){
         if( !self::isLockedOut() ) return $msg;
         return '<h2>Error: You have been locked out for too many failed login attempts!</h2>';
     }
     
     
     
     
     /**
      * Runs each time a login Fails
      * 
      * @uses addes 1 per tries in the option
      * @uses if the ip has not already failed it will add a time to use as well
      * @uses filter 'wp_login_failed'
      * @since 4.15.13
      */
     function failedLogin($mess){
          $failed = self::getFailedLogins();
          if( isset( $failed[$this->ip] ) ){
              $failed[$this->ip]['tries']++;
              $failed[$this->ip]['time'] = time()+15*60; // 15 minutes from now
          } else {
              $failed[$this->ip]['tries'] = 1;
              $failed[$this->ip]['time'] = time()+15*60; // 15 minutes from now
          }         
         self::updateFailedLogins($failed);
        
     }
     
     
     /**
      * Start the Whole Thing Over
      * 
      * @since 4.15.13
      * @uses current on a scheduled event to run daily
      */
     function clearFailedLogins(){
         self::updateFailedLogins( array() );
     }
     
     
     /**
      * Wrapper to retrieve the option which contains the failed logins.
      * 
      * @return array();
      */
     function getFailedLogins(){
         return get_option('mvc-failed-logins', array() );
     }
     
     
     /**
      * Wrapper to update the failed logins
      * 
      * @param array() $logins
      */
     function updateFailedLogins(array $logins){
         update_option('mvc-failed-logins', $logins);
     }
     
     
     /**
      * Checks to make sure a user is not over the limit in tries or if it has been long enough since they were locked out
      * 
      * @uses will reset their tries if they have not exceeded
      * @return bool
      * @since 4.15.13
      */
     function isLockedOut(){
          $failed = self::getFailedLogins();
          if( isset( $failed[$this->ip] ) ){
              if( $failed[$this->ip]['tries'] > 5 && time() < $failed[$this->ip]['time'] ){
                  return true;
              } else {
                  return false;
              }
          } else {
              return false;
          }
         
     }
     
     
     /**
      * Actions run during authentication
      * 
      * @uses returns false if they are locked out otherwise return what they had already
      * @uses filter 'wp_authenticate_user'
      * @since 4.15.13
      */
     function authenticateUser($user){
         if( self::isLockedOut() ){
            $this->locked_out = true;   
            return false;
         } else {
            $failed = self::getFailedLogins();
            unset( $failed[$this->ip] );
            self::updateFailedLogins($failed);  
            return $user; 
             
         }
         
         
         
     }
     
     
     
}  


new MvcLogin;