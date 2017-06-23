<?php

namespace MVC\Util;


/**
 * Mvc Format
 *
 * Formatting for Genesis and Other WordPress outputs
 *
 * @package MvcTheme
 *
 * @uses    add_theme_support('mvc_template');
 *
 */
class Template {

	use \MVC\Traits\Singleton;

	public $sidebar_changed = false;

	private static $body_classes = array();

	/**
	 * Init Theme Adjustments
	 *
	 * Called by bootstrap only to use hooks if theme supports to do so
	 *
	 * @todo Move this stuff to it's own deal
	 *
	 * @static
	 *
	 * @return void
	 */
	public static function init_theme_adjustments(){
		$class = self::instance();

		//Add the class 'first-class' to the first post
		add_filter( 'post_class', array( $class, 'first_post_class' ), 0, 2 );

		//Add the special classes to the nav
		add_filter( 'wp_nav_menu_objects', array( $class, 'menu_classes' ) );

		//Changes the Sidebar for the Blog Pages
		add_action( 'wp', array( $class, 'blog_sidebar' ) );

		//Add a class matching the page name for styling - nice :)
		add_filter( 'body_class', array( $class, 'body_class' ) );

		add_action( 'genesis_before', array( $class, 'outabody_open' ) );
		add_action( 'genesis_after', array( $class, 'outabody_close' ) );
	}


	/**
	 * @deprecated
	 */
	function isBlogPage(){
		_deprecated_function( "isBlogPage", "1.20.16", "use is_posts_page" );
		return $this->is_posts_page();
	}


	/**
	 * Check if we on a a 'post' post type page
	 * For determining if we should have a blog
	 * style layout etc.
	 * Searches:
	 * - blog page template
	 * - post archive
	 * - single post
	 * - category
	 * - date and 'post' post type
	 *
	 * @return bool
	 */
	function is_posts_page(){
		if( is_page_template( 'page_blog.php' ) ||
		    is_post_type_archive( 'post' ) ||
		    is_singular( 'post' ) ||
		    is_category() ||
		    ( is_date() && get_post_type() == 'post' ) ){
			return true;

		} else {
			return false;
		}
	}


	/**
	 * Retrieve the post_id of the page with the page_blog.php template
	 * If none is found use the reading settings value for where
	 * posts are displayed
	 *
	 * Will return 0 if no page is set or end up on home
	 *
	 * @return bool|mixed|void
	 */
	public function get_blog_page(){
		$page_id = \MVC\Util\Cache::get( 'getBlogPage', \MVC\Util\Cache::FLUSH_ON_SAVE_POST_GROUP );
		if( $page_id !== false ){
			return $page_id;
		}

		$args = array(
			'post_type'   => 'page',
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'page_blog.php',
			'numberposts' => 1,
			'fields'      => 'ids'
		);

		$pages = get_posts( $args );
		if( !empty( $pages[ 0 ] ) ){
			$page_id = $pages[ 0 ];
		} else {
			if( empty( $blog_page_id ) ){
				$front_page_displays = get_option( 'show_on_front' );
				if( 'page' == $front_page_displays ){
					$page_id = get_option( 'page_for_posts' );
				}
			}
		}

		//for cache
		if( empty( $page_id ) ){
			$page_id = 0;
		}

		\MVC\Util\Cache::set( 'getBlogPage', $page_id, \MVC\Util\Cache::FLUSH_ON_SAVE_POST_GROUP );

		return $page_id;

	}


	/**
	 * @deprecated
	 */
	public function getBlogPage(){
		_deprecated_function( 'getBlogPage', "1.19.16", "use get_blog_page()" );
		return $this->get_blog_page();
	}


