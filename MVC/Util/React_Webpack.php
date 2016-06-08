<?php

namespace MVC\Util;

/**
 * React_Webpack
 *
 * @author  Mat Lipe
 * @since   6/8/2016
 *
 * @package MVC\Util
 *
 * @example (new \MVC\Util\React_Webpack( get_stylesheet_directory_uri() . '/resources/react') )->init();
 *
 * @internal
 */
class React_Webpack {
	private $directory;

	private $handle;


	public function __construct( $directory, $handle = 'mvc-react-webpack' ){
		$this->directory = $directory;
		$this->handle    = $handle;
	}


	public function init(){
		$this->hooks();
	}


	private function hooks(){
		if( !did_action( 'wp_enqueue_scripts' ) ){
			add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		} else {
			$this->register();
		}
	}


	public function register(){
		if( !defined( 'SCRIPT_DEBUG' ) || SCRIPT_DEBUG === false ){
			$js_file  = trailingslashit( $this->directory ) . 'dist/master.js';
			$css_file = trailingslashit( $this->directory ) . 'dist/master.css';
		} else {
			$js_file = 'http://localhost:3000/dist/master.js';
		}
		wp_enqueue_script( $this->handle, $js_file, array(), mvc_util()->get_beanstalk_based_version(), true );

		if( !empty( $css_file ) ){
			wp_enqueue_style( $this->handle . '-css', $css_file, array(), mvc_util()->get_beanstalk_based_version() );
		}

	}
}