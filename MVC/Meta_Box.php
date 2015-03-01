<?php
namespace MVC;

/**
 * 
 * Meta Box
 * 
 * The base meta box class. To extend this, implement the
 * render() and save() functions.
 *
 * render() will be called when the meta box needs to be printed
 * in the post editor
 *
 * save() will be called when a post is being saved. No need to
 * worry about validating if the correct post type has been
 * submitted, nonces, and all that. They're already taken care of.
 * 
 * @example extend this class then construct it new %class( %post_type%, %args% );
 * @example mvc_meta_box( %post_type%, %class%, %args% );
 * 
 * @package MVC Theme
 * @namespace MVC
 * 
 */
abstract class Meta_Box {
	const NONCE_ACTION = 'mvc_meta_box_save_post';
	const NONCE_NAME = 'mvc_meta_box_save_post_nonce';

	protected $id;
	protected $title;
	protected $context;
	protected $priority;
	protected $callback_args;
	protected $meta_box_defaults;

	private static $registry = array( );
	
	/**
	 * Is Init
	 * 
	 * Bool to know if we already ran init so we can call internally once
	 * 
	 * @var bool 
	 */
	public static $is_init = false;
	

	/**
	 * Defaults
	 * 
	 * Default class args
	 * 
	 * @var array
	 */
	protected $defaults = array(
		'title'   => '',
		'context'   => 'advanced',
		'priority'   => 'default',
		'callback_args'   => NULL,
		'defaults'   => array(),
	);

	/**
	 * Constructor
	 * 
	 * @param string $post_type
	 * @param array $args = array(
	 * 		'title'   => '',
			'context'   => 'advanced',
			'priority'   => 'default',
			'callback_args'   => NULL,
			'defaults'   => array(),
	 * )
	 * 
	 * @return self()
	 * 
	 */
	public function __construct($post_type, $args = array()) {
		$this->post_type = $post_type;
		
		//only call init the first time this class sees light of day
		if( !self::$is_init ){
			self::init();
		}
		
		if( !empty( $args[ 'id' ] ) ) {
			$this->id = $args[ 'id' ];
		} else {
			$this->id = self::build_id( $this->post_type, get_class( $this ) );
		}

		self::$registry[ $post_type ][ $this->id ] = $this;

		if( !$this->defaults[ 'title' ] ) {
			$this->defaults[ 'title' ] = $this->id;
		}

		$args = wp_parse_args( $args, $this->defaults );
		
		$this->title = $args[ 'title' ];
		$this->context = $args[ 'context' ];
		$this->priority = $args[ 'priority' ];
		$this->callback_args = $args[ 'callback_args' ];
		$this->meta_box_defaults = $args[ 'defaults' ];

		add_action( 'add_meta_boxes_' . $this->post_type, array(
			$this,
			'register_meta_box'
		), 10, 0 );
	}


	/**
	 * @abstract
	 * @param \WP_Post $post The post being edited
	 * @return void
	 */
	abstract public function render($post);

	/**
	 * @abstract
	 * @param int $post_id The ID of the post being saved
	 * @param object $post The post being saved
	 * @return void
	 */
	abstract protected function save($post_id, $post);
	
	
   /**
	 * Init
	 * 
	 * Add the actions to handle output and saving meta box data
	 * 
	 * @example Run this before anything else
	 * 
	 * @return void
	 */
	public static function init() {
		self::$is_init = true;
		add_action( 'post_submitbox_misc_actions', array(
			__CLASS__,
			'display_nonce'
		) );
		add_action( 'save_post', array(
			__CLASS__,
			'save_meta_boxes'
		), 10, 2 );
	}
	


	/**
	 * Register the meta box for its post type
	 *
	 * @action 'add_meta_boxes_' . $post_type
	 * @return void
	 */
	public function register_meta_box() {
		add_meta_box( $this->get_id(), $this->get_title(), array(
			$this,
			'render'
		), $this->post_type, $this->get_context(), $this->get_priority(), $this->get_callback_args() );
	}

	/**
	 * Save Meta Field
	 *
	 * Quick and dirty save a field what was sent via $_POST[ %field% ]
	 * Can send array if desired
	 * Will save the meta field to null if empty
	 *
	 *
	 * @param int $post_id
	 * @param string|array $field
	 *
	 * @return void
	 */
	public function save_meta_field( $post_id, $field ){

		foreach( (array)$field as $this_field ){
			if( ! empty( $_POST[ $this_field ] ) ){
				update_post_meta( $post_id, $this_field, $_POST[ $this_field] );
			} else {
				update_post_meta( $post_id, $this_field, null );
			}
		}
	}


	/**
	 * extractable_meta_fields
	 *
	 * Retrieve an array of key value pairs of the posts meta fields
	 * Can be turned into variables with extract()
	 *
	 * @param int $post_id
	 * @param string|array $field
	 *
	 * @return mixed
	 */
	public function extractable_meta_fields( $post_id, $field ){
		$meta = array();

		foreach( (array)$field as $this_field ){
			$meta[ $this_field ] = get_post_meta( $post_id, $this_field, true );
		}

		return $meta;
	}


