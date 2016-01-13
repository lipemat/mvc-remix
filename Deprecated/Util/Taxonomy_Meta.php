<?php

namespace MVC\Util;

/**
 * Taxonomy_Meta
 *
 * @deprecated as of version 4.4 of WordPress
 */
class Taxonomy_Meta {
	use \MVC\Traits\Singleton;

	const DB_OPTION = 'mvc_taxonomy_meta';

	public function hooks(){

		//less than version 4.4
		if( version_compare( $GLOBALS[ 'wp_version' ], "4.4" ) === - 1 ){
			add_action( 'init', array( $this, 'configure_term_meta' ), 0, 0 );
			add_action( 'created_term', array( $this, 'delete_extra_meta' ), - 1000, 3 );
			add_action( 'delete_term', array( $this, 'delete_all_term_meta' ), 10, 3 );
			add_action( 'switch_blog', array( $this, 'switch_to_blog' ), 10, 2 );

		} else {
			//backward compatibility for anything still using this property
			global $wpdb;
			$wpdb->term_taxonomymeta = $wpdb->termmeta;
		}

		add_action( 'wp_upgrade', array( $this, 'migrate_data_to_core_structure' ), 1, 2 );
		add_action( 'wpmu_drop_tables', array( $this, 'drop_table' ), 10, 1 );

	}


	/**
	 * Version 4.4 implemented their own term meta structure.
	 * The terms used to be able to share term_ids and only
	 * had a unique term_taxonomy_id. That is not the case any
	 * more so this migrates all the old data over to the new
	 * termmeta table.
	 *
	 *
	 * @param int $db_version            - upgrading to version
	 * @param int $wp_current_db_version - current (old) version
	 *
	 * @return void
	 */
	public function migrate_data_to_core_structure( $db_version, $wp_current_db_version ){
		//4.4 or greater coming from 4.3 or lower
		if( $db_version >= 35700 && $wp_current_db_version < 35700 ){
			global $wpdb;
			$existing = $wpdb->get_var( "SELECT meta_id FROM $wpdb->termmeta LIMIT 1" );
			if( $existing ){
				return; //bail because we have already upgraded once
			}
			$query = "SELECT term_id, meta_key, meta_value
						FROM $wpdb->term_taxonomymeta AS meta
						LEFT JOIN $wpdb->term_taxonomy AS terms
						USING (term_taxonomy_id)";
			$meta  = $wpdb->get_results( $query );

			$import_query  = "INSERT INTO $wpdb->termmeta (term_id, meta_key, meta_value)";
			$sql_query_sel = array();
			foreach( $meta as $_meta ){
				$meta_key        = $_meta->meta_key;
				$meta_value      = addslashes( $_meta->meta_value );
				//because we could have come old data
				if( !empty( $_meta->term_id ) && !empty( $meta_key ) && !empty( $meta_value ) ){
					$sql_query_sel[] = "SELECT $_meta->term_id, '$meta_key', '$meta_value'";
				}
			}

			$import_query .= implode( " UNION ALL ", $sql_query_sel );
			$wpdb->query( $import_query );
		}

	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 * @see        get_term_meta()
	 */
	public function get_term_meta( $term_id, $taxonomy, $key, $single = false ){
		if( function_exists( 'get_term_meta' ) ){
			return get_term_meta( $term_id, $key, $single );
		} else {
			return get_metadata( 'term_taxonomy', $this->get_term_taxonomy_id( $term_id, $taxonomy ), $key, $single );
		}
	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 * @see        add_term_meta()
	 */
	public function add_term_meta( $term_id, $taxonomy, $meta_key, $meta_value, $unique = false ){
		if( function_exists( 'add_term_meta' ) ){
			return add_term_meta( $term_id, $meta_key, $meta_value, $unique );

		} else {
			return add_metadata( 'term_taxonomy', $this->get_term_taxonomy_id( $term_id, $taxonomy ), $meta_key, $meta_value, $unique );
		}
	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 * @see        update_term_meta()
	 */
	public function update_term_meta( $term_id, $taxonomy, $meta_key, $meta_value, $prev_value = '' ){
		if( function_exists( 'update_term_meta' ) ){
			return update_term_meta( $term_id, $meta_key, $meta_value, $prev_value );

		} else {
			return update_metadata( 'term_taxonomy', $this->get_term_taxonomy_id( $term_id, $taxonomy ), $meta_key, $meta_value, $prev_value );
		}
	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 * @see        delete_term_meta()
	 */
	public function delete_term_meta( $term_id, $taxonomy, $meta_key, $meta_value = '' ){
		if( function_exists( 'get_term_meta' ) ){
			return delete_term_meta( $term_id, $meta_key, $meta_value );

		} else {
			return delete_metadata( 'term_taxonomy', $this->get_term_taxonomy_id( $term_id, $taxonomy ), $meta_key, $meta_value );
		}
	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 */
	public function get_term_taxonomy_id( $term_id, $taxonomy ){
		if( !$term_id ){
			return 0;
		}
		$term = get_term( $term_id, $taxonomy );
		if( !$term ){
			return 0;
		}
		if( $term ){
			return $term->term_taxonomy_id;
		}
	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 */
	public function delete_all_term_meta( $term_id, $tt_id, $taxonomy ){
		global $wpdb;
		$term_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->term_taxonomymeta WHERE term_taxonomy_id = %d ", $tt_id ) );
		foreach( $term_meta_ids as $mid ){
			delete_metadata_by_mid( 'term_taxonomy', $mid );
		}
	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 */
	public function configure_term_meta(){
		global $wpdb;

		if( get_option( self::DB_OPTION ) !== "installed" ){

			$this->_create_term_taxonomy_meta_table();

			update_option( self::DB_OPTION, "installed" );
		}

		$table_name = $wpdb->prefix . 'term_taxonomymeta';

		$wpdb->term_taxonomymeta = $table_name;

	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 */
	public function delete_extra_meta( $term_id, $tt_id, $taxonomy ){
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->term_taxonomymeta WHERE term_taxonomy_id=%d", $tt_id ) );
	}


	public function drop_table( $tables ){
		global $wpdb;
		// don't use $wpdb->term_taxonomymeta, points to wrong blog
		$tables[] = $wpdb->prefix . 'term_taxonomymeta';

		return $tables;
	}


	/**
	 * @deprecated as of version 4.4 in WP Core
	 */
	public function switch_to_blog( $new_blog_id, $old_blog_id ){
		global $wpdb;
		$table_name              = $wpdb->prefix . 'term_taxonomymeta';
		$wpdb->term_taxonomymeta = $table_name;
	}


	/**
	 * @deprecated
	 */
	private function _create_term_taxonomy_meta_table(){

		global $wpdb;

		$table_name = $wpdb->prefix . 'term_taxonomymeta';

		if( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ){

			$sql = sprintf( 'CREATE TABLE IF NOT EXISTS `%s` (
							      `meta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
							      `term_taxonomy_id` BIGINT(20) UNSIGNED NOT NULL,
							      `meta_key` VARCHAR(255),
							      `meta_value` LONGTEXT,
							      PRIMARY KEY (`meta_id`),
								  KEY `term_taxonomy_id` (`term_taxonomy_id`),
							      KEY `meta_key` (`meta_key`)
							   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;', $table_name );

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}
}