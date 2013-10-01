<?php
/**
 * Adds ability to upload images into a gallery group
 * * Creates meta box and handles all uploding needs
 * 
 * @uses new MvcGallery($postTypes);
 * @param array $postTypes - The post types to use this on
 * 
 * @since 4.5.0
 * 
 * @since 7.22.13
 * 
 * @TODO Get the Add to Gallery Buttons to Work on the Upload Screen
 */
class MvcGallery extends MvcFramework{
       private $post_types = array();
       private $groups = array();
       
       /**
        * Sets everything in motion
        * 
        * @param array $postTypes - the posts types to add the functionalty to
        * @param array [$groups] - the different groups of images default to gallery 
        * @uses __construct($postTypes)
        * @since 4.5.0
        * 
        * @since 6.18.13
        */
       function __construct($postTypes = array('post','page'), $groups = array('gallery')){
           
            if( !is_array( $postTypes ) ){
                $postTypes = array( $postTypes );   
            }
           
             $this->post_types = $postTypes;
             $this->groups = $groups;
           
             add_action('admin_print_scripts', array( $this, 'js' ), 999 );  
             wp_enqueue_script('jquery-ui-sortable');  
             wp_enqueue_script('media-upload');
             wp_enqueue_script('thickbox');
             wp_enqueue_style('thickbox');
             
             add_action('admin_menu', array($this, 'metaBoxSetup'), 99 );

             add_filter('attachment_fields_to_edit', array($this, 'addAttachmentField'), 20, 2);    

             add_action('save_post', array($this, 'save_post'), 99 );       
          //   add_filter('the_post', array($this, 'the_post'));  -- Deprecated until I find a use for it        
       }
       
 
       /**
       * Register Meta Boxes
       **/   
       function metaBoxSetup(){
              foreach( $this->post_types as $pt ){
                   foreach($this->groups as $group){
                           add_meta_box($group, $this->MvcString->human_format_slug($group), array($this, 'metaBoxOutput'), $pt, 'advanced', 'high' );
                     }
               }
                  
       }
       
       
              /**
        * Returns and an array of the groups with slugs as keys
        * 
        * @since 5.15.0
        * @uses $this->groups;
        */
       function cleanGroups(){
           static $clean = false;
           if( $clean) return $clean;
           
           $clean = array();
           foreach( $this->groups as $group ){
               $clean[$this->MvcString->human_format_slug($group)] = $group;
           }
           
           return $clean;
           
           
       }

       /**
        * Add the "Add to" section of the image editing box
        * 
        * @since 5.9.13
        * @uses added to the 'attachment_fields_to_edit' filter by self::construct
        */
       function addAttachmentField($form_fields, $post ){
              $calling_post_id = 0 ;
              if( isset( $_GET['post_id' ] )){
                     $calling_post_id = absint($_GET['post_id']);
              } elseif (isset ($_POST ) && count($_POST)){ // Like for async-upload where $_GET['post_id'] isn't set
                     $calling_post_id = $post->post_parent;  
              }
              
              if(!$calling_post_id) return $form_fields;  
              if( !isset( $_REQUEST['mvc_gallery']) ) return $form_fields; 
                 
    
              $form_fields["{$post->post_type}-{$post->ID}-mvc-gallery"] = array(
                     'label' => 'Add To',
                     'input' => 'html',
                     'html'  => '<a href="#" class="mvc-gallery button-primary">Attach</a>'
              );

              return $form_fields;      
       }      
      
       
      
      
      
