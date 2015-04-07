<?php

namespace MVC\Util;

/**
 * Translate
 *
 * @author Mat Lipe
 * @since 4/7/2015
 *
 * @package MVC\Util
 */
class Translate {
	use \MVC\Traits\Singleton;

	protected function __construct(){
		add_filter( 'date_i18n', array( $this, 'translate_dates' ), 9, 4 );
	}

	/**
	 * Translates the S from php date
	 *
	 * Using date_il8n will take care of the month and weekday
	 * This takes care of the suffix like th, nd
	 *
	 * @param string $j          Formatted date string.
	 * @param string $req_format Format to display the date.
	 * @param int    $i          Unix timestamp.
	 * @param bool   $gmt
	 *
	 * @return string
	 *
	 */
	public function translate_dates( $j, $req_format, $i, $gmt ){
		if( strpos( $req_format, 'S' ) !== false ){
			$dateformatstring = $req_format;
			$translated       = __( date( 'S', $i ) );
			$dateformatstring = preg_replace( "/([^\\\])S/", "\\1" . backslashit( $translated ), $dateformatstring );
			$j                = date_i18n( $dateformatstring, $i, $gmt );
		}

		return $j;

	}


	/**
	 * Private unused method to setup translating S date formats
	 *
	 * @void Never runs but picked up by gettext
	 */
	private function add_S_translations(){
		__( 'rd' );
		__( 'th' );
		__( 'nd' );
		__( 'st' );
	}

}