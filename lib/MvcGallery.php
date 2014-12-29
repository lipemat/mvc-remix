<?php
/**
 * Adds ability to upload images into a gallery group
 * * Creates meta box and handles all uploding needs
 *
 * @uses  new MvcGallery($postTypes, array( %gallery_1, %gallery_2) );
 *
 * @param array $postTypes - The post types to use this on
 *
 * @since 1.23.14
 *
 *
 */
if( class_exists( 'MvcGallery' ) ){
	return;
}


class MvcGallery extends MvcFramework {

	public $post_types = array();

	private $groups = array();


	/**
	 * Sets everything in motion
	 *
	 * @param array $postTypes - the posts types to add the functionalty to
	 * @param       array      [$groups] - the different groups of images default to gallery
	 *
	 * @uses  __construct($postTypes)
	 *
	 * @since 1.20.14
	 */
	function __construct( $postTypes = array( 'post', 'page' ), $groups = array( 'gallery' ) ){

		if( !is_array( $postTypes ) ){
			$postTypes = array( $postTypes );
		}

		$this->post_types = $postTypes;
		$this->groups     = $groups;

		add_action( 'admin_print_scripts', array( $this, 'js' ), 999 );
		wp_enqueue_script( 'jquery-ui-sortable' );

		add_action( 'admin_menu', array( $this, 'metaBoxSetup' ), 99 );

		add_action( 'save_post', array( $this, 'save_post' ), 99 );

	}


	/**
	 * Register Meta Boxes
	 *
	 * @since 10.1.13
	 **/
	function metaBoxSetup(){
		foreach( $this->post_types as $pt ){
			foreach( $this->groups as $group ){
				add_meta_box( $group, ucwords( str_replace( '_', ' ', $group ) ), array(
					$this,
					'metaBoxOutput'
				), $pt, 'advanced', 'high' );
			}
		}

	}


	/**
	 * Returns and an array of the groups with slugs as keys
	 *
	 * @since 10.1.13
	 * @uses  $this->groups;
	 */
	function cleanGroups(){
		static $clean = false;
		if( $clean ){
			return $clean;
		}

		$clean = array();
		foreach( $this->groups as $group ){
			$clean[ ucwords( str_replace( '_', ' ', $group ) ) ] = $group;
		}

		return $clean;


	}


	/**
	 * Saves the Gallery Images to the Post Meta
	 *
	 * @since 1.20.14
	 * @uses  called by the 'save_post' action
	 *
	 */
	function save_post( $post_id ){
		global $post, $wp_post_types;

		switch ( true ){
			case !wp_verify_nonce( @$_POST[ __CLASS__ . '_noncename' ], __CLASS__ ):
			case defined( 'DOING_AUTOSAVE ' ) && DOING_AUTOSAVE:
			case !current_user_can( 'edit_post', $post_id ):
				return $post_id;
		}

		foreach( $this->groups as $group ){
			if( isset( $_POST[ 'mvc-gallery-' . $group ] ) ){
				update_post_meta( $post_id, 'mvc-gallery-' . $group, $_POST[ 'mvc-gallery-' . $group ] );
			} else {
				delete_post_meta( $post_id, 'mvc-gallery-' . $group );
			}
		}

		return $post_id;
	}


	/**
	 * Output of the Gallery Meta Box
	 * * Displays the existing Gallery Images and has links to add more
	 *
	 * @since 1.20.14
	 * @uses  called by self::metaBoxSetup
	 *
	 * @param obj   $post  - the current post
	 * @param array $group - the gallery group data
	 */
	function metaBoxOutput( $post, $group ){
		$tbody = '';

		$images = get_post_meta( $post->ID, 'mvc-gallery-' . $group[ 'id' ], true );

		?>
		<div class="mvc-gallery">
			<p>
				<input class="button-secondary" data-group="<?php echo $group[ 'id' ]; ?>" data-title="<?php echo $group[ 'title' ]; ?>" value="Add Image" style="text-align: center"/>
			</p>
			<?php if( empty ( $images ) ){
				?>
				<p id="uncheck-message" style="display:none">Uncheck an image to remove it.</p><?php
			} else {
				?>
				<p>Uncheck an image to remove it.</p><?php
			}
			?>
			<div class="scroll">
				<ol rel="<?php echo 'mvc-gallery-' . $group[ 'id' ]; ?>"><?php
					if( !empty ( $images ) ){
						foreach( $images as $image ){
							$image = get_post( $image );
							if( empty( $image ) ){
								continue;
							}
							?>
							<li>
								<img src="<?php echo $this->getAttachmentUrl( $image ); ?>"/>
								<input
									type="checkbox"
									checked="checked"
									name="mvc-gallery-<?php echo $group[ 'id' ]; ?>[]"
									value="<?php echo $image->ID; ?>"
									id="c<?php echo $image->ID; ?>"

									/>
								<label for="c<?php echo $image->ID; ?>">
									<span> </span><?php echo basename( $image->guid ); ?>
								</label>
							</li>
						<?php
						}
					}
					?></ol>
			</div>


		</div><!-- //.mvc-gallery -->
		<?php
		global $is_IE;
		?>
		<input
			type="hidden"
			name="<?php echo __CLASS__;?>_noncename"
			value="<?php echo wp_create_nonce( __CLASS__ ); ?>"
			/>

		<style type="text/css">
			.mvc-gallery p {
				text-align : left;
			}

			.mvc-gallery.scroll {
				max-height : 300px;
				height     : auto !important;
				height     : 300px;
				overflow   : auto;
			}

			.mvc-gallery li {
				border-bottom : 1px solid #ccc;
			}

			.mvc-gallery img {
				width          : 75px;
				margin         : 0 10px 10px 0;
				vertical-align : top;
			}
		</style>
	<?php
	}


