<?php

namespace MVC;

/**
 * Script
 *
 * @author  Mat Lipe
 * @since   11/4/2015
 *
 * @package MVC
 */
class Script {

	/**
	 * file
	 *
	 * The name of the file.
	 * %name%.js works as well as %name%
	 *
	 * @var string
	 */
	private $file;

	/**
	 * handle
	 *
	 * Script handle.
	 * Will generate one if not specified
	 *
	 * @var string
	 */
	public $handle = false;

	/**
	 * folder
	 *
	 * Optional location of the js file. If not specified
	 * this will locate the file in either a "js" dir or
	 * a "resources/js" dir of any of the mvc locations.
	 *
	 * @uses mvc_file()->locate_template()
	 *
	 * @var string
	 */
	public $folder = false;

	/**
	 * dependencies
	 *
	 * Scripts this depends on.
	 * Defaults to array( 'jquery' )
	 *
	 * @var array
	 */
	public $dependencies = array( 'jquery' );

	/**
	 * version
	 *
	 * The scripts version.
	 * Defaults to mvc_util()->get_beanstalk_based_version()
	 *
	 * @var bool
	 */
	public $version = false;

	/**
	 * in_footer
	 *
	 * Output the file in the footer.
	 * Default to false for in header.
	 *
	 * @var bool
	 */
	public $in_footer = false;

	/**
	 * include_in_admin
	 *
	 * Add this file to the wp-admin.
	 * Defaults to false
	 *
	 * @var bool
	 */
	public $include_in_admin = false;

	/**
	 * include_in_frontend
	 *
	 * Add this file to the front end of the site
	 * Defaults to true
	 *
	 * @var bool
	 */
	public $include_in_frontend = true;

	/**
	 * data
	 *
	 * Data that will be localized for this
	 * script
	 *
	 * @see $this->set_data()
	 *
	 * @var array
	 */
	private $data = false;

	/**
	 * data_object_name
	 *
	 * The name used for the localized data object
	 *
	 * @see $this->set_data()
	 *
	 * @var string
	 */
	private $data_object_name = false;


	public function __construct( $file ){
		$this->file = $file;

		$this->hooks();
	}


	private function hooks(){
		add_action( 'wp_loaded', array( $this, 'register_script' ) );
	}


	/**
	 * Register the proper hook to cue the script
	 * Based on is_admin() and the class parameters
	 *
	 * @uses $this->include_in_admin
	 * @uses $this->include_in_frontend
	 * @uses $this->enque_script()
	 *
	 * @return void
	 */
	public function register_script(){
		if( is_admin() ){
			if( $this->include_in_admin ){
				add_action( 'admin_enqueue_scripts', array( $this, 'enque_script' ) );
			}
		} else {
			if( $this->include_in_frontend ){
				add_action( 'wp_enqueue_scripts', array( $this, 'enque_script' ) );
			}
		}
	}


	/**
	 * The actual cueing of the script.
	 * Added to the appropriate action by $this->register_script()
	 *
	 * @uses $this->register_script()
	 *
	 * @return void
	 */
	public function enque_script(){
		$file   = $this->locate_js_file( $this->file );
		$handle = $this->get_handle();
		wp_enqueue_script( $handle, $file, array( 'jquery' ), $this->get_version(), $this->in_footer );

		if( !empty( $this->data ) ){
			wp_localize_script( $handle, $this->data_object_name, $this->data );
		}
	}


	/**
	 * Set data that will be used to localize the script
	 *
	 * @param string $object_name - name of JS variable that will be created
	 * @param array  $data        - data that will be assigned
	 *
	 * @return void
	 */
	public function set_data( $object_name, $data ){
		$this->data_object_name = $object_name;
		$this->data             = $data;
	}


	/**
	 * Get the scripts version.
	 * If not specified will pull the git hash from
	 * the beanstalk .version file
	 *
	 * @uses $this->version
	 *
	 * @return bool|string
	 */
	private function get_version(){
		$version = $this->version;
		if( empty( $version ) ){
			$version = mvc_util()->get_beanstalk_based_version();
		}

		return $version;
	}


	/**
	 * Retrieve the scripts handle.
	 * If not specified this will generate one based on the md5
	 * of this class.
	 *
	 * @uses $this->handle
	 *
	 * @return string
	 */
	private function get_handle(){
		$handle = $this->handle;
		if( empty( $handle ) ){
			$handle = 'mvc-script-' . md5( $this );
		}

		return $handle;
	}


	/**
	 * locate_js_file
	 *
	 * Locates the proper js file based on SCRIPT_DEBUG
	 * And theme structure
	 *
	 * Will look in a /js folder first then /resources/js
	 *
	 * if !SCRIPT_DEBUG will look for a $file_name.min.js file
	 * first then fallback to non min file. It will also look
	 * within min folders.
	 *
	 * @param $file_name
	 *
	 * @return bool|string
	 */
	private function locate_js_file( $file_name ){
		$file_name = str_replace( '.js', '', $file_name );

		if( !defined( 'SCRIPT_DEBUG' ) || !SCRIPT_DEBUG ){
			if( !$file = mvc_file()->locate_template( "js/$file_name.min.js", true ) ){
				if( !$file = mvc_file()->locate_template( "js/min/$file_name.min.js", true ) ){
					if( !$file = mvc_file()->locate_template( "resources/js/$file_name.min.js", true ) ){
						$file = mvc_file()->locate_template( "resources/js/min/$file_name.min.js", true );
					}
				}
			}
		}

		if( empty( $file ) ){
			if( !$file = mvc_file()->locate_template( "js/$file_name.js", true ) ){
				$file = mvc_file()->locate_template( "resources/js/$file_name.js", true );
			}
		}

		return $file;

	}
}