<?php

    /**
     * Creates a social profiles icons Widget which allows for overwriting the image icons
     * @since 6.14.13
     * @uses automatically registerd in the standar widget area
     * @see Vimm Profiles Widget
     * 
     * @filters apply_filters('vimm-profiles-data', $data, $instance)
     * @filters - adding an image the theme's images folder with the same name as any of these will overide it
     *
     */
class Vimm_Profiles_Widget extends WP_Widget {
    
     private $types = array(
                    'RSS',
                    'Twitter',
                    'Facebook',
                    'GooglePlus',
                    'Linkedin',
                    'YouTube',
                    'Flickr',
                    'Delicious',
                    'StumbleUpon',
                    'Digg',
                    'MySpace',
                    'Pintrest',
                    'Email'
                    );
    
    

	function __construct() {
		$widget_ops = array( 'classname' => 'vimm-profiles', 'description' => __('Displays Social Profile links as icons', 'vimm_profiles') );
		$this->WP_Widget( 'vimmprofiles', __('Vimm Profiles', 'vimm_profiles'), $widget_ops );
	}

	var $plugin_imgs_url;


    /**
     * Builds the data Array of all avaialable profile images
     * 
     * 
     * @filters apply_filters('vimm-profiles-data', $data, $instance)
     * @since 6.14.13
     */
	function vimm_profiles_fields_array( $instance = array() ) {

		$this->plugins_imgs_url = THEME_DIR .'lib/widgets/vimm-profiles-widget/images/';
		
		
		//Until new sets are added
		$instance['icon_set'] = 'default';
        
        //Create the data for the all images
        foreach( $this->types as $type ){
               $data[$type]['title'] = $type. ' URL';
               $data[$type]['img'] = sprintf( '%s/%s_%s.png', $this->plugins_imgs_url.$instance['icon_set'], $type, $instance['size'] );
               $data[$type]['img_widget'] = sprintf( '%s/%s_%s.png', $this->plugins_imgs_url.$instance['icon_set'], $type, $instance['size']);
               $data[$type]['file'] = sprintf( '%s_%s.png', $type, $instance['size']);
               $data[$type]['img_title'] = $type;  
        }
        return apply_filters('vimm-profiles-data', $data, $instance);
      
	}



    /**
     * The Output of the Widget
     * 
     */
    function widget($args, $instance) {

        extract($args);

        $instance = wp_parse_args($instance, array(
            'title' => '',
            //'new_window' => 0,
            //'icon_set' => 'default',
            'size' => '24x24'
        ) );

        echo $before_widget;

            if ( ! empty( $instance['title'] ) )
                echo $before_title . $instance['title'] . $after_title;

            foreach ( $this->vimm_profiles_fields_array( $instance ) as $key => $data ) {
                if ( ! empty ( $instance[$key] ) ) {
                    if( file_exists(THEME_FILE_DIR.'images/'.$data['file']) ){
                         $data['img'] = IMAGE_DIR.$data['file'];
                    }
                    
                    printf( '<a href="%s" target="_blank"><img src="%s" alt="%s"/></a>', esc_url( $instance[$key] ), esc_url( $data['img'] ), esc_attr( $data['img_title'] ) );
                }
            }

        echo $after_widget;

    }

	
	
	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	
    
    
	/**
     * Admin Form
     * 
     * @filters - adding an image the theme's images folder with the same name as any of these will overide it
     * 
     */
	function form($instance) {
		$instance = wp_parse_args($instance, array(
			'title' => '',
			//'new_window' => 0,
			//'icon_set' => 'default',
			'size' => '24x24'
		) );
?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'vimm_profiles'); ?>:</label><br />
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" />
		</p>
		
		<!-- 
		<p><label><input id="<?php echo $this->get_field_id( 'new_window' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'new_window' ); ?>" value="1" <?php checked( 1, $instance['new_window'] ); ?>/> <?php esc_html_e( 'Open links in new window?', 'vimm_profiles' ); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('icon_set'); ?>"><?php _e('Icon Set', 'vimm_profiles'); ?>:</label>
			<select id="<?php echo $this->get_field_id('icon_set'); ?>" name="<?php echo $this->get_field_name('icon_set'); ?>">
				<option style="padding-right:10px;" value="default" <?php selected('default', $instance['icon_set']); ?>><?php _e('Default', 'vimm_profiles'); ?></option>
				<option style="padding-right:10px;" value="circles" <?php selected('circles', $instance['icon_set']); ?>><?php _e('Circles', 'vimm_profiles'); ?></option>
				<option style="padding-right:10px;" value="denim" <?php selected('denim', $instance['icon_set']); ?>><?php _e('Denim', 'vimm_profiles'); ?></option>
				<option style="padding-right:10px;" value="inside" <?php selected('inside', $instance['icon_set']); ?>><?php _e('Inside', 'vimm_profiles'); ?></option>
				<option style="padding-right:10px;" value="sketch" <?php selected('sketch', $instance['icon_set']); ?>><?php _e('Sketch', 'vimm_profiles'); ?></option>
			</select>
		</p>
		
		 -->

		<p>
			<label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Icon Size', 'vimm_profiles'); ?>:</label>
			<select id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>">
				<option style="padding-right:10px;" value="24x24" <?php selected('24x24', $instance['size']); ?>><?php _e('Mini', 'vimm_profiles'); ?> (24px)</option>
				<option style="padding-right:10px;" value="32x32" <?php selected('32x32', $instance['size']); ?>><?php _e('Small', 'vimm_profiles'); ?> (32px)</option>
				<option style="padding-right:10px;" value="48x48" <?php selected('48x48', $instance['size']); ?>><?php _e('Large', 'vimm_profiles'); ?> (48px)</option>
			</select>
		</p>

		<p><?php _e('Enter the URL(s) for your various social profiles below. If you leave a profile URL field blank, it will not be used.', 'vimm_profiles'); ?></p>

<?php

		foreach ( $this->vimm_profiles_fields_array( $instance ) as $key => $data ) {
		    //Swap the file for the one in child theme if exists
            if( file_exists(THEME_FILE_DIR.'images/'.$data['file']) ){
                $data['img_widget'] = IMAGE_DIR.$data['file'];
            }
            
			echo '<p>';
			printf( '<img style="float: left; margin-right: 3px;" src="%s" title="%s" />', $data['img_widget'], $data['img_title'] );
			printf( '<label for="%s"> %s:</label>', esc_attr( $this->get_field_id($key) ), esc_attr( $data['title'] ) );
			
			//Turn email into mailto
			if( $key == 'email'){ 
			    if( is_email( $instance[$key] ) ){
			        $em = 'mailto:' . $instance[$key];
			    } else {
			        $em = esc_url( $instance[$key], 'mailto');
			    }
			    printf( '<input id="%s" name="%s" value="%s" style="%s" />', esc_attr( $this->get_field_id($key) ), esc_attr( $this->get_field_name($key) ), $em, 'width:65%;' );     
			} else {
			    printf( '<input id="%s" name="%s" value="%s" style="%s" />', esc_attr( $this->get_field_id($key) ), esc_attr( $this->get_field_name($key) ), esc_url( $instance[$key] ), 'width:65%;' );
			}
			
			echo '</p>' . "\n";
		}

	}
}