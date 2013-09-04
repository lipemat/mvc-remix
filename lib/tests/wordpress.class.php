<?php

/**
 * Handy Methods for Wordpress Selenium Testing
 * @author mat Lipe
 * @since 2.1.13
 * @uses extend your testing class with this one and use like normal
 *
 */

class wordpressTesting extends PHPUnit_Extensions_SeleniumTestCase
{

	protected $captureScreenshotOnFailure = TRUE;
	protected $screenshotPath = 'd:/htdocs/test/screenshots/child';
	protected $screenshotUrl = 'http://test.loc/screenshots/child';

	public $sample_images_folder = 'C:\\Users\\Public\\Pictures\\Sample Pictures\\';


	/**
	 * Some default Post Names
	 * @var unknown
	 */
	public $post_names = array(
			array( 'title' => 'I\'m a Test Post' ),
			array( 'title' => 'Testing' ),
			array( 'title' => 'The third test post' ),
			array( 'title' => 'I\'m a post which should be deleted' ),
			array( 'title' => 'Fifth Post For Lots Long Name' ),
			array( 'title' => 'Post Number Six to make sure get_posts is set properly' )
	);

	/**
	 * Some default images in the Window Sample Images folder
	 * @var unknown
	*/
	public $images = array( 'Desert.jpg',
			'Chrysanthemum.jpg',
			'Hydrangeas.jpg',
			'Jellyfish.jpg',
			'Koala.jpg',
			'Lighthouse.jpg',
			'Penguins.jpg',
			'Tulips.jpg',
			'Jellyfish.jpg',
			'Koala.jpg',
			'Lighthouse.jpg',
			'Penguins.jpg',
			'Tulips.jpg'
	);

	/**
	 * Makes sure we are testing locally and not on live server
	*/
	protected function verifyLocal(){
		if( $_SERVER['HOMEDRIVE'] != 'C:' ) die();
		 
	}
    
    
    /**
     * Adds 6 Post with Featured Images to Test With
     * @since 2.1.13
     * 
     * @TODO add the feature image back in when working on Wordpress 3.5
     */
    protected function addPosts(){
       foreach( $this->post_names as $key => $name ){
            $this->click("link=Add New");
            $this->waitForPageToLoad("30000");
            $this->type("id=title", $name['title']);
          //  $this->addFeaturedImage($this->images[$key]);
            $this->click("id=publish");
            $this->waitForPageToLoad("30000");   
       }
        
    }
    


	/**
	 * Add a feature Image
	 * @param $image the image file name
	 * @param $folder the image location - defaults to sample images
     * 
     * 
     * @TODO Make Work with Wordpress 3.5
     * 
	 */
	protected function addFeaturedImage( $image, $folder = false ){
		if( !$folder ){
			$folder = $this->sample_images_folder;
		}
		$this->click("id=set-post-thumbnail");
		$this->waitForFrameToLoad("id=TB_iframeContent", "");
		$this->selectFrame("id=TB_iframeContent");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("css=.plupload")) break;
			} catch (Exception $e) {
			}
			sleep(1);
		}

		$this->type("xpath=//input[@type='file']", $image);
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("css=.wp-post-thumbnail")) break;
			} catch (Exception $e) {
			}
			sleep(1);
		}

		$this->click("css=.wp-post-thumbnail");
		$this->selectFrame("relative=up");
		$this->click("css=#TB_closeWindowButton > img");
	}


	/**
	 * Logs into the site
	 * @param $user optional username
	 * @param $password optional password
	 */
	protected function login( $user = "test", $password = 'test' ){
		$this->open("/wp-admin/");
		$this->type("id=user_pass", $password);
		$this->type("id=user_login", $user);
		$this->click("id=wp-submit");
		$this->waitForPageToLoad("30000");
	}


	/**
	 * Uploads an image through the post uploader
	 * @param string $img image file name
	 * @param string $folder - defaults to sample images
     * 
     * 
     * @TODO Make Work on Wordpress 3.5
	 */
	function addImage( $img, $folder = false ){
		 
		if( !$folder ){
			$folder = $this->sample_images_folder;
		}
		$this->click("css=#content-add_media > img");
		$this->waitForFrameToLoad("id=TB_iframeContent","");
		$this->selectFrame("id=TB_iframeContent");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("css=.plupload")) break;
			} catch (Exception $e) {
			}
			sleep(1);
		}
		 
		$this->type("xpath=//input[@type='file']", $image);
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("xpath=//input[@value='Insert into Post']")) break;
			} catch (Exception $e) {
			}
			sleep(1);
		}
		 
		$this->click("xpath=//input[@value='Insert into Post']");
		//$this->click("css=#TB_closeWindowButton > img");
		$this->selectFrame("relative=up");
		 
	}


	/**
	 * Deletes all post of a type
	 * @param string $postTypePlural defaults to post
	 * @uses send the plural label of a post
	 */
	function deleteAllPosts( $postTypePlural = 'posts' ){
		$this->click("link=All ".$post_type);
		$this->waitForPageToLoad("30000");
		$this->click("css=#cb > input[type=\"checkbox\"]");
		$this->select("name=action", "label=Move to Trash");
		$this->click("id=doaction");
		$this->waitForPageToLoad("30000");
		$this->click("css=li.trash > a");
		$this->waitForPageToLoad("30000");
		$this->click("id=delete_all");
		$this->waitForPageToLoad("30000");
	}


	/**
	 * Edits the post url slug on the post edit screen
	 * @param string $slug the desired slug
	 */
	function editPostUrlSlug( $slug ){
		$this->click("id=publish");
		for ($second = 0; ; $second++) {
			if ($second >= 10) $this->fail("timeout");
			try {
				if ($this->isElementPresent("xpath=//span[@id='edit-slug-buttons']//a")) break;
			} catch (Exception $e) {
			}
			sleep(1);
		}
		$this->click("xpath=//span[@id='edit-slug-buttons']//a");
		$this->type('id=new-post-slug', $slug );
		$this->click('link=OK');
		 
	}




}
