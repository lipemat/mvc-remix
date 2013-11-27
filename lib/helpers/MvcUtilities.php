<?php

/**
 * Utility Type Methods for interacting with data and such
 * 
 * @author Mat Lipe
 * 
 * @since 11.27.13
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
   
   
   /**
    * Coverts a string date to a Mysql Time Stamp
    * 
    * @since 11.27.13
    * 
    * @param string $date - the date string
    * 
    * @return string 
    * 
    */
   public function stringToMysqlTimeStamp( $date ){
       $timestamp = strtotime($date);
       return date("Y-m-d H:i:s", $timestamp);
   }
   
   
   /**
    * Coverts Mysql Time Stamp to string Date
    * 
    * @since 11.27.13
    * 
    * @param string $date - the date string
    * 
    * @return string 
    * 
    */
   public function MysqlTimeStampToString( $date, $format = 'm/d/Y'){
       $timestamp = strtotime($date);
       return date($format, $timestamp);
   }
   
   




}
    