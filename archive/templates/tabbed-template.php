<?php

/*
 Template Name: Tabbed
*/

add_action( 'genesis_after_post_content', array( $MvcFramework,'tabsOutput' ) );


genesis();
