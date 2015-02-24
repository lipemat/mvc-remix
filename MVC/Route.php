<?php

namespace MVC;


use WP_Query;

/**
 * Route
 *
 * Routes a custom url to a page template
 *
 * @example \MVC\Route::add( 'custom-page', array( 'title' => 'Custom Page', 'template' => get_stylesheet_directory() . '/content/custompage.php' ) );
 *          
 *
 *
 * @since     2/24/2015
 *
 * @package   Mvc Theme
 * @namespace MVC
 */
class Route {

	use Traits\Singleton;

	const POST_TYPE = 'mvc_route';
	const QUERY_VAR = 'mvc_route_template';
	const OPTION = 'mvc_route_cache';

	protected static $rewrite_slug = 'Mvc_Route';

	protected static $post_id = 0;

	private static $routes = array();


	protected function __construct(){
		$this->hooks();
	}


	/**
	 * add_route
	 *
	 * @param string $url      - url appended to the sites home url
	 * @param array  $args     (
	 *                         title => string,
	 *                         template => string - full file path to template
	 *                         )
	 *
	 *
	 * @return void
	 */
	public static function add( $url, array $args ){
		self::$routes[ $url ] = $args;
	}


	protected function hooks(){
		add_filter( 'query_vars', array( $this, 'add_query_var' ) );
		add_action( 'init', array( $this, 'setup_endpoints' ) );
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'pre_get_posts', array( $this, 'edit_query' ), 10, 1 );

		add_action( 'wp_loaded', array( $this, 'maybe_flush_rules' ) );
	}


	/**
	 * add_post_hooks
	 *
	 * Hooks we only run if we are retrieving a custom route
	 *
	 * @return void
	 */
	protected function add_post_hooks(){
		add_filter( 'the_title', array( $this, 'get_title' ), 10, 2 );
		add_filter( 'single_post_title', array( $this, 'get_title' ), 10, 2 );

		add_filter( 'template_include', array( $this, 'override_template' ), 10, 1 );

	}


	/**
	 * If asking for a paged router page, or if %category% is in
	 * the permastruct, WordPress will try to redirect to the URL
	 * for the dummy page. This should stop that from happening.
	 *
	 * @wordpress-filter redirect_canonical
	 *
	 * @param string $redirect_url
	 * @param string $requested_url
	 *
	 * @return bool
	 */
	public function override_redirect( $redirect_url, $requested_url ){
		if( $redirect_url && get_query_var( self::QUERY_VAR ) ){
			return false;
		}
		if( $redirect_url && get_permalink( self::get_post_id() ) == $redirect_url ){
			return false;
		}

		return $redirect_url;
	}


	/**
	 * maybe_flush_rules
	 *
	 * Adding rewrite rules requires a flush of all rules
	 * This checks for new ones then flushes as needed
	 *
	 * @return void
	 */
	public function maybe_flush_rules(){
		if( get_option( self::OPTION ) != md5( serialize( self::$routes ) ) ){
			flush_rewrite_rules();
			update_option( self::OPTION, md5( serialize( self::$routes ) ) );
		}
	}


	/**
	 * add_query_var
	 *
	 * Add a query var to allow for our custom urls to be specificed
	 *
	 * @param $vars
	 *
	 * @return array
	 */
	public function add_query_var( $vars ){
		$vars[ ] = self::QUERY_VAR;

		return $vars;
	}


	/**
	 * setup_endpoints
	 *
	 * Register the rewrite rules to send the appropriate urls to our
	 * custom query var which will tell us what route we are using
	 *
	 *
	 * @return void
	 */
	public function setup_endpoints(){
		foreach( self::$routes as $_route => $_args ){
			add_rewrite_rule( $_route, 'index.php?post_type=' . self::POST_TYPE . '&' . self::QUERY_VAR . '=' . $_route, 'top' );
		}
	}


	/**
	 * Use the specified template file
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function override_template( $template ){
		$route = $this->get_current_route();

		return $route[ 'template' ];
	}


	public function get_current_route(){
		$route = get_query_var( self::QUERY_VAR );

		return self::$routes[ $route ];
	}


	/**
	 * Set the title for the placeholder page
	 *
	 * @param string $title
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public function get_title( $title, $post ){
		$post = get_post( $post );
		if( $post->ID == self::get_post_id() ){
			$route = $this->get_current_route();

			return $route[ 'title' ];
		}

		return $title;
	}


	/**
	 * Edit WordPress's query so it finds our placeholder page
	 *
	 * @param WP_Query $query
	 *
	 * @return void
	 */
	public function edit_query( WP_Query $query ){
		if( isset( $query->query_vars[ self::QUERY_VAR ] ) ){

			$this->add_post_hooks();

			// make sure we get the right post
			$query->query_vars[ 'p' ] = self::get_post_id();

			// override any vars WordPress set based on the original query
			$query->is_single            = true;
			$query->is_singular          = true;
			$query->is_404               = false;
			$query->is_home              = false;
			$query->is_archive           = false;
			$query->is_post_type_archive = false;
		}
	}


	/********** statics ***************/

	/**
	 * register_post_type
	 *
	 * Setup a special post type to be used in the queries so we return
	 * an actual post and not a 404.
	 * A single post of this type is created to be queried
	 * We then filter the post to match our needs
	 *
	 * @static
	 *
	 * @return void
	 */
	public static function register_post_type(){

		$args = array(
			'public'              => false,
			'show_ui'             => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'supports'            => array( 'title' ),
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => self::$rewrite_slug,
				'with_front' => false,
				'feeds'      => false,
				'pages'      => false,
			)
		);
		register_post_type( self::POST_TYPE, $args );
	}


	/**
	 * Get the ID of the placeholder post
	 *
	 * @static
	 * @return int
	 */
	protected static function get_post_id(){
		if( !self::$post_id ){
			$posts = get_posts( array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			) );
			if( $posts ){
				self::$post_id = $posts[ 0 ]->ID;
			} else {
				self::$post_id = self::make_post();
			}
		}

		return self::$post_id;
	}


	/**
	 * Make a new placeholder post
	 *
	 * @static
	 * @return int The ID of the new post
	 */
	private static function make_post(){
		$post = array(
			'post_title'  => 'Mvc Placeholder Post',
			'post_status' => 'publish',
			'post_type'   => self::POST_TYPE,
		);
		$id   = wp_insert_post( $post );
		if( is_wp_error( $id ) ){
			return 0;
		}

		return $id;
	}

}