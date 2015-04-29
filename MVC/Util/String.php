<?php

namespace MVC\Util;


/**
 * Mvc String
 *
 * String Helpers available in Views or via mvc_string()
 *
 * @author  Mat Lipe
 *
 * @uses    this will be available in all views via the $MvcString Variable
 *
 * @example mvc_string()->theContentLimit()
 *
 * @package MVC
 *
 */
class String {
	use \MVC\Traits\Singleton;

	/**
	 * @deprecated use mvc_template()->get_current_url();
	 */
	public function get_current_url(){
		_deprecated_function( 'get_current_url', '4.10.15', 'mvc_url()->get_current_url()' );

		return mvc_url()->get_current_url();
	}


	/**
	 * Give me the plural version
	 *
	 * @param string $label
	 *
	 * @return string
	 */
	public function plural_label( $label ){
		$end = substr( $label, - 1 );
		if( $end == 's' ){
			$plural = ucwords( $label . 'es' );
		} elseif( $end == 'y' ) {
			$plural = ucwords( rtrim( $label, 'y' ) . 'ies' );
		} else {
			$plural = ucwords( $label . 's' );
		}

		return __( $plural, 'edspire' );

	}


	/**
	 * getYoutubeImage
	 *
	 * @param string $embed
	 *
	 * @return string
	 */
	public function getYoutubeImage( $embed ){

		$video_thumb = '';

		// YouTube - get the video code if this is an embed code (old embed)
		preg_match( '/youtube\.com\/v\/([\w\-]+)/', $embed, $match );

		// YouTube - if old embed returned an empty ID, try capuring the ID from the new iframe embed
		if( !isset( $match[ 1 ] ) ){
			preg_match( '/youtube\.com\/embed\/([\w\-]+)/', $embed, $match );
		}

		// YouTube - if it is not an embed code, get the video code from the youtube URL
		if( !isset( $match[ 1 ] ) ){
			preg_match( '/v\=(.+)&/', $embed, $match );
		}

		// YouTube - get the corresponding thumbnail images
		if( isset( $match[ 1 ] ) ){
			$video_thumb = "http://img.youtube.com/vi/" . $match[ 1 ] . "/0.jpg";
		}

		// return whichever thumbnail image you would like to retrieve
		return $video_thumb;
	}


	/**
	 * Returns the content of the current $post limited to a number of characters
	 *
	 * @since 6.25.13
	 *
	 * @param int   $maxChar - number of characters to limit to
	 * @param       string   [$moreLinkText] - the text for the read more link - (defaults to
	 *                       false)
	 * @param array $args    (
	 *                       strip_tags => true
	 *                       'strip_teaser' => false
	 *                       'allowed_tags' => '<p><b><strong><em><i><u><del><span
	 *                       style="text-decoration: underline;">'
	 *
	 * @return string
	 */
	public function theContentLimit( $maxChar, $moreLinkText = false, $args = array() ){
		global $post;

		$defaults = array(
			'strip_tags'   => true,
			'strip_teaser' => false,
			'allowed_tags' => '<p><b><strong><em><i><u><del><span style="text-decoration: underline;">'
		);
		$args     = wp_parse_args( $args, $defaults );

		$content = get_the_content( $moreLinkText, $args[ 'strip_teaser' ] );

		$content = apply_filters( 'the_content', $content );

		$content = self::limitText( $content, $maxChar, $args );
		if( $moreLinkText ){
			$content .= apply_filters( 'content-limit-read-more', '<a class="read-more" href= ' . apply_filters( 'the_permalink', get_permalink() ) . '>' . $moreLinkText . '</a>', $post );
		}

		return $content;
	}


	/**
	 * Get the limited content from a particular post
	 *
	 * @since 6.25.13
	 *
	 * @param       mixed    int|obj $post - The Post or post ID
	 * @param int   $maxChar - number of characters to limit to
	 * @param       string   [$moreLinkText] - the text for the read more link - (defaults to
	 *                       false)
	 * @param array $args    (
	 *                       strip_tags => true
	 *                       'allowed_tags' => '<p><b><strong><em><i><u><del><span
	 *                       style="text-decoration: underline;">'
	 *
	 * @return string
	 */
	public function postContentLimit( $post, $maxChar, $moreLinkText = false, $args = array() ){

		$defaults = array(
			'strip_tags'   => true,
			'allowed_tags' => '<p><b><strong><em><i><u><del><span style="text-decoration: underline;">',
		);
		$args     = wp_parse_args( $args, $defaults );

		if( !is_object( $post ) ){
			$post = get_post( $post );
		}
		$content = $post->post_content;

		$content = self::limitText( $content, $maxChar, $args );
		if( $moreLinkText ){
			$content .= apply_filters( 'content-limit-read-more', '<a class="read-more" href= ' . apply_filters( 'the_permalink', get_permalink( $post->ID ) ) . '>' . $moreLinkText . '</a>', $post );
		}

		return $content;

	}


	/**
	 * Limit a sting to a number of characters
	 *
	 * @since 6.25.13
	 *
	 * @param string $content - the content to limit
	 * @param int    $maxChar - the number of characters to max out on
	 * @param        bool     [$striptags] - to remove the html tags from the content (defaults
	 *                        to true)
	 * @param array  $args    (
	 *                        'allowed_tags' => '<p><b><strong><em><i><u><del><span
	 *                        style="text-decoration: underline;">',
	 *                        'strip_tags' => true
	 */
	public function limitText( $content, $maxChar = 1000, $args = array() ){

		$defaults = array(
			'allowed_tags' => '<p><b><strong><em><i><u><del><span style="text-decoration: underline;">',
			'strip_tags'   => true
		);
		$args     = wp_parse_args( $args, $defaults );

		$content = strip_shortcodes( $content );
		$content = str_replace( ']]> ', ']]&gt; ', $content );
		$content = preg_replace( '#<p class="wp-caption-text">(.*?)</p># ', '', $content );

		if( $args[ 'strip_tags' ] ){
			$content = strip_tags( $content );
		} else {
			$content = strip_tags( $content, $args[ 'allowed_tags' ] );
		}

		if( ( strlen( $content ) > $maxChar ) && ( $space = strpos( $content, " ", $maxChar ) ) ){
			$content = substr( $content, 0, $space );
		}

		if( !$args[ 'strip_tags' ] ){
			return balanceTags( $content );
		}

		return $content;

	}


