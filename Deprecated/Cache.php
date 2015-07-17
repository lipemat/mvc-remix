<?php

namespace MVC;

/**
 * Cache
 *
 * @author  Mat Lipe
 * @since   7/17/2015
 *
 * @package MVC
 */
class Cache extends Util\Cache{
	function __construct(){
		_deprecated_function( 'MVC\CACHE', '1.0', 'MVC\Util\Cache' );
		parent::__construct();
	}
}