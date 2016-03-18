<?php

namespace MVC;

/**
 * Style
 *
 * @author  Mat Lipe
 * @since   1/19/2016
 *
 * @package MVC
 */
class Style {

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
	 * Style handle.
	 * Will generate one if not specified
	 *
	 * @var string
	 */
	public $handle = false;

	/**
	 * dependencies
	 *
	 * Styles this depends on.
	 * Defaults to array()
	 *
	 * @var array
	 */
	public $dependencies = array();

	/**
	 * version
	 *
	 * The styles version.
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
	 * folder
	 *
	 * A specific folder to find the file in.
	 * Use only for non standard folder structures
	 * or conflicting file names
	 *
	 * @notice Should be set to the theme or plugin folder not css folder
	 *
	 * @var
	 */
	public $folder;

	/**
	 * located_file
	 *
	 * Used internally to keep the handles unique
	 *
	 * @var bool
	 */
	private $located_file = false;

	public function __construct( $file ){
		$this->file = $file;

		$this->hooks();
	}


	private function hooks(){
		add_action( 'admin_enqueue_scripts', array( $this, 'register_style' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_style' ), 99 );
	}


	/**
	 * Register the proper hook to cue the style
	 * Based on is_admin() and the class parameters
	 *
	 * @uses $this->include_in_admin
	 * @uses $this->include_in_frontend
	 * @uses $this->enque_style()
	 *
	 * @return void
	 */
	public function register_style(){
		if( is_admin() ){
			if( $this->include_in_admin ){
				$this->cue_style();
			}
		} else {
			if( $this->include_in_frontend ){
				$this->cue_style();
			}
		}
	}


	/**
	 * The actual cueing of the style.
	 * Added to the appropriate action by $this->register_style()
	 *
	 * @uses $this->register_style()
	 *
	 * @return void
	 */
	private function cue_style(){
		$file   = $this->locate_css_file( $this->file );
		if( $file ){
			$handle = $this->get_handle();
			wp_enqueue_style( $handle, $file, $this->dependencies, $this->get_version(), $this->in_footer );
		}

	}


	/**
	 * Set data that will be used to localize the style
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
	 * Get the styles version.
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
	 * Retrieve the styles handle.
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
			$handle = 'mvc-style-' . md5( json_encode( $this ) );
		}

		return $handle;
	}


	/**
	 * locate_css_file
	 *
	 * Locates the proper css file based on SCRIPT_DEBUG
	 * Searches:
	 * /
	 * /css
	 * /resources/css
	 *
	 * If !SCRIPT_DEBUG will look for .min.css file
	 *
	 * @param $file_name
	 *
	 * @return bool|string
	 */
	private function locate_css_file( $file_name ){
		$file_name = str_replace( '.css', '', $file_name );

		if( !defined( 'SCRIPT_DEBUG' ) || !SCRIPT_DEBUG ){
			if( !$file = mvc_file()->locate_template( "$file_name.min.css", true, false, $this->folder ) ){
				if( !$file = mvc_file()->locate_template( "css/$file_name.min.css", true, false, $this->folder ) ){
					$file = mvc_file()->locate_template( "resources/css/$file_name.min.css", true, false, $this->folder );
				}
			}

		}

		if( empty( $file ) ){
			if( !$file = mvc_file()->locate_template( "$file_name.css", true, false, $this->folder ) ){
				if( !$file = mvc_file()->locate_template( "css/$file_name.css", true, false, $this->folder ) ){
					$file = mvc_file()->locate_template( "resources/css/$file_name.css", true, false, $this->folder );
				}
			}
		}

		return $this->located_file = $file;
	}
}