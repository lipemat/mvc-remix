<?php

namespace MVC\Util;

/**
 * MvcForm
 *
 * Form Helpers
 *
 * @author  Mat Lipe <mat@matlipe.com>
 *
 * @class   Form
 * @package MVC
 *
 *
 * @namespace MVC\Util
 *
 */
class Form {
	use \MVC\Traits\Singleton;

	private static $image_js_out = false;


	/**
	 * Turns an array of attributes into a usable string
	 *
	 * @param array $atts
	 *
	 * @example array( 'id' => 'my-id', 'name' => 'myname' );
	 *
	 * @uses    sending a false value will display no attribute
	 * @since   5.29.13
	 */
	function attributeFactory( $atts ){
		$output = '';
		foreach( $atts as $attr => $value ){
			if( $attr == 'label' ){
				continue;
			}
			if( $value === false ){
				continue;
			}
			$output .= ' ' . $attr . '="' . $value . '"';
		}

		return substr( $output, 1, 999 );

	}


	/**
	 * Image Upload Form complete with Jquery
	 *
	 * @since 2.4.14
	 *
	 * @param string $name - the fields name if no specified in the args
	 * @param string $value
	 * @param array  $args - array(
	 *                     'value'        => $value,
	 *                     'button_label' => 'Upload',
	 *                     'name'         => $name,
	 *                     'id'           => $name
	 *                     );
	 * @param bool   $echo (defaults to true );
	 *
	 * @uses  contains and event called 'MVCImageUploadReturn' which is triggered when a new image is returned
	 *       This may be tapped into via js like so JQuery(document).bind("MVCImageUploadReturn", function( e, url ){});
	 *
	 * @uses  Be sure the ID does not already exist on the dom or this will break
	 *
	 * @return string|null
	 *
	 */
	function imageUploadForm( $name, $value = '', $args = array(), $echo = true ){
		wp_enqueue_media();

		$defaults = array(
			'value'        => $value,
			'button_label' => 'Upload',
			'name'         => $name,
			'id'           => $name,
			'size'         => 36
		);

		$args = wp_parse_args( $args, $defaults );

		$atts = $args;
		unset( $atts[ 'button_label' ] );

		$input = sprintf( '<input type="text" %s />', $this->attributeFactory( $args ) );
		$input .= sprintf( '<input type="button" rel="%s" value="%s" class="button-secondary image_upload" />', $args[ 'id' ], $args[ 'button_label' ] );

		//only display the js once
		if( self::$image_js_out ){
			if( $echo ){
				echo $input;

			} else {
				return $input;
			}
		} else {
			self::$image_js_out = true;

			ob_start();

			echo $input;
			?>

			<script type="text/javascript">
				function handle_mvc_form_image_upload( e ){
					var cu = wp.media( {
						button : {
							text : 'Use Selected Media'
						},
						multiple : false
					} ).on( 'select', function(){
						var items = cu.state().get( 'selection' );
						var attachment = items.models[0].toJSON();
						jQuery( 'document' ).trigger( 'MVCImageUploadReturn', [attachment.url, attachment, attachment] );
						jQuery( "#" + e.attr( 'rel' ) ).val( attachment.url );
					} ).open();
					return false;
				}
				jQuery( function( $ ){
					jQuery( '.image_upload' ).click( function( e ){
						handle_mvc_form_image_upload( jQuery( this ) );
					} );
				} );
			</script>

			<?php
			if( $echo ){
				echo ob_get_clean();
			} else {
				return ob_get_clean();
			}
		}

	}


	/**
	 * select
	 *
	 * Outputs a select from an array
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $options - array( %value% => %label )
	 * @param string [$all_label]
	 *
	 * @return void
	 */
	function select( $name, $value, array $options, $all_label = null ){
		printf( '<select name="%s">', $name );
			if( $all_label != null ){
				printf( '<option value="">%s</option>', $all_label );
			}
			foreach( $options as $_key => $_value ){
				printf( '<option value="%s" %s>%s</option>', $_key, selected( $_key, $value, false ), $_value );
			}
		printf( '</select>' );
	}
}