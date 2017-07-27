<?php

namespace MVC\Util;

use MVC\Traits\Singleton;

/**
 * Images
 *
 * @author  Mat Lipe
 * @since   3/1/2015
 *
 * @package MVC\Util
 */
class Images {
	use Singleton;

	/**
	 * Get the first image of the post's content
	 *
	 * @param int [$post_id] - defaults to global $post
	 *
	 * @since 9.13.13
	 *
	 * @return string|null
	 *
	 * */
	public function getFirstContentImage( $post_id = 0 ){
		if( empty( $post_id ) ){
			$post_id = get_the_ID();
			if( empty( $post_id ) ){
				return null;
			}
		}

		$first_img = wp_cache_get( __METHOD__ . ':' . $post_id, 'default' );
		if( $first_img !== false ){
			return $first_img;
		}

		$content = get_post_field( 'post_content', $post_id );

		if( is_wp_error( $content ) || empty( $content ) ){
			$first_img = null;

		} else {

			preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches );
			if( isset( $matches[ 1 ][ 0 ] ) ){
				$first_img = $matches[ 1 ][ 0 ];
			} else {
				$first_img = null;
			}
		}

		wp_cache_set( __METHOD__ . ':' . $post_id, $first_img, 'default', DAY_IN_SECONDS );

