<?php

namespace MVC;

/**
 * Post_List_Column
 *
 * @author  Mat Lipe
 * @since   4/1/2016
 *
 * @package MVC
 */
abstract class Post_List_Column {
	protected $column_label;

	protected $column_slug;

	protected $post_types = array();

	protected $filters = array();


	/**
	 * Renders the output of the column in each row
	 * of the posts list
	 *
	 * @param string $column
	 * @param int    $post_id
	 *
	 * @return void
	 */
	abstract function render_column( $column, $post_id );


	/**
	 * Post_List_Column constructor.
	 * 
	 *
	 * @param string  $column_label
	 * @param array $post_types
	 */
	public function __construct( $column_label, $post_types = array( 'post' ) ){
		$this->column_label = $column_label;
		$this->column_slug  = sanitize_title_with_dashes( $this->column_label );
		$this->post_types   = $post_types;
		$this->hooks();
	}


	private function hooks(){
		global $pagenow;
		if( is_admin() && $pagenow == 'edit.php' ){
			add_action( 'restrict_manage_posts', array( $this, 'render_filter' ) );
			add_action( 'parse_query', array( $this, 'maybe_filter_query' ) );
			foreach( $this->post_types as $post_type ){
				add_action( "manage_{$post_type}_posts_columns", array( $this, 'add_column' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_column' ), 10, 2 );

			}
		}
	}


	/**
	 * Add Column to Post List
	 *
	 * @internal
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_column( $columns ){
		$columns[ $this->column_slug ] = $this->column_label;

		return $columns;
	}


	/**
	 * Override to filter the query being used in the posts list
	 * Will provide the selected value and the query
	 * to filter
	 *
	 * @notice This method must be overridden if using filters
	 *
	 * @param mixed     $value
	 * @param \WP_Query $query
	 *
	 * @throws \Exception
	 * @return \WP_Query|\Throwable
	 */
	public function filter_query( $value, \WP_Query $query ){
		throw new \Exception( 'You must override the Post_List_Column::filter_query() method if you are using Post_List_Column::set_filters()' );
	}


	/**
	 * If a value is selected from this drop-down
	 * Call $this->filter_query to adjust query as needed
	 *
	 * @notice Only called if we are using $this->set_filters() to handle rendering
	 * @see    $this->set_filters();
	 *
	 * @internal
	 *
	 * @param \WP_Query $query
	 *
	 * @return void
	 */
	public function maybe_filter_query( \WP_Query $query ){
		if( empty( $query->query_vars[ 'post_type' ] ) || !in_array( $query->query_vars[ 'post_type' ], $this->post_types ) ){
			return;
		}

		if( !empty( $this->filters[ 'name' ] ) ){
			$selected = empty( $_REQUEST[ $this->filters[ 'name' ] ] ) ? false : $_REQUEST[ $this->filters[ 'name' ] ];
			if( !empty( $selected ) ){
				$query = $this->filter_query( $selected, $query );
			}
		}
	}


	/**
	 * Outputs the drop-down select above the posts list
	 * Automatically selects the current one.
	 *
	 * @notice Will not output if self::set_filters() is not called previously
	 * @see    self::set_filters()
	 *
	 * @return void
	 */
	public function render_filter(){
		global $typenow;
		if( empty( $this->filters ) || !in_array( $typenow, $this->post_types ) ){
			return;
		}

		$args     = $this->filters;
		$selected = empty( $_REQUEST[ $args[ 'name' ] ] ) ? false : $_REQUEST[ $args[ 'name' ] ];

		?>
		<select name="<?php echo $args[ 'name' ]; ?>" id="<?php echo $args[ 'name' ]; ?>-select" class="postform" title="<?php echo $args[ 'show_all' ]; ?>">
			<?php
			if( !empty( $args[ 'show_all' ] ) ){
				?>
				<option value="0" selected="selected">
					<?php echo $args[ 'show_all' ]; ?>
				</option>
				<?php
			}
			foreach( $args[ 'items' ] as $value => $_label ){
				?>
				<option class="level-0" value="<?php echo $value; ?>" <?php selected( $selected, $value ); ?>>
					<?php echo $_label; ?>
				</option>
				<?php
			}

			?>
		</select>

		<?php

	}


	/**
	 * Setup filters to be used as a drop-down select in the posts
	 * list.
	 * Using this method will cause $this->filter_query to
	 * be called when rendering the post list
	 *
	 * @param array $args {
	 *                    'name' => %name of input%
	 *                    'items => array { %value% => %label% }
	 *                    'show_all' => %All Label%
	 *                    }
	 *
	 * @return void
	 */
	public function set_filters( array $args ){
		$defaults = array(
			'show_all' => false,
			'name'     => __CLASS__,
			'items'    => array(),
		);

		$this->filters = wp_parse_args( $args, $defaults );
	}

}