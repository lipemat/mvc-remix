<?php

namespace MVC\Util;

/**
 * Taxonomy_Meta
 *
 * @author  Mat Lipe
 * @since   6/10/2015
 *
 * @package MVC\Util
 */
class Taxonomy_Meta {

	use \MVC\Traits\Singleton;

	const DB_OPTION = 'mvc_taxonomy_meta';


	public function hooks(){
		add_action( 'init', array( $this, 'configure_term_meta' ), 0, 0 );
		add_action( 'created_term', array( $this, 'delete_extra_meta' ), - 1000, 3 );
		add_action( 'delete_term', array( $this, 'delete_all_term_meta' ), 10, 3 );
		add_action( 'wpmu_drop_tables', array( $this, 'drop_table' ), 10, 1 );
		add_action( 'switch_blog', array( $this, 'switch_to_blog' ), 10, 2 );
	}


	public function get_term_meta( $term_id, $taxonomy, $key, $single = false ){
		return get_metadata( 'term_taxonomy', get_term_taxonomy_id( $term_id, $taxonomy ), $key, $single );
	}


	public function add_term_meta( $term_id, $taxonomy, $meta_key, $meta_value, $unique = false ){
		return add_metadata( 'term_taxonomy', get_term_taxonomy_id( $term_id, $taxonomy ), $meta_key, $meta_value, $unique );
	}


	public function update_term_meta( $term_id, $taxonomy, $meta_key, $meta_value, $prev_value = '' ){
		return update_metadata( 'term_taxonomy', get_term_taxonomy_id( $term_id, $taxonomy ), $meta_key, $meta_value, $prev_value );
	}


	public function delete_term_meta( $term_id, $taxonomy, $meta_key, $meta_value = '' ){
		return delete_metadata( 'term_taxonomy', get_term_taxonomy_id( $term_id, $taxonomy ), $meta_key, $meta_value );
	}


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
	 * When a term is deleted, we need to delete all of its
	 * associated meta
	 *
	 * @param int    $term_id
	 * @param int    $tt_id
	 * @param string $taxonomy
	 */
	public function delete_all_term_meta( $term_id, $tt_id, $taxonomy ){
		global $wpdb;
		$term_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->term_taxonomymeta WHERE term_taxonomy_id = %d ", $tt_id ) );
		foreach( $term_meta_ids as $mid ){
			delete_metadata_by_mid( 'term_taxonomy', $mid );
		}
	}


	/**
	 * Make sure db tables exist
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
	 * Some data from old terms seems to be stuck in the
	 * database, and is causing corrupt terms. Delete
	 * any taxonomy meta for the term_taxonomy_id that
	 * already exists when the term is created.
	 *
	 * @param int    $term_id
	 * @param int    $tt_id
	 * @param string $taxonomy
	 */
	public function delete_extra_meta( $term_id, $tt_id, $taxonomy ){
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->term_taxonomymeta WHERE term_taxonomy_id=%d", $tt_id ) );
	}


	public function drop_table( $tables ){
		global $wpdb;
		// don't use $wpdb->term_taxonomymeta, points to wrong blog
		$tables[ ] = $wpdb->prefix . 'term_taxonomymeta';

		return $tables;
	}


	/**
	 * Update the table pointer to correct blog
	 *
	 * @param int $new_blog_id
	 * @param int $old_blog_id
	 */
	public function switch_to_blog( $new_blog_id, $old_blog_id ){
		global $wpdb;
		$table_name              = $wpdb->prefix . 'term_taxonomymeta';
		$wpdb->term_taxonomymeta = $table_name;
	}


	/**
	 * Create the term meta table
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