	/**
	 * Checks to see if this page has a parent or is child of a specified page
	 *
	 * @param mixed $page can be a page name or an id
	 *
	 * @since 7/24/12
	 */
	function is_subpage( $page = null ){
		global $post;
		// does it have a parent?
		if( !isset( $post->post_parent ) OR $post->post_parent <= 0 ){
			return false;
		}
		// is there something to check against?
		if( !isset( $page ) ){
			// yup this is a sub-page
			return true;
		} else {
			// if $page is an integer then its a simple check
			if( is_int( $page ) ){
				// check
				if( $post->post_parent == $page ){
					return true;
				}
			} else if( is_string( $page ) ){
				// get ancestors
				$parent = get_ancestors( $post->ID, 'page' );
				// does it have ancestors?
				if( empty( $parent ) ){
					return false;
				}
				// get the first ancestor
				$parent = get_post( $parent[ 0 ] );
				// compare the post_name
				if( $parent->post_name == $page ){
					return true;
				}
			}

			return false;
		}
	}


	/**
	 * Returns the Current Templates Name
	 *
	 * @since 2.3.0
	 *
	 * @since 10.18.13
	 *
	 * @return string | false in in admin
	 */
	function getPageTemplateName(){
		if( is_admin() ){
			return false;
		}
		$post = get_post();
		if( empty( $post ) ){
			return;
		}

		return str_replace( '.php', '', get_post_meta( $post->ID, '_wp_page_template', true ) );
	}


	/**
	 * Retrieve the widgets instance data and id
	 * optionaly specify the widgets Area name to only retrieve those
	 *
	 * @since 3.8.0
	 * @uses  Must be Called after the functions.php file has loaded or non default sidebars do not exist yet - unless you don't care about non default ones
	 *
	 * @param array $args = array()
	 *
	 * Available Args:
	 *                  'string [sidebar_name] - Name of Widget Area'
	 *                  'string [widget_name] - Name of Registered Widget'
	 *                  'bool [inactive_widgets] - to include inactive widgets - only works when not specifing $sidebar_name'
	 *                  'bool [object_data] - to return full object including class information'
	 *                  'bool [include_output'] - to include the output of the widgets'
	 *
	 * @return array
	 */
	function getWidgetData( $args = array() ){
		$defaults = array(
			'sidebar_name'     => false,
			'widget_name'      => false,
			'inactive_widgets' => false,
			'object_data'      => false,
			'include_output'   => false
		);
		$args     = wp_parse_args( $args, $defaults );

		extract( $args );

		global $wp_registered_sidebars, $wp_registered_widgets;

		// Holds the final data to return
		$output = array();
		if( $sidebar_name ){
			// Loop over all of the registered sidebars looking for the one with the same name as $sidebar_name
			$sibebar_id = false;
			foreach( $wp_registered_sidebars as $sidebar ){
				if( $sidebar[ 'name' ] == $sidebar_name ){
					// We now have the Sidebar ID, we can stop our loop and continue.
					$sidebar_id = $sidebar[ 'id' ];
					break;
				}
			}

			if( !$sidebar_id ){
				// There is no sidebar registered with the name provided.
				return $output;
			}
			$sidebars_widgets = wp_get_sidebars_widgets();
			$widget_ids       = $sidebars_widgets[ $sidebar_id ];

		} else {
			$sidebars_widgets = wp_get_sidebars_widgets();

			$widget_ids = array();
			foreach( $sidebars_widgets as $sidebar_id => $widgets ){
				if( $sidebar_id != 'wp_inactive_widgets' || $inactive_widgets ){
					$widget_ids = array_merge( $widget_ids, $widgets );
				}
			}
		}

		if( !$widget_ids ){
			// Without proper widget_ids we can't continue.
			return array();
		}

		// Loop over each widget_id so we can fetch the data out of the wp_options table.
		foreach( $widget_ids as $id ){
			if( $widget_name && $wp_registered_widgets[ $id ][ 'name' ] != $widget_name ){
				continue;
			}
			// The name of the option in the database is the name of the widget class.
			$option_name = $wp_registered_widgets[ $id ][ 'callback' ][ 0 ]->option_name;

			//If selected to include the output of the widget
			if( $include_output ){
				$params = array_merge( array(
					array_merge( $sidebar,
						array(
							'widget_id'   => $id,
							'widget_name' => $wp_registered_widgets[ $id ][ 'name' ]
						) )
				),
					(array) $wp_registered_widgets[ $id ][ 'params' ]
				);
				// Substitute HTML id and class attributes into before_widget
				$classname_ = '';
				foreach( (array) $wp_registered_widgets[ $id ][ 'classname' ] as $cn ){
					if( is_string( $cn ) ){
						$classname_ .= '_' . $cn;
					} elseif( is_object( $cn ) ) {
						$classname_ .= '_' . get_class( $cn );
					}
				}
				$classname_ = ltrim( $classname_, '_' );
				$classname_ .= ' from-get-widget-data';
				$params[ 0 ][ 'before_widget' ] = sprintf( $params[ 0 ][ 'before_widget' ], $id, $classname_ );

				$callback = $wp_registered_widgets[ $id ][ 'callback' ];

				if( is_callable( $callback ) ){
					ob_start();
					call_user_func_array( $callback, $params );
					$output[ $id ][ 'output' ] = apply_filters( 'mvc_dynamic_sidebar_output', ob_get_clean(), $callback, $params, $id );
				} else {
					$output[ $id ][ 'output' ] = false;
				}
			}

			// Widget data is stored as an associative array. To get the right data we need to get the right key which is stored in $wp_registered_widgets
			$key                     = $wp_registered_widgets[ $id ][ 'params' ][ 0 ][ 'number' ];
			$widget_data             = get_option( $option_name );
			$output[ $id ][ 'data' ] = (object) $widget_data[ $key ];
			$output[ $id ][ 'name' ] = $wp_registered_widgets[ $id ][ 'name' ];
			if( $object_data ){
				$output[ $id ][ 'object_data' ] = $wp_registered_widgets[ $id ];
			}

		}

		return $output;
	}