		return $first_img;
	}


	/**
	 * Returns the Attachments ID using the url
	 *
	 * @since 4.3.0
	 *
	 * @param string $attachment_url - the url
	 *
	 * @uses  must be a url of an image uploaded via wordpress
	 */
	function getAttachmentIdbyUrl( $attachment_url = '' ){

		global $wpdb;
		$attachment_id = false;

		// If there is no url, return.
		if( '' == $attachment_url ){
			return;
		}

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if( false !== strpos( $attachment_url, $upload_dir_paths[ 'baseurl' ] ) ){

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths[ 'baseurl' ] . '/', '', $attachment_url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

		}

		return $attachment_id;
	}


	/**
	 * Returns the featured image or the first on uploaded if no feature exists
	 *
	 * @since 5.5.0
	 *
	 * @since 7.22.13
	 *
	 * @param string $size the size of the image defaults to 'thumbnail'
	 * @param        int   [optional] $post_id the id of the post
	 * @param bool   $html or object format defaults html
	 */
	function getFirstImage( $size = 'thumbnail', $postId = false, $html = true ){

		//Use the current post's id of one was not sent
		if( !$postId ){
			global $post;
			$postId = $post->ID;
		}

		//If the post has a thumbnail
		if( has_post_thumbnail( $postId ) ){
			if( $html ){
				return get_the_post_thumbnail( $postId, $size );
			} else {
				$image[ 'ID' ] = get_post_thumbnail_id( $postId );

				return $this->getImageData( $image[ 'ID' ], $size );
			}
		}

		//Retrieve the First Image uploaded to the post if no thumbnail
		$image = get_children(
			array(
				'post_parent'    => $postId,
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'numberposts'    => '1',
				'fields'         => 'ids'
			)
		);

		if( empty( $image ) ){
			return false;
		}

		$image         = (array) reset( $image );
		$image[ 'ID' ] = $image[ 0 ];

		//If just needs an html image return the image
		if( $html ){
			return wp_get_attachment_image( $image[ 'ID' ], $size );
		} else {
			return $this->getImageData( $image[ 'ID' ], $size );
		}

	}


	/**
	 * Get Post Images
	 *
	 * Retrives all the images attached to a post
	 *
	 *
	 * @param array $args   array(
	 *                      -  bool   'html' - to return pre formatted images ( defaults to true )
	 *                      -  bool   'include_featured' - to include the featured image or not ( defaults to false )
	 *                      -  string 'size' - the image size as specified in add_image_size()
	 *                      -  string 'wrap_start' - if using html what to wrap the element it e.g  <div>
	 *                      -  string 'wrap_end' -  the closing wrap e.g. </div>
	 *                      -  bool   'include_content_images' - to include images which appear in content - ( default false )
	 *                      -  bool   'include_meta_images' -  to include images added to meta fields like tabs  ( default false )
	 *                      -  string 'mvc-gallery' - The name of the gallery used when constructing MvcGallery(, $gallery)
	 *
	 * @param       WP_Post [$post] - ( defaults to global $post )
	 *
	 */
	function getPostImages( $args, $post = null ){

		$post = get_post( $post );

		//Caching of the retrived image per gallery in case of multiple gallery calls on same page
		static $retrieved;
		static $retrieved_gallery;

		$defaults = array(
			'html'                   => true,
			'include_featured'       => false,
			'size'                   => 'thumbnail',
			'wrap_start'             => '',
			'wrap_end'               => '',
			'include_content_images' => false,
			'include_meta_images'    => false,
			'mvc_gallery'            => false
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		$content_images = array();

		//to exclude the featured image
		if( $include_featured ){
			$exclude = '';
		} else {
			$exclude = get_post_thumbnail_id();
		}

		if( isset( $retrieved[ $post->ID ] ) && ( $retrieved_gallery[ $post->ID ] == $mvc_gallery ) ){
			//Use cached version if available
			$all_images = $retrieved[ $post->ID ];
		} else {
			$img_args = array(
				'post_parent'    => $post->ID,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => 'ASC',
				'orderby'        => 'menu_order ID',
				'exclude'        => $exclude
			);

			//Retrieve all the images in this posts gallery
			if( $mvc_gallery ){
				$images = get_post_meta( $post->ID, 'mvc-gallery-' . $mvc_gallery, true );
				if( empty( $images ) ){
					return false;
				}

				unset( $img_args[ 'post_parent' ] );
				$img_args[ 'numberposts' ] = - 1;
				$img_args[ 'orderby' ]     = 'post__in';
				$img_args[ 'post__in' ]    = $images;
				$all_images                = get_posts( $img_args );
			} else {
				//REtrieve all the images attached to this post
				$all_images = get_children( $img_args );
			}
		}

		//Retrieve the other possible sizes
		foreach( $all_images as $image ){
			if( $size != 'default' ){
				$image->{$size} = wp_get_attachment_image_src( $image->ID, $size );
				$image->guid    = $image->{$size}[ 0 ];
			}
			$image->thumb  = wp_get_attachment_image_src( $image->ID, 'thumbnail' );
			$image->medium = wp_get_attachment_image_src( $image->ID, 'medium' );
			$image->large  = wp_get_attachment_image_src( $image->ID, 'large' );
		}

		//for caching;
		$retrieved[ $post->ID ]         = $all_images;
		$retrieved_gallery[ $post->ID ] = $mvc_gallery;

		//To Exclude images in post meta like tabs
		if( !$include_images_meta ){
			foreach( get_post_meta( $post->ID ) as $meta ){
				preg_match_all( '/src="([^"]*)"/i', $meta[ 0 ], $images );
				if( !empty( $images[ 1 ] ) ){
					$content_images = array_merge( $content_images, $images[ 1 ] );
				}
			}
		}

		//To exclude any in the content
		if( !$include_content_images ){
			preg_match_all( '/src="([^"]*)/i', $post->post_content, $images );
			if( !empty( $images[ 1 ] ) ){
				$content_images = array_merge( $content_images, $images[ 1 ] );
			}
		}

		//Remove the images in the content from the $all_images array
		foreach( $all_images as $image ){

			//If any of the images considered content images are this image remove it
			if( in_array( $image->guid, $content_images ) ||
			    in_array( $image->thumb[ 0 ], $content_images ) ||
			    in_array( $image->medium[ 0 ], $content_images ) ||
			    in_array( $image->large[ 0 ], $content_images )
			){
				//remove it from the global arrray
				unset( $all_images[ $image->ID ] );
			}
		}

		//to return the images in html form
		if( $html ){
			foreach( $all_images as $image ){
				$html_images .= $wrap_start . '<img src="' . $image->guid . '" title="' . $image->post_title . '" />' . $wrap_end;
			}
			if( !isset( $html_images ) ){
				return false;
			}

			return $html_images;
		} else {
			return $all_images;
		}

	}


	/**
	 * Retrieves all data for a particluar image
	 *
	 * @param  $image_id
	 *
	 * @return array|boolean
	 * @uses  returns false if no image returned
	 * @uses  called by self::get_first_image()
	 * @since 2.11.14
	 */
	function getImageData( $image_id, $size = 'thumbnail' ){

		if( !is_numeric( $image_id ) ){
			$image_id = $this->getAttachmentIdbyUrl( $image_id );
		}

		if( empty( $image_id ) ){
			return false;
		}

		$image[ 'ID' ]  = $image_id;
		$src            = wp_get_attachment_image_src( $image[ 'ID' ], $size );
		$image[ 'url' ] = wp_get_attachment_image_src( $image[ 'ID' ], $size );

		$image[ 'meta' ] = wp_get_attachment_metadata( $image[ 'ID' ], true );
		$folder          = explode( '/', $image[ 'meta' ][ 'file' ] );
		array_pop( $folder );
		$dir    = wp_upload_dir();
		$folder = $dir[ 'baseurl' ] . '/' . implode( '/', $folder );

		foreach( $image[ 'meta' ][ 'sizes' ] as $size => $data ){
			$image[ $size ] = $folder . '/' . $data[ 'file' ];
		}

		if( $src ){
			list( $src, $width, $height ) = $src;
			$hwstring = image_hwstring( $width, $height );
			if( is_array( $size ) ){
				$size = join( 'x', $size );
			}
			$attachment = get_post( $image[ 'ID' ] );
			$data       = array(
				'src'   => $src,
				'class' => "attachment-$size",
				'alt'   => trim( strip_tags( get_post_meta( $image[ 'ID' ], '_wp_attachment_image_alt', true ) ) ),
				// Use Alt field first
				'title' => trim( strip_tags( $attachment->post_title ) ),
			);
			// If not, Use the Caption
			if( empty( $data[ 'alt' ] ) ){
				$data[ 'alt' ] = trim( strip_tags( $attachment->post_excerpt ) );
			}
			// Finally, use the title
			if( empty( $data[ 'alt' ] ) ){
				$data[ 'alt' ] = trim( strip_tags( $attachment->post_title ) );
			}
			//Combine the image with the data
			$image = array_merge( $image, $data );
		} else {
			return false;
		}

		$image[ 'full_size_url' ] = wp_get_attachment_image_src( $image[ 'ID' ], 'full' );

		return $image;
	}


}