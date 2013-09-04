<?php 


/**
 * Creates a widget which lists all child pages under a selected parent
 * @since 9/5/12
 * @uses called by widgets/init.php
 * @author mat Lipe <mat@vimm.com>
 *
 */

class VIMM_page_list extends WP_Widget{
    
    function __construct() {
    
    
        $widget_ops = array(
                'classname'   => 'page-lists',
                'description' => 'Lists Selected Page and all child pages.',
        );
        
        
        $control_ops = array(
            'width'    =>  350
        );
         
        $this->WP_Widget( 'vimm-page-lists', 'VIMM Page Lists', $widget_ops, $control_ops);
    
    }
    
    /**
     * (non-PHPdoc)
     * @see WP_Widget::form()
     */
    function form($instance){

        $pages = get_pages ( array(
                'sort_column' => 'post_title',
                'hierarchical' => 0,
                'parent'       => 0,
                'sort_order'   => 'ASC'
            )
        );
        
        ?>
        <h4>Page to Display With Children:</h4 >
        <select name=<?php echo $this->get_field_name( 'selected-page' ); ?> id="<?php echo $this->get_field_id( 'selected-page' ); ?>">
        <?php
        foreach( $pages as $page ){
            printf( '<option value="%s" %s>%s</option>',
                    $page->ID, selected( $instance['selected-page'], $page->ID, false), $page->post_title );
        }
        ?></select><?php 
        

    }
    
    /**
     * 
     * @param  $new_instance
     * @param $old_instance
     */
    function update($new_instance, $old_instance){
              return $new_instance;
    }
    
    /**
     * 
     * @param  $args
     * @param  $settings
     */
    function widget( $args, $settings ){
        extract( $args );
        
        echo $before_widget;
        
        $args = array(
                'include' => $settings['selected-page'],
                'title_li' => false
        );
       
        ?><div class="page-list-parent"><?php 
                wp_list_pages( $args );
        ?></div><?php 
        
        $args = array(
                'child_of'=> $settings['selected-page'],
                'title_li' => false
                );
        
        ?><div class="page-list-children"><?php 
                wp_list_pages( $args );
        ?></div><?php 

        echo $after_widget;
 
    }
    
    
}

