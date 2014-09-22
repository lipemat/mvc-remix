<?php
namespace MVC;

/**
 * Custom Post Type
 * 
 * @package MVC Theme
 * @namespace MVC
 * 
 * @todo Cleanup the phpdocs and all around code
 * 
 */
class Custom_Post_Type {
	
	private static $post_type_registry = array();
	private static $rewrite_checked = false;

	public $post_type_label_singular = '';
	public $post_type_label_plural = '';
	
	/**
	 * @var string The label that will be shown on the front end title bar
	 */
	public $front_end_label = '';

	protected $supports = array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' );

	public $description = 'A custom post type';
	public $hierarchical = FALSE;

	public $capability_type = 'post';
	public $capabilities = array();
	public $map_meta_cap = FALSE;

	public $menu_icon = NULL;
	public $menu_position = 5;

	public $public = TRUE;
	public $publicly_queryable = NULL;
	public $exclude_from_search = NULL;
	public $has_archive = TRUE;
	public $slug = '';
	public $query_var = TRUE;

	public $show_ui = NULL;
	public $show_in_menu = NULL;
	public $show_in_nav_menus = NULL;
	public $show_in_admin_bar = NULL;

	public $rewrite = NULL;
	public $permalink_epmask = EP_PERMALINK;
	public $can_export = TRUE;
	public $taxonomies = array();
	public $labels;

	/**
	 * Post Type
	 * 
	 * The post type slug
	 * 
	 * @var string
	 */
	protected $post_type = '';
	

	/**
	 * Constructor
	 * 
	 * @param string $post_type;
	 * 
	 * @return self()
	 */
	public function __construct( $post_type ) {
		if( !self::$rewrite_checked ){
			add_action( 'init', array(__CLASS__, 'check_rewrite_rules' ), 10000, 0);
			self::$rewrite_checked = true;
			
		}
		$this->post_type = $post_type;
		$this->hooks();
		$this->filters();
	}

	/**
	 * Hooks
	 * 
	 * Setup neccessary hooks to register post type
	 * 
	 * @return void
	 * 
	 */
	protected function hooks() {
		
		//allow methods added to the init hook to customize the post type
		add_action( 'wp_loaded', array( $this, 'register_post_type' ) );

	}

	/**
	 * Check Rewrite Rules
	 * 
	 * If the post types registered through this API have changed,
	 * rewrite rules need to be flushed.
	 * 
	 * @static
	 * 
	 * @return void
	 * 
	 */
	public static function check_rewrite_rules() {
		if ( get_option( 'mvc_cpt_registry' ) != self::$post_type_registry ) {
			
			flush_rewrite_rules();

			update_option( 'mvc_cpt_registry', self::$post_type_registry );
		}
	}
	
	
	
