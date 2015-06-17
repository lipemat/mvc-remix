<?php
namespace MVC\Util;

/**
 * Cache
 * 
 * Ability to manage cache a little easier for groups etc
 * 
 * @uses MVC\Cache::get_instance() will add ability to clear cache from admin bar
 * 
 * @package MVC Theme
 * @namespace MVC
 * @class Cache
 * 
 * @todo General cleanup of code and PHP docs
 * @todo More testing required before recommending use
 * 
 */
class Cache {
	use \MVC\Traits\Singleton;

	const OPTION_GROUP_KEYS = 'mvc_cache_group_keys';
	const DEFAULT_GROUP = 'mvc';
	const FLUSH_ON_SAVE_POST_GROUP = 'mvc_cache_flush_save_post';

	/**
	 * Constructor
	 * 
	 * 
	 */
	function __construct(){
		$this->hooks();
	}


	/**
	 * Hooks
	 * 
	 * @return void
	 */
	protected function hooks() {
		add_action( 'init', array( $this, 'maybe_clear_cache' ), 9, 0 );
		
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_button' ), 100, 1 );
		}

		add_action( 'save_post', array( $this, 'clear_save_post_group' ), 1, 0 );
	}

	public function clear_save_post_group(){
		self::flush_group( self::FLUSH_ON_SAVE_POST_GROUP );
	}


	public function maybe_clear_cache() {
		if ( empty($_REQUEST['mvc-clear-cache']) || empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'clear-cache') ) {
			return; 
		}
		self::flush_all();
		wp_redirect( remove_query_arg( array( 'mvc-clear-cache', '_wpnonce' ) ) );
		die();
	}


	public function add_admin_bar_button( $admin_bar ) {
		$admin_bar->add_menu( array(
			'parent' => '',
			'id' => 'clear-cache',
			'title' => __( 'Clear Cache', 'mvc' ),
			'meta' => array( 'title' => __( 'Clear the cache for this site', 'mvc' ) ),
			'href' => wp_nonce_url( add_query_arg( array( 'mvc-clear-cache' => 1 ) ), 'clear-cache' ),
		) );
	}

	/**
	 * Process the cache key so that any unique data may serve as a key, even if it's an object or array.
	 *
	 * @param array|object|string $key
	 *
	 * @return bool|string
	 */
	private static function filter_key( $key ) {
		if ( empty( $key ) ) return false;
		$key = ( is_array( $key ) ) ? md5( serialize( $key ) ) : $key;
		return $key;
	}


	public static function set( $key, $value, $group = self::DEFAULT_GROUP, $expire = 0 ) {
		$group = self::get_group_key($group);
		return wp_cache_set( self::filter_key( $key ), $value, $group, $expire );
	}


	public static function get( $key, $group = self::DEFAULT_GROUP ) {
		$group = self::get_group_key($group);
		$results = wp_cache_get( self::filter_key( $key ), $group );
		return $results;
	}


	public static function delete( $key, $group = self::DEFAULT_GROUP ) {
		$group = self::get_group_key($group);
		$results = wp_cache_delete( self::filter_key( $key ), $group );
		return $results;
	}


	/**
	 * Clear the cache on all blogs
	 */
	public static function flush_all_sites() {
		global $wp_object_cache;
		if ( isset($wp_object_cache->mc) ) {
			foreach ( array_keys( $wp_object_cache->mc ) as $group ) {
				$wp_object_cache->mc[$group]->flush();
			}
		}
	}


	/**
	 * Change the key for everything we've tracked,
	 * thereby flushing the cache for a blog
	 */
	public static function flush_all() {
		$keys = self::get_group_keys();
		$time = time();
		foreach ( $keys as $key => &$value ) {
			$value = $key.$time;
		}
		self::set_group_keys($keys);

		wp_cache_flush();
	}


	public static function flush_group( $group = self::DEFAULT_GROUP ) {
		self::update_group_key($group);
	}


	private static function set_group_keys( array $keys ) {
		update_option(self::OPTION_GROUP_KEYS, $keys);
	}


	private static function get_group_keys() {
		$keys = get_option(self::OPTION_GROUP_KEYS, array());
		if ( empty($keys) || !is_array($keys) ) { $keys = array(); };
		return $keys;
	}


	private static function get_group_key( $group ) {
		$keys = self::get_group_keys();
		if ( isset($keys[$group]) ) {
			return $keys[$group];
		}
		// make a new key
		$group = self::update_group_key( $group );
		return $group;
	}


	private static function update_group_key( $group ) {
		$keys = self::get_group_keys();
		$new = $group.time();
		$keys[$group] = $new;
		self::set_group_keys($keys);
		return $new;
	}

}
