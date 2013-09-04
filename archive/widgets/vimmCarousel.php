<?php
/**
 * Creates a widget for Carousels
 * @since 4.12.13
 * @uses called by widgets/init.php
 * @author mat Lipe <mat@vimm.com>
 * 
 *
 */
class vimmCarousel extends WP_Widget{
    
    var $post_type      = array('project'); //Set This to the post Types to Show up on
    var $title          = 'Featured Projects'; //Set this to a leading title for the Carousel like "featured ARticles"
    var $taxonomy       = 'project_category'; //Set this to a Taxonomy to allow filtering by - False to remove this option
    var $taxonomy_label = 'Category'; //set this to the label shown in the widget
    
    function __construct() {
        add_action('wp_enqueue_scripts', array( $this, 'addJs') );
    
        $widget_ops = array(
                'classname'   => 'vimm-carousel',
                'description' => 'Displays the Carousel',
        );
   
        $this->WP_Widget( 'vimm-carousel', $this->title.' Carousel', $widget_ops);
        
        //Stuff For Meta Boxes
        add_action( 'do_meta_boxes', array( $this, 'metaBoxes') );
        add_action( 'save_post', array( $this, 'saveMeta') );
    
    }
    
    
    /***
     * queue the appropriate js scripts
     * */
    function addJs(){
       
    }
    
    
    
    /**
        * Setup the Meta Boxes
        * @since 12.28.12
        */
       function metaBoxes(){
           if( is_array( $this->post_type ) ){
               foreach( $this->post_type as $type ){
                      add_meta_box('our-services-carousel', $this->title.' Carousel', array( $this, 'metaBox'), $type, 'side', 'core');     
               }
           } else {
                   add_meta_box('our-services-carousel', $this->title.' Carousel', array( $this, 'metaBox'), $this->post_type, 'side', 'core');
           }
           
       }
       
    /**
     * Save any custom Meta Data
     * @since 1.28.13
     */
    public function saveMeta(){
        global $post;
         //Make sure this is valid
         if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
             return;
 
         update_post_meta( $post->ID, 'carousel', $_POST['carousel'] );
         update_post_meta( $post->ID, 'carousel-title', $_POST['carousel-title'] );
    }
       
      /**
       * Meta checkbox for adding to Carousel
       * @since 1.28.13
       */
      function metaBox($post){
         ?>
         <p>Add to Carousel: <input type="checkbox" name="carousel" value="1" 
             <?php checked(get_post_meta( $post->ID, 'carousel', true ) ); ?> />
         </p>
         <p>Carousel Title: <input type="text" name="carousel-title" value="<?php echo get_post_meta( $post->ID, 'carousel-title', true ); ?>"/>
         </p>
         <?php 
         
     }
    
    
    
    /**
     * (non-PHPdoc)
     * @see WP_Widget::form()
     */
    function form($instance){
        $form = new MvcForm;
        
        ?><p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label><br />
            <?php echo $form->text($this->get_field_name('title'), $instance['title'], $this->get_field_id('title') ); ?>
        </p>
        
        <?php if( $this->taxonomy ){ ?>
        <p>
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php echo $this->taxonomy_label;?>:</label><br />
            
            <?php wp_dropdown_categories(array( 
                                              'taxonomy'        => $this->taxonomy,
                                              'selected'        => $instance['category'],
                                              'name'            => $this->get_field_name('category'),
                                              'id'              => $this->get_field_id('category'),
                                              'show_option_all' => ' All ',
                                              'hide_empty'      => false
                                            ) );
        ?></p>
        
        <?php } //-- End if Taxonomy ?>
          <p>
              
              <label for="<?php echo $this->get_field_id('count'); ?>">Number of Items:</label><br />
              <?php 
                    $args = array( 
                                'id'   => $this->get_field_id('count'),
                                'selected' => $instance['count'],
                                'options'  => array(3 => '3', 4 => '4')
                                );
              $form->select($this->get_field_name('count'), $args);
              ?>
              
          </p>
        
        <?php
        
    }
    
    
    
    
    function update($new_instance, $old_instance){  
        return $new_instance;
    }
    
    
    /**
     * The output of the Carousel
     * @see WP_Widget::widget()
     * @since 2.19.13
     */
    public function widget($args, $instance){
         wp_enqueue_script('jcarousel');
        extract($args);
        
        //Retrive the latest 9 posts with the box checked for popular-articles
        $query_args = array(
                        'orderby'        => 'menu_order',
                        'posts_per_page' => 9,
                        'meta_query'     => array(
                                            array(
                                                    'key'     => 'carousel',
                                                    'value'   => 1,
                                                    'compare' => '='
                                                    )
                                    
                                ),
                        'order'            => 'ASC',
                        'post_type'        => $this->post_type
                );
                
                if( $instance['category'] ){
                       $query_args['tax_query'] = array(
                                                        array(
                                                            'taxonomy' => $this->taxonomy,
                                                            'field' => 'id',
                                                             'terms' => $instance['category']
                                                         )
                                                     );
                }
        echo $before_widget;
        
        if( !empty($instance['title']) ){
            echo $before_title.$instance['title'].$after_title;
        }
  
        
        $featured_posts = new WP_Query( $query_args );
        
        ?>
        <script type="text/javascript">
      //Start when document is ready
        jQuery(document).ready(function($) {
          
           /**
            *Setup the scrolling of the popular posts 
            */
           jQuery("#carouselME").jcarousel({
                initCallback: mycarousel_initCallback,
                wrap: 'circular',
                visible: <?php echo $instance['count'] ?>,
                scroll:  <?php echo $instance['count'] ?>
            });
        }) ;


        /**
         * Makes the custom forward and back buttons work  
         */
        function mycarousel_initCallback(carousel) {
            jQuery('#mycarousel-next').click( function() {
                carousel.next();
                return false;
            });

            jQuery('#mycarousel-prev').click( function() {
                carousel.prev();
                return false;
            });
        };


        </script>
        <?php //The Navigation Arrows ?>
        <div id="carousel-arrows">
               <div id="mycarousel-next" >
               </div>
               <div id="mycarousel-prev">
               </div>
        </div>
        
        <?php //The Output ?>
        <ul id="carouselME">
            <?php 
            if ( $featured_posts->have_posts() ) : while ( $featured_posts->have_posts() ) : $featured_posts->the_post();
                echo '<li class="' . implode( ' ', get_post_class() ) . '">';
                       echo '<div class="featured-image">';
   
                          //the featured image
                          printf(
                                    '<a href="%s" title="%s">%s</a>',
                                    get_permalink(),
                                    the_title_attribute( 'echo=0' ),
                                    genesis_get_image( array( 'format' => 'html', 'size' => 'carousel', ) )
                            );
                    
                     echo '</div><!-- End .featured-image -->';
                    
                    //Default title if not specified in Meta box
                    global $post;
                    $title = get_post_meta( $post->ID, 'carousel-title', true );
                    if( empty( $title ) ){
                        $title = get_the_title();
                    }
                    
                   printf( '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), $title);
              echo '</li><!--end post_class()-->'."\n\n";
            endwhile; endif;
        ?></ul><!-- End carouselME --><?php
        
        echo $after_widget;	
        wp_reset_query();
    }
    
    
    
    
    
}