	/**
	 * Filters
	 * 
	 * Setup neccessary filters to have appropriate update and edit messages etc.
	 * 
	 * @return void
	 * 
	 */
	function filters(){	
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ), 10, 1 );
		add_filter( 'post_type_archive_title', array($this, 'filter_post_type_archive_title' ), 10, 1 );		
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_edit_messages' ), 10, 2);
		
	}
	
	
	

	/**
	 * Register this post type with WordPress
	 *
	 * @return void
	 */
	public function register_post_type() {
		$response = register_post_type($this->post_type, $this->post_type_args());
		if ( !is_wp_error($response) ) {
			self::$post_type_registry[$this->post_type] = get_class($this);
		}
	}

	/**
	 * The the post type defined by this class
	 *
	 * @param string $format Either 'id' (for the post type ID) or 'object' (for the WP post type object)
	 * @return object|string
	 */
	public function get_post_type( $format = 'id' ) {
		switch ( $format ) {
			case 'object':
				return get_post_type_object($this->post_type);
			default:
				return $this->post_type;
		}
	}

	/**
	 * Return the slug of the supertype
	 *
	 * @return string supertype slug
	 */
	public function get_slug() {
		return $this->slug ? $this->slug : $this->post_type;
	}

	/**
	 * Build the args array for the post type definition
	 *
	 * @return array
	 */
	protected function post_type_args() {
		$args = array(
			'labels' => $this->post_type_labels(),
			'description' => $this->description,
			'public' => $this->public,
			'publicly_queryable' => $this->publicly_queryable,
			'show_ui' => $this->show_ui,
			'show_in_menu' => $this->show_in_menu,
			'show_in_nav_menus' => $this->show_in_nav_menus,
			'menu_icon' => $this->menu_icon,
			'capability_type' => $this->capability_type,
			'map_meta_cap' => $this->map_meta_cap,
			'capabilities' => $this->capabilities,
			'hierarchical' => $this->hierarchical,
			'supports' => $this->supports,
			'has_archive' => $this->has_archive,
			'taxonomies' => $this->taxonomies,
			'rewrite' => $this->rewrites(),
			'query_var' => $this->query_var,
			'menu_position' => $this->menu_position,
			'exclude_from_search' => $this->exclude_from_search,
			'can_export' => $this->can_export,
		);

		$args = apply_filters( 'mvc_custom_post_type_args', $args, $this->post_type);
		$args = apply_filters( 'mvc_custom_post_type_args_'.$this->post_type, $args);

		return $args;
	}


	/**
	 * Rewrites
	 *
	 * Build the rewrites param. Will send defaults if not set
	 *
	 * @return array
	 */
	protected function rewrites(){
		if( empty( $this->rewrite ) ){
			return array(
				'slug' => $this->get_slug(),
				'with_front' => FALSE,
			);
		} else {
			return $this->rewrite;
		}
	}



	/**
	 * Post Type labels
	 * 
	 * Build the labels array for the post type definition
	 *
	 * @param string $single
	 * @param string $plural
	 * @return array
	 */
	protected function post_type_labels( $single = '', $plural = '' ) {
		
		$single = $single ? $single : $this->post_type_label( 'singular' );
		$plural = $plural ? $plural : $this->post_type_label( 'plural' );

		$labels = array(
			'name' => $plural,
			'singular_name' => $single,
			'add_new' => __( 'Add New' ),
			'add_new_item' => sprintf(__( 'Add New %s' ),$single),
			'edit_item' => sprintf(__( 'Edit %s' ),$single),
			'new_item' => sprintf(__( 'New %s' ),$single),
			'view_item' => sprintf(__( 'View %s' ),$single),
			'search_items' => sprintf(__( 'Search %s' ),$plural),
			'not_found' => sprintf(__( 'No %s Found' ),$plural),
			'not_found_in_trash' => sprintf(__( 'No %s Found in Trash' ),$plural),
			'menu_name' => $plural,
		);
		
		if( !empty( $this->labels ) ){
			$labels = wp_parse_args( $this->labels, $labels );	
		}

		$labels = apply_filters( 'mvc_custom_post_type_labels', $labels, $this->post_type);
		$labels = apply_filters( 'mvc_custom_post_type_labels_'.$this->post_type, $labels);

		return $labels;
	}



	/**
	 * Bulk Edit Messages
	 * 
	 * Filters the bulk edit message to match the custom post type
	 * 
	 * @uses added to the post_row_actions filter by self::register_post_type
	 * 
	 * @param array $actions - existing actions
	 * 
	 * @return array
	 * 
	 */
	public function bulk_edit_messages( $bulk_messages, $bulk_counts ){
		$bulk_messages[$this->post_type] = array(
        	'updated'   => _n( 
        		'%s '.$this->post_type_label_singular.' updated.', 
        		'%s '.$this->post_type_label_plural.' updated.', 
        		$bulk_counts['updated']
        	 ),
        		
        	'locked'    => _n( 
        		'%s '.$this->post_type_label_singular.' not updated, somebody is editing it.', 
        		'%s '.$this->post_type_label_plural.' not updated, somebody is editing them.', 
        		$bulk_counts['locked']  
        	),
        		
        	'deleted'   => _n( 
        		'%s '.$this->post_type_label_singular.' permanently deleted.', 
        		'%s '.$this->post_type_label_plural.' permanently deleted.', 
        		$bulk_counts['deleted']
        	),
        		
        	'trashed'   => _n( 
        		'%s '.$this->post_type_label_singular.' moved to the Trash.', 
        		'%s '.$this->post_type_label_plural.' moved to the Trash.', 
        		$bulk_counts['trashed']
        	),
        		
        	'untrashed' => _n( 
        		'%s '.$this->post_type_label_singular.' restored from the Trash.', 
        		'%s '.$this->post_type_label_plural.' restored from the Trash.', 
        		$bulk_counts['untrashed']
        	),
    	);
		
		return $bulk_messages;
	}
	

	/**
	 * Add messaging for this custom post type.
	 *
	 * @param array $messages list of alert messages
	 * @return array
	 */
	public function post_updated_messages( $messages = array() ) {
		global $post, $post_ID;

		$messages[$this->post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( '%s updated. <a href="%s">View the %s...</a>' ), $this->post_type_label(), esc_url( get_permalink($post_ID) ), strtolower($this->post_type_label()) ),
			2 => __( 'Custom field updated.' ),
			3 => __( 'Custom field deleted.' ),
			4 => sprintf( __( '%s updated.' ), $this->post_type_label() ),
			5 => isset($_GET['revision']) ? sprintf( __( '%s restored to revision from %s' ), $this->post_type_label(), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( '%s published. <a href="%s">View %s</a>' ), $this->post_type_label(), esc_url( get_permalink($post_ID) ), strtolower($this->post_type_label()) ),
			7 => sprintf( __( '%s saved.' ), $this->post_type_label() ),
			8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>' ), $this->post_type_label(), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ), strtolower($this->post_type_label()) ),
			9 => sprintf( __( '%3$s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ), strtolower($this->post_type_label()) ),
			10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>' ), $this->post_type_label(), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ), strtolower($this->post_type_label()) ),
		);

		return $messages;
	}


	/**
	 * Post Typle Label
	 * 
	 * Set and get a post type label
	 * 
	 * @param string $quantity - set to plural if setting plural
	 * 
	 * @return string
	 */
	public function post_type_label( $quantity = 'singular' ) {

		switch ( $quantity ) {
			case 'plural':
				if ( !$this->post_type_label_plural ) {
					$this->set_post_type_label($this->post_type_label_singular);
				}
				return $this->post_type_label_plural;
			default:
				if ( !$this->post_type_label_singular ) {
					$this->set_post_type_label();
				}
				return $this->post_type_label_singular;
		}
	}


	/**
	 * Set Post Type Label
	 * 
	 * Set the labels for the post type
	 * 
	 * @param string $singular
	 * @param string $plural
	 * 
	 * @return void
	 */
	public function set_post_type_label( $singular = '', $plural = '' ) {
		
		if ( !$singular ) {
			$singular = str_replace( '_', ' ', $this->post_type );
			$singular = ucwords( $singular );
		}
		
		if ( !$plural ) {
	    	$end = substr( $singular, -1 );
        	if( $end == 's' ){
            	$plural = ucwords( $singular.'es' );
        	} elseif( $end == 'y' ){
            	$plural = ucwords( rtrim( $singular, 'y' ) . 'ies' );
        	} else {
        		$plural = ucwords( $singular.'s' );
			}

		}
		$this->post_type_label_singular = $singular;
		$this->post_type_label_plural = $plural;
	}

	/**
	 * Get the label to display for this post type on public-facing pages
	 *
	 * @return string
	 */
	public function public_label() {
		if ( $this->front_end_label ) {
			return $this->front_end_label;
		} else {
			return $this->post_type_label( 'plural' );
		}
	}

	public function filter_post_type_archive_title( $title ) {
		if ( is_post_type_archive($this->post_type) ) {
			$title = $this->public_label();
		}
		return $title;
	}


	public function add_support( $features = array() ) {
		if ( !is_array($features) ) {
			$features = array($features);
		}
		$this->supports = array_unique(array_merge($this->supports, $features));
		return $this->supports;
	}

	public function remove_support( $features = array() ) {
		if ( !is_array($features) ) {
			$features = array($features);
		}
		$this->supports = array_diff($this->supports, $features);
		return $this->supports;
	}
}