	/**
	 * Return the ID of the meta box
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}


	/**
	 * Return the translated title of the meta box
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}


	/**
	 * Return the context in which to display the meta box
	 *
	 * @return string
	 */
	public function get_context() {
		return $this->context;
	}


	/**
	 * Return the priority in which to display the meta box
	 *
	 * @return string
	 */
	public function get_priority() {
		return $this->priority;
	}


	/**
	 * Return the defaults set in the network admin
	 *
	 * @return array
	 */
	public function get_defaults() {
		return $this->meta_box_defaults;
	}


	/**
	 * Return arguments to pass to the meta box
	 *
	 * @return array|null
	 */
	public function get_callback_args() {
		return $this->callback_args;
	}


	/**
	 * Set the arguments to pass to the meta box
	 *
	 * @param array|null $args
	 * @return void
	 */
	public function set_callback_args($args) {
		$this->callback_args = $args;
	}


	/**
	 * @static
	 * @param string $post_type
	 * @param string $class
	 * @return string A unique identifier for this meta box
	 */
	protected static function build_id($post_type, $class) {
		$id = $post_type . '-' . $class;
		$append = 0;
		if( isset( self::$registry[ $post_type ][ ($id . ($append ? '-' . $append : '')) ] ) ) {
			$append++;
		}
		$id = $id . ($append ? '-' . $append : '');
		return $id;
	}
	
	



	/**
	 * Put our nonce in the Publish box, so we can share it
	 * across all meta boxes
	 *
	 * @return void
	 */
	public static function display_nonce() {
		global $post;
		if( !empty( self::$registry[ $post->post_type ] ) ) {
			wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		}
	}


	/**
	 * Save the meta boxes for this post type
	 *
	 * @param int $post_id The ID of the post being saved
	 * @param object $post The post being saved
	 * @return void
	 */
	public static function save_meta_boxes($post_id, $post) {
		if( !$post_type = self::should_meta_boxes_be_saved( $post_id, $post ) ) {
			return;
		}
		if( empty( self::$registry[ $post_type ] ) ) {
			return;
		}

		global $wp_filter;
		$current = key( $wp_filter[ 'save_post' ] );

		foreach( self::$registry[$post_type] as $meta_box ) {
			/** @var $meta_box Meta_Box */
			$meta_box->save( $post_id, $post );
		}

		/*
		 * if any of the meta boxes creates/updates a different
		 * post, we'll end up leaving the $wp_filter['save_post']
		 * array in an incorrect state
		 * see http://xplus3.net/2011/08/18/wordpress-action-nesting/
		 */
		if( key( $wp_filter[ 'save_post' ] ) != $current ) {
			reset( $wp_filter[ 'save_post' ] );
			foreach( array_keys($wp_filter['save_post']) as $key ) {
				if( $key == $current ) {
					break;
				}
				next( $wp_filter[ 'save_post' ] );
			}
		}
	}


	/**
	 * Make sure this is a save_post where we actually want to update the meta
	 *
	 * @param int $post_id
	 * @param object $post
	 * @return bool
	 */
	protected static function should_meta_boxes_be_saved($post_id, $post) {
		// make sure this is a valid submission
		if( !isset( $_POST[ self::NONCE_NAME ] ) || !wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::NONCE_ACTION ) ) {
			return FALSE;
		}

		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET[ 'bulk_edit' ] ) || wp_is_post_revision( $post_id ) ) {
			return FALSE;
		}

		// looks like the answer is Yes
		return $post->post_type;
	}


	/**
	 * Get a metabox of the given class for the post type
	 *
	 * If more than one metabox of the same class registered
	 * with the same post type, the first to register will be returned
	 *
	 * @static
	 * @param string $post_type
	 * @param string $class
	 * @return Meta_Box|null
	 */
	public static function get_meta_box($post_type, $class) {
		if( !isset( self::$registry[ $post_type ] ) ) {
			return NULL;
		}
		foreach( self::$registry[$post_type] as $meta_box ) {
			if( get_class( $meta_box ) == $class ) {
				return $meta_box;
			}
		}
		return NULL;
	}


	/**
	 * Get the metabox with the given ID
	 *
	 * @static
	 * @param string $post_type
	 * @param string $id
	 * @return Meta_Box|null
	 */
	public static function get_meta_box_by_id($post_type, $id) {
		if( isset( self::$registry[ $post_type ][ $id ] ) ) {
			return self::$registry[ $post_type ][ $id ];
		}
		return NULL;
	}


	/**
	 * @static
	 * @param string $post_type
	 * @param string $class
	 * @return bool Whether a meta box with the given class has been
	 *              registered for the given post type
	 */
	public static function has_meta_box($post_type, $class) {
		$metabox = self::get_meta_box( $post_type, $class );
		if( $metabox == NULL ) {
			return FALSE;
		}
		return TRUE;
	}

}