	/**
	 * Append a class to the body tag.
	 * Must be called before the body_class filter is called.
	 *
	 * @param string $class
	 *
	 * @return void
	 */
	public function add_body_class( $class ){
		if( !in_array( $class, self::$body_classes ) ){
			self::$body_classes[] = $class;
		}
	}


	/**
	 * Called on the body_class filter to
	 * add some classes related to the current section, post, etc.
	 *
	 * @param array $classes
	 *
	 * @return array
	 *
	 */
	public function body_class( $classes ){
		global $post;

		//deprecate way of doing things
		if( is_string( $classes ) ){
			_deprecated_function( "body_class", "1.20.16", "use add_body_class" );
			$this->add_body_class( $classes );
			return;
		}

		if( !empty( $post->ID ) ){
			if( has_post_thumbnail() ){
				$classes[ ] = 'has-thumbnail';
			}
		}

		if( $this->is_posts_page() ){
			$classes[ ] = 'blog-page'; //deprecated
			$classes[ ] = 'posts-page';
		}

		//Add an archive class for the blog template
		if( $this->getPageTemplateName() == 'page_blog' ){
			$classes[ ] = 'archive';
		}

		//Add a class for sub pages
		if( !is_home() && ( strpos( $this->getPageTemplateName(), 'home' ) === false ) ){
			$classes[ ] = 'sub';
		}

		//Add the page title as a class
		if( !empty( $post ) ){
			$classes[ ] = mvc_string()->slug_format_human( $post->post_title );
		}

		if( !empty( self::$body_classes ) ){
			$classes = array_merge( $classes, self::$body_classes );
		}

		return $classes;
	}


	/**
	 * Removes the post meta and info from the output
	 *
	 * @since 3.5.13
	 * @uses  can be called anywhere before the loop
	 */
	function removePostData(){
		add_action( 'genesis_before_loop', array( $this, 'removePostDataHooks' ) );
	}


