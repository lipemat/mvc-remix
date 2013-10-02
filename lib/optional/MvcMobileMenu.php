<?php
/**
 * Collapsible Mobile Menu Generator
 * 
 * @uses add_theme_support('mobile_menu');
 * 
 * @since 5.7.0
 * @author Mat Lipe
 * 
 */
class MvcMobileMenu extends MvcFramework{
     
     //Light or dark theme was specified
     public $theme_color;
        
     /**
      * @since 8.6.13
      */
     function __construct(){
        $this->checkForLightOrDark();
        add_action('wp_enqueue_scripts', array( $this, 'addJsCss') ); 
        add_filter('current_theme_supports-mobile_menu', array( $this, 'checkForLightOrDark' ),99,3 );
        add_action('genesis_after_header', array( $this, 'menuButton'),1 );
  
     }
     
     
     /**
      * Sets the theme color to whatever was send as the second argument
      * 
      * @uses this is changed by add_theme_support('mobile_menu', %color%)
      * @uses called directly by self::__construct()
      * 
      * @defaults to dark
      * 
      * @since 8.6.13
      * 
      */
     function checkForLightOrDark(){
        global $_wp_theme_features;
        
        $features = $_wp_theme_features['mobile_menu']; 
        if( isset( $features[0] ) ){
            $this->theme_color = $features[0];
        } else {
            $this->theme_color = 'dark';   
        }
  
     }
     
     
     
     /**
      * Output a Menu Button before the Nav
      * 
      * @since 8.6.13
      * @uses added to the genesis_after_header hook by self::__constrcut()
      */
     function menuButton(){
        ?>
        <div id="nav-button">
            <img src="<?php echo IMAGE_DIR; ?>menu-button.png" />
        </div>
       <?php
           
     }
          
     
     /**
      * Adds the js and css for the collapsible mobile menu
      * 
      * @since 8.6.13
      * @uses will enque the css file matching the color name specified during add_theme_support('mobile_menu', %color% )
      * 
      * @uses added to the wp_enqueue_scripts hook by self::construct()
      */
     function addJsCss(){
         
        wp_enqueue_script(
                'jquery-sidr',
                 THEME_DIR.'lib/js/mobile_menu.js',
                 array('jquery'),
                 null,
                 true
        );

        $css = THEME_DIR.'lib/css/mobile_menu_'.$this->theme_color.'.css';

        wp_enqueue_style(
                'mvc-mobile-menu-css',
                 $css
        );
     }
       
        
}
    