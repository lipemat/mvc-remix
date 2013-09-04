<?php
require('wordpress.class.php');
            /**
             * Basic Setup of Tests
             * @since 2.1.13
             * @author Mat Lipe
             */
class Basics extends wordpressTesting{
    
    
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://wordpress.loc/");
    $this->verifyLocal();
    
  }       
        
        
  function testAddPosts(){
     $this->login('viadmin','5Wtcmv!');
      
      $this->addPosts();
      
  }      
   
        
        
        
        
}
    