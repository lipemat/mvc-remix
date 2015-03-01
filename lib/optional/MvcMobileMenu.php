<?php
/**
 * Mvc Mobile Menu
 *
 * Collapsible Mobile Menu Generator
 *
 * @uses   add_theme_support( 'mobile_menu' );
 *
 * @author Mat Lipe <mat@matlipe.com>
 *
 */
if( class_exists( 'MvcMobileMenu' ) ){
	return;
}


class MvcMobileMenu extends MvcFramework {

	/**
	 * display_on_all_devices
	 *
	 * Override to have it always display and use your own
	 * media queries to hide. Comes in handy when serving
	 * page cache to all devices
	 *
	 * @static
	 * @var bool
	 */
	public static $display_on_all_devices = false;

	//Light or dark theme was specified
	public $theme_color;

	//default menu and button ids
	private $defaults = array(
		'menu'        => '#nav',
		'menu_button' => '#nav-button',
		'html5'       => false

	);


	/**
	 * Constructor
	 *
	 */
	function __construct(){
		if( !self::$display_on_all_devices && !$this->is_mobile() ){
			return;
		}

		$this->checkForLightOrDark();
		$this->hooks();
	}


	/**
	 * Hook me up
	 *
	 */
	private function hooks(){

		//switch defaults if html5 support
		add_action( 'after_setup_theme', array( $this, 'adjust_defaults' ), 999 );

		//main js and css
		add_action( 'wp_enqueue_scripts', array( $this, 'addJsCss' ) );

		//filterable styles
		add_action( 'wp_head', array( $this, 'menu_css' ) );

		//check which theme is being used
		add_filter( 'current_theme_supports-mobile_menu', array( $this, 'checkForLightOrDark' ), 99, 3 );

		//output
		add_action( 'genesis_after_header', array( $this, 'menuButton' ), 1 );

	}


	/**
	 * Adjust Defaults
	 *
	 * @sets   the defaults based on html5 support and filters
	 * @filter mvc-sidr
	 *
	 */
	public function adjust_defaults(){
		if( current_theme_supports( 'html5' ) ){
			$this->defaults[ 'menu' ]  = '.nav-primary';
			$this->defaults[ 'html5' ] = true;
		}

		$this->defaults = apply_filters( 'mvc-sidr', $this->defaults );

	}


	/**
	 * Menu Css
	 *
	 * Css which may be filtered therefore gets outputted manually in the head
	 *
	 * @filter mvc-menu-css
	 *
	 */
	public function menu_css(){

		//roll your own styles
		if( apply_filters( 'mvc-menu-css', false, $this->defaults ) ){
			return;
		}


		?>
		<style type="text/css" marker="xxxx">
			@media only screen and (max-width : 600px) {
				#wrap <?php echo $this->defaults[ 'menu_button' ]; ?> {
					display : block;
				}

				#wrap <?php echo $this->defaults[ 'menu' ]; ?> {
					display : none;
				}
			}
		</style>
	<?php


	}


	/**
	 * Sets the theme color to whatever was send as the second argument
	 *
	 * @uses     this is changed by add_theme_support('mobile_menu', %color%)
	 * @uses     called directly by self::__construct()
	 *
	 * @defaults to dark
	 *
	 * @since    8.6.13
	 *
	 */
	function checkForLightOrDark(){
		global $_wp_theme_features;

		if( isset( $_wp_theme_features[ 'mobile_menu' ][ 0 ] ) ){
			$this->theme_color = $_wp_theme_features[ 'mobile_menu' ][ 0 ];
		} else {
			$this->theme_color = 'dark';
		}

	}


	/**
	 * Menu Button
	 *
	 * Output a Menu Button before the Nav
	 *
	 * @uses   added to the genesis_after_header hook by self::__constrcut()
	 *
	 * @filter mvc-menu-button
	 */
	function menuButton(){
		?>
		<div id="nav-button">
			<?php
			echo apply_filters( 'mvc-menu-button', '<img src="' . MVC_IMAGE_URL . 'menu-button.png" />' );
			?>
		</div>
	<?php

	}


	/**
	 * Add Js Css
	 *
	 * Adds the js and css for the collapsible mobile menu
	 *
	 * @uses   will enque the css file matching the color name specified during add_theme_support('mobile_menu', %color% )
	 *
	 * @uses   added to the wp_enqueue_scripts hook by self::construct()
	 *
	 * @filter mvc-sidr
	 */
	function addJsCss(){

		wp_enqueue_script(
			'jquery-sidr',
			MVC_ASSETS_URL . 'js/mobile_menu.js',
			array( 'jquery' ),
			null,
			true
		);

		wp_localize_script( 'jquery-sidr', 'Sidr', apply_filters( 'mvc-sidr', $this->defaults ) );

		$css = MVC_ASSETS_URL . 'css/mobile_menu_' . $this->theme_color . '.css';

		wp_enqueue_style(
			'mvc-mobile-menu-css',
			$css
		);
	}


}
    