	/**
	 * Retreive either the thumb for an image or placeholder for docs
	 *
	 * @since 10.1.13
	 *
	 * @param WP_Post $img
	 */
	function getAttachmentUrl( WP_Post $img ){

		$type = get_post_mime_type( $img );

		$base = apply_filters( 'icon_dir_uri', includes_url( 'images/crystal' ) );

		switch ( $type ){
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				return wp_get_attachment_thumb_url( $img->ID );
				break;
			case 'video/mpeg':
			case 'video/mp4':
			case 'video/quicktime':
				return $base . "/video.png";
				break;
			case 'text/csv':
			case 'text/plain':
			case 'text/xml':
				return $base . "/text.png";
				break;
			default:
				return $base . "/document.png";
				break;
		}

	}


	/**
	 * The Js required for the Image uploading and attaching
	 *
	 *
	 * @uses added to 'admin_print_scripts' hook by self::__construct
	 *
	 */
	function js(){
		?>
		<script type="text/javascript">
			jQuery( function( $ ){
				if( !$( '.mvc-gallery ol' ).length ){
                    return;
                }

				$( '.mvc-gallery ol' ).sortable( {
					placeholder : 'sortable-placeholder'
				} );

				$( '.mvc-gallery .button-secondary' ).click( function( e ){
					var group = $( this );
					var ol = $( '[rel="mvc-gallery-' + group.data( "group" ) + '"]' );
					var custom_uploader = wp.media( {
						title : 'Add to ' + group.data( 'title' ),
						button : {
							text : 'Add to ' + group.data( 'title' )
						},
						library : {
							type : 'image'
						},
						multiple : true

					} ).on( 'select', function(){
						ol.find( '#uncheck-message' ).show();

						var items = custom_uploader.state().get( 'selection' );

						for( var i = 0; i < items.models.length; i++ ){

							var attachment = items.models[i].toJSON();

							ol.append( '<li>' + '<img src="' + attachment.url + '"/>' + '<input type="checkbox" name="mvc-gallery-' + group.data( "group" ) + '[]" value="' + attachment.id + '" checked="checked" />' + '<label><span>' + attachment.filename + '</span></label>' + '</li>' );
						}
					} ).open();

				} ); // end click()
			} );

		</script>
		<style type="text/css">
			.mvc-gallery ol li {
				cursor : move;
			}
		</style>
	<?php
	}


	/**
	 * Returns the featured image or the first image in the gallery
	 *
	 * @since 7.22.13
	 *
	 * @param        int          [optional] $post_id the id of the post
	 * @param string $size        the size of the image defaults to 'thumbnail'
	 * @param bool   $html        or object format defaults html
	 * @param bool   $useFeatured - if true a set featured image will override the the first gallery image
	 * @param string $galleryName the name of the gallery defaults to 'image-gallery'
	 */
	function getFirstImage( $postId = false, $size = 'thumbnail', $html = true, $useFeatured = false, $galleryName = 'image-gallery' ){

		//Use the current post's id of one was not sent
		if( !$postId ){
			global $post;
			if( !is_single() && !is_page() ){
				return false;
			}
			$postId = $post->ID;
		}

		//Check if the featured image should be used, then check if the post has a thumbnail
		if( $useFeatured ){
			if( has_post_thumbnail( $postId ) ){
				if( $html ){
					return get_the_post_thumbnail( $postId, $size );
				} else {
					$image[ 'ID' ] = get_post_thumbnail_id( $postId );
					$imageData     = $this->getImageData( $image[ 'ID' ], $size );

					return $imageData;
				}
			}
		}

		$img_args = array(
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => 'ASC',
			'orderby'        => 'menu_order ID',
			'numberposts'    => 1,
			'orderby'        => 'post__in',
			'post__in'       => get_post_meta( $postId, 'mvc-gallery-' . $galleryName, true ),
			'fields'         => 'ids'
		);

		$gallery_images = get_posts( $img_args );

		if( empty( $gallery_images ) ){
			return false;
		}

		//If just needs an html image return the image
		if( $html ){
			return wp_get_attachment_image( $gallery_images[ 0 ], $size );
		} else {
			$imageData = $this->getImageData( $gallery_images[ 0 ], $size );

			return $imageData;
		}
	}

}


