<?php


/**
 * MvcInternalTax
 *
 * @author Mat Lipe
 * @since  8/10/2015
 *
 */
class MvcInternalTax extends \MVC\Util\Internal_Tax{

	public function __construct(){
		_deprecated_function( 'MvcInternalTax', 1.0, '\MVC\Util\Internal_Tax');
		parent::__construct();
	}
}