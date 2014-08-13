<?php
namespace MVC;

/**
 * Settings
 *
 * Abstract starting point for a settings page
 *
 *
 * @uses extend this with another class that does not have a __construct method or call parent::__construct()
 *       Implement the abstract methods and set appropriate class vars. This will do the rest.
 *
 * @uses to have a description for a section create a public method %section_slug%_description and
 *       it will automatically be used
 *
 * @uses to override the default text field create a protected method with same name as option and
 *       it will be passed the value of the option as it only argument
 *
 * @package MVC Theme
 * @namespace MVC
 *
 * @class Settings
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
	 * Add Settings
	 * 
	 * Method used to set the settings
	 * Separate from set_vars to keep things cleaner
	 * 
	 * @uses $this->settings
	 * 
	 * @example $this->settings = array(
	 * 		'career-page' => array(
	 * 			'title'  	=> 'Career Page',
	 *          'fields' 	=> array(
	 * 				'career_heading_message' => 'Heading Message'
	 * 				
	 * 			)
	 * 		)
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
	function __construct() {	
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
			add_action( 'admin_menu', array( $this, 'register_settings' ) );	
		} else {		
			add_action( 'admin_menu', array( $this, 'register_settings_page' ), 10, 0 );
			
		}
		
	}


	/**
	 * Add Options
	 * 
	 * Used to register settings for use with Network admin
	 * The network_admin_menu will not register the settings so you get a no options page error
	 * 
	 * @uses called via admin_menu when using the $this->network
	 * 
	 * @return void
	 */
	public function register_settings(){
		foreach( $this->settings as $section => $params ){
			foreach( $params[ 'fields' ] as $field => $title ){
				register_setting( $this->slug, $field );
			}
		}	
		
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
				
				register_setting( $this->slug, $field );
			}
		}
	
	}
	
	
	/**
	 * Field
	 * 
	 * Will call a method matching the field name if exists
	 * Otherwise outputs a standard text field
	 * 
	 * @param array $item
	 * @param string $field
	 * 
	 * @return void
	 * 
	 */
	public function field( $field ){
		
		$value = get_option( $field, '' );
		
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
	}
	
	


	/**
	 * Display Settings Page
	 *
	 * Outputs the settings page
	 *
	 * @return void
	 */
	public function display_settings_page() {
		?>
		<div class="wrap">
			<h2><?php echo $this->title; ?></h2>
			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php
				settings_fields( $this->slug );
				do_settings_sections( $this->slug );
				submit_button();
				?>
			</form>
		</div>
		<?php
		
	}

}
