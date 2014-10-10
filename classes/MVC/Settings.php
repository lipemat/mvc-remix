<?php
namespace MVC;

/**
 * Settings
 *
 * Abstract starting point for a settings page
 * Retrieve option from proper location by using $this->get_option()
 *
 * Extend this with another class that does not have a __construct method or call parent::__construct()
 * Implement the abstract methods and set appropriate class vars. This will do the rest.
 *
 * To have a description for a section create a public method %section_slug%_description and
 * it will automatically be used
 *
 * To sanitize a field create a method named %field_slug%_sanitize and it wll automatically
 * receive the value to sanitize
 *
 * To override the default text field create a protected method with same name as option and
 * it will be passed the value of the option as it only argument
 *
 *
 * @package   MVC Theme
 * @namespace MVC
 *
 * @class     Settings
 *
 */
abstract class Settings {

	/**
	 * Title
	 *
	 * Menu and Settings page title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Menu Title
	 *
	 * Menu item label ( defaults to $this->title )
	 *
	 * @var string
	 */
	protected $menu_title;

	/**
	 * Slug
	 *
	 * Settings slug
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Parent menu Slug
	 *
	 * Where should we put this menu
	 *
	 * @uses leave blank for a top level menu
	 *
	 * @var string
	 */
	protected $parent_menu_slug = 'options-general.php';

	/**
	 * Menu Icon
	 *
	 * If you are creating a main level menu use an icon
	 *
	 * @var string Url
	 */
	protected $menu_icon;

	/**
	 * Menu Position
	 *
	 * If you would like specify a menu order
	 *
	 * @var int
	 */
	protected $menu_position;

	/**
	 * Capability
	 *
	 * What permission do I need to use this menu
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Network
	 *
	 * Network admin menu?
	 *
	 * @var bool
	 */
	protected $network = false;


	/**
	 * Settings
	 *
	 * Set me within your add_settings() method
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * tabs
	 *
	 * Put the settings sections into tabs
	 *
	 * @var bool
	 */
	protected $tabs = false;


	/**
	 * form_url
	 *
	 * The url the form submits
	 * Set automatically only adjust in extreme cases
	 *
	 * @var string
	 */
	protected $form_url;


	/**
	 * Add Settings
	 *
	 * Method used to set the settings
	 * Separate from set_vars to keep things cleaner
	 *
	 * @uses    $this->settings
	 *
	 * @example $this->settings = array(
	 *        'career-page' => array(
	 *            'title'    => 'Career Page',
	 *          'fields'    => array(
	 *                'career_heading_message' => 'Heading Message'
	 *
	 *            )
	 *        )
	 * );
	 *
	 */
	abstract protected function add_settings();


	/**
	 * Set Vars
	 *
	 * Use this method to set the necessary class vars
	 *
	 * @see This classes vars
	 *
	 * @return void
	 *
	 */
	abstract protected function set_vars();


	/**
	 * Call
	 *
	 */
	function __call( $name, $args ){

	}


	/**
	 * Construct
	 *
	 */
	function __construct(){
		$this->add_settings();
		$this->set_vars();
		$this->fill_class_vars();
		$this->hooks();

	}


	/**
	 * Hook em up
	 *
	 * @return void
	 */
	private function hooks(){
		if( $this->network ){
			add_action( 'network_admin_menu', array( $this, 'register_settings_page' ), 10, 0 );
			add_action( 'network_admin_edit_' . $this->slug, array( $this, 'save_network_settings' ), 10, 0 );

		} else {
			add_action( 'admin_menu', array( $this, 'register_settings_page' ), 10, 0 );

		}

	}


	/**
	 * Get Option
	 *
	 * Get a site option or regular depending if we are network or not
	 *
	 * @param string $field
	 *
	 * @return mixed|void
	 */
	public function get_option( $field ){
		if( $this->network ){
			return get_site_option( $field, null );
		} else {
			return get_option( $field, null );
		}
	}


	/**
	 * Save Network Settings
	 *
	 * Saves the settings if on a network page
	 * Uses update_site_option() instead of update_site_option
	 *
	 * @return void
	 */
	public function save_network_settings(){
		if( !isset( $_POST[ '_wpnonce' ] ) || !wp_verify_nonce( $_POST[ '_wpnonce' ], $this->slug . '-options' ) ){
			return;
		}

		foreach( $this->settings as $section => $params ){
			foreach( $params[ 'fields' ] as $field => $title ){
				if( method_exists( $this, $field . "_sanitize" ) ){
					$value = $this->{$field."_sanitize"}( $_POST[ $field ] );
				} else {
					$value = $_POST[ $field ];
				}
				update_site_option( $field, $value );
			}
		}

		wp_redirect( add_query_arg( array( 'page' => $this->slug, 'updated' => 'true' ), network_admin_url( $this->parent_menu_slug ) ) );

		exit();

	}


