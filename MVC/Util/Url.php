<?php

namespace MVC\Util;


/**
 * Mvc Url
 *
 * Url helpers available via via mvc_url()
 *
 * @author  Mat Lipe
 *
 * @since 4.10.15
 *
 * @example mvc_url()->get_content_url()
 *
 * @package MVC
 *
 */
class Url {
	use \MVC\Traits\Singleton;

	/**
	 * Get Current Url
	 *
	 * Returns the url of the page you are currently on
	 *
	 * @return string
	 */
	public function get_current_url(){
		$prefix = is_ssl() ? "https://" : "http://";
		$current_url = $prefix. $_SERVER["HTTP_HOST"] . $_SERVER[ "REQUEST_URI" ];
		return $current_url;
	}

}