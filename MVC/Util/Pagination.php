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

	const PER_PAGE = 8;

	private $items;

	private $page;

	public function __construct( $items, $current_page ){
		$this->items = $items;
		$this->page = $current_page;
	}

	public function get_total_pages(){
		$count = count( $this->items );
		if( $count == 0 ){
			return 0;
		}

		return ceil( $count / self::PER_PAGE );
	}


	/**
	 * Returns the items that belong on the current
	 * page only
	 *
	 * @return array
	 */
	public function get_this_pages_items(){
		$page   = $this->page;
		$bottom = ( $page - 1 ) * self::PER_PAGE + 1;

		$items = array_slice( $this->items, $bottom, self::PER_PAGE );

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
	public function get_pagination_output(){
		$page  = $this->page;
		$total = $this->get_total_pages();

		if( $page < 3 ){
			$bottom = 1;
		} elseif( $page == $total ){
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

		?>
		<ul class="drive-pagination">
			<?php
			if( $page != "1" ){
				?>
				<li>
					<a data-page="1">
						&laquo;
					</a>
				</li>
				<li>
					<a data-page="<?php echo $page - 1; ?>">
						&lt;
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
						<?php _e( $bottom, 'edspire' ); ?>
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
					<?php _e( $total, 'edspire' ); ?>
				</a>
			</li>
			<?php
			if( $page != $total ){
				?>
				<li>
					<a data-page="<?php echo $page + 1; ?>">
						&gt;
					</a>
				</li>
				<li>
					<a data-page="<?php echo $total; ?>">
						&raquo;
					</a>
				</li>
			<?php
			}
			?>
		</ul>
	<?php
	}
}