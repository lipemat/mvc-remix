<?php


/**
 * MvcImageResize
 *
 * @author Mat Lipe
 * @since  8/11/2015
 *
 */
class MvcImageResize extends \MVC\Util\Image_Resize{

	public function __construct(){
		_deprecated_function( 'MvcImageResize', 1.0, '\MVC\Util\Image_Resize' );
		parent::__construct();
	}
}