       /**
       * Saves the Gallery Images to the Post Meta
       * 
       * @since 5.9.13
       * @uses called by the 'save_post' action
       *
       */           
       function save_post($post_id ){
              global $post, $wp_post_types;
              

              switch(true ){
                     case !wp_verify_nonce(@$_POST[__CLASS__.'_noncename'], __CLASS__):
                     case defined('DOING_AUTOSAVE ') && DOING_AUTOSAVE:
                     case !current_user_can('edit_post', $post_id):
                           return $post_id;
              }

    

              foreach($this->groups as $group){
                     if( isset( $_POST['mvc-gallery-'.$group] ) ){
                        update_post_meta($post_id, 'mvc-gallery-'.$group, $_POST['mvc-gallery-'.$group] );
                     } else {
                        delete_post_meta($post_id, 'mvc-gallery-'.$group, $_POST['mvc-gallery-'.$group] );
                     }
              }
                           
              return $post_id;
       }



       /**
       * Add Attachments to Post
       * 
        * @deprecated
       */
       function the_post($post ){
              if( !in_array( $post->post_type, $this->post_types ) ) return;
     
              foreach($this->groups as $group){
                     $d = new WP_Query(array (
                           'post_type '          => 'attachment',
                           'post_status '        => 'inherit',
                           'post__in '           =>  (array)get_post_meta($post->ID, 'mvc-gallery-'. $group, true)
                     ));   
                     $imgs = $d->query['post__in'];
                     foreach($d->posts as $p){
                           @$imgs[array_search($p->ID, $imgs)] = $p ;
                     }
                     $d->posts = $imgs;  
                     $d->rewind_posts();
                     $post->{$group} = $d;
              }               
              
      
             
       }
       
       
              /**
        * Output of the Gallery Meta Box
        * * Displays the existing Gallery Images and has links to add more
        * 
        * @since 6.18.13
        * @uses called by self::metaBoxSetup
        * 
        * @param obj $post - the current post
        * @param array $group - the gallery group data
        */
       function metaBoxOutput($post, $group){
             $tbody = '';
      
             $images = get_post_meta($post->ID, 'mvc-gallery-'.$group['id'], true);

             ?>
               <div class="mvc-gallery">
                    <p>
                     <a class="thickbox button-secondary" href="media-upload.php?post_id=<?php echo $post->ID; ?> &amp;mvc_gallery=1&amp;tab=library&amp;TB_iframe=1&amp;width=640&amp;height=299" title="Add Attachment">Add Image from Library</a>
                   </p>
                   <?php if(empty ($images)){
                            ?><p id="uncheck-message" style="display:none">Uncheck an image to remove it.</p><?php
                        } else {
                            ?><p>Uncheck an image to remove it.</p><?php
                        }
                  ?>
                  <div class="scroll">
                      <ol><?php 
                          if(!empty ($images)){
                               foreach($images as $image){
                                    $image = get_post($image);
                                    ?>
                                    <li>
                                       <img src="<?php echo wp_get_attachment_thumb_url($image->ID); ?>" />
                                        <input 
                                              type="checkbox" 
                                              checked="checked" 
                                              name="mvc-gallery-<?php echo $group['id']; ?>[]" 
                                              value="<?php echo $image->ID; ?>"
                                              id="c<?php echo $image->ID; ?>"
                                            
                                          />
                                         <label for="c<?php echo $image->ID; ?>">           
                                             <span> </span><?php echo $image->post_title; ?>
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
                 name="<?php echo __CLASS__ ;?>_noncename" 
                 value="<?php echo wp_create_nonce(__CLASS__ ); ?>" 
              />
                     
                     <style type="text/css">
                           .mvc-gallery p{text-align: left;}
                           .mvc-gallery.scroll{max-height:300px; height:auto !important; height:300px; overflow: auto;}
                           .mvc-gallery li{border-bottom: 1px solid #ccc;}
                           .mvc-gallery img{width: 75px; margin: 0 10px 10px 0; vertical-align: top;}
                           
                    
                      <?php global $is_IE; 
                       if( !$is_IE ){
                         /*(*
                          * @TODO  get the checkbox to work consitently when using javascript
                          *  ?>   
                         .mvc-gallery input[type="checkbox"] {
                                display:none;
                            }
                         .mvc-gallery input[type="checkbox"] + label span {
                                   display:inline-block;
                                    width:19px;
                                    height:19px;
                                    margin:-1px 4px 0 0;
                                    vertical-align:middle;
                                    background:url(<?php echo THEME_DIR; ?>lib/img/check_radio_sheet.png) left top no-repeat;
                                    cursor:pointer;
                            }
                            .mvc-gallery input[type="checkbox"]:checked + label span {
                                 background:url(<?php echo THEME_DIR; ?>lib/img/check_radio_sheet.png) -19px top no-repeat;  
                            }
                       <?php
                          * **/ } ?>
                     </style>
             <?php
       }
       
       /**
        * The Js required for the Image uploading and attaching
        * 
        * @since 5.9.13
        * @uses added to 'admin_print_scripts' hook by self::__construct
        */
       function js(){
           ?><script type="text/javascript">
                var mvcGallery = false;

                jQuery(function($){
                    $('.mvc-gallery a').click (function(){
                        mvcGallery = $(this);
                    });
                    
                    $('input[type="checkbox"]:checked + label span').addClass('checked');
                    
                    $('.mvc-gallery ol').sortable({placeholder: 'sortable-placeholder'});
                    
                    $('a.mvc-gallery').each(function(){
                        //check for opener of attachPro
                         win = window.dialogArguments || opener || parent || top;

                        if(!win.mvcGallery) return;
             
                        //Change the link text to the title of the meta box
                        $(this).text(win.mvcGallery.closest('.postbox').find('h3').text());

                        $(this).click(function(){
                            t = $(this).closest('.media-item' );
                            win.mvcGalleryAdd (
                                t.attr('id').replace(/\D/g,''),
                                t.find ('img.pinkynail:first').attr('src' ),
                                t.find ('tr.post_title input').val()
                            );
                            t.find ('.describe-toggle-off').click();
                        });
                    });
                });
                

                //Add the new images to the meta box
                function mvcGalleryAdd(id, tn , l ){
                    g = mvcGallery.closest('.postbox' );
                    g.find('#uncheck-message').show();
                    g.find ('ol') .append(
                        '<li><img src="'+tn+'"/> ' +
                        '<input type="checkbox" name="mvc-gallery-' +g.attr('id')+'[]" value="'+id+'" checked="checked" />'+
                        '<label><span></span>'+l+'</label>'+
                        '</li>'
                    );
                };
       
           </script>
           <?php
       }


    /**
     * Returns the featured image or the first image in the gallery
     * @since 7.22.13
     * @param int [optional] $post_id the id of the post
     * @param string $size the size of the image defaults to 'thumbnail'
     * @param bool $html or object format defaults html
	 * @param bool $useFeatured - if true a set featured image will override the the first gallery image
	 * @param string $galleryName the name of the gallery defaults to 'image-gallery'
     */
    function getFirstImage( $postId = false, $size = 'thumbnail', $html = true, $useFeatured = false, $galleryName = 'image-gallery' ){

        //Use the current post's id of one was not sent
        if( !$postId ){
            global $post;
            if( !is_single() && !is_page() ) return false;
            $postId = $post->ID;
        }
    
		//Check if the featured image should be used, then check if the post has a thumbnail
		if($useFeatured){
	        if( has_post_thumbnail($postId) ){
	            if( $html ){
	                return get_the_post_thumbnail( $postId, $size );
	            } else {
	            	$image['ID']  = get_post_thumbnail_id( $postId );  
					$imageData = $this->get_image_data($image['ID'], $size);
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
            'orderby'		 => 'post__in',
            'post__in'		 => get_post_meta( $postId, 'mvc-gallery-'.$galleryName, true ),
		    'fields'         => 'ids'
            );  
		  
		 
        $gallery_images = get_posts( $img_args );
	
        if( empty( $gallery_images ) ){
            return false;
        }

        //If just needs an html image return the image
        if( $html ){
            return wp_get_attachment_image($gallery_images[0], $size );			
        } else {
        	$imageData = $this->get_image_data($gallery_images[0], $size );
            return $imageData;
        }
    }

}


