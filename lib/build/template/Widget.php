<?php

        /**
         * Creates the %name% Widget
         * @author Mat Lipe
         * @since %date%
         */

class %class-name% extends WP_Widget{
       
       function __construct(){
           $widget_ops = array(
              'classname'   => '%name%',
               'description' => '%name%',
            );
            $control_ops = array(
              'id_base' => '%name%',
               'width'   => 505,
               'height'  => 350,
           );

          $this->WP_Widget( '%name%', '%name%', $widget_ops, $control_ops );
        
       }
       
       
       /**
        * The Output of the Widget
        * 
        * @since %date%
        * @see WP_Widget::widget()
        */
       function widget($args, $instance){
          extract($args);    
              
          echo $before_widget;                 
            if( !empty($instance['title']) ){
                echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;
            }                

          echo $after_widget;
       }
       
       
       /**
        * Updates the Instances of the Widget
        * 
        * since %date%
        * @see WP_Widget::update()
        */
       function update($new, $old){
           return $new;
       }
       
       
       /**
        * Ouputs the Form on the Site
        * 
        * @see WP_Widget::form()
        * since %date%
        */
       function form($instance){
         ?><p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                Title:
            </label>
            <br />
            <input type="text" 
                id="<?php echo $this->get_field_id( 'title' ); ?>" 
                name="<?php echo $this->get_field_name( 'title' ); ?>" 
                value="<?php echo esc_attr( $instance['title'] ); ?>" 
                class="widefat" 
            />
          </p><?php
           
       } 
}
    