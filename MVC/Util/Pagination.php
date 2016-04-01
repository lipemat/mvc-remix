<?php

namespace MVC\Util;

/**
 * Pagination
 *
 * I expect you to have already sort the items before you
 * give them to me. I will generate a html list designed to
 * be used with ajax as well as the items for this current page.
 *
 * @author  Mat Lipe
 * @since   7/8/2015
 *
 * @package EdSpireDocImporter
 */
class Pagination {

	private $per_page = 10;

	private $items;

	/**
	 * wp_query
	 *
	 * @var \WP_Query|null
	 */
	private $wp_query = null;

	private $page;


	/**
	 * Send me some items and i'll create the pagination.
	 * If no wp_query is used I will split the list and return the items within that range
	 * If a wp_query is used, I will return the same items you sent me
	 *
	 * @param array            $items     - either the full list of items, or this page's posts when using a wp_query
	 * @param \WP_Query|false [$wp_query] - send a wp_query to use that for handling calculations instead of an independent list
	 * @param       int       [$per_page] - defaults to 10 ( ignored if using a $wp_query )
	 */
	public function __construct( $items, \WP_Query $wp_query = null, $per_page = 10 ){
		$this->items    = $items;
		$this->wp_query = $wp_query;

		if( $wp_query ){
			$this->per_page = $wp_query->get( 'posts_per_page' );
			$page = $this->wp_query->is_paged() ? $wp_query->get( 'paged' ) : 1;
		} else {
			$this->per_page = $per_page;
			$page = empty( $_REQUEST[ 'page' ] ) ? 1 : $_REQUEST[ 'page' ];
		}
		$this->page = $page;
	}


	public function get_total_pages(){
		if( $this->wp_query ){
			$count = $this->wp_query->found_posts;
		} else {
			$count = count( $this->items );
		}
		if( $count == 0 ){
			return 0;
		}

		return ceil( $count / $this->per_page );
	}


	/**
	 * Returns the items that belong on the current
	 * page only
	 *
	 * @return array
	 */
	public function get_this_pages_items(){
		//wp_query already gave us this page's items in the first place
		if( $this->wp_query ){
			return $this->items;
		}
		$page   = $this->page;
		$bottom = ( $page - 1 ) * $this->per_page;

		$items = array_slice( $this->items, $bottom, $this->per_page );

		return $items;
	}


	/**
	 * Generates the html for the pagination
	 * <a data-page="%page%">
	 *
	 * Let's do the numbers so that only 5 pages show at a time (<< < 1 2 3 4 5 .. 20 > >>) avoiding a long set of numbers]
	 *
	 * @return void
	 */
	public function render_pagination(){
		$page  = $this->page;
		$total = $this->get_total_pages();

		if( $total < 2 ){
			return;
		}

		if( $page < 3 ){
			$bottom = 1;
		} elseif( $page == $total ) {
			$bottom = max( 1, $page - 5 );
		} elseif( $page + 3 > $total ) {
			$bottom = max( 1, $page - 4 );
		} else {
			$bottom = $page - 2;
		}

		if( $page > ( $total - 3 ) ){
			$top = $total - 1;
		} else {
			$top = $bottom + 4;
		}

		if( $this->wp_query ){
			$this->link_html( $page, $total, $top, $bottom );
		} else {
			$this->ajax_html( $page, $total, $top, $bottom );
		}
	}


	/**
	 * Generates the html based on links /page/%number%
	 * Used by standard wp queries.
	 *
	 * Used when we do have a wp_query
	 *
	 * @param $page
	 * @param $total
	 * @param $top
	 * @param $bottom
	 *
	 * @return void
	 */
	private function link_html( $page, $total, $top, $bottom ){

		get_next_posts_link()
		?>
		<ul class="navigation">
			<?php
			if( $page != "1" ){
				?>
				<li>
					<a href="<?php echo get_pagenum_link( 1 ); ?>">
						<?php echo apply_filters( 'mvc_paginate_double_back', '&laquo' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo get_pagenum_link( $page - 1 ); ?>">
						<?php echo apply_filters( 'mvc_paginate_back', '&lt' ); ?>
					</a>
				</li>
				<?php
			}

			while( $bottom <= $top ){
				$class = '';
				if( $bottom == $page ){
					$class = ' class="current"';
				}
				?>
				<li>
					<a href="<?php echo get_pagenum_link( $bottom ); ?>"<?php echo $class; ?>>
						<?php _e( $bottom ); ?>
					</a>
				</li>
				<?php
				$bottom ++;
			}

			?>
			<li>
				...
			</li>
			<li>
				<a href="<?php echo get_pagenum_link( $total ); ?>">
					<?php _e( $total ); ?>
				</a>
			</li>
			<?php
			if( $page != $total ){
				?>
				<li>
					<a href="<?php echo get_pagenum_link( $page + 1 ); ?>">
						<?php echo apply_filters( 'mvc_paginate_next', '&gt;' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo get_pagenum_link( $total ); ?>">
						<?php echo apply_filters( 'mvc_paginate_double_next', '&raquo;' ); ?>
					</a>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}


	/**
	 * Generate the ajax driven pagination
	 * Used when we don't have a wp_query
	 *
	 * @param $page
	 * @param $total
	 * @param $top
	 * @param $bottom
	 *
	 * @return void
	 */
	private function ajax_html( $page, $total, $top, $bottom ){
		?>
		<ul class="pagination navigation">
			<?php
			if( $page != "1" ){
				?>
				<li>
					<a data-page="1">
						<?php echo apply_filters( 'mvc_paginate_double_back', '&laquo' ); ?>
					</a>
				</li>
				<li>
					<a data-page="<?php echo $page - 1; ?>">
						<?php echo apply_filters( 'mvc_paginate_back', '&lt' ); ?>
					</a>
				</li>
				<?php
			}

			while( $bottom <= $top ){
				$class = '';
				if( $bottom == $page ){
					$class = ' class="current"';
				}
				?>
				<li>
					<a data-page="<?php echo $bottom; ?>"<?php echo $class; ?>>
						<?php _e( $bottom ); ?>
					</a>
				</li>
				<?php
				$bottom ++;
			}

			?>
			<li>
				...
			</li>
			<li>
				<a data-page="<?php echo $total; ?>">
					<?php _e( $total ); ?>
				</a>
			</li>
			<?php
			if( $page != $total ){
				?>
				<li>
					<a data-page="<?php echo $page + 1; ?>">
						<?php echo apply_filters( 'mvc_paginate_next', '&gt;' ); ?>
					</a>
				</li>
				<li>
					<a data-page="<?php echo $total; ?>">
						<?php echo apply_filters( 'mvc_paginate_double_next', '&raquo;' ); ?>
					</a>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
}