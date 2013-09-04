<?php

             /**
              * Makes a reloadable Nanny Nine Category Posts list
              * @since 2.1.13
              * @author Mat Lipe
              * 
              * 
              * //TODO Make this have a selected able category
              * @TODO Also should name it properly and add a title text field
              */



class ajaxPostLoading extends WP_Widget {
    
    
    function __construct() {
        $widget_ops = array(
            'classname' => 'nanny-nine',
            'description' => 'Reloadable list of the post from the Nanny Nine Category.',
        );
        $control_ops = array('width' => 350);
        $this->WP_Widget('nanny-nine', 'Nanny Nine', $widget_ops, $control_ops);
        
        
        add_action('wp_enqueue_scripts', array($this,'addJs') );
        add_action('wp_head', array( $this,'outputJs') );
       
       if( isset( $_REQUEST['nanny-nine'] ) ){
            $this->getThisTabsPosts($_REQUEST['nanny-nine']);
           die();   
       }
    }
    
    
    /**
     * Creates a list of Posts specifically for this tab
     * @since 2.1.13
     */
    function getThisTabsPosts($count){
           $start = ($count-1)*3;
           $posts = get_posts( array('category'  => 11,
                                  'numberposts'  => 3,
                                  'fields'       => 'ids',
                                  'offset'       => $start ) );
                                  
           foreach( $posts as $id ){
               printf('<div class="post nanny-nine"><h2><a href="%s">%s</a></h2></div>', get_permalink($id), get_the_title($id) );   
           }
           
    }
    
    
    /**
     * Add Js to head for this functionality
     * @since 2.1.13
     */
    function outputJs(){
       ?><script type="text/javascript">
           jQuery(document).ready( function($){
                var $tabs = $('#tabs').tabs({
                               fx: { opacity: 'toggle' },
                               load: function(event,ui){
                                    $(".ui-tabs-panel").each(function(i){
                                        var totalSize = $(".ui-tabs-panel").size() - 1;
                                        var id = $(this).attr('id');
                                        if (i != 0 && id == ui.panel.id) {
                                            prev = i;
                                            $(this).append("<a href='#' class='prev-tab mover' rel='" + prev + "'>&#171; Prev </a>");
                                        }
                                        if (i != totalSize && i != 0 && id == ui.panel.id) {
                                            next = i + 2;
                                            $(this).append("<a href='#' class='next-tab mover' rel='" + next + "'>Next &#187;</a>");
                                        }
                                  });
                                  
                                  $('.next-tab, .prev-tab').click(function() { 
                                     $tabs.tabs('select', $(this).attr("rel"));
                                     return false;
                                   });    
                               }
                    });
               
                $('.next-tab, .prev-tab').click(function() { 
                                     $tabs.tabs('select', $(this).attr("rel"));
                                     return false;
                }); 
    
            
               
        });
           
       </script>
       <?php
    }
    
    /**
     * Enque the Jquery ui tabs
     * @since 2.1.13
     * 
     */
    function addJs(){
        wp_enqueue_script('jquery-ui-tabs');
    
    }
    

    function update($new, $old) {
        return $new;
    }

    function form($instance) {

    }

