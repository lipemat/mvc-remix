<?php

        /**
         * Creates the youtube Widget
         * @author Mat Lipe
         * @since 7.30.13
         */

class Youtube extends WP_Widget{
       
       function __construct(){
           $widget_ops = array(
              'classname'   => 'youtube',
               'description' => __( 'Outputs a Youtube Video', 'genesis' ),
            );
            $control_ops = array(
              'id_base' => 'youtube',
               'width'   => 505,
               'height'  => 350,
           );

          $this->WP_Widget( 'youtube', 'Youtube', $widget_ops, $control_ops );
        
       }
       
       
       /**
        * The Output of the Widget
        * 
        * @since 04.23.13
        * @see WP_Widget::widget()
        */
       function widget($args, $instance){
          extract($args);    
              
          echo $before_widget;                 
            if( !empty($instance['title']) ){
                echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;
            }                
            
            ?>
            <style type="text/css">
                .video-container {
                    position: relative;
                    padding-bottom: 50%;
                    padding-top: 30px; 
                    overflow: hidden;
                }

                .video-container iframe,
                .video-container object,
                .video-container embed {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                }
            </style>
            
            <div class="video-container">
            <iframe
                src="http://www.youtube.com/embed/<?php echo $instance["youtube-id"];?>" frameborder="0" allowfullscreen>
            </iframe>
            </div>
          <?php
          echo $after_widget;
       }
       
       
       /**
        * Updates the Instances of the Widget
        * 
        * since 7.30.13
        * @see WP_Widget::update()
        */
       function update($new, $old){
           
           $new['youtube-id'] = end( explode('/', $new['youtube-id'] ) );
 
           if( strpos ($new['youtube-id'] ,'watch?v=') !== false ){
                $new['youtube-id'] = str_replace('watch?v=', '', $new['youtube-id']);
                 
           }
           
           if( strpos ($new['youtube-id'] ,'#aid') !== false ){
               $new['youtube-id'] = reset( explode('#aid', $new['youtube-id']) );
           }
           
           return $new;
       }
       
       
       /**
        * Ouputs the Form on the Site
        * 
        * @see WP_Widget::form()
        * since 04.23.13
        */
       function form($instance){
           ?><p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label><br />
            <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'youtube-id' ); ?>">
               Youtube ID:</label><br />
            <input type="text" id="<?php echo $this->get_field_id( 'youtube-id' ); ?>" name="<?php echo $this->get_field_name( 'youtube-id' ); ?>" value="<?php echo esc_attr( $instance['youtube-id'] ); ?>" class="widefat" />
        </p>
        
        
        <?php
        
        
        
        
           
       } 
}
    