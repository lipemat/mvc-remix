<?php

error_reporting(0);

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class WordpressPassword extends PHPUnit_Extensions_SeleniumTestCase {

    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = 'D:\htdocs\test\screenshots\password';
    protected $screenshotUrl = 'http://test.loc/screenshots/password';
    
    
    private $old_pass = '$Bricesi8974';
    private $new_pass = '5Wtcmv!';
    

    protected function setUp() {
        $this->setBrowser("firefox");
        $this->setBrowserUrl("http://wordpress.loc/");
    }

    public function testChangePassword() {
        
        require_once 'Excel/reader.php';
        $data = new Spreadsheet_Excel_Reader();
        $data->read('sites.xls');
        $data->setOutputEncoding('CP1251');
        
     //   print_r( $data->sheets );
    //    die();
        foreach ($data->sheets[0]['cells'] as $site) {
            
         //   $this->setBrowserUrl($site[1]);
            $this->open('http://'.$site[1]."/wp-login.php");
            
            for ($second = 0; ; $second++) {
                if ($second >= 3){
                   $skip = true;
                   break;   
                }                
                    try {
                        if ($this->isElementPresent("id=user_pass")){
                           $skip = false; 
                            break;  
                        } 
                    } catch (Exception $e) {
                }
                sleep(1);
            }
            
            
            
            if( $skip ) continue;
            $this->type("id=user_pass", $this->old_pass);
            $this->type("id=user_login", "viadmin");
            $this->click("id=wp-submit");
            $this->waitForPageToLoad("30000");
            if ($this->isElementPresent("link=Edit My Profile")){
                 $this->click('link=Edit My Profile');
            } else {
                $this->click('//a[@title="Edit your profile"]');
            }
            $this->waitForPageToLoad("30000");
            $this->type("id=pass1", $this->new_pass);
            $this->type("id=pass2", $this->new_pass);
            if( $this->isElementPresent('id=submit') ){
                 $this->click("id=submit");
            } else {
                $this->click('//input[@value="Update Profile"]');
            }
            $this->waitForPageToLoad("30000");
            $this->click("link=Log Out");
            $this->waitForPageToLoad("30000");
            $this->type("id=user_pass", $this->new_pass);
            $this->type("id=user_login", "viadmin");
            $this->click("id=wp-submit");
            $this->waitForPageToLoad("30000");
            $this->assertTextPresent('Dashboard');

        }

    }

    public function TurnBack() {
        $this->open("/wp-login.php?redirect_to=http%3A%2F%2Fwordpress.loc%2Fwp-admin%2F&reauth=1");
        $this->type("id=user_pass", $this->new_pass);
        $this->type("id=user_login", "viadmin");
        $this->click("id=wp-submit");
        $this->waitForPageToLoad("30000");
        $this->click("//li[@id='menu-users']/a/div[3]");
        $this->waitForPageToLoad("30000");
        $this->click("css=strong > a");
        $this->waitForPageToLoad("30000");
        $this->type("id=pass1", $this->old_pass);
        $this->type("id=pass2", $this->old_pass);
        $this->click("id=submit");
        $this->waitForPageToLoad("30000");
    }

}
?>