    /**
     * @since 2.1.13
     * @param  $args
     * @param  $settings
     */
    function widget($args, $settings) {
        if( !in_category('The Nanny Nine') && !is_category('The Nanny Nine') ) return;
        extract( $args);
        
        echo $before_widget;
        
        ?><h4 class="widgettitle">The Nanny Nine</h4><?php
        
        $posts = get_posts( array('category'     => 11,
                                  'numberposts'  => -1,
                                  'fields'       => 'ids' ) );
                               
                                  
        ?><div id="tabs">
              <ul id="nanny-nine-pages">
                 <li><a href="#tabs-1">1</a></li>
                 <?php for( $i = 2; $i <= ceil(count($posts)/3); $i++){
                            printf('<li><a href="/?nanny-nine=%s">%s</a></li>',$i, $i);
                       }
                 ?>
              </ul>
            
              <div id="tabs-1"><?php
                for( $i = 0; $i < 3; $i++ ){
                    printf('<div class="post nanny-nine"><h2><a href="%s">%s</a></h2></div>', get_permalink($posts[$i]), get_the_title($posts[$i]) );   
                }
                ?><a href="#" class="next-tab mover" rel="2">Next »</a><?php


            ?></div><?php
        ?></div><?php
        
        echo $after_widget;
    }
    
    
    function __construct() {
        $widget_ops = array(
            'classname' => 'nanny-nine',
            'description' => 'Reloadable list of the post from the Nanny Nine Category.',
        );
        $control_ops = array('width' => 350);
        $this->WP_Widget('nanny-nine', 'Nanny Nine', $widget_ops, $control_ops);
        
        
        add_action('wp_enqueue_scripts', array($this,'addJs') );
        add_action('wp_head', array( $this,'outputJs') );
       
       if( isset( $_REQUEST['nanny-nine'] ) ){
            $this->getThisTabsPosts($_REQUEST['nanny-nine']);
           die();   
       }
    }
    
    
    /**
     * Creates a list of Posts specifically for this tab
     * @since 2.1.13
     */
    function getThisTabsPosts($count){
           $start = ($count-1)*3;
           $posts = get_posts( array('category'  => 'The Nanny Nine',
                                  'numberposts'  => 3,
                                  'fields'       => 'ids',
                                  'offset'       => $start ) );
                                  
           foreach( $posts as $id ){
               printf('<div class="post nanny-nine"><h2><a href="%s">%s</a></h2></div>', get_permalink($id), get_the_title($id) );   
           }
           
    }
    
    
    /**
     * Add Js to head for this functionality
     * @since 2.1.13
     */
    function outputJs(){
       ?><script type="text/javascript">
           jQuery(document).ready( function($){
                var $tabs = $('#tabs').tabs({
                               fx: { opacity: 'toggle' },
                               load: function(event,ui){
                                    $(".ui-tabs-panel").each(function(i){
                                        var totalSize = $(".ui-tabs-panel").size() - 1;
                                        var id = $(this).attr('id');
                                        if (i != 0 && id == ui.panel.id) {
                                            prev = i;
                                            $(this).append("<a href='#' class='prev-tab mover' rel='" + prev + "'>&#171; Prev Page</a>");
                                        }
                                        if (i != totalSize && i != 0 && id == ui.panel.id) {
                                            next = i + 2;
                                            $(this).append("<a href='#' class='next-tab mover' rel='" + next + "'>Next Page &#187;</a>");
                                        }
                                  });
                                  
                                  $('.next-tab, .prev-tab').click(function() { 
                                     $tabs.tabs('select', $(this).attr("rel"));
                                     return false;
                                   });    
                               }
                    });
               
                $('.next-tab, .prev-tab').click(function() { 
                                     $tabs.tabs('select', $(this).attr("rel"));
                                     return false;
                }); 
    
            
               
        });
           
       </script>
       <?php
    }
    
    /**
     * Enque the Jquery ui tabs
     * @since 2.1.13
     * 
     */
    function addJs(){
        wp_enqueue_script('jquery-ui-tabs');
    
    }
    

    function update($new, $old) {
        return $new;
    }

    function form($instance) {

    }

    /**
     * @since 2.1.13
     * @param  $args
     * @param  $settings
     */
    function widget($args, $settings) {
        if( !in_category('The Nanny Nine') && !is_category('The Nanny Nine') ) return;
        
        
        echo $before_widget;
        
        ?><h4 class="widgettitle">The Nanny Nine</h4><?php
        
        $posts = get_posts( array('category'     => 'The Nanny Nine',
                                  'numberposts'  => -1,
                                  'fields'       => 'ids' ) );
        ?><div id="tabs">
              <ul id="nanny-nine-pages">
                 <li><a href="#tabs-1">1</a></li>
                 <?php for( $i = 2; $i <= ceil(count($posts)/3); $i++){
                            printf('<li><a href="/?nanny-nine=%s">%s</a></li>',$i, $i);
                       }
                 ?>
              </ul>
            
              <div id="tabs-1"><?php
                for( $i = 0; $i < 3; $i++ ){
                    printf('<div class="post nanny-nine"><h2><a href="%s">%s</a></h2></div>', get_permalink($posts[$i]), get_the_title($posts[$i]) );   
                }
                ?><a href="#" class="next-tab mover" rel="2">Next Page »</a><?php


            ?></div><?php
        ?></div><?php
        
        echo $after_widget;
    }
}





 