	/**
	 *
	 * Unhooks the genesis_post_info and genesis_post_meta
	 *
	 * @uses used by self::removePostData()
	 *
	 * @uses could be called wherever you like as well but used by removePostData
	 *
	 * @return void
	 */
	function removePostDataHooks(){
		remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
		remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
		remove_action( 'genesis_before_post_content', 'genesis_post_info' );
		remove_action( 'genesis_after_post_content', 'genesis_post_meta' );
	}


	/**
	 * Move Genesis Meta Boxes To Bottom
	 *
	 * Move the genesis layout and seo to bottom of post edit screen
	 *
	 *
	 * @return void
	 */
	function move_genesis_meta_boxes_to_bottom(){
		//Move the genesis meta box below our special ones
		if( function_exists( 'genesis_add_inpost_layout_box' ) ){
			remove_action( 'admin_menu', 'genesis_add_inpost_layout_box' );
			add_action( 'do_meta_boxes', 'genesis_add_inpost_layout_box' );
			remove_action( 'admin_menu', 'genesis_add_inpost_seo_box' );
			add_action( 'do_meta_boxes', 'genesis_add_inpost_seo_box' );
		}

	}


	/**
	 * Change the sidebar to another widget area
	 *
	 * @param string $sidebar - Name of widget area
	 *
	 * @see  must be called before the 'genesis_after_content' hook is run
	 * @see  Will not work if not using Genesis
	 */
    function changeSidebar( $sidebar ){
        $this->sidebar_changed = true;
        if( function_exists( 'genesis_site_layout' ) && genesis_site_layout( 0 ) != 'full-width-content' ){
            remove_action( 'genesis_after_content', 'genesis_get_sidebar' );
            add_action( 'genesis_after_content', array( mvc(), 'sidebar_' . $sidebar ) );
        }
    }


	/**
	 * Change Layout
	 *
	 * Changes the pages layout
	 *
	 * @uses    call this anytime before the get_head() hook
	 * @uses    - defaults to 'full-width-content'
	 *
	 * @param string $layout - desired layout
	 *                       -  'full-width-content'
	 *                       -  'content-sidebar'
	 *                       -  'sidebar-content'
	 *                       -  'content-sidebar-sidebar'
	 *                       -  'sidebar-sidebar-content'
	 *                       -  'sidebar-content-sidebar'
	 *
	 * @example may be used in the single() or before() hooks etc
	 *
	 * @return void
	 */
	function change_layout( $layout = 'full-width-content' ){
		$this->layout = $layout;
		add_filter( 'genesis_site_layout', array( mvc(), 'return_' . $layout ) );
	}


	/**
	 * Outabody Open
	 *
	 * Open up the bg divs
	 *
	 * @return void
	 */
	function outabody_open(){
		?>
		<div id="outabody">
		<div id="outabody2">
		<div id="outabody3">
	<?php
	}


	/**
	 * Outabody Close
	 *
	 * Close the bg divs
	 *
	 * @return void
	 */
	function outabody_close(){
		?>

		</div>
		</div>
		</div>
	<?php
	}


	/**
	 * Outputs a Sidebar for Page or Posts for Whatever
	 * Use widgetArea for a standard widget and this for a true sidebar
	 *
	 * @param string $name of widget area
	 * @param        bool  [$echo] defaults to true
	 *
	 * @uses  genesis_markup() and mvc_dynamic_sidebar() - if not using genesis will just display sidebar
	 *
	 * @return string
	 */
	function sidebar( $name, $echo = true ){

		ob_start();
		//we are not rocking genesis
		if( !function_exists( 'genesis_markup' ) ){
			mvc_dynamic_sidebar( $name );
		} else {

			$class = mvc_string()->slug_format_human( $name );

			genesis_markup( array(
				'html5'   => '<aside class="sidebar sidebar-primary widget-area ' . $class . '">',
				'xhtml'   => '<div id="sidebar" class="sidebar widget-area ' . $class . '">',
				'context' => 'sidebar-primary',
			) );
			do_action( 'genesis_before_sidebar_widget_area' );
			mvc_dynamic_sidebar( $name );
			do_action( 'genesis_after_sidebar_widget_area' );

			genesis_markup( array(
				'html5' => '</aside>',
				'xhtml' => '</div>',
			) );
		}

		$output = ob_get_clean();

		if( !$echo ){
			return $output;
		}

		echo $output;

	}


