<?php
            /**
             * The Meta Box Generator
             * 
             * @since 4.7.0
             * 
             * @since 8.27.13
             * 
             * @uses construct as separate object per meta box
             *
             * @TODO Come up with a way to assign attributes to fields such as text
             * @TODO Ponder a way to allow for revision of meta data as well
             */
if( class_exists('MvcMetaBox') ) return;             
class MvcMetaBox extends MvcFramework{
      public $id; //Id of the meta box
      public $postTypes; //Array of post types the meta box should show up on    
      public $fields = array(); //Array of fields to show up in meta box
      public $label; //The Label of the Meta Box\
      public $args; //Created at construct
      public $description = false; //The Meta Box Description

        
      /**
       * Sets everything in motion. Should construct for each meta box
       * 
       * @param string $label - label of meta box
       * @param array $postTypes - post types to automatically show up on
       * 
       * @param array $args = 
       *             'priority'     => 'high',
       *             'position'     => 'normal',
       *             'fields'       => array(),
       *             'descriptions' => array(),
       *             'auto_generate => true //Allows for manually outputing the content via $this->metaBoxOutput()
       *  
       * @since 6.3.13
       * 
       */
      function __construct($label, $postTypes = array(), $args = array()){
          $defaults = array(
                 'priority'      => 'high',
                 'position'      => 'normal',
                 'descriptions'  => array(),
                 'auto_generate' => true
                 );
          $this->args = wp_parse_args($args, $defaults);
          
          if( isset( $this->args['fields'] ) ){
              $this->addFields( $this->args['fields'], $this->args['descriptions'] );
          }
          
          
          $this->id = $this->slug_format_human($label);
          $this->label = $label;
          
          if( !is_array( $postTypes ) ){
              $postTypes = array( $postTypes );
          }
          $this->postTypes = $postTypes;
          
          
          add_action('do_meta_boxes', array( $this, 'initMetaBox' ) );         
          add_action( 'save_post', array( $this, 'saveMetaData' ) );
      }   
      
      
      /**
       * Add a description for the entire meta box
       * 
       * @since 5.29.13
       * 
       * @param string $description - the description
       * @param bool   $append - to add to any existing (defaults to false );
       */
      function addDescription($description, $append = false ){
          if( $append ){
              $this->description .= $description;
          } else {
              $this->description = $description;
          }
          
      }
      
      
      /**
       * Init the meta boxes on all the specified Post Types
       * 
       * @since 10.9.13
       * 
       * @uses a bunch of the stuff set on __construct()
       */
      function initMetaBox(){
          if( !$this->args['auto_generate'] ) return;
          
          foreach( $this->postTypes as $type ){
            add_meta_box( $this->id, $this->label, array( $this, 'metaBoxOutput' ), $type, $this->args['position'], $this->args['priority'] );
          }
      }
      
      
      /**
       * Saves the Meta Data
       * 
       * @since 8.27.13
       */
      function saveMetaData($postId){
        global $post;

        //Make sure this is valid
        if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( wp_is_post_revision( $postId ) ) return;
        if ( !wp_verify_nonce( $_POST[$this->id.'_meta_box'], plugin_basename(__FILE__ ) ) ) return;
        
        
        
        foreach( $this->getFieldNames() as $field ){
            
            if( is_array( $_POST[$field] ) ){
                 $_POST[$field] = $this->MvcUtilites->arrayFilterRecursive( $_POST[$field] );
            }
            
            $data = apply_filters('mvc_meta_data_save_'.$field, $_POST[$field], $this );
            
            update_post_meta( $post->ID, $field, $data );
        }
        
        do_action( 'save_meta_data_'.$this->id, $this );

      }
      
      
      /**
       * Retrieve the meta data formated for the form
       * 
       * @since 7.18.13
       * @param init [$postId] - id of post (defaults to current post's id )
       * 
       * @return array
       */
      function getMetaData($postId = false ){
          global $post;
          if( !$postId ){
              $postId = $post->ID;
          }
          
          $meta = get_post_meta( $postId );
          
          foreach( $this->getFieldNames() as $field ){
              if( isset( $meta[$field][0] ) ){
                  $data[$field] = $meta[$field][0];
                  
                  //In case of repeater fields
                  if( ($unseralized = @unserialize($data[$field])) !== false ){
                      $data[$field] = $unseralized;
                  }
              } else {
                  $data[$field] = '';
              }
          }
          
          return $data;
      }
      
      
      /**
       * Retrieve all of the form fields keys sanitized for the form
       * 
       * @since 5.29.13
       * 
       * @return array
       */
      function getFieldNames(){
          foreach( $this->fields as $key => $field ){
             $fields[] = $this->getFieldName($field, $key);
          }
          
          return $fields;
      }
      
      
      /**
       * Add fields to the meta box
       * 
       * @uses Must be called before the do_meta_boxes hook
       * 
       * @param array $fields  => array( 
       *                  'checkbox_1' => %name%, 
       *                'image_1'    => array(
                                        'button_label' => ['Upload'],
                                        'name'         => [$name],
                                        'id'           => ['upload_image']
     * 
     *                  'select_%name%' => $args ( 
     *                              options        => array( %key% => %value )
     *                              selected       => [%value%]
     *                              id             => [%string%]
     *                              all_label      => [%string%] ) 
     *                  'textarea_1' => array( 
       *                                    %name%,
       *                                    [mce] => bool
       *                                    ),
       * 
     *                  'repeater_%name%' => array( 'checkbox_2' => %name%, 'textarea_1' => %name% ),
     *                  'button_%name%' => $args  => array(
     *                        'class' => %class%,
     *                        'value' => %value%,
     *                        'id'    => %id%
     *                        'onclick' => %onclick%
     *                        'extras'  => array( key => value pairs will be turned into attributes )
     *                      )
     *                  'hidden_%name'  => array( 
     *                                 'id' => %id%,
     *                                 'value' => %value%
     *                                  )
     *                  ),
       * 
       * @param array $descriptions = text to accompany the the field - html 0k
       *  * e.g. $descriptions = array( 'checkbox_1' => 'I am a checkbox' );
       * 
       * @param bool $append - to add to existing or start over (defaults to true)
       * 
       */
      function addFields($fields = array(), $descriptions = array(), $append = true ){
          if( $append ){
            foreach( $fields as $key => $field ){
               $this->fields[$key] = $field;   
            }
          } else {
              $this->fields = $fields;
          }
          
          
         $this->descriptions = $descriptions;
          
      }     
      
      
      /**
       * Outputs the Meta Box form and data
       * 
       * @since 7.2.13
       * 
       * @uses $this->addFields() must be used before this will run
       */
      function metaBoxOutput(){
            global $post;

            if( $this->description ){
                echo '<div class="description">'.$this->description.'</div>';
            }
            
             do_action( 'before_meta_box_content_'.$this->id, $this );
            
            
            if( empty( $this->fields ) ) return; 
            
            $data = $this->getMetaData();
      
            $output .= '<ul>';
    
            //Go through all the fields
            foreach( $this->fields as $key => $field ){
                
                 $output .= '<li style="list-style: none; margin: 0 0 15px 10px">';

                 $field_name = $this->getFieldName($field, $key);
                 
                 //get the id of form field
                 if( isset( $field['id'] ) ){
                     $id = $field['id'];
                 } else {
                     $id = $field_name;
                 }
                 
                 if( is_array( $field ) ){
                     $atts = $field;
                 } else {
                     $atts = array();  
                 }
                 

                 //labels  
                 if( strpos($key,'repeater') === false && strpos($key,'hidden') === false ){
                     if( !isset($atts['label']) ){  
                        $label = $this->human_format_slug($field_name);
                     } else {
                         $label = $field['label'];
                     }
                     $output .= sprintf('<label for="%s">%s : </label> ', $id, $label );
                 }
                 

                 //Descriptions
                 if( isset( $this->descriptions[$key] ) ){
                    $output .= sprintf('<span class="description">%s</span><br>', $this->descriptions[$key]);  
                 }

 
                     
                     
                 //Checkbox
                 if( strpos($key,'checkbox') !== false ){
                     $output .= $this->MvcForm->checkbox($field_name, $data[$field_name], false, $atts );
           
                 }
                 
                 //Image Upload Form
                 elseif( strpos($key, 'image_') !== false ){
                     $output .= $this->MvcForm->imageUploadForm($field_name, $data[$field_name], $atts, false );
                 }
                 
                 
                 //Button
                 elseif( strpos($key,'button_') !== false ){
                     $output .= $this->MvcForm->button($field_name, $field, false );
                 } 
                  
                    
                //Select Field  
                 elseif( strpos($key,'select_') !== false ){
                    if( $data[$field_name] == '' && !isset($field['selected']) ){
                         $field['selected'] = '';
                    } elseif( $data[$field_name] != '' ){
                       $field['selected'] = $data[$field_name];   
                    }

                    $output .= $this->MvcForm->select($field_name, $field, false );            
                
                } 
                
                //textarea field
                elseif( strpos($key,'textarea') !== false ){
                    
                    $output .= $this->MvcForm->textarea($field_name, $data[$field_name], $atts, false, $field);
                }
                
                
                //repeater field
                elseif( strpos($key,'repeater') !== false ){
              
                    $output .= $this->MvcForm->repeater($field_name, $field, $data[$field_name], array(), false);
                }
                
                //Hidden field
                elseif( strpos($key,'hidden_') !== false ){
              
                   $output .= $this->MvcForm->hidden($field_name, $field, false);
                   
                //Standard Text Field   
                } else {
                   $atts['value'] = $data[$field_name];
                   $atts['id']    = $id;

                   $output .= $this->MvcForm->text($field_name, $atts, false);
                }
            
            $output .=  '</li>';
        
        } //-- end foreach field
        
        $output .= '</ul>';   
        
        echo $output;

        do_action( 'after_meta_box_content_'.$this->id, $this );
        
        wp_nonce_field( plugin_basename( __FILE__ ), $this->id. '_meta_box', true );
        
          
      }
      
      
      /**
       * Returns a clean field name stripped of all prefixes
       * 
       * @since 5.29.13
       * 
       * @param array|string $field - the field
       * @param string       $key - the array key used by some elements
       * 
       * @return string
       */
      function getFieldName($field, $key){
           
         if( is_array( $field ) ){
              if( isset( $field['name'] ) ){
                        $field_name = $field['name'];
              } else {
                        $field_name = str_replace(  array(
                                                            'hidden_',
                                                            'repeater_',
                                                            'select_',
                                                            'button_',
                                                            'image_'
                                                           ),
                                                     array("","","",""),
                                                    $key
                        );
              }
         } else {
              $field_name = $field;
         }  
          
         return $field_name;
      }

}