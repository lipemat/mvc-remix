<?php
namespace MVC;

/**
 * Taxonomy
 * 
 * Register taxonomies
 * 
 * @example mvc_versions()->add_update( %version%, %function% );
 * 
 * @uses You must add updates during the init hook, because this will run them at the end of the init hook
 * 
 * @package MVC Theme
 * @namespace MVC
 * @class Taxonomy
 * 
 * @todo General cleanup of code and PHP docs
 * 
 */
 class Taxonomy {
	private static $taxonomy_registry = array();
	private static $rewrite_checked = false;

	/**
	 * The ID of the taxonomy
	 * @var string
	 */
	protected $taxonomy = '';
	protected $label_singular = '';
	protected $label_plural = '';
	protected $label_menu = '';
	protected $default_terms = array();
	

	/**
	 * The arguments for the taxonomy
	 * Set these in the constructed class
	 * 
	 * @var mixed
	 * 
	 */
	public $post_types = array();
	public $public = TRUE;
	public $publicly_queryable = NULL;
	public $show_ui = NULL;
	public $show_in_nav_menus = NULL;
	public $show_tagcloud = NULL;
	public $hierarchical = FALSE;
	public $update_count_callback = NULL;
	public $query_var = NULL;
	public $capabilities = array();
	public $slug = NULL;
	public $meta_box_cb = NULL;
	

	/**
	 * Construct
	 * 
	 * Takes care of the neccessary hooks and registering
	 * 
	 * @uses set the class vars to edit arguments
	 * 
	 * @param string $taxonomy - Singular Title case name of taxonomy
	 * @param array  [$post_types] - may also be set by $this->post_types = array() 
	 * 
	 */
	public function __construct( $taxonomy, $post_types = array() ) {
		$this->post_types = $post_types;
		
		if( !self::$rewrite_checked ){
			add_action( 'init', array(__CLASS__, 'check_rewrite_rules' ), 10000, 0);
			self::$rewrite_checked = true;			
		}
		
		$this->taxonomy = strtolower( str_replace( ' ', '_', $taxonomy) );
		$this->hooks();
	}


	/**
	 * Return $string after translating it with the plugin's text domain
	 * @static
	 * @param string $string
	 * @return string|void
	 */
	protected static function __( $string ) {
		return __($string, self::TEXT_DOMAIN);
	}

	/**
	 * @static
	 *
	 * If the post types registered through this API have changed,
	 * rewrite rules need to be flushed.
	 */
	public static function check_rewrite_rules() {
		if ( get_option('mvc_taxonomy_registry') != self::$taxonomy_registry ) {
			add_action('init', 'flush_rewrite_rules', 10000, 0);
			update_option('mvc_taxonomy_registry', self::$taxonomy_registry);
		}
	}

	/**
	 * Get a registered taxonomy object
	 * @static
	 * @param $taxonomy
	 * @return Taxonomy|NULL
	 */
	public static function get_taxonomy( $taxonomy ) {
		if ( isset(self::$taxonomy_registry[$taxonomy]) ) {
			return self::$taxonomy_registry[$taxonomy];
		}
		return NULL;
	}

	/**
	 * Hook the taxonomy into worpress
	 */
	protected function hooks() {
		
		//so we can add and edit stuff on init hook
		add_action( 'wp_loaded', array( $this, 'register_taxonomy' ), 8, 0 );
		add_action( 'wp_loaded', array( $this, 'register_default_terms' ), 9, 0 );
		
	}

	
	/**
	 * Set Default Terms
	 * 
	 * Specify terms to be registered automatically when a taxonomy is created
	 * 
	 * @param array $terms = array( slug' => term, slug => term );
	 * 
	 * @return void
	 */
	public function set_default_terms( $terms = array() ) {
		$this->default_terms = $terms;
	}

	public function register_default_terms() {
		// don't do anything if the taxonomy already has terms
		if ( !$this->default_terms || get_terms($this->taxonomy, array('hide_empty'=>FALSE)) ) {
			return;
		}
		foreach( $this->default_terms as $slug => $term ) {
			$args = array();
			if ( !is_numeric($slug) ) {
				$args['slug'] = $slug;
			}
			wp_insert_term($term, $this->taxonomy, $args);
		}
	}



	/**
	 * Register this post type with WordPress
	 *
	 * @return void
	 */
	public function register_taxonomy() {
		$response = register_taxonomy($this->taxonomy, $this->post_types, $this->taxonomy_args());
		if ( !is_wp_error($response) ) {
			self::$taxonomy_registry[$this->taxonomy] = $this;
		}
	}

	/**
	 * Build the args array for the post type definition
	 *
	 * @uses may be overridden using the matching class vars
	 * 
	 * @return array
	 */
	protected function taxonomy_args() {
		$args = array(
			'labels'                => $this->taxonomy_labels(),
			'public'                => $this->public,
			'publicly_queryable'    => $this->publicly_queryable,
			'show_ui'               => $this->show_ui,
			'show_in_nav_menus'     => $this->show_in_nav_menus,
			'show_tagcloud'         => $this->show_tagcloud,
			'hierarchical'          => $this->hierarchical,
			'update_count_callback' => $this->update_count_callback,
			'query_var'             => empty($this->query_var)?$this->taxonomy:$this->query_var,
			'rewrite'               => array(
				'slug'         => $this->get_slug(),
				'with_front'   => FALSE,
				'hierarchical' => $this->hierarchical,
			),
			'capabilities'          => $this->capabilities,
			'meta_box_cb'           => $this->meta_box_cb
		);

		$args = apply_filters('mvc_custom_taxonomy_args', $args, $this->taxonomy);
		$args = apply_filters('mvc_custom_taxonomy_args_'.$this->taxonomy, $args);

		return $args;
	}

	/**
	 * Build the labels array for the post type definition
	 *
	 * @param string $single
	 * @param string $plural
	 * @return array
	 */
	protected function taxonomy_labels( $single = '', $plural = '' ) {
		$single = $single?$single:$this->get_label('singular');
		$plural = $plural?$plural:$this->get_label('plural');
		$labels = array(
			'name' => $plural,
			'singular_name' => $single,
			'search_items' => sprintf(__('Search %s'),$plural),
			'popular_items' => sprintf(__('Popular %s'),$plural),
			'all_items' => sprintf(__('All %s'),$plural),
			'parent_item' => sprintf(__('Parent %s'),$single),
			'parent_item_colon' => sprintf(__('Parent %s:'),$single),
			'edit_item' => sprintf(__('Edit %s'),$single),
			'update_item' => sprintf(__('Update %s'),$single),
			'add_new_item' => sprintf(__('Add New %s'),$single),
			'new_item_name' => sprintf(__('New %s Name'),$single),
			'separate_items_with_commas' => sprintf(__('Separate %s with commas'),$plural),
			'add_or_remove_items' => sprintf(__('Add or remove %s'),$plural),
			'choose_from_most_used' => sprintf(__('Choose from the most used %s'),$plural),
			'menu_name' => $this->get_menu_label()
		);

		$labels = apply_filters('mvc_custom_taxonomy_labels', $labels, $this->taxonomy);
		$labels = apply_filters('mvc_custom_taxonomy_labels_'.$this->taxonomy, $labels);

		return $labels;
	}


    /**
	 * Get Menu Label
	 * 
	 * Returns the set menu label or the plural label if not set
	 * 
	 * @since 3.6.14
	 * 
	 * @uses $this->label_menu
	 * @uses $this->label_plural
	 * 
	 * @return string
	 * 
	 */
	public function get_menu_label() {
		if( !$this->label_menu ){
			$this->label_menu = $this->label_plural;
		}
		return $this->label_menu;
	}
	
	/**
	 * Set Menu Label
	 * 
	 * Sets the label for the menu
	 * 
	 * @since 3.6.14
	 * 
	 * @param string $label - the label to set it to
	 * 
	 * @uses $this->label_menu
	 * @uses $this->label_plural
	 * 
	 * @return void
	 * 
	 */
	public function set_menu_label( $label ) {
		$this->label_menu = $label;
	}


	/**
	 * Set Label
	 * 
	 * Sets the singular and plural labels automatically
	 * 
	 * @since 3.6.14
	 * 
	 * @return void
	 * @uses $this->label_singular
	 * @uses $this->label_plural
	 * 
	 */
	public function set_label( $singular = '', $plural = '' ) {
		if ( !$singular ) {
			$singular = ucwords( str_replace( '_', ' ', $this->taxonomy) );
		}
		if ( !$plural ) {
			if( substr($singular, -1) == 'y' ){
				$plural = substr($singular, 0, -1).'ies'; 
			} else {
				$plural = $singular.'s'; 
			}
		}
		
		$this->label_singular = $singular;
		$this->label_plural = $plural;
	}


	public function get_label( $quantity = 'singular' ) {
		switch ( $quantity ) {
			case 'plural':
				if ( !$this->label_plural ) {
					$this->set_label($this->label_singular);
				}
				return $this->label_plural;
			default:
				if ( !$this->label_singular ) {
					$this->set_label();
				}
				return $this->label_singular;
		}
	}

	/**
	 * Return the slug of the supertype
	 *
	 * @return string supertype slug
	 */
	public function get_slug() {
		if( empty( $this->slug ) ){
			$this->slug = strtolower( str_replace( ' ', '-', $this->taxonomy ) );
		}
		return $this->slug;
	}
}
