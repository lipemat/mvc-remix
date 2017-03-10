<?php

namespace MVC\Util;

/**
 * Webpack
 *
 * @author  Mat Lipe
 * @since   3/10/2017
 *
 * @package MVC\Util
 */
class React_Webpack extends Webpack {
	public function __construct( $directory, $handle = 'mvc-react-webpack' ){
		_deprecated_constructor( 'React_Webpack', 'MVC\Util\Webpack' );
		parent::__construct( $directory, $handle );
	}

}