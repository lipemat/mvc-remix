<?php

/**
 * Utility Type Methods for interacting with data and such
 * 
 * @since 2.0
 * @author Mat Lipe
 */
 
if( class_exists('MvcUtilites') ) return;  
 
class MvcUtilites {
    
    
    /**
     * Filters an array on every level
     * 
     * @since 2.0
     * @param array $arr
     */
   public function arrayFilterRecursive($arr) {
        $rarr = array();
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $rarr[$k] = self::arrayFilterRecursive($v);
            } else {
                if (!empty($v)) {
                    $rarr[$k] = $v;
                }
            }
        }
        $rarr = array_filter($rarr);
        return $rarr;
    }

}
    