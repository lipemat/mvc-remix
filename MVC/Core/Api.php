<?php

namespace MVC\Core;


/**
 * Api
 *
 * Simple api endpoint
 *
 * @example add_action( 'mvc_api_%endpoint%', %function )
 *          Then go to %site_root%/api/%endpoint% to use the action
 *
 * @author  Mat Lipe
 * @since   1/26/2015
 *
 * @package MVC
 */
class Api {

	use \MVC\Traits\Singleton;

	const DB_VERSION = 1;
	const DB_KEY = 'mvc-api-version';


	protected function hooks(){
		add_action( 'init', array( $this, 'add_endpoint' ), 10, 0 );
		add_action( 'parse_request', array( $this, 'handle_request' ), 10, 1 );
	}


	public function add_endpoint(){
		add_rewrite_endpoint( 'api', EP_ROOT );

		if( version_compare( get_option( self::DB_KEY, '0.0.1' ), self::DB_VERSION ) == - 1 ){
			flush_rewrite_rules();
			update_option( self::DB_KEY, self::DB_VERSION );
		}
	}


	/**
	 * @param \WP_Query $wp
	 *
	 * @return void
	 */
	public function handle_request( $wp ){
		if( empty( $wp->query_vars[ 'api' ] ) ){
			return;
		}
		$args     = explode( '/', $wp->query_vars[ 'api' ] );
		$endpoint = array_shift( $args );
		do_action( 'mvc_api_' . $endpoint, $args );
	}


}