	/**
	 * Returns and html link to a post using the Post's ID
	 *
	 * @since 8.6.13
	 *
	 * @param init $postId    - the Id of a post of any type
	 * @param bool $newWindow - open link in new window
	 */
	public function getPostLink( $postId, $newWindow = false ){
		$title = get_the_title( $postId );

		if( get_post_type( $postId ) == 'attachment' ){
			$link = get_post_field( 'guid', $postId );
		} else {
			$link = get_permalink( $postId );
		}

		if( !$newWindow ){
			$target = '_self';
		} else {
			$target = '_blank';
		}

		return sprintf( '<a href="%s" title="%s" target="%s">%s</a>', $link, 'Go To ' . $title, $target, $title );

	}


	/**
	 * Returns a human readable slug with the _ remove and words uppercase
	 *
	 * @param string $slug
	 *
	 * @return string
	 * @since 5.9.13
	 */
	public function human_format_slug( $slug ){
		return ucwords( str_replace( '_', ' ', $slug ) );
	}


	/**
	 * Turns and human readable phrase into a slug
	 *
	 * @param string $human
	 *
	 * @return string
	 * @since 5.9.13
	 */
	public function slug_format_human( $human ){
		return preg_replace( '/[^a-z0-9_]/', '', strtolower( str_replace( ' ', '_', $human ) ) );
	}


	/**
	 * Wraps the First word in a Span for Styling
	 *
	 * @since 2.18.13
	 *
	 * @param STRING | $text the text to wrap
	 *
	 * @return STRING
	 */
	function wrapFirstWord( $text ){
		$text_elements = explode( " ", $text );
		$text          = '<span class="first-word">' . $text_elements[ 0 ] . '</span> ';
		unset( $text_elements[ 0 ] );
		$text .= implode( ' ', $text_elements );

		return $text;
	}


	/**
	 * Wraps the pipes and dashed with spans
	 * * Seems excessive right? Well think again.
	 * * Our designers seem to think we can drag elements all over the page like
	 * Dreamweaver or something!.!
	 * * Well, I'm here to tell you this is bullshit. - so instead of getting pissed,
	 * I wrote this.
	 *
	 * @uses  run any content through here to get some spans to add padding to
	 * @uses  use wrapPipes instead for stuff which may contain links
	 *
	 * @see   wrapPipes
	 *
	 * @param string $output - the content to add the spans to
	 *
	 * @return string
	 * @since 4.4.0
	 *
	 * @since 6.4.13
	 *
	 */
	function wrapPipesAndDashes( $output ){
		$output = str_replace( array(
			'|',
			'-'
		), array(
			'<span class="pipe">|</span>',
			'<span class="dashes">-</span>'
		), $output );

		return $output;

	}


	/**
	 * Wraps the pipes with spans (had to do this because the dashes were breaking
	 * links
	 * * Seems excessive right? Well think again.
	 * * Our designers seem to think we can drag elements all over the page like
	 * Dreamweaver or something!.!
	 * * Well, I'm here to tell you this is bullshit. - so instead of getting pissed,
	 * I wrote this.
	 *
	 * @uses  run any content through here to get some spans to add padding to
	 *
	 * @param string $output - the content to add the spans to
	 *
	 * @return string
	 * @since 4.4.0
	 *
	 */
	function wrapPipes( $output ){
		$output = str_replace( array( '|' ), array( '<span class="pipe">|</span>' ), $output );

		return $output;
	}


	/**
	 * This function is used to find the string between two stings
	 *
	 * @example echo find_between($s, '<title>', '</title>'), "\n";
	 * @example echo find_tag_content($s, 'body'), "\n";
	 * #
	 * # Requires $s = 'the string to serach through
	 * # $str1 = 'The starting string'
	 * # $str2 = 'The ending stings'
	 * # [optionsl] $case_sensitive = boolean = 'case sensitive search or not
	 *
	 */
	function find_between( $s, $str1, $str2, $case_sensitive = false ){
		if( $case_sensitive == false ){

			//finds the first occurance of the $str1 no case senative
			$start = stripos( $s, $str1 );
			if( $start === false ){
				return ' Start Not Found';
			}
			// adds the length of the string to the start just in case the end is in the
			// string
			$start += strlen( $str1 );
			$end = stripos( $s, $str2, $start );
			if( $end === false ){
				return 'End not Found';
			}

		} else {
			//finds the first occurance of the $str1 case sensitive
			$start = strpos( $s, $str1 );
			if( $start === false ){
				return ' Start Not Found';
			}
			$start += strlen( $str1 );
			$end = strpos( $s, $str2, $start );
			if( $end === false ){
				return 'End not Found';
			}
		}

		//return the string between the end and the start
		return substr( $s, $start, $end - $start );
	}


	/**
	 * Find the contents between two tags
	 *
	 * @since   5.14.13
	 * @example findTagContents('<div>hi</div>','div');
	 */
	function findTagContents( $s, $tag ){
		return $this->find_between( $s, "<$tag>", "</$tag>", true );
	}


}