	/**
	 * Register Settings Page
	 *
	 * Build the settings page using the options framework
	 *
	 * @uses $this->settings
	 *
	 * @return void
	 *
	 */
	public function register_settings_page(){

		if( !empty( $this->parent_menu_slug ) ){
			add_submenu_page(
				$this->parent_menu_slug,
				$this->title,
				$this->menu_title,
				$this->capability,
				$this->slug,
				array( $this, 'display_settings_page' )
			);
		} else {
			add_menu_page(
				$this->title,
				$this->menu_title,
				$this->capability,
				$this->slug,
				array( $this, 'display_settings_page' ),
				$this->menu_icon,
				$this->menu_position
			);
		}

		foreach( $this->settings as $section => $params ){
			add_settings_section(
				$section,
				$params[ 'title' ],
				array( $this, $section . '_description' ),
				$this->slug
			);

			foreach( $params[ 'fields' ] as $field => $title ){
				add_settings_field(
					$field,
					$title,
					array( $this, 'field' ),
					$this->slug,
					$section,
					$field
				);

				if( !$this->network ){
					if( method_exists( $this, $field . "_sanitize" ) ){
						register_setting( $this->slug, $field, array( $this, $field . '_sanitize' ) );
					} else {
						register_setting( $this->slug, $field );
					}
				}

			}
		}

	}


	/**
	 * Field
	 *
	 * Will call a method matching the field name if exists
	 * Otherwise outputs a standard text field
	 *
	 * @param array  $item
	 * @param string $field
	 *
	 * @return void
	 *
	 */
	public function field( $field ){

		if( $this->network ){
			$value = get_site_option( $field, '' );
		} else {
			$value = get_option( $field, '' );
		}

		if( method_exists( $this, $field ) ){
			$this->{$field}( $value );

			return;
		}

		printf( '<input name="%1$s" value="%2$s" />', $field, $value );

	}


	/**
	 * Fill Class Vars
	 *
	 * Did you forget something? Oh well, this will fix it
	 *
	 * @return void
	 */
	private function fill_class_vars(){

		if( empty( $this->title ) ){
			$this->title = __( 'Settings', 'mvc' );
		}

		if( empty( $this->slug ) ){
			$this->slug = strtolower( str_replace( '\\', '-', get_class( $this ) ) );
		}

		if( empty( $this->menu_title ) ){
			$this->menu_title = $this->title;
		}

		if( $this->network ){
			if( 'options-general.php' == $this->parent_menu_slug ){
				$this->parent_menu_slug = 'settings.php';
			}
		}

		if( $this->network ){
			$this->form_url = network_admin_url( 'edit.php?action=' . $this->slug );
		} else {
			$this->form_url = admin_url( 'options.php' );
		}


	}


	/**
	 * Display Settings Page
	 *
	 * Outputs the settings page
	 *
	 * @return void
	 */
	public function display_settings_page(){
		?>
		<div class="wrap">
			<h2><?php echo $this->title; ?></h2>

			<?php
			if( $this->tabs ){
				$this->tabbed_form();

			} else {
				?>
				<form action="<?php echo $this->form_url; ?>" method="post">
					<?php
					settings_fields( $this->slug );
					do_settings_sections( $this->slug );
					submit_button();
					?>
				</form>
			<?php
			}
			?>
		</div>
	<?php

	}


	/**
	 * tabbed_form
	 *
	 * Generate a settings page with the settings sections placed into tabs
	 * Set $this->tabs to true and it will happen automatically
	 *
	 * @uses $this->settings
	 * @uses $this->tabs
	 *
	 * @return void
	 */
	private function tabbed_form(){
		reset( $this->settings );

		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : key( $this->settings );

		?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( $this->settings as $section => $params ){
				printf( '<a id="nav-%s" href="%s" class="nav-tab%s">%s</a>',
					$section,
					add_query_arg( 'tab', $section ),
					$tab == $section ? ' nav-tab-active' : '',
					$params[ 'title' ]
				);
			}
			?>
		</h2>

		<form action="<?php echo $this->form_url; ?>" method="post">
			<?php
			settings_fields( $this->slug );

			foreach( $this->settings as $section => $params ){
				printf( '<div class="tab-content" id="tab-%s" %s>',
					$section,
					$section != $tab ? 'style="display:none;"' : ''
				);

				?>
				<h3><?php echo $params[ 'title' ]; ?></h3>

				<?php
				$func = $section . '_description';
				$this->{$func}();
				?>

				<table class="form-table">
					<?php
					do_settings_fields( $this->slug, $section );
					?>
				</table>
				<?php

				submit_button();

				?>
				</div>
			<?php
			}
			?>
		</form>
		<script type="text/javascript">
			jQuery( 'a.nav-tab' ).click( function( e ){
				e.preventDefault();
				var id = e.target.id.substr( 4 );
				jQuery( 'div.tab-content' ).hide();
				jQuery( 'div#tab-' + id ).show();
				jQuery( 'a.nav-tab-active' ).removeClass( 'nav-tab-active' );
				jQuery( e.target ).addClass( 'nav-tab-active' );
			} );
		</script>
	<?php


	}

}
