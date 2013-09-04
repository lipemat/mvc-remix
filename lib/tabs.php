<?php 

/**
 * Can be brought in by the framework by moving this file to the includes directory
 * @uses select the tabbed template on the post editing screen
 * @uses Advanced Custom Fields Plugin
 * @since 11.14.12
 */
if(function_exists( "register_field_group"))
{
function mvc_add_tabbed_script(){
    wp_enqueue_script( 'jquery-ui-tabs' );   
}
add_action('wp_enqueue_scripts','mvc_add_tabbed_script');    
 
 
register_field_group( array (
  'id'   => '5080163318c15',
  'title' => 'Tabbed Pages',
  'fields' =>
  array (
    0 =>
    array (
      'key' => 'field_4f6251615bbc9',
      'label' => 'Tab 1 Label',
      'name' => 'tab_1_label',
      'type' => 'text',
      'default_value' => '',
      'formatting' => 'html',
      'instructions' => 'This is the label for the first tab',
      'required' => '0',
      'order_no' => '0',
    ),
    1 =>
    array (
      'key' => 'field_4f6251615c388',
      'label' => 'Tab 1 Content',
      'name' => 'tab_1_content',
      'type' => 'wysiwyg',
      'toolbar' => 'full',
      'media_upload' => 'yes',
      'instructions' => 'This is the content for the first tab',
      'required' => '0',
      'order_no' => '1',
    ),
    2 =>
    array (
      'key' => 'field_4f62517c00f05',
      'label' => 'Tab 2 Label',
      'name' => 'tab_2_label',
      'type' => 'text',
      'default_value' => '',
      'formatting' => 'html',
      'instructions' => 'This is the label for the second tab',
      'required' => '0',
      'order_no' => '2',
    ),
    3 =>
    array (
      'key' => 'field_4f62526687165',
      'label' => 'Tab 2 Content',
      'name' => 'tab_2_content',
      'type' => 'wysiwyg',
      'toolbar' => 'full',
      'media_upload' => 'yes',
      'instructions' => 'This is the content for second tab',
      'required' => '0',
      'order_no' => '3',
    ),
    4 =>
    array (
      'key' => 'field_4f625266877c7',
      'label' => 'Tab 3 Label',
      'name' => 'tab_3_label',
      'type' => 'text',
      'default_value' => '',
      'formatting' => 'html',
      'instructions' => 'This is the label for the third tab',
      'required' => '0',
      'order_no' => '4',
    ),
    5 =>
    array (
      'key' => 'field_4f62526687e0c',
      'label' => 'Tab 3 Content',
      'name' => 'tab_3_content',
      'type' => 'wysiwyg',
      'toolbar' => 'full',
      'media_upload' => 'yes',
      'instructions' => 'This is the content for third tab',
      'required' => '0',
      'order_no' => '5',
    ),
    6 =>
    array (
      'key' => 'field_4f6252668845c',
      'label' => 'Tab 4 Label',
      'name' => 'tab_4_label',
      'type' => 'text',
      'default_value' => '',
      'formatting' => 'html',
      'instructions' => 'This is the label for the fourth tab',
      'required' => '0',
      'order_no' => '6',
    ),
    7 =>
    array (
      'key' => 'field_4f62526688b74',
      'label' => 'Tab 4 Content',
      'name' => 'tab_4_content',
      'type' => 'wysiwyg',
      'toolbar' => 'full',
      'media_upload' => 'yes',
      'instructions' => 'This is the content for fourth tab',
      'required' => '0',
      'order_no' => '7',
    ),
    8 =>
    array (
      'key' => 'field_4f625266891bd',
      'label' => 'Tab 5 Label',
      'name' => 'tab_5_label',
      'type' => 'text',
      'default_value' => '',
      'formatting' => 'html',
      'instructions' => 'This is the label for the fifth tab',
      'required' => '0',
      'order_no' => '8',
    ),
    9 =>
    array (
      'key' => 'field_4f62526689917',
      'label' => 'Tab 5 Content',
      'name' => 'tab_5_content',
      'type' => 'wysiwyg',
      'toolbar' => 'full',
      'media_upload' => 'yes',
      'instructions' => 'This is the content for fifth tab',
      'required' => '0',
      'order_no' => '9',
    ),
    10 =>
    array (
      'label' => 'Tab 6 Label',
      'name' => 'tab_6_label',
      'type' => 'text',
      'default_value' => '',
      'formatting' => 'html',
      'instructions' => '',
      'required' => '0',
      'key' => 'field_4f737e1034f5e',
      'order_no' => '10',
    ),
    11 =>
    array (
      'label' => 'Tab 6 Content',
      'name' => 'tab_6_content',
      'type' => 'wysiwyg',
      'toolbar' => 'full',
      'media_upload' => 'yes',
      'instructions' => '',
      'required' => '0',
      'key' => 'field_4f737e1035730',
      'order_no' => '11',
    )
  ),
  'location' => array (
              'rules' =>
                  array (
                      0 =>
                          array (
                              'param' => 'page_template',
                              'operator' => '==' ,
                              'value' => 'tabbed-template.php',
                              'order_no' => '0' ,
                          ),
                 ),
             'allorany' => 'all',
   ),
   'options' =>
               array (
                   'position' => 'normal',
                   'layout' => 'default',
                   'hide_on_screen' => array (
                    ),
  ),
  'menu_order' => 0,
));
}
