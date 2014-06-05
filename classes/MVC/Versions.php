<?php
namespace MVC;

/**
 * Versions
 * 
 * Allow for running things only once and keeping track of the db version
 * 
 * @example mvc_versions()->add_update( %version%, %function% );
 * 
 * @uses You must add updates during the init hook, because this will run them at the end of the init hook
 * @uses may retrieve current version via mvc_versions()->get_version()
 * 
 * @package MVC Theme
 * @namespace MVC
 * 
 * 
 */
class Versions {
	
	const OPTION = 'mvc-versions-version';
	
	/**
	 * Version
	 * 
	 * Keeps track of version in db
	 * 
	 * @static
	 * 
	 * @var float
	 */
	public static $version;
	
	
	/**
	 * Updates
	 * 
	 * Keeps track of the updates to run
	 * 
	 * @static
	 * 
	 * @var array
	 */
	public static $updates = array();


	/**
	 * Constructor
	 * 
	 * Set neccessary values
	 * 
	 */
	public function __construct(){
		
		self::$version = get_option( self::OPTION, 0.1 );
		
		$this->actions();	
	}
	
	
	public function actions(){
		add_action( 'init', array( $this, 'run_updates' ), 99999 );	
		
	}
	
	/**
	 * Get Version
	 * 
	 * Returns current version in db to know where to set updates
	 * 
	 * @uses option - mvc-versions-version
	 * 
	 * @return float
	 */
	public function get_version(){
		return self::$version;	
		
	}

	
	/**
	 * Add Update
	 * 
	 * Adds a method to be run if the version says to
	 * 
	 * @param float $version - the version to check against
	 * @param mixed $function_to_run - method or function to run if the version checks out
	 * @param mixed $args - args to pass to the function
	 * 
	 * @uses self::$updates
	 * 
	 * @return void
	 * 
	 */
	public function add_update( $version, $function_to_run, $args = null ){
		//if the version is higher than one in db, add to updates
		if( version_compare( $version, self::$version, '>' ) == 1 ){
			self::$updates[] = array( 'version' => $version, 'function' => $function_to_run, 'args' => $args );
		}
		
	}
	
	
	/**
	 * Run Updates
	 * 
	 * Run any updates with a newer version and update class and db to match newest
	 * 
	 * @uses added to the wp hook by $this->hooks()
	 * 
	 * @return void
	 */
	public function run_updates(){
		if( empty( self::$updates ) ) return;
		
		usort( self::$updates, array( $this, 'sort_by_version')  );
		
		foreach( self::$updates as $func ){
			self::$version = $func[ 'version' ];
	
			call_user_func( $func[ 'function' ], $func[ 'args' ] );	
							
		}
		
		update_option( self::OPTION, self::$version );
		
	}
	
	/**
	 * Sort By Version
	 * 
	 * Make sure the updates run in order by version
	 * 
	 * @param array $a
	 * @param array $b
	 * 
	 * @return bool
	 * 
	 */
	public function sort_by_version( $a, $b ){
		
		return version_compare( $a[ 'version' ], $b[ 'version' ], '>' );	
		
	}
	
	
	
	/********** SINGLETON FUNCTIONS **********/

	/**
	 * Instance of this class for use as singleton
	 */
	private static $instance;

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return self
	 */
	public static function get_instance() {
		if( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
			
		
}
	