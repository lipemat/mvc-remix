<?php

namespace MVC\Rest_Api;

use MVC\Db;
use MVC\Traits\Singleton;

/**
 * Auth_Table
 *
 * @see Login
 *
 * @author  Mat Lipe
 * @since   3.10.17
 *
 * @package WSWD\Rest_Api
 */
class AuthTable extends Db {
	use Singleton;

	protected $db_option = "auth_db";

	protected $db_version = 1;

	protected $id_field = 'id';

	protected $columns = [
		'id'      => '%d',
		'user_id' => '%d',
		'token'   => '%s',
		'expires' => '%s',
	];


	private function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix . 'auth';

		if( $this->update_required() ){
			$this->run_updates();
		}
	}


	/**
	 * Runs when a new one is added
	 * to clean out expired tokens
	 *
	 * @see $this->add_token();
	 *
	 * @return false|int
	 */
	public function clean_expired_tokens(){
		global $wpdb;
		$expires = gmdate( 'Y-m-d H:i:s' );
		$sql     = "DELETE FROM $this->table WHERE `expires` < '$expires'";

		return $wpdb->query( $sql );
	}


	public function get_user( $token ){
		global $wpdb;
		$expires = gmdate( 'Y-m-d H:i:s' );
		$token   = wp_hash( $token );

		$sql = "SELECT user_id FROM $this->table WHERE `token` = '$token' AND `expires` > '$expires'";

		return $wpdb->get_var( $sql );
	}


	public function add_token( $columns ){
		$columns[ 'token' ] = wp_hash( $columns[ 'token' ] );
		$this->add( $columns );
		$this->clean_expired_tokens();
	}


	protected function create_table(){
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE IF NOT EXISTS " . $this->table . " (
	  id BIGINT(50) NOT NULL AUTO_INCREMENT,
	  user_id BIGINT(20) NOT NULL,
	  token VARCHAR(50) NOT NULL,
      expires TIMESTAMP NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY auth_tokens (token, expires)
	  );";

		dbDelta( $sql );
	}
}