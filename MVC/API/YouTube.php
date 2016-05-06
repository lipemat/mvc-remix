<?php

namespace MVC\API;

use MVC\Cache;

/**
 * Youtube
 *
 * To do so I need an api key. This can be obtained by registering a project with Google APIs and giving it YouTube
 * access.
 * # Go to the developer console https://console.developers.google.com/project
 * # Click "Create Project" if some already exist otherwise, use the "Select a Project" drop-down and click "Create a
 * Project"
 * # Enter a project name like "Steelcase.com"
 * # Click "Create"
 * # Under the list of "Popular APIs" click "YouTube Data API"
 * # Click "Enable"
 * # Click "Go to Credentials"
 * # Under "Where will you be calling the API from?" select "Web Browser"
 * # Under "What data will you be accessing?" check "Public data"
 * # Click "What credentials do I need?"
 * # Fill out the "Name" field
 * # Leave the "Accept requests from these HTTP referrers (web sites)" blank
 * # Click "Create API key"
 * # Under "Get your credentials" copy the API key and post it here
 *
 * E.G AIzaSyCwuMNgkjhfDWZc_FDrcq8TexW3OMT3I1Q
 *
 *
 * @author  Mat Lipe
 *
 *
 * @link    https://developers.google.com/youtube/v3/getting-started#Sample_Partial_Requests
 *
 * @package MVC\API
 */
class Youtube implements \JsonSerializable {
	const API_URL = "https://www.googleapis.com/youtube/v3/videos?id=%id%&key=%api_key%&part=snippet";

	const OEMBED_URL = "http://www.youtube.com/oembed?url=%url%&maxwidth=%width%&maxheight=%height%";

	private $api_key = false;

	private $url;

	private $object;

	public $height = 400;

	public $width = 700;


	public function __construct( $url, $api_key ){
		$this->url     = $url;
		$this->api_key = $api_key;
		$this->object  = $this->request_from_api();
	}


	/**
	 * Calls the methods to match the structure of a returned
	 * oembed object
	 *
	 * So $video->thumbnail_url becomes $this->get_thumbnail_url()
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public function __get( $field ){
		if( method_exists( $this, "get_$field" ) ){
			return $this->{"get_$field"}();
		}

		return false;
	}

	public function jsonSerialize(){
		return array(
			'id'            => $this->get_id(),
			'url'           => $this->url,
			'title'         => $this->get_title(),
			'video'         => $this->get_html(),
			'thumbnail_url' => $this->get_thumbnail_url(),
			'description'   => $this->get_description(),
			'html'          => $this->get_html(),
		);
	}


	public function get_id(){
		return isset( $this->object->id ) ? $this->object->id : "";

	}


	public function get_title(){
		return isset( $this->object->title ) ? $this->object->title : "";

	}


	public function get_description(){
		return isset( $this->object->description ) ? $this->object->description : "";

	}


	public function get_thumbnail_url(){
		$thumbnail = '';
		if( isset( $this->object->thumbnails->high ) ){
			$thumbnail = $this->object->thumbnails->high->url;
		} elseif( isset( $this->object->thumbnails->medium ) ) {
			$thumbnail = $this->object->thumbnails->medium->url;
		}

		return $thumbnail;
	}


	public function get_html(){
		$frame = "";
		if( !empty( $this->object->id ) ){
			$frame = '<iframe 
						width="' . $this->width . '" 
						height="' . $this->height . '"
						src="http://www.youtube.com/embed/' . $this->object->id . '">
				</iframe>';
		}

		return $frame;
	}


	public function set_width( $width ){
		$this->width = $width;
	}


	public function set_height( $height ){
		$this->height = $height;
	}


	/**
	 * Get the video object from the api
	 * Contains description and image size which the
	 * oembed does not.
	 * Does not include and html player
	 *
	 * @notice If you dont' have an api key like this will be distributed
	 *         Then use $this->get_oembed()
	 *
	 * @return mixed
	 */
	private function request_from_api(){
		if( empty( $this->api_key ) ){
			return false;
		}

		$cache_key = array(
			__CLASS__,
			__METHOD__,
			'url' => $this->url,
		);

		$object = Cache::get( $cache_key );
		if( $object === false ){
			$url_parts = explode( 'v=', $this->url );
			$id        = array_pop( $url_parts );
			if( !empty( $id ) ){
				$url = str_replace( '%id%', $id, self::API_URL );
				$url = str_replace( '%api_key%', $this->api_key, $url );

				$response = wp_remote_get( $url );
				$object   = @json_decode( wp_remote_retrieve_body( $response ) );
				if( !empty( $object ) ){
					$video      = array_shift( $object->items );
					$object     = $video->snippet;
					$object->id = $video->id;
				}
				Cache::set( $cache_key, $object );
			}
		}

		return $object;
	}


	/**
	 * Get the Oembed object for this video
	 * Does not include things like description and thumbnail size
	 * but does include an html player
	 * Also, does not require an api key
	 *
	 * @notice if you have an api key this method is pretty much redundant
	 *         and here for possible future usage
	 *
	 * @return object
	 */
	public function get_oembed(){
		$cache_key = array(
			__CLASS__,
			__METHOD__,
			'url' => $this->url,
		);

		$object = Cache::get( $cache_key );
		if( $object === false ){
			$url = str_replace( '%url%', urlencode( $this->url ), self::OEMBED_URL );
			$url = str_replace( '%height%', $this->height, $url );
			$url = str_replace( '%width%', $this->width, $url );

			$response = wp_remote_get( $url );
			$object   = @json_decode( wp_remote_retrieve_body( $response ) );
			Cache::set( $cache_key, $object );
		}

		return $object;
	}


	/**
	 * @deprecated
	 *
	 * @see \MVC\APIYoutube::get_thumbnail_url();
	 */
	public function get_thumbnail(){
		return $this->get_thumbnail_url();
	}
}