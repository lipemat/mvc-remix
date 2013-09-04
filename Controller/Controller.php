<?php

                     /**
                      * Methods that will be made available to all Controllers
                      * @since 2.2
                      * @author Mat Lipe
                      */



class Controller extends MvcFramework{
        
        /**
         * Will run only once on page load
         * @uses Must have this method
         */
        public function init(){
            
        }
        
        
        /**
         * Will run right before the page is rendered
         * @uses Optional Method for using conditional/hooks etc that must be run later in the load
         */
        public function before(){

        }
        
}
    