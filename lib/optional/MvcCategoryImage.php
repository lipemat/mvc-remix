<?php

/**
 * Adds Category Icon Ability
 * 
 * @since 5.3.0
 * 
 * @since 6.25.13
 * 
 * @uses add_theme_support('category-icons');
 * @uses MvcCateoryImage::getImage($term_id = false);
 * 
 */
if( class_exists('MvcCategoryImage') ) return;   
class MvcCategoryImage extends MvcFramework{
        
        
       function __construct(){
         
           //Add the upload form
           add_action('category_add_form_fields', array ( $this , 'imageUploadForm'), 99 );
           add_action('edit_category_form_fields', array( $this , 'imageEditUploadForm' ), 99 );
           
           //Add the JQuery for the media uploader
           add_action('admin_head', array($this, 'mediaUploader') );
           
           //Save the category meta on add category - Genesis take care of this on edit
           add_action('created_category', array($this,'genesis_term_meta_save'), 10, 2 );
       } 
       
       
       
       /**
        * Get the image for a category. Defaults to current category
        * 
        * @since 6.25.13
        * @param mixed int|string|obj $term - the term to retrive image for
        * 
        * @param string $taxonmy - the taxonomy to retrieve the image from - (defaults to category)
        */
       function getImage($term = false, $taxonomy = 'category', $args = array()){
           
           $defaults = array( 
                        'html' => true
                        );
           $args = wp_parse_args($arg, $defaults);
          
           if( !$term ){
               if( $taxonomy == 'category' ){
                   $term_id = get_query_var('cat'); 
               } else {
                   $term = get_term_by('slug' , get_query_var('term'), $taxonomy);
                   $term_id = $term->term_id;  
               }
               
           } else {

               if( is_object($term) ){
                  if( isset( $term->term_id ) ){
                      $term_id = $term->term_id;
                  } else {
                      $term_id = $term->cat_ID;
                  }   
               } elseif( is_numeric($term) ){
                  $term_id = $term;   
               } else {
                    $term = get_term_by('name', $term, $taxonomy );   
                    $term_id = $term->term_id;
               }

           }
           $term_meta = (array) get_option( 'genesis-term-meta' );

           $output =  $term_meta[$term_id]['category-image'];
           
           if( $args['html'] ){
               $output = sprintf( '<img src="%s" />', $output );
           }

          return $output;
       }
       
       
       
       
        /**
        * Form for uploading the Category Image
        * 
        * @since 6.24.13
        * @uses added to the 'category_add_form_fields' and 'edit_category_form_fields' by self::__construct()
        */
       function imageEditUploadForm($args){
           
           if( empty( $args->meta['category-image'] ) ){
               $value = 'Click to Upload';
               $icon = '';
           } else {
               $value = 'Click to Change';
               $icon = sprintf('<img src="%s" width="40px" />', $args->meta['category-image'] );
           }
           
           
           ?><tr>
                <th scope="row" valign="top">
                    <label for="slug">Category Image:</label>
                       
                </th>
            <td>
                <?php echo $icon; ?>
                <input type="text" name="meta[category-image]" id="category-image" value="<?php echo $args->meta['category-image']; ?>" size="40"/>
                <input type="button" rel="category-image" value="<?php echo $value;?>" class="button-secondary upload-image"/>
            </td>
        </tr>
          
           <?php

       }
       
       
       
       
       
       
       /**
        * Form for uploading the Category Image
        * 
        * @since 6.24.13
        * @uses added to the 'category_add_form_fields' and 'edit_category_form_fields' by self::__construct()
        */
       function imageUploadForm($args){
           ?>
           <div>
              <p>
               Category Image:
               <input type="text" name="meta[category-image]" id="category-image" value="" />
               <input type="button" 
                    rel="category-image" 
                    value="Click to Upload" 
                    class="button-secondary upload-image"
               />
               </p>
           </div>
           <?php

       }

 
     /**
     * Exact Replica of the genesis Version but designed to work with Ajax Calls
     *
     * @since 6.24.13
     * @uses save the category meta on add
     */
      
      function genesis_term_meta_save( $term_id, $tt_id ) {

            $term_meta = (array) get_option( 'genesis-term-meta' );

            $term_meta[$term_id] = isset( $_POST['meta'] ) ? (array) $_POST['meta'] : array();

            if ( !current_user_can( 'unfiltered_html' ) && isset( $term_meta[$term_id ]['archive_description'] ) )
                $term_meta[$term_id]['archive_description '] = genesis_formatting_kses( $term_meta[$term_id ]['archive_description '] );

            update_option( 'genesis-term-meta', $term_meta );
       }


   
}
    