	/**
	 * Outputs a Widget Area By Name
	 * Use sidebar for a true sidebar and this for a standard widget area
	 *
	 * @param string $name of widget area
	 * @param bool   $echo defaults to true
	 *
	 * @since 4.16.13
	 */
	function widgetArea( $name, $echo = true ){
		$output = '<div id="' . mvc_string()->slug_format_human( $name ) . '" class="widget-area">';
		$output .= mvc_dynamic_sidebar( $name, false );
		$output .= '</div>';

		if( !$echo ){
			return $output;
		}

		echo $output;

	}


	/**
	 * Echos the meta viewpoint for Phones
	 *
	 * @since 2.15.13
	 * @uses  call as is, will echo for you - probably in genesis_meta call
	 * @uses  automatically added when mobile_reponsiveness is turned on
	 */
	function metaViewPoint(){
		echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
	}


	/**
	 * Changes all the sidebar on the Blog type "post" pages if a widget called "Blog Sidebar" exists
	 *
	 * @uses  create a widget area called 'Blog Sidebar' this will do the rest
	 * @uses  called by __construct();
	 * @since 1.7.14
	 */
	function blog_sidebar(){
		if( $this->sidebar_changed ){
			return;
		}

		if( !function_exists( 'genesis_site_layout' ) ){
			return;
		}

		if( genesis_site_layout() == 'full-width-content' ){
			return;
		}

		if( mvc_dynamic_sidebar( 'Blog Sidebar', false ) ){
			if( $this->is_posts_page() ){
				remove_action( 'genesis_after_content', 'genesis_get_sidebar' );
				add_action( 'genesis_after_content', array( mvc(), 'sidebar_Blog_Sidebar' ) );
			}
		}
	}


	/**
	 * Menu Classes
	 *
	 * Adds a class to the first and last item in every menu
	 *
	 * @param array $items the menu Items
	 *
	 * @return array
	 * @uses called by Bootstrap::__construct()
	 */
	function menu_classes( $items ){
		$top_count = 1;
		while( next( $items ) ){
			$k = key( $items );
			if( $items[ $k ]->menu_item_parent == 0 ){
				$top_count ++;
				$items[ $k ]->classes[ ] = 'item-count-' . $top_count;
				//keep track of last menu item by setting it on each one
				$last_menu_item = $k;
			}
		}
		if( !empty( $last_menu_item ) ){
			$items[ $last_menu_item ]->classes[ ] = 'last-menu-item';
		}
		reset( $items )->classes[ ] = 'first-menu-item';

		return $items;
	}


	/**
	 * Add the 'first-post' class to the first post on any page
	 * * Also adds and item-count class
	 *
	 * @param array $classes existing classes for post
	 *
	 * @return array
	 * @uses  called by __construct()
	 * @since 8.1.13
	 */
	function first_post_class( $classes ){
		global $post, $posts, $wp_query;
        if( !is_array( $posts ) ){
            return $classes;
        }
		if( ( $wp_query->current_post === 0 ) || ( $post == $posts[ 0 ] ) ){
			$classes[ ] = 'first-post';
		}

		if( has_post_thumbnail() ){
			$classes[ ] = 'has-thumbnail';
		}

		if( !empty( $posts ) ){
			$classes[] = sprintf( 'item-count-%s', array_search( $post, $posts ) + 1 );
		}

		return $classes;
	}
}
