<?php

/**
 * Deprecated Methods from the Framework
 * 
 * @since 5.5.0
 * 
 * @author Mat Lipe
 * @uses These should no longer be used. Only here to prevent breaking on Updates
 * 
 */
 
class MvcDeprecated extends MvcFramework{
   
    /**
     * @deprecated in favor of MvcFramework::getFirstImage()
     */
    function get_first_image( $postId = false, $size = 'thumbnail', $html = true ){
        return $this->getFirstImage( $size, $postId, $html);